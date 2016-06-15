<?php
namespace TDS\Ads\Admin;

use GW;

class Tables {
    static public function create() {
        global $wpdb;
        require_once ABSPATH . '/wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();
        $config = GW\Config::instance(TDS_ADS_PLUGIN_NAME);

        $tables = array(
            'advertisers',
            'campaigns',
            'ads',
            'campaign_ads',
            'views',
            'view_slots'
        );

        $createSql = '';
        foreach ($tables as $table) {
            $file = $config->plugin_path . 'assets/sql/' . $table . '.sql';
            $prefix = $wpdb->prefix . 'tds_';
            $sql = str_replace(
                array('{{TABLE}}', '{{PREFIX}}', '{{CHARSET}}'),
                array($prefix . $table, $prefix, $charset_collate),
                file_get_contents($file)
            );
            $createSql .= $sql;
        }
        $res = \dbDelta($createSql);
    }
}
