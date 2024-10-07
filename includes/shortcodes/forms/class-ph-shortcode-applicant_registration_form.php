<?php

class PH_Shortcode_Applicant_Registration_Form extends PH_Shortcode{
     public function __construct(){
        parent::__construct("applicant_registration_form", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){

		$atts = shortcode_atts( array(

		), $atts, 'applicant_registration_form' );

		$assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
        wp_enqueue_script( 'propertyhive_account', $assets_path . 'js/frontend/account.js', array( 'jquery' ), PH_VERSION, true );

		ob_start();

		if ( is_user_logged_in() )
		{
			ph_get_template( 'account/already-logged-in.php' );
			return ob_get_clean();
		}

		$form_controls = ph_get_user_details_form_fields();

    	$form_controls = apply_filters( 'propertyhive_user_details_form_fields', $form_controls );

    	$form_controls_2 = ph_get_applicant_requirements_form_fields();

    	$form_controls_2 = apply_filters( 'propertyhive_applicant_requirements_form_fields', $form_controls_2, false );

    	$form_controls = array_merge( $form_controls, $form_controls_2 );

    	if ( get_option( 'propertyhive_applicant_registration_form_disclaimer', '' ) != '' )
	    {
	        $disclaimer = get_option( 'propertyhive_applicant_registration_form_disclaimer', '' );

	        $form_controls['disclaimer'] = array(
	            'type' => 'checkbox',
	            'label' => $disclaimer,
	            'label_style' => 'width:100%;',
	            'required' => true
	        );
	    }

    	$form_controls = apply_filters( 'propertyhive_applicant_registration_form_fields', $form_controls );

    	ph_get_template( 'account/applicant-registration-form.php', array( 'form_controls' => $form_controls ) );

		return ob_get_clean();
    }
}

new PH_Shortcode_Applicant_Registration_Form();