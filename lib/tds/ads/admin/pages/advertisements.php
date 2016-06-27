<?php
namespace TDS\Ads\Admin\Pages;

use TDS\Ads\Admin;
use GW;

class Advertisements {
    private $notice = null;

    public function __construct() {
        $this->config = GW\Config::instance(TDS_ADS_PLUGIN_NAME);
    }

    public function process() {
        if (isset($_POST['action'])) {
            if ($_POST['action'] == 'tds-add-advertiser') {
                $this->createAdvertiser();
            }
        } elseif (isset($_POST['tds-advertiser-delete-id'])) {
            $this->deleteAdvertiser();
        }
    }

    public function createAdvertiser() {
        global $wpdb;

        $name = isset($_POST['name']) ? trim($_POST['name']) : null;
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;
        $res = $wpdb->insert($this->config->dbprefix . 'advertisers', array(
            'name' => $name,
            'description' => $description
        ), array('%s', '%s'));

        if (false === $res) {
            $this->notice = array(
                'type' => 'failure',
                'msg' => 'Could not create advertiser'
            );
        } else {
            $this->notice = array(
                'type' => 'success',
                'msg' => 'Advertiser successfully created'
            );
        }
    }

    public function deleteAdvertiser() {
        global $wpdb;

        $id = intval($_POST['tds-advertiser-delete-id']);
        $res = $wpdb->delete($this->config->dbprefix . 'advertisers', array(
            'id' => $id
        ), array('%d'));

        if (false === $res) {
            $this->notice = array(
                'type' => 'failure',
                'msg' => 'Could not remove advertiser'
            );
        } else {
            $this->notice = array(
                'type' => 'success',
                'msg' => 'Advertiser successfully deleted'
            );
        }
    }

    public function loadAdvertisers() {
        global $wpdb;

        $table = $this->config->dbprefix . 'advertisers';
        $sql = <<<EOS
SELECT * FROM $table
EOS;

        return $wpdb->get_results($sql, ARRAY_A);
    }

    public function render() {
        $data = array();
        if (null !== $this->notice) $data['notice'] = $this->notice;
        $data['advertisers'] = $this->loadAdvertisers();

        Admin\View::render('advertisements', $data);
    }
}
