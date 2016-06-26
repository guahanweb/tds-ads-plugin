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
        // $this->addOptionsPage();
        $this->addPluginMenu();
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

            $current_screen->add_help_tab(
                array(
                    'id' => 'advertisements',
                    'title' => __('Advertisements', $this->config->domain),
                    'content' => 
                        '<p><strong>' . esc_html__('TDS Ads :: Advertisements', $this->config->domain) . '</strong></p>' .
                        '<p>' . esc_html__('The advertisements section will allow you to modify both advertisers and individual advertisements that can be referenced from within campaigns.', $this->config->domain) . '</p>'
                )
            );

            $current_screen->add_help_tab(
                array(
                    'id' => 'campaigns',
                    'title' => __('Campaigns', $this->config->domain),
                    'content' => 
                        '<p><strong>' . esc_html__('Campaigns', $this->config->domain) . '</strong></p>' .
                        '<p>' . esc_html__('Campaign creation will provide hooks that will inject advertisements into a list of posts on the site.', $this->config->domain) . '</p>' .
                        '<p>' . esc_html__('This page allows you to configure your campaign definition and display rules for those hooks.', $this->config->domain) . '</p>'
                )
            );

            $current_screen->set_help_sidebar(
                '<p><strong>' . esc_html__('For more information:', $this->config->domain) . '</strong></p>' .
                '<p>Contact Mac Slavo :)</p>'
            );
        }
    }

    public function renderDashboard() {
        $page = new Pages\Dashboard();
        $page->process();
        $page->render();
    }

    public function renderAdsPage() {
        $page = new Pages\Advertisements();
        $page->process();
        $page->render();
    }

    public function renderCampaignsPage() {
        $page = new Pages\Campaigns();
        $page->process();
        $page->render();
    }

    public function listen() {
        add_action('admin_menu', array($this, 'setupAdminMenu'), 5);
        add_filter('plugin_action_links_' . $this->config->basename, array($this, 'addPluginSettingsLink'));
    }

    private function addPluginMenu() {
        $hooks = array(
            add_menu_page(__('TDS Ads', $this->config->domain), __('TDS Ads', $this->config->domain), 'manage_options', 'tds-ads-plugin-home', array($this, 'renderDashboard'), 'dashicons-money', 30),
            add_submenu_page('tds-ads-plugin-home', __('Dashboard', $this->config->domain), __('Dashboard', $this->config->domain), 'manage_options', 'tds-ads-plugin-home', array($this, 'renderDashboard')),
            add_submenu_page('tds-ads-plugin-home', __('Advertisements', $this->config->domain), __('Advertisements', $this->config->domain), 'manage_options', 'tds-ads-plugin-advertisements', array($this, 'renderAdsPage')),
            add_submenu_page('tds-ads-plugin-home', __('Campaigns', $this->config->domain), __('Campaigns', $this->config->domain), 'manage_options', 'tds-ads-plugin-campaigns', array($this, 'renderCampaignsPage'))
        );

        if (version_compare($GLOBALS['wp_version'], '3.3', '>=')) {
            foreach ($hooks as $hook) {
                add_action("load-$hook", array($this, 'adminHelp'));
            }
        }

    }

    private function renderOptionsPage() {
        View::render('config', array(
        ));
    }
}
