<?php
namespace TDS\Ads\Admin\Pages;

use TDS\Ads\Admin;
use GW;

class Campaigns {
    private function loadAds() {
        global $wpdb;

        $t_ads = $this->config->dbprefix . 'ads';
        $t_advertisers = $this->config->dbprefix . 'advertisers';

        $sql = <<<EOQ
SELECT ads.*, a.name AS advertiser
FROM $t_ads ads, $t_advertisers a
WHERE ads.advertiser_id = a.id
ORDER BY ads.name ASC
EOQ;

        $this->ads = $wpdb->get_results($sql, ARRAY_A);
    }

    private function loadCampaignAds() {
        global $wpdb;

        $t_campaign_ads = $this->config->dbprefix . 'campaign_ads';
        $t_ads = $this->config->dbprefix . 'ads';

        $sql = <<<EOQ
SELECT ca.id, ca.ad_id, a.name
FROM $t_campaign_ads ca, $t_ads a
WHERE ca.ad_id = a.id
AND ca.campaign_id = $this->campaign_id
ORDER BY a.name
EOQ;

        $this->campaign_ads = $wpdb->get_results($sql, ARRAY_A);
    }

    private function loadView() {
        global $wpdb;

        $view_id = $wpdb->get_var(sprintf('SELECT id FROM %s WHERE campaign_id = %d LIMIT 1',
            $this->config->dbprefix . 'views',
            $this->campaign_id
        ));

        $this->view_id = $view_id;
    }

    private function loadDetails() {
        $this->details = array();
        if (!$this->campaign_id) return;

        global $wpdb;

        // Campaign details
        $t_campaigns = $this->config->dbprefix . 'campaigns';
        $sql = <<<EOQ
SELECT id, name, description
FROM $t_campaigns
WHERE id = $this->campaign_id
LIMIT 1
EOQ;

        $details = $wpdb->get_row($sql, ARRAY_A);
        $this->details['campaign-name'] = $details['name'];
        $this->details['campaign-description'] = $details['description'];


        // View details
        $t_view = $this->config->dbprefix . 'views';
        $sql = <<<EOQ

EOQ;
    }

    private function loadSlots() {
        global $wpdb;

        $t_slots = $this->config->dbprefix . 'view_slots';
        $t_campaign_ads = $this->config->dbprefix . 'campaign_ads';
        $t_ads = $this->config->dbprefix . 'ads';

        $sql = <<<EOQ
SELECT s.*, a.name
FROM $t_slots s
LEFT JOIN $t_campaign_ads c ON (s.campaign_ad_id = c.id)
LEFT JOIN $t_ads a ON (c.ad_id = a.id)
WHERE s.view_id = $this->view_id
ORDER BY position
EOQ;

        $this->slots = $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Create a new campaign and corresponding view.
     * On success, we need to render the Display view
     */
    private function createCampaign() {
        global $wpdb;

        $campaign_res = $wpdb->insert($this->config->dbprefix . 'campaigns', array(
            'name' => $this->data['campaign-name'],
            'description' => $this->data['campaign-description']
        ), array('%s', '%s'));

        if (false !== $campaign_res) {
            $campaign_id = $wpdb->insert_id;

            $view_name = sprintf('%s_%d', 'tds_campaign', $campaign_id);
            $view_res = $wpdb->insert($this->config->dbprefix . 'views', array(
                'campaign_id' => $campaign_id,
                'name' => $view_name
            ), array('%d', '%s'));

            if (false !== $view_res) {
                $view_id = $wpdb->insert_id;
                $this->view_id = $view_id;

                // Store campaign ads
                foreach ($this->data['campaign-ads'] as $ad) {
                    $wpdb->insert($this->config->dbprefix . 'campaign_ads', array(
                        'campaign_id' => $campaign_id,
                        'ad_id' => $ad
                    ), array('%d', '%d'));
                }

                // If we were successful, redirect to the next step
                wp_redirect(admin_url('/page=tds-ads-plugin-campaigns&action=create&id=' . $this->campaign_id, 'http'));
                exit;

                $this->force_update = true;
            } else {
                // Rollback campaign creation
                $wpdb->delete($this->config->dbprefix . 'campaigns', array('id' => $campaign_id), array('%d'));
                $this->notice = array(
                    'type' => 'failure',
                    'msg' => 'Could not create campaign'
                );
            }
        } else {
            $this->notice = array(
                'type' => 'failure',
                'msg' => 'Could not create campaign'
            );
        }
    }

    private function updateCampaign() {
        global $wpdb;

        $campaign_res = $wpdb->update($this->config->dbprefix . 'campaigns', array(
            'name' => $this->data['campaign-name'],
            'description' => $this->data['campaign-description'],
            'updated' => date('Y-m-d H:i:s')
        ), array(
            'id' => $this->campaign_id
        ), array('%s', '%s'));

        if (false !== $campaign_res) {
            $t_campaign_ads = $this->config->dbprefix . 'campaign_ads';

            // Clean up POST data
            $ads = array();
            foreach ($this->data['campaign-ads'] as $ad) {
                $ads[] = intval($ad);
            }

            // Delete any NOT IN the current array
            $wpdb->query(
                sprintf('DELETE FROM %s WHERE campaign_id = %d AND ad_id NOT IN ("%s")', $t_campaign_ads, $this->campaign_id, implode('", "', $ads))
            );

            // Replace into in order to insert only new records
            foreach ($this->data['campaign-ads'] as $ad) {
                $wpdb->replace($t_campaign_ads, array(
                    'campaign_id' => $this->campaign_id,
                    'ad_id' => $ad
                ), array('%d', '%d'));
            }

            $this->notice = array(
                'type' => 'success',
                'msg' => 'Updated campaign'
            );

        } else {
            $this->notice = array(
                'type' => 'failure',
                'msg' => 'Could not create campaign'
            );
        }
    }

    public function updateView() {
        global $wpdb;

        $slot_positions = array_map(function ($val) {
            return intval($val);
        }, $this->data['slot-position']);

        $slot_position_articles = array_map(function ($val) {
            return intval($val);
        }, $this->data['slot-position-article']);

        // First, clear out any slots not indicated in this update
        $t_view_slots = $this->config->dbprefix . 'view_slots';
        $wpdb->query(
            sprintf('DELETE FROM %s WHERE view_id = %d AND position NOT IN (%s)', $t_view_slots, $this->view_id, implode(', ', $slot_positions))
        );

        // Next, replace into table all the new slots
        foreach ($slot_positions as $index => $ad) {
            $wpdb->replace($t_view_slots, array(
                'view_id' => $this->view_id,
                'position' => $ad,
                'campaign_ad_id' => $slot_position_articles[$index]
            ), array('%d', '%d', '%d'));
        }

        $this->notice = array(
            'type' => 'success',
            'msg' => 'Updated view with new rules'
        );
    }

    public function __construct() {
        $this->force_update = false;
        $this->config = GW\Config::instance(TDS_ADS_PLUGIN_NAME);
    }

    public function process($id = null) {
        $this->campaign_id = $id;
        $this->loadDetails();
        $this->loadView();

        $fields = array(
            'campaign-name',
            'campaign-description'
        );

        $this->data = array();
        foreach ($fields as $key) {
            $this->data[$key] = isset($_POST[$key]) ? trim($_POST[$key]) : $this->details[$key];
        }

        $this->data['campaign-ads'] = isset($_POST['campaign-ads']) ? $_POST['campaign-ads'] : array();

        if (isset($_POST['campaign-create'])) {
            $this->createCampaign();
        } elseif (isset($_POST['campaign-update'])) {
            $this->updateCampaign();
        } elseif (isset($_POST['campaign-update-view'])) {
            $this->data['slot-position'] = isset($_POST['slot-position']) ? $_POST['slot-position'] : array();
            $this->data['slot-position-article'] = isset($_POST['slot-position-article']) ? $_POST['slot-position-article'] : array();
            $this->updateView();
        }

        $this->loadAds();
        $this->loadCampaignAds();
        $this->loadSlots();
    }

    public function renderCreate() {
        if ($this->campaign_id) {
            Admin\View::render('campaign-view', array(
                'details' => $this->details,
                'action' => 'create',
                'ads' => $this->campaign_ads,
                'campaign' => $this->data,
                'slots' => $this->slots,
                'notice' => isset($this->notice) ? $this->notice : null
            ));
        } else {
            Admin\View::render('campaign-form', array(
                'action' => 'create',
                'ads' => $this->ads,
                'campaign_name' => $this->data['campaign-name'],
                'campaign_description' => $this->data['campaign-description'],
                'notice' => isset($this->notice) ? $this->notice : null
            ));
        }
    }

    public function getPageLink($view = 'details') {
        $parts = array('page=' . $_GET['page']);
        $parts[] = 'action=edit';
        $parts[] = 'view=' . $view;
        if ($this->campaign_id) {
            $parts[] = 'id=' . $this->campaign_id;
        }
        return '?' . implode('&', $parts);
    }

    public function renderUpdate() {
        $details_link = $this->getPageLink();
        $view_link = $this->getPageLink('view');

        if (isset($_GET['view']) && $_GET['view'] == 'view') {
            Admin\View::render('campaign-view', array(
                'link_details' => $details_link,
                'link_view' => $view_link,
                'action' => 'edit',
                'details' => $this->details,
                'campaign' => $this->data,
                'campaign_ads' => $this->campaign_ads,
                'slots' => $this->slots,
                'notice' => isset($this->notice) ? $this->notice : null
            ));
        } else {
            Admin\View::render('campaign-form', array(
                'link_details' => $details_link,
                'link_view' => $view_link,
                'details' => $this->details,
                'action' => 'edit',
                'ads' => $this->ads,
                'campaign_ads' => $this->campaign_ads,
                'campaign_name' => $this->data['campaign-name'],
                'campaign_description' => $this->data['campaign-description'],
                'notice' => isset($this->notice) ? $this->notice : null
            ));
        }
    }
}
