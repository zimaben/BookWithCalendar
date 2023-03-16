<?php
use rbtGoogleCalendar as Plugin;
use rbtgc\admin\Model as Model;
/* RUNNING TALLY OF SCOPES NEEDED 
https://www.googleapis.com/auth/calendar.readonly
https://www.googleapis.com/auth/calendar

*/

class GCalendar {

    private static $instance = null;

    public static function get_instance( Object $Client = null, Object $Calendar = null){
        #singletons have feelings too
        if(null==self::$instance) self::$instance = new self( $Client, $Calendar);

        return self::$instance;
    }

    private function __construct( Object $Client = null, Object $Calendar = null){
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
        if(!isset($arguments['UserEmail'])) return false;
        $description = isset($arguments['Description']) ? $arguments['Description'] : '';
        $clientEmail = \get_option('rbtgc_client_email');
        #Lets get the timestamps into something Google and PHP can agree on
        $TimeZoneString = $this->getTimeZone();
        
        $startTime = new \DateTime( $StartTimeString, new DateTimeZone($TimeZoneString) );
        $StartStringFinal = $startTime->format( \DateTime::ATOM );

        $startTime->add( new \DateInterval("PT".$DurationInMinutes."M"));  
        $EndStringFinal = $startTime->format( \DateTime::ATOM );

        $newEvent = array(
            'summary' => $arguments['Name'],
            'description' => $description,
            'location' => $arguments['Location'],
            'start' => array('dateTime'=> $StartStringFinal, 'timeZone'=> $TimeZoneString),
            'end' => array('dateTime'=> $EndStringFinal, 'timeZone'=> $TimeZoneString),
            'attendees' => array(
                array('email' => $arguments['UserEmail'] ),
                array('email' => $clientEmail),
                ),
            'reminders' => array('useDefault' => TRUE),
        );
        #Add Event
        error_log("trying to add event");
        $event = new \Google_Service_Calendar_Event( $newEvent );
        $addevent = $this->calendar->events->insert($CalendarId, $event);
        return $addevent ? true : false;
    }
    public function checkCalendarsFree($startTime = null, $DurationInMinutes = 0){
        require_once Plugin::get_plugin_path() . '/admin/model.php';
        $Calendars = Model::get_calendars();
        $timezone = $this->getTimeZone();
        $startTime = new \DateTime( $startTime, new DateTimeZone($timezone));
        $StartString = $startTime->format( \DateTime::ATOM );
        $startTime->add( new \DateInterval("PT".$DurationInMinutes."M"));  
        $EndString = $startTime->format( \DateTime::ATOM );
        error_log("CALENDARS");
        error_log(print_r($Calendars, true));
        foreach($Calendars as $key => $calendar){
            error_log("checking for " . $key);
            error_log("key " . $calendar['id']);
            error_log($StartString);
            error_log($EndString);
            if( $this->checkCalendarFree( $StartString, $EndString, $calendar['id']) ) return $calendar['id'];
        }
        error_log("checked all calendars without returning an id. Returning False.");
        return false;
    }
    public function checkCalendarFree( $startTime = null, $endTime = null, $Calendar_Id = null ){

        #Fail silently
        if(!$startTime || !$endTime || !$Calendar_Id) {
            error_log("Missing startTime, endTime, or Calendar_Id");
            return false;
        }
        $timezone = $this->getTimeZone();
        $startTime= new \DateTime( $startTime, new DateTimeZone($timezone) );
        #$startTime->setTimezone( new \DateTimeZone($timezone) );
        $StartStringFinal = $startTime->format( \DateTime::ATOM );
        $endTime = new \DateTime( $endTime, new DateTimeZone($timezone) );
        #$endTime->setTimezone( new \DateTimeZone($timezone) );
        $EndStringFinal = $endTime->format( \DateTime::ATOM );
        $request = new \Google_Service_Calendar_FreeBusyRequest();
        $item = new \Google_Service_Calendar_FreeBusyRequestItem();
        $item->setId($Calendar_Id);
        $request->setItems( array( $item ) );
        $request->setTimeZone( $timezone );
        $request->setTimeMin($StartStringFinal);
        $request->setTimeMax($EndStringFinal);
        $query = $this->calendar->freebusy->query($request);

        $response = $query->getCalendars()[$Calendar_Id]->getBusy();
        if($response){
            //this Calendar is being tracked. 
            foreach($response as $idx => $TimePeriod){
                #we don't need to match times because we already set start and end time for the query
                if($TimePeriod && $TimePeriod->end && $TimePeriod->start){
                    error_log("BUSY");
                    return false;
                }
            }
            return true;

        } else {
            return true;
        }

    }
}