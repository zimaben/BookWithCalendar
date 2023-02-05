<?php 
namespace rbtgc\admin;
use rbtGoogleCalendar as Plugin;

class PluginOptions {
    public static function run(){
        \add_action( 'admin_menu', array(get_class(), 'add_menu' ));
 
        \add_action( 'admin_post', array( get_class(), 'save_menu_submission' ) );
        
    }
    public static function add_menu() {
        \add_menu_page( Plugin::nice_name . ' Options', Plugin::nice_name . ' Options', 'administrator', Plugin::text_domain . '_options', array(get_class(), 'render_options_page'), 'dashicons-chart-pie', 0 );
    }
    public static function render_options_page(){
        $clientid = \get_option( 'rbtgc_client_id');
        $clientsecret = \get_option( 'rbtgc_client_secret');
        $developerkey = \get_option( 'rbtgc_developer_key');

        \do_action( 'admin_notices' );

        if(isset($_GET['update-clientinfo'])) {
            self::render_step_one($clientid, $clientsecret, $developerkey);
            return true;
        }

        if($clientid && $clientsecret && $developerkey){
            self::render_step_two();
        } else {
            self::render_step_one($clientid, $clientsecret, $developerkey);
        }

    }
    private static function checkfields(){
        $required_fields = array('client-id', 'client-secret', 'developer-key');

        foreach($required_fields as $field) if( !isset($_POST[$field])) return false;
        return \wp_verify_nonce( $_POST['settings'], Plugin::text_domain . '_options' );
        
    }
    function save_error() {
        $class = 'notice notice-error is-dismissible';
        $message = __( 'There was an error saving your data', Plugin::text_domain );
    
        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    }
    function save_notice() {
        $class = 'notice notice-success is-dismissible';
        $message = __( 'Settings saved', Plugin::text_domain );
    
        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    }
    public static function save_menu_submission(){
        if( self::checkfields() ){
            // If the above are valid, sanitize and save the option. 
            if ( null !== wp_unslash( $_POST['client-id'] ) ) {
                $value = \sanitize_text_field( $_POST['client-id'] );
                \update_option( 'rbtgc_client_id', $value );
            }
            if ( null !== wp_unslash( $_POST['developer-key'] ) ) {
                $value = \sanitize_text_field( $_POST['developer-key'] );
                \update_option( 'rbtgc_developer_key', $value );
            }
            if ( null !== wp_unslash( $_POST['client-id'] ) ) {
                $value = \sanitize_text_field( $_POST['client-secret'] );
                \update_option( 'rbtgc_client_secret', $value );
            }
            \add_action( 'admin_notices', 'save_notice' );
            wp_redirect( get_admin_url() . '?page=rbtgc_options' );
            exit;
        } else {
            \add_action( 'admin_notices', 'save_error' );
            \wp_redirect( get_admin_url() . '?page=rbtgc_options' );
            exit;
        }
    }
    private static function render_step_two(){
        require_once Plugin::get_plugin_path() . 'sdk/Client.php';
        $Client = new \CalendarClient();
        if( $Client->err){
            ?>
            <div class="notice notice-error is-dismissible"><?php echo $Client->err ?></div>
            <?php
        } 
        if( ! $Client->permission_granted){
            $authUrl = $Client->client->createAuthUrl();
            print "<a class='login' href='$authUrl'>Connect Your Calendar</a>";
        }
        ?>
        
        <a href="<?php echo get_admin_url() . '?page=rbtgc_options&update-clientinfo=true' ?>">Reset Credentials</a>
        <?php
    }
    private static function render_step_one($clientid = null, $clientsecret = null, $developerkey = null){
        ?> 

        <h2><?php echo Plugin::nice_name . ' Settings' ?></h2> 
        <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
            <div id="universal-message-container">
                <h2>Required fields</h2>
                        <div class="instructions">
                            <p>Please include the Client ID and Client Secret you generated from creating a new project in the Google Calendar API using <a href="https://developers.google.com/calendar/api/guides/overview" target="_blank">these steps</a></p>
                        </div>
                        <div class="options">
                            <p>
                                <label>Client ID:</label>
                                <br />
                                <input type="password" name="client-id" value="<?php echo $clientid ? $clientid : ''?>" />
                            </p>
                            <p>
                                <label>Client Secret:</label>
                                <br />
                                <input type="password" name="client-secret" value="<?php echo $clientsecret ? $clientsecret : ''?>" />
                            </p>
                            <p>
                                <label>API Key:</label>
                                <br />
                                <input type="password" name="developer-key" value="<?php echo $developerkey ? $developerkey : ''?>" />
                            </p>
                    </div><!-- #universal-message-container -->
                    <?php 
            \wp_nonce_field( Plugin::text_domain . '_options', 'settings' ); 
            submit_button(); 
            ?>
        </form>
        
        <?php ; 
    }

}
PluginOptions::run();