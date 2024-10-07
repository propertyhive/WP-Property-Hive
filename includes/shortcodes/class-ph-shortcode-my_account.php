<?php


class PH_Shortcode_My_Account extends PH_Shortcode{
     public function __construct(){
        parent::__construct("propertyhive_my_account", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){
        $atts = shortcode_atts( array(

		), $atts, 'my_account' );

		$assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
        wp_enqueue_script( 'propertyhive_account', $assets_path . 'js/frontend/account.js', array( 'jquery' ), PH_VERSION, true );

		ob_start();

		// Check user is logged in
		if ( !is_user_logged_in() )
		{
			ph_get_template( 'account/invalid-access.php' );
			return ob_get_clean();
		}

		// Check 'propertyhive_applicant_users' setting is enabled
		if ( get_option( 'propertyhive_applicant_users', '' ) != 'yes' )
   		{
   			ph_get_template( 'account/invalid-access.php' );
			return ob_get_clean();
		}

		ph_get_template( 'account/my-account.php' );

		return ob_get_clean();
    }
}

new PH_Shortcode_My_Account();