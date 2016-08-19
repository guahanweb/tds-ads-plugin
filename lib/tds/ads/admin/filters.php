<?php
namespace TDS\Ads\Admin;

use GW;

class Filters {
    private $filters;
    private $cache;

    static public function instance() {
        static $instance;

        if (null === $instance) {
            $instance = new Filters();
            $instance->config = GW\Config::instance(TDS_ADS_PLUGIN_NAME);
            $instance->cache = CampaignCache::instance();
            $instance->ads = array();
            $instance->listen();
        }

        return $instance;
    }

    public function __construct() {
    }

    public function listen() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
        add_action('tds_ad_campaign', array($this, 'action'), 1, 2);
        add_filter('wp_footer', array($this, 'renderClientCode'), 1);
    }

    public function enqueue() {
        if (!is_admin()) {
            wp_enqueue_script('tds-campaign-ads', $this->config->plugin_uri . 'assets/js/campaign.js', array('jquery'), $this->config->version, true);
        }
    }

    /**
     * Based on the ID and index provided, render out ad holders appropriately.
     * Action is to be done after render of individual posts, so we can render
     * out the placeholder for the add accordingly. Subsequently, the JavaScript
     * will calculate any randomization needed and place the ad content into
     * location.
     *
     * On the first iteration of any particular campaign ID, we will cache the
     * results locally to reduce the number of DB hits.
     *
     * @param $id The campaign ID to render
     * @param $index The index of the_loop we are in
     */
    public function action($hook, $index) {
        // If we don't recognize the ID, continue
        if (!preg_match('/^tds_campaign_(\d+)$/', $hook, $match)) {
            return null;
        }

        $id = $match[1];
        $campaign = $this->cache->$id;
        if (FALSE !== ($index = array_search($index, $campaign['slots']['position']))) {
            $ad_id = $campaign['slots']['ad'][$index];
            $html_data = array('tds-campaign' => $id);
            if ($ad_id === NULL) {
                $html_data['tds-ad-random'] = 'true';
                $html_data['tds-ad-id'] = null;
            } else {
                $html_data['tds-ad-random'] = 'false';
                $html_data['tds-ad-id'] = $campaign['ads'][$ad_id];
            }

            // Allow consumers to apply their own classes to the holders
            $html_class = apply_filters('tds-campaign-ad-class', array(
                'tds-campaign-ad', 'tds-ad-inline'
            ));

            $data_attributes = array();
            foreach ($html_data as $k => $v) {
                $data_attributes[] = sprintf('data-%s="%s"', $k, $v);
            }

            printf('<div class="%s" %s></div>',
                implode(' ', $html_class),
                implode(' ', $data_attributes)
            );
        }
    }

    /**
     * Render single hidden ads for all those that have been cached during
     * action calls.
     */
    public function renderClientCode() {
        // unique list of ads to be cloned into place
        $ads = $this->cache->getAds();
        $campaigns = $this->cache->flush();
        echo '<div id="tds-campaign-ad-cache" class="hide hidden tds-ad-cache">';
        foreach ($ads as $ad) {
            printf('<div class="tds-ad" id="tds-ad-%d">%s</div>', $ad['ad_id'], $ad['content']);
        }
        echo '</div>';

        // Print the JavaScript to know what ads are available for each campaign
        $list = array();
        foreach ($campaigns as $campaign) {
            $list[$campaign['id']] = array_unique(array_values($campaign['ads']));
        }
        echo "<script>\n";
        printf("var TDS_CAMPAIGN_ADS = %s;\n", json_encode($list));
        echo "</script>\n";
    }
}

class CampaignCache {
    static public function instance() {
        static $instance;
        if (null === $instance) {
            $instance = new CampaignCache();
            $instance->config = GW\Config::instance(TDS_ADS_PLUGIN_NAME);
            $instance->campaigns = array();
            $instance->ads = array();
        }
        return $instance;
    }

    public function __construct() {
    }

    /**
     * Use cached version if it exists. Otherwise, attempt to load it.
     * If we fail loading, just return null.
     *
     * @param $k the ID of the campaign to load
     * @return object|null
     */
    public function __get($k) {
        if (!isset($this->campaigns[$k])) {
            $this->loadCampaignDetails($k);
        }
        return $this->campaigns[$k];
    }

    public function getAds() {
        return $this->ads;
    }

    /**
     * Returns all campaigns in the current cache and clears it
     * @return array
     */
    public function flush() {
        $ret = $this->campaigns;
        $this->campaigns = array();
        $this->ads = array();
        return $ret;
    }

    /**
     * If we cannot find the ID, set the return value to NULL to
     * avoid lookups on future loops.
     */
    private function loadCampaignDetails($id) {
        global $wpdb;

        // Query and cache all campaign details
        $t_campaigns = $this->config->dbprefix . 'campaigns';
        $t_views = $this->config->dbprefix . 'views';
        $t_ads = $this->config->dbprefix . 'ads';
        $t_campaign_ads = $this->config->dbprefix . 'campaign_ads';
        $t_slots = $this->config->dbprefix . 'view_slots';

        $sql = <<<EOQ
SELECT c.id, c.name, c.description, v.id AS view_id, v.name AS hook
FROM %s c
INNER JOIN %s v ON (c.id = v.campaign_id)
WHERE c.id = %d
LIMIT 1
EOQ;

        $data = $wpdb->get_results(sprintf($sql, $t_campaigns, $t_views, intval($id)), ARRAY_A);
        if (count($data) !== 1) {
            $this->campaigns[$id] = false;
            return;
        }

        // Load the ads (taking into account expiration)
        $campaign = $data[0];
        $sql = <<<EOQ
SELECT ca.id, a.id AS ad_id, a.content
FROM %s a
INNER JOIN %s ca ON (a.id = ca.ad_id)
WHERE ca.campaign_id = %d
AND NOW() BETWEEN ca.start_date AND ca.end_date
EOQ;
        $data = $wpdb->get_results(sprintf($sql, $t_ads, $t_campaign_ads, intval($id)), ARRAY_A);
        $campaign['ads'] = array();
        foreach ($data as $ad) {
            $ad_id = $ad['ad_id'];
            $campaign_ad_id = $ad['id'];
            $campaign['ads'][$campaign_ad_id] = $ad['ad_id'];

            // Cache the unique ads as well
            if (!isset($this->ads[$ad_id])) {
                $this->ads[$ad_id] = $ad;
            }
        }

        // Load the view rules
        $sql = <<<EOQ
SELECT vs.position, vs.campaign_ad_id AS id
FROM %s vs
LEFT JOIN %s v ON (vs.view_id = v.id)
WHERE v.campaign_id = %d
EOQ;
        $data = $wpdb->get_results(sprintf($sql, $t_slots, $t_views, intval($id)), ARRAY_A);
        $campaign['slots'] = array(
            'position' => array(),
            'ad' => array()
        );
        foreach ($data as $rule) {
            $campaign['slots']['position'][] = $rule['position'];
            $campaign['slots']['ad'][] = ($rule['id'] == 0) ? null : intval($rule['id']);
        }

        $this->campaigns[$id] = $campaign;
    }
}
