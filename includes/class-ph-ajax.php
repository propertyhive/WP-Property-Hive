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

            // Viewing actions
            'book_viewing_property' => false,
            'book_viewing_contact' => false,
            'get_viewing_details_meta_box' => false,
            'get_viewing_actions' => false,
            'viewing_carried_out' => false,
            'viewing_cancelled' => false,
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
                'post_status' => array( 'publish' ),
                'fields' => 'ids'
            );
            if ( isset($_POST['contact_type']) && $_POST['contact_type'] != '' )
            {
                $args['meta_query'] = array(
                    array(
                        'key' => '_contact_types',
                        'value' => $_POST['contact_type'],
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
                    
                    $return[] = array(
                        'ID' => get_the_ID(),
                        'post_title' => get_the_title(get_the_ID())
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
        
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( like_escape( trim( $_POST['keyword'] ) ) ) . '%\'';
        
        return $where;
    }

    /**
     * Search propertie via ajax
     */
    public function search_properties() {
        
        global $post;
        
        check_ajax_referer( 'search-properties', 'security' );
        
        $return = array();
        
        $keyword = trim( $_POST['keyword'] );
        
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
                    'value' => $_POST['department'],
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
                    
                    $return[] = array(
                        'ID' => get_the_ID(),
                        'post_title' => $property->get_formatted_full_address(),
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
            (mt1.meta_key='_address_name_number' AND mt1.meta_value LIKE '" . esc_sql($_POST['keyword']). "%')
            OR
            (mt1.meta_key='_address_street' AND mt1.meta_value LIKE '" . esc_sql($_POST['keyword']). "%')
            OR
            (mt1.meta_key='_address_2' AND mt1.meta_value LIKE '" . esc_sql($_POST['keyword']). "%')
            OR
            (mt1.meta_key='_address_3' AND mt1.meta_value LIKE '" . esc_sql($_POST['keyword']). "%')
            OR
            (mt1.meta_key='_address_4' AND mt1.meta_value LIKE '" . esc_sql($_POST['keyword']). "%')
            OR
            (mt1.meta_key='_address_postcode' AND mt1.meta_value LIKE '" . esc_sql($_POST['keyword']). "%')
            OR
            (mt1.meta_key='_reference_number' AND mt1.meta_value = '" . esc_sql($_POST['keyword']). "')
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
        
        $keyword = trim( $_POST['keyword'] );
        
        if ( !empty( $keyword ) && strlen( $keyword ) > 2 )
        {
            // Get all contacts that match the name
            $args = array(
                'number' => 9999,
                'search' => $keyword . '*',
                'orderby' => 'display_name'
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
                $headers[] = 'From: ' . $_POST['name'] . ' <' . $from_email_address . '>';
            }
            else
            {
                $headers[] = 'From: <' . $from_email_address . '>';
            }
            $headers[] = 'Reply-To: ' . $_POST['email_address'];
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

    // Dashboard related functionas
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
                'post_title'    => wp_strip_all_tags($_POST['applicant_name']),
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
                $applicant_contact_ids[] = $applicant_id;
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
            
            add_post_meta( $viewing_post_id, '_start_date_time', $_POST['start_date'] . ' ' . $_POST['start_time'] );
            add_post_meta( $viewing_post_id, '_duration', 30 * 60 ); // Stored in seconds. Default to 30 mins
            add_post_meta( $viewing_post_id, '_property_id', $_POST['property_id'] );
            add_post_meta( $viewing_post_id, '_applicant_contact_id', $applicant_contact_id );
            add_post_meta( $viewing_post_id, '_status', 'pending' );
            add_post_meta( $viewing_post_id, '_feedback_status', '' );
            add_post_meta( $viewing_post_id, '_feedback', '' );
            add_post_meta( $viewing_post_id, '_feedback_passed_on', '' );

            if ( !empty($_POST['negotiator_ids']) )
            {
                foreach ( $_POST['negotiator_ids'] as $negotiator_id )
                {
                    add_post_meta( $viewing_post_id, '_negotiator_id', $negotiator_id );
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
            
            add_post_meta( $viewing_post_id, '_start_date_time', $_POST['start_date'] . ' ' . $_POST['start_time'] );
            add_post_meta( $viewing_post_id, '_duration', 30 * 60 ); // Stored in seconds. Default to 30 mins
            add_post_meta( $viewing_post_id, '_property_id', $property_id );
            add_post_meta( $viewing_post_id, '_applicant_contact_id', $_POST['contact_id'] );
            add_post_meta( $viewing_post_id, '_status', 'pending' );
            add_post_meta( $viewing_post_id, '_feedback_status', '' );
            add_post_meta( $viewing_post_id, '_feedback', '' );
            add_post_meta( $viewing_post_id, '_feedback_passed_on', '' );

            if ( !empty($_POST['negotiator_ids']) )
            {
                foreach ( $_POST['negotiator_ids'] as $negotiator_id )
                {
                    add_post_meta( $viewing_post_id, '_negotiator_id', $negotiator_id );
                }
            }
        }

        $properties = array();
        foreach ( $_POST['property_ids'] as $property_id )
        {
            $properties[] = array(
                'ID' => $property_id,
                'post_title' => get_the_title($property_id),
                'edit_link' => get_edit_post_link( $property_id, '' ),
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
        check_ajax_referer( 'viewing-details-meta-box', 'security' );

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

        $post_id = $_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );
        $feedback_status = get_post_meta( $post_id, '_feedback_status', TRUE );

        echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="propertyhive_viewing_actions_meta_box">

        <div class="options_group" style="padding-top:8px;">';

        $show_feedback_meta_boxes = false;

        $actions = array();

        if ( $status == 'pending' )
        {
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
        }

        if ( $status == 'carried_out' )
        {
            if ( $feedback_status == '' )
            {
                $actions[] = '<a 
                    href="#action_panel_viewing_interested" 
                    class="button button-success viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Applicant Interested', 'propertyhive') . '</a>';

                $actions[] = '<a 
                    href="#action_panel_viewing_not_interested" 
                    class="button button-danger viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Applicant Not Interested', 'propertyhive') . '</a>';

                $actions[] = '<a 
                    href="#action_panel_viewing_feedback_not_required" 
                    class="button viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Feedback Not Required', 'propertyhive') . '</a>';

                $show_feedback_meta_boxes = true;
            }

            if ( $feedback_status == 'interested' )
            {
                $actions[] = '<a 
                    href="' . trim(admin_url(), '/') . '/post-new.php?post_type=viewing&applicant_contact_id=' . get_post_meta( $post_id, '_applicant_contact_id', TRUE ) . '&property_id=' . get_post_meta( $post_id, '_property_id', TRUE ) . '&viewing_id=' . $post_id .'" 
                    class="button button-success"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Book Second Viewing', 'propertyhive') . '</a>';

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
                                >' . __('View Offer', 'propertyhive') . '</a>';
                        }
                        else
                        {
                            $actions[] = '<a 
                                    href="' . wp_nonce_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ), '1', 'create_offer' ) . '" 
                                    class="button button-success"
                                    style="width:100%; margin-bottom:7px; text-align:center" 
                                >' . __('Record Offer', 'propertyhive') . '</a>';
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
                >' . __('Feedback Passed On To Owner', 'propertyhive') . '</a>';
            }

            if ( $feedback_status == 'interested' || $feedback_status == 'not_interested' || $feedback_status == 'not_required' )
            {
                $actions[] = '<a 
                    href="#action_panel_viewing_revert_feedback_pending" 
                    class="button viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Revert To Feedback Pending', 'propertyhive') . '</a>';
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
                        >' . __('View Offer', 'propertyhive') . '</a>';
                }
            }
        }

        if ( ( $status == 'carried_out' && $feedback_status == '' ) || $status == 'cancelled' )
        {
            $actions[] = '<a 
                    href="#action_panel_viewing_revert_pending" 
                    class="button viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Revert To Pending', 'propertyhive') . '</a>';
        }

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

        if ( $show_feedback_meta_boxes )
        {
            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_interested" style="display:none;">

                <div class="options_group" style="padding-top:8px;">

                    <div class="form-field">

                        <label for="_viewing_interested_feedback">' . __( 'Applicant Feedback', 'propertyhive' ) . '</label>
                        
                        <textarea id="_interested_feedback" name="_interested_feedback" style="width:100%;">' . get_post_meta( $post_id, '_feedback', TRUE ) . '</textarea>

                    </div>

                    <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
                    <a class="button button-primary interested-feedback-action-submit" href="#">' . __( 'Save Feedback', 'propertyhive' ) . '</a>

                </div>

            </div>';

            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_not_interested" style="display:none;">

                <div class="options_group" style="padding-top:8px;">

                    <div class="form-field">

                        <label for="_viewing_not_interested_feedback">' . __( 'Applicant Feedback', 'propertyhive' ) . '</label>
                        
                        <textarea id="_not_interested_feedback" name="_not_interested_feedback" style="width:100%;">' . get_post_meta( $post_id, '_feedback', TRUE ) . '</textarea>

                    </div>

                    <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
                    <a class="button button-primary not-interested-feedback-action-submit" href="#">' . __( 'Save Feedback', 'propertyhive' ) . '</a>

                </div>

            </div>';
        }

        die();
    }

    public function viewing_carried_out()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = $_POST['viewing_id'];

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

        $post_id = $_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'pending' )
        {
            update_post_meta( $post_id, '_status', 'cancelled' );

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

    public function viewing_interested_feedback()
    {
        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = $_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' )
        {
            update_post_meta( $post_id, '_feedback_status', 'interested' );
            update_post_meta( $post_id, '_feedback', $_POST['feedback'] );

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

        $post_id = $_POST['viewing_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' )
        {
            update_post_meta( $post_id, '_feedback_status', 'not_interested' );
            update_post_meta( $post_id, '_feedback', $_POST['feedback'] );

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

        $post_id = $_POST['viewing_id'];

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

        $post_id = $_POST['viewing_id'];

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

        $post_id = $_POST['viewing_id'];

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

        $post_id = $_POST['viewing_id'];

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
                        'value' => $_POST['post_id']
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
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_post_meta(get_the_ID(), '_applicant_contact_id', TRUE), '' ) . '">' . get_the_title(get_post_meta(get_the_ID(), '_applicant_contact_id', TRUE)) . '</a></td>';
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
                        'value' => $_POST['post_id']
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
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_post_meta(get_the_ID(), '_property_id', TRUE), '' ) . '">' . $property->get_formatted_full_address() . '</a></td>';
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
                'post_title'    => wp_strip_all_tags($_POST['applicant_name']),
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
                $applicant_contact_ids[] = $applicant_id;
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
            
            add_post_meta( $offer_post_id, '_offer_date_time', $_POST['offer_date'] . ' ' . $_POST['offer_time'] );
            add_post_meta( $offer_post_id, '_property_id', $_POST['property_id'] );
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

            $amount = preg_replace("/[^0-9]/", '', $_POST['amount']);
            
            add_post_meta( $offer_post_id, '_offer_date_time', $_POST['offer_date'] . ' ' . $_POST['offer_time'] );
            add_post_meta( $offer_post_id, '_property_id', $property_id );
            add_post_meta( $offer_post_id, '_applicant_contact_id', $_POST['contact_id'] );
            add_post_meta( $offer_post_id, '_amount', $amount );
            add_post_meta( $offer_post_id, '_status', 'pending' );
        }

        $properties = array();
        foreach ( $_POST['property_ids'] as $property_id )
        {
            $properties[] = array(
                'ID' => $property_id,
                'post_title' => get_the_title($property_id),
                'edit_link' => get_edit_post_link( $property_id, '' ),
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

        $offer_date_time = get_post_meta( $offer->id, $offer->offer_date_time, true );
        if ( $offer_date_time == '' )
        {
            $offer_date_time = date("Y-m-d H:i:s");
        }

        echo '<p class="form-field offer_date_time_field">
    
            <label for="_offer_date">' . __('Offer Date / Time', 'propertyhive') . '</label>
            
            <input type="text" id="_offer_date" name="_offer_date" class="date-picker short" placeholder="yyyy-mm-dd" style="width:120px;" value="' . date("Y-m-d", strtotime($offer_date_time)) . '">
            <select id="_offer_time_hours" name="_offer_time_hours" class="select short" style="width:55px">';
        
        if ( $offer_date_time == '' )
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
        
        if ( $offer_date_time == '' )
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

        $post_id = $_POST['offer_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="propertyhive_offer_actions_meta_box">

        <div class="options_group" style="padding-top:8px;">';

        if ( $status == 'pending' )
        {
            echo '<a 
                    href="#action_panel_offer_accepted" 
                    class="button button-success offer-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Accept Offer', 'propertyhive') . '</a>';
            echo '<a 
                    href="#action_panel_offer_declined" 
                    class="button button-danger offer-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Decline Offer', 'propertyhive') . '</a>';
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
                echo '<a 
                        href="' . get_edit_post_link( $sale_id, '' ) . '" 
                        class="button"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . __('View Sale', 'propertyhive') . '</a>';
            }
            else
            {
                echo '<a 
                        href="' . wp_nonce_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ), '1', 'create_sale' ) . '" 
                        class="button button-success"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . __('Create Sale', 'propertyhive') . '</a>';
            }
        }

        if ( $status == 'declined' )
        {
            
        }

        if ( $status == 'accepted' || $status == 'declined' )
        {
            echo '<a 
                    href="#action_panel_offer_revert_pending" 
                    class="button offer-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Revert To Pending', 'propertyhive') . '</a>';
        }

        echo '</div>

        </div>';

        die();
    }

    public function offer_accepted()
    {
        check_ajax_referer( 'offer-actions', 'security' );

        $post_id = $_POST['offer_id'];

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

        $post_id = $_POST['offer_id'];

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

        $post_id = $_POST['offer_id'];

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
                        'value' => $_POST['post_id']
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
                        'value' => $_POST['post_id']
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
                        $owner_contact_id = $property->_owner_contact_id;
                        if ($owner_contact_id !='' && $owner_contact_id != 0)
                        {
                            echo get_the_title($owner_contact_id) . '<br>';
                            echo '<div style="color:#BBB">';
                            echo 'T: ' . get_post_meta($owner_contact_id, '_telephone_number', TRUE) . '<br>';
                            echo 'E: ' . get_post_meta($owner_contact_id, '_email_address', TRUE);
                            echo '</div>';
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

        $sale_date_time = get_post_meta( $offer->id, $sale->sale_date_time, true );
        if ( $sale_date_time == '' )
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

        $post_id = $_POST['sale_id'];

        $status = get_post_meta( $post_id, '_status', TRUE );

        echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="propertyhive_sale_actions_meta_box">

        <div class="options_group" style="padding-top:8px;">';

        if ( $status == 'current' )
        {
            echo '<a 
                    href="#action_panel_sale_exchanged" 
                    class="button button-success sale-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Sale Exchanged', 'propertyhive') . '</a>';
            
        }

        if ( $status == 'exchanged' )
        {
            echo '<a 
                    href="#action_panel_sale_completed" 
                    class="button button-success sale-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Sale Completed', 'propertyhive') . '</a>';
        }

        if ( $status == 'completed' )
        {
            echo '<div style="text-align:center">' . __( 'No actions to display', 'propertyhive' ) . '</div>';
        }

        if ( $status == 'current' || $status == 'exchanged' )
        {
            echo '<a 
                    href="#action_panel_sale_fallen_through" 
                    class="button sale-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . __('Sale Fallen Through', 'propertyhive') . '</a>';
        }

        echo '</div>

        </div>';

        die();
    }

    public function sale_exchanged()
    {
        check_ajax_referer( 'sale-actions', 'security' );

        $post_id = $_POST['sale_id'];

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

        $post_id = $_POST['sale_id'];

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

        $post_id = $_POST['sale_id'];

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
                        'value' => $_POST['post_id']
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
                        'value' => $_POST['post_id']
                    )
                )
            );
            $viewings_query = new WP_Query( $args );

            if ( $viewings_query->have_posts() )
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

                while ( $viewings_query->have_posts() )
                {
                    $viewings_query->the_post();

                    $property = new PH_Property((int)get_post_meta(get_the_ID(), '_property_id', TRUE));

                    echo '<tr>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_the_ID(), '') . '">' . date("jS F Y", strtotime(get_post_meta(get_the_ID(), '_sale_date_time', TRUE))) . '</a></td>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_post_meta(get_the_ID(), '_property_id', TRUE), '' ) . '">' . $property->get_formatted_full_address() . '</a></td>';
                        echo '<td style="text-align:left;">OWNER DETAILS</td>';
                        echo '<td style="text-align:left;">' . get_post_meta(get_the_ID(), '_amount', TRUE) . '</td>';
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
