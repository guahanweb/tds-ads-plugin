<?php
namespace TDS\Ads;
use GW;

if (!class_exists('\TDS\Ads\Plugin')):

define('TDS_ADS_PLUGIN_NAME', '\TDS\Ads\Plugin');

class Plugin {
    static public function instance($base = null) {
        static $instance;
        if (null === $instance) {
            $instance = new Plugin();
            $instance->configure($base);
            $instance->listen();
            $instance->modules();
        }
        return $instance;
    }

    public function configure($base) {
        global $wpdb;

        if (null === $base) {
            $base = __FILE__;
        }

        $config = GW\Config::instance(TDS_ADS_PLUGIN_NAME);
        $config->add('domain', 'tds');
        $config->add('min_version', '4.1');

        $config->add('settings_opt', 'tds_ad_plugin_settings');

        $config->add('basename', \plugin_basename(\plugin_dir_path($base) . 'gw-tds-ads.php'));
        $config->add('plugin_file', $base);
        $config->add('plugin_uri', \plugin_dir_url($base));
        $config->add('plugin_path', \plugin_dir_path($base));

        $config->add('dbprefix', $wpdb->prefix . 'tds_');

        $this->config = $config;
    }

    public function install() {
        // Create new DB tables
        Admin\Tables::create();

        // Add any new post types here
        flush_rewrite_rules();
    }

    public function uninstall() {
        flush_rewrite_rules();
    }

    private function listen() {
        \register_activation_hook($this->config->plugin_file, array($this, 'install'));
        \register_deactivation_hook($this->config->plugin_file, array($this, 'uninstall'));
    }

    private function modules() {
        $settings = Admin\Settings::instance();
        $view = Admin\View::instance();
        $api = Admin\API::instance();
        $filters = Admin\Filters::instance();
    }
}

endif;
