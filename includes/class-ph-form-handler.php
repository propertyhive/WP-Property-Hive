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

		$current_settings = get_option( 'propertyhive_template_assistant', array() );
		
		if ( isset($current_settings['search_forms']) && !empty($current_settings['search_forms']) )
        {
            foreach ( $current_settings['search_forms'] as $id => $form )
            {
                add_filter( 'propertyhive_search_form_fields_' . $id, function($fields)
                {
                    $form_id = str_replace( "propertyhive_search_form_fields_", "", current_filter() );

                    $current_settings = get_option( 'propertyhive_template_assistant', array() );

                    $new_fields = ( 
                        ( 
                            isset($current_settings['search_forms'][$form_id]['active_fields'])
                            &&
                            !empty($current_settings['search_forms'][$form_id]['active_fields'])
                        ) ? 
                        $current_settings['search_forms'][$form_id]['active_fields'] : 
                        $fields 
                    );
                    
                    // Remove any fields that are in the $fields array but not active in active_fields, excluding hidden fields
                    $hidden_fields = array();
                    foreach ( $fields as $field_id => $field )
                    {
                        if ( !isset($new_fields[$field_id]) && $field['type'] != 'hidden' )
                        {
                            unset($fields[$field_id]);
                        }

                        if ( isset($field['type']) && $field['type'] == 'hidden' && !isset($new_fields[$field_id]) )
                        {
                            $new_fields[$field_id] = $field;
                        }
                    }

                    // Merge the new with existing (if existing exists)
                    foreach ( $new_fields as $field_id => $new_field )
                    {
                        $fields[$field_id] = array_merge( ( isset($fields[$field_id]) ? $fields[$field_id] : array() ), $new_field );
                    }

                    // Set order
                    $new_ordered_fields = array();
                    foreach ( $new_fields as $field_id => $new_field )
                    {
                        $new_ordered_fields[$field_id] = $fields[$field_id];
                    }
                    $fields = $new_ordered_fields;

                    // Check if any of the fields at this point are setup as additional fields
                    $custom_fields = ( ( isset($current_settings['custom_fields']) ) ? $current_settings['custom_fields'] : array() );

                    foreach ( $fields as $field_id => $field )
                    {
                        foreach ( $custom_fields as $custom_field )
                        {
                            if ( $custom_field['field_name'] == $field_id && ( $custom_field['field_type'] == 'select' || $custom_field['field_type'] == 'multiselect' ) && isset($custom_field['dropdown_options']) && is_array($custom_field['dropdown_options']) )
                            {
                                $options = array('' => ( (isset($field['blank_option'])) ? $field['blank_option'] : '' ) );

                                foreach ( $custom_field['dropdown_options'] as $dropdown_option )
                                {
                                    $options[$dropdown_option] = $dropdown_option;
                                }

                                $fields[$field_id]['options'] = $options;

                                if ( $custom_field['field_type'] == 'multiselect' ) { $fields[$field_id]['type'] = 'select'; }
                            }
                        }
                    }

                    return $fields;
                } , 99, 1 );
            }
        }

	}

	public function add_captcha_to_forms()
	{
		if ( in_array(get_option( 'propertyhive_captcha_service', '' ), array('recaptcha', 'recaptcha-v3', 'hCaptcha', 'turnstile')) )
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
			{
				$fields['recaptcha'] = array(
			        'type' => $captcha_service,
			        'site_key' => get_option( 'propertyhive_captcha_site_key', '' ),
			        'secret' => get_option( 'propertyhive_captcha_secret', '' ),
			    );
				break;
			}
			case "recaptcha-v3":
			{
				$fields['recaptcha-v3'] = array(
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
			case "turnstile":
			{
				$fields['turnstile'] = array(
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
