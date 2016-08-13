<?php
namespace TDS\Ads\Admin\Pages;

use TDS\Ads\Admin;
use GW;

class CampaignList {
    public function __construct() {
        $this->config = GW\Config::instance(TDS_ADS_PLUGIN_NAME);
    }

    public function process() {

    }

    public function loadCampaigns() {
        global $wpdb;

        $t_advertisers = $this->config->dbprefix . 'advertisers';
        $t_ads = $this->config->dbprefix . 'ads';
        $t_campaigns = $this->config->dbprefix . 'campaigns';
        $t_campaign_ads = $this->config->dbprefix . 'campaign_ads';
        $t_views = $this->config->dbprefix . 'views';
        $t_view_slots = $this->config->dbprefix . 'view_slots';

        $sql = <<<EOS
SELECT c.id, c.created, c.updated, c.name, c.description, v.name as hook
FROM $t_campaigns c
INNER JOIN $t_views v ON (c.id = v.campaign_id)
ORDER BY c.created DESC
EOS;
        $campaigns = $wpdb->get_results($sql, ARRAY_A);

        // Load ad details
        foreach ($campaigns as $i => $campaign) {
            $id = $campaign['id'];
            $sql = <<<EOS
SELECT ca.id, ca.ad_id, ca.start_date, ca.end_date, a.name, a.description
FROM $t_campaign_ads ca
INNER JOIN $t_ads a ON (ca.ad_id = a.id)
WHERE ca.campaign_id = $id
EOS;

            $campaigns[$i]['ads'] = $wpdb->get_results($sql, ARRAY_A);
        }

        return $campaigns;
    }

    public function getPageLink($action = 'create') {
        $parts = array('page=' . $_GET['page']);
        $parts[] = 'action=' . $action;
        return '?' . implode('&', $parts);
    }

    public function render() {
        Admin\View::render('campaign-list', array(
            'new_link' => $this->getPageLink(),
            'edit_link' => $this->getPageLink('edit'),
            'campaigns' => $this->loadCampaigns()
        ));
    }
}
