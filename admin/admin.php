<?php 
namespace rbtgc\admin;
use \rbtGoogleCalendar as Plugin;

class PluginAdmin extends Plugin {
    public static function run(){
        #options page
        require_once self::get_plugin_path() . 'admin/plugin_options.php';

    }

}
PluginAdmin::run();