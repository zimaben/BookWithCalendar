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
        \add_action( 'wp_ajax_rbtgc_link_calendar', array(get_class(), 'rbtgc_link_calendar'));
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
        $required = isset(self::$required_fields[$function]);
        if($required){
            foreach(self::$required_fields[$function] as $required){
                if(!isset($_POST[$required])) self::return_false( 'Missing required field');
            }
        }
        
        if( ! wp_verify_nonce( $_POST['nonce'], 'admin_js' ) )  self::return_false( 'Invalid nonce' );

        return true; 
    }
   # m55qqs0bi4or2iu8anfov3kuko@group.calendar.google.com
    public static function rbtgc_test_features(){
        /* boilerplate */
        $check = self::checkrequest( __FUNCTION__ );
        require_once Plugin::get_plugin_path() . 'sdk/Client.php';
        $Client = new \CalendarClient();
        if($Client->err) self::return_false( $Client->err );
        require_once Plugin::get_plugin_path() . 'sdk/Calendar.php';
        $Cal = new \GCalendar($Client->client, $Client->calendar);
        /* boilerplate end */
        require_once Plugin::get_plugin_path() . 'admin/model.php';

        #4 hour version
        $startTime = new \DateTimeImmutable('2023-02-08 08:00');
        $endTime = $startTime->add( new \DateInterval("PT4H"));
        $TimeZone = $Cal->getTimeZone();
        $CalId = 'm55qqs0bi4or2iu8anfov3kuko@group.calendar.google.com';
        $newEvent = array(
            'summary' => 'Half Day Driver Appointment',
            'description' => 'Any Additional Description goes here',
            'location' => 'Ngurah Rai Airport',
            'start' => array('dateTime'=> $startTime->format(\DateTime::ATOM), 'timeZone'=> $TimeZone),
            'end' => array('dateTime'=> $endTime->format(\DateTime::ATOM), 'timeZone'=> $TimeZone),
            'attendees' => array(
                array('email' => 'lpage@example.com'),
                array('email' => 'sbrin@example.com'),
              ),
            'reminders' => array('useDefault' => TRUE),
        );
        #Add Event
        $event = new \Google_Service_Calendar_Event( $newEvent );
        $addevent = $Cal->calendar->events->insert($CalId, $event);
    }

    public static function rbtgc_add_calendar(){
        /* boilerplate */
        $check = self::checkrequest( __FUNCTION__ );
        require_once Plugin::get_plugin_path() . 'admin/model.php';
        $nextnum = Model::next_free_cal_number();
        if(!$nextnum) self::return_false('Cannot add more than 4 calendars');

        require_once Plugin::get_plugin_path() . 'sdk/Client.php';
        $Client = new \CalendarClient();
        if($Client->err) self::return_false( $Client->err );
        require_once Plugin::get_plugin_path() . 'sdk/Calendar.php';
        $Cal = new \GCalendar($Client->client, $Client->calendar);
        /* boilerplate end */
        
        
        $CalendarID = $Cal->createNewCalendar( $_POST['calendar'] );
        if($CalendarID) {
            Model::save_calendar( $CalendarID, $_POST['calendar'] );
            self::return_true( 'Created or retrieved calendar ID:'.$CalendarID .' - '. $_POST['calendar']);
        }
        self::return_false('Something may have went wrong creating the calendar. Check your Google Calendar Application');


    }

    public static function rbtgc_link_calendar(){
        error_log("DOING LINK CALENDAR SERVER");
        /* boilerplate */
        $check = self::checkrequest( __FUNCTION__ );
        require_once Plugin::get_plugin_path() . 'admin/model.php';
        $nextnum = Model::next_free_cal_number();
        if(!$nextnum) self::return_false('Cannot add more than 4 calendars');

        require_once Plugin::get_plugin_path() . 'sdk/Client.php';
        $Client = new \CalendarClient();
        if($Client->err) self::return_false( $Client->err );
        require_once Plugin::get_plugin_path() . 'sdk/Calendar.php';
        $Cal = new \GCalendar($Client->client, $Client->calendar);
        /* boilerplate end */
        
        $CalendarName = $Cal->getCalendarName( $_POST['calendar'] );
        if($CalendarName) {
            error_log("GOT NAME " . $CalendarName);
            Model::save_calendar( $_POST['calendar'], $CalendarName );
            self::return_true( 'Successfully linked calendar '.$CalendarName );
        }
        self::return_false('Something may have went wrong linking the calendar. Check your Google Calendar Application');

    }
    
    public static function  rbtgc_remove_calendar(){
        /* boilerplate */
        $check = self::checkrequest( __FUNCTION__ );
        require_once Plugin::get_plugin_path() . 'admin/model.php';
        $Row = Model::get_calendar_by_id( $_POST['calendar'] );
        $Match = $Row ? $Row->option_name : false; //Ex: rbtgc_calendar_2_id
        $Calendar = $Match ? str_replace('_id', '', $Match) : false; //Ex: rbtgc_calendar
        $Name = $Calendar ? $Calendar . '_name' : false;
        if( $Match && $Calendar && $Name ){
            \delete_option( $Match );
            \delete_option( $Calendar );
            \delete_option( $Name );
            self::return_true("Successfully removed calendar " .$_POST['calendar'] . " from the application");
        } else {
            self::return_false("Could not remove calendar " .$_POST['calendar'] . " from the application");
        }
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