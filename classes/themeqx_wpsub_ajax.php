<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Declare ajax class
 */
if ( ! class_exists('THEMEQX_WPSUB_AJAX')){
    class THEMEQX_WPSUB_AJAX{
        /**
         * @var null
         */
        protected static $_instance = null;


        /**
         * @return null|THEMEQX_WPSUB_AJAX
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
            add_action('wp_ajax_wpsub_add_lookup', array($this, 'themeqx_wpsub_add_lookup'));
            add_action('wp_ajax_wpsub_lookup_delete', array($this, 'themeqx_wpsub_lookup_delete'));
            add_action('wp_ajax_wpsub_load_lookup_items', array($this, 'themeqx_wpsub_load_lookup_items'));

            //Fire cron
            add_action('themeqx_wpsub_lookup_event', array($this, 'themeqx_wpsub_lookup_event'));

        }

        /**
         * Ajax add lookup
         */
        public function themeqx_wpsub_add_lookup(){
            global $wpdb;
            $wpsub_add_lookup_name = sanitize_text_field($_POST['wpsub_add_lookup_name']);
            $wpsub_add_lookup_url = esc_url(sanitize_text_field($_POST['wpsub_add_lookup_url']));
            $mysql_current_type =  current_time( 'mysql' );

            $insert_lookup = $wpdb->insert(
                $wpdb->prefix."wpsub_lookup_item",
                array(
                    'lookup_name'   => $wpsub_add_lookup_name,
                    'lookup_url'    => $wpsub_add_lookup_url,
                    'created_at'    => $mysql_current_type,
                    'status'        => 1
                )
            );
            if ($insert_lookup){
                die(json_encode(array('status' => 1, 'msg' => __('Lookup item has been added', 'wp-server-uptime-bot') )));
            }
            die(json_encode(array('status' => 0, 'msg' => __('Something went wrong, please try again', 'wp-server-uptime-bot'))));
        }

        /**
         * Delete Lookup Item
         */
        public function themeqx_wpsub_lookup_delete(){
            global $wpdb;
            $lookup_id = (int) (sanitize_text_field($_POST['lookup_id']));
            $wpdb->delete(
                $wpdb->prefix."wpsub_lookup_item",
                array('ID'   => $lookup_id)
            );
            die(json_encode(array('status' => 1, 'msg' => __('Lookup item has been deleted', 'wp-server-uptime-bot') )));
        }

        /**
         * Load lookup items
         */
        public function themeqx_wpsub_load_lookup_items(){
            include WPSUB_DIR.'view/themeqx_ajax_lookup_items.php';
            die();
        }

        /**
         * Fire cron event now
         */

        public function themeqx_wpsub_lookup_event(){
            global $wpdb;
            $mysql_current_type =  current_time( 'mysql' );
            $admin_email = get_option('admin_email');

            $get_all_lookup = $wpdb->get_results("select * from {$wpdb->prefix}wpsub_lookup_item");

            if ($get_all_lookup){

                foreach ($get_all_lookup as $lookup_res){
                    $lookup_url = $lookup_res->lookup_url;

                    $lookup_status_if_exists = $wpdb->get_row("select * from {$wpdb->prefix}wpsub_lookup_status where lookup_item_id = {$lookup_res->ID}  ORDER BY ID DESC limit 1");
                    //Get server response now
                    $response = $this->get_server_response($lookup_url);

                    if ($lookup_status_if_exists){
                        //Server responsed
                        if ($response){

                            if ( $lookup_status_if_exists->status == 'down'){
                                //previous check server was down, now its up
                                $wpdb->update(
                                    $wpdb->prefix."wpsub_lookup_status",
                                    array(
                                        'end_datetime'  => $mysql_current_type,
                                    ),
                                    array('ID' => $lookup_status_if_exists->ID)
                                );
                                $insert_lookup_up = $wpdb->insert(
                                    $wpdb->prefix."wpsub_lookup_status",
                                    array(
                                        'lookup_item_id'    => $lookup_res->ID,
                                        'lookup_item_name'  => $lookup_res->lookup_name,
                                        'status'            => 'up',
                                        'state_datetime'    => $mysql_current_type,
                                        'end_datetime'      => '',
                                        'server_response'   => $response
                                    )
                                );
                                if ($insert_lookup_up){
                                    //Send email now for notification
                                    $subject = $lookup_url.apply_filters('wpsub_server_up_mail_subject', __(' server has been back and running', 'wp-server-uptime-bot') );
                                    $down_time_was = human_time_diff( strtotime($lookup_status_if_exists->state_datetime), time() );
                                    $headers = array('Content-Type: text/html; charset=UTF-8');
                                    ob_start();
                                    include WPSUB_DIR.'view/emails/themeqx_wpsub_email_server_up.php';
                                    $email_body = ob_get_clean();


                                    add_filter( 'wp_mail_from', $from_func = function ( $from_email ) { return get_option('admin_email'); } );
                                    add_filter( 'wp_mail_from_name', $from_name_func = function ( $from_name ) { return get_option('blogname'); } );
                                    wp_mail($admin_email, $subject, $email_body, $headers);
                                    remove_filter( 'wp_mail_from', $from_func );
                                    remove_filter( 'wp_mail_from_name', $from_name_func );
                                }
                            }
                        }else{

                            //die('SErver Down');
                            if ($lookup_status_if_exists->status == 'up'){
                                //

                                //previous check server was up, now its down
                                $wpdb->update(
                                    $wpdb->prefix."wpsub_lookup_status",
                                    array(
                                        'end_datetime'  => $mysql_current_type,
                                    ),
                                    array('ID' => $lookup_status_if_exists->ID)
                                );
                                $insert_lookup_down = $wpdb->insert(
                                    $wpdb->prefix."wpsub_lookup_status",
                                    array(
                                        'lookup_item_id'    => $lookup_res->ID,
                                        'lookup_item_name'  => $lookup_res->lookup_name,
                                        'status'            => 'down',
                                        'state_datetime'    => $mysql_current_type,
                                        'end_datetime'      => '',
                                        'server_response'   => $response
                                    )
                                );
                                if ($insert_lookup_down){
                                    //Send email now for notification
                                    $subject = $lookup_url.apply_filters('wpsub_server_up_mail_subject', __(' server has been Down', 'wp-server-uptime-bot') );
                                    $up_time_was = human_time_diff( strtotime($lookup_status_if_exists->state_datetime), time() );
                                    $headers = array('Content-Type: text/html; charset=UTF-8');
                                    ob_start();
                                    include WPSUB_DIR.'view/emails/themeqx_wpsub_email_server_down.php';
                                    $email_body = ob_get_clean();


                                    add_filter( 'wp_mail_from', $from_func = function ( $from_email ) { return get_option('admin_email'); } );
                                    add_filter( 'wp_mail_from_name', $from_name_func = function ( $from_name ) { return get_option('blogname'); } );
                                    wp_mail($admin_email, $subject, $email_body, $headers);
                                    remove_filter( 'wp_mail_from', $from_func );
                                    remove_filter( 'wp_mail_from_name', $from_name_func );
                                }

                            }else{
                                //Till now server is down
                            }


                        }

                    } else{

                        //There is no inserted item for this lookup, now insert
                        $response = $this->get_server_response($lookup_url);
                        //print_r($response);
                        if ($response) {
                            //Server is OK
                            $insert_lookup = $wpdb->insert(
                                $wpdb->prefix."wpsub_lookup_status",
                                array(
                                    'lookup_item_id'    => $lookup_res->ID,
                                    'lookup_item_name'    => $lookup_res->lookup_name,
                                    'status'            => 'up',
                                    'state_datetime'    => $mysql_current_type,
                                    'end_datetime'      => '',
                                    'server_response'   => $response
                                )
                            );
                        }else{

                            $insert_lookup = $wpdb->insert(
                                $wpdb->prefix."wpsub_lookup_status",
                                array(
                                    'lookup_item_id'    => $lookup_res->ID,
                                    'lookup_item_name'    => $lookup_res->lookup_name,
                                    'status'            => 'down',
                                    'state_datetime'    => $mysql_current_type,
                                    'end_datetime'      => '',
                                    'server_response'   => $response
                                )
                            );

                        }

                    }


                }
            }


        }

        public function get_server_response($lookup_url = ''){
            //Send Curl Request
            $cl = curl_init($lookup_url);
            curl_setopt($cl,CURLOPT_CONNECTTIMEOUT,10);
            curl_setopt($cl,CURLOPT_HEADER,true);
            curl_setopt($cl,CURLOPT_NOBODY,true);
            curl_setopt($cl,CURLOPT_RETURNTRANSFER,true);
            //get response
            $response = curl_exec($cl);
            curl_close($cl);
            return $response;
        }



    }
}


/**
 * Initialize The Class
 */
THEMEQX_WPSUB_AJAX::instance();
