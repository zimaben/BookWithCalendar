<?php
use rbtGoogleCalendar as Plugin;
/* RUNNING TALLY OF SCOPES NEEDED 
https://www.googleapis.com/auth/calendar.readonly
https://www.googleapis.com/auth/calendar

*/

class GCalendar {

    public function __construct( Object $Client = null, Object $Calendar = null){
        $this->client = $Client;
        $this->calendar = $Calendar;
        $this->timezone = $this->getTimeZone();
        if(!$this->client || !$this->calendar) $this->createNewClient();
    }
    private function getTimeZone(){
        $tz = \get_option('rbtgc_timezone');
        error_log("TIMEZONE: " . $tz);

        return $tz ? $tz : "Etc/GMT";
    }
    public function createNewClient(){
        require_once Plugin::get_plugin_path() . 'sdk/Client.php';
        $Client = new \CalendarClient;
        $this->client = $Client->client;
        $this->calendar = $Client->calendar;
    }
    public function checkForCalendar( $name ){
        $calendarList = $this->calendar->calendarList->listCalendarList();
        foreach ($calendarList->getItems() as $calendarListEntry) {
            if(trim(strtoupper($name)) === trim(strtoupper( $calendarListEntry->getSummary() )) ){
                return $calendarListEntry->getId();
            };
        }
        return false;
    }
    public function createNewCalendar( String $name ){
        $exists = $this->checkForCalendar( $name );
        if($exists) return $exists;

        error_log($name . " doesn't exist yet" );
        $calendar = new Google_Service_Calendar_Calendar();
        $calendar->setSummary($name);
        $calendar->setTimeZone($this->timezone);
        error_log(print_r($calendar, true));
        $createdCalendar = $this->calendar->calendars->insert($calendar);
        return $createdCalendar->getId();
    }
}