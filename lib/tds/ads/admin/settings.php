<?php
namespace TDS\Ads\Admin;

use GW;

class Settings {
    static public function instance() {
        static $instance;
        if (null === $instance) {
            $instance = new Settings();
            $instance->configure();
            $instance->defaults();
            $instance->listen();
        }
        return $instance;
    }

    public function configure() {
        $config = GW\Config::instance(TDS_ADS_PLUGIN_NAME);
        $this->config = $config;
    }

    public function defaults() {
        if (\get_option($this->config->settings_opt) === false) {
            $opts = array('foo' => 'bar');
            \add_option($this->config->settings_opt, $opts);
        }
    }

    public function addPluginSettingsLink($links) {
        $settings = sprintf('<a href="%s">%s</a>', esc_url($this->getPageUrl()), __('Settings', $this->config->domain));
        array_unshift($links, $settings);
        return $links;
    }

    public function getPageUrl($page = 'config') {
        $args = array('page' => 'tds-ads-config');
        // If custom page is needed, modify query params here
        $url = \add_query_arg($args, \admin_url('options-general.php'));
        return $url;
    }

    public function setupAdminMenu() {
        $this->addOptionsPage();
    }

    public function adminHelp() {
        $current_screen = \get_current_screen();
        if (\current_user_can('manage_options')) {
            $current_screen->add_help_tab(
                array(
                    'id' => 'overview',
                    'title' => __('Overview', $this->config->domain),
                    'content' => 
                        '<p><strong>' . esc_html__('TDS Ads Setup', $this->config->domain) . '</strong></p>' .
                        '<p>' . esc_html__('Create advertisers, ads and campaigns to manage display within the individual custom feeds of the site.', $this->config->domain) . '</p>' .
                        '<p>' . esc_html__('On this page, you are able to configure the TDS Ads plugin.', $this->config->domain) . '</p>'
                )
            );

            $current_screen->set_help_sidebar(
                '<p><strong>' . esc_html__('For more information:', $this->config->domain) . '</strong></p>' .
                '<p>Contact Mac Slavo :)</p>'
            );
        }
    }

    public function renderPage() {
        if (isset($_GET['view']) && $_GET['view'] == 'foobar') {
            echo 'foobar';
        } else {
            $this->renderOptionsPage();
        }
    }

    public function listen() {
        // Actions
        \add_action('admin_menu', array($this, 'setupAdminMenu'), 5);

        // Filters
        \add_filter('plugin_action_links_' . $this->config->basename, array($this, 'addPluginSettingsLink'));
    }

    private function addOptionsPage() {
        $hook = add_options_page(__('TDS Ads', $this->config->domain), __('TDS Ads', $this->config->domain), 'manage_options', 'tds-ads-config', array($this, 'renderPage'));
        if (version_compare($GLOBALS['wp_version'], '3.3', '>=')) {
            add_action("load-$hook", array($this, 'adminHelp'));
        }
    }

    private function renderOptionsPage() {
        View::render('config', array(
        ));
    }
}
