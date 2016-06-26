<?php
namespace TDS\Ads\Admin;

use GW;

class View {
    static public function render($name, array $args = array()) {
        $args = apply_filters('tds_ads_view_arguments', $args, $name);

        $config = GW\Config::instance(TDS_ADS_PLUGIN_NAME);
        foreach ($args as $key => $val) {
            $$key = $val;
        }

        \load_plugin_textdomain($config->domain);

        $file = $config->plugin_path . 'views/' . $name . '.php';
        include $file;
    }

    static public function instance() {
        static $instance;
        if (null === $instance) {
            $instance = new View();
            $config = GW\Config::instance(TDS_ADS_PLUGIN_NAME);
            $instance->config = $config;

            $instance->listen();
        }
        return $instance;
    }

    public function loadResources() {
        global $hook_suffix;
        if (preg_match('/page_tds-ads/', $hook_suffix)) {
            wp_register_style('font-awesome', $this->config->plugin_uri . 'assets/font-awesome/css/font-awesome.min.css', array(), $this->config->version);
            wp_register_style('tds_ads.css', $this->config->plugin_uri . 'assets/css/main.css', array('font-awesome'), $this->config->version);
            wp_enqueue_style('tds_ads.css');

            wp_register_script('tds_admin.js', $this->config->plugin_uri . 'assets/js/admin.js', array(), $this->config->version);
            wp_enqueue_script('tds_admin.js');
        }
    }

    private function listen() {
        add_action('admin_enqueue_scripts', array($this, 'loadResources'));
    }
}
