<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Handle frontend form submissions
 *
 * @class 		PH_Form_Handler
 * @version		1.0.0
 * @package		PropertyHive/Classes/
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Form_Handler {

	public function __construct() {

		add_action( 'init', array( $this, 'add_captcha_to_forms' ) );

	}

	public function add_captcha_to_forms()
	{
		if ( in_array(get_option( 'propertyhive_captcha_service', '' ), array('recaptcha', 'recaptcha-v3', 'hCaptcha')) )
		{
			add_filter( 'propertyhive_property_enquiry_form_fields', array( $this, 'add_captcha_to_form' ) );
			add_filter( 'propertyhive_applicant_registration_form_fields', array( $this, 'add_captcha_to_form' ) );
			add_filter( 'propertyhive_send_to_friend_form_fields', array( $this, 'add_captcha_to_form' ) );
		}
	}

	public function add_captcha_to_form($fields)
	{
		$captcha_service = get_option( 'propertyhive_captcha_service', '' );

		switch ( $captcha_service )
		{
			case "recaptcha":
			case "recaptcha-v3":
			{
				$fields['recaptcha'] = array(
			        'type' => $captcha_service,
			        'site_key' => get_option( 'propertyhive_captcha_site_key', '' ),
			        'secret' => get_option( 'propertyhive_captcha_secret', '' ),
			    );
				break;
			}
			case "hCaptcha":
			{
				$fields['hCaptcha'] = array(
			        'type' => $captcha_service,
			        'site_key' => get_option( 'propertyhive_captcha_site_key', '' ),
			        'secret' => get_option( 'propertyhive_captcha_secret', '' ),
			    );
				break;
			}
		}

		return $fields;
	}
}

new PH_Form_Handler();
