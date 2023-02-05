
<?php
use rbtGoogleCalendar as Plugin;
require_once Plugin::get_plugin_path() . 'sdk/vendor/autoload.php';
// require_once Plugin::get_plugin_path() . 'sdk/v2/src/Client.php';
// require_once Plugin::get_plugin_path() . 'sdk/v2/src/Service/Calendar.php';

class CalendarClient {
        public $err = false;
        public $permission_granted = false;
        #set scopes here
        private $scopes = array(                
            'https://www.googleapis.com/auth/plus.me', 
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.readonly'
        );
    public function __construct(){
        $this->client = new Google\Client();
        $this->setClientID();
        $this->setClientSecret();
        $this->setDeveloperKey();
        $this->startSession();
        $this->client->setApplicationName("WPToGoogleCalendar");
        $this->client->setScopes( $this->scopes );
        $this->client->setRedirectUri( \admin_url() . '?page=rbtgc_options');
        $this->calendar = !$this->err ? $this->getCalendar() : false;
        error_log(print_r($this, true));
    }
    private function getCalendar(){
        $calendar = new Google\Service\Calendar($this->client);

        if (isset($_GET['logout'])) {
            unset($_SESSION['token']);
        }
        if (isset($_GET['code'])) {
            $this->client->authenticate($_GET['code']);
            $_SESSION['token'] = $this->client->getAccessToken();
            header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
        }
        if (isset($_SESSION['token'])) {
            $this->client->setAccessToken($_SESSION['token']);
        }
        if( $this->client->getAccessToken()) { 
            $this->permission_granted = true;
        } 
    }
    private function startSession(){
        if (!isset($_SESSION) ) {
            session_start();
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
