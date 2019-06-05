<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PropertyHive PH_AJAX
 *
 * AJAX Event Handler
 *
 * @class 		PH_AJAX
 * @version		1.0.0
 * @package		PropertyHive/Classes
 * @category	Class
 * @author 		PropertyHive
 */
class PH_AJAX {

	/**
	 * Hook into ajax events
	 */
	public function __construct() {

		// propertyhive_EVENT => nopriv
		$ajax_events = array(
			'add_note' => false,
			'delete_note' => false,
			'search_contacts' => false,
            'search_properties' => false,
            'search_negotiators' => false,
			'load_existing_owner_contact' => false,
            'load_existing_features' => false,
			'make_property_enquiry' => true,
            'create_contact_from_enquiry' => false,

            // Dashboard components
            'get_news' => false,
            'get_viewings_awaiting_applicant_feedback' => false,

            // Contact actions
            'create_contact_login' => false,

            // Appraisal actions
            'get_appraisal_details_meta_box' => false,
            'get_appraisal_actions' => false,
            'appraisal_carried_out' => false,
            'appraisal_cancelled' => false,
            'appraisal_won' => false,
            'appraisal_lost_reason' => false,
            'appraisal_instructed' => false,
            'appraisal_revert_pending' => false,
            'appraisal_revert_carried_out' => false,
            'appraisal_revert_won' => false,

            // Viewing actions
            'book_viewing_property' => false,
            'book_viewing_contact' => false,
            'get_viewing_details_meta_box' => false,
            'get_viewing_actions' => false,
            'viewing_carried_out' => false,
            'viewing_cancelled' => false,
            'viewing_email_applicant_booking_confirmation' => false,
            'viewing_email_owner_booking_confirmation' => false,
            'viewing_interested_feedback' => false,
            'viewing_not_interested_feedback' => false,
            'viewing_feedback_not_required' => false,
            'viewing_revert_feedback_pending' => false,
            'viewing_revert_pending' => false,
            'viewing_feedback_passed_on' => false,
            'get_property_viewings_meta_box' => false,
            'get_contact_viewings_meta_box' => false,

            // Offer actions
            'record_offer_property' => false,
            'record_offer_contact' => false,
            'get_offer_details_meta_box' => false,
            'get_offer_actions' => false,
            'get_property_offers_meta_box' => false,
            'offer_accepted' => false,
            'offer_declined' => false,
            'offer_revert_pending' => false,
            'get_contact_offers_meta_box' => false,
            
            // Sale actions
            'get_sale_details_meta_box' => false,
            'get_sale_actions' => false,
            'get_sale_details_meta_box' => false,
            'sale_exchanged' => false,
            'sale_completed' => false,
            'sale_fallen_through' => false,
            'offer_declined' => false,
            'get_property_sales_meta_box' => false,
            'get_contact_sales_meta_box' => false,

            'validate_save_contact' => false,
            'applicant_registration' => true,
            'login' => true,
            'save_account_details' => true,
            'save_account_requirements' => true,

            'dismiss_notice_leave_review' => false,
            'dismiss_notice_missing_search_results' => false,
            'dismiss_notice_missing_google_maps_api_key' => false,
            'dismiss_notice_invalid_expired_license_key' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_propertyhive_' . $ajax_event, array( $this, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_propertyhive_' . $ajax_event, array( $this, $ajax_event ) );
			}
		}
	}

    public function dismiss_notice_leave_review()
    {
        update_option( 'propertyhive_review_prompt_due_timestamp', 0 );
        
        // Quit out
        die();
    }

    public function dismiss_notice_missing_search_results()
    {
        update_option( 'missing_search_results_notice_dismissed', 'yes' );
        
        // Quit out
        die();
    }

    public function dismiss_notice_missing_google_maps_api_key()
    {
        update_option( 'missing_google_maps_api_key_notice_dismissed', 'yes' );
        
        // Quit out
        die();
    }

    public function dismiss_notice_invalid_expired_license_key()
    {
        update_option( 'missing_invalid_expired_license_key_notice_dismissed', 'yes' );
        
        // Quit out
        die();
    }

	/**
	 * Output headers for JSON requests
	 */
	private function json_headers() {
		header( 'Content-Type: application/json; charset=utf-8' );
	}

    public function create_contact_login()
    {
        check_ajax_referer( 'create-login', 'security' );

        $this->json_headers();

        if (empty($_POST['contact_id']))
        {
            $return = array('error' => 'No contact selected');
            echo json_encode( $return );
            die();
        }

        if (empty($_POST['password']))
        {
            $return = array('error' => 'No password entered');
            echo json_encode( $return );
            die();
        }

        $contact = new PH_Contact((int)$_POST['contact_id']);

         // Create user
        $userdata = array(
            'display_name' => get_the_title((int)$_POST['contact_id']),
            'user_login' => sanitize_email($contact->email_address),
            'user_email' => sanitize_email($contact->email_address),
            'user_pass'  => $_POST['password'],
            'role' => 'property_hive_contact',
            'show_admin_bar_front' => 'false',
        );

        $user_id = wp_insert_user( $userdata );

        // On success
        if ( ! is_wp_error( $user_id ) )
        {
            // Assign user ID to CPT
            add_post_meta( (int)$_POST['contact_id'], '_user_id', $user_id );

            $return = array('success' => true);
        }
        else
        {
            $return = array('error' => 'Failed to create user login');
        }

        echo json_encode( $return );
        die();
    }

    /**
     * Login user
     */
    public function login()
    {
        $return = array(
            'success' => false,
            'errors' => array(),
        );

        if ( check_ajax_referer( 'ph_login', 'security', false ) === FALSE )
        {
            $return['errors'][] = 'Invalid nonce';

            $this->json_headers();
            echo json_encode( $return );
            
            // Quit out
            die();
        }

        $creds = array(
            'user_login' => ph_clean($_POST['email_address']),
            'user_password' => ph_clean($_POST['password']),
        );

        $user = wp_signon( apply_filters( 'propertyhive_login_credentials', $creds ), is_ssl() );

        if ( is_wp_error( $user ) ) 
        {
            //$return['errors'][] = $user->get_error_message();
        }
        else
        {
            // Check has associated contact CPT and is published
            $args = array(
                'post_type' => 'contact',
                'fields' => 'ids',
                'posts_per_page' => 1,
                'post_status' => array( 'publish' ),
                'meta_query' => array(
                    array(
                        'key' => '_user_id',
                        'value' => $user->ID
                    )
                )
            );

            $contact_query = new WP_Query( $args );

            if ( $contact_query->have_posts() )
            {
                while ( $contact_query->have_posts() )
                {
                    $contact_query->the_post();

                    // Has associated published contact CPT
                    $return['success'] = true;

                    do_action('propertyhive_user_logged_in', get_the_ID(), $user->ID);
                }
            }
            
            wp_reset_postdata();
        }

        $this->json_headers();
        echo json_encode( $return );
        
        // Quit out
        die();
    }

    /**
     * Register applicant
     */
    public function applicant_registration()
    {
        // Validate contact
        global $post;
        
        $return = array(
            'success' => false,
            'errors' => array(),
        );

        if ( check_ajax_referer( 'ph_register', 'security', false ) === FALSE )
        {
            $return['errors'][] = 'Invalid nonce';

            $this->json_headers();
            echo json_encode( $return );
            
            // Quit out
            die();
        }
        
        // Validate
        $errors = array();

        $form_controls = ph_get_user_details_form_fields();
    
        $form_controls = apply_filters( 'propertyhive_user_details_form_fields', $form_controls );

        $form_controls_2 = ph_get_applicant_requirements_form_fields();
    
        $form_controls_2 = apply_filters( 'propertyhive_applicant_requirements_form_fields', $form_controls_2 );
        
        $form_controls = array_merge( $form_controls, $form_controls_2 );

        // need to improve this as duplicated in ph-shortcodes.php
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

        foreach ( $form_controls as $key => $control )
        {
            if ( isset( $control ) && isset( $control['required'] ) && $control['required'] === TRUE )
            {
                // This field is mandatory. Lets check we received it in the post
                if ( ! isset( $_POST[$key] ) || ( isset( $_POST[$key] ) && empty( $_POST[$key] ) ) )
                {
                    $errors[] = __( 'Missing required field', 'propertyhive' ) . ': ' . $key;
                }
            }
            if ( isset( $control['type'] ) && $control['type'] == 'email' && isset( $_POST[$key] ) && ! empty( $_POST[$key] ) )
            {
                if ( ! is_email( $_POST[$key] ) )
                {
                    $errors[] = __( 'Invalid email address provided', 'propertyhive' );
                }
                else
                {
                    // Make sure this email address doesn't exist already
                    $args = array(
                        'post_type' => 'contact',
                        'posts_per_page' => 1,
                        'fields' => 'ids',
                        'post_status' => array( 'publish' ),
                        'meta_query' => array(
                            array(
                                'key' => '_email_address',
                                'value' => $_POST[$key]
                            )
                        )
                    );

                    $contacts_query = new WP_Query( $args );

                    if ( $contacts_query->have_posts() )
                    {
                        $errors[] = __( 'This email address is already registered', 'propertyhive' );
                    }
                    else
                    {
                        if ( email_exists( $_POST[$key] ) ) 
                        {
                            $errors[] = __( 'This email address is already registered to a user', 'propertyhive' );
                        }
                    }
                    wp_reset_postdata();
                }
            }
            if ( $key == 'recaptcha' )
            {
                $secret = isset( $control['secret'] ) ? $control['secret'] : '';
                $response = isset( $_POST['g-recaptcha-response'] ) ? ph_clean($_POST['g-recaptcha-response']) : '';

                $response = wp_remote_post( 
                    'https://www.google.com/recaptcha/api/siteverify',
                    array(
                        'method' => 'POST',
                        'body' => array( 'secret' => $secret, 'response' => $response ),
                    )
                );
                if ( is_wp_error( $response ) ) 
                {
                    $errors[] = $response->get_error_message();
                }
                else
                {
                    $response = json_decode($response['body'], TRUE);
                    if ( $response === FALSE )
                    {
                        $errors[] = __( 'Error decoding response from reCAPTCHA check', 'propertyhive' );
                    }
                    else
                    {
                        if ( isset($response['success']) && $response['success'] == true )
                        {

                        }
                        else
                        {
                            $errors[] = __( 'Failed reCAPTCHA validation', 'propertyhive' );
                        }
                    }
                }
            }
        }

        // Check password and password2 match
        if ( isset( $_POST['password'] ) && isset( $_POST['password2'] ) && $_POST['password'] != $_POST['password2'] )
        {
            $errors[] = __( 'The passwords entered do not match', 'propertyhive' );
        }
        
        if ( !empty($errors) )
        {
            // Failed validation
            
            $return['success'] = false;
            $return['reason'] = 'validation';
            $return['errors'] = $errors;
        }
        else
        {
            // create CPT
            $contact_post = array(
                'post_title'    => ph_clean($_POST['name']),
                'post_content'  => '',
                'post_type'     => 'contact',
                'post_status'   => 'publish',
                'comment_status'=> 'closed',
                'ping_status'   => 'closed',
            );
            
            // Insert the post into the database
            $contact_post_id = wp_insert_post( $contact_post );

            // Add post meta (contact details, requirements etc)
            add_post_meta( $contact_post_id, '_email_address', sanitize_email($_POST['email_address']) );
            
            add_post_meta( $contact_post_id, '_telephone_number', ( ( isset($_POST['telephone_number']) ) ? ph_clean($_POST['telephone_number']) : '' ) );
            add_post_meta( $contact_post_id, '_telephone_number_clean', ( ( isset($_POST['telephone_number']) ) ? ph_clean( ph_clean_telephone_number( $_POST['telephone_number']) ) : '' ) );

            add_post_meta( $contact_post_id, '_contact_types', array('applicant') );

            add_post_meta( $contact_post_id, '_applicant_profiles', 1 );

            $applicant_profile = array();
            $applicant_profile['department'] = $_POST['department'];

            if ( $_POST['department'] == 'residential-sales' )
            {
                $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_price']));

                $applicant_profile['max_price'] = $price;

                // Not used yet but could be if introducing currencies in the future.
                $applicant_profile['max_price_actual'] = $price;
            }
            elseif ( $_POST['department'] == 'residential-lettings' )
            {
                $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_rent']));

                $applicant_profile['max_rent'] = $price;
                $applicant_profile['rent_frequency'] = 'pcm';
                $price_actual = $price; // Stored in pcm
                $applicant_profile['max_price_actual'] = $price_actual;
            }

            if ( $_POST['department'] == 'residential-sales' || $_POST['department'] == 'residential-lettings' )
            {
                $beds = preg_replace("/[^0-9]/", '', ph_clean($_POST['minimum_bedrooms']));
                $applicant_profile['min_beds'] = $beds;

                if ( isset($_POST['property_type']) && !empty($_POST['property_type']) )
                {
                    $applicant_profile['property_types'] = is_array(ph_clean($_POST['property_type'])) ? ph_clean($_POST['property_type']) : array(ph_clean($_POST['property_type']));
                }
            }

            if ( $_POST['department'] == 'commercial' )
            {
                $available_as = array();
                if ( isset($_POST['available_as_sale']) && $_POST['available_as_sale'] == 'yes' )
                {
                    $available_as[] = 'sale';
                }
                if ( isset($_POST['available_as_rent']) && $_POST['available_as_rent'] == 'yes' )
                {
                    $available_as[] = 'rent';
                }
                $applicant_profile['available_as'] = $available_as;

                if ( isset($_POST['commercial_property_type']) && !empty($_POST['commercial_property_type']) )
                {
                    $applicant_profile['commercial_property_types'] = is_array(ph_clean($_POST['commercial_property_type'])) ? ph_clean($_POST['commercial_property_type']) : array(ph_clean($_POST['commercial_property_type']));
                }
            }

            if ( isset($_POST['location']) && !empty($_POST['location']) )
            {
                $applicant_profile['locations'] = is_array(ph_clean($_POST['location'])) ? ph_clean($_POST['location']) : array(ph_clean($_POST['location']));
            }

            $applicant_profile['notes'] = ( ( isset($_POST['additional_requirements']) ) ? sanitize_textarea_field($_POST['additional_requirements']) : '' );

            $applicant_profile['send_matching_properties'] = 'yes';
            //$applicant_profile['auto_match_disabled'] = ''; // don't know what to do about this yet. Should probably look at global setting and reflect that

            update_post_meta( $contact_post_id, '_applicant_profile_0', $applicant_profile );
            
            // Create user
            $userdata = array(
                'display_name' => ph_clean($_POST['name']),
                'user_login' => sanitize_email($_POST['email_address']),
                'user_email' => sanitize_email($_POST['email_address']),
                'user_pass'  => ph_clean($_POST['password']),
                'role' => 'property_hive_contact',
                'show_admin_bar_front' => 'false',
            );

            $user_id = wp_insert_user( $userdata );

            //On success
            if ( ! is_wp_error( $user_id ) )
            {
                // Assign user ID to CPT
                add_post_meta( $contact_post_id, '_user_id', $user_id );

                $return['success'] = true;

                wp_set_auth_cookie( $user_id, true );

                do_action( 'propertyhive_applicant_registered', $contact_post_id, $user_id );
            }
            else
            {
                $return['success'] = false;
                $return['reason'] = 'validation';
                $return['errors'] = array('Failed to create user. You might experience issues with logging in');
            }
        }

        $this->json_headers();
        echo json_encode( $return );
        
        // Quit out
        die();
    }

    /**
     * Save account details
     */
    public function save_account_details()
    {
        global $wpdb, $current_user;

        add_filter( 'send_email_change_email', '__return_false' );

        $return = array(
            'success' => false,
            'errors' => array(),
        );

        // Got an issue with nonce being declined on second submission.
        // Need to sort before putting this back in
        /*if ( check_ajax_referer( 'ph_details', 'security', false ) === FALSE )
        {
            $return['errors'][] = 'Invalid nonce';

            $this->json_headers();
            echo json_encode( $return );
            
            // Quit out
            die();
        }*/
        
        // Validate
        $errors = array();

        $user_id = (int) get_current_user_id();
        $current_user = get_user_by( 'id', $user_id );
        if ( $user_id <= 0 ) 
        {
            $return['success'] = false;
            $return['reason'] = 'notloggedin';
            $return['errors'] = array('It doesn\'t appear that you\'re logged in');

            $this->json_headers();
            echo json_encode( $return );
            
            // Quit out
            die();
        }

        $form_controls = ph_get_user_details_form_fields();
    
        $form_controls = apply_filters( 'propertyhive_user_details_form_fields', $form_controls );

        foreach ( $form_controls as $key => $control )
        {
            if ( isset( $control ) && isset( $control['required'] ) && $control['required'] === TRUE )
            {
                // This field is mandatory. Lets check we received it in the post
                if ( ! isset( $_POST[$key] ) || ( isset( $_POST[$key] ) && empty( $_POST[$key] ) ) && $control['type'] != 'password' )
                {
                    $errors[] = __( 'Missing required field', 'propertyhive' ) . ': ' . $key;
                }
            }
            if ( isset( $control['type'] ) && $control['type'] == 'email' && isset( $_POST[$key] ) && ! empty( $_POST[$key] ) )
            {
                if ( ! is_email( $_POST[$key] ) )
                {
                    $errors[] = __( 'Invalid email address provided', 'propertyhive' );
                }

                // need to see if email address is being changed and, if so, check new email address doesn't exist already
            }
        }

        // Check password and password2 match
        if ( isset( $_POST['password'] ) && isset( $_POST['password2'] ) && !empty( $_POST['password'] ) && $_POST['password'] != $_POST['password2'] )
        {
            $errors[] = __( 'The passwords entered do not match', 'propertyhive' );
        }

        if ( !empty($errors) )
        {
            // Failed validation
            
            $return['success'] = false;
            $return['reason'] = 'validation';
            $return['errors'] = $errors;
        }
        else
        {
            $contact = new PH_Contact( '', $user_id );
            
            // create CPT
            $contact_post = array(
                'ID' => $contact->id,
                'post_title' => ph_clean($_POST['name']),
            );
            
            // Update the post in the database
            $contact_post_id = wp_update_post( $contact_post );

            update_post_meta( $contact_post_id, '_email_address', sanitize_email($_POST['email_address']) );
            if (isset($_POST['telephone_number']))
            {
                update_post_meta( $contact_post_id, '_telephone_number', ph_clean($_POST['telephone_number']) );
            }

            // Update user
            $userdata = array(
                'ID' => $user_id,
                'display_name' => ph_clean($_POST['name']),
                'user_email' => sanitize_email($_POST['email_address']),
            );

            if ( isset($_POST['password']) && !empty($_POST['password']) )
            {
                $userdata['user_pass'] = ph_clean($_POST['password']);
            }

            $user_id = wp_update_user( $userdata );

            $user_roles = $current_user->roles;
            $user_role = array_shift($user_roles);

            if ( $user_role === 'property_hive_contact' )
            {
                // Have to update login via SQL as wp_update_user won't allow altering
                // Only do it for property hive contacts though as admin or editor might be viewing this page
                $wpdb->update($wpdb->users, array('user_login' => sanitize_email($_POST['email_address'])), array('ID' => $user_id));
            }

            //On success
            if ( ! is_wp_error( $user_id ) )
            {
                $return['success'] = true;

                wp_set_auth_cookie( $user_id, true );

                do_action( 'propertyhive_account_details_updated', $contact_post_id, $user_id );
            }
            else
            {
                $return['success'] = false;
                $return['reason'] = 'validation';
                $return['errors'] = array('Failed to update user. Please try again');
            }
        }

        $this->json_headers();
        echo json_encode( $return );
        
        // Quit out
        die();
    }

    /**
     * Save account requirements
     */
    public function save_account_requirements()
    {
        // Validate contact
        global $post;
        
        $return = array(
            'success' => false,
            'errors' => array(),
        );

        // Got an issue with nonce being declined on second submission.
        // Need to sort before putting this back in
        /*if ( check_ajax_referer( 'ph_requirements', 'security', false ) === FALSE )
        {
            $return['errors'][] = 'Invalid nonce';

            $this->json_headers();
            echo json_encode( $return );
            
            // Quit out
            die();
        }*/
        
        // Validate
        $errors = array();

        $user_id = (int) get_current_user_id();
        $current_user = get_user_by( 'id', $user_id );
        if ( $user_id <= 0 ) 
        {
            $return['success'] = false;
            $return['reason'] = 'notloggedin';
            $return['errors'] = array('It doesn\'t appear that you\'re logged in');

            $this->json_headers();
            echo json_encode( $return );
            
            // Quit out
            die();
        }

        $form_controls = ph_get_applicant_requirements_form_fields();
    
        $form_controls = apply_filters( 'propertyhive_applicant_requirements_form_fields', $form_controls );

        foreach ( $form_controls as $key => $control )
        {
            if ( isset( $control ) && isset( $control['required'] ) && $control['required'] === TRUE )
            {
                // This field is mandatory. Lets check we received it in the post
                if ( ! isset( $_POST[$key] ) || ( isset( $_POST[$key] ) && empty( $_POST[$key] ) ) )
                {
                    $errors[] = __( 'Missing required field', 'propertyhive' ) . ': ' . $key;
                }
            }
        }
        
        if ( !empty($errors) )
        {
            // Failed validation
            
            $return['success'] = false;
            $return['reason'] = 'validation';
            $return['errors'] = $errors;
        }
        else
        {
            $contact = new PH_Contact( '', $user_id );

            $contact_post_id = $contact->id;

            $applicant_profile = array();
            $applicant_profile['department'] = ph_clean($_POST['department']);

            if ( $_POST['department'] == 'residential-sales' )
            {
                $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_price']));

                $applicant_profile['max_price'] = $price;

                // Not used yet but could be if introducing currencies in the future.
                $applicant_profile['max_price_actual'] = $price;
            }
            elseif ( $_POST['department'] == 'residential-lettings' )
            {
                $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_rent']));

                $applicant_profile['max_rent'] = $price;
                $applicant_profile['rent_frequency'] = 'pcm';
                $price_actual = $price; // Stored in pcm
                $applicant_profile['max_price_actual'] = $price_actual;
            }

            if ( $_POST['department'] == 'residential-sales' || $_POST['department'] == 'residential-lettings' )
            {
                $beds = preg_replace("/[^0-9]/", '', ph_clean($_POST['minimum_bedrooms']));
                $applicant_profile['min_beds'] = $beds;

                if ( isset($_POST['property_type']) && !empty($_POST['property_type']) )
                {
                    $applicant_profile['property_types'] = array(ph_clean($_POST['property_type']));
                }
            }

            if ( $_POST['department'] == 'commercial' )
            {
                $available_as = array();
                if ( isset($_POST['available_as_sale']) && $_POST['available_as_sale'] == 'yes' )
                {
                    $available_as[] = 'sale';
                }
                if ( isset($_POST['available_as_rent']) && $_POST['available_as_rent'] == 'yes' )
                {
                    $available_as[] = 'rent';
                }
                $applicant_profile['available_as'] = $available_as;

                if ( isset($_POST['commercial_property_type']) && !empty($_POST['commercial_property_type']) )
                {
                    $applicant_profile['commercial_property_types'] = array(ph_clean($_POST['commercial_property_type']));
                }
            }

            if ( isset($_POST['location']) && !empty($_POST['location']) )
            {
                $applicant_profile['locations'] = array(ph_clean($_POST['location']));
            }

            $applicant_profile['notes'] = ( ( isset($_POST['additional_requirements']) ) ? sanitize_textarea_field($_POST['additional_requirements']) : '' );

            $applicant_profile['send_matching_properties'] = 'yes';
            //$applicant_profile['auto_match_disabled'] = ''; // don't know what to do about this yet. Should probably look at global setting and reflect that

            update_post_meta( $contact_post_id, '_applicant_profile_0', $applicant_profile );

            $return['success'] = true;

            do_action( 'propertyhive_account_requirements_updated', $contact_post_id, $user_id );
        }

        $this->json_headers();
        echo json_encode( $return );
        
        // Quit out
        die();
    }

    /**
     * Load existing features
     */
    public function load_existing_features() {

        global $post, $wpdb;

        $this->json_headers();

        $return = array();

        $property_query = new WP_Query(array(
            'post_type' => 'property',
            'post_status' => 'any',
            'nopaging' => true
        ));

        if ($property_query->have_posts())
        {
            while ($property_query->have_posts())
            {
                $property_query->the_post();

                $num_property_features = get_post_meta($post->ID, '_features', TRUE);
                if ($num_property_features == '') { $num_property_features = 0; }
                
                for ($i = 0; $i < $num_property_features; ++$i)
                {
                    $feature = get_post_meta($post->ID, '_feature_' . $i, TRUE);
                    if (!in_array($feature, $return) && trim($feature) != '')
                    {
                        $return[] = $feature;
                    }
                }
            }
        }

        echo json_encode($return);

        // Quit out
        die();
    }
    
    /**
     * Load existing owner on property record
     */
    public function load_existing_owner_contact() {
        
        check_ajax_referer( 'load-existing-owner-contact', 'security' );
        
        $contact_id = (int)$_POST['contact_id'];
        
        $contact = get_post($contact_id);
        
        echo '<div id="existing-owner-details-' . $contact_id . '">';
        
        if ( !is_null( $contact ) )
        {
            echo '<p class="form-field">';
                echo '<label>' . __('Name', 'propertyhive') . '</label>';
                echo '<a href="' . get_edit_post_link( $contact_id ) . '">' . get_the_title($contact_id) . '</a>';
            echo '</p>';
            
            $address = array();
            $address_elements = array( '_address_name_number', '_address_street', '_address_two', '_address_three', '_address_four', '_address_postcode' );
            foreach ( $address_elements as $address_element )
            {
                if ( get_post_meta($contact_id, $address_element, TRUE) != '' )
                {
                    $address[] = get_post_meta($contact_id, $address_element, TRUE);
                }
            }

            echo '<p class="form-field">';
                echo '<label>' . __('Address', 'propertyhive') . '</label>';
                echo ( ( !empty($address) ) ? implode(", ", $address) : '-' );
            echo '</p>';
            
            echo '<p class="form-field">';
                echo '<label>' . __('Telephone Number', 'propertyhive') . '</label>';
                echo ( ( get_post_meta($contact_id, '_telephone_number', TRUE) != '' ) ? get_post_meta($contact_id, '_telephone_number', TRUE) : '-' );
            echo '</p>';
            
            echo '<p class="form-field">';
                echo '<label>' . __('Email Address', 'propertyhive') . '</label>';
                echo ( ( get_post_meta($contact_id, '_email_address', TRUE) != '' ) ? get_post_meta($contact_id, '_email_address', TRUE) : '-' );
            echo '</p>';
        }
        else
        {
            echo __( 'Invalid contact record', 'propertyhive' );
        }
        
        echo '<p class="form-field">';
            echo '<label></label>';
            echo '<a href="" class="button" id="remove-owner-contact-' . $contact_id . '">Remove Owner</a> ';
            echo '<a href="" class="button add-additional-owner-contact">Add Additional Owner</a>';
        echo '</p>';

        echo '</div>';
        
        // Quit out
        die();
        
    }
    
    /**
     * Search contacts via ajax
     */
    public function search_contacts() {
        
        global $post;
        
        check_ajax_referer( 'search-contacts', 'security' );
        
        $return = array();
        
        $keyword = ph_clean($_POST['keyword']);
        
        if ( !empty( $keyword ) && strlen( $keyword ) > 2 )
        {
            // Get all contacts that match the name
            $args = array(
                'post_type' => 'contact',
                'nopaging' => true,
                'post_status' => array( 'publish' ),
                'fields' => 'ids'
            );
            if ( isset($_POST['contact_type']) && $_POST['contact_type'] != '' )
            {
                $args['meta_query'] = array(
                    array(
                        'key' => '_contact_types',
                        'value' => ph_clean($_POST['contact_type']),
                        'compare' => 'LIKE',
                    )
                );
            }
            
            add_filter( 'posts_where', array( $this, 'search_contacts_where' ), 10, 2 );
            
            $contact_query = new WP_Query( $args );
            
            remove_filter( 'posts_where', array( $this, 'search_contacts_where' ) );
            
            if ( $contact_query->have_posts() )
            {
                while ( $contact_query->have_posts() )
                {
                    $contact_query->the_post();

                    $contact = new PH_Contact( get_the_ID() );
                    
                    $return[] = array(
                        'ID' => get_the_ID(),
                        'post_title' => get_the_title(get_the_ID()),
                        'address_name_number' => $contact->_address_name_number,
                        'address_street' => $contact->_address_street,
                        'address_two' => $contact->_address_two,
                        'address_three' => $contact->_address_three,
                        'address_four' => $contact->_address_four,
                        'address_postcode' => $contact->_address_postcode,
                        'address_country' => $contact->_address_country,
                        'address_full_formatted' => $contact->get_formatted_full_address('<br>'),
                    );
                }
            }
            
            wp_reset_postdata();
        }
        
        $this->json_headers();
        echo json_encode( $return );
        
        // Quit out
        die();
    }
    
    public function search_contacts_where( $where, &$wp_query )
    {
        global $wpdb;
        
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( like_escape( ph_clean($_POST['keyword']) ) ) . '%\'';
        
        return $where;
    }

    /**
     * Search propertie via ajax
     */
    public function search_properties() {
        
        global $post;
        
        check_ajax_referer( 'search-properties', 'security' );
        
        $return = array();
        
        $keyword = ph_clean($_POST['keyword']);
        
        if ( !empty( $keyword ) && strlen( $keyword ) > 2 )
        {
            // Get all contacts that match the name
            $args = array(
                'post_type' => 'property',
                'nopaging' => true,
                'post_status' => array( 'publish' ),
                'fields' => 'ids'
            );

            $meta_query = array();
            if ( isset($_POST['department']) && $_POST['department'] != '' )
            {
                $meta_query[] = array(
                    'key' => '_department',
                    'value' => ph_clean($_POST['department']),
                );
            }
            if ( !empty($meta_query) )
            {
                $args['meta_query'] = $meta_query;
            }

            add_filter( 'posts_join', array( $this, 'search_properties_join' ), 10, 2 );
            add_filter( 'posts_where', array( $this, 'search_properties_where' ), 10, 2 );
            
            $property_query = new WP_Query( $args );
            
            remove_filter( 'posts_join', array( $this, 'search_properties_join' ) );
            remove_filter( 'posts_where', array( $this, 'search_properties_where' ) );
            
            if ( $property_query->have_posts() )
            {
                while ( $property_query->have_posts() )
                {
                    $property_query->the_post();

                    $property = new PH_Property(get_the_ID());

                    $owner_id = $property->_owner_contact_id;
                    $owner_name = '';
                    if ( ( is_array($owner_id) && !empty($owner_id) ) || ( !is_array($owner_id) && $owner_id != '' ) )
                    {
                        if ( is_array($owner_id) )
                        {
                            $owner_id = reset($owner_id);
                        }
                        $owner_name = get_the_title($owner_id);
                    }
                    
                    $return[] = array(
                        'ID' => get_the_ID(),
                        'post_title' => $property->get_formatted_full_address(),
                        'owner_id' => $owner_id,
                        'owner_name' => $owner_name
                    );
                }
            }
            
            wp_reset_postdata();
        }
        
        $this->json_headers();
        echo json_encode( $return );
        
        // Quit out
        die();
    }

    public function search_properties_join( $joins )
    {
        global $wpdb;

        $joins .= " INNER JOIN {$wpdb->postmeta} AS mt1 ON {$wpdb->posts}.ID = mt1.post_id ";

        return $joins;
    }

    public function search_properties_where( $where )
    {
        $where .= " AND (
            (mt1.meta_key='_address_name_number' AND mt1.meta_value LIKE '" . esc_sql(ph_clean($_POST['keyword'])) . "%')
            OR
            (mt1.meta_key='_address_street' AND mt1.meta_value LIKE '" . esc_sql(ph_clean($_POST['keyword'])) . "%')
            OR
            (mt1.meta_key='_address_2' AND mt1.meta_value LIKE '" . esc_sql(ph_clean($_POST['keyword'])) . "%')
            OR
            (mt1.meta_key='_address_3' AND mt1.meta_value LIKE '" . esc_sql(ph_clean($_POST['keyword'])) . "%')
            OR
            (mt1.meta_key='_address_4' AND mt1.meta_value LIKE '" . esc_sql(ph_clean($_POST['keyword'])) . "%')
            OR
            (mt1.meta_key='_address_postcode' AND mt1.meta_value LIKE '" . esc_sql(ph_clean($_POST['keyword'])) . "%')
            OR
            (mt1.meta_key='_reference_number' AND mt1.meta_value = '" . esc_sql(ph_clean($_POST['keyword'])) . "')
        ) ";
        
        return $where;
    }

    /**
     * Search users/negotiators via ajax
     */
    public function search_negotiators() {
        
        global $post;
        
        check_ajax_referer( 'search-negotiators', 'security' );
        
        $return = array();
        
        $keyword = ph_clean($_POST['keyword']);
        
        if ( !empty( $keyword ) && strlen( $keyword ) > 2 )
        {
            // Get all contacts that match the name
            $args = array(
                'number' => 9999,
                'search' => $keyword . '*',
                'orderby' => 'display_name',
                'role__not_in' => array('property_hive_contact') 
            );
            
            $user_query = new WP_User_Query( $args );

            // Get the results
            $users = $user_query->get_results();

            if ( !empty($users) )
            {
                foreach ($users as $user)
                {
                    $user_data = get_userdata($user->ID);

                    $return[] = array(
                        'ID' => $user->ID,
                        'post_title' => $user_data->display_name
                    );
                }
            }
        }
        
        $this->json_headers();
        echo json_encode( $return );
        
        // Quit out
        die();
    }
    
	/**
	 * Add note via ajax
	 */
	public function add_note() {
    
		check_ajax_referer( 'add-note', 'security' );
        
		$post_id = (int)$_POST['post_id'];

		if ( $post_id > 0 ) {

            $current_user = wp_get_current_user();

            $note = wp_kses_post( trim( stripslashes( $_POST['note'] ) ) );

            // Add note/comment to property
            $comment = array(
                'note_type' => 'note',
                'note' => $note
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );

            if ($comment_id !== FALSE)
            {            
                $comment = get_comment($comment_id);

?>
                <li rel="<?php echo absint( $comment_id ) ; ?>" class="note">
                    <div class="note_content">
                        <?php echo wpautop( wptexturize( wp_kses_post( $note ) ) ); ?>
                    </div>
                    <p class="meta">
                        <abbr class="exact-date" title="<?php echo $comment->comment_date_gmt; ?> GMT"><?php printf( __( '%s ago', 'propertyhive' ), human_time_diff( strtotime( $comment->comment_date_gmt ), current_time( 'timestamp', 1 ) ) ); ?></abbr>
                        <?php if ( $comment->comment_author !== __( 'Property Hive', 'propertyhive' ) ) printf( ' ' . __( 'by %s', 'propertyhive' ), $comment->comment_author ); ?>
                        <a href="#" class="delete_note"><?php _e( 'Delete', 'propertyhive' ); ?></a>
                    </p>
                </li>
<?php
            }
		}

		// Quit out
		die();
	}

	/**
	 * Delete order note via ajax
	 */
	public function delete_note() {

		check_ajax_referer( 'delete-note', 'security' );

		$note_id = (int)$_POST['note_id'];

		if ( $note_id > 0 ) {
			wp_delete_comment( $note_id );
		}

		// Quit out
		die();
	}
    
    /**
     * Delete order note via ajax
     */
    public function make_property_enquiry() {
        
        global $post;
        
        $return = array();
        
        // Validate
        $errors = array();
        $form_controls = array();

        if ( ! isset( $_POST['property_id'] ) || ( isset( $_POST['property_id'] ) && empty( $_POST['property_id'] ) ) )
        {
            $errors[] = __( 'Property ID is a required field and must be supplied when making an enquiry', 'propertyhive' ) . ': ' . $key;
        }
        else
        {
            //$post = get_post((int)$_POST['property_id']);
            
            $form_controls = ph_get_property_enquiry_form_fields();

            $form_controls = apply_filters( 'propertyhive_property_enquiry_form_fields', $form_controls );
        }
        
        foreach ( $form_controls as $key => $control )
        {
            if ( isset( $control ) && isset( $control['required'] ) && $control['required'] === TRUE )
            {
                // This field is mandatory. Lets check we received it in the post
                if ( ! isset( $_POST[$key] ) || ( isset( $_POST[$key] ) && empty( $_POST[$key] ) ) )
                {
                    $errors[] = __( 'Missing required field', 'propertyhive' ) . ': ' . $key;
                }
            }
            if ( isset( $control['type'] ) && $control['type'] == 'email' && isset( $_POST[$key] ) && ! empty( $_POST[$key] ) && ! is_email( $_POST[$key] ) )
            {
                $errors[] = __( 'Invalid email address provided', 'propertyhive' ) . ': ' . $key;
            }
            if ( $key == 'recaptcha' )
            {
                $secret = isset( $control['secret'] ) ? $control['secret'] : '';
                $response = isset( $_POST['g-recaptcha-response'] ) ? ph_clean($_POST['g-recaptcha-response']) : '';

                $response = wp_remote_post( 
                    'https://www.google.com/recaptcha/api/siteverify',
                    array(
                        'method' => 'POST',
                        'body' => array( 'secret' => $secret, 'response' => $response ),
                    )
                );
                if ( is_wp_error( $response ) ) 
                {
                    $errors[] = $response->get_error_message();
                }
                else
                {
                    $response = json_decode($response['body'], TRUE);
                    if ( $response === FALSE )
                    {
                        $errors[] = 'Error decoding response from reCAPTCHA check';
                    }
                    else
                    {
                        if ( isset($response['success']) && $response['success'] == true )
                        {

                        }
                        else
                        {
                            $errors[] = 'Failed reCAPTCHA validation';
                        }
                    }
                }
            }
        }
        
        if ( !empty($errors) )
        {
            // Failed validation
            
            $return['success'] = false;
            $return['reason'] = 'validation';
            $return['errors'] = $errors;
        }
        else
        {
            // Passed validation
            $property_ids = explode("|", ph_clean($_POST['property_id']));
            
            // Get recipient email address
            $to = '';
            
            // Try and get office's email address first, else fallback to admin email
            $office_id = get_post_meta((int)$property_ids[0], '_office_id', TRUE);
            if ( $office_id != '' )
            {
                if ( get_post_type( $office_id ) == 'office' )
                {
                    $property_department = get_post_meta((int)$property_ids[0], '_department', TRUE);
                    
                    $fields_to_check = array();
                    switch ( $property_department )
                    {
                        case "residential-sales":
                        {
                            $fields_to_check[] = '_office_email_address_sales';
                            $fields_to_check[] = '_office_email_address_lettings';
                            $fields_to_check[] = '_office_email_address_commercial';
                            break;
                        }
                        case "residential-lettings":
                        {
                            $fields_to_check[] = '_office_email_address_lettings';
                            $fields_to_check[] = '_office_email_address_sales';
                            $fields_to_check[] = '_office_email_address_commercial';
                            break;
                        }
                        case "commercial":
                        {
                            $fields_to_check[] = '_office_email_address_commercial';
                            $fields_to_check[] = '_office_email_address_lettings';
                            $fields_to_check[] = '_office_email_address_sales';
                            break;
                        }
                    }
                    
                    foreach ( $fields_to_check as $field_to_check )
                    {
                        $to = get_post_meta($office_id, $field_to_check, TRUE);
                        if ( $to != '' )
                        {
                            break;
                        }
                    }
                }
            }
            if ( $to == '' )
            {
                $to = get_option( 'admin_email' );
            }
            
            if ( count($property_ids) == 1 )
            {
                $subject = __( 'New Property Enquiry', 'propertyhive' ) . ': ' . get_the_title( (int)$property_ids[0] );
            }
            else
            {
                $subject = __( 'Multiple Property Enquiry', 'propertyhive' ) . ': ' . count($property_ids) . ' Properties';
            }
            $message = __( "You have received a property enquiry via your website. Please find details of the enquiry below", 'propertyhive' ) . "\n\n";
            
            $message = apply_filters( 'propertyhive_property_enquiry_pre_body', $message, $property_ids );

            $message .= __( 'Properties', 'propertyhive' ) . ":\n";
            foreach ( $property_ids as $property_id )
            {
                $message .= get_the_title( (int)$property_id ) . " (" . get_permalink( (int)$property_id ) . ")\n";
            }
            $message .= "\n";

            unset($form_controls['action']);
            unset($_POST['action']);
            unset($form_controls['property_id']); // Unset so the field doesn't get shown in the enquiry details
            
            $form_controls = apply_filters( 'propertyhive_property_enquiry_body_form_fields', $form_controls );

            foreach ($form_controls as $key => $control)
            {
                if ( isset($control['type']) && $control['type'] == 'html' ) { continue; }

                $label = ( isset($control['label']) ) ? $control['label'] : $key;
                $label = ( isset($control['email_label']) ) ? $control['email_label'] : $label;
                $value = ( isset($_POST[$key]) ) ? sanitize_textarea_field($_POST[$key]) : '';

                $message .= strip_tags($label) . ": " . strip_tags($value) . "\n";
            }
            
            $from_email_address = get_option('propertyhive_email_from_address', '');
            if ( $from_email_address == '' )
            {
                $from_email_address = get_option('admin_email');
            }
            if ( $from_email_address == '' )
            {
                // Should never get here
                $from_email_address = $_POST['email_address'];
            }

            $headers = array();
            if ( isset($_POST['name']) && ! empty($_POST['name']) )
            {
                $headers[] = 'From: ' . ph_clean( $_POST['name'] ) . ' <' . sanitize_email( $from_email_address ) . '>';
            }
            else
            {
                $headers[] = 'From: <' . sanitize_email( $from_email_address ) . '>';
            }
            if ( isset($_POST['email_address']) && sanitize_email( $_POST['email_address'] ) != '' )
            {
                $headers[] = 'Reply-To: ' . sanitize_email( $_POST['email_address'] );
            }

            $to = apply_filters( 'propertyhive_property_enquiry_to', $to, $property_ids );
            $subject = apply_filters( 'propertyhive_property_enquiry_subject', $subject, $property_ids );
            $message = apply_filters( 'propertyhive_property_enquiry_body', $message, $property_ids );
            $headers = apply_filters( 'propertyhive_property_enquiry_headers', $headers, $property_ids );

            $sent = wp_mail( $to, $subject, $message, $headers );
            
            if ( ! $sent )
            {
                $return['success'] = false;
                $return['reason'] = 'nosend';
                $return['errors'] = $errors;
            }
            else
            {
                $return['success'] = true;
                
                if ( get_option( 'propertyhive_store_property_enquiries', 'yes' ) == 'yes' )
                {
                    // Now insert into enquiries section of WordPress
                    if ( count($property_ids) == 1 )
                    {
                        $title = __( 'Property Enquiry', 'propertyhive' ) . ': ' . get_the_title( (int)$property_ids[0] );
                    }
                    else
                    {
                        $title = __( 'Multiple Property Enquiry', 'propertyhive' );
                    }
                    if ( isset($_POST['name']) && ! empty($_POST['name']) )
                    {
                        $title .= __( ' from ', 'propertyhive' ) . ph_clean($_POST['name']);
                    }
                    
                    $enquiry_post = array(
                      'post_title'    => $title,
                      'post_content'  => '',
                      'post_type'  => 'enquiry',
                      'post_status'   => 'publish',
                      'comment_status'    => 'closed',
                      'ping_status'    => 'closed',
                    );
                    
                    // Insert the post into the database
                    $enquiry_post_id = wp_insert_post( $enquiry_post );
                    
                    add_post_meta( $enquiry_post_id, '_status', 'open' );
                    add_post_meta( $enquiry_post_id, '_source', 'website' );
                    add_post_meta( $enquiry_post_id, '_negotiator_id', '' );
                    add_post_meta( $enquiry_post_id, '_office_id', $office_id );
                    
                    foreach ($_POST as $key => $value)
                    {
                        if ( $key == 'property_id' )
                        {
                            foreach ( $property_ids as $property_id )
                            {
                                add_post_meta( $enquiry_post_id, $key, (int)$property_id );
                            }
                        }
                        else
                        {
                            add_post_meta( $enquiry_post_id, $key, sanitize_textarea_field($value) );
                        }
                    }
                }

                // Send auto-responder
                if ( get_option( 'propertyhive_enquiry_auto_responder', '' ) == 'yes' )
                {
                    // Auto-responder enabled
                    PH()->email->send_enquiry_auto_responder( $_POST );
                }

                do_action('propertyhive_property_enquiry_sent', $_POST, $to);
            }
        }
        
        $this->json_headers();
        echo json_encode( $return );
        
        // Quit out
        die();
    }

    /**
     * Create contact from enquiry
     */
    public function create_contact_from_enquiry()
    {
        global $post;

        $enquiry_post_id = ( (isset($_POST['post_id'])) ? (int)$_POST['post_id'] : '' );
        $nonce = ( (isset($_POST['security'])) ? ph_clean($_POST['security']) : '' );

        if ( ! wp_verify_nonce( $nonce, 'create-content-from-enquiry-nonce-' . $enquiry_post_id ) ) 
        {
            // This nonce is not valid.
            die( json_encode( array('error' => 'Invalid nonce. Please refresh and try again') ) ); 
        }

        $enquiry_meta = get_metadata( 'post', $enquiry_post_id );

        $name = false;
        $email = false;
        $telephone = false;

        foreach ($enquiry_meta as $key => $value)
        {
            if ( strpos($key, 'name') !== false )
            {
                $name = $value[0];
            }
            elseif ( strpos($key, 'email') !== false )
            {
                $email = $value[0];
            }
            elseif ( strpos($key, 'telephone') !== false )
            {
                $telephone = $value[0];
            }
        }

        if ( $name === false || $email === false )
        {
            // This nonce is not valid.
            die( json_encode( array('error' => 'Name or email address not found') ) );
        }

        // We've not imported this property before
        $postdata = array(
            'post_excerpt'   => '',
            'post_content'   => '',
            'post_title'     => utf8_encode(wp_strip_all_tags( $name )),
            'post_status'    => 'publish',
            'post_type'      => 'contact',
            'ping_status'    => 'closed',
            'comment_status' => 'closed',
        );

        $contact_post_id = wp_insert_post( $postdata, true );

        if ( is_wp_error( $contact_post_id ) ) 
        {
            die( json_encode( array('error' => 'Error creating contact') ) );
        }
        elseif ( $contact_post_id == 0 )
        {
            die( json_encode( array('error' => 'Error creating contact') ) );
        }

        if ( $telephone !== FALSE ) { 
            update_post_meta( $contact_post_id, '_telephone_number', ph_clean( $telephone ) );
            update_post_meta( $contact_post_id, '_telephone_number', ph_clean( ph_clean_telephone_number( $telephone ) ) );
        }

        if ( $email !== FALSE ) { update_post_meta( $contact_post_id, '_email_address', ph_clean( $email ) ); }

        do_action('propertyhive_create_contact_from_enquiry', $enquiry_post_id, $contact_post_id);

        die( json_encode( array('success' => get_edit_post_link($contact_post_id, '')) ) );
    }

    public function validate_save_contact()
    {
        global $post;

        check_ajax_referer( 'contact-save-validation', 'security' );

        $this->json_headers();

        parse_str($_POST['form_data']);
        
        $return = array('errors' => array());

        if ( isset($_email_address) && $_email_address != '' )
        {
            $email_addresses = explode( ",", $_email_address );

            foreach ( $email_addresses as $email_address )
            {
                $email_address = trim( $email_address );

                if ( !is_email($email_address) )
                {
                    $return['errors'][] = __( 'Email address is invalid', 'propertyhive' ) . ' - ' . $email_address;
                }

                // Check it doesn't exist already
                $args = array(
                    'post_type' => 'contact',
                    'post_status' => 'any',
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => '_email_address',
                            'value' => $email_address,
                            'compare' => '='
                        ),
                        array(
                            'key' => '_email_address',
                            'value' => ',' . $email_address,
                            'compare' => 'LIKE'
                        )
                        ,
                        array(
                            'key' => '_email_address',
                            'value' => $email_address . ',',
                            'compare' => 'LIKE'
                        )
                    )
                );
                if ( isset($post_ID) && $post_ID != '' )
                {
                    $args['post__not_in'] = array( $post_ID );
                }

                $contact_query = new WP_Query( $args );

                if ( $contact_query->have_posts() )
                {
                    while ( $contact_query->have_posts() )
                    {
                        $contact_query->the_post();

                        $return['errors'][] = __( 'A contact, ' . get_the_title() . ', already exists with email address', 'propertyhive' ) . ' ' . $email_address;
                    }
                }
            }
        }

        echo json_encode($return);

        die();
    }

    // Dashboard related functions
    public function get_news()
    {
        $this->json_headers();

        include_once( ABSPATH . WPINC . '/feed.php' );

        $return = array();

        // Get a SimplePie feed object from the specified feed source.
        $rss = fetch_feed( 'https://wp-property-hive.com/category/property-hive-news/feed/' );

        $maxitems = 0;

        if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly

            // Figure out how many total items there are, but limit it to 5. 
            $maxitems = $rss->get_item_quantity( 5 ); 

            // Build an array of all the items, starting with element 0 (first element).
            $rss_items = $rss->get_items( 0, $maxitems );

            foreach ( $rss_items as $item )
            {
                $return[] = array(
                    'title' => esc_html( $item->get_title() ),
                    'permalink' => esc_url( $item->get_permalink() ),
                    'date' => $item->get_date('F d, Y')
                );
            }

        endif;

        echo json_encode($return);

        die();
    }

    public function get_viewings_awaiting_applicant_feedback()
    {
        global $post;

        $this->json_headers();

        $return = array();

        $args = array(
            'post_type' => 'viewing',
            'fields' => 'ids',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_status',
                    'value' => 'carried_out'
                ),
                array(
                    'key' => '_feedback_status',
                    'value' => ''
                )
            )
        );

        $viewings_query = new WP_Query( $args );

        if ( $viewings_query->have_posts() )
        {
            while ( $viewings_query->have_posts() )
            {
                $viewings_query->the_post();

                $property_id = get_post_meta( get_the_ID(), '_property_id', TRUE );
                $property = new PH_Property((int)$property_id);

                $applicant_contact_id = get_post_meta( get_the_ID(), '_applicant_contact_id', TRUE );

                $return[] = array(
                    'ID' => get_the_ID(),
                    'edit_link' => get_edit_post_link( get_the_ID() ),
                    'start_date_time' => get_post_meta( get_the_ID(), '_start_date_time', TRUE ),
                    'start_date_time_formatted_Hi_jSFY' => date("H:i jS F Y", strtotime(get_post_meta( get_the_ID(), '_start_date_time', TRUE ))),
                    'property_id' => $property_id,
                    'property_address' => $property->get_formatted_full_address(),
                    'applicant_contact_id' => $applicant_contact_id,
                    'applicant_name' => get_the_title( $applicant_contact_id ),
                );
            }
        }

        wp_reset_postdata();

        echo json_encode($return);

        die();
    }

    public function get_appraisal_details_meta_box()
    {
        global $post;

        check_ajax_referer( 'appraisal-details-meta-box', 'security' );

        $post = get_post((int)$_POST['appraisal_id']);

        $appraisal = new PH_Appraisal((int)$_POST['appraisal_id']);

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        echo '<p class="form-field">
        
            <label for="">' . __('Status', 'propertyhive') . '</label>
            
            ' . ucwords(str_replace("_", " ", $appraisal->status));
        
        echo '</p>';

        if ( $appraisal->status == 'cancelled' )
        {
            $args = array( 
                'id' => '_cancelled_reason', 
                'label' => __( 'Reason Cancelled', 'propertyhive' ), 
                'desc_tip' => false, 
                'class' => '',
                'value' => $appraisal->cancelled_reason,
                'custom_attributes' => array(
                    'style' => 'width:95%; max-width:500px;'
                )
            );
            propertyhive_wp_textarea_input( $args );
        }

        if ( $appraisal->status == 'carried_out' || $appraisal->status == 'won' || $appraisal->status == 'instructed' )
        {
            if ( $appraisal->department == 'residential-sales' )
            {
                $args = array( 
                    'id' => '_valued_price', 
                    'label' => __( 'Valued Price', 'propertyhive' ) . ' (&pound;)', 
                    'desc_tip' => false, 
                    'class' => 'short',
                    'value' => $appraisal->valued_price,
                );
                propertyhive_wp_text_input( $args );
            }
            elseif ( $appraisal->department == 'residential-lettings' )
            {
                $rent_frequency = $appraisal->valued_rent_frequency;

                echo '<p class="form-field">
        
                    <label for="">' . __('Valued Rent', 'propertyhive') . ' (&pound;)</label>

                    <input type="text" class="" name="_valued_rent" id="_valued_rent" value="' . $appraisal->valued_rent . '" placeholder="" style="width:10%; min-width:100px;">
                
                    <select id="_valued_rent_frequency" name="_valued_rent_frequency" class="select" style="width:auto">
                        <option value="pppw"' . ( ($rent_frequency == 'pppw') ? ' selected' : '') . '>' . __('Per Person Per Week', 'propertyhive') . '</option>
                        <option value="pw"' . ( ($rent_frequency == 'pw') ? ' selected' : '') . '>' . __('Per Week', 'propertyhive') . '</option>
                        <option value="pcm"' . ( ($rent_frequency == 'pcm' || $rent_frequency == '') ? ' selected' : '') . '>' . __('Per Calendar Month', 'propertyhive') . '</option>
                        <option value="pq"' . ( ($rent_frequency == 'pq') ? ' selected' : '') . '>' . __('Per Quarter', 'propertyhive') . '</option>
                        <option value="pa"' . ( ($rent_frequency == 'pa') ? ' selected' : '') . '>' . __('Per Annum', 'propertyhive') . '</option>
                    </select>

                </p>';
            }
        }

        if ( $appraisal->status == 'lost' )
        {
            $args = array( 
                'id' => '_lost_reason', 
                'label' => __( 'Reason Lost', 'propertyhive' ), 
                'desc_tip' => false, 
                'class' => '',
                'value' => $appraisal->lost_reason,
                'custom_attributes' => array(
                    'style' => 'width:95%; max-width:500px;'
                )
            );
            propertyhive_wp_textarea_input( $args );
        }

        do_action('propertyhive_appraisal_details_fields');
        
        echo '</div>';
        
        echo '</div>';

        die();
    }

    public function get_appraisal_actions()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );
        $department = get_post_meta( $post_id, '_department', TRUE );

        echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="propertyhive_appraisal_actions_meta_box">

        <div class="options_group" style="padding-top:8px;">';

        $show_cancelled_meta_boxes = false;
        $show_carried_out_meta_boxes = false;
        $show_instructed_meta_boxes = false;
        $show_lost_meta_boxes = false;

        $actions = array();

        if ( $status == 'pending' )
        {
            /*$actions[] = '<a 
                    href="" 
                    class="button"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Print Market Appraisal Sheet', 'propertyhive') . '</a>';
            if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
            {
                $actions[] = '<a 
                        href="" 
                        class="button"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . __('Run Potential Applicant Match', 'propertyhive') . '</a>';
            }*/
            $actions[] = '<a 
                    href="#action_panel_appraisal_carried_out" 
                    class="button button-success appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Appraisal Carried Out', 'propertyhive') . '</a>';
            $actions[] = '<a 
                    href="#action_panel_appraisal_cancelled" 
                    class="button appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Appraisal Cancelled', 'propertyhive') . '</a>';

            $show_cancelled_meta_boxes = true;
            $show_carried_out_meta_boxes = true;
        }

        if ( $status == 'carried_out' )
        {
            $actions[] = '<a 
                    href="#action_panel_appraisal_won" 
                    class="button button-success appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Appraisal Won', 'propertyhive') . '</a>';

            $actions[] = '<a 
                    href="#action_panel_appraisal_lost" 
                    class="button button-danger appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Appraisal Lost', 'propertyhive') . '</a>';

            $show_lost_meta_boxes = true;
        }

        if ( $status == 'won' )
        {
            $actions[] = '<a 
                    href="#action_panel_appraisal_instruct" 
                    class="button button-success appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Instruct Property', 'propertyhive') . '</a>';

            $show_instructed_meta_boxes = true;
        }

        if ( $status == 'won' || $status == 'lost' )
        {
            $actions[] = '<a 
                    href="#action_panel_appraisal_revert_carried_out" 
                    class="button appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Revert To Carried Out', 'propertyhive') . '</a>';
        }

        if ( $status == 'instructed' )
        {
            $property_id = get_post_meta( $post_id, '_property_id', TRUE );

            $actions[] = '<a 
                    href="' . get_edit_post_link($property_id) . '" 
                    class="button"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('View Instructed Property', 'propertyhive') . '</a>';

            /*$actions[] = '<a 
                    href="#action_panel_appraisal_revert_won" 
                    class="button appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Revert To Won', 'propertyhive') . '</a>';*/
        }

        if ( $status == 'carried_out' || $status == 'cancelled' )
        {
            $actions[] = '<a 
                    href="#action_panel_appraisal_revert_pending" 
                    class="button appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Revert To Pending', 'propertyhive') . '</a>';
        }

        $actions = apply_filters( 'propertyhive_admin_appraisal_actions', $actions, $post_id );

        if ( !empty($actions) )
        {
            echo implode("", $actions);
        }
        else
        {
            echo '<div style="text-align:center">' . __( 'No actions to display', 'propertyhive' ) . '</div>';
        }

        echo '</div>

        </div>';

        if ( $show_cancelled_meta_boxes )
        {
            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_appraisal_cancelled" style="display:none;">

                <div class="options_group" style="padding-top:8px;">

                    <div class="form-field">

                        <label for="_appraisal_cancelled_reason">' . __( 'Reason Cancelled', 'propertyhive' ) . '</label>
                        
                        <textarea id="_cancelled_reason" name="_cancelled_reason" style="width:100%;">' . get_post_meta( $post_id, '_cancelled_reason', TRUE ) . '</textarea>

                    </div>

                    <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
                    <a class="button button-primary cancelled-reason-action-submit" href="#">' . __( 'Save', 'propertyhive' ) . '</a>

                </div>

            </div>';
        }

        if ( $show_carried_out_meta_boxes )
        {
            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_appraisal_carried_out" style="display:none;">

                <div class="options_group" style="padding-top:8px;">';

            if ( $department == 'residential-sales' )
            {
                echo '<div class="form-field">

                        <label for="_price">' . __( 'Valued Price (&pound;)', 'propertyhive' ) . '</label>
                        
                        <input type="text" id="_price" name="_price" style="width:100%;" value="' . get_post_meta( $post_id, '_valued_price', TRUE ) . '">

                    </div>';
            }
            else
            {
                $rent_frequency = get_post_meta( $post_id, '_valued_rent_frequency', TRUE );
                echo '<div class="form-field">

                        <label for="_price">' . __( 'Valued Rent (&pound;)', 'propertyhive' ) . '</label>
                        
                        <input type="text" id="_price" name="_price" style="width:100%;" value="' . get_post_meta( $post_id, '_valued_rent', TRUE ) . '">

                        <select id="_rent_frequency" name="_rent_frequency" class="select" style="width:100%">
                            <option value="pppw"' . ( ($rent_frequency == 'pppw') ? ' selected' : '') . '>' . __('Per Person Per Week', 'propertyhive') . '</option>
                            <option value="pw"' . ( ($rent_frequency == 'pw') ? ' selected' : '') . '>' . __('Per Week', 'propertyhive') . '</option>
                            <option value="pcm"' . ( ($rent_frequency == 'pcm' || $rent_frequency == '') ? ' selected' : '') . '>' . __('Per Calendar Month', 'propertyhive') . '</option>
                            <option value="pq"' . ( ($rent_frequency == 'pq') ? ' selected' : '') . '>' . __('Per Quarter', 'propertyhive') . '</option>
                            <option value="pa"' . ( ($rent_frequency == 'pa') ? ' selected' : '') . '>' . __('Per Annum', 'propertyhive') . '</option>
                        </select>

                    </div>';
            }

            echo '<a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
                    <a class="button button-primary carried-out-action-submit" href="#">' . __( 'Save', 'propertyhive' ) . '</a>

                </div>

            </div>';
        }

        if ( $show_instructed_meta_boxes )
        {
            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_appraisal_instruct" style="display:none;">

                <div class="options_group" style="padding-top:8px;">';

                echo '<div style="margin-bottom:13px;">' . __( 'Upon instruction a new property record will be created within the \'Properties\' area.', 'propertyhive' ) . '</div>';

            echo '<a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
                    <a class="button button-primary instructed-action-submit" href="#">' . __( 'OK', 'propertyhive' ) . '</a>

                </div>

            </div>';
        }

        if ( $show_lost_meta_boxes )
        {
            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_appraisal_lost" style="display:none;">

                <div class="options_group" style="padding-top:8px;">

                    <div class="form-field">

                        <label for="_lost_reason">' . __( 'Reason Lost', 'propertyhive' ) . '</label>
                        
                        <textarea id="_lost_reason" name="_lost_reason" style="width:100%;">' . get_post_meta( $post_id, '_lost_reason', TRUE ) . '</textarea>

                    </div>

                    <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
                    <a class="button button-primary lost-reason-action-submit" href="#">' . wp_kses_post( __( 'Save Reason Lost', 'propertyhive' ) ) . '</a>

                </div>

            </div>';
        }

        die();
    }

    public function appraisal_carried_out()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'pending' )
        {
            update_post_meta( $post_id, '_status', 'carried_out' );

            if ( get_post_meta( $post_id, '_department', TRUE ) == 'residential-sales' )
            {
                $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['price']));
                update_post_meta( $post_id, '_valued_price', $price );
                update_post_meta( $post_id, '_valued_price_actual', $price );
            }
            elseif ( get_post_meta( $post_id, '_department', TRUE ) == 'residential-lettings' )
            {
                $rent = preg_replace("/[^0-9]/", '', ph_clean($_POST['rent']));
                update_post_meta( $post_id, '_valued_rent', $rent );

                update_post_meta( $post_id, '_valued_rent_frequency', ph_clean($_POST['rent_frequency']) );

                switch (ph_clean($_POST['rent_frequency']))
                {
                    case "pppw":
                    {
                        $bedrooms = get_post_meta( $postID, '_bedrooms', true );
                        if ( ( $bedrooms !== FALSE && $bedrooms != 0 && $bedrooms != '' ) && apply_filters( 'propertyhive_pppw_to_consider_bedrooms', true ) == true )
                        {
                            $price = (($rent * 52) / 12) * $bedrooms;
                        }
                        else
                        {
                            $price = ($rent * 52) / 12;
                        }
                        break;
                    }
                    case "pw": { $price = ($rent * 52) / 12; break; }
                    case "pcm": { $price = $rent; break; }
                    case "pq": { $price = ($rent * 4) / 12; break; }
                    case "pa": { $price = ($rent / 12); break; }
                }
                update_post_meta( $post_id, '_valued_price_actual', $price );
            }

            $current_user = wp_get_current_user();

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_carried_out',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function appraisal_cancelled()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'pending' )
        {
            update_post_meta( $post_id, '_status', 'cancelled' );
            update_post_meta( $post_id, '_cancelled_reason', sanitize_textarea_field( $_POST['cancelled_reason'] ) );

            $current_user = wp_get_current_user();

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_cancelled',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function appraisal_won()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' )
        {
            update_post_meta( $post_id, '_status', 'won' );

            $current_user = wp_get_current_user();

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_won',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function appraisal_lost_reason()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' )
        {
            update_post_meta( $post_id, '_status', 'lost' );
            update_post_meta( $post_id, '_lost_reason', sanitize_textarea_field( $_POST['lost_reason'] ) );

            $current_user = wp_get_current_user();

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_lost',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function appraisal_instructed()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'won' )
        {
            // Create property record and copy everything over
            $display_address = array();
            if ( get_post_meta( $post_id, '_address_street', TRUE ) != '' )
            {
                $display_address[] = get_post_meta( $post_id, '_address_street', TRUE );
            }
            if ( get_post_meta( $post_id, '_address_two', TRUE ) != '' )
            {
                $display_address[] = get_post_meta( $post_id, '_address_two', TRUE );
            }
            if ( get_post_meta( $post_id, '_address_three', TRUE ) != '' )
            {
                $display_address[] = get_post_meta( $post_id, '_address_three', TRUE );
            }
            else
            {
                if ( get_post_meta( $post_id, '_address_four', TRUE ) != '' )
                {
                    $display_address[] = get_post_meta( $post_id, '_address_four', TRUE );
                }
            }
            $display_address = implode(", ", $display_address);

            $property_post = array(
                'post_title'    => ph_clean($display_address),
                'post_content'  => '',
                'post_type'     => 'property',
                'post_status'   => 'publish',
                'comment_status'    => 'closed',
                'ping_status'    => 'closed',
            );
                    
            // Insert the post into the database
            $property_post_id = wp_insert_post( $property_post );

            if ( is_wp_error($property_post_id) || $property_post_id == 0 )
            {
                // Failed. Don't really know at the moment how to handle this

                $return = array('error' => 'Failed to create property post. Please try again');
                //echo json_encode( $return );
                //die();
            }
            else
            {
                // Successfully added property post

                $department = get_post_meta( $post_id, '_department', TRUE );

                update_post_meta( $property_post_id, '_address_name_number', get_post_meta( $post_id, '_address_name_number', TRUE ) );
                update_post_meta( $property_post_id, '_address_street', get_post_meta( $post_id, '_address_street', TRUE ) );
                update_post_meta( $property_post_id, '_address_two', get_post_meta( $post_id, '_address_two', TRUE ) );
                update_post_meta( $property_post_id, '_address_three', get_post_meta( $post_id, '_address_three', TRUE ) );
                update_post_meta( $property_post_id, '_address_four', get_post_meta( $post_id, '_address_four', TRUE ) );
                update_post_meta( $property_post_id, '_address_postcode', get_post_meta( $post_id, '_address_postcode', TRUE ) );
                update_post_meta( $property_post_id, '_address_country', get_post_meta( $post_id, '_address_country', TRUE ) );

                if ( ini_get('allow_url_fopen') )
                {
                    // No lat lng. Let's get it
                    $address_to_geocode = array();
                    if ( get_post_meta( $post_id, '_address_name_number', TRUE ) != '' ) { $address_to_geocode[] = get_post_meta( $post_id, '_address_name_number', TRUE ); }
                    if ( get_post_meta( $post_id, '_address_street', TRUE ) != '' ) { $address_to_geocode[] = get_post_meta( $post_id, '_address_street', TRUE ); }
                    if ( get_post_meta( $post_id, '_address_two', TRUE ) != '' ) { $address_to_geocode[] = get_post_meta( $post_id, '_address_two', TRUE ); }
                    if ( get_post_meta( $post_id, '_address_three', TRUE ) != '' ) { $address_to_geocode[] = get_post_meta( $post_id, '_address_three', TRUE ); }
                    if ( get_post_meta( $post_id, '_address_four', TRUE ) != '' ) { $address_to_geocode[] = get_post_meta( $post_id, '_address_four', TRUE ); }
                    if ( get_post_meta( $post_id, '_address_postcode', TRUE ) ) { $address_to_geocode[] = get_post_meta( $post_id, '_address_postcode', TRUE ); }

                    $country = get_option( 'propertyhive_default_country', 'GB' );
                    $request_url = "https://maps.googleapis.com/maps/api/geocode/xml?address=" . urlencode( implode( ", ", $address_to_geocode ) ) . "&sensor=false&region=gb"; // the request URL you'll send to google to get back your XML feed
                    
                    $api_key = get_option('propertyhive_google_maps_api_key', '');
                    if ( $api_key != '' ) { $request_url .= "&key=" . $api_key; }

                    $response = wp_remote_get($request_url);

                    if ( is_array( $response ) && !is_wp_error( $response ) ) 
                    {
                        $header = $response['headers']; // array of http header lines
                        $body = $response['body']; // use the content

                        $xml = simplexml_load_string($body);

                        if ( $xml !== FALSE )
                        {
                            $status = $xml->status; // Get the request status as google's api can return several responses

                            if ($status == "OK") 
                            {
                                //request returned completed time to get lat / lng for storage
                                $lat = (string)$xml->result->geometry->location->lat;
                                $lng = (string)$xml->result->geometry->location->lng;
                                
                                if ($lat != '' && $lng != '')
                                {
                                    update_post_meta( $post_id, '_latitude', $lat );
                                    update_post_meta( $post_id, '_longitude', $lng );
                                }
                            }
                        }
                    }
                }

                update_post_meta( $property_post_id, '_department', $department );

                switch ( $department )
                {
                    case "residential-sales":
                    {
                        update_post_meta( $property_post_id, '_currency', 'GBP' );

                        $price = preg_replace("/[^0-9]/", '', get_post_meta( $post_id, '_valued_price', TRUE ));
                        update_post_meta( $property_post_id, '_price', $price );
                        
                        break;
                    }
                    case "residential-lettings":
                    {
                        update_post_meta( $property_post_id, '_currency', 'GBP' );

                        $rent = preg_replace("/[^0-9.]/", '', get_post_meta( $post_id, '_valued_rent', TRUE ));
                        update_post_meta( $property_post_id, '_rent', $rent );
                        update_post_meta( $property_post_id, '_rent_frequency', get_post_meta( $post_id, '_valued_rent_frequency', TRUE ) );

                        break;
                    }
                }

                // Store price in common currency (GBP) used for ordering
                $ph_countries = new PH_Countries();
                $ph_countries->update_property_price_actual( $property_post_id );

                update_post_meta( $property_post_id, '_bedrooms', get_post_meta( $post_id, '_bedrooms', TRUE ) );
                update_post_meta( $property_post_id, '_bathrooms', get_post_meta( $post_id, '_bathrooms', TRUE ) );
                update_post_meta( $property_post_id, '_reception_rooms', get_post_meta( $post_id, '_reception_rooms', TRUE ) );

                update_post_meta( $property_post_id, '_on_market', '' );
                update_post_meta( $property_post_id, '_featured', '' );

                // Taxonomies
                wp_set_object_terms( $property_post_id, wp_get_object_terms( $post_id, 'property_type', array("fields" => "ids") ), 'property_type' );
                wp_set_object_terms( $property_post_id, wp_get_object_terms( $post_id, 'parking', array("fields" => "ids") ), 'parking' );
                wp_set_object_terms( $property_post_id, wp_get_object_terms( $post_id, 'outside_space', array("fields" => "ids") ), 'outside_space' );

                $owner_contact_ids = get_post_meta( $post_id, '_property_owner_contact_id', TRUE );
                if ( !is_array($owner_contact_ids) )
                {
                    $owner_contact_ids = array($owner_contact_ids);
                }
                update_post_meta( $property_post_id, '_owner_contact_id', $owner_contact_ids );

                // Make updates to appraisal
                update_post_meta( $post_id, '_status', 'instructed' );
                update_post_meta( $post_id, '_property_id', $property_post_id );

                //Update owner(s)
                foreach ( $owner_contact_ids as $owner_contact_id )
                {
                    $contact_types = get_post_meta( $owner_contact_id, '_contact_types', TRUE );

                    if ( !in_array('owner', $contact_types) )
                    {
                        $contact_types[] = 'owner';
                    }

                    // get appraisals where this is the owner and where not instructed
                    $args = array(
                        'post_type' => 'appraisal',
                        'nopaging' => true,
                        'meta_query' => array(
                            array(
                                'key' => '_property_owner_contact_id',
                                'value' => $post->ID,
                                'compare' => '='
                            ),
                            array(
                                'key' => '_status',
                                'value' => 'instructed',
                                'compare' => '!='
                            )
                        )
                    );

                    $appraisal_query = new WP_Query($args);
                    
                    if (!$appraisal_query->have_posts())
                    {
                        // no longer a potential owner. 
                        if (($key = array_search('potentialowner', $contact_types)) !== false) 
                        {
                            unset($contact_types[$key]);
                        }
                    }
                    wp_reset_postdata();

                    update_post_meta( $owner_contact_id, '_contact_types', $contact_types );
                }

                $current_user = wp_get_current_user();

                // Add note/comment to appraisal
                $comment = array(
                    'note_type' => 'action',
                    'action' => 'appraisal_instructed',
                );

                $data = array(
                    'comment_post_ID'      => $post_id,
                    'comment_author'       => $current_user->display_name,
                    'comment_author_email' => 'propertyhive@noreply.com',
                    'comment_author_url'   => '',
                    'comment_date'         => date("Y-m-d H:i:s"),
                    'comment_content'      => serialize($comment),
                    'comment_approved'     => 1,
                    'comment_type'         => 'propertyhive_note',
                );
                $comment_id = wp_insert_comment( $data );
            }
        }

        die();
    }

    public function appraisal_revert_pending()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' || $status == 'cancelled' )
        {
            update_post_meta( $post_id, '_status', 'pending' );

            $current_user = wp_get_current_user();

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_revert_pending',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function appraisal_revert_carried_out()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'won' || $status == 'lost' )
        {
            update_post_meta( $post_id, '_status', 'carried_out' );

            $current_user = wp_get_current_user();

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_revert_carried_out',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function appraisal_revert_won()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'instructed' )
        {
            update_post_meta( $post_id, '_status', 'won' );

            $current_user = wp_get_current_user();

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_revert_won',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    // Viewing related functions
    public function book_viewing_property()
    {
        check_ajax_referer( 'book-viewing', 'security' );

        $this->json_headers();

        // TO DO: Should do validation on server side also
        if (empty($_POST['property_id']))
        {
            $return = array('error' => 'No property selected');
            echo json_encode( $return );
            die();
        }

        $property = new PH_Property((int)$_POST['property_id']);
        
        $applicant_contact_ids = array();

        // Create applicant record if required
        if (empty($_POST['applicant_ids']) && !empty($_POST['applicant_name']))
        {
            // Need to create contact/applicant
            $contact_post = array(
                'post_title'    => ph_clean($_POST['applicant_name']),
                'post_content'  => '',
                'post_type'     => 'contact',
                'post_status'   => 'publish',
                'comment_status'    => 'closed',
                'ping_status'    => 'closed',
            );
                    
            // Insert the post into the database
            $contact_post_id = wp_insert_post( $contact_post );

            if ( is_wp_error($contact_post_id) || $contact_post_id == 0 )
            {
                $return = array('error' => 'Failed to create contact post. Please try again');
                echo json_encode( $return );
                die();
            }

            update_post_meta( $contact_post_id, '_contact_types', array('applicant') );

            update_post_meta( $contact_post_id, '_applicant_profiles', 1 );
            update_post_meta( $contact_post_id, '_applicant_profile_0', array( 'department' => $property->department, 'send_matching_properties' => '' ) );

            $applicant_contact_ids[] = $contact_post_id;
        }

        if (!empty($_POST['applicant_ids']) && empty($_POST['applicant_name']))
        {
            // This is an existing contact
            if ( !is_array($_POST['applicant_ids']) )
            {
                $_POST['applicant_ids'] = array(ph_clean($_POST['applicant_ids']));
            }

            foreach ( $_POST['applicant_ids'] as $applicant_id )
            {
                $applicant_contact_ids[] = (int)$applicant_id;
            }
        }

        $applicant_contact_ids = array_unique($applicant_contact_ids);

        if ( empty($applicant_contact_ids) )
        {
            $return = array('error' => 'No applicant selected, or unable to create applicant record');
            echo json_encode( $return );
            die();
        }

        // Make sure each of the contacts has an applicant profile of the correct department
        /*foreach ( $applicant_contact_ids as $applicant_contact_id )
        {
            $has_correct_profile = false;

            // Get all existing profiles
            $existing_contact_types = get_post_meta( $applicant_contact_id, '_contact_types', TRUE );
            if ( $existing_contact_types == '' || !is_array($existing_contact_types) )
            {
                $existing_contact_types = array();
            }
            if ( in_array( 'applicant', $existing_contact_types ) )
            {
                $num_applicant_profiles = get_post_meta( $applicant_contact_id, '_applicant_profiles', TRUE );
                if ( $num_applicant_profiles == '' )
                {
                    $num_applicant_profiles = 0;
                }

                if ( $num_applicant_profiles > 0 ) 
                {
                    for ( $i = 0; $i < $num_applicant_profiles; ++$i )
                    {
                        $applicant_profile = get_post_meta( $applicant_contact_id, '_applicant_profile_' . $i, TRUE );
                        if ( $applicant_profile['department'] == $property->department )
                        {
                            $has_correct_profile = true;
                        }
                    }
                }
            }

            if ( !$has_correct_profile )
            {
                if ( in_array( 'applicant', $existing_contact_types ) )
                {
                    // Already an applicant. Just need to add profile
                }
                else
                {
                    $existing_contact_types[] = 'applicant';
                    update_post_meta( $applicant_contact_id, '_contact_types', $existing_contact_types );
                }

                $num_applicant_profiles = get_post_meta( $applicant_contact_id, '_applicant_profiles', TRUE );
                if ( $num_applicant_profiles == '' )
                {
                    $num_applicant_profiles = 0;
                }

                update_post_meta( $applicant_contact_id, '_applicant_profiles', $num_applicant_profiles + 1 );
                update_post_meta( $applicant_contact_id, '_applicant_profile_' . $num_applicant_profiles, array( 'department' => $property->department ) );
            }
        }*/

        // Loop through contacts and create one viewing each
        // At the moment it's a 1-to-1 relationship, but might support multiple in the future
        foreach ( $applicant_contact_ids as $applicant_contact_id )
        {
            // Insert viewing record
            $viewing_post = array(
                'post_title'    => '',
                'post_content'  => '',
                'post_type'  => 'viewing',
                'post_status'   => 'publish',
                'comment_status'    => 'closed',
                'ping_status'    => 'closed',
            );
                    
            // Insert the post into the database
            $viewing_post_id = wp_insert_post( $viewing_post );

            if ( is_wp_error($viewing_post_id) || $viewing_post_id == 0 )
            {
                $return = array('error' => 'Failed to create viewing post. Please try again');
                echo json_encode( $return );
                die();
            }
            
            add_post_meta( $viewing_post_id, '_start_date_time', ph_clean($_POST['start_date']) . ' ' . ph_clean($_POST['start_time']) );
            add_post_meta( $viewing_post_id, '_duration', 30 * 60 ); // Stored in seconds. Default to 30 mins
            add_post_meta( $viewing_post_id, '_property_id', (int)$_POST['property_id'] );
            add_post_meta( $viewing_post_id, '_applicant_contact_id', $applicant_contact_id );
            add_post_meta( $viewing_post_id, '_status', 'pending' );
            add_post_meta( $viewing_post_id, '_feedback_status', '' );
            add_post_meta( $viewing_post_id, '_feedback', '' );
            add_post_meta( $viewing_post_id, '_feedback_passed_on', '' );

            if ( !empty($_POST['negotiator_ids']) )
            {
                foreach ( $_POST['negotiator_ids'] as $negotiator_id )
                {
                    add_post_meta( $viewing_post_id, '_negotiator_id', (int)$negotiator_id );
                }
            }
        }

        $applicant_contacts = array();
        foreach ( $applicant_contact_ids  as $applicant_contact_id )
        {
            $applicant_contacts[] = array(
                'ID' => $applicant_contact_id,
                'post_title' => get_the_title($applicant_contact_id),
                'edit_link' => get_edit_post_link( $applicant_contact_id, '' ),
            );
        }

        $return = array('success' => array(
            'viewing' => array(
                'ID' => $viewing_post_id,
                'edit_link' => get_edit_post_link( $viewing_post_id, '' ),
            ),
            'applicant_contacts' => $applicant_contacts,
        ));

        echo json_encode( $return );

        die();
    }

    public function book_viewing_contact()
    {
        check_ajax_referer( 'book-viewing', 'security' );

        $this->json_headers();

        // TO DO: Should do validation on server side also
        if (empty($_POST['contact_id']))
        {
            $return = array('error' => 'No contact selected');
            echo json_encode( $return );
            die();
        }

        if (empty($_POST['property_ids']))
        {
            $return = array('error' => 'No property selected');
            echo json_encode( $return );
            die();
        }

        // Loop through contacts and create one viewing each
        // At the moment it's a 1-to-1 relationship, but might support multiple in the future
        foreach ( $_POST['property_ids'] as $property_id )
        {
            // Insert viewing record
            $viewing_post = array(
                'post_title'    => '',
                'post_content'  => '',
                'post_type'  => 'viewing',
                'post_status'   => 'publish',
                'comment_status'    => 'closed',
                'ping_status'    => 'closed',
            );
                    
            // Insert the post into the database
            $viewing_post_id = wp_insert_post( $viewing_post );

            if ( is_wp_error($viewing_post_id) || $viewing_post_id == 0 )
            {
                $return = array('error' => 'Failed to create viewing post. Please try again');
                echo json_encode( $return );
                die();
            }
            
            add_post_meta( $viewing_post_id, '_start_date_time', ph_clean($_POST['start_date']) . ' ' . ph_clean($_POST['start_time']) );
            add_post_meta( $viewing_post_id, '_duration', 30 * 60 ); // Stored in seconds. Default to 30 mins
            add_post_meta( $viewing_post_id, '_property_id', (int)$property_id );
            add_post_meta( $viewing_post_id, '_applicant_contact_id', (int)$_POST['contact_id'] );
            add_post_meta( $viewing_post_id, '_status', 'pending' );
            add_post_meta( $viewing_post_id, '_feedback_status', '' );
            add_post_meta( $viewing_post_id, '_feedback', '' );
            add_post_meta( $viewing_post_id, '_feedback_passed_on', '' );

            if ( !empty($_POST['negotiator_ids']) )
            {
                foreach ( $_POST['negotiator_ids'] as $negotiator_id )
                {
                    add_post_meta( $viewing_post_id, '_negotiator_id', (int)$negotiator_id );
                }
            }
        }

        $properties = array();
        foreach ( $_POST['property_ids'] as $property_id )
        {
            $properties[] = array(
                'ID' => (int)$property_id,
                'post_title' => get_the_title((int)$property_id),
                'edit_link' => get_edit_post_link( (int)$property_id, '' ),
            );
        }

        $return = array('success' => array(
            'viewing' => array(
                'ID' => $viewing_post_id,
                'edit_link' => get_edit_post_link( $viewing_post_id, '' ),
            ),
            'properties' => $properties,
        ));

        echo json_encode( $return );

        die();
    }

    public function get_viewing_details_meta_box()
    {
        global $post;

        check_ajax_referer( 'viewing-details-meta-box', 'security' );

        $post = get_post((int)$_POST['viewing_id']);

        $viewing = new PH_Viewing((int)$_POST['viewing_id']);

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        echo '<p class="form-field">
        
            <label for="">' . __('Status', 'propertyhive') . '</label>
            
            ' . ucwords(str_replace("_", " ", $viewing->status));

        if ( $viewing->status == 'offer_made' )
        {
            if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
            {
                $offer_id = get_post_meta( $viewing->id, '_offer_id', TRUE );
                if ( $offer_id != '' && get_post_status($offer_id) != 'publish' )
                {
                    $offer_id = '';
                }

                if ( $offer_id != '' )
                {
                    echo ' (<a href="' . get_edit_post_link($offer_id) . '">' . __('View Offer', 'propertyhive') . '</a>)';
                }
            }
        }
        
        echo '</p>';

        if ( $viewing->status == 'cancelled' )
        {
            $args = array( 
                'id' => '_cancelled_reason', 
                'label' => __( 'Reason Cancelled', 'propertyhive' ), 
                'desc_tip' => false, 
                'class' => '',
                'value' => $viewing->cancelled_reason,
                'custom_attributes' => array(
                    'style' => 'width:95%; max-width:500px;'
                )
            );
            propertyhive_wp_textarea_input( $args );
        }

        if ( $viewing->status == 'carried_out' )
        {
            echo '<p class="form-field">
        
                <label for="">' . __('Applicant Feedback', 'propertyhive') . '</label>';

            switch ( $viewing->feedback_status )
            {
                case "interested":
                {
                    echo 'Interested';
                    break;
                }
                case "not_interested":
                {
                    echo 'Not Interested';
                    break;
                }
                case "not_required":
                {
                    echo 'Feedback Not Required';
                    break;
                }
                default:
                {
                    echo 'Awaiting Feedback';
                }
            }

            echo '</p>';

            if ( $viewing->feedback_status == 'interested' || $viewing->feedback_status == 'not_interested' )
            {
                $args = array( 
                    'id' => '_feedback', 
                    'label' => __( 'Feedback', 'propertyhive' ), 
                    'desc_tip' => false, 
                    'class' => '',
                    'value' => $viewing->feedback,
                    'custom_attributes' => array(
                        'style' => 'width:95%; max-width:500px;'
                    )
                );
                propertyhive_wp_textarea_input( $args );
            }
        }

        if ( $viewing->status == 'carried_out' && ( $viewing->feedback_status == 'interested' || $viewing->feedback_status == 'not_interested' ) )
        {
            echo '<p class="form-field">
        
                <label for="">' . __('Feedback Passed On', 'propertyhive') . '</label>';

                echo ( ($viewing->feedback_passed_on == 'yes') ? 'Yes' : 'No' );

            echo '</p>';
        }

        do_action('propertyhive_viewing_details_fields');
        
        echo '</div>';
        
        echo '</div>';

        die();
    }

    public function get_viewing_actions()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );
        $feedback_status = get_post_meta( $post_id, '_feedback_status', TRUE );

        echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="propertyhive_viewing_actions_meta_box">

        <div class="options_group" style="padding-top:8px;">';

        $show_cancelled_meta_boxes = false;
        $show_feedback_meta_boxes = false;

        $actions = array();

        if ( $status == 'pending' )
        {
            $applicant_contact_id = get_post_meta( $post_id, '_applicant_contact_id', TRUE );
            $property_id = get_post_meta( $post_id, '_property_id', TRUE );
            $applicant_email_address = get_post_meta( $applicant_contact_id, '_email_address', TRUE );

            if ( (int)$applicant_contact_id == '' || (int)$property_id == '' || (int)$applicant_contact_id == 0 || (int)$property_id == 0 || sanitize_email($applicant_email_address) == '' )
            {

            }
            else
            {
                $applicant_booking_confirmation_sent_at = get_post_meta( $post_id, '_applicant_booking_confirmation_sent_at', TRUE );
                $owner_booking_confirmation_sent_at = get_post_meta( $post_id, '_owner_booking_confirmation_sent_at', TRUE );
                
                //Applicant
                $actions[] = '<a 
                        href="#action_panel_viewing_email_applicant_booking_confirmation" 
                        class="button viewing-action"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . ( ( $applicant_booking_confirmation_sent_at == '' ) ? __('Email Applicant Booking Confirmation', 'propertyhive') : __('Re-Email Applicant Booking Confirmation', 'propertyhive') ) . '</a>';

                $actions[] = '<div id="viewing_applicant_confirmation_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . ( ( $applicant_booking_confirmation_sent_at == '' ) ? 'display:none' : '' ) . '">' . ( ( $applicant_booking_confirmation_sent_at != '' ) ? 'Previously sent to applicant on <span title="' . $applicant_booking_confirmation_sent_at . '">' . date("jS F", strtotime($applicant_booking_confirmation_sent_at)) : '' ) . '</span></div>';

                // Owner/Landlord
                $property_department = get_post_meta( $property_id, '_department', TRUE );
                $owner_contact_ids = get_post_meta( $property_id, '_owner_contact_id', TRUE );
                $owner_or_landlord = ( $property_department == 'residential-lettings' ? 'Landlord' : 'Owner' );

                if ( count($owner_contact_ids) > 0) {

                    $actions[] = '<a 
                            href="#action_panel_viewing_email_owner_booking_confirmation" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $owner_booking_confirmation_sent_at == '' ) ? __('Email ' . $owner_or_landlord . ' Booking Confirmation', 'propertyhive') : __('Re-Email ' . $owner_or_landlord . ' Booking Confirmation', 'propertyhive') ) . '</a>';
                    
                    $actions[] = '<div id="viewing_owner_confirmation_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . ( ( $owner_booking_confirmation_sent_at == '' ) ? 'display:none' : '' ) . '">' . ( ( $owner_booking_confirmation_sent_at != '' ) ? 'Previously sent to ' . strtolower($owner_or_landlord) . ' on <span title="' . $owner_booking_confirmation_sent_at . '">' . date("jS F", strtotime($owner_booking_confirmation_sent_at)) : '' ) . '</span></div>';
                }

                $actions[] = '<hr>';
            }

            $actions[] = '<a 
                    href="#action_panel_viewing_carried_out" 
                    class="button button-success viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Viewing Carried Out', 'propertyhive') . '</a>';
            $actions[] = '<a 
                    href="#action_panel_viewing_cancelled" 
                    class="button viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Viewing Cancelled', 'propertyhive') . '</a>';

            $show_cancelled_meta_boxes = true;
        }

        if ( $status == 'carried_out' )
        {
            if ( $feedback_status == '' )
            {
                $actions[] = '<a 
                    href="#action_panel_viewing_interested" 
                    class="button button-success viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Applicant Interested', 'propertyhive') ) . '</a>';

                $actions[] = '<a 
                    href="#action_panel_viewing_not_interested" 
                    class="button button-danger viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Applicant Not Interested', 'propertyhive') ) . '</a>';

                $actions[] = '<a 
                    href="#action_panel_viewing_feedback_not_required" 
                    class="button viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Feedback Not Required', 'propertyhive') ) . '</a>';

                $show_feedback_meta_boxes = true;
            }

            if ( $feedback_status == 'interested' )
            {
                $actions[] = '<a 
                    href="' . trim(admin_url(), '/') . '/post-new.php?post_type=viewing&applicant_contact_id=' . get_post_meta( $post_id, '_applicant_contact_id', TRUE ) . '&property_id=' . get_post_meta( $post_id, '_property_id', TRUE ) . '&viewing_id=' . $post_id .'" 
                    class="button button-success"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Book Second Viewing', 'propertyhive') ) . '</a>';

                if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
                {
                    $property_id = get_post_meta( $post_id, '_property_id', TRUE );
                    if ( get_post_meta( $property_id, '_department', TRUE ) == 'residential-sales' )
                    {
                        // See if an offer has this viewing id associated with it
                        $offer_id = get_post_meta( $post_id, '_offer_id', TRUE );
                        if ( $offer_id != '' && get_post_status($offer_id) != 'publish' )
                        {
                            $offer_id = '';
                        }

                        if ( $offer_id != '' )
                        {
                            $actions[] = '<a 
                                    href="' . get_edit_post_link( $offer_id, '' ) . '" 
                                    class="button"
                                    style="width:100%; margin-bottom:7px; text-align:center" 
                                >' . wp_kses_post( __('View Offer', 'propertyhive') ) . '</a>';
                        }
                        else
                        {
                            $actions[] = '<a 
                                    href="' . wp_nonce_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ), '1', 'create_offer' ) . '" 
                                    class="button button-success"
                                    style="width:100%; margin-bottom:7px; text-align:center" 
                                >' . wp_kses_post( __('Record Offer', 'propertyhive') ) . '</a>';
                        }
                    }
                }
            }

            if ( get_post_meta( $post_id, '_feedback_passed_on', TRUE ) != 'yes' && ( $feedback_status == 'interested' || $feedback_status == 'not_interested' ) )
            {
                $actions[] = '<a 
                    href="#action_panel_viewing_revert_feedback_passed_on" 
                    class="button viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Feedback Passed On To Owner', 'propertyhive') ) . '</a>';
            }

            if ( $feedback_status == 'interested' || $feedback_status == 'not_interested' || $feedback_status == 'not_required' )
            {
                $actions[] = '<a 
                    href="#action_panel_viewing_revert_feedback_pending" 
                    class="button viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Revert To Feedback Pending', 'propertyhive') ) . '</a>';
            }
        }

        if ( $status == 'offer_made' )
        {
            if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
            {
                $offer_id = get_post_meta( $post_id, '_offer_id', TRUE );
                if ( $offer_id != '' && get_post_status($offer_id) != 'publish' )
                {
                    $offer_id = '';
                }

                if ( $offer_id != '' )
                {
                    $actions[] = '<a 
                            href="' . get_edit_post_link( $offer_id, '' ) . '" 
                            class="button"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . wp_kses_post( __('View Offer', 'propertyhive') ) . '</a>';
                }
            }
        }

        if ( ( $status == 'carried_out' && $feedback_status == '' ) || $status == 'cancelled' )
        {
            $actions[] = '<a 
                    href="#action_panel_viewing_revert_pending" 
                    class="button viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Revert To Pending', 'propertyhive') ) . '</a>';
        }

        $actions = apply_filters( 'propertyhive_admin_viewing_actions', $actions, $post_id );

        if ( !empty($actions) )
        {
            echo implode("", $actions);
        }
        else
        {
            echo '<div style="text-align:center">' . wp_kses_post( __( 'No actions to display', 'propertyhive' ) ) . '</div>';
        }

        echo '</div>

        </div>';

        if ( $show_cancelled_meta_boxes )
        {
            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_cancelled" style="display:none;">

                <div class="options_group" style="padding-top:8px;">

                    <div class="form-field">

                        <label for="_viewing_cancelled_reason">' . __( 'Reason Cancelled', 'propertyhive' ) . '</label>
                        
                        <textarea id="_cancelled_reason" name="_cancelled_reason" style="width:100%;">' . get_post_meta( $post_id, '_cancelled_reason', TRUE ) . '</textarea>

                    </div>

                    <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
                    <a class="button button-primary cancelled-reason-action-submit" href="#">' . __( 'Save', 'propertyhive' ) . '</a>

                </div>

            </div>';
        }

        if ( $show_feedback_meta_boxes )
        {
            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_interested" style="display:none;">

                <div class="options_group" style="padding-top:8px;">

                    <div class="form-field">

                        <label for="_viewing_interested_feedback">' . __( 'Applicant Feedback', 'propertyhive' ) . '</label>
                        
                        <textarea id="_interested_feedback" name="_interested_feedback" style="width:100%;">' . get_post_meta( $post_id, '_feedback', TRUE ) . '</textarea>

                    </div>

                    <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
                    <a class="button button-primary interested-feedback-action-submit" href="#">' . wp_kses_post( __( 'Save Feedback', 'propertyhive' ) ) . '</a>

                </div>

            </div>';

            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_not_interested" style="display:none;">

                <div class="options_group" style="padding-top:8px;">

                    <div class="form-field">

                        <label for="_viewing_not_interested_feedback">' . __( 'Applicant Feedback', 'propertyhive' ) . '</label>
                        
                        <textarea id="_not_interested_feedback" name="_not_interested_feedback" style="width:100%;">' . get_post_meta( $post_id, '_feedback', TRUE ) . '</textarea>

                    </div>

                    <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
                    <a class="button button-primary not-interested-feedback-action-submit" href="#">' . wp_kses_post( __( 'Save Feedback', 'propertyhive' ) ) . '</a>

                </div>

            </div>';
        }

        die();
    }

    public function viewing_carried_out()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'pending' )
        {
            update_post_meta( $post_id, '_status', 'carried_out' );

            $current_user = wp_get_current_user();

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_carried_out',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function viewing_cancelled()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'pending' )
        {
            update_post_meta( $post_id, '_status', 'cancelled' );
            update_post_meta( $post_id, '_cancelled_reason', sanitize_textarea_field( $_POST['cancelled_reason'] ) );

            $current_user = wp_get_current_user();

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_cancelled',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function viewing_email_applicant_booking_confirmation()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $applicant_contact_id = get_post_meta( $post_id, '_applicant_contact_id', TRUE );
        $property_id = get_post_meta( $post_id, '_property_id', TRUE );

        if ( (int)$applicant_contact_id == '' || (int)$property_id == '' || (int)$applicant_contact_id == 0 || (int)$property_id == 0 )
        {
            die();
        }

        $property = new PH_Property((int)$property_id);

        $to = get_post_meta( $applicant_contact_id, '_email_address', TRUE );

        if ( sanitize_email($to) != '' )
        {
            $subject = get_option( 'propertyhive_viewing_applicant_booking_confirmation_email_subject', '' );
            $body = get_option( 'propertyhive_viewing_applicant_booking_confirmation_email_body', '' );

            $subject = str_replace('[property_address]', $property->get_formatted_full_address(), $subject);
            $subject = str_replace('[applicant_name]', get_the_title($applicant_contact_id), $subject);
            $subject = str_replace('[viewing_time]', date("H:i", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $subject);
            $subject = str_replace('[viewing_date]', date("l jS F Y", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $subject);

            $body = str_replace('[property_address]', $property->get_formatted_full_address(), $body);
            $body = str_replace('[applicant_name]', get_the_title($applicant_contact_id), $body);
            $body = str_replace('[viewing_time]', date("H:i", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $body);
            $body = str_replace('[viewing_date]', date("l jS F Y", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $body);

            $from = $property->office_email_address;
            if ( sanitize_email($from) == '' )
            {
                $from = get_bloginfo('admin_email');
            }

            $headers = array();
            $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $from . '>';
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';

            wp_mail($to, $subject, $body, $headers);

            update_post_meta( $post_id, '_applicant_booking_confirmation_sent_at', date("Y-m-d H:i:s") );
        }

        die();
    }

    public function viewing_email_owner_booking_confirmation()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $property_id = get_post_meta( $post_id, '_property_id', TRUE );
        $property_department = get_post_meta( $property_id, '_department' );

        $applicant_contact_id = get_post_meta( $post_id, '_applicant_contact_id', TRUE );
        $owner_contact_ids = get_post_meta( $property_id, '_owner_contact_id', TRUE );
 
        if ( $owner_contact_ids > 0 ) {

            $owner_emails = array();
            $owner_names = array();
    
            foreach ($owner_contact_ids as $owner_id) 
            {
                $owner_email = sanitize_email( get_post_meta($owner_id, '_email_address', TRUE) );
                $owner_name = get_the_title($owner_id);

                if( ! empty($owner_email) ) array_push($owner_emails, $owner_email);
                if( ! empty($owner_name) ) array_push($owner_names, $owner_name);
            }

            $property = new PH_Property((int)$property_id);

            $to = implode(",", $owner_emails);

            $subject = get_option( 'propertyhive_viewing_owner_booking_confirmation_email_subject', '' );
            $body = get_option( 'propertyhive_viewing_owner_booking_confirmation_email_body', '' );

            $subject = str_replace('[property_address]', $property->get_formatted_full_address(), $subject);
            $subject = str_replace('[owner_name]', implode(", ", $owner_names), $subject);
            $subject = str_replace('[viewing_time]', date("H:i", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $subject);
            $subject = str_replace('[viewing_date]', date("l jS F Y", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $subject);

            $body = str_replace('[property_address]', $property->get_formatted_full_address(), $body);
            $body = str_replace('[owner_name]', implode(", ", $owner_names), $body);
            $body = str_replace('[viewing_time]', date("H:i", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $body);
            $body = str_replace('[viewing_date]', date("l jS F Y", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $body);

            $from = $property->office_email_address;
            if ( sanitize_email($from) == '' )
            {
                $from = get_bloginfo('admin_email');
            }

            $headers = array();
            $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $from . '>';
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';

            wp_mail($to, $subject, $body, $headers);

            update_post_meta( $post_id, '_owner_booking_confirmation_sent_at', date("Y-m-d H:i:s") );

        }

        die();
    }

    public function viewing_interested_feedback()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' )
        {
            update_post_meta( $post_id, '_feedback_status', 'interested' );
            update_post_meta( $post_id, '_feedback', sanitize_textarea_field( $_POST['feedback'] ) );

            $current_user = wp_get_current_user();

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_applicant_interested',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function viewing_not_interested_feedback()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' )
        {
            update_post_meta( $post_id, '_feedback_status', 'not_interested' );
            update_post_meta( $post_id, '_feedback', sanitize_textarea_field( $_POST['feedback'] ) );

            $current_user = wp_get_current_user();

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_applicant_not_interested',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function viewing_feedback_not_required()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' )
        {
            update_post_meta( $post_id, '_feedback_status', 'not_required' );

            $current_user = wp_get_current_user();

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_feedback_not_required',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function viewing_revert_feedback_pending()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' )
        {
            update_post_meta( $post_id, '_feedback_status', '' );
            update_post_meta( $post_id, '_feedback_passed_on', '' );

            $current_user = wp_get_current_user();

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_revert_feedback_pending',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function viewing_revert_pending()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' || $status == 'cancelled' )
        {
            update_post_meta( $post_id, '_status', 'pending' );
            update_post_meta( $post_id, '_feedback_status', '' );

            $current_user = wp_get_current_user();

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_revert_pending',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function viewing_feedback_passed_on()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' )
        {
            update_post_meta( $post_id, '_feedback_passed_on', 'yes' );

            $current_user = wp_get_current_user();

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_feedback_passed_on',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function get_property_viewings_meta_box()
    {
        check_ajax_referer( 'get_property_viewings_meta_box', 'security' );

        global $post;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

            $args = array(
                'post_type'   => 'viewing', 
                'nopaging'    => true,
                'orderby'   => 'meta_value',
                'order'       => 'DESC',
                'meta_key'  => '_start_date_time',
                'post_status'   => 'publish',
                'meta_query'  => array(
                    array(
                        'key' => '_property_id',
                        'value' => (int)$_POST['post_id']
                    )
                )
            );
            $viewings_query = new WP_Query( $args );

            if ( $viewings_query->have_posts() )
            {
                echo '<table style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align:left;">' . __( 'Date', 'propertyhive' ) . ' / ' . __( 'Time', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Applicant', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Attending Negotiator(s)', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Status', 'propertyhive' ) . '</th>
                        </tr>
                    </thead>
                    <tbody>';

                while ( $viewings_query->have_posts() )
                {
                    $viewings_query->the_post();

                    echo '<tr>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_the_ID(), '' ) . '">' . date("H:i jS F Y", strtotime(get_post_meta(get_the_ID(), '_start_date_time', TRUE))) . '</a></td>';
                        echo '<td style="text-align:left;">';
                        if ( get_post_meta(get_the_ID(), '_applicant_contact_id', TRUE) != '' )
                        {
                            echo  '<a href="' . get_edit_post_link( get_post_meta(get_the_ID(), '_applicant_contact_id', TRUE), '' ) . '">' . get_the_title(get_post_meta(get_the_ID(), '_applicant_contact_id', TRUE)) . '</a>';
                        }
                        else
                        {
                            echo '-';
                        }
                        echo '</td>';
                        echo '<td style="text-align:left;">';

                        $negotiator_ids = get_post_meta(get_the_ID(), '_negotiator_id');

                        if (!empty($negotiator_ids))
                        {
                            $i = 0;
                            foreach ($negotiator_ids as $negotiator_id)
                            {
                                if ( $i > 0 ) { echo ', '; }

                                $userdata = get_userdata( $negotiator_id );
                                if ( $userdata !== FALSE )
                                {
                                    echo $userdata->display_name;
                                }
                                else
                                {
                                    echo '<em>Unknown user</em>';
                                }
                                ++$i;
                            }
                        }
                        else
                        {
                            echo 'Unattended';
                        }

                        echo '</td>';
                        echo '<td style="text-align:left;">';

                        $status = get_post_meta(get_the_ID(), '_status', TRUE);
                        echo ucwords(str_replace("_", " ", $status));
                        if ( $status == 'pending' )
                        {
                            echo '<br>';
                            // confirmation status
                            if ( get_post_meta(get_the_ID(), '_all_confirmed', TRUE) == 'yes' )
                            {
                                echo __( 'All Parties Confirmed', 'propertyhive' );
                            }
                            else
                            {
                                echo __( 'Awaiting Confirmation', 'propertyhive' );
                            }
                        }
                        if ( $status == 'carried_out' )
                        {
                            echo '<br>';
                            $feedback_status = get_post_meta(get_the_ID(), '_feedback_status', TRUE);
                            switch ( $feedback_status )
                            {
                                case "interested": { echo 'Applicant Interested'; break; }
                                case "not_interested": { echo 'Applicant Not Interested'; break; }
                                case "not_required": { echo 'Feedback Not Required'; break; }
                                default: { echo 'Awaiting Feedback'; }
                            }

                            if ( $feedback_status == 'interested' || $feedback_status == 'not_interested' )
                            {
                                $feedback_passed_on = get_post_meta(get_the_ID(), '_feedback_passed_on', TRUE);
                                echo '<br>' . ( ($feedback_passed_on == 'yes') ? 'Feedback Passed On' : 'Feedback Not Passed On' );
                            }
                        }
                        echo '</td>';
                    echo '</tr>';
                }

                echo '
                    </tbody>
                </table>
                <br>';
            }
            else
            {
                echo '<p>' . __( 'No viewings exist for this property', 'propertyhive') . '</p>';
            }
            wp_reset_postdata();

        do_action('propertyhive_property_viewings_fields');
        
        echo '</div>';
        
        echo '</div>';

        die();
    }

    public function get_contact_viewings_meta_box()
    {
        check_ajax_referer( 'get_contact_viewings_meta_box', 'security' );

        global $post;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

            $args = array(
                'post_type'   => 'viewing', 
                'nopaging'    => true,
                'orderby'   => 'meta_value',
                'order'       => 'DESC',
                'post_status'   => 'publish',
                'meta_key'  => '_start_date_time',
                'meta_query'  => array(
                    array(
                        'key' => '_applicant_contact_id',
                        'value' => (int)$_POST['post_id']
                    )
                )
            );
            $viewings_query = new WP_Query( $args );

            if ( $viewings_query->have_posts() )
            {
                echo '<table style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align:left;">' . __( 'Date', 'propertyhive' ) . ' / ' . __( 'Time', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Property', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Attending Negotiator(s)', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Status', 'propertyhive' ) . '</th>
                        </tr>
                    </thead>
                    <tbody>';

                while ( $viewings_query->have_posts() )
                {
                    $viewings_query->the_post();

                    $property = new PH_Property((int)get_post_meta(get_the_ID(), '_property_id', TRUE));

                    echo '<tr>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_the_ID(), '') . '">' . date("H:i jS F Y", strtotime(get_post_meta(get_the_ID(), '_start_date_time', TRUE))) . '</a></td>';
                        echo '<td style="text-align:left;">';
                        if ( get_post_meta(get_the_ID(), '_property_id', TRUE) != '' )
                        {
                            echo '<a href="' . get_edit_post_link( get_post_meta(get_the_ID(), '_property_id', TRUE), '' ) . '">' . $property->get_formatted_full_address() . '</a>';
                        }
                        else
                        {
                            echo '-';
                        }
                        echo '</td>';

                        echo '<td style="text-align:left;">';

                        $negotiator_ids = get_post_meta(get_the_ID(), '_negotiator_id');

                        if (!empty($negotiator_ids))
                        {
                            $i = 0;
                            foreach ($negotiator_ids as $negotiator_id)
                            {
                                if ( $i > 0 ) { echo ', '; }

                                $userdata = get_userdata( $negotiator_id );
                                if ( $userdata !== FALSE )
                                {
                                    echo $userdata->display_name;
                                }
                                else
                                {
                                    echo '<em>Unknown user</em>';
                                }
                                ++$i;
                            }
                        }
                        else
                        {
                            echo 'Unattended';
                        }

                        echo '</td>';
                        echo '<td style="text-align:left;">';

                        $status = get_post_meta(get_the_ID(), '_status', TRUE);
                        echo ucwords(str_replace("_", " ", $status));
                        if ( $status == 'pending' )
                        {
                            echo '<br>';
                            // confirmation status
                            if ( get_post_meta(get_the_ID(), '_all_confirmed', TRUE) == 'yes' )
                            {
                                echo __( 'All Parties Confirmed', 'propertyhive' );
                            }
                            else
                            {
                                echo __( 'Awaiting Confirmation', 'propertyhive' );
                            }
                        }
                        if ( $status == 'carried_out' )
                        {
                            echo '<br>';
                            $feedback_status = get_post_meta(get_the_ID(), '_feedback_status', TRUE);
                            switch ( get_post_meta(get_the_ID(), '_feedback_status', TRUE) )
                            {
                                case "interested": { echo 'Applicant Interested'; break; }
                                case "not_interested": { echo 'Applicant Not Interested'; break; }
                                case "not_required": { echo 'Feedback Not Required'; break; }
                                default: { echo 'Awaiting Feedback'; }
                            }

                            if ( $feedback_status == 'interested' || $feedback_status == 'not_interested' )
                            {
                                $feedback_passed_on = get_post_meta(get_the_ID(), '_feedback_passed_on', TRUE);
                                echo '<br>' . ( ($feedback_passed_on == 'yes') ? 'Feedback Passed On' : 'Feedback Not Passed On' );
                            }
                        }
                        echo '</td>';
                    echo '</tr>';
                }

                echo '
                    </tbody>
                </table>
                <br>';
            }
            else
            {
                echo '<p>' . __( 'No viewings exist for this contact', 'propertyhive') . '</p>';
            }
            wp_reset_postdata();

        do_action('propertyhive_contact_viewings_fields');
        
        echo '</div>';
        
        echo '</div>';

        die();
    }

    // Offer related functions
    public function record_offer_property()
    {
        check_ajax_referer( 'record-offer', 'security' );

        $this->json_headers();

        // TO DO: Should do validation on server side also
        if (empty($_POST['property_id']))
        {
            $return = array('error' => 'No property selected');
            echo json_encode( $return );
            die();
        }

        $property = new PH_Property((int)$_POST['property_id']);
        
        $applicant_contact_ids = array();

        // Create applicant record if required
        if (empty($_POST['applicant_ids']) && !empty($_POST['applicant_name']))
        {
            // Need to create contact/applicant
            $contact_post = array(
                'post_title'    => ph_clean($_POST['applicant_name']),
                'post_content'  => '',
                'post_type'     => 'contact',
                'post_status'   => 'publish',
                'comment_status'    => 'closed',
                'ping_status'    => 'closed',
            );
                    
            // Insert the post into the database
            $contact_post_id = wp_insert_post( $contact_post );

            if ( is_wp_error($contact_post_id) || $contact_post_id == 0 )
            {
                $return = array('error' => 'Failed to create contact post. Please try again');
                echo json_encode( $return );
                die();
            }

            update_post_meta( $contact_post_id, '_contact_types', array('applicant') );

            update_post_meta( $contact_post_id, '_applicant_profiles', 1 );
            update_post_meta( $contact_post_id, '_applicant_profile_0', array( 'department' => $property->department, 'send_matching_properties' => '' ) );

            $applicant_contact_ids[] = $contact_post_id;
        }

        if (!empty($_POST['applicant_ids']) && empty($_POST['applicant_name']))
        {
            // This is an existing contact
            if ( !is_array($_POST['applicant_ids']) )
            {
                $_POST['applicant_ids'] = array($_POST['applicant_ids']);
            }

            foreach ( $_POST['applicant_ids'] as $applicant_id )
            {
                $applicant_contact_ids[] = (int)$applicant_id;
            }
        }

        $applicant_contact_ids = array_unique($applicant_contact_ids);

        if ( empty($applicant_contact_ids) )
        {
            $return = array('error' => 'No applicant selected, or unable to create applicant record');
            echo json_encode( $return );
            die();
        }

        // Loop through contacts and create one offer each
        // At the moment it's a 1-to-1 relationship, but might support multiple in the future
        foreach ( $applicant_contact_ids as $applicant_contact_id )
        {
            // Insert offer record
            $offer_post = array(
                'post_title'    => '',
                'post_content'  => '',
                'post_type'  => 'offer',
                'post_status'   => 'publish',
                'comment_status'    => 'closed',
                'ping_status'    => 'closed',
            );
                    
            // Insert the post into the database
            $offer_post_id = wp_insert_post( $offer_post );

            if ( is_wp_error($offer_post_id) || $offer_post_id == 0 )
            {
                $return = array('error' => 'Failed to create offer post. Please try again');
                echo json_encode( $return );
                die();
            }

            $amount = preg_replace("/[^0-9]/", '', $_POST['amount']);
            
            add_post_meta( $offer_post_id, '_offer_date_time', ph_clean($_POST['offer_date']) . ' ' . ph_clean($_POST['offer_time']) );
            add_post_meta( $offer_post_id, '_property_id', (int)$_POST['property_id'] );
            add_post_meta( $offer_post_id, '_applicant_contact_id', $applicant_contact_id );
            add_post_meta( $offer_post_id, '_amount', $amount );
            add_post_meta( $offer_post_id, '_status', 'pending' );
        }

        $applicant_contacts = array();
        foreach ( $applicant_contact_ids  as $applicant_contact_id )
        {
            $applicant_contacts[] = array(
                'ID' => $applicant_contact_id,
                'post_title' => get_the_title($applicant_contact_id),
                'edit_link' => get_edit_post_link( $applicant_contact_id, '' ),
            );
        }

        $return = array('success' => array(
            'offer' => array(
                'ID' => $offer_post_id,
                'edit_link' => get_edit_post_link( $offer_post_id, '' ),
            ),
            'applicant_contacts' => $applicant_contacts,
        ));

        echo json_encode( $return );

        die();
    }

    public function record_offer_contact()
    {
        check_ajax_referer( 'record-offer', 'security' );

        $this->json_headers();

        // TO DO: Should do validation on server side also
        if (empty($_POST['contact_id']))
        {
            $return = array('error' => 'No contact selected');
            echo json_encode( $return );
            die();
        }

        if (empty($_POST['property_ids']))
        {
            $return = array('error' => 'No property selected');
            echo json_encode( $return );
            die();
        }

        // Loop through contacts and create one offer each
        // At the moment it's a 1-to-1 relationship, but might support multiple in the future
        foreach ( $_POST['property_ids'] as $property_id )
        {
            // Insert offer record
            $offer_post = array(
                'post_title'    => '',
                'post_content'  => '',
                'post_type'  => 'offer',
                'post_status'   => 'publish',
                'comment_status'    => 'closed',
                'ping_status'    => 'closed',
            );
                    
            // Insert the post into the database
            $offer_post_id = wp_insert_post( $offer_post );

            if ( is_wp_error($offer_post_id) || $offer_post_id == 0 )
            {
                $return = array('error' => 'Failed to create offer post. Please try again');
                echo json_encode( $return );
                die();
            }

            $amount = preg_replace("/[^0-9]/", '', ph_clean($_POST['amount']));
            
            add_post_meta( $offer_post_id, '_offer_date_time', ph_clean($_POST['offer_date']) . ' ' . ph_clean($_POST['offer_time']) );
            add_post_meta( $offer_post_id, '_property_id', (int)$property_id );
            add_post_meta( $offer_post_id, '_applicant_contact_id', (int)$_POST['contact_id'] );
            add_post_meta( $offer_post_id, '_amount', $amount );
            add_post_meta( $offer_post_id, '_status', 'pending' );
        }

        $properties = array();
        foreach ( $_POST['property_ids'] as $property_id )
        {
            $properties[] = array(
                'ID' => (int)$property_id,
                'post_title' => get_the_title((int)$property_id),
                'edit_link' => get_edit_post_link( (int)$property_id, '' ),
            );
        }

        $return = array('success' => array(
            'offer' => array(
                'ID' => $offer_post_id,
                'edit_link' => get_edit_post_link( $offer_post_id, '' ),
            ),
            'properties' => $properties,
        ));

        echo json_encode( $return );

        die();
    }

    public function get_offer_details_meta_box()
    {
        check_ajax_referer( 'offer-details-meta-box', 'security' );

        $offer = new PH_Offer((int)$_POST['offer_id']);

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        if ( $offer->status != '' )
        {
            echo '<p class="form-field">
            
                <label for="">' . __('Status', 'propertyhive') . '</label>
                
                ' . ucwords(str_replace("_", " ", $offer->status)) . '    
            
            </p>';
        }

        $offer_date_time = $offer->offer_date_time;
        if ( empty($offer_date_time) )
        {
            $offer_date_time = date("Y-m-d H:i:s");
        }

        echo '<p class="form-field offer_date_time_field">
    
            <label for="_offer_date">' . __('Offer Date / Time', 'propertyhive') . '</label>
            
            <input type="text" id="_offer_date" name="_offer_date" class="date-picker short" placeholder="yyyy-mm-dd" style="width:120px;" value="' . date("Y-m-d", strtotime($offer_date_time)) . '">
            <select id="_offer_time_hours" name="_offer_time_hours" class="select short" style="width:55px">';
        
        if ( empty($offer_date_time) )
        {
            $value = date("H");
        }
        else
        {
            $value = date( "H", strtotime( $offer_date_time ) );
        }
        for ( $i = 0; $i < 23; ++$i )
        {
            $j = str_pad($i, 2, '0', STR_PAD_LEFT);
            echo '<option value="' . $j . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . $j . '</option>';
        }
        
        echo '</select>
        :
        <select id="_offer_time_minutes" name="_offer_time_minutes" class="select short" style="width:55px">';
        
        if ( empty($offer_date_time) )
        {
            $value = '';
        }
        else
        {
            $value = date( "i", strtotime( $offer_date_time ) );
        }
        for ( $i = 0; $i < 60; $i+=5 )
        {
            $j = str_pad($i, 2, '0', STR_PAD_LEFT);
            echo '<option value="' . $j . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . $j . '</option>';
        }
        
        echo '</select>
            
        </p>';

        $args = array( 
            'id' => '_amount', 
            'label' => __( 'Offer Amount', 'propertyhive' ) . ' (&pound;)', 
            'desc_tip' => false, 
            'class' => 'short',
            'value' => ( is_numeric($offer->amount) ? number_format($offer->amount) : '' ),
            'custom_attributes' => array(
                //'style' => 'width:95%; max-width:500px;'
            )
        );
        propertyhive_wp_text_input( $args );

        do_action('propertyhive_offer_details_fields');
        
        echo '</div>';
        
        echo '</div>';

        die();
    }

    public function get_offer_actions()
    {
        check_ajax_referer( 'offer-actions', 'security' );

        $post_id = (int)$_POST['offer_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="propertyhive_offer_actions_meta_box">

        <div class="options_group" style="padding-top:8px;">';

        $actions = array();

        if ( $status == 'pending' )
        {
            $actions[] = '<a 
                    href="#action_panel_offer_accepted" 
                    class="button button-success offer-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Accept Offer', 'propertyhive') ) . '</a>';
            $actions[] = '<a 
                    href="#action_panel_offer_declined" 
                    class="button button-danger offer-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Decline Offer', 'propertyhive') ) . '</a>';
        }

        if ( $status == 'accepted' )
        {
            // See if a sale has this offer id associated with it
            $sale_id = get_post_meta( $post_id, '_sale_id', TRUE );
            if ( $sale_id != '' && get_post_status($sale_id) != 'publish' )
            {
                $sale_id = '';
            }

            if ( $sale_id != '' )
            {
                $actions[] = '<a 
                        href="' . get_edit_post_link( $sale_id, '' ) . '" 
                        class="button"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . wp_kses_post( __('View Sale', 'propertyhive') ) . '</a>';
            }
            else
            {
                $actions[] = '<a 
                        href="' . wp_nonce_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ), '1', 'create_sale' ) . '" 
                        class="button button-success"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . wp_kses_post( __('Create Sale', 'propertyhive') ) . '</a>';
            }
        }

        if ( $status == 'declined' )
        {
            
        }

        if ( $status == 'accepted' || $status == 'declined' )
        {
            $actions[] = '<a 
                    href="#action_panel_offer_revert_pending" 
                    class="button offer-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Revert To Pending', 'propertyhive') ) . '</a>';
        }

        $actions = apply_filters( 'propertyhive_admin_offer_actions', $actions, $post_id );

        if ( !empty($actions) )
        {
            echo implode("", $actions);
        }
        else
        {
            echo '<div style="text-align:center">' . __( 'No actions to display', 'propertyhive' ) . '</div>';
        }

        echo '</div>

        </div>';

        die();
    }

    public function offer_accepted()
    {
        check_ajax_referer( 'offer-actions', 'security' );

        $post_id = (int)$_POST['offer_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'pending' )
        {
            update_post_meta( $post_id, '_status', 'accepted' );

            $current_user = wp_get_current_user();

            // Add note/comment to offer
            $comment = array(
                'note_type' => 'action',
                'action' => 'offer_accepted',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function offer_declined()
    {
        check_ajax_referer( 'offer-actions', 'security' );

        $post_id = (int)$_POST['offer_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'pending' )
        {
            update_post_meta( $post_id, '_status', 'declined' );

            $current_user = wp_get_current_user();

            // Add note/comment to offer
            $comment = array(
                'note_type' => 'action',
                'action' => 'offer_declined',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function offer_revert_pending()
    {
        check_ajax_referer( 'offer-actions', 'security' );

        $post_id = (int)$_POST['offer_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'accepted' || $status == 'declined' )
        {
            update_post_meta( $post_id, '_status', 'pending' );

            $current_user = wp_get_current_user();

            // Add note/comment to offer
            $comment = array(
                'note_type' => 'action',
                'action' => 'offer_revert_pending',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function get_property_offers_meta_box()
    {
        check_ajax_referer( 'get_property_offers_meta_box', 'security' );

        global $post;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

            $args = array(
                'post_type'   => 'offer', 
                'nopaging'    => true,
                'orderby'   => 'meta_value',
                'order'       => 'DESC',
                'meta_key'  => '_offer_date_time',
                'post_status'   => 'publish',
                'meta_query'  => array(
                    array(
                        'key' => '_property_id',
                        'value' => (int)$_POST['post_id']
                    )
                )
            );
            $offers_query = new WP_Query( $args );

            if ( $offers_query->have_posts() )
            {
                echo '<table style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align:left;">' . __( 'Offer Date', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Applicant', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Offer Amount', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Status', 'propertyhive' ) . '</th>
                        </tr>
                    </thead>
                    <tbody>';

                while ( $offers_query->have_posts() )
                {
                    $offers_query->the_post();

                    $offer = new PH_Offer(get_the_ID());

                    echo '<tr>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_the_ID(), '' ) . '">' . date("jS F Y", strtotime(get_post_meta(get_the_ID(), '_offer_date_time', TRUE))) . '</a></td>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_post_meta(get_the_ID(), '_applicant_contact_id', TRUE), '' ) . '">' . get_the_title(get_post_meta(get_the_ID(), '_applicant_contact_id', TRUE)) . '</a></td>';
                        echo '<td style="text-align:left;">' . $offer->get_formatted_amount() . '</td>';
                        echo '<td style="text-align:left;">';
                        $status = get_post_meta(get_the_ID(), '_status', TRUE);
                        echo ucwords(str_replace("_", " ", $status));
                        echo '</td>';
                    echo '</tr>';
                }

                echo '
                    </tbody>
                </table>
                <br>';
            }
            else
            {
                echo '<p>' . __( 'No offers exist for this property', 'propertyhive') . '</p>';
            }
            wp_reset_postdata();

        do_action('propertyhive_property_offers_fields');
        
        echo '</div>';
        
        echo '</div>';

        die();
    }

    public function get_contact_offers_meta_box()
    {
        check_ajax_referer( 'get_contact_offers_meta_box', 'security' );

        global $post;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

            $args = array(
                'post_type'   => 'offer', 
                'nopaging'    => true,
                'orderby'   => 'meta_value',
                'order'       => 'DESC',
                'post_status'   => 'publish',
                'meta_key'  => '_offer_date_time',
                'meta_query'  => array(
                    array(
                        'key' => '_applicant_contact_id',
                        'value' => (int)$_POST['post_id']
                    )
                )
            );
            $offers_query = new WP_Query( $args );

            if ( $offers_query->have_posts() )
            {
                echo '<table style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align:left;">' . __( 'Offer Date', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Property', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Property Owner', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Offer Amount', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Status', 'propertyhive' ) . '</th>
                        </tr>
                    </thead>
                    <tbody>';

                while ( $offers_query->have_posts() )
                {
                    $offers_query->the_post();

                    $property = new PH_Property((int)get_post_meta(get_the_ID(), '_property_id', TRUE));
                    $offer = new PH_Offer(get_the_ID());

                    echo '<tr>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_the_ID(), '') . '">' . date("jS F Y", strtotime(get_post_meta(get_the_ID(), '_offer_date_time', TRUE))) . '</a></td>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_post_meta(get_the_ID(), '_property_id', TRUE), '' ) . '">' . $property->get_formatted_full_address() . '</a></td>';
                        echo '<td style="text-align:left;">';

                        $owner_contact_ids = $property->_owner_contact_id;
                        if ( 
                            ( !is_array($owner_contact_ids) && $owner_contact_ids != '' && $owner_contact_ids != 0 ) 
                            ||
                            ( is_array($owner_contact_ids) && !empty($owner_contact_ids) )
                        )
                        {
                            if ( !is_array($owner_contact_ids) )
                            {
                                $owner_contact_ids = array($owner_contact_ids);
                            }

                            foreach ( $owner_contact_ids as $owner_contact_id )
                            {
                                echo get_the_title($owner_contact_id) . '<br>';
                                echo '<div style="color:#BBB">';
                                echo 'T: ' . get_post_meta($owner_contact_id, '_telephone_number', TRUE) . '<br>';
                                echo 'E: ' . get_post_meta($owner_contact_id, '_email_address', TRUE);
                                echo '</div>';
                            }
                        }

                        echo '</td>';
                        echo '<td style="text-align:left;">' . $offer->get_formatted_amount() . '</td>';
                        echo '<td style="text-align:left;">';
                        $status = get_post_meta(get_the_ID(), '_status', TRUE);
                        echo ucwords(str_replace("_", " ", $status));
                        echo '</td>';
                    echo '</tr>';
                }

                echo '
                    </tbody>
                </table>
                <br>';
            }
            else
            {
                echo '<p>' . __( 'No offers exist for this contact', 'propertyhive') . '</p>';
            }
            wp_reset_postdata();

        do_action('propertyhive_contact_offers_fields');
        
        echo '</div>';
        
        echo '</div>';

        die();
    }

    // Sale related functions
    public function get_sale_details_meta_box()
    {
        check_ajax_referer( 'sale-details-meta-box', 'security' );

        $sale = new PH_Offer((int)$_POST['sale_id']);

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        if ( $sale->status != '' )
        {
            echo '<p class="form-field">
            
                <label for="">' . __('Status', 'propertyhive') . '</label>
                
                ' . ucwords(str_replace("_", " ", $sale->status)) . '    
            
            </p>';
        }

        $sale_date_time = $sale->sale_date_time;
        if ( empty($sale_date_time) )
        {
            $sale_date_time = date("Y-m-d H:i:s");
        }

        echo '<p class="form-field sale_date_field">
    
            <label for="_sale_date">' . __('Sale Date', 'propertyhive') . '</label>
            
            <input type="text" id="_sale_date" name="_sale_date" class="date-picker short" placeholder="yyyy-mm-dd" style="width:120px;" value="' . date("Y-m-d", strtotime($sale_date_time)) . '">
            
        </p>';

        $args = array( 
            'id' => '_amount', 
            'label' => __( 'Sale Amount', 'propertyhive' ) . ' (&pound;)', 
            'desc_tip' => false, 
            'class' => 'short',
            'value' => ( is_numeric($sale->amount) ? number_format($sale->amount) : '' ),
            'custom_attributes' => array(
                //'style' => 'width:95%; max-width:500px;'
            )
        );
        propertyhive_wp_text_input( $args );

        do_action('propertyhive_sale_details_fields');
        
        echo '</div>';
        
        echo '</div>';

        die();
    }

    public function get_sale_actions()
    {
        check_ajax_referer( 'sale-actions', 'security' );

        $post_id = (int)$_POST['sale_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="propertyhive_sale_actions_meta_box">

        <div class="options_group" style="padding-top:8px;">';

        $actions = array();

        if ( $status == 'current' )
        {
            $actions[] = '<a 
                    href="#action_panel_sale_exchanged" 
                    class="button button-success sale-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Sale Exchanged', 'propertyhive') . '</a>';
            
        }

        if ( $status == 'exchanged' )
        {
            $actions[] = '<a 
                    href="#action_panel_sale_completed" 
                    class="button button-success sale-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Sale Completed', 'propertyhive') . '</a>';
        }

        if ( $status == 'completed' )
        {
            
        }

        if ( $status == 'current' || $status == 'exchanged' )
        {
            $actions[] = '<a 
                    href="#action_panel_sale_fallen_through" 
                    class="button sale-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Sale Fallen Through', 'propertyhive') . '</a>';
        }

        $actions = apply_filters( 'propertyhive_admin_sale_actions', $actions, $post_id );

        if ( !empty($actions) )
        {
            echo implode("", $actions);
        }
        else
        {
            echo '<div style="text-align:center">' . __( 'No actions to display', 'propertyhive' ) . '</div>';
        }

        echo '</div>

        </div>';

        die();
    }

    public function sale_exchanged()
    {
        check_ajax_referer( 'sale-actions', 'security' );

        $post_id = (int)$_POST['sale_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'current' )
        {
            update_post_meta( $post_id, '_status', 'exchanged' );

            $current_user = wp_get_current_user();

            // Add note/comment to sale
            $comment = array(
                'note_type' => 'action',
                'action' => 'sale_exchanged',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function sale_completed()
    {
        check_ajax_referer( 'sale-actions', 'security' );

        $post_id = (int)$_POST['sale_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'exchanged' )
        {
            update_post_meta( $post_id, '_status', 'completed' );

            $current_user = wp_get_current_user();

            // Add note/comment to sale
            $comment = array(
                'note_type' => 'action',
                'action' => 'sale_completed',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function sale_fallen_through()
    {
        check_ajax_referer( 'sale-actions', 'security' );

        $post_id = (int)$_POST['sale_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'current' || $status == 'exchanged' )
        {
            update_post_meta( $post_id, '_status', 'fallen_through' );

            $current_user = wp_get_current_user();

            // Add note/comment to sale
            $comment = array(
                'note_type' => 'action',
                'action' => 'sale_fallen_through',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );
        }

        die();
    }

    public function get_property_sales_meta_box()
    {
        check_ajax_referer( 'get_property_sales_meta_box', 'security' );

        global $post;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

            $args = array(
                'post_type'   => 'sale', 
                'nopaging'    => true,
                'orderby'   => 'meta_value',
                'order'       => 'DESC',
                'meta_key'  => '_sale_date_time',
                'post_status'   => 'publish',
                'meta_query'  => array(
                    array(
                        'key' => '_property_id',
                        'value' => (int)$_POST['post_id']
                    )
                )
            );
            $sales_query = new WP_Query( $args );

            if ( $sales_query->have_posts() )
            {
                echo '<table style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align:left;">' . __( 'Sale Date', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Applicant', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Sale Amount', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Status', 'propertyhive' ) . '</th>
                        </tr>
                    </thead>
                    <tbody>';

                while ( $sales_query->have_posts() )
                {
                    $sales_query->the_post();

                    $sale = new PH_Sale(get_the_ID());

                    echo '<tr>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_the_ID(), '' ) . '">' . date("jS F Y", strtotime(get_post_meta(get_the_ID(), '_sale_date_time', TRUE))) . '</a></td>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_post_meta(get_the_ID(), '_applicant_contact_id', TRUE), '' ) . '">' . get_the_title(get_post_meta(get_the_ID(), '_applicant_contact_id', TRUE)) . '</a></td>';
                        echo '<td style="text-align:left;">' . $sale->get_formatted_amount() . '</td>';
                        echo '<td style="text-align:left;">';
                        $status = get_post_meta(get_the_ID(), '_status', TRUE);
                        echo ucwords(str_replace("_", " ", $status));
                        echo '</td>';
                    echo '</tr>';
                }

                echo '
                    </tbody>
                </table>
                <br>';
            }
            else
            {
                echo '<p>' . __( 'No sales exist for this property', 'propertyhive') . '</p>';
            }
            wp_reset_postdata();

        do_action('propertyhive_property_sales_fields');
        
        echo '</div>';
        
        echo '</div>';

        die();
    }

    public function get_contact_sales_meta_box()
    {
        check_ajax_referer( 'get_contact_sales_meta_box', 'security' );

        global $post;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

            $args = array(
                'post_type'   => 'sale', 
                'nopaging'    => true,
                'orderby'   => 'meta_value',
                'order'       => 'DESC',
                'post_status'   => 'publish',
                'meta_key'  => '_sale_date_time',
                'meta_query'  => array(
                    array(
                        'key' => '_applicant_contact_id',
                        'value' => (int)$_POST['post_id']
                    )
                )
            );
            $sales_query = new WP_Query( $args );

            if ( $sales_query->have_posts() )
            {
                echo '<table style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align:left;">' . __( 'Offer Date', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Property', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Property Owner', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Offer Amount', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Status', 'propertyhive' ) . '</th>
                        </tr>
                    </thead>
                    <tbody>';

                while ( $sales_query->have_posts() )
                {
                    $sales_query->the_post();

                    $sale = new PH_Sale(get_the_ID());

                    $property = new PH_Property((int)get_post_meta(get_the_ID(), '_property_id', TRUE));

                    echo '<tr>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_the_ID(), '') . '">' . date("jS F Y", strtotime(get_post_meta(get_the_ID(), '_sale_date_time', TRUE))) . '</a></td>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_post_meta(get_the_ID(), '_property_id', TRUE), '' ) . '">' . $property->get_formatted_full_address() . '</a></td>';
                        echo '<td style="text-align:left;">';

                        $owner_contact_ids = $property->_owner_contact_id;
                        if ( 
                            ( !is_array($owner_contact_ids) && $owner_contact_ids != '' && $owner_contact_ids != 0 ) 
                            ||
                            ( is_array($owner_contact_ids) && !empty($owner_contact_ids) )
                        )
                        {
                            if ( !is_array($owner_contact_ids) )
                            {
                                $owner_contact_ids = array($owner_contact_ids);
                            }

                            foreach ( $owner_contact_ids as $owner_contact_id )
                            {
                                echo get_the_title($owner_contact_id) . '<br>';
                                echo '<div style="color:#BBB">';
                                echo 'T: ' . get_post_meta($owner_contact_id, '_telephone_number', TRUE) . '<br>';
                                echo 'E: ' . get_post_meta($owner_contact_id, '_email_address', TRUE);
                                echo '</div>';
                            }
                        }

                        echo '</td>';
                        echo '<td style="text-align:left;">' . $sale->get_formatted_amount() . '</td>';
                        echo '<td style="text-align:left;">';
                        $status = get_post_meta(get_the_ID(), '_status', TRUE);
                        echo ucwords(str_replace("_", " ", $status));
                        echo '</td>';
                    echo '</tr>';
                }

                echo '
                    </tbody>
                </table>
                <br>';
            }
            else
            {
                echo '<p>' . __( 'No sales exist for this contact', 'propertyhive') . '</p>';
            }
            wp_reset_postdata();

        do_action('propertyhive_contact_sales_fields');
        
        echo '</div>';
        
        echo '</div>';

        die();
    }
}

new PH_AJAX();
