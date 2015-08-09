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

	/**
	 * Hook in methods
	 */
	public static function init() {
		//add_action( 'init', array( __CLASS__, 'process_login' ) );
		//add_action( 'init', array( __CLASS__, 'process_registration' ) );
	}

	/**
	 * Process the login form.
	 */
	public static function process_login() {
		
	}

	/**
	 * Process the registration form.
	 */
	public static function process_registration() {
		
	}
}

PH_Form_Handler::init();
