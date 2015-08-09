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
     * Load existing owner on property record
     */
    public function load_existing_owner_contact() {
        
        check_ajax_referer( 'load-existing-owner-contact', 'security' );
        
        $contact_id = $_POST['contact_id'];
        
        $contact = get_post($contact_id);
        
        $output = '';
        
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
        
        echo $output;
        
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
                            break;
                        }
                        case "residential-lettings":
                        {
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
