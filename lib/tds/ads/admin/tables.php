<?php
namespace TDS\Ads\Admin;

use GW;

class Tables {
    /**
     * This will load in all our SQL definition files and create the necessary tables
     * within the WordPress databse. This will also manage upgrades for us in future
     * versions.
     */
    static public function create() {
        global $wpdb;

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
            $sql = str_replace(
                array('{{TABLE}}', '{{PREFIX}}', '{{CHARSET}}'),
                array($prefix . $table, $config->dbprefix, $charset_collate),
                file_get_contents($file)
            );
            $createSql .= $sql;
        }

        require_once ABSPATH . '/wp-admin/includes/upgrade.php';
        $res = \dbDelta($createSql);
    }
}
