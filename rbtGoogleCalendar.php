<?php

/* 
 * Google Calendar Website Integration 
 *
 * @package         rbtGoogleCalendar
 * @author          friendly Robot
 * @license         @ToDo
 * @link            https://google.com/calendar
 *
 * @wordpress-plugin
 * Plugin Name:     Google Calendar Website Integration
 * Plugin URI:      https://google.com/calendar
 * Description:     Offers a simple, class-based approach to link and authenticate Google Projects.
 * Version:         1.0.1
 * Author:          friendlyRobot
 * Author URI:      https://ben-toth.com/
 * License:         @ToDo
 * Copyright:       friendlyRobot
 * Class:           rbtGoogleCalendar
 * Text Domain:     rbtgc
 * GitHub Plugin URI: https://github.com/zimaben/psyML_starter
*/

defined( 'ABSPATH' ) OR exit;

if ( ! class_exists( 'rbtGoogleCalendar' ) ) {

    register_activation_hook( __FILE__, array ( 'rbtGoogleCalendar', 'register_activation_hook' ) );    
    add_action( 'plugins_loaded', array ( 'rbtGoogleCalendar', 'get_instance' ), 5 );
    
    class rbtGoogleCalendar {
 
        private static $instance = null;

        // Plugin Settings
        const version = '1.0.1';
        static $debug = true; //turns PHP and javascript logging on/off
        const text_domain = 'rbtgc'; // for translation & namespacing ##
        const nice_name = 'Google Calendar Integration'; //should match text_domain except capitalization & whitespace

        //Plugin Options

        /**
         * Returns a singleton instance
         */
        public static function get_instance() 
        {

            if ( 
                null == self::$instance 
            ) {

                self::$instance = new self;

            }

            return self::$instance;

        }
        
        private function __construct() {

            // actvation ##
            \register_activation_hook( __FILE__, array ( get_class(), 'register_activation_hook' ) );

            // deactvation ##
            \register_deactivation_hook( __FILE__, array ( get_class(), 'register_deactivation_hook' ) );

            // set text domain ##
            \add_action( 'init', array( get_class(), 'load_plugin_textdomain' ), 1 );

            #execute deactivation options
            \add_action( 'wp_ajax_deactivate', array( get_class(), 'deactivate_callback') );

            // load libraries ##
            self::load_libraries();

            // enqueue scripts & styles


        }
        
        private static function load_libraries() {

            //Get/Set Keys and Scopes
            if( \is_admin()){
                require_once self::get_plugin_path() . 'admin/admin.php';
            } else {
                require_once self::get_plugin_path() . 'theme/theme.php';
            }

        }

        /* UTILITY FUNCTIONS */

        public static function register_activation_hook() {

            $option = self::text_domain . '-version';
            \update_option( $option, self::version ); 
            #add psyML pages on first run
            \update_option( 'auto_add_psyml', "yes" ); 
                
        }

        public static function register_deactivation_hook() {
            
            #@Todo need to figure out how to set flag for uninstall.php to read without
            #another page load (since plugin dies after deactivation)
        }

        public static function load_plugin_textdomain() 
        {
            
            // set text-domain ##
            $domain = self::text_domain;
            
            // The "plugin_locale" filter is also used in load_plugin_textdomain()
            $locale = apply_filters('plugin_locale', get_locale(), $domain);

            // try from global WP location first ##
            load_textdomain( $domain, WP_LANG_DIR.'/plugins/'.$domain.'-'.$locale.'.mo' );
            
            // try from plugin last ##
            load_plugin_textdomain( $domain, FALSE, plugin_dir_path( __FILE__ ).'library/language/' );
            
        }

        public static function get_plugin_url( $path = '' ) 
        {

            return plugins_url( $path, __FILE__ );

        }
        
        public static function get_plugin_path( $path = '' ) 
        {

            return plugin_dir_path( __FILE__ ).$path;

        }

    }

}