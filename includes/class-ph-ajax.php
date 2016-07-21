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
			'add_note'      => false,
			'delete_note'   => false,
			'search_contacts'   => false,
			'load_existing_owner_contact'   => false,
            'load_existing_features'   => false,
            'load_applicant_matching_properties'   => false,
			'make_property_enquiry'   => true
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_propertyhive_' . $ajax_event, array( $this, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_propertyhive_' . $ajax_event, array( $this, $ajax_event ) );
			}
		}
	}

	/**
	 * Output headers for JSON requests
	 */
	private function json_headers() {
		header( 'Content-Type: application/json; charset=utf-8' );
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
     * Load matching applicant properties
     */
    public function load_applicant_matching_properties() {

        global $post;

        check_ajax_referer( 'load-applicant-matching-properties', 'security' );
        
        $contact_id = $_POST['contact_id'];
        $applicant_profile = $_POST['applicant_profile'];

        $contact = get_post($contact_id);

        if ( !is_null( $contact ) )
        {
            $applicant_profile = get_post_meta( $contact_id, '_applicant_profile_' . $applicant_profile, TRUE );

            $args = array(
                'post_type' => 'property',
                'nopaging' => true,
            );

            // Meta query
            $meta_query = array('relation' => 'AND');
            $meta_query[] = array(
                'key' => '_on_market',
                'value' => 'yes'
            );
            if ( isset($applicant_profile['department']) && $applicant_profile['department'] == 'residential-sales' )
            {
                $meta_query[] = array(
                    'key' => '_department',
                    'value' => $applicant_profile['department']
                );
                if ( isset($applicant_profile['max_price_actual']) && $applicant_profile['max_price_actual'] != '' && $applicant_profile['max_price_actual'] != 0 )
                {
                    $meta_query[] = array(
                        'key' => '_price_actual',
                        'value' => $applicant_profile['max_price_actual'],
                        'compare' => '<=',
                        'type' => 'NUMERIC'
                    );
                }
            }
            elseif ( isset($applicant_profile['department']) && $applicant_profile['department'] == 'residential-lettings' )
            {
                $meta_query[] = array(
                    'key' => '_department',
                    'value' => $applicant_profile['department']
                );
                if ( isset($applicant_profile['max_price_actual']) && $applicant_profile['max_price_actual'] != '' && $applicant_profile['max_price_actual'] != 0 )
                {
                    $meta_query[] = array(
                        'key' => '_price_actual',
                        'value' => $applicant_profile['max_price_actual'],
                        'compare' => '<=',
                        'type' => 'NUMERIC'
                    );
                }
            }
            if ( isset($applicant_profile['min_beds']) && $applicant_profile['min_beds'] != '' && $applicant_profile['min_beds'] != 0 )
            {
                $meta_query[] = array(
                    'key' => '_bedrooms',
                    'value' => $applicant_profile['min_beds'],
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }
            $args['meta_query'] = $meta_query;

            // Term query
            $tax_query = array('relation' => 'AND');
            if ( isset($applicant_profile['property_types']) && is_array($applicant_profile['property_types']) && !empty($applicant_profile['property_types']) )
            {
                $tax_query[] = array(
                    'taxonomy' => 'property_type',
                    'field'    => 'term_id',
                    'terms'    => $applicant_profile['property_types'],
                    'operator' => 'IN',
                );
            }
            if ( isset($applicant_profile['locations']) && is_array($applicant_profile['locations']) && !empty($applicant_profile['locations']) )
            {
                $tax_query[] = array(
                    'taxonomy' => 'location',
                    'field'    => 'term_id',
                    'terms'    => $applicant_profile['locations'],
                    'operator' => 'IN',
                );
            }
            $args['tax_query'] = $tax_query;

            $properties_query = new WP_Query( $args );

            if ( $properties_query->have_posts() )
            {
                echo '<h2>' . $properties_query->found_posts . ' matching propert' . ( ( $properties_query->found_posts != 1 ) ? 'ies' : 'y') . ' found</h2>';

                echo '<div style="background:#F3F3F3; border:1px solid #DDD; padding:20px;">
                    
                    <h3 style="padding-top:0; margin-top:0;">Applicant Requirements</h3>';
                if ( 
                    isset($applicant_profile['department']) && $applicant_profile['department'] == 'residential-sales' &&
                    isset($applicant_profile['max_price_actual']) && $applicant_profile['max_price_actual'] != '' && $applicant_profile['max_price_actual'] != 0
                )
                {
                    echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                        <strong>Maximum Price:</strong><br>
                        &pound;' . number_format($applicant_profile['max_price']) . '
                    </div>';
                }
                if ( 
                    isset($applicant_profile['department']) && $applicant_profile['department'] == 'residential-lettings' &&
                    isset($applicant_profile['max_price_actual']) && $applicant_profile['max_price_actual'] != '' && $applicant_profile['max_price_actual'] != 0
                )
                {
                    echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                        <strong>Maximum Rent:</strong><br>
                        &pound;' . number_format($applicant_profile['max_rent']) . ' ' . $applicant_profile['rent_frequency'] . '
                    </div>';
                }
                if ( isset($applicant_profile['min_beds']) && $applicant_profile['min_beds'] != '' && $applicant_profile['min_beds'] != 0 )
                {
                    echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                        <strong>Minimum Beds:</strong><br>
                        ' . $applicant_profile['min_beds'] . '
                    </div>';
                }
                if ( isset($applicant_profile['property_types']) && is_array($applicant_profile['property_types']) && !empty($applicant_profile['property_types']) )
                {
                    $terms = get_terms('property_type', array('hide_empty' => false, 'fields' => 'names', 'include' => $applicant_profile['property_types']));
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                    {
                        $sliced_terms = array_slice( $terms, 0, 2 );
                        echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                            <strong>Property Types:</strong><br>
                            ' . implode(", ", $sliced_terms) . ( (count($terms) > 2) ? '<span title="' . addslashes( implode(", ", $terms) ) .'"> + ' . (count($terms) - 2) . ' more</span>' : '' ) . '
                        </div>';
                    }
                }
                if ( isset($applicant_profile['locations']) && is_array($applicant_profile['locations']) && !empty($applicant_profile['locations']) )
                {
                    $terms = get_terms('location', array('hide_empty' => false, 'fields' => 'names', 'include' => $applicant_profile['locations']));
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                    {
                        $sliced_terms = array_slice( $terms, 0, 2 );
                        echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                            <strong>Locations:</strong><br>
                            ' . implode(", ", $sliced_terms) . ( (count($terms) > 2) ? ' <span title="' . addslashes( implode(", ", $terms) ) .'">+ ' . (count($terms) - 2) . ' more</span>' : '' ) . '
                        </div>';
                    }
                }

                if ( isset($applicant_profile['notes']) && $applicant_profile['notes'] != '' )
                {
                    echo '<div style="display:inline-block; width:100%; vertical-align:top; margin-top:15px;">
                            <strong>Additional Requirement Notes:</strong><br>
                            ' . strip_tags( $applicant_profile['notes'] ) . '
                        </div>';
                }

                echo '</div>';

                while ( $properties_query->have_posts() )
                {
                    $properties_query->the_post();

                    $property = new PH_Property($post->ID);

                    echo '<div style="padding:20px 0; border-bottom:1px solid #CCC;">';
                    
                        echo '<div style="float:left; width:18%;"><a href="' . get_edit_post_link( $post->ID ) . '" target="_blank"><img src="' . $property->get_main_photo_src() . '" style="max-width:100%; margin:0 auto; display:block;" alt="' . addslashes($property->get_formatted_summary_address()) . '"></a></div>';
                        
                        echo '<div style="float:right; width:79%;">';
                            
                            echo '<h3 style="margin:0; padding:0; margin-bottom:9px;"><a href="' . get_edit_post_link( $post->ID ) . '" target="_blank">' . $property->get_formatted_summary_address() . '</a></h3>';

                            echo '<div style="margin-bottom:7px;">
                                <strong>' . ( ($property->_department == 'residential-lettings') ? __('Rent', 'propertyhive') : __('Price', 'propertyhive') ) . ': ' . $property->get_formatted_price() . '</strong>
                                |
                                ' . $property->bedrooms . ' bed ' . $property->get_property_type() . '
                                |
                                ' . $property->get_availability() . '
                            </div>';

                            echo '<div style="">' . strip_tags(get_the_excerpt()) . '</div>';

                        echo '</div>';

                        echo '<div style="clear:both"></div>';

                    echo '</div>';
                }
            }
            else
            {
                echo '<div style="text-align:center"><br><br>' . __( 'No matching properties found', 'propertyhive' ) . '</div>';
            }
            wp_reset_postdata();
        }
        else
        {
            echo __( 'Invalid contact record', 'propertyhive' );
        }
        
        // Quit out
        die();
    }
    
    /**
     * Load existing owner on property record
     */
    public function load_existing_owner_contact() {
        
        check_ajax_referer( 'load-existing-owner-contact', 'security' );
        
        $contact_id = $_POST['contact_id'];
        
        $contact = get_post($contact_id);
        
        if ( !is_null( $contact ) )
        {
            echo '<p class="form-field">';
                echo '<label>' . __('Name', 'propertyhive') . '</label>';
                echo '<a href="' . get_edit_post_link( $contact_id ) . '">' . get_the_title($contact_id) . '</a>';
            echo '</p>';
            
            echo '<p class="form-field">';
                echo '<label>' . __('Address', 'propertyhive') . '</label>';
                echo get_post_meta($contact_id, '_address_name_number', TRUE) . ' ';
                echo get_post_meta($contact_id, '_address_street', TRUE) . ', ';
                echo get_post_meta($contact_id, '_address_two', TRUE) . ', ';
                echo get_post_meta($contact_id, '_address_three', TRUE) . ', ';
                echo get_post_meta($contact_id, '_address_four', TRUE) . ', ';
                echo get_post_meta($contact_id, '_address_postcode', TRUE);
            echo '</p>';
            
            echo '<p class="form-field">';
                echo '<label>' . __('Telephone Number', 'propertyhive') . '</label>';
                echo get_post_meta($contact_id, '_telephone_number', TRUE);
            echo '</p>';
            
            echo '<p class="form-field">';
                echo '<label>' . __('Email Address', 'propertyhive') . '</label>';
                echo get_post_meta($contact_id, '_email_address', TRUE);
            echo '</p>';
        }
        else
        {
            echo __( 'Invalid contact record', 'propertyhive' );
        }
        
        echo '<p class="form-field">';
            echo '<label></label>';
            echo '<a href="" class="button" id="remove-owner-contact">Remove Owner</a>';
        echo '</p>';
        
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
        
        $keyword = trim( $_POST['keyword'] );
        
        if ( !empty( $keyword ) && strlen( $keyword ) > 2 )
        {
            // Get all contacts that match the name
            $args = array(
                'post_type' => 'contact',
                'nopaging' => true,
                'post_status' => array( 'publish' )
            );
            
            add_filter( 'posts_where', array( $this, 'search_contacts_where' ), 10, 2 );
            
            $contact_query = new WP_Query( $args );
            
            remove_filter( 'posts_where', array( $this, 'search_contacts_where' ) );
            
            if ( $contact_query->have_posts() )
            {
                while ( $contact_query->have_posts() )
                {
                    $contact_query->the_post();
                    
                    $return[] = array(
                        'ID' => $post->ID,
                        'post_title' => get_the_title()
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
    
    function search_contacts_where( $where, &$wp_query )
    {
        global $wpdb;
        
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( like_escape( trim( $_POST['keyword'] ) ) ) . '%\'';
        
        return $where;
    }
    
	/**
	 * Add order note via ajax
	 */
	public function add_note() {
    
		check_ajax_referer( 'add-note', 'security' );
        
		$post_id   = (int) $_POST['post_id'];
		$note      = wp_kses_post( trim( stripslashes( $_POST['note'] ) ) );
		$note_type = $_POST['note_type'];

		if ( $post_id > 0 ) {
			$property      = new PH_Property( $post_id );
			$comment_id = $property->add_note( $note );
            
            $comment = get_comment($comment_id);
            
			echo '<li rel="' . esc_attr( $comment_id ) . '" class="note"><div class="note_content">';
			echo wpautop( wptexturize( $note ) );
			echo '</div>
			<p class="meta">
			 <abbr class="exact-date" title="' . $comment->comment_date_gmt . ' GMT">' . printf( __( 'added %s ago', 'propertyhive' ), human_time_diff( strtotime( $comment->comment_date_gmt ), current_time( 'timestamp', 1 ) ) ) . '</abbr>
			 ';
			 if ( $comment->comment_author !== __( 'PropertyHive', 'propertyhive' ) ) printf( ' ' . __( 'by %s', 'propertyhive' ), $comment->comment_author );
			 echo '<a href="#" class="delete_note">'.__( 'Delete note', 'propertyhive' ).'</a>
			</p>';
			echo '</li>';
		}

		// Quit out
		die();
	}

	/**
	 * Delete order note via ajax
	 */
	public function delete_note() {

		check_ajax_referer( 'delete-note', 'security' );

		$note_id = (int) $_POST['note_id'];

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
        //if ( ! array_key_exists( 'property_id', $form_controls ) )
        //{
        //    $errors[] = __( 'Property ID is a required field and must be supplied when making an enquiry', 'propertyhive' );
        //}
        //else
        //{
            if ( ! isset( $_POST['property_id'] ) || ( isset( $_POST['property_id'] ) && empty( $_POST['property_id'] ) ) )
            {
                $errors[] = __( 'Property ID is a required field and must be supplied when making an enquiry', 'propertyhive' ) . ': ' . $key;
            }
            else
            {
                $post = get_post($_POST['property_id']);
                
                $form_controls = ph_get_property_enquiry_form_fields();
    
                $form_controls = apply_filters( 'propertyhive_property_enquiry_form_fields', $form_controls );
            }
        //}
        
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
            
            // Get recipient email address
            $to = '';
            
            // Try and get office's email address first, else fallback to admin email
            $office_id = get_post_meta($_POST['property_id'], '_office_id', TRUE);
            if ( $office_id != '' )
            {
                if ( get_post_type( $office_id ) == 'office' )
                {
                    $property_department = get_post_meta($_POST['property_id'], '_department', TRUE);
                    
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
            
            $subject = __( 'New Property Enquiry', 'propertyhive' ) . ': ' . get_the_title( $_POST['property_id'] );
            $message = __( "You have received a property enquiry via your website. Please find details of the enquiry below", 'propertyhive' ) . "\n\n";
            
            $message .= __( 'Property', 'propertyhive' ) . ': ' . get_the_title( $_POST['property_id'] ) . " (" . get_permalink( $_POST['property_id'] ) . ")";
            
            unset($form_controls['action']);
            unset($form_controls['property_id']); // Unset so the fields dosn't get shown in the enquiry details
            
            foreach ($form_controls as $key => $control)
            {
                $label = ( isset($control['label']) ) ? $control['label'] : $key;
                $message .= $label . ": " . $_POST[$key] . "\n";
            }
            
            $headers = array();
            if ( isset($_POST['name']) && isset($_POST['email_address']) && ! empty($_POST['name']) && ! empty($_POST['email_address']) )
            {
                $headers[] = 'From: ' . $_POST['name'] . ' <' . $_POST['email_address'] . '>';
            }
            elseif ( isset($_POST['email_address']) && ! empty($_POST['email_address']) )
            {
                $headers[] = 'From: <' . $_POST['email_address'] . '>';
            }
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
                
                // Now insert into enquiries section of WordPress
                $title = __( 'Property Enquiry', 'propertyhive' ) . ': ' . get_the_title( $_POST['property_id'] );
                if ( isset($_POST['name']) && ! empty($_POST['name']) )
                {
                    $title .= __( ' from ', 'propertyhive' ) . sanitize_text_field($_POST['name']);
                }
                
                $enquiry_post = array(
                  'post_title'    => $title,
                  'post_content'  => '',
                  'post_type'  => 'enquiry',
                  'post_status'   => 'publish'
                );
                
                // Insert the post into the database
                $enquiry_post_id = wp_insert_post( $enquiry_post );
                
                add_post_meta( $enquiry_post_id, '_status', 'open' );
                add_post_meta( $enquiry_post_id, '_source', 'website' );
                add_post_meta( $enquiry_post_id, '_negotiator_id', '' );
                add_post_meta( $enquiry_post_id, '_office_id', $office_id );
                
                foreach ($_POST as $key => $value)
                {
                    add_post_meta( $enquiry_post_id, $key, $value );
                }
            }
        }
        
        $this->json_headers();
        echo json_encode( $return );
        
        // Quit out
        die();
    }
}

new PH_AJAX();
