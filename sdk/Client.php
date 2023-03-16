
<?php
use rbtGoogleCalendar as Plugin;
require_once Plugin::get_plugin_path() . 'sdk/vendor/autoload.php';
// require_once Plugin::get_plugin_path() . 'sdk/v2/src/Client.php';
// require_once Plugin::get_plugin_path() . 'sdk/v2/src/Service/Calendar.php';

class CalendarClient {
        public $err = false;
        public $permission_granted = false;
        public $token = false;
        #set scopes here
        private $scopes = array(                
            'https://www.googleapis.com/auth/plus.me', 
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.readonly', 
            'https://www.googleapis.com/auth/calendar.acls.readonly',
            'https://www.googleapis.com/auth/calendar.app.created',
            'https://www.googleapis.com/auth/calendar.calendarlist',
            'https://www.googleapis.com/auth/calendar.calendarlist.readonly',
            'https://www.googleapis.com/auth/calendar.calendars',
            'https://www.googleapis.com/auth/calendar.calendars.readonly',
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/calendar.events.freebusy',
            'https://www.googleapis.com/auth/calendar.events.owned',
            'https://www.googleapis.com/auth/calendar.events.owned.readonly',
            'https://www.googleapis.com/auth/calendar.events.public.readonly',
            'https://www.googleapis.com/auth/calendar.events.readonly',
            'https://www.googleapis.com/auth/calendar.freebusy'
        );
    public function __construct(){
        if(session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->client = new Google_Client();
        $this->setClientID();
        $this->setClientSecret();
        $this->setDeveloperKey();
        $this->client->setApplicationName("WPToGoogleCalendar");
        $this->client->setScopes( $this->scopes );
        $this->client->setRedirectUri( \admin_url() . '?page=rbtgc_options');
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setIncludeGrantedScopes(true); 
        $this->doAuth();
        if($this->err) error_log(print_r($this,true));
        $this->calendar = !$this->err ? $this->getCalendar() : false;

    }
    private function checkClientEmail($calendar){
        #store email once
        if( !\get_option('rbtgc_client_email')){
            $CalendarEmailAddress = $calendar->calendars->get('primary')->id;
            \update_option('rbtgc_client_email', $CalendarEmailAddress);
        }
    }
    private function getCalendar(){

        if( $this->client->getAccessToken()) { 
            $this->permission_granted = true;
            $calendar = new Google_Service_Calendar($this->client);
            if($calendar) $this->checkClientEmail($calendar);
            return $calendar;
        } else {
            $expired = $this->client->isAccessTokenExpired();
            if($expired) {
                $refresh_token = \get_option('rbtgc_refresh_token');
                $this->client->refreshToken($refresh_token);
                $newtoken=$this->client->getAccessToken();
                if($newtoken){
                    $this->client->setAccessToken($newtoken);
                }
                if($this->client->getAccessToken() ){
                    $this->permission_granted = true;
                    $calendar = new Google_Service_Calendar($this->client);
                    if($calendar) $this->checkClientEmail($calendar);
                    return $calendar;
                }
            } else {
                $this->err = "Could not get access token";
            }
            return false;
        }

    }
    private function doAuth(){

        if (isset($_GET['logout'])) {
            unset($_SESSION['access_token']);
        }
        if (isset($_GET['code'])) {
            $this->client->authenticate($_GET['code']);
            $access_token = $this->client->getAccessToken();
            if($access_token){
                $_SESSION['access_token'] = $access_token;
                $refresh_token = $this->client->getRefreshToken();
                error_log("REFESH TOKEN?");
                error_log($refresh_token);
                \update_option('rbtgc_refresh_token', $refresh_token);
                $this->client->setAccessToken($access_token);
            }
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
        }


    }
    private function setClientID(){
        $clientid = \get_option( 'rbtgc_client_id');
        if($clientid){
            $this->client->setClientId($clientid);
        } else {
            $this->err = 'Missing or invalid client ID';
        }
    }
    private function setClientSecret(){
        $clientsecret = \get_option( 'rbtgc_client_secret');
        if($clientsecret){
            $this->client->setClientSecret($clientsecret);
        } else {
            $this->err = 'Missing or invalid client Secret';
        }
    }
    private function setDeveloperKey(){
        $developerkey = \get_option( 'rbtgc_developer_key');
        if($developerkey){
            $this->client->setDeveloperKey($developerkey);
        } else {
            $this->err = 'Missing or invalid API Key';
        }
    }
}
