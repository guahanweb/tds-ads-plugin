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
                // Store campaign ads
                foreach ($this->data['campaign-ads'] as $ad) {
                    $wpdb->insert($this->config->dbprefix . 'campaign_ads', array(
                        'campaign_id' => $campaign_id,
                        'ad_id' => $ad
                    ), array('%d', '%d'));
                }

                $this->notice = array(
                    'type' => 'success',
                    'message' => 'Successfully created campaign'
                );
                $this->force_update = true;
            } else {
                // Rollback campaign creation
                $wpdb->delete($this->config->dbprefix . 'campaigns', array('id' => $campaign_id), array('%d'));
                $this->notice = array(
                    'type' => 'failure',
                    'message' => 'Could not create campaign'
                );
            }
        } else {
            $this->notice = array(
                'type' => 'failure',
                'message' => 'Could not create campaign'
            );
        }
    }

    private function updateCampaign() {

    }

    public function __construct() {
        $this->force_update = false;
        $this->config = GW\Config::instance(TDS_ADS_PLUGIN_NAME);
    }

    public function process() {
        $fields = array(
            'campaign-name',
            'campaign-description'
        );

        $this->loadAds();
        $this->data = array();
        foreach ($fields as $key) {
            $this->data[$key] = isset($_POST[$key]) ? trim($_POST[$key]) : null;
        }

        $this->data['campaign-ads'] = isset($_POST['campaign-ads']) ? $_POST['campaign-ads'] : array();

        if (isset($_POST['campaign-create'])) {
            $this->createCampaign();
        } elseif (isset($_POST['campaign-update'])) {
            $this->updateCampaign();
        }
    }

    public function renderCreate() {
        if ($this->force_update || isset($_GET['debug-display'])) {
            Admin\View::render('campaign-view', array(
                'action' => 'create',
                'ads' => $this->ads,
                'campaign' => $this->data
            ));
        } else {
            Admin\View::render('campaign-form', array(
                'action' => 'create',
                'ads' => $this->ads,
                'campaign_name' => $this->data['campaign-name'],
                'campaign_description' => $this->data['campaign-description']
            ));
        }
    }

    public function renderUpdate() {
        Admin\View::render('campaign-form', array(
            'action' => 'edit',
            'ads' => $this->ads,
            'campaign_name' => $this->data['campaign-name'],
            'campaign_description' => $this->data['campaign-description'],
            'campaign_hook' => $this->data['campaign-hook']
        ));
    }
}
