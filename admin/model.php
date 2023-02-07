<?php 
namespace rbtgc\admin;
use \rbtGoogleCalendar as Plugin;

class Model extends Plugin {
    //The Model class is for getting and setting options information in the wordpress wp_options table
    const MAXCALENDARS = 4;

    public static function save_calendar( $id, $name ){
        $index = self::next_free_cal_number();
        \update_option('rbtgc_calendar_' . $index, 'Connected');
        \update_option('rbtgc_calendar_' . $index . '_id', $id);
        \update_option('rbtgc_calendar_' . $index . '_name', $name);
    }
    public static function remove_calendar_by_id( $id ){
        $records = self::get_calendar_by_id( $id );
        \update_option('rbtgc_calendar_' . $index, 'Connected');
        \update_option('rbtgc_calendar_' . $index . '_id', $id);
        \update_option('rbtgc_calendar_' . $index . '_name', $name);
    }
    public static function get_calendar_by_id( $id ){
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}options WHERE option_value = %s", $id) );
    }
    public static function get_calendar_by_name( $name ){
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}options WHERE option_value = %s", $id) );
    }
    public static function get_calendars(){
        global $wpdb;
        $option_name = 'rbtc_calendar_';
        $results = $wpdb->get_results( 
                $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}options WHERE option_name LIKE =%s", $wpdb->esc_like($option_name  . '%%') )
        );
        error_log("RESULTS");
        error_log(print_r($results, true));
        return $results;
    }

    public static function next_free_cal_number(){

        for($i = 1;$i<self::MAXCALENDARS;$i++){
            $check = \get_option('rbtgc_calendar_' . $i);
            if(!$check) return $i;
        }
        return false;
    }
    public static function sequence_calendars(){
        //re-sorts the calendars into sequential order and returns the next number
        //if 4 calendars are already in use returns false
        $max = 4;
        $queue = array();
        $total = 0;
        for($i = 1;$i<$max;$i++){
            #check sequentially for a linked calendar
            $check = \get_option('rbtgc_calendar_' . $i);
            if($check){
                $total++;
                #check if there is a smaller number in queue
                if($queue[0] < $i){
                    \update_option('rbtgc_calendar_' . $queue[0], $check);
                    \delete_option('rbtgc_calendar_' . $i);
                    array_shift($queue);
                    array_push($i);
                }
            } else {
                array_push($i);
            }   
        }
        return ($total + 1 <= $max) ? $total + 1 : false;
    }

}