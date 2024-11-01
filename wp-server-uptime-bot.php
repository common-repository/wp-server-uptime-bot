<?php
/*
 * Plugin Name:       WP Server UpTime Bot
 * Plugin URI:        http://themeqx.com
 * Description:       Want too see how much time server is up and when its just goes down, WP Server UpTime bot is a talented system, that can be track and notify when server goes down
 * Version:           1.0
 * Author:            Themeqx
 * Author URI:        http://themeqx.com
 * Text Domain:       wp-server-uptime-bot
 * Domain Path:       /languages
 */

//Constants
define('WPSUB_VERSION', 1.0);
define('WPSUB_DIR', plugin_dir_path(__FILE__));
define('WPSUB_URL', plugin_dir_url(__FILE__));
define('WPSUB_BASE_FILE', __FILE__);

if ( ! defined( 'ABSPATH' ) ) {
    echo "Direct access not allowed";
    exit; // Exit if accessed directly
}

/**
 * Include themeqx_wpsub_base
 */
include WPSUB_DIR.'classes/themeqx_wpsub_base.php';