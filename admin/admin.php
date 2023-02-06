<?php 
namespace rbtgc\admin;
use \rbtGoogleCalendar as Plugin;

class PluginAdmin extends Plugin {
    public static function run(){
        #options page
        require_once self::get_plugin_path() . 'admin/plugin_options.php';
        require_once self::get_plugin_path() . 'admin/ajax.php';
        \add_action( 'admin_enqueue_scripts', array(get_class(), 'admin_script_variables'));
    }
    public static function admin_script_variables() {
        \wp_enqueue_script( 'rbtgc_admin', Plugin::get_plugin_url() .'/admin/js/admin.js', false );
        \wp_enqueue_script( 'rbtgc_admin_foot', Plugin::get_plugin_url() .'/admin/js/adminfoot.js', true );
          $data = array(
              'nonce' => \wp_create_nonce('admin_js'),
              'ajaxurl' => \admin_url('admin-ajax.php')
          );
        \wp_localize_script( 'rbtgc_admin', 'rbtgc', $data );
      
    }
    public static function get_calendars(){
        global $wpdb;
        $option_name = 'rbtc_calendar_%';
        $results = $wpdb->get_results( 
                $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}options WHERE option_name LIKE =%s", $option_name )
        );
        error_log("RESULTS");
        error_log(print_r($results, true));
        return $results;
    }
    public static function get_next_cal_number(){
        $max = 4;
        $gaps = array();
        $unset = array();
        $count = 0;
        for($i = 1;$i<$max;$i++){
            $check = \get_option('rbtgc_calendar_' . $i);
            if($check){
                $count++;
            } else {
                array_push($unset, $i);
            }
            
        }
    }
    
}
PluginAdmin::run();