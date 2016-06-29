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
            } elseif ($_POST['action'] == 'tds-add-advertisement') {
                $this->createAdvertisement();
            } elseif ($_POST['action'] == 'tds-edit-advertisement') {
                $this->editAdvertisement();
            }
        } elseif (isset($_POST['tds-advertiser-delete-id'])) {
            $this->deleteAdvertiser();
        } elseif (isset($_POST['tds-advertisement-delete-id'])) {
            $this->deleteAdvertisement();
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

    public function deleteAdvertisement() {
        global $wpdb;

        $id = intval($_POST['tds-advertisement-delete-id']);
        $res = $wpdb->delete($this->config->dbprefix . 'ads', array(
            'id' => $id
        ), array('%d'));

        if (false === $res) {
            $this->notice = array(
                'type' => 'failure',
                'msg' => 'Could not remove advertisement'
            );
        } else {
            $this->notice = array(
                'type' => 'success',
                'msg' => 'Advertisement successfully deleted'
            );
        }
    }

    public function createAdvertisement() {
        global $wpdb;

        $advertiser = isset($_POST['advertiser']) ? intval($_POST['advertiser']) : null;
        $name = isset($_POST['name']) ? trim($_POST['name']) : null;
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;
        $content = isset($_POST['content']) ? trim($_POST['content']) : null;

        $res = $wpdb->insert($this->config->dbprefix . 'ads', array(
            'advertiser_id' => $advertiser,
            'name' => $name,
            'description' => $description,
            'content' => $content
        ), array('%d', '%s', '%s', '%s'));

        if (false === $res) {
            $this->notice = array(
                'type' => 'failure',
                'msg' => 'Could not create ad'
            );
        } else {
            $this->notice = array(
                'type' => 'success',
                'msg' => 'Ad successfully created'
            );
        }
    }

    public function editAdvertisement() {
        global $wpdb;

        $id = isset($_POST['id']) ? intval($_POST['id']) : null;
        $advertiser = isset($_POST['advertiser']) ? intval($_POST['advertiser']) : null;
        $name = isset($_POST['name']) ? trim($_POST['name']) : null;
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;
        $content = isset($_POST['content']) ? trim($_POST['content']) : null;

        $res = $wpdb->update(
            $this->config->dbprefix . 'ads', // table
            array(
                'advertiser_id' => $advertiser,
                'name' => $name,
                'description' => $description,
                'content' => $content
            ),
            array(
                'id' => $id
            ),
            array('%d', '%s', '%s', '%s'),
            array('%d')
        );

        if (false === $res) {
            $this->notice = array(
                'type' => 'failure',
                'msg' => 'Could not update ad'
            );
        } else {
            $this->notice = array(
                'type' => 'success',
                'msg' => 'Ad successfully updated'
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

    public function loadAdvertisements() {
        global $wpdb;

        $table = $this->config->dbprefix . 'ads';
        $sql = <<<EOS
SELECT * FROM $table
EOS;

        return $wpdb->get_results($sql, ARRAY_A);
    }

    public function render() {
        $data = array();
        if (null !== $this->notice) $data['notice'] = $this->notice;
        $data['advertisers'] = $this->loadAdvertisers();
        $data['advertisements'] = $this->loadAdvertisements();

        Admin\View::render('advertisements', $data);
    }
}
