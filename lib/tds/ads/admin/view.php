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
        $supported = array('settings_page_tds-ads-config');
        if (in_array($hook_suffix, $supported)) {
            wp_register_style('tds_ads.css', $this->config->plugin_uri . 'assets/css/main.css', array(), $this->config->version);
            wp_enqueue_style('tds_ads.css');
        }
    }

    private function listen() {
        \add_action('admin_enqueue_scripts', array($this, 'loadResources'));
    }
}
