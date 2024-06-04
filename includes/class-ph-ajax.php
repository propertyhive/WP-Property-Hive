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
			'toggle_note_pinned' => false,
			'get_notes_grid' => false,
            'get_pinned_notes_grid' => false,
			'search_contacts' => false,
            'search_properties' => false,
            'search_negotiators' => false,
			'load_existing_owner_contact' => false,
            'load_existing_features' => false,
			'make_property_enquiry' => true,
            'create_contact_from_enquiry' => false,
            'merge_contact_records' => false,

            // Dashboard components
            'get_news' => false,
            'get_viewings_awaiting_applicant_feedback' => false,
            'get_my_upcoming_appointments' => false,
            'get_upcoming_overdue_key_dates' => false,

            // Property actions
            'osm_geocoding_request'  => false,
            'get_property_marketing_statistics_meta_box' => false,
            'get_property_tenancies_grid' => false,

            // Contact actions
            'create_contact_login' => false,
            'get_contact_tenancies_grid' => false,
            'get_contact_solicitor' => false,

            // Appraisal actions
            'get_appraisal_details_meta_box' => false,
            'get_appraisal_actions' => false,
            'appraisal_carried_out' => false,
            'appraisal_cancelled' => false,
            'appraisal_won' => false,
            'appraisal_lost_reason' => false,
            'appraisal_instructed' => false,
            'appraisal_email_owner_booking_confirmation' => false,
            'appraisal_revert_pending' => false,
            'appraisal_revert_carried_out' => false,
            'appraisal_revert_won' => false,

            // Viewing actions
            'book_viewing_property' => false,
            'book_viewing_contact' => false,
            'get_viewing_details_meta_box' => false,
            'get_viewing_actions' => false,
            'get_viewing_lightbox' => false,
            'viewing_carried_out' => false,
            'viewing_cancelled' => false,
            'viewing_no_show' => false,
            'viewing_email_applicant_booking_confirmation' => false,
            'viewing_email_owner_booking_confirmation' => false,
            'viewing_email_attending_negotiator_booking_confirmation' => false,
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
            'offer_withdrawn' => false,
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

            // Enquiry actions
            'get_property_enquiries_meta_box' => false,
            'get_contact_enquiries_meta_box' => false,

            // Tenancy actions
            'add_key_date' => false,
            'get_management_dates_grid' => false,
            'get_key_dates_quick_edit_row' => false,
            'check_key_date_recurrence' => false,
            'save_key_date' => false,
            'delete_key_date' => false,

            'validate_save_contact' => false,
            'applicant_registration' => true,
            'login' => true,
            'lost_password' => true,
            'reset_password' => true,
            'save_account_details' => true,
            'save_account_requirements' => true,

            // Dismissing notices
            'dismiss_notice_leave_review' => false,
            'dismiss_notice_demo_data' => false,
            'dismiss_notice_missing_search_results' => false,
            'dismiss_notice_missing_google_maps_api_key' => false,
            'dismiss_notice_invalid_expired_license_key' => false,
            'dismiss_notice_email_cron_not_running' => false,

            // Settings
            'save_term_order' => false,

            // PRO features activate/deactivate
            'activate_pro_feature' => false,
            'deactivate_pro_feature' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_propertyhive_' . $ajax_event, array( $this, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_propertyhive_' . $ajax_event, array( $this, $ajax_event ) );
			}
		}
	}

    public function save_term_order()
    {
        check_ajax_referer( 'updates', 'security' );

        if ( !isset($_POST['taxonomy']) || ( isset($_POST['taxonomy']) && empty(ph_clean($_POST['taxonomy'])) ) )
        {
            die();
        }

        if ( !isset($_POST['term']) || ( isset($_POST['term']) && empty(ph_clean($_POST['term'])) ) )
        {
            die();
        }

        update_option( 'propertyhive_taxonomy_terms_order_' . ph_clean($_POST['taxonomy']), implode("|", ph_clean($_POST['term'])));
        
        // Quit out
        die();
    }

    public function dismiss_notice_leave_review()
    {
        update_option( 'propertyhive_review_prompt_due_timestamp', 0 );
        
        // Quit out
        die();
    }

    public function dismiss_notice_demo_data()
    {
        update_option( 'propertyhive_hide_demo_data_tab', 'yes' );
        
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

	public function dismiss_notice_email_cron_not_running()
	{
		update_option( 'email_cron_not_running_dismissed', 'yes' );
	}

	/**
	 * Output headers for JSON requests
	 */
	private function json_headers() {
		header( 'Content-Type: application/json; charset=utf-8' );
	}

    /**
     * Return a list string, comma delimited with an ampersand(&) before the final item
     */
    private function get_list_string( $list_items )
    {
        $list_string = '';
        if ( count($list_items) == 1 )
        {
            $list_string = $list_items[0];
        }
        elseif ( count($list_items) > 1 )
        {
            $last_item = array_pop($list_items);
            $list_string = implode(', ', $list_items) . ' & ' . $last_item;
        }
        return $list_string;
    }

    private function check_recaptcha_form_response($errors, $key, $control)
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
                if (
                    isset($response['success']) && $response['success'] == true
                    &&
                    (
                        // If we're using Recaptcha V3, check the score
                        // 1.0 is very likely a good interaction, 0.0 is very likely a bot
                        $key == 'recaptcha'
                        ||
                        ( isset($response['score']) && $response['score'] >= 0.5 )
                    )
                )
                {

                }
                else
                {
                    $errors[] = 'Failed reCAPTCHA validation';
                }
            }
        }
        return $errors;
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

        $display_name = get_the_title((int)$_POST['contact_id']);

         // Create user
        $userdata = array(
            'display_name' => $display_name,
            'user_login' => sanitize_email($contact->email_address),
            'user_email' => sanitize_email($contact->email_address),
            'user_pass'  => $_POST['password'],
            'role' => 'property_hive_contact',
            'show_admin_bar_front' => 'false',
        );

        if ( !empty($display_name) )
        {
            $name_parts = explode( ' ', $display_name );

            if ( count($name_parts) > 1 )
            {
                $userdata['last_name'] = array_pop($name_parts);
                $userdata['first_name'] = implode(' ', $name_parts);
            }
            else
            {
                $userdata['last_name'] = $display_name;
            }
        }

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
                'post_type' => apply_filters( 'propertyhive_allowed_login_post_type', array( 'contact' ) ),
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
     * Lost password
     */
    public function lost_password()
    {
        $return = array(
            'success' => false,
            'errors' => array(),
        );

        if ( check_ajax_referer( 'ph_lost_password', 'security', false ) === FALSE )
        {
            $return['errors'][] = 'Invalid nonce';

            $this->json_headers();
            echo json_encode( $return );
            
            // Quit out
            die();
        }

        $email_address = sanitize_email($_POST['email_address']);

        $user_data = get_user_by( 'email', $email_address );

        // check email address exists
        if ( !$user_data )
        {
            $return['errors'][] = 'Email address not found';

            $this->json_headers();
            echo json_encode( $return );
            
            // Quit out
            die();
        }

        // Send reset email
        $to = $email_address;
        $subject = __( 'Password Reset Request for', 'propertyhive' ) . ' ' . get_bloginfo('name');
        $body = __( 'Someone has requested a new password for an account on', 'propertyhive' ) . ' ' . get_bloginfo('name') . ".\n\n";
        $body .= __( 'If you didn\'t make this request you can ignore this email. If you\'d like to proceed please follow the link below', 'propertyhive' ) . ":\n\n";
        $body .= add_query_arg( array(
            'key' => get_password_reset_key( $user_data ),
            'id' => $user_data->ID,
        ), get_permalink( get_option( 'propertyhive_applicant_reset_password_page_id', '' ) ) );


        $from = get_option('propertyhive_email_from_address', '');
        if ( $from == '' )
        {
            $from = get_bloginfo('admin_email');
        }

        $headers = array();
        $headers[] = 'From: ' . html_entity_decode(get_bloginfo('name')) . ' <' . sanitize_email($from) . '>';
        $headers[] = 'Reply-To: ' . sanitize_email($from);
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';

        $headers = apply_filters( 'propertyhive_lost_password_email_headers', $headers );

        wp_mail( $to, $subject, $body, $headers );
        
        $return['success'] = true;

        $this->json_headers();
        echo json_encode( $return );
        
        // Quit out
        die();
    }

    /**
     * Reset password
     */
    public function reset_password()
    {
        $return = array(
            'success' => false,
            'errors' => array(),
        );

        if ( check_ajax_referer( 'ph_reset_password', 'security', false ) === FALSE )
        {
            $return['errors'][] = 'Invalid nonce';

            $this->json_headers();
            echo json_encode( $return );
            
            // Quit out
            die();
        }

        // check key and user login again
        $user = check_password_reset_key( ph_clean($_POST['reset_key']), ph_clean($_POST['reset_login']) );

        // check passwords match and are strong enough
        if ( $user instanceof WP_User ) 
        {
            $password_1 = ph_clean($_POST['password_1']);
            $password_2 = ph_clean($_POST['password_2']);

            if ( empty( $password_1 ) ) 
            {
                $return['errors'][] = __( 'Please enter your password.', 'propertyhive' );
            }

            if ( $password_1 !== $password_2 ) 
            {
                $return['errors'][] = __( 'Passwords do not match.', 'propertyhive' );
            }

            // Check password strength?
        }
        else
        {
            $return['errors'][] = __( 'This key is invalid or has already been used. Please reset your password again if needed..', 'propertyhive' );
        }

        if ( !empty($return['errors']) )
        {
            $this->json_headers();
            echo json_encode( $return );
            
            // Quit out
            die();
        }

        // do actual reset
        $errors = new WP_Error();
        do_action( 'validate_password_reset', $errors, $user );

        do_action( 'password_reset', $user, $password_1 );

        wp_set_password( $password_1, $user->ID );
        
        $return['success'] = true;

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
    
        $form_controls_2 = apply_filters( 'propertyhive_applicant_requirements_form_fields', $form_controls_2, false );
        
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

        $contact_post_id = false;

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
                        while ( $contacts_query->have_posts() )
                        {
                            $contacts_query->the_post();

                            $contact_post_id = get_the_ID();
                        }
                        //$errors[] = __( 'This email address is already registered', 'propertyhive' );
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
            if ( in_array( $key, array('recaptcha', 'recaptcha-v3') ) )
            {
                $errors = $this->check_recaptcha_form_response($errors, $key, $control);
            }

            if ( $key == 'hCaptcha' )
            {
                $secret = isset( $control['secret'] ) ? $control['secret'] : '';
                $response = isset( $_POST['h-captcha-response'] ) ? ph_clean($_POST['h-captcha-response']) : '';

                $response = wp_remote_post(
                    'https://hcaptcha.com/siteverify',
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
                        $errors[] = 'Error decoding response from hCaptcha check';
                    }
                    else
                    {
                        if ( isset($response['success']) && $response['success'] == true )
                        {

                        }
                        else
                        {
                            $errors[] = 'Failed hCaptcha validation';
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
            if ( $contact_post_id === FALSE )
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
            }
            else
            {
                // update CPT
                $contact_post = array(
                    'ID'            => $contact_post_id,
                    'post_title'    => ph_clean($_POST['name']),
                    'post_status'   => 'publish',
                );

                // Insert the post into the database
                wp_update_post( $contact_post );
            }
            
            $forbidden_contact_methods = get_post_meta( $contact_post_id, '_forbidden_contact_methods', TRUE );
            if ( !is_array($forbidden_contact_methods) )
            {
                $forbidden_contact_methods = array();
            }
            if ( ( $key = array_search('email', $forbidden_contact_methods) ) !== false ) {
                unset($forbidden_contact_methods[$key]);
            }
            update_post_meta( $contact_post_id, '_forbidden_contact_methods', array_unique($forbidden_contact_methods) );

            // Add post meta (contact details, requirements etc)
            update_post_meta( $contact_post_id, '_email_address', sanitize_email($_POST['email_address']) );
            
            $telephone_number = get_post_meta( $contact_post_id, '_telephone_number', TRUE );
            if ( isset($_POST['telephone_number']) && $_POST['telephone_number'] != '' )
            {
                $telephone_number = $_POST['telephone_number'];
            }
            update_post_meta( $contact_post_id, '_telephone_number', ph_clean($telephone_number) );
            update_post_meta( $contact_post_id, '_telephone_number_clean', ph_clean( ph_clean_telephone_number($telephone_number) ) );
            
            $contact_types = get_post_meta( $contact_post_id, '_contact_types', TRUE );
            if ( !is_array($contact_types) )
            {
                $contact_types = array();
            }
            if ( !in_array('applicant', $contact_types) )
            {
                $contact_types[] = 'applicant';
            }
            update_post_meta( $contact_post_id, '_contact_types', array_unique($contact_types) );

            update_post_meta( $contact_post_id, '_applicant_profiles', 1 );

            $applicant_profile = array();
            $applicant_profile['department'] = $_POST['department'];

            $base_department = $_POST['department'];
            if ( !in_array( $base_department, array('residential-sales', 'residential-lettings', 'commercial') ) )
            {
                $base_department = ph_get_custom_department_based_on($base_department);
            }

            if ( $base_department == 'residential-sales' )
            {
                $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_price']));

                $applicant_profile['max_price'] = $price;

                // Not used yet but could be if introducing currencies in the future.
                $applicant_profile['max_price_actual'] = $price;

                $percentage_lower = get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' );
                $percentage_higher = get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' );

                if ( $percentage_lower != '' && $percentage_higher != '' && $_POST['maximum_price'] != '' && $_POST['maximum_price'] != 0 )
                {
                    $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_price']));
                    $applicant_profile['match_price_range_lower'] = $price - ( $price * ( $percentage_lower / 100 ) );
                    $applicant_profile['match_price_range_lower_actual'] = $price - ( $price * ( $percentage_lower / 100 ) );
                    
                    $applicant_profile['match_price_range_higher'] = $price + ( $price * ( $percentage_higher / 100 ) );
                    $applicant_profile['match_price_range_higher_actual'] = $price + ( $price * ( $percentage_higher / 100 ) );
                }
            }
            elseif ( $base_department == 'residential-lettings' )
            {
                $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_rent']));

                $applicant_profile['max_rent'] = $price;
                $applicant_profile['rent_frequency'] = 'pcm';
                $price_actual = $price; // Stored in pcm
                $applicant_profile['max_price_actual'] = $price_actual;
            }

            if ( $base_department == 'residential-sales' || $base_department == 'residential-lettings' )
            {
                $beds = preg_replace("/[^0-9]/", '', ph_clean($_POST['minimum_bedrooms']));
                $applicant_profile['min_beds'] = $beds;

                if ( isset($_POST['property_type']) && !empty($_POST['property_type']) )
                {
                    $applicant_profile['property_types'] = is_array(ph_clean($_POST['property_type'])) ? ph_clean($_POST['property_type']) : array(ph_clean($_POST['property_type']));
                }
            }

            if ( $base_department == 'commercial' )
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

                $floor_area = preg_replace("/[^0-9.]/", '', ph_clean($_POST['minimum_floor_area']));
                $applicant_profile['min_floor_area'] = $floor_area;
                $applicant_profile['min_floor_area_actual'] = $floor_area;

                $floor_area = preg_replace("/[^0-9.]/", '', ph_clean($_POST['maximum_floor_area']));
                $applicant_profile['max_floor_area'] = $floor_area;
                $applicant_profile['max_floor_area_actual'] = $floor_area;

                if ( isset($_POST['commercial_property_type']) && !empty($_POST['commercial_property_type']) )
                {
                    $applicant_profile['commercial_property_types'] = is_array(ph_clean($_POST['commercial_property_type'])) ? ph_clean($_POST['commercial_property_type']) : array(ph_clean($_POST['commercial_property_type']));
                }
            }

            if ( isset($_POST['location']) && !empty($_POST['location']) )
            {
                $applicant_profile['locations'] = is_array(ph_clean($_POST['location'])) ? ph_clean($_POST['location']) : array(ph_clean($_POST['location']));
            }

            if ( isset($_POST['location_text']) && !empty($_POST['location_text']) )
            {
                $applicant_profile['location_text'] = ph_clean($_POST['location_text']);
            }

            $applicant_profile['notes'] = ( ( isset($_POST['additional_requirements']) ) ? sanitize_textarea_field($_POST['additional_requirements']) : '' );

            $applicant_profile['send_matching_properties'] = 'yes';
            //$applicant_profile['auto_match_disabled'] = ''; // don't know what to do about this yet. Should probably look at global setting and reflect that

            update_post_meta( $contact_post_id, '_applicant_profile_0', $applicant_profile );
            
            if ( get_option( 'propertyhive_applicant_users', '' ) == 'yes' )
            {
                $display_name = ph_clean($_POST['name']);

                // Create user
                $userdata = array(
                    'display_name' => $display_name,
                    'user_login' => sanitize_email($_POST['email_address']),
                    'user_email' => sanitize_email($_POST['email_address']),
                    'user_pass'  => ph_clean($_POST['password']),
                    'role' => 'property_hive_contact',
                    'show_admin_bar_front' => 'false',
                );

                if ( !empty($display_name) )
                {
                    $name_parts = explode( ' ', $display_name );

                    if ( count($name_parts) > 1 )
                    {
                        $userdata['last_name'] = array_pop($name_parts);
                        $userdata['first_name'] = implode(' ', $name_parts);
                    }
                    else
                    {
                        $userdata['last_name'] = $display_name;
                    }
                }

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
            else
            {
                $return['success'] = true;

                do_action( 'propertyhive_applicant_registered', $contact_post_id, 0 );
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

        $contact = new PH_Contact( '', $user_id );

        $contact_post_id = $contact->id;

        $form_controls = ph_get_applicant_requirements_form_fields();
    
        $form_controls = apply_filters( 'propertyhive_applicant_requirements_form_fields', $form_controls, get_post_meta( $contact_post_id, '_applicant_profile_' . ( isset($_POST['profile_id']) && $_POST['profile_id'] != '' ? (int)$_POST['profile_id'] : '0' ), true ) );

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
            $applicant_profile = array();
            $applicant_profile['department'] = ph_clean($_POST['department']);

            $base_department = $_POST['department'];
            if ( !in_array( $base_department, array('residential-sales', 'residential-lettings', 'commercial') ) )
            {
                $base_department = ph_get_custom_department_based_on($base_department);
            }

            if ( $base_department == 'residential-sales' )
            {
                $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_price']));

                $applicant_profile['max_price'] = $price;

                // Not used yet but could be if introducing currencies in the future.
                $applicant_profile['max_price_actual'] = $price;

                $percentage_lower = get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' );
                $percentage_higher = get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' );

                if ( $percentage_lower != '' && $percentage_higher != '' && $_POST['maximum_price'] != '' && $_POST['maximum_price'] != 0 )
                {
                    $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_price']));
                    $applicant_profile['match_price_range_lower'] = $price - ( $price * ( $percentage_lower / 100 ) );
                    $applicant_profile['match_price_range_lower_actual'] = $price - ( $price * ( $percentage_lower / 100 ) );
                    
                    $applicant_profile['match_price_range_higher'] = $price + ( $price * ( $percentage_higher / 100 ) );
                    $applicant_profile['match_price_range_higher_actual'] = $price + ( $price * ( $percentage_higher / 100 ) );
                }
            }
            elseif ( $base_department == 'residential-lettings' )
            {
                $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['maximum_rent']));

                $applicant_profile['max_rent'] = $price;
                $applicant_profile['rent_frequency'] = 'pcm';
                $price_actual = $price; // Stored in pcm
                $applicant_profile['max_price_actual'] = $price_actual;
            }

            if ( $base_department == 'residential-sales' || $base_department == 'residential-lettings' )
            {
                $beds = preg_replace("/[^0-9]/", '', ph_clean($_POST['minimum_bedrooms']));
                $applicant_profile['min_beds'] = $beds;

                if ( isset($_POST['property_type']) && !empty($_POST['property_type']) )
                {
                    $applicant_profile['property_types'] = is_array(ph_clean($_POST['property_type'])) ? ph_clean($_POST['property_type']) : array(ph_clean($_POST['property_type']));
                }
            }

            if ( $base_department == 'commercial' )
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

                $floor_area = preg_replace("/[^0-9.]/", '', ph_clean($_POST['minimum_floor_area']));
                $applicant_profile['min_floor_area'] = $floor_area;
                $applicant_profile['min_floor_area_actual'] = $floor_area;

                $floor_area = preg_replace("/[^0-9.]/", '', ph_clean($_POST['maximum_floor_area']));
                $applicant_profile['max_floor_area'] = $floor_area;
                $applicant_profile['max_floor_area_actual'] = $floor_area;

                if ( isset($_POST['commercial_property_type']) && !empty($_POST['commercial_property_type']) )
                {
                    $applicant_profile['commercial_property_types'] = is_array(ph_clean($_POST['commercial_property_type'])) ? ph_clean($_POST['commercial_property_type']) : array(ph_clean($_POST['commercial_property_type']));
                }
            }

            if ( isset($_POST['location']) && !empty($_POST['location']) )
            {
                $applicant_profile['locations'] = is_array(ph_clean($_POST['location'])) ? ph_clean($_POST['location']) : array(ph_clean($_POST['location']));
            }

            if ( isset($_POST['location_text']) && !empty($_POST['location_text']) )
            {
                $applicant_profile['location_text'] = ph_clean($_POST['location_text']);
            }

            $applicant_profile['notes'] = ( ( isset($_POST['additional_requirements']) ) ? sanitize_textarea_field($_POST['additional_requirements']) : '' );

            $applicant_profile['send_matching_properties'] = 'yes';
            //$applicant_profile['auto_match_disabled'] = ''; // don't know what to do about this yet. Should probably look at global setting and reflect that

            update_post_meta( $contact_post_id, '_applicant_profile_' . ( isset($_POST['profile_id']) && $_POST['profile_id'] != '' ? (int)$_POST['profile_id'] : '0' ), $applicant_profile );

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
            'post_status' => 'publish',
            'nopaging' => true,
            'fields' => 'ids',
        ));

        if ($property_query->have_posts())
        {
            while ($property_query->have_posts())
            {
                $property_query->the_post();

                $num_property_features = get_post_meta(get_the_ID(), '_features', TRUE);
                if ($num_property_features == '') { $num_property_features = 0; }
                
                for ($i = 0; $i < $num_property_features; ++$i)
                {
                    $feature = get_post_meta(get_the_ID(), '_feature_' . $i, TRUE);
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
        
        echo '<div id="existing-owner-details-' . esc_attr($contact_id) . '">';
        
        if ( !is_null( $contact ) )
        {
            echo '<p class="form-field">';
                echo '<label>' . esc_html(__('Name', 'propertyhive')) . '</label>';
                echo '<a href="' . get_edit_post_link( $contact_id ) . '">' . esc_html(get_the_title($contact_id)) . '</a>';
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
                echo '<label>' . esc_html(__('Address', 'propertyhive')) . '</label>';
                echo ( ( !empty($address) ) ? esc_html(implode(", ", $address)) : '-' );
            echo '</p>';
            
            echo '<p class="form-field">';
                echo '<label>' . esc_html(__('Telephone Number', 'propertyhive')) . '</label>';
                echo ( ( get_post_meta($contact_id, '_telephone_number', TRUE) != '' ) ? esc_html(get_post_meta($contact_id, '_telephone_number', TRUE)) : '-' );
            echo '</p>';
            
            echo '<p class="form-field">';
                echo '<label>' . esc_html(__('Email Address', 'propertyhive')) . '</label>';
                echo ( ( get_post_meta($contact_id, '_email_address', TRUE) != '' ) ? esc_html(get_post_meta($contact_id, '_email_address', TRUE)) : '-' );
            echo '</p>';

            $contact_solicitor_contact_id = get_post_meta( $contact_id, '_contact_solicitor_contact_id', true );

            if ( !empty($contact_solicitor_contact_id) )
            {
                $solicitor_contact = new PH_Contact($contact_solicitor_contact_id);

                echo '<p class="form-field">';
                    echo '<label>' . esc_html(__('Solicitor', 'propertyhive')) . '</label>';
                    echo '<a href="' . get_edit_post_link($contact_solicitor_contact_id, '') . '">' . esc_html(get_the_title($contact_solicitor_contact_id) . ( $solicitor_contact->company_name != '' && $solicitor_contact->company_name != get_the_title($contact_solicitor_contact_id) ? ' (' . $solicitor_contact->company_name . ')' : '' )) . '</a>';
                echo '</p>';
            }
        }
        else
        {
            echo esc_html(__( 'Invalid contact record', 'propertyhive' ));
        }
        
        echo '<p class="form-field">';
            echo '<label></label>';
            echo '<a href="" class="button" id="remove-owner-contact-' . esc_attr($contact_id) . '">Remove Owner</a> ';
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
            if ( isset($_POST['exclude_ids']) && $_POST['exclude_ids'] != '' )
            {
                $args['post__not_in'] = explode('|', $_POST['exclude_ids']);
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
                        'post_title' => get_the_title(get_the_ID()) . ( isset($_POST['contact_type']) && $_POST['contact_type'] == 'thirdparty' && $contact->company_name != '' && $contact->company_name != get_the_title(get_the_ID()) ? ' (' . $contact->company_name . ')' : '' ) ,
                        'address_name_number' => $contact->_address_name_number,
                        'address_street' => $contact->_address_street,
                        'address_two' => $contact->_address_two,
                        'address_three' => $contact->_address_three,
                        'address_four' => $contact->_address_four,
                        'address_postcode' => $contact->_address_postcode,
                        'address_country' => $contact->_address_country,
                        'address_full_formatted' => $contact->get_formatted_full_address(', '),
                        'telephone_number' => $contact->_telephone_number,
                        'email_address' => $contact->_email_address,
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
    
    public function search_contacts_where( $where, $wp_query )
    {
        global $wpdb;
        
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( ph_clean($_POST['keyword']) ) ) . '%\'';
        
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
                'post_status' => array( 'publish', 'draft' ),
                'fields' => 'ids'
            );

            $meta_query = array(
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_address_concatenated',
                        'value' => ph_clean($_POST['keyword']),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_reference_number',
                        'value' => ph_clean($_POST['keyword']),
                        'compare' => '='
                    ),
                ),
            );

            if ( isset($_POST['department']) && $_POST['department'] != '' )
            {
                $departments_query = array(
                    'relation' => 'OR',
                );

                $explode_departments = explode("|", ph_clean($_POST['department']));
                $new_departments = array();
                foreach ( $explode_departments as $department )
                {
                    $explode_department = explode("~", $department);

                    $new_departments[] = $explode_department[0];

                    $departments_sub_query = array();

                    $departments_sub_query[] = array(
                        'key' => '_department',
                        'value' => $explode_department[0],
                    );

                    if ( $explode_department[0] == 'commercial' && isset($explode_department[1]) )
                    {
                        switch ($explode_department[1])
                        {
                            case "forsale":
                            {
                                $departments_sub_query[] = array(
                                    'key' => '_for_sale',
                                    'value' => 'yes',
                                );
                                break;
                            }
                        }
                    }

                    $departments_query[] = $departments_sub_query;
                }
                $meta_query[] = $departments_query;
            }
            
            if ( !empty($meta_query) )
            {
                $args['meta_query'] = $meta_query;
            }

            $property_query = new WP_Query( $args );
            
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

                    $post_title = $property->get_formatted_full_address();
                    if ( get_post_status() == 'draft' )
                    {
                        $post_title .= ' - Draft';
                    }
                    
                    $return[] = array(
                        'ID' => get_the_ID(),
                        'post_title' => $post_title,
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
                'role__not_in' => apply_filters( 'property_negotiator_exclude_roles', array('property_hive_contact', 'subscriber') )
            );

            $args = apply_filters( 'propertyhive_negotiators_query', $args );
            
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

        if ( ! current_user_can( 'manage_propertyhive' ) )
            wp_die( __( 'You do not have permission to manage notes', 'propertyhive' ), 403 );
        
		$post_id = (int)$_POST['post_id'];

		if ( $post_id > 0 ) {

            $note = wp_kses_post( trim( stripslashes( $_POST['note'] ) ) );

            // Add note/comment to property
            $comment = array(
                'note_type' => 'note',
                'note' => $note
            );

            if ( isset($_POST['pinned']) )
            {
                $comment['pinned'] = '1';
            }

            $comment_id = PH_Comments::insert_note( $post_id, $comment );

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

        if ( ! current_user_can( 'manage_propertyhive' ) )
            wp_send_json_error( __( 'You do not have permission to manage notes', 'propertyhive' ), 403 );

		$note_id = (int)$_POST['note_id'];

		if ( $note_id > 0 ) {
			wp_delete_comment( $note_id );

            wp_send_json_success();
		}

		wp_send_json_error();
	}

    /**
     * Change existing note entry to be pinned
     */
    public function toggle_note_pinned() {

        check_ajax_referer( 'pin-note', 'security' );

        if ( ! current_user_can( 'manage_propertyhive' ) )
            wp_send_json_error( __( 'You do not have permission to manage notes', 'propertyhive' ), 403 );

        $note_id = (int)$_POST['note_id'];

        if ( $note_id > 0 ) {

            $comment = get_comment($note_id);
            $comment_content = @unserialize($comment->comment_content, ['allowed_classes' => false]);

            if ( $comment_content !== false )
            {
                if ( isset($comment_content['pinned']))
                {
                    unset($comment_content['pinned']);
                }
                else
                {
                    $comment_content['pinned'] = '1';
                }
            }

            wp_update_comment( array('comment_ID' => $_POST['note_id'], 'comment_content' => serialize($comment_content)) );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function get_notes_grid() {

        global $wpdb, $post;

        check_ajax_referer( 'get-notes', 'security' );

        if ( ! current_user_can( 'manage_propertyhive' ) )
            wp_die( __( 'You do not have permission to manage notes', 'propertyhive' ), 403 );
        
        $post = get_post((int)$_POST['post_id']);

        $section = $_POST['section'];
        include( PH()->plugin_path() . '/includes/admin/views/html-display-notes.php' );

        // Quit out
        die();
    }

    public function get_pinned_notes_grid() {

        global $wpdb, $post;

        check_ajax_referer( 'get-notes', 'security' );

        if ( ! current_user_can( 'manage_propertyhive' ) )
            wp_die( __( 'You do not have permission to manage notes', 'propertyhive' ), 403 );
        
        $post = get_post((int)$_POST['post_id']);

        $section = $_POST['section'];
        include( PH()->plugin_path() . '/includes/admin/views/html-display-notes.php' );

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

            $form_controls = apply_filters( 'propertyhive_property_enquiry_form_fields', $form_controls, ph_clean($_POST['property_id']) );
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
            if ( in_array( $key, array('recaptcha', 'recaptcha-v3') ) )
            {
                $errors = $this->check_recaptcha_form_response($errors, $key, $control);
            }
            if ( $key == 'hCaptcha' )
            {
                $secret = isset( $control['secret'] ) ? $control['secret'] : '';
                $response = isset( $_POST['h-captcha-response'] ) ? ph_clean($_POST['h-captcha-response']) : '';

                $response = wp_remote_post(
                    'https://hcaptcha.com/siteverify',
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
                        $errors[] = 'Error decoding response from hCaptcha check';
                    }
                    else
                    {
                        if ( isset($response['success']) && $response['success'] == true )
                        {

                        }
                        else
                        {
                            $errors[] = 'Failed hCaptcha validation';
                        }
                    }
                }
            }
        }

        if ( 
            get_option( 'propertyhive_property_enquiry_form_disclaimer', '' ) != '' &&
            ( 
                !isset( $_POST['disclaimer'] ) || 
                ( 
                    isset( $_POST['disclaimer'] ) && empty( $_POST['disclaimer'] ) 
                ) 
            )
        )
        {
            $errors[] = __( 'Missing required field', 'propertyhive' ) . ': disclaimer';
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
                        default:
                        {
                            $fields_to_check[] = '_office_email_address_' . str_replace("residential-", "", $property_department);
                            $fields_to_check[] = '_office_email_address_sales';
                            $fields_to_check[] = '_office_email_address_lettings';
                            $fields_to_check[] = '_office_email_address_commercial';
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

            $message .= ( count($property_ids) > 1 ? __( 'Properties', 'propertyhive' ) : __( 'Property', 'propertyhive' ) ) . ":\n";
            foreach ( $property_ids as $property_id )
            {
                $property = new PH_Property((int)$property_id);
                $message .= apply_filters( 'propertyhive_property_enquiry_property_output', $property->get_formatted_full_address() . "\n" . html_entity_decode(strip_tags($property->get_formatted_price())) . "\n" . get_permalink( (int)$property_id ), (int)$property_id ) . "\n\n";
            }

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

            if ( 
                apply_filters('propertyhive_enquiry_email_show_manage_link', true) &&
                count($property_ids) == 1 &&
                get_option( 'propertyhive_module_disabled_enquiries', '' ) != 'yes' &&
                get_option( 'propertyhive_store_property_enquiries', 'yes' ) == 'yes'
            )
            {
                $post_type_object = get_post_type_object( 'property' );
                $property_enquiries_url = admin_url( sprintf( $post_type_object->_edit_link . '&action=edit', (int)$property_ids[0] ) ) . '#propertyhive-property-enquiries';
                $message .= "\n" . __( "To manage this enquiry please visit the following URL", 'propertyhive' ) . ':' . "\n\n";
                $message .= $property_enquiries_url;
            }

            $message = apply_filters( 'propertyhive_property_enquiry_post_body', $message, $property_ids );
            
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
                $headers[] = 'From: ' . html_entity_decode(ph_clean( $_POST['name'] )) . ' <' . sanitize_email( $from_email_address ) . '>';
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

            do_action( 'propertyhive_before_property_enquiry_sent' );

            $sent = wp_mail( $to, $subject, $message, $headers );

            do_action( 'propertyhive_after_property_enquiry_sent' );
            
            if ( ! $sent )
            {
                $return['success'] = false;
                $return['reason'] = 'nosend';
                $return['errors'] = $errors;
            }
            else
            {
                $return['success'] = true;

                $enquiry_post_id = '';
                
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

                do_action('propertyhive_property_enquiry_sent', $_POST, $to, $enquiry_post_id);

                // Send auto-responder
                if ( get_option( 'propertyhive_enquiry_auto_responder', '' ) == 'yes' )
                {
                    // Auto-responder enabled
                    PH()->email->send_enquiry_auto_responder( $_POST );
                }
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
        $address = false;
        $postcode = false;
        $property_id = false;

        foreach ($enquiry_meta as $key => $value)
        {
            if ( strpos(strtolower($key), 'name') !== false && $value[0] != '' )
            {
                if ( $name === false )
                {
                    $name = $value[0];
                }
                else
                {
                    $name .= ' ' . $value[0];
                }
            }
            elseif ( strpos(strtolower($key), 'email') !== false && $value[0] != '' )
            {
                if ( $email === false )
                {
                    $email = $value[0];
                }
                else
                {
                    $email .= ',' . $value[0];
                }
            }
            elseif ( strpos(strtolower($key), 'phone') !== false && $value[0] != '' )
            {
                if ( $telephone === false )
                {
                    $telephone = $value[0];
                }
                else
                {
                    $telephone .= ',' . $value[0];
                }
            }
            elseif ( strtolower($key) == 'address' && $value[0] != '' )
            {
                $address = $value[0];
            }
            elseif ( strtolower($key) == 'postcode' && $value[0] != '' )
            {
                $postcode = $value[0];
            }
            elseif ( !$property_id && strpos(strtolower($key), 'property_id') !== false && !empty($value[0]) )
            {
                $property_id = (int)$value[0];
            }
        }

        if ( $name === false && $email === false )
        {
            die( json_encode( array('error' => 'Name and email address not found') ) );
        }

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

        update_post_meta( $enquiry_post_id, '_contact_id', $contact_post_id );

        if ( $telephone !== FALSE ) { 
            update_post_meta( $contact_post_id, '_telephone_number', ph_clean( ph_clean_telephone_number( $telephone ) ) );
        }

        if ( $email !== FALSE ) { update_post_meta( $contact_post_id, '_email_address', ph_clean( $email ) ); }

        if ( $address !== FALSE )
        {
            if ( strpos(strtolower($address), ',') !== false )
            {
                // Split name/number and street by the first comma
                $address_parts = explode(',', $address, 2);
                update_post_meta( $contact_post_id, '_address_name_number', ph_clean( trim($address_parts[0]) ) );
                update_post_meta( $contact_post_id, '_address_street', ph_clean( trim($address_parts[1]) ) );
            }
            else
            {
                $address_parts = explode(' ', $address, 2);
                // If first "word" starts with a number (123, 1A etc), put it in name/number
                if ( is_numeric(substr($address_parts[0], 0, 1)) )
                {
                    update_post_meta( $contact_post_id, '_address_name_number', ph_clean( trim($address_parts[0]) ) );
                    update_post_meta( $contact_post_id, '_address_street', ph_clean( trim($address_parts[1]) ) );
                }
                else
                {
                    update_post_meta( $contact_post_id, '_address_name_number', ph_clean( $address ) );
                }
            }
        }

        if ( $postcode !== FALSE ) { update_post_meta( $contact_post_id, '_address_postcode', ph_clean( $postcode ) ); }

        // Enquiry is related to a property, so create an applicant record for the contact
        if ( !empty( $property_id ) && get_post_type( $property_id ) == 'property' )
        {
            update_post_meta( $contact_post_id, '_applicant_profiles', '1' );

            $applicant_profile = array();
            $applicant_profile['department'] = get_post_meta( $property_id, '_department', TRUE );

            $base_department = $applicant_profile['department'];
            if ( !in_array( $base_department, array('residential-sales', 'residential-lettings', 'commercial') ) )
            {
                $base_department = ph_get_custom_department_based_on($base_department);
            }

            if ( $base_department == 'residential-sales' )
            {
                $property_price = preg_replace("/[^0-9]/", '', ph_clean(get_post_meta( $property_id, '_price', TRUE )));

                if ( !empty($property_price) )
                {
                    $applicant_profile['max_price'] = $property_price;

                    // Not used yet but could be if introducing currencies in the future.
                    $applicant_profile['max_price_actual'] = $property_price;

                    $percentage_lower = get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' );
                    $percentage_higher = get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' );

                    if ( $percentage_lower != '' && $percentage_higher != '' )
                    {
                        $applicant_profile['match_price_range_lower'] = $property_price - ( $property_price * ( $percentage_lower / 100 ) );
                        $applicant_profile['match_price_range_lower_actual'] = $property_price - ( $property_price * ( $percentage_lower / 100 ) );

                        $applicant_profile['match_price_range_higher'] = $property_price + ( $property_price * ( $percentage_higher / 100 ) );
                        $applicant_profile['match_price_range_higher_actual'] = $property_price + ( $property_price * ( $percentage_higher / 100 ) );
                    }
                }
            }
            elseif ( $base_department == 'residential-lettings' )
            {
                $property_rent = preg_replace("/[^0-9]/", '', ph_clean(get_post_meta( $property_id, '_rent', TRUE )));
                $property_rent_freq = get_post_meta( $property_id, '_rent_frequency', TRUE );

                $applicant_profile['max_rent'] = $property_rent;
                $applicant_profile['rent_frequency'] = $property_rent_freq;

                $price_actual = $property_rent; // Used for ordering properties. Stored in pcm
                switch ( $property_rent_freq )
                {
                    case "pw": { $price_actual = ($property_rent * 52) / 12; break; }
                    case "pcm": { $price_actual = $property_rent; break; }
                    case "pq": { $price_actual = ($property_rent * 4) / 52; break; }
                    case "pa": { $price_actual = ($property_rent / 52); break; }
                }
                $applicant_profile['max_price_actual'] = $price_actual;
            }

            if ( $base_department == 'residential-sales' || $base_department == 'residential-lettings' )
            {
                $beds = preg_replace("/[^0-9]/", '', ph_clean(get_post_meta( $property_id, '_bedrooms', TRUE )));
                $applicant_profile['min_beds'] = $beds;
            }

            if ( $base_department == 'commercial' )
            {
                $property_for_sale = get_post_meta( $property_id, '_for_sale', TRUE );
                $property_to_rent = get_post_meta( $property_id, '_to_rent', TRUE );

                $available_as = array();
                if ( $property_for_sale == 'yes' )
                {
                    $available_as[] = 'sale';
                }
                if ( $property_to_rent == 'yes' )
                {
                    $available_as[] = 'rent';
                }
                $applicant_profile['available_as'] = $available_as;
            }

            $applicant_profile['send_matching_properties'] = apply_filters( 'propertyhive_default_applicant_send_matching_properties', false ) === true ? 'yes' : '';
            $applicant_profile['auto_match_disabled'] = 'yes';

            $applicant_profile['added_from_enquiry'] = 'yes';

            update_post_meta( $contact_post_id, '_applicant_profile_0', $applicant_profile );

            update_post_meta( $contact_post_id, '_contact_types', array( 'applicant' ) );
        }

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

    public function merge_contact_records()
    {
        $this->json_headers();

        if ( !isset( $_POST['contact_ids'] ) || !isset( $_POST['primary_contact_id'] ) )
        {
            $return = array('error' => 'Invalid parameters received');
            echo json_encode( $return );
            die();
        }

        $contacts_to_merge = explode('|', $_POST['contact_ids']);
        $primary_contact_id = $_POST['primary_contact_id'];

        if ( !is_array($contacts_to_merge) || !in_array( $primary_contact_id, $contacts_to_merge )  )
        {
            $return = array('error' => 'Invalid Contact IDs received');
            echo json_encode( $return );
            die();
        }

        // Check each post ID passed through is in fact of post type 'contact'
        foreach ( $contacts_to_merge as $child_contact_id )
        {
            if ( get_post_type((int)$child_contact_id) != 'contact' )
            {
                $return = array('error' => 'Contact ID ' . $child_contact_id . ' received which is not a contact');
                echo json_encode( $return );
                die();
            }
        }

        // Remove primary from list
        unset($contacts_to_merge[array_search($primary_contact_id, $contacts_to_merge)]);

        include_once( 'includes/class-ph-admin-merge-contacts.php' );
        $ph_admin_merge_contacts = new PH_Admin_Merge_Contacts();
        $ph_admin_merge_contacts->do_merge( $primary_contact_id, $contacts_to_merge );

        echo json_encode( array('success' => true) );
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

        $args = apply_filters( 'propertyhive_admin_dashboard_viewings_awaiting_applicant_feedback_args', $args );

        $viewings_query = new WP_Query( $args );

        if ( $viewings_query->have_posts() )
        {
            while ( $viewings_query->have_posts() )
            {
                $viewings_query->the_post();

                $property_id = get_post_meta( get_the_ID(), '_property_id', TRUE );
                $property = new PH_Property((int)$property_id);

                $applicant_contact_ids = get_post_meta( get_the_ID(), '_applicant_contact_id' );

                $return[] = array(
                    'ID' => get_the_ID(),
                    'edit_link' => get_edit_post_link( get_the_ID() ),
                    'start_date_time' => get_post_meta( get_the_ID(), '_start_date_time', TRUE ),
                    'start_date_time_formatted_Hi_jSFY' => date("H:i jS F Y", strtotime(get_post_meta( get_the_ID(), '_start_date_time', TRUE ))),
                    'property_id' => $property_id,
                    'property_address' => $property->get_formatted_full_address(),
                    'applicant_contact_id' => $applicant_contact_ids[0],
                    'applicant_name' => get_the_title( $applicant_contact_ids[0] ),
                );
            }
        }

        wp_reset_postdata();

        echo json_encode($return);

        die();
    }

    public function get_my_upcoming_appointments()
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
                    'value' => 'pending'
                ),
                array(
                    'key' => '_start_date_time',
                    'value' => date("Y-m-d H:i:s"),
                    'compare' => '>='
                ),
                array(
                    'key' => '_negotiator_id',
                    'value' => get_current_user_id(),
                ),
            )
        );

        $args = apply_filters( 'propertyhive_admin_dashboard_my_upcoming_appointments_viewing_args', $args );
        $args = apply_filters( 'propertyhive_admin_dashboard_my_upcoming_appointments_args', $args );

        $viewings_query = new WP_Query( $args );

        if ( $viewings_query->have_posts() )
        {
            while ( $viewings_query->have_posts() )
            {
                $viewings_query->the_post();

                $property_id = get_post_meta( get_the_ID(), '_property_id', TRUE );
                $property = new PH_Property((int)$property_id);

                $return[] = array(
                    'ID' => get_the_ID(),
                    'edit_link' => get_edit_post_link( get_the_ID() ),
                    'start_date_time' => get_post_meta( get_the_ID(), '_start_date_time', TRUE ),
                    'start_date_time_formatted_Hi_jSFY' => date("H:i jS F Y", strtotime(get_post_meta( get_the_ID(), '_start_date_time', TRUE ))),
                    'start_date_time_timestamp' => strtotime(get_post_meta( get_the_ID(), '_start_date_time', TRUE )),
                    'title' => 'Viewing at ' . $property->get_formatted_full_address(),
                );
            }
        }

        wp_reset_postdata();

        $args = array(
            'post_type' => 'appraisal',
            'fields' => 'ids',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_status',
                    'value' => 'pending'
                ),
                array(
                    'key' => '_start_date_time',
                    'value' => date("Y-m-d H:i:s"),
                    'compare' => '>='
                ),
                array(
                    'key' => '_negotiator_id',
                    'value' => get_current_user_id(),
                ),
            )
        );

        $args = apply_filters( 'propertyhive_admin_dashboard_my_upcoming_appointments_appraisal_args', $args );
        $args = apply_filters( 'propertyhive_admin_dashboard_my_upcoming_appointments_args', $args );

        $appraisals_query = new WP_Query( $args );

        if ( $appraisals_query->have_posts() )
        {
            while ( $appraisals_query->have_posts() )
            {
                $appraisals_query->the_post();

                $appraisal = new PH_Appraisal(get_the_ID());

                $return[] = array(
                    'ID' => get_the_ID(),
                    'edit_link' => get_edit_post_link( get_the_ID() ),
                    'start_date_time' => get_post_meta( get_the_ID(), '_start_date_time', TRUE ),
                    'start_date_time_formatted_Hi_jSFY' => date("H:i jS F Y", strtotime(get_post_meta( get_the_ID(), '_start_date_time', TRUE ))),
                    'start_date_time_timestamp' => strtotime(get_post_meta( get_the_ID(), '_start_date_time', TRUE )),
                    'title' => 'Appraisal at ' . $appraisal->get_formatted_full_address(),
                );
            }
        }

        wp_reset_postdata();

        $return = apply_filters( 'propertyhive_dashboard_my_upcoming_appointments', $return );

        if ( !empty($return) )
        {
            $sort = array();
            foreach ($return as $key => $part) {
                   $sort[$key] = strtotime($part['start_date_time']);
            }
            array_multisort($sort, SORT_ASC, $return);

            $return = array_slice($return, 0, 10);
        }

        echo json_encode($return);

        die();
    }

    public function get_upcoming_overdue_key_dates()
    {
        global $post;

        $this->json_headers();

        $return = array();

        $meta_query = array(
            array(
                'key' => '_key_date_status',
                'value' => 'pending',
            ),
        );

        $upcoming_threshold = new DateTime('+ ' . apply_filters( 'propertyhive_key_date_upcoming_days', 7 ) . ' DAYS');
        $meta_query[] = array(
            'key' => '_date_due',
            'value' => $upcoming_threshold->format('Y-m-d'),
            'type' => 'date',
            'compare' => '<=',
        );

        $args = array(
            'post_type' => 'key_date',
            'fields' => 'ids',
            'post_status' => 'publish',
            'meta_query' => $meta_query,
            'meta_key' => '_date_due',
            'orderby' => 'meta_value',
            'order' => 'ASC',
        );

        $args = apply_filters( 'propertyhive_admin_dashboard_upcoming_overdue_key_dates_args', $args );

        $key_dates_query = new WP_Query( $args );

        if ( $key_dates_query->have_posts() )
        {
            while ( $key_dates_query->have_posts() )
            {
                $key_dates_query->the_post();

                $key_date = new PH_Key_Date( get_post( get_the_ID() ) );

                $property_id = get_post_meta( get_the_ID(), '_property_id', TRUE );
                $property = new PH_Property((int)$property_id);
                $property_edit_link = get_edit_post_link( $property_id );

                $tenancy_id = get_post_meta( get_the_ID(), '_tenancy_id', TRUE );
                if ( !empty($tenancy_id) )
                {
                    $key_date_edit_link = get_edit_post_link( $tenancy_id ) . '#propertyhive-tenancy-management%7Cpropertyhive-management-dates';
                }
                else
                {
                    $key_date_edit_link = $property_edit_link . '#propertyhive-property-tenancies%7Cpropertyhive-management-dates';
                }

                $due_date = $key_date->date_due();
                $date_format = 'jS F Y';
                if ( $due_date->format('H:i') != '00:00' )
                {
                    $date_format = 'H:i ' . $date_format;
                }

                $return[] = array(
                    'ID' => get_the_ID(),
                    'key_date_edit_link' => $key_date_edit_link,
                    'description' => $key_date->description(),
                    'upcoming_overdue_status' => $key_date->status(),
                    'property_edit_link' => $property_edit_link,
                    'property_address' => $property->get_formatted_full_address(),
                    'due_date_time_formatted' => $due_date->format($date_format),
                );
            }
        }

        wp_reset_postdata();

        echo json_encode($return);

        die();
    }

    public function osm_geocoding_request()
    {
        check_ajax_referer( 'osm_geocoding_request', 'security' );

        $this->json_headers();

        $lat = '';
        $lng = '';
        $error = '';

        $request_url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=" . strtolower(ph_clean($_POST['country'])) . "&addressdetails=1&q=" . urlencode(ph_clean($_POST['address']));
        
        $response = wp_remote_get($request_url);

        if ( !is_wp_error( $response ))
        {
            if ( is_array( $response ) )
            {
                $body = wp_remote_retrieve_body( $response );
                $json = json_decode($body, true);

                if ( !empty($json) && isset($json[0]['lat']) && isset($json[0]['lon']) )
                {
                    $lat = $json[0]['lat'];
                    $lng = $json[0]['lon'];
                }
                else
                {
                    $error = 'No co-ordinates returned for the address provided: ' . ph_clean($_POST['address']);
                }
            }
        }
        else
        {
            $error = $response->get_error_message();
        }
        echo json_encode(array('error' => $error, 'lat' => $lat, 'lng' => $lng));

        die();
    }

    public function get_property_marketing_statistics_meta_box()
    {
        check_ajax_referer( 'get_property_marketing_statistics_meta_box', 'security' );

        global $post;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

            $view_statistics = get_post_meta( (int)$_POST['post_id'], '_view_statistics', TRUE );
            if ( !is_array($view_statistics) )
            {
                $view_statistics = array();
            }

            $date_from = isset($_POST['statistics_date_from']) ? ph_clean($_POST['statistics_date_from']) : date("Y-m-d", strtotime('7 days ago'));
            $date_from = strtotime($date_from);

            $date_to = isset($_POST['statistics_date_to']) ? ph_clean($_POST['statistics_date_to']) : date("Y-m-d");
            $date_to = strtotime($date_to);

            $view_statistics_output = array();
            $total_views = 0;

            for ($i = $date_from; $i <= $date_to; $i += 86400) 
            { 
                if ( isset($view_statistics[date("Y-m-d", $i)]) )
                {
                    $view_statistics_output[] = array( $i * 1000, $view_statistics[date("Y-m-d", $i)] );
                    $total_views += $view_statistics[date("Y-m-d", $i)];
                }
                else
                {
                    $view_statistics_output[] = array( $i * 1000, 0 );
                }
            }

            echo '<h3>' . __( 'Views On Website', 'propertyhive' ) . ' (' . number_format($total_views, 0) . ')</h3>';

            echo '<div id="marketing_statistics_website_view_graph" style="height:400px; width:100%;"></div>';
        
        echo '</div>';
        
        echo '</div>';

        echo '<input type="hidden" name="marketing_statistics" id="marketing_statistics" value="' . esc_attr(json_encode($view_statistics_output)) . '">';

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
        
            <label for="">' . esc_html(__('Status', 'propertyhive')) . '</label>
            
            ' . esc_html(ucwords(str_replace("_", " ", $appraisal->status)));
        
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
            $ph_countries = new PH_Countries();
            
            $currency = 'GBP';
            $currency_symbol = '&pound;';

            $default_country = get_option( 'propertyhive_default_country', 'GB' );
            $countries = get_option( 'propertyhive_countries', array( $default_country ) );
            if ( count($countries) == 1 )
            {
                foreach ( $countries as $country )
                {
                    $country = $ph_countries->get_country( $country );

                    $currency = $country['currency_code'];
                }
            }
            
            $currency = $ph_countries->get_currency( $currency );
            if ( isset($currency['currency_symbol']) )
            {
                $currency_symbol = $currency['currency_symbol'];
            }

            if ( $appraisal->department == 'residential-sales' )
            {
                $args = array( 
                    'id' => '_valued_price', 
                    'label' => __( 'Valued Price', 'propertyhive' ) . ' (' . $currency_symbol . ')', 
                    'desc_tip' => false, 
                    'class' => 'short',
                    'value' => ph_display_price_field( $appraisal->valued_price ),
                );
                propertyhive_wp_text_input( $args );
            }
            elseif ( $appraisal->department == 'residential-lettings' )
            {
                $rent_frequency = $appraisal->valued_rent_frequency;

                echo '<p class="form-field">
        
                    <label for="">' . esc_html(__('Valued Rent', 'propertyhive')) . ' (' . $currency_symbol . ')</label>

                    <input type="text" class="" name="_valued_rent" id="_valued_rent" value="' . esc_attr(ph_display_price_field( $appraisal->valued_rent )) . '" placeholder="" style="width:10%; min-width:100px;">
                
                    <select id="_valued_rent_frequency" name="_valued_rent_frequency" class="select" style="width:auto">
                        <option value="pd"' . ( ($rent_frequency == 'pd') ? ' selected' : '') . '>' . esc_html(__('Per Day', 'propertyhive')) . '</option>
                        <option value="pppw"' . ( ($rent_frequency == 'pppw') ? ' selected' : '') . '>' . esc_html(__('Per Person Per Week', 'propertyhive')) . '</option>
                        <option value="pw"' . ( ($rent_frequency == 'pw') ? ' selected' : '') . '>' . esc_html(__('Per Week', 'propertyhive')) . '</option>
                        <option value="pcm"' . ( ($rent_frequency == 'pcm' || $rent_frequency == '') ? ' selected' : '') . '>' . esc_html(__('Per Calendar Month', 'propertyhive')) . '</option>
                        <option value="pq"' . ( ($rent_frequency == 'pq') ? ' selected' : '') . '>' . esc_html(__('Per Quarter', 'propertyhive')) . '</option>
                        <option value="pa"' . ( ($rent_frequency == 'pa') ? ' selected' : '') . '>' . esc_html(__('Per Annum', 'propertyhive')) . '</option>
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
        $show_customise_confirmation_meta_boxes = false;

        $actions = array();

        if ( $status == 'pending' )
        {
            $owner_booking_confirmation_sent_at = get_post_meta( $post_id, '_owner_booking_confirmation_sent_at', TRUE );

            $appraisal_department = get_post_meta( $post_id, '_department', TRUE );
            $owner_contact_id = get_post_meta( $post_id, '_property_owner_contact_id', TRUE );
            $owner_or_landlord = ( $appraisal_department == 'residential-lettings' ? 'Landlord' : 'Owner' );

            if ( !empty($owner_contact_id) )
            {
                if ( get_option( 'propertyhive_customise_confirmation_emails', '' ) == 'yes' )
                {
                    $actions[] = '<a 
                            href="#action_panel_appraisal_email_owner_booking_confirmation_customise" 
                            class="button appraisal-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $owner_booking_confirmation_sent_at == '' ) ? esc_html(__('Email ' . $owner_or_landlord . ' Booking Confirmation', 'propertyhive')) : esc_html(__('Re-Email ' . $owner_or_landlord . ' Booking Confirmation', 'propertyhive') ) ) . '</a>';

                    $show_customise_confirmation_meta_boxes = true;
                }
                else
                {
                    $actions[] = '<a
                            href="#action_panel_appraisal_email_owner_booking_confirmation"
                            class="button appraisal-action"
                            style="width:100%; margin-bottom:7px; text-align:center"
                        >' . ( ( $owner_booking_confirmation_sent_at == '' ) ? esc_html(__('Email ' . $owner_or_landlord . ' Booking Confirmation', 'propertyhive')) : esc_html(__('Re-Email ' . $owner_or_landlord . ' Booking Confirmation', 'propertyhive') )) . '</a>';
                }

                $actions[] = '<div id="appraisal_owner_confirmation_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . ( ( $owner_booking_confirmation_sent_at == '' ) ? 'display:none' : '' ) . '">' . ( ( $owner_booking_confirmation_sent_at != '' ) ? 'Previously sent to ' . esc_html(strtolower($owner_or_landlord)) . ' on <span title="' . esc_attr($owner_booking_confirmation_sent_at) . '">' . esc_html(date("jS F", strtotime($owner_booking_confirmation_sent_at))) . '</span>' : '' ) . '</div>';

                $actions[] = '<hr>';
            }

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
                >' . esc_html(__('Appraisal Carried Out', 'propertyhive')) . '</a>';
            $actions[] = '<a 
                    href="#action_panel_appraisal_cancelled" 
                    class="button appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . esc_html(__('Appraisal Cancelled', 'propertyhive')) . '</a>';

            $show_cancelled_meta_boxes = true;
            $show_carried_out_meta_boxes = true;
        }

        if ( $status == 'carried_out' )
        {
            $actions[] = '<a 
                    href="#action_panel_appraisal_won" 
                    class="button button-success appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . esc_html(__('Appraisal Won', 'propertyhive')) . '</a>';

            $actions[] = '<a 
                    href="#action_panel_appraisal_lost" 
                    class="button button-danger appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . esc_html(__('Appraisal Lost', 'propertyhive')) . '</a>';

            $show_lost_meta_boxes = true;
        }

        if ( $status == 'won' )
        {
            $actions[] = '<a 
                    href="#action_panel_appraisal_instruct" 
                    class="button button-success appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . esc_html(__('Instruct Property', 'propertyhive')) . '</a>';

            $show_instructed_meta_boxes = true;
        }

        if ( $status == 'won' || $status == 'lost' )
        {
            $actions[] = '<a 
                    href="#action_panel_appraisal_revert_carried_out" 
                    class="button appraisal-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . esc_html(__('Revert To Carried Out', 'propertyhive')) . '</a>';
        }

        if ( $status == 'instructed' )
        {
            $property_id = get_post_meta( $post_id, '_property_id', TRUE );

            $actions[] = '<a 
                    href="' . get_edit_post_link($property_id) . '" 
                    class="button"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . esc_html(__('View Instructed Property', 'propertyhive')) . '</a>';

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
                >' . esc_html(__('Revert To Pending', 'propertyhive')) . '</a>';
        }

        $actions = apply_filters( 'propertyhive_admin_appraisal_actions', $actions, $post_id );
        $actions = apply_filters( 'propertyhive_admin_post_actions', $actions, $post_id );

        if ( !empty($actions) )
        {
            echo implode("", $actions);
        }
        else
        {
            echo '<div style="text-align:center">' . esc_html(__( 'No actions to display', 'propertyhive' )) . '</div>';
        }

        echo '</div>

        </div>';

        // Success action panel
        echo '<div id="action_panel_success" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
                     
            <div class="options_group" style="padding-top:8px;">

                <div id="success_actions"></div>

                <a class="button action-cancel" style="width:100%;" href="#">' . esc_html(__( 'Back To Actions', 'propertyhive' )) . '</a>

            </div>

        </div>';

        do_action( 'propertyhive_admin_appraisal_action_options', $post_id );
        do_action( 'propertyhive_admin_post_action_options', $post_id );

        if ( $show_customise_confirmation_meta_boxes )
        {
            $subject = get_option( 'propertyhive_appraisal_owner_booking_confirmation_email_subject', '' );
            $body = get_option( 'propertyhive_appraisal_owner_booking_confirmation_email_body', '' );

            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_appraisal_email_owner_booking_confirmation_customise" style="display:none;">

                <div class="options_group" style="padding-top:8px;">

                    <div class="form-field">

                        <label for="_owner_confirmation_email_subject">' . esc_html(__( 'Subject', 'propertyhive' )) . '</label>
                        
                        <input id="_owner_confirmation_email_subject" name="_owner_confirmation_email_subject" style="width:100%;" value="' . esc_attr($subject) . '">

                    </div>

                    <div class="form-field">

                        <label for="_owner_confirmation_email_body">' . esc_html(__( 'Body', 'propertyhive' )) . '</label>
                        
                        <textarea id="_owner_confirmation_email_body" name="_owner_confirmation_email_body" style="width:100%; height:100px;">' . esc_html($body) . '</textarea>

                    </div>

                    <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
                    <a class="button button-primary owner-booking-confirmation-action-submit" href="#">' . esc_html(__( 'Send', 'propertyhive' )) . '</a>

                </div>

            </div>';
        }

        if ( $show_cancelled_meta_boxes )
        {
            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_appraisal_cancelled" style="display:none;">

                <div class="options_group" style="padding-top:8px;">

                    <div class="form-field">

                        <label for="_appraisal_cancelled_reason">' . esc_html(__( 'Reason Cancelled', 'propertyhive' )) . '</label>
                        
                        <textarea id="_cancelled_reason" name="_cancelled_reason" style="width:100%;">' . esc_html(get_post_meta( $post_id, '_cancelled_reason', TRUE )) . '</textarea>

                    </div>

                    <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
                    <a class="button button-primary cancelled-reason-action-submit" href="#">' . esc_html(__( 'Save', 'propertyhive' )) . '</a>

                </div>

            </div>';
        }

        if ( $show_carried_out_meta_boxes )
        {
            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_appraisal_carried_out" style="display:none;">

                <div class="options_group" style="padding-top:8px;">';

            $ph_countries = new PH_Countries();

            $currency = 'GBP';
            $currency_symbol = '&pound;';

            $default_country = get_option( 'propertyhive_default_country', 'GB' );
            $countries = get_option( 'propertyhive_countries', array( $default_country ) );
            if ( count($countries) == 1 )
            {
                foreach ( $countries as $country )
                {
                    $country = $ph_countries->get_country( $country );

                    $currency = $country['currency_code'];
                }
            }
            
            $currency = $ph_countries->get_currency( $currency );
            if ( isset($currency['currency_symbol']) )
            {
                $currency_symbol = $currency['currency_symbol'];
            }

            if ( $department == 'residential-sales' )
            {
                echo '<div class="form-field">

                        <label for="_price">' . esc_html(__( 'Valued Price (' . $currency_symbol . ')', 'propertyhive' )) . '</label>
                        
                        <input type="text" id="_price" name="_price" style="width:100%;" value="' . esc_attr(get_post_meta( $post_id, '_valued_price', TRUE )) . '">

                    </div>';
            }
            else
            {
                $rent_frequency = get_post_meta( $post_id, '_valued_rent_frequency', TRUE );
                echo '<div class="form-field">

                        <label for="_price">' . esc_html(__( 'Valued Rent (' . $currency_symbol . ')', 'propertyhive' )) . '</label>
                        
                        <input type="text" id="_price" name="_price" style="width:100%;" value="' . esc_attr(get_post_meta( $post_id, '_valued_rent', TRUE )) . '">

                        <select id="_rent_frequency" name="_rent_frequency" class="select" style="width:100%">
                            <option value="pd"' . ( ($rent_frequency == 'pd') ? ' selected' : '') . '>' . esc_html(__('Per Day', 'propertyhive')) . '</option>
                            <option value="pppw"' . ( ($rent_frequency == 'pppw') ? ' selected' : '') . '>' . esc_html(__('Per Person Per Week', 'propertyhive')) . '</option>
                            <option value="pw"' . ( ($rent_frequency == 'pw') ? ' selected' : '') . '>' . esc_html(__('Per Week', 'propertyhive')) . '</option>
                            <option value="pcm"' . ( ($rent_frequency == 'pcm' || $rent_frequency == '') ? ' selected' : '') . '>' . esc_html(__('Per Calendar Month', 'propertyhive')) . '</option>
                            <option value="pq"' . ( ($rent_frequency == 'pq') ? ' selected' : '') . '>' . esc_html(__('Per Quarter', 'propertyhive')) . '</option>
                            <option value="pa"' . ( ($rent_frequency == 'pa') ? ' selected' : '') . '>' . esc_html(__('Per Annum', 'propertyhive')) . '</option>
                        </select>

                    </div>';
            }

            echo '<a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
                    <a class="button button-primary carried-out-action-submit" href="#">' . esc_html(__( 'Save', 'propertyhive' )) . '</a>

                </div>

            </div>';
        }

        if ( $show_instructed_meta_boxes )
        {
            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_appraisal_instruct" style="display:none;">

                <div class="options_group" style="padding-top:8px;">';

                echo '<div style="margin-bottom:13px;">' . esc_html(__( 'Upon instruction a new property record will be created within the \'Properties\' area.', 'propertyhive' )) . '</div>';

            echo '<a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
                    <a class="button button-primary instructed-action-submit" href="#">' . esc_html(__( 'OK', 'propertyhive' )) . '</a>

                </div>

            </div>';
        }

        if ( $show_lost_meta_boxes )
        {
            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_appraisal_lost" style="display:none;">

                <div class="options_group" style="padding-top:8px;">

                    <div class="form-field">

                        <label for="_lost_reason">' . esc_html(__( 'Reason Lost', 'propertyhive' )) . '</label>
                        
                        <textarea id="_lost_reason" name="_lost_reason" style="width:100%;">' . esc_html(get_post_meta( $post_id, '_lost_reason', TRUE )) . '</textarea>

                    </div>

                    <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
                    <a class="button button-primary lost-reason-action-submit" href="#">' . esc_html( __( 'Save Reason Lost', 'propertyhive' ) ) . '</a>

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
                    case "pd": { $price = ($rent * 365) / 12; break; }
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

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_carried_out',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_success();
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

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_cancelled',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function appraisal_won()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' )
        {
            update_post_meta( $post_id, '_status', 'won' );

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_won',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
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

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_lost',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
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
                echo json_encode( $return );
                die();
            }
            else
            {
                // Successfully added property post

                $department = get_post_meta( $post_id, '_department', TRUE );

                $reference_number = '';
                if ( get_option( 'propertyhive_auto_incremental_reference_numbers' ) == 'yes' )
                {
                    $next = get_option( 'propertyhive_auto_incremental_next', '' );
                    if ( $next == '' || (int)$next == 0 )
                    {
                        $next = 1;
                    }
                    $reference_number = $next;

                    $next_auto_increment = $next + 1;

                    update_option( 'propertyhive_auto_incremental_next', $next_auto_increment );
                }
                update_post_meta( $property_post_id, '_reference_number', $reference_number );

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

                    if ( get_option('propertyhive_geocoding_provider') == 'osm' )
                    {
                        $request_url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=" . strtolower($country) . "&addressdetails=1&q=" . urlencode(implode( ", ", $address_to_geocode ));
                        $response = wp_remote_get($request_url);
                        if ( is_array( $response ) )
                        {
                            $body = wp_remote_retrieve_body( $response );
                            $json = json_decode($body, true);

                            if ( !empty($json) && isset($json[0]['lat']) && isset($json[0]['lon']) )
                            {
                                $lat = $json[0]['lat'];
                                $lng = $json[0]['lon'];

                                if ($lat != '' && $lng != '')
                                {
                                    update_post_meta( $property_post_id, '_latitude', $lat );
                                    update_post_meta( $property_post_id, '_longitude', $lng );
                                }
                            }
                        }
                    }
                    else
                    {
                        $request_url = "https://maps.googleapis.com/maps/api/geocode/xml?address=" . urlencode( implode( ", ", $address_to_geocode ) ) . "&sensor=false&region=" . strtolower($country); // the request URL you'll send to google to get back your XML feed

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
                                        update_post_meta( $property_post_id, '_latitude', $lat );
                                        update_post_meta( $property_post_id, '_longitude', $lng );
                                    }
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

                update_post_meta( $property_post_id, '_council_tax_band', get_post_meta( $post_id, '_council_tax_band', TRUE ) );

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

                // Add note/comment to appraisal
                $comment = array(
                    'note_type' => 'action',
                    'action' => 'appraisal_instructed',
                );

                PH_Comments::insert_note( $post_id, $comment );

                wp_send_json_success();
            }
        }

        wp_send_json_error();
    }

    public function appraisal_email_owner_booking_confirmation()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $appraisal = new PH_Appraisal($post_id);

        $owner_contact_id = $appraisal->property_owner_contact_id;

        if ( !is_array($owner_contact_id) ) { $owner_contact_id = array($owner_contact_id); }

        if ( !empty($owner_contact_id) )
        {
            $owner_emails = array();
            $owner_names = array();
            $owner_dears = array();

            foreach ($owner_contact_id as $owner_id)
            {
                $owner_contact = new PH_Contact($owner_id);

                $owner_email = sanitize_email( $owner_contact->email_address );
                $owner_name = $owner_contact->post_title;
                $owner_dear = $owner_contact->dear();

                if( ! empty($owner_email) ) array_push($owner_emails, $owner_email);
                if( ! empty($owner_name) ) array_push($owner_names, $owner_name);
                if( ! empty($owner_dear) ) array_push($owner_dears, $owner_dear);
            }

            $owner_names_string = $this->get_list_string($owner_names);
            $owner_dears_string = $this->get_list_string($owner_dears);

            $negotiator_names = array();
            $negotiator_names_string = '';
            $negotiator_ids = get_post_meta( $post_id, '_negotiator_id' );
            if ( !empty($negotiator_ids) )
            {
                foreach ( $negotiator_ids as $negotiator_id )
                {
                    $negotiator = get_user_by( 'id', $negotiator_id );
                    if ( $negotiator !== false && isset($negotiator->display_name) && !empty($negotiator->display_name) )
                    {
                        $negotiator_names[] = $negotiator->display_name;
                    }
                }
            }
            if ( !empty($negotiator_names) )
            {
                $last  = array_slice($negotiator_names, -1);
                $first = join(', ', array_slice($negotiator_names, 0, -1));
                $both  = array_filter(array_merge(array($first), $last), 'strlen');
                $negotiator_names_string = join(' and ', $both);
            }

            $to = implode(",", $owner_emails);

            $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : get_option( 'propertyhive_appraisal_owner_booking_confirmation_email_subject', '' );
            $body = isset($_POST['body']) ? sanitize_textarea_field($_POST['body']) : get_option( 'propertyhive_appraisal_owner_booking_confirmation_email_body', '' );

            $appraisal_date_timestamp = strtotime($appraisal->start_date_time);

            $subject = str_replace('[property_address]', $appraisal->get_formatted_full_address(), $subject);
            $subject = str_replace('[owner_name]', $owner_names_string, $subject);
            $subject = str_replace('[appraisal_time]', date("H:i", $appraisal_date_timestamp), $subject);
            $subject = str_replace('[appraisal_date]', date("l jS F Y", $appraisal_date_timestamp), $subject);
            $subject = str_replace('[negotiator_name]', $negotiator_names_string, $subject);

            $body = str_replace('[property_address]', $appraisal->get_formatted_full_address(), $body);
            $body = str_replace('[owner_name]', $owner_names_string, $body);
            $body = str_replace('[owner_dear]', $owner_dears_string, $body);
            $body = str_replace('[appraisal_time]', date("H:i", $appraisal_date_timestamp), $body);
            $body = str_replace('[appraisal_date]', date("l jS F Y", $appraisal_date_timestamp), $body);
            $body = str_replace('[negotiator_name]', $negotiator_names_string, $body);

            $body = html_entity_decode($body);

            $body = apply_filters( 'appraisal_owner_booking_confirmation_email_body', $body, $post_id );

            $from = get_option('propertyhive_email_from_address', '');
            if ( $from == '' )
            {
                $from = get_bloginfo('admin_email');
            }

            $headers = array();
            $headers[] = 'From: ' . html_entity_decode(get_bloginfo('name')) . ' <' . sanitize_email($from) . '>';
            $headers[] = 'Reply-To: ' . sanitize_email($from);
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';

            $headers = apply_filters( 'propertyhive_appraisal_owner_booking_confirmation_email_headers', $headers );

            wp_mail($to, $subject, $body, $headers);

            update_post_meta( $post_id, '_owner_booking_confirmation_sent_at', date("Y-m-d H:i:s") );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function appraisal_revert_pending()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' || $status == 'cancelled' )
        {
            update_post_meta( $post_id, '_status', 'pending' );

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_revert_pending',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function appraisal_revert_carried_out()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'won' || $status == 'lost' )
        {
            update_post_meta( $post_id, '_status', 'carried_out' );

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_revert_carried_out',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function appraisal_revert_won()
    {
        check_ajax_referer( 'appraisal-actions', 'security' );

        $post_id = (int)$_POST['appraisal_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'instructed' )
        {
            update_post_meta( $post_id, '_status', 'won' );

            // Add note/comment to appraisal
            $comment = array(
                'note_type' => 'action',
                'action' => 'appraisal_revert_won',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
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

            $email_address = isset($_POST['applicant_email_address']) ? sanitize_email($_POST['applicant_email_address']) : '';
            $telephone_number = isset($_POST['applicant_telephone_number']) ? sanitize_text_field($_POST['applicant_telephone_number']) : '';
            update_post_meta( $contact_post_id, '_email_address', $email_address );
            update_post_meta( $contact_post_id, '_telephone_number', $telephone_number );

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

        $applicant_contacts = array();
        foreach ($applicant_contact_ids as $applicant_contact_id)
        {
            add_post_meta( $viewing_post_id, '_applicant_contact_id', $applicant_contact_id );

            $applicant_contacts[] = array(
                'ID' => $applicant_contact_id,
                'post_title' => get_the_title($applicant_contact_id),
                'edit_link' => get_edit_post_link( $applicant_contact_id, '' ),
            );
        }

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

        $readonly = isset($_POST['readonly']) ? filter_var($_POST['readonly'], FILTER_VALIDATE_BOOLEAN) : false;

        include( PH()->plugin_path() . '/includes/admin/views/html-viewing-details-meta-box.php' );

        die();
    }

    public function get_viewing_actions()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        include( PH()->plugin_path() . '/includes/admin/views/html-viewing-actions.php' );

        die();
    }

    public function get_viewing_lightbox()
    {
        $post_id = $_GET['post_id'];

        $post = get_post((int)$post_id);

        $viewing = new PH_Viewing($post_id);

        include( PH()->plugin_path() . '/includes/admin/views/html-viewing-details-lightbox.php' );

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

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_carried_out',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function viewing_no_show()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'pending' )
        {
            update_post_meta( $post_id, '_status', 'no_show' );

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_applicant_no_show',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
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

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_cancelled',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function viewing_email_applicant_booking_confirmation()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $applicant_contact_ids = get_post_meta( $post_id, '_applicant_contact_id' );
        $property_id = get_post_meta( $post_id, '_property_id', TRUE );

        if ( !is_array($applicant_contact_ids) || (int)$property_id == '' || count($applicant_contact_ids) == 0 || (int)$property_id == 0 )
        {
            wp_send_json_error();
        }

        $property = new PH_Property((int)$property_id);

        $to = array();
        foreach ($applicant_contact_ids as $applicant_contact_id)
        {
            $applicant_email_address = get_post_meta( $applicant_contact_id, '_email_address', TRUE );
            $explode_applicant_email_address = explode( ",", $applicant_email_address );
            foreach ( $explode_applicant_email_address as $email_address )
            {
                $to[] = sanitize_email($email_address);
            }
        }

        $to = array_filter($to);

        if ( !empty(implode($to)) )
        {
            $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : get_option( 'propertyhive_viewing_applicant_booking_confirmation_email_subject', '' );
            $body = isset($_POST['body']) ? sanitize_textarea_field($_POST['body']) : get_option( 'propertyhive_viewing_applicant_booking_confirmation_email_body', '' );

            $applicant_names = array();
            $applicant_dears = array();
            foreach ($applicant_contact_ids as $applicant_contact_id)
            {
                $applicant_contact = new PH_Contact($applicant_contact_id);
                $applicant_names[] = $applicant_contact->post_title;
                $applicant_dears[] = $applicant_contact->dear();
            }
            $applicant_names = array_filter($applicant_names);
            $applicant_dears = array_filter($applicant_dears);

            $applicant_names_string = $this->get_list_string($applicant_names);
            $applicant_dears_string = $this->get_list_string($applicant_dears);

            $negotiator_names = array();
            $negotiator_names_string = '';
            $negotiator_ids = get_post_meta( $post_id, '_negotiator_id' );
            if ( !empty($negotiator_ids) )
            {
                foreach ( $negotiator_ids as $negotiator_id )
                {
                    $negotiator = get_user_by( 'id', $negotiator_id );
                    if ( $negotiator !== false && isset($negotiator->display_name) && !empty($negotiator->display_name) )
                    {
                        $negotiator_names[] = $negotiator->display_name;
                    }
                }
            }
            if ( !empty($negotiator_names) )
            {
                $last  = array_slice($negotiator_names, -1);
                $first = join(', ', array_slice($negotiator_names, 0, -1));
                $both  = array_filter(array_merge(array($first), $last), 'strlen');
                $negotiator_names_string = join(' and ', $both);
            }

            $subject = str_replace('[property_address]', $property->get_formatted_full_address(), $subject);
            $subject = str_replace('[applicant_name]', $applicant_names_string, $subject);
            $subject = str_replace('[viewing_time]', date("H:i", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $subject);
            $subject = str_replace('[viewing_date]', date("l jS F Y", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $subject);
            $subject = str_replace('[negotiator_name]', $negotiator_names_string, $subject);

            $body = str_replace('[property_address]', $property->get_formatted_full_address(), $body);
            $body = str_replace('[applicant_name]', $applicant_names_string, $body);
            $body = str_replace('[applicant_dear]', $applicant_dears_string, $body);
            $body = str_replace('[viewing_time]', date("H:i", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $body);
            $body = str_replace('[viewing_date]', date("l jS F Y", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $body);
            $body = str_replace('[negotiator_name]', $negotiator_names_string, $body);

            $body = html_entity_decode($body);

            $body = apply_filters( 'viewing_applicant_booking_confirmation_email_body', $body, $post_id, $property_id );

            $from = $property->office_email_address;
            if ( sanitize_email($from) == '' )
            {
                $from = get_option('propertyhive_email_from_address', '');
                if ( $from == '' )
                {
                    $from = get_bloginfo('admin_email');
                }
            }

            $headers = array();
            $headers[] = 'From: ' . html_entity_decode(get_bloginfo('name')) . ' <' . sanitize_email($from) . '>';
            $headers[] = 'Reply-To: ' . sanitize_email($from);
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';

            $headers = apply_filters( 'propertyhive_viewing_applicant_booking_confirmation_email_headers', $headers );

            wp_mail($to, $subject, $body, $headers);

            update_post_meta( $post_id, '_applicant_booking_confirmation_sent_at', date("Y-m-d H:i:s") );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function viewing_email_owner_booking_confirmation()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $property_id = get_post_meta( $post_id, '_property_id', TRUE );
        $property_department = get_post_meta( $property_id, '_department' );

        $applicant_contact_ids = get_post_meta( $post_id, '_applicant_contact_id' );
        $owner_contact_ids = get_post_meta( $property_id, '_owner_contact_id', TRUE );
 
        if ( $owner_contact_ids > 0 ) {

            $owner_emails = array();
            $owner_names = array();
            $owner_dears = array();
    
            foreach ($owner_contact_ids as $owner_id) 
            {
                $owner_contact = new PH_Contact($owner_id);

                $owner_name = $owner_contact->post_title;
                $owner_dear = $owner_contact->dear();

                if( ! empty($owner_name) ) array_push($owner_names, $owner_name);
                if( ! empty($owner_dear) ) array_push($owner_dears, $owner_dear);

                $owner_email = $owner_contact->email_address;
                $explode_owner_email = explode( ",", $owner_email );
                foreach ( $explode_owner_email as $email_address )
                {
                    $owner_emails[] = sanitize_email($email_address);
                }
            }

            $owner_names_string = $this->get_list_string($owner_names);
            $owner_dears_string = $this->get_list_string($owner_dears);

            if ( !empty($applicant_contact_ids) ) 
            {
                $applicant_names = array();
                $applicant_dears = array();
                foreach ($applicant_contact_ids as $applicant_contact_id)
                {
                    $applicant_contact = new PH_Contact($applicant_contact_id);
                    $applicant_names[] = $applicant_contact->post_title;
                    $applicant_dears[] = $applicant_contact->dear();
                }
                $applicant_names = array_filter($applicant_names);
                $applicant_dears = array_filter($applicant_dears);
            }
            
            $applicant_names_string = $this->get_list_string($applicant_names);
            $applicant_dears_string = $this->get_list_string($applicant_dears);

            $negotiator_names = array();
            $negotiator_names_string = '';
            $negotiator_ids = get_post_meta( $post_id, '_negotiator_id' );
            if ( !empty($negotiator_ids) )
            {
                foreach ( $negotiator_ids as $negotiator_id )
                {
                    $negotiator = get_user_by( 'id', $negotiator_id );
                    if ( $negotiator !== false && isset($negotiator->display_name) && !empty($negotiator->display_name) )
                    {
                        $negotiator_names[] = $negotiator->display_name;
                    }
                }
            }
            if ( !empty($negotiator_names) )
            {
                $last  = array_slice($negotiator_names, -1);
                $first = join(', ', array_slice($negotiator_names, 0, -1));
                $both  = array_filter(array_merge(array($first), $last), 'strlen');
                $negotiator_names_string = join(' and ', $both);
            }

            $property = new PH_Property((int)$property_id);

            $to = implode(",", $owner_emails);

            $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : get_option( 'propertyhive_viewing_owner_booking_confirmation_email_subject', '' );
            $body = isset($_POST['body']) ? sanitize_textarea_field($_POST['body']) : get_option( 'propertyhive_viewing_owner_booking_confirmation_email_body', '' );

            $subject = str_replace('[property_address]', $property->get_formatted_full_address(), $subject);
            $subject = str_replace('[owner_name]', $owner_names_string, $subject);
            $subject = str_replace('[applicant_name]', $applicant_names_string, $subject);
            $subject = str_replace('[viewing_time]', date("H:i", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $subject);
            $subject = str_replace('[viewing_date]', date("l jS F Y", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $subject);
            $subject = str_replace('[negotiator_name]', $negotiator_names_string, $subject);

            $body = str_replace('[property_address]', $property->get_formatted_full_address(), $body);
            $body = str_replace('[owner_name]', $owner_names_string, $body);
            $body = str_replace('[owner_dear]', $owner_dears_string, $body);
            $body = str_replace('[applicant_name]', $applicant_names_string, $body);
            $body = str_replace('[applicant_dear]', $applicant_dears_string, $body);
            $body = str_replace('[viewing_time]', date("H:i", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $body);
            $body = str_replace('[viewing_date]', date("l jS F Y", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $body);
            $body = str_replace('[negotiator_name]', $negotiator_names_string, $body);

            $body = html_entity_decode($body);

            $body = apply_filters( 'viewing_owner_booking_confirmation_email_body', $body, $post_id, $property_id );

            $from = $property->office_email_address;
            if ( sanitize_email($from) == '' )
            {
                $from = get_option('propertyhive_email_from_address', '');
                if ( $from == '' )
                {
                    $from = get_bloginfo('admin_email');
                }
            }

            $headers = array();
            $headers[] = 'From: ' . html_entity_decode(get_bloginfo('name')) . ' <' . sanitize_email($from) . '>';
            $headers[] = 'Reply-To: ' . sanitize_email($from);
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';

            $headers = apply_filters( 'propertyhive_viewing_owner_booking_confirmation_email_headers', $headers );

            wp_mail($to, $subject, $body, $headers);

            update_post_meta( $post_id, '_owner_booking_confirmation_sent_at', date("Y-m-d H:i:s") );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function viewing_email_attending_negotiator_booking_confirmation()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];
        $property_id = get_post_meta( $post_id, '_property_id', TRUE );

        $negotiator_ids = get_post_meta( $post_id, '_negotiator_id' );

        $applicant_contact_ids = get_post_meta( $post_id, '_applicant_contact_id' );
        $owner_contact_ids = get_post_meta( $property_id, '_owner_contact_id', TRUE );
 
        if ( !empty($negotiator_ids) ) {

            $tos = array();
            foreach ($negotiator_ids as $negotiator_id) 
            {
                $user_info = get_userdata((int)$negotiator_id);
                $tos[] = sanitize_email($user_info->user_email);
            }
            $to = implode(",", $tos);

            $owner_emails = array();
            $owner_names = array();
            $owner_dears = array();
            $owner_details = array();
            
            if ( !empty($owner_contact_ids) ) 
            {
                foreach ($owner_contact_ids as $owner_id) 
                {
                    $owner_contact = new PH_Contact($owner_id);

                    $owner_name = $owner_contact->post_title;
                    $owner_dear = $owner_contact->dear();

                    if( ! empty($owner_name) ) array_push($owner_names, $owner_name);
                    if( ! empty($owner_dear) ) array_push($owner_dears, $owner_dear);

                    $owner_email = $owner_contact->email_address;
                    $explode_owner_email = explode( ",", $owner_email );
                    foreach ( $explode_owner_email as $email_address )
                    {
                        $owner_emails[] = sanitize_email($email_address);
                    }

                    $owner_details[] = $owner_contact->post_title . "\nT: " . $owner_contact->telephone_number . "\nE: " . $owner_contact->email_address;
                }
            }

            $owner_details = implode("\n\n", $owner_details);

            $owner_names_string = $this->get_list_string($owner_names);
            $owner_dears_string = $this->get_list_string($owner_dears);

            $applicant_names = array();
            $applicant_dears = array();
            $applicant_details = array();

            if ( !empty($applicant_contact_ids) ) 
            {
                foreach ($applicant_contact_ids as $applicant_contact_id)
                {
                    $applicant_contact = new PH_Contact($applicant_contact_id);
                    $applicant_names[] = $applicant_contact->post_title;
                    $applicant_dears[] = $applicant_contact->dear();

                    $applicant_details[] = $applicant_contact->post_title . "\nT: " . $applicant_contact->telephone_number . "\nE: " . $applicant_contact->email_address;
                }
            }

            $applicant_details = implode("\n\n", $applicant_details);

            $applicant_names = array_filter($applicant_names);
            $applicant_dears = array_filter($applicant_dears);

            $applicant_names_string = $this->get_list_string($applicant_names);
            $applicant_dears_string = $this->get_list_string($applicant_dears);

            $negotiator_names = array();
            $negotiator_names_string = '';
            $negotiator_ids = get_post_meta( $post_id, '_negotiator_id' );
            if ( !empty($negotiator_ids) )
            {
                foreach ( $negotiator_ids as $negotiator_id )
                {
                    $negotiator = get_user_by( 'id', $negotiator_id );
                    if ( $negotiator !== false && isset($negotiator->display_name) && !empty($negotiator->display_name) )
                    {
                        $negotiator_names[] = $negotiator->display_name;
                    }
                }
            }
            if ( !empty($negotiator_names) )
            {
                $last  = array_slice($negotiator_names, -1);
                $first = join(', ', array_slice($negotiator_names, 0, -1));
                $both  = array_filter(array_merge(array($first), $last), 'strlen');
                $negotiator_names_string = join(' and ', $both);
            }

            $property = new PH_Property((int)$property_id);

            $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : get_option( 'propertyhive_viewing_attending_negotiator_booking_confirmation_email_subject', '' );
            $body = isset($_POST['body']) ? sanitize_textarea_field($_POST['body']) : get_option( 'propertyhive_viewing_attending_negotiator_booking_confirmation_email_body', '' );

            $subject = str_replace('[property_address]', $property->get_formatted_full_address(), $subject);
            $subject = str_replace('[owner_name]', $owner_names_string, $subject);
            $subject = str_replace('[applicant_name]', $applicant_names_string, $subject);
            $subject = str_replace('[viewing_time]', date("H:i", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $subject);
            $subject = str_replace('[viewing_date]', date("l jS F Y", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $subject);
            $subject = str_replace('[negotiator_name]', $negotiator_names_string, $subject);

            $body = str_replace('[property_address]', $property->get_formatted_full_address(), $body);
            $body = str_replace('[owner_name]', $owner_names_string, $body);
            $body = str_replace('[owner_dear]', $owner_dears_string, $body);
            $body = str_replace('[owner_details]', $owner_details, $body);
            $body = str_replace('[applicant_name]', $applicant_names_string, $body);
            $body = str_replace('[applicant_dear]', $applicant_dears_string, $body);
            $body = str_replace('[applicant_details]', $applicant_details, $body);
            $body = str_replace('[viewing_time]', date("H:i", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $body);
            $body = str_replace('[viewing_date]', date("l jS F Y", strtotime(get_post_meta( $post_id, '_start_date_time', true ))), $body);
            $body = str_replace('[negotiator_name]', $negotiator_names_string, $body);

            $body = html_entity_decode($body);

            $body = apply_filters( 'viewing_attending_negotiator_booking_confirmation_email_body', $body, $post_id, $property_id );

            $from = $property->office_email_address;
            if ( sanitize_email($from) == '' )
            {
                $from = get_option('propertyhive_email_from_address', '');
                if ( $from == '' )
                {
                    $from = get_bloginfo('admin_email');
                }
            }

            $headers = array();
            $headers[] = 'From: ' . html_entity_decode(get_bloginfo('name')) . ' <' . sanitize_email($from) . '>';
            $headers[] = 'Reply-To: ' . sanitize_email($from);
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';

            $headers = apply_filters( 'propertyhive_viewing_attending_negotiator_booking_confirmation_email_headers', $headers );

            wp_mail($to, $subject, $body, $headers);

            update_post_meta( $post_id, '_attending_negotiator_booking_confirmation_sent_at', date("Y-m-d H:i:s") );

            wp_send_json_success();
        }

        wp_send_json_error();
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

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_applicant_interested',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
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

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_applicant_not_interested',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function viewing_feedback_not_required()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' )
        {
            update_post_meta( $post_id, '_feedback_status', 'not_required' );

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_feedback_not_required',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
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
            delete_post_meta( $post_id, '_feedback_received_date' );

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_revert_feedback_pending',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function viewing_revert_pending()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( in_array( $status, array('carried_out', 'cancelled', 'no_show') ) )
        {
            update_post_meta( $post_id, '_status', 'pending' );
            update_post_meta( $post_id, '_feedback_status', '' );
            delete_post_meta( $post_id, '_feedback_received_date' );

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_revert_pending',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function viewing_feedback_passed_on()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' )
        {
            update_post_meta( $post_id, '_feedback_passed_on', 'yes' );

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_feedback_passed_on',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function get_property_viewings_meta_box()
    {
        $post_id = $_POST['post_id'];

        $selected_status = '';
        if ( isset($_POST['selected_status']) )
        {
            $selected_status = ph_clean($_POST['selected_status']);
        }

        include( PH()->plugin_path() . '/includes/admin/views/html-property-viewings-meta-box.php' );

        do_action('propertyhive_property_viewings_fields');

        // Quit out
        die();
    }

    public function get_contact_viewings_meta_box()
    {
        $post_id = $_POST['post_id'];

        $selected_status = '';
        if ( isset($_POST['selected_status']) )
        {
            $selected_status = ph_clean($_POST['selected_status']);
        }

        include( PH()->plugin_path() . '/includes/admin/views/html-contact-viewings-meta-box.php' );

        do_action('propertyhive_contact_viewings_fields');

        // Quit out
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

            $applicant_solicitor_contact_id = get_post_meta( $applicant_contact_id, '_contact_solicitor_contact_id', TRUE );
            if ( !empty($applicant_solicitor_contact_id) )
            {
                add_post_meta( $offer_post_id, '_applicant_solicitor_contact_id', (int)$applicant_solicitor_contact_id );
            }

            $owner_contact_ids = get_post_meta((int)$_POST['property_id'], '_owner_contact_id', TRUE);
            if ( !empty($owner_contact_ids) )
            {
                foreach ( $owner_contact_ids as $owner_contact_id )
                {
                    $property_owner_solicitor_contact_id = get_post_meta( (int)$owner_contact_id, '_contact_solicitor_contact_id', TRUE );
                    if ( !empty($property_owner_solicitor_contact_id) )
                    {
                        add_post_meta( $offer_post_id, '_property_owner_solicitor_contact_id', (int)$property_owner_solicitor_contact_id );
                    }
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

            $applicant_solicitor_contact_id = get_post_meta( (int)$_POST['contact_id'], '_contact_solicitor_contact_id', TRUE );
            if ( !empty($applicant_solicitor_contact_id) )
            {
                add_post_meta( $offer_post_id, '_applicant_solicitor_contact_id', (int)$applicant_solicitor_contact_id );
            }

            $owner_contact_ids = get_post_meta($property_id, '_owner_contact_id', TRUE);
            if ( !empty($owner_contact_ids) )
            {
                foreach ( $owner_contact_ids as $owner_contact_id )
                {
                    $property_owner_solicitor_contact_id = get_post_meta( (int)$owner_contact_id, '_contact_solicitor_contact_id', TRUE );
                    if ( !empty($property_owner_solicitor_contact_id) )
                    {
                        add_post_meta( $offer_post_id, '_property_owner_solicitor_contact_id', (int)$property_owner_solicitor_contact_id );
                    }
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
        global $post;

        check_ajax_referer( 'offer-details-meta-box', 'security' );

        $post = get_post((int)$_POST['offer_id']);

        $offer = new PH_Offer((int)$_POST['offer_id']);

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        if ( $offer->status != '' )
        {
            echo '<p class="form-field">
            
                <label for="">' . __('Status', 'propertyhive') . '</label>
                
                ' . __( ucwords(str_replace("_", " ", $offer->status)), 'propertyhive' ) . '    
            
            </p>';
        }

        $offer_date_time = $offer->offer_date_time;
        if ( empty($offer_date_time) )
        {
            $offer_date_time = date("Y-m-d H:i:s");
        }

        echo '<p class="form-field offer_date_time_field">
    
            <label for="_offer_date">' . __('Offer Date / Time', 'propertyhive') . '</label>
            
            <input type="date" class="small" name="_offer_date" id="_offer_date" value="' . esc_attr(date("Y-m-d", strtotime($offer_date_time))) . '" placeholder="">
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
            echo '<option value="' . esc_attr($j) . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . esc_html($j) . '</option>';
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
            echo '<option value="' . esc_attr($j) . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . esc_html($j) . '</option>';
        }
        
        echo '</select>
            
        </p>';

        $args = array( 
            'id' => '_amount', 
            'label' => __( 'Offer Amount', 'propertyhive' ) . ' (&pound;)', 
            'desc_tip' => false, 
            'class' => 'short',
            'value' => ( is_numeric($offer->amount) ? ph_display_price_field( $offer->amount ) : '' ),
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

        // Success action panel
        echo '<div id="action_panel_success" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
                     
            <div class="options_group" style="padding-top:8px;">

                <div id="success_actions"></div>

                <a class="button action-cancel" style="width:100%;" href="#">' . __( 'Back To Actions', 'propertyhive' ) . '</a>

            </div>

        </div>';

        do_action( 'propertyhive_admin_offer_action_options', $post_id );
        do_action( 'propertyhive_admin_post_action_options', $post_id );

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
            $actions[] = '<a 
                    href="#action_panel_offer_withdrawn" 
                    class="button offer-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Withdraw Offer', 'propertyhive') ) . '</a>';
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
                        class="button button-success button-create-sale"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                        onclick="setTimeout(function() { jQuery(\'.button-create-sale\').attr(\'href\', \'#\'); jQuery(\'.button-create-sale\').attr(\'disabled\', \'disabled\'); jQuery(\'.button-create-sale\').html(\'Creating...\'); }, 50);"
                    >' . wp_kses_post( __('Create Sale', 'propertyhive') ) . '</a>';
                $actions[] = '<a 
                    href="#action_panel_offer_withdrawn" 
                    class="button offer-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Withdraw Offer', 'propertyhive') ) . '</a>';
            }
        }

        if ( $status == 'accepted' || $status == 'declined' || $status == 'withdrawn' )
        {
            $actions[] = '<a 
                    href="#action_panel_offer_revert_pending" 
                    class="button offer-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('Revert To Pending', 'propertyhive') ) . '</a>';
        }

        $actions = apply_filters( 'propertyhive_admin_offer_actions', $actions, $post_id );
        $actions = apply_filters( 'propertyhive_admin_post_actions', $actions, $post_id );

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

            // Add note/comment to offer
            $comment = array(
                'note_type' => 'action',
                'action' => 'offer_accepted',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function offer_declined()
    {
        check_ajax_referer( 'offer-actions', 'security' );

        $post_id = (int)$_POST['offer_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'pending' )
        {
            update_post_meta( $post_id, '_status', 'declined' );

            // Add note/comment to offer
            $comment = array(
                'note_type' => 'action',
                'action' => 'offer_declined',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function offer_withdrawn()
    {
        check_ajax_referer( 'offer-actions', 'security' );

        $post_id = (int)$_POST['offer_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'pending' || $status == 'accepted' )
        {
            update_post_meta( $post_id, '_status', 'withdrawn' );

            // Add note/comment to offer
            $comment = array(
                'note_type' => 'action',
                'action' => 'offer_withdrawn',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function offer_revert_pending()
    {
        check_ajax_referer( 'offer-actions', 'security' );

        $post_id = (int)$_POST['offer_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'accepted' || $status == 'declined' || $status == 'withdrawn' )
        {
            update_post_meta( $post_id, '_status', 'pending' );

            // Add note/comment to offer
            $comment = array(
                'note_type' => 'action',
                'action' => 'offer_revert_pending',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function get_property_offers_meta_box()
    {
        $post_id = $_POST['post_id'];

        $selected_status = '';
        if ( isset($_POST['selected_status']) )
        {
            $selected_status = ph_clean($_POST['selected_status']);
        }

        include( PH()->plugin_path() . '/includes/admin/views/html-property-offers-meta-box.php' );

        do_action('propertyhive_property_offers_fields');

        // Quit out
        die();
    }

    public function get_contact_offers_meta_box()
    {
        $post_id = $_POST['post_id'];

        $selected_status = '';
        if ( isset($_POST['selected_status']) )
        {
            $selected_status = ph_clean($_POST['selected_status']);
        }

        include( PH()->plugin_path() . '/includes/admin/views/html-contact-offers-meta-box.php' );

        do_action('propertyhive_contact_offers_fields');

        // Quit out
        die();
    }

    // Sale related functions
    public function get_sale_details_meta_box()
    {
        global $post;

        check_ajax_referer( 'sale-details-meta-box', 'security' );

        $post = get_post((int)$_POST['sale_id']);

        $sale = new PH_Offer((int)$_POST['sale_id']);

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        if ( $sale->status != '' )
        {
            echo '<p class="form-field">
            
                <label for="">' . __('Status', 'propertyhive') . '</label>
                
                ' . __( ucwords(str_replace("_", " ", $sale->status)), 'propertyhive' ) . '    
            
            </p>';
        }

        $sale_date_time = $sale->sale_date_time;
        if ( empty($sale_date_time) )
        {
            $sale_date_time = date("Y-m-d H:i:s");
        }

        echo '<p class="form-field sale_date_field">
    
            <label for="_sale_date">' . __('Sale Date', 'propertyhive') . '</label>

            <input type="date" class="small" name="_sale_date" id="_sale_date" value="' . date("Y-m-d", strtotime($sale_date_time)) . '" placeholder="">
            
        </p>';

        $args = array( 
            'id' => '_amount', 
            'label' => __( 'Sale Amount', 'propertyhive' ) . ' (&pound;)', 
            'desc_tip' => false, 
            'class' => 'short',
            'value' => ( is_numeric($sale->amount) ? ph_display_price_field( $sale->amount ) : '' ),
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

        // Success action panel
        echo '<div id="action_panel_success" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
                     
            <div class="options_group" style="padding-top:8px;">

                <div id="success_actions"></div>

                <a class="button action-cancel" style="width:100%;" href="#">' . __( 'Back To Actions', 'propertyhive' ) . '</a>

            </div>

        </div>';

        do_action( 'propertyhive_admin_sale_action_options', $post_id );
        do_action( 'propertyhive_admin_post_action_options', $post_id );

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
        $actions = apply_filters( 'propertyhive_admin_post_actions', $actions, $post_id );

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

            // Add note/comment to sale
            $comment = array(
                'note_type' => 'action',
                'action' => 'sale_exchanged',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function sale_completed()
    {
        check_ajax_referer( 'sale-actions', 'security' );

        $post_id = (int)$_POST['sale_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'exchanged' )
        {
            update_post_meta( $post_id, '_status', 'completed' );

            // Add note/comment to sale
            $comment = array(
                'note_type' => 'action',
                'action' => 'sale_completed',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function sale_fallen_through()
    {
        check_ajax_referer( 'sale-actions', 'security' );

        $post_id = (int)$_POST['sale_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'current' || $status == 'exchanged' )
        {
            update_post_meta( $post_id, '_status', 'fallen_through' );

            // Add note/comment to sale
            $comment = array(
                'note_type' => 'action',
                'action' => 'sale_fallen_through',
            );

            PH_Comments::insert_note( $post_id, $comment );

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    public function get_property_sales_meta_box()
    {
        $post_id = (int)$_POST['post_id'];

        $selected_status = '';
        if ( isset($_POST['selected_status']) )
        {
            $selected_status = ph_clean($_POST['selected_status']);
        }

        include( PH()->plugin_path() . '/includes/admin/views/html-property-sales-meta-box.php' );

        do_action('propertyhive_property_sales_fields');

        // Quit out
        die();
    }

    public function get_contact_sales_meta_box()
    {
        $post_id = (int)$_POST['post_id'];

        $selected_status = '';
        if ( isset($_POST['selected_status']) )
        {
            $selected_status = ph_clean($_POST['selected_status']);
        }

        include( PH()->plugin_path() . '/includes/admin/views/html-contact-sales-meta-box.php' );

        do_action('propertyhive_contact_sales_fields');

        // Quit out
        die();
    }

    public function get_property_enquiries_meta_box()
    {
        $post_id = (int)$_POST['post_id'];

        $selected_status = '';
        if ( isset($_POST['selected_status']) )
        {
            $selected_status = ph_clean($_POST['selected_status']);
        }

        include( PH()->plugin_path() . '/includes/admin/views/html-property-enquiries-meta-box.php' );

        do_action('propertyhive_property_enquiries_fields');

        // Quit out
        die();
    }

    public function get_contact_enquiries_meta_box()
    {
        $post_id = (int)$_POST['post_id'];

        $selected_status = '';
        if ( isset($_POST['selected_status']) )
        {
            $selected_status = ph_clean($_POST['selected_status']);
        }

        include( PH()->plugin_path() . '/includes/admin/views/html-contact-enquiries-meta-box.php' );

        do_action('propertyhive_contact_enquiries_fields');

        // Quit out
        die();
    }

    /**
	 * Add new management key date via ajax
	 */
    public function add_key_date()
    {
        $parent_post_id = (int)$_POST['post_id'];

        if ( $parent_post_id > 0 ) {
            $date_description = wp_kses_post( trim( stripslashes( $_POST['key_date_description'] ) ) );
            $date_type_id = ph_clean( stripslashes( $_POST['key_date_type'] ) );
            $date_due = ph_clean($_POST['key_date_due']) . ' ' . ph_clean($_POST['key_date_hours']) . ':' . ph_clean($_POST['key_date_minutes']);
            $date_notes = sanitize_textarea_field($_POST['key_date_notes']);

            $parent_post_type = get_post_type( $parent_post_id );

            // Insert key date record
            $key_date_post = array(
                'post_title' => $date_description,
                'post_content' => '',
                'post_type' => 'key_date',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            );

            // Insert the post into the database
            $key_date_post_id = wp_insert_post( $key_date_post );

            if ( is_wp_error($key_date_post_id) || $key_date_post_id == 0 )
            {
                $return = array('error' => 'Failed to create key date post. Please try again');
                echo json_encode( $return );
                die();
            }

            add_post_meta( $key_date_post_id, '_date_due', $date_due );
            add_post_meta( $key_date_post_id, '_key_date_status', 'pending' );
            add_post_meta( $key_date_post_id, '_key_date_type_id', $date_type_id );
            add_post_meta( $key_date_post_id, '_key_date_notes', $date_notes );

            switch ( $parent_post_type )
            {
                case 'property' :
                {
                    add_post_meta( $key_date_post_id, '_property_id', $parent_post_id );
                    break;
                }
                case 'tenancy' :
                {
                    add_post_meta( $key_date_post_id, '_tenancy_id', $parent_post_id );

                    $parent_property_id = get_post_meta( $parent_post_id, '_property_id', true );
                    add_post_meta( $key_date_post_id, '_property_id', $parent_property_id );
                    break;
                }
            }
        }
        die();
    }

    public function get_management_dates_grid()
    {
        $post_id = (int)$_POST['post_id'];

        if ( isset($_POST['selected_type_id']) )
        {
            $selected_type_id = (int)$_POST['selected_type_id'];
        }

        if ( isset($_POST['selected_status']) )
        {
            $selected_status = ph_clean($_POST['selected_status']);
        }

        include( PH()->plugin_path() . '/includes/admin/views/html-management-dates-meta-box.php' );

        // Quit out
        die();
    }

    public function get_key_dates_quick_edit_row()
    {
        $post_id = (int)$_POST['post_id'];

        include( PH()->plugin_path() . '/includes/admin/views/html-key-dates-quick-edit.php' );

        // Quit out
        die();
    }

    public function check_key_date_recurrence()
    {
        $post_id = (int)$_POST['post_id'];

        $next_key_date = '';

        $key_date = new PH_Key_Date(get_post($post_id));
        $key_date_due = $key_date->date_due();

        $key_date_type = $key_date->key_date_type_id();

        $recurrence_rules = get_option( 'propertyhive_key_date_type', array() );
        $recurrence_rules = is_array( $recurrence_rules ) ? $recurrence_rules : array();

        if ( isset($recurrence_rules[$key_date_type]) && isset( $recurrence_rules[$key_date_type]['recurrence_rule'] ) )
        {
            foreach (explode(';', $recurrence_rules[$key_date_type]['recurrence_rule']) as $key_value_pair){
                list($key, $value) = explode('=', $key_value_pair);
                $recurrence[strtolower($key)] = $value;
            }

            if ( isset($recurrence['freq']) && $recurrence['freq'] != 'ONCE' )
            {
                $interval = isset($recurrence['interval']) ? $recurrence['interval'] : '1';
                switch( $recurrence['freq'] )
                {
                    case 'DAILY':
                        $frequency = 'day';
                        break;
                    case 'WEEKLY':
                        $frequency = 'week';
                        break;
                    case 'MONTHLY':
                        $frequency = 'month';
                        break;
                    case 'YEARLY':
                        $frequency = 'year';
                        break;
                }

                if ( isset($frequency) )
                {
                    $next_key_date = date_add($key_date_due, date_interval_create_from_date_string($interval . ' ' . $frequency));
                    $next_key_date = date_format($next_key_date, 'Y-m-d');
                }
            }
        }

        echo $next_key_date;

        // Quit out
        die();
    }

    public function save_key_date()
    {
        check_ajax_referer( 'save-key-date', 'security' );

        $this->json_headers();

        if ( ! current_user_can( 'manage_propertyhive' ) )
            wp_send_json_error( __( 'You do not have permission to manage key dates', 'propertyhive' ), 403 );

        $key_date_post_id = (int)$_POST['post_id'];

        $args = array(
            'ID' => $key_date_post_id,
            'post_title' => ph_clean($_POST['description']),
        );
        wp_update_post( $args );

        update_post_meta( $key_date_post_id, '_date_due', ph_clean($_POST['due_date_time']) );
        update_post_meta( $key_date_post_id, '_key_date_status', ph_clean($_POST['status']) );
        update_post_meta( $key_date_post_id, '_key_date_type_id', (int)$_POST['type'] );
        update_post_meta( $key_date_post_id, '_key_date_notes', sanitize_textarea_field($_POST['notes'] ));

        if ( isset($_POST['next_key_date']) )
        {
            // Insert next key date record
            $next_key_date_post = array(
                'post_title' => ph_clean($_POST['description']),
                'post_content' => '',
                'post_type' => 'key_date',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            );

            // Insert the post into the database
            $next_key_date_post_id = wp_insert_post( $next_key_date_post );

            if ( is_wp_error($next_key_date_post_id) || $next_key_date_post_id == 0 )
            {
                $return = array('error' => 'Failed to create next key date post. Please try again');
                echo json_encode( $return );
                die();
            }

            add_post_meta( $next_key_date_post_id, '_date_due', ph_clean($_POST['next_key_date']) );
            add_post_meta( $next_key_date_post_id, '_key_date_status', 'pending' );
            add_post_meta( $next_key_date_post_id, '_key_date_type_id', (int)$_POST['type'] );

            if ( metadata_exists('post', $key_date_post_id, '_property_id') ) {
                add_post_meta( $next_key_date_post_id, '_property_id', get_post_meta($key_date_post_id, '_property_id', true) );
            }

            if ( metadata_exists('post', $key_date_post_id, '_tenancy_id') ) {
                add_post_meta( $next_key_date_post_id, '_tenancy_id', get_post_meta($key_date_post_id, '_tenancy_id', true) );
            }
        }

        die();
    }

    public function delete_key_date()
    {
        check_ajax_referer( 'delete-key-date', 'security' );

        $this->json_headers();

        if ( ! current_user_can( 'manage_propertyhive' ) )
            wp_send_json_error( __( 'You do not have permission to manage key dates', 'propertyhive' ), 403 );

        $date_post_id = (int)$_POST['date_post_id'];

        wp_delete_post($date_post_id, TRUE);

        $return = array('success' => true);
        echo json_encode( $return );

        die();
    }

    public function get_property_tenancies_grid()
    {
        $post_id = (int)$_POST['post_id'];

        if ( isset($_POST['selected_status']) )
        {
            $selected_status = ph_clean($_POST['selected_status']);
        }

        include( PH()->plugin_path() . '/includes/admin/views/html-property-tenancies-meta-box.php' );

        // Quit out
        die();
    }

    public function get_contact_tenancies_grid()
    {
        $post_id = (int)$_POST['post_id'];

        if ( isset($_POST['selected_status']) )
        {
            $selected_status = ph_clean($_POST['selected_status']);
        }

        include( PH()->plugin_path() . '/includes/admin/views/html-contact-tenancies-meta-box.php' );

        // Quit out
        die();
    }

    public function get_contact_solicitor()
    {
        switch( get_post_type((int)$_POST['post_id']) )
        {
            case 'contact':
            {
                $contact_post_ids = array( (int)$_POST['post_id'] );
                break;
            }
            case 'property':
            {
                $owner_contact_ids = get_post_meta((int)$_POST['post_id'], '_owner_contact_id', TRUE);
                if ( !empty( $owner_contact_ids ) )
                {
                    if ( !is_array($owner_contact_ids) )
                    {
                        $owner_contact_ids = array($owner_contact_ids);
                    }

                    $contact_post_ids = $owner_contact_ids;
                }
                break;
            }
        }

        if ( isset( $contact_post_ids ) )
        {
            foreach ( $contact_post_ids as $contact_post_id )
            {
                $solicitor_contact_id = get_post_meta( $contact_post_id, '_contact_solicitor_contact_id', TRUE );
                if ( !empty($solicitor_contact_id) )
                {
                    $solicitor_name = get_the_title($solicitor_contact_id);

                    $solicitor_company_name = get_post_meta( $solicitor_contact_id, '_company_name', TRUE );
                    if ( !empty($solicitor_company_name) && $solicitor_company_name != $solicitor_name )
                    {
                        $solicitor_name .= ' (' . $solicitor_company_name . ')';
                    }

                    echo json_encode( array(
                        'id' => $solicitor_contact_id,
                        'name' => $solicitor_name,
                    ) );
                    break;
                }
            }
        }

        // Quit out
        die();
    }

    public function activate_pro_feature()
    {
        if ( !wp_verify_nonce( $_POST['_ajax_nonce'], "updates" ) ) 
        {
            $return = array(
                'errorMessage' => 'Invalid nonce provided'
            );
            wp_send_json_error($return);
        } 

        if ( ! current_user_can( 'install_plugins' ) ) 
        {
            $return = array(
                'errorMessage' => __( 'Sorry, you are not allowed to manage plugins on this site.' )
            );
            wp_send_json_error( $return );
        }
        
        // check plugin status
        $slug = ph_clean($_POST['slug']);

        $feature = get_ph_pro_feature( $slug );

        if ( $feature === false )
        {
            $return = array(
                'errorMessage' => 'Feature not found'
            );
            wp_send_json_error($return);
        }

        if ( is_plugin_active( $feature['wordpress_plugin_file'] ) )
        {
            $return = array(
                'errorMessage' => 'Plugin already active'
            );
            wp_send_json_error($return);
        }

        $pro = false;
        $plans = (isset($feature['plans']) & is_array($feature['plans'])) ? $feature['plans'] : array();
        if ( !in_array('free', $plans) )
        {
            $pro = true;
        }

        // check it's not a pro feature if they don't have pro enabled
        if ( $pro )
        {
            $valid_license_key = false;

            // check it's not a plugin that was installed pre version 2
            $pre_pro_add_ons = get_option( 'propertyhive_pre_pro_add_ons', array() );
            if ( empty($pre_pro_add_ons) ) { $pre_pro_add_ons = array(); }
            foreach ($pre_pro_add_ons as $pre_pro_add_on)
            {
                if ( $pre_pro_add_on['slug'] == $slug )
                {
                    // Yep. It was installed already and should be allowed to be activated
                    $valid_license_key = true;
                }
            }

            if ( $valid_license_key === false )
            {
                // check pro license key valid
                if ( PH()->license->is_valid_pro_license_key(true) )
                {
                    $product_id_and_package = PH()->license->get_pro_license_product_id_and_package();

                    if ( isset($product_id_and_package['success']) && $product_id_and_package['success'] === true )
                    {
                        if ( 
                            isset($feature['plans']) && 
                            isset($product_id_and_package['package']) &&
                            in_array($product_id_and_package['package'], $feature['plans'])
                        )
                        {
                            $valid_license_key = true;
                        }
                        else
                        {
                            $return = array(
                                'errorMessage' => 'Trying to activate a feature that\'s not on your chosen plan'
                            );
                            wp_send_json_error($return);
                        }
                    }
                    else
                    {
                        $return = array(
                            'errorMessage' => 'License key valid but failed to get package'
                        );
                        wp_send_json_error($return);
                    }
                }
                else
                {
                    $return = array(
                        'errorMessage' => 'Trying to activate a PRO feature but no valid PRO license key entered'
                    );
                    wp_send_json_error($return);
                }
            }

            if ( $valid_license_key === false )
            {
                $return = array(
                    'errorMessage' => 'Trying to activate a PRO feature but no valid PRO license key entered'
                );
                wp_send_json_error($return);
            }
        }

        if ( !is_dir(WP_PLUGIN_DIR . '/' . $slug) && strpos($feature['download_url'], 'wordpress.org') === false )
        {
            // not a public WP plugin. Must be hosted privately
            $response = wp_remote_get(
                $feature['download_url'],
                array(
                    'timeout' => 30
                )
            );

            if ( is_wp_error( $response ) )
            {
                $return = array(
                    'errorMessage' => $response->get_error_message()
                );
                wp_send_json_error($return);
            }

            if ( !isset($response['body']) )
            {
                $return = array(
                    'errorMessage' => 'No response body received'
                );
                wp_send_json_error($return);
            }

            $zip_contents = $response['body']; // use the content
        
            if ( empty($zip_contents) )
            {
                $return = array(
                    'errorMessage' => 'Failed to obtain plugin'
                );
                wp_send_json_error($return);
            }

            if ( ! wp_is_writable( WP_PLUGIN_DIR ) ) 
            {
                $return = array(
                    'errorMessage' => 'Destination directory (' . WP_PLUGIN_DIR . ') for writing plugin temporarily does not exist or is not writable.'
                );
                wp_send_json_error($return);
            }

            $tmpfname = WP_PLUGIN_DIR . '/' . $slug . '.zip';

            $handle = @fopen($tmpfname, "w");
            if ( $handle === false )
            {
                $return = array(
                    'errorMessage' => 'Failed to write plugin contents to temp file: ' . $tmpfname
                );
                wp_send_json_error($return);
            }
            fwrite($handle, $zip_contents);
            fclose($handle);

            global $wp_filesystem;

            require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

            $wp_filesystem = new WP_Filesystem_Direct( false );

            if ( !defined( 'FS_CHMOD_FILE' ) ) {
                define( 'FS_CHMOD_FILE', ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
            }
            if ( !defined( 'FS_CHMOD_DIR' ) ) {
                define( 'FS_CHMOD_DIR', ( fileperms( ABSPATH ) & 0777 | 0755 ) );
            }

            // file obtained and stored. need to unzip and put into plugins directory
            $unzipped = unzip_file( $tmpfname, WP_PLUGIN_DIR );
            if ( is_wp_error( $unzipped ) ) 
            {
                @unlink($tmpfname);

                $return = array(
                    'errorMessage' => $unzipped->get_error_message()
                );
                wp_send_json_error($return);
            }

            @unlink($tmpfname);

            // Need to sort out cache for activate plugin to work
            // Taken from WordPress.org docs
            $cache_plugins = wp_cache_get( 'plugins', 'plugins' );
            if ( !empty( $cache_plugins ) ) 
            {
                $new_plugin = array(
                    'Name' => $slug,
                    'PluginURI' => '',
                    'Version' => '',
                    'Description' => '',
                    'Author' => '',
                    'AuthorURI' => '',
                    'TextDomain' => '',
                    'DomainPath' => '',
                    'Network' => '',
                    'Title' => $slug,
                    'AuthorName' => '',
                );
                $cache_plugins[''][$feature['wordpress_plugin_file']] = $new_plugin;
                wp_cache_set( 'plugins', $cache_plugins, 'plugins' );
            }
        }

        if ( is_dir(WP_PLUGIN_DIR . '/' . $slug) )
        {
            // folder already exists. just activate it
            $activated = activate_plugin( $feature['wordpress_plugin_file'] );
            if ( is_wp_error( $activated ) ) 
            {
                $return = array(
                    'errorMessage' => $activated->get_error_message()
                );
                wp_send_json_error($return);
            }

            wp_send_json_success();
        }

        if ( strpos($feature['download_url'], 'wordpress.org') !== false )
        {
            // this is a public WP plugin
            wp_ajax_install_plugin();
        }

        wp_send_json_success();
    }

    public function deactivate_pro_feature()
    {
        if ( !wp_verify_nonce( $_POST['_ajax_nonce'], "updates" ) ) 
        {
            $return = array(
                'errorMessage' => 'Invalid nonce provided'
            );
            wp_send_json_error($return);
        } 

        if ( ! current_user_can( 'install_plugins' ) ) 
        {
            $return = array(
                'errorMessage' => __( 'Sorry, you are not allowed to manage plugins on this site.' )
            );
            wp_send_json_error( $return );
        }

        // check plugin is active
        $slug = ph_clean($_POST['slug']);

        $feature = get_ph_pro_feature( $slug );

        if ( !is_plugin_active( $feature['wordpress_plugin_file'] ) )
        {
            $return = array(
                'errorMessage' => 'Plugin not active'
            );
            wp_send_json_error($return);
        }

        deactivate_plugins( array($feature['wordpress_plugin_file']) );

        wp_send_json_success();
    }
}

new PH_AJAX();
