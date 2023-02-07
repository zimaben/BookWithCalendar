
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
        session_start();
        $this->client = new Google_Client();
        $this->client->setAuthConfig( Plugin::get_plugin_path() . 'sdk/client_secret_741701277318-b04uro3lssiaoqonv5ar5432dqhrn55n.apps.googleusercontent.com.json');
        // $this->setClientID();
        // $this->setClientSecret();
        // $this->setDeveloperKey();
        $this->client->setApplicationName("WPToGoogleCalendar");
        $this->client->setScopes( $this->scopes );
        $this->client->setRedirectUri( \admin_url() . '?page=rbtgc_options');
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setIncludeGrantedScopes(true); 
        $this->doAuth();
        if($this->err) error_log(print_r($this,true));
        $this->calendar = !$this->err ? $this->getCalendar() : false;
        
        // $client->setAuthConfig('client_secret.json');
        // $client->addScope(Google\Service\Drive::DRIVE_METADATA_READONLY);
        // $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');
        // // offline access will give you both an access and refresh token so that
        // // your app can refresh the access token without user interaction.
        // $client->setAccessType('offline');
        // // Using "consent" ensures that your application always receives a refresh token.
        // // If you are not using offline access, you can omit this.
        // $client->setApprovalPrompt('consent');
        // $client->setIncludeGrantedScopes(true);   // incremental auth

    }

    private function getCalendar(){
        $try = $this->client->getAccessToken();
        error_log($try);
        error_log(print_r($try, true));
        if( $this->client->getAccessToken()) { 
            $this->permission_granted = true;
            $calendar = new Google_Service_Calendar($this->client);
            return $calendar;
        } else {

            error_log(print_r($this,true));
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
            $_SESSION['access_token'] = $access_token;
            $this->client->setAccessToken($access_token);
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
        }

        // // Set the refresh token on the client.	
		// if (isset($_SESSION['refresh_token']) && $_SESSION['refresh_token']) {
		// 	$this->client->refreshToken($_SESSION['refresh_token']);
		// }
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
