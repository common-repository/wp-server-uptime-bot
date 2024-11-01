<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Declare main class
 */
if ( ! class_exists('THEMEQX_WPSUB_ADMIN_MENU')){
    class THEMEQX_WPSUB_ADMIN_MENU{
        /**
         * @var null
         */
        protected static $_instance = null;


        /**
         * @return null|THEMEQX_WPSUB_ADMIN_MENU
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
            add_action('admin_menu', array($this, 'themeqx_wpsub_admin_menu'));
        }


        /**
         * Main dashboard
         */
        public function themeqx_wpsub_dashboard(){
            include WPSUB_DIR.'view/themeqx_wpsub_admin.php';
        }

        public function themeqx_wpsub_admin_menu(){
            add_menu_page(__('WP Server UpTime Bot'), __('WP Server UpTime Bot'),'manage_options','wp-server-uptime-bot', array($this, 'themeqx_wpsub_dashboard'), 'dashicons-visibility' );
        }

    }
}


/**
 * Initialize Main Class
 */
THEMEQX_WPSUB_ADMIN_MENU::instance();
