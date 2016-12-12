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
			'make_property_enquiry'   => true,
            'create_contact_from_enquiry' => false,
            'get_news' => false
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
	 * Add note via ajax
	 */
	public function add_note() {
    
		check_ajax_referer( 'add-note', 'security' );
        
		$post_id   = (int) $_POST['post_id'];

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
            
            $message .= __( 'Property', 'propertyhive' ) . ': ' . get_the_title( $_POST['property_id'] ) . " (" . get_permalink( $_POST['property_id'] ) . ")\n\n";
            
            unset($form_controls['action']);
            unset($_POST['action']);
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

    /**
     * Create contact from enquiry
     */
    public function create_contact_from_enquiry()
    {
        global $post;

        $enquiry_post_id = ( (isset($_POST['post_id'])) ? $_POST['post_id'] : '' );
        $nonce = ( (isset($_POST['security'])) ? $_POST['security'] : '' );

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

        if ( $telephone !== FALSE ) { update_post_meta( $contact_post_id, '_telephone_number', $telephone ); }
        if ( $email !== FALSE ) { update_post_meta( $contact_post_id, '_email_address', $email ); }

        die( json_encode( array('success' => get_edit_post_link($contact_post_id, '')) ) );
    }

    public function get_news()
    {
        $this->json_headers();

        include_once( ABSPATH . WPINC . '/feed.php' );

        $return = array();

        // Get a SimplePie feed object from the specified feed source.
        $rss = fetch_feed( 'https://wp-property-hive.com/feed/' );

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
}

new PH_AJAX();
