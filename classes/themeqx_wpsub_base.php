<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Declare main class
 */
if ( ! class_exists('THEMEQX_WPSUB_BASE')){
    class THEMEQX_WPSUB_BASE{
        /**
         * @var null
         */
        protected static $_instance = null;


        /**
         * @return null|THEMEQX_WPSUB_BASE
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * THEMEQX_WPSUB_BASE constructor.
         */
        public function __construct(){
            add_action('admin_enqueue_scripts', array($this, 'themeqx_wpsub_load_script'));

            //Set a minute once cron
            add_filter( 'cron_schedules', array($this, 'themeqx_wpsub_cron_add_minute') );
        }

        /**
         * Setup some data during plugin activate
         */
        public function themeqx_wpsub_initial_setup(){
            //Setting version number during firstime activate
            if (! get_option('themeqx_wpsub_version')){
                global $wpdb;
                //Set this plugin version
                update_option('themeqx_wpsub_version', WPSUB_VERSION);

                //Import database structure, for the first time
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                //Query database
                $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpsub_lookup_item` (
                  `ID` int(11) NOT NULL AUTO_INCREMENT,
                  `lookup_name` varchar(250) NOT NULL,
                  `lookup_url` text NOT NULL,
                  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  `status` int(11) NOT NULL DEFAULT '1',
                  PRIMARY KEY (`ID`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
                dbDelta( $sql );

                //Create assigned table
                $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpsub_lookup_status` (
                  `ID` int(11) NOT NULL AUTO_INCREMENT,
                  `lookup_item_id` int(11) NOT NULL,
                  `lookup_item_name` varchar(255) NOT NULL,
                  `status` varchar(20) NOT NULL,
                  `state_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  `end_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  `is_sent_email` int(11) DEFAULT NULL,
                  `server_response` text NOT NULL,
                  PRIMARY KEY (`ID`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;";
                dbDelta($sql);
            }

            /**
             * Setup Cron job
             */
            if (! wp_next_scheduled ( 'themeqx_wpsub_lookup_event' )) {
                wp_schedule_event(time(), 'everyminute', 'themeqx_wpsub_lookup_event');
            }

            
        }

        /**
         * Load Themeqx WP Server UpTime Main css and js
         */

        public function themeqx_wpsub_load_script(){
            wp_enqueue_style('themeqx-wpsub-style', WPSUB_URL.'assets/css/wpsub.css', array(),WPSUB_VERSION);
            wp_enqueue_script('themeqx-wpsub-js', WPSUB_URL.'assets/js/wpsub.js', array('jquery'),WPSUB_VERSION, true);
        }

        /**
         * @param $schedules
         * @return mixed
         * Set a custom once per miniute cron
         */
        public function themeqx_wpsub_cron_add_minute($schedules){
            // Adds once every minute to the existing schedules.
            $schedules['everyminute'] = array(
                'interval' => 5,
                'display' => __( 'Once Every Minute', 'wp-server-uptime-bot' )
            );
            return $schedules;
        }

        /**
         * De-active cron job
         */

        public function themeqx_wpsub_deactivation() {
            wp_clear_scheduled_hook('themeqx_wpsub_lookup');
        }

    }
}


/**
 * Initialize Main Class
 */
THEMEQX_WPSUB_BASE::instance();

include WPSUB_DIR.'classes/themeqx_wpsub_admin_menu.php';
include WPSUB_DIR.'classes/themeqx_wpsub_ajax.php';
/**
 * Doing something during plugin activate
 */
register_activation_hook( WPSUB_BASE_FILE, array('THEMEQX_WPSUB_BASE', 'themeqx_wpsub_initial_setup') );
register_deactivation_hook(WPSUB_BASE_FILE, array('THEMEQX_WPSUB_BASE', 'themeqx_wpsub_deactivation') );
