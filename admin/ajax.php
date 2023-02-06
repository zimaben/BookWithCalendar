<?php 
namespace rbtgc\admin;
use \rbtGoogleCalendar as Plugin;

class PluginAjax extends Plugin {
    static $required_fields = array(
        'rbtgc_add_calendar' => array('calendar'),
        'rbtgc_remove_calendar' => array('calendar'),
        'rbtgc_set_timezone' => array('timezone'),
    );
    public static function run(){
        \add_action( 'wp_ajax_rbtgc_add_calendar', array(get_class(), 'rbtgc_add_calendar' ));
        \add_action( 'wp_ajax_rbtgc_remove_calendar', array(get_class(), 'rbtgc_remove_calendar' ));
        \add_action( 'wp_ajax_rbtgc_set_timezone', array(get_class(), 'rbtgc_set_timezone' ));
        \add_action( 'wp_ajax_rbtgc_test_features', array(get_class(), 'rbtgc_test_features' ));
    }
    private static function return_false( $message = 'There was a problem', $code = 400 ){
        echo json_encode(array('status' => $code, 'payload' => $message ));
        die();
    }
    private static function return_true( $message = 'Success', $code = 200 ){
        echo json_encode(array('status' => $code, 'payload' => $message ));
        die();
    }
    private static function checkrequest( $function ){
        if(!isset($_POST['nonce'])) self::return_false( 'Missing nonce' );
        $required = self::$required_fields[$function];
        if($required){
            foreach($required as $required){
                if(!isset($_POST[$required])) self::return_false( 'Missing required field');
            }
        }
        
        if( ! wp_verify_nonce( $_POST['nonce'], 'admin_js' ) )  self::return_false( 'Invalid nonce' );

        return true; 
    }

    public static function test_features(){
        
    }

    public static function  rbtgc_add_calendar(){
        /* boilerplate */
        $check = self::checkrequest( __FUNCTION__ );
        require_once Plugin::get_plugin_path() . 'sdk/Client.php';
        $Client = new \CalendarClient();
        if($Client->err) self::return_false( $Client->err );
        require_once Plugin::get_plugin_path() . 'sdk/Calendar.php';
        $Cal = new \GCalendar($Client->client, $Client->calendar);
        /* boilerplate end */

        if($Cal->client->getAccessToken()) {     
            $CalendarID = $Cal->createNewCalendar( $_POST['calendar'] );
            if($CalendarID) {
                self::return_true( 'Created or retrieved calendar ID:'.$CalendarID .' - '. $_POST['calendar']);
            }
            self::return_false('Something may have went wrong creating the calendar. Check your Google Calendar Application');

        } else {
            // Set the refresh token on the client.	
		    if (isset($_SESSION['refresh_token']) && $_SESSION['refresh_token']) {
			    $this->client->refreshToken($_SESSION['refresh_token']);
		    }
            if($Cal->client->getAccessToken()){

            } else {
                self::return_false( 'Problem generating Access Token with those Credentials' );
            }
        }
    }
    
    public static function  rbtgc_remove_calendar(){
        /* boilerplate */
        $check = self::checkrequest( __FUNCTION__ );
        require_once Plugin::get_plugin_path() . 'sdk/Client.php';
        $Client = new \CalendarClient();
        if($Client->err) self::return_false( $Client->err );
        require_once Plugin::get_plugin_path() . 'sdk/Calendar.php';
        $Cal = new \GCalendar($Client->client, $Client->calendar);
        
        /* boilerplate end */
    }
    public static function rbtgc_set_timezone(){

        $check = self::checkrequest( __FUNCTION__ );
        $newTZ = $_POST['timezone'];
        if( \update_option('rbtgc_timezone', $newTZ) ){
            self::return_true( 'Timezone changed' ); 
        } else {
            self::return_false( 'Something went wrong with the timezone request');
        }

    }

}
PluginAjax::run();