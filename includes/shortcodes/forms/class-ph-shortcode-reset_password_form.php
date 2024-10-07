<?php


class PH_Shortcode_Reset_Password_Form extends PH_Shortcode{
     public function __construct(){
        parent::__construct("propertyhive_reset_password_form", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){
        $atts = shortcode_atts( array(

		), $atts, 'reset_password_form' );

		$assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
        wp_enqueue_script( 'propertyhive_account', $assets_path . 'js/frontend/account.js', array( 'jquery' ), PH_VERSION, true );

		ob_start();

		if ( is_user_logged_in() )
		{
			ph_get_template( 'account/already-logged-in.php' );
			return ob_get_clean();
		}

		// Check 'propertyhive_applicant_users' setting is enabled
		if ( get_option( 'propertyhive_applicant_users', '' ) != 'yes' )
   		{
   			ph_get_template( 'account/invalid-access.php' );
			return ob_get_clean();
		}

		// check key provided is valid
		if ( !isset($_GET['key']) || empty(ph_clean($_GET['key'])) || !isset($_GET['id']) || empty(absint($_GET['id'])) )
		{
			echo esc_html(__( 'Invalid key or id provided. Please try again', 'propertyhive' ));
			return ob_get_clean();
		}

		$key = ph_clean($_GET['key']);
		$user_id = absint($_GET['id']);

		$userdata = get_userdata( $user_id );
		$user_login = $userdata ? $userdata->user_login : '';

		$user = check_password_reset_key( $key, $user_login );

		if ( is_wp_error( $user ) ) 
		{
			echo esc_html(__( 'This key is invalid or has already been used. Please reset your password again if needed.', 'propertyhive' ));
			return ob_get_clean();
		}

		ph_get_template( 'account/reset-password-form.php', array( 'reset_key' => $key, 'reset_login' => $user_login ) );

		return ob_get_clean();

    }
}

new PH_Shortcode_Reset_Password_Form();