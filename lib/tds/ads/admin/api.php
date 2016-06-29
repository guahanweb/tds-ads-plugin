<?php
namespace TDS\Ads\Admin;

use GW;

class API {
    private $actions;

    static public function instance() {
        static $instance;

        if (null === $instance) {
            $instance = new API();
            $instance->config = GW\Config::instance(TDS_ADS_PLUGIN_NAME);
            $instance->register();
        }

        return $instance;
    }

    private function __construct() {
        $this->actions = array(
            'load_advertisement'
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
    public function handle_load_advertisement() {
        global $wpdb;

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        $table = $this->config->dbprefix . 'ads';
        $sql = <<<EOQ
SELECT * FROM %s
WHERE id = %d
LIMIT 1
EOQ;

        $data = $wpdb->get_results(sprintf($sql, $table, $id), ARRAY_A);
        if (count($data) === 1) {
            $this->reply($data[0]);
        } else {
            $this->reply(false);
        }
    }
}
