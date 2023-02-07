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
    public function getTimeZone(){
        $tz = \get_option('rbtgc_timezone');
        return $tz ? $tz : "Etc/GMT";
    }

    public function getCalendarId( $name ){
        #Alias for checkForCalendar - which returns ID
        return self::checkForCalendar( $name );
    }

    public function getCalendarName( $id ){
        $calendarList = $this->calendar->calendarList->listCalendarList();
        foreach ($calendarList->getItems() as $calendarListEntry) {
            if($calendarListEntry->getId() === $id){
                return $calendarListEntry->getSummary();
            }
        }
        return false;
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
    public function newEvent($StartTimeString = null, $DurationInMinutes = null, $CalendarId = null, $arguments = array() ){
                if(!isset($arguments['Name'])) return false;
                if(!isset($arguments['Location'])) return false;
                if(!isset($arguments['ClientEmail'])) return false;
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
}