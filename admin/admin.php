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


    
}
PluginAdmin::run();