<?php
/**
 * Plugin Name: TDS | Ad Campaign Manager
 * Plugin URI: http://www.guahanweb.com
 * Description: Create ad campaigns to inject content within feeds
 * Version: 0.1
 * Tested With: 4.3.1
 * Author: Garth Henson
 * Author URI: http://www.guahanweb.com
 * License: GPLv2 or later
 * Text Domain: tds
 * Domain Path: /languages
 */

use TDS\Ads;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/autoload.php';
$plugin = Ads\Plugin::instance(__FILE__);

