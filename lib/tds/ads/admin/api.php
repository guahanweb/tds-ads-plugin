<?php
namespace TDS\Ads\Admin;

use GW;

class API {
    private $actions;

    static public function instance() {
        static $instance;

        if (null === $instance) {
            $instance = new API();
            $instance->register();
        }

        return $instance;
    }

    private function __construct() {
        $this->actions = array(
            'add_advertiser',
            'add_advertisement'
        );
    }

    private function register() {
        foreach ($this->actions as $action) {
            add_action('wp_ajax_' . $action, array($this, 'handle_' . $action));
        }
    }

    private function reply($data) {
        echo json_encode($data);
        wp_die();
    }

    // API handlers
    public function handle_add_advertiser() {
        global $wpdb;
        $this->reply(array('hello' => 'world'));
    }

    public function handle_add_advertisement() {
        global $wpdb;
        $this->reply(array('foo' => 'bar'));
    }
}
