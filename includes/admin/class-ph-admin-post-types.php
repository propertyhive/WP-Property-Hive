<?php
/**
 * Post Types Admin
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Admin_Post_Types' ) ) :

/**
 * PH_Admin_Post_Types Class
 */
class PH_Admin_Post_Types {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'include_post_type_handlers' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
        add_action( 'admin_print_scripts', array( $this, 'remove_month_filter' ) );
		add_action( 'admin_print_scripts', array( $this, 'disable_autosave' ) );

        // Filters
        add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
        add_filter( 'request', array( $this, 'request_query' ) );

		// Status transitions
		add_action( 'delete_post', array( $this, 'delete_post' ) );
		add_action( 'wp_trash_post', array( $this, 'trash_post' ) );
		add_action( 'untrash_post', array( $this, 'untrash_post' ) );
        
        
	}

	/**
	 * Conditonally load classes and functions only needed when viewing a post type.
	 */
	public function include_post_type_handlers() {
        include( 'post-types/class-ph-admin-header-stripes.php' );
		include( 'post-types/class-ph-admin-meta-boxes.php' );
        
		include( 'post-types/class-ph-admin-cpt-property.php' );
        include( 'post-types/class-ph-admin-cpt-contact.php' );
        include( 'post-types/class-ph-admin-cpt-enquiry.php' );
        include( 'post-types/class-ph-admin-cpt-office.php' );
        include( 'post-types/class-ph-admin-cpt-appraisal.php' );
        include( 'post-types/class-ph-admin-cpt-viewing.php' );
        include( 'post-types/class-ph-admin-cpt-offer.php' );
        include( 'post-types/class-ph-admin-cpt-sale.php' );
	}

	/**
	 * Change messages when a post type is updated.
	 *
	 * @param  array $messages
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages['property'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Property updated. <a href="%s">View Property</a>', 'propertyhive' ), esc_url( get_permalink($post_ID) ) ),
			2 => __( 'Custom field updated.', 'propertyhive' ),
			3 => __( 'Custom field deleted.', 'propertyhive' ),
			4 => __( 'Property updated.', 'propertyhive' ),
			5 => isset($_GET['revision']) ? sprintf( __( 'Property restored to revision from %s', 'propertyhive' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Property published. <a href="%s">View Property</a>', 'propertyhive' ), esc_url( get_permalink($post_ID) ) ),
			7 => __( 'Property saved.', 'propertyhive' ),
			8 => sprintf( __( 'Property submitted. <a target="_blank" href="%s">Preview Property</a>', 'propertyhive' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( 'Property scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Property</a>', 'propertyhive' ),
			  date_i18n( __( 'M j, Y @ G:i', 'propertyhive' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __( 'Property draft updated. <a target="_blank" href="%s">Preview Property</a>', 'propertyhive' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		$messages['contact'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => __( 'Contact updated.', 'propertyhive' ),
            2 => __( 'Custom field updated.', 'propertyhive' ),
            3 => __( 'Custom field deleted.', 'propertyhive' ),
            4 => __( 'Contact updated.', 'propertyhive' ),
            5 => isset($_GET['revision']) ? sprintf( __( 'Contact restored to revision from %s', 'propertyhive' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6 => __( 'Contact published.', 'propertyhive' ),
            7 => __( 'Contact saved.', 'propertyhive' ),
            8 => __( 'Contact submitted.', 'propertyhive' ),
            9 => sprintf( __( 'Contact scheduled for: <strong>%1$s</strong>.', 'propertyhive' ), date_i18n( __( 'M j, Y @ G:i', 'propertyhive' ), strtotime( $post->post_date ) )),
            10 => __( 'Contact draft updated.', 'propertyhive' ),
        );
        
        $messages['office'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => __( 'Office updated.', 'propertyhive' ),
            2 => __( 'Custom field updated.', 'propertyhive' ),
            3 => __( 'Custom field deleted.', 'propertyhive' ),
            4 => __( 'Office updated.', 'propertyhive' ),
            5 => isset($_GET['revision']) ? sprintf( __( 'Office restored to revision from %s', 'propertyhive' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6 => sprintf( __( 'Office published.', 'propertyhive' ), esc_url( get_permalink($post_ID) ) ),
            7 => __( 'Office saved.', 'propertyhive' ),
            8 => sprintf( __( 'Office submitted.', 'propertyhive' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
            9 => sprintf( __( 'Office scheduled for: <strong>%1$s</strong>.', 'propertyhive' ),
              date_i18n( __( 'M j, Y @ G:i', 'propertyhive' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
            10 => sprintf( __( 'Office draft updated. ', 'propertyhive' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
        );
        
        $messages['enquiry'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf( __( 'Enquiry updated.', 'propertyhive' ), esc_url( get_permalink($post_ID) ) ),
            2 => __( 'Custom field updated.', 'propertyhive' ),
            3 => __( 'Custom field deleted.', 'propertyhive' ),
            4 => __( 'Enquiry updated.', 'propertyhive' ),
            5 => isset($_GET['revision']) ? sprintf( __( 'Enquiry restored to revision from %s', 'propertyhive' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6 => sprintf( __( 'Enquiry published.', 'propertyhive' ), esc_url( get_permalink($post_ID) ) ),
            7 => __( 'Enquiry saved.', 'propertyhive' ),
            8 => sprintf( __( 'Enquiry submitted.', 'propertyhive' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
            9 => sprintf( __( 'Enquiry scheduled for: <strong>%1$s</strong>.', 'propertyhive' ),
              date_i18n( __( 'M j, Y @ G:i', 'propertyhive' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
            10 => sprintf( __( 'Enquiry draft updated.', 'propertyhive' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
        );

		return $messages;
	}

    /**
     * Remove month filter from some property hive pages
     */
    public function remove_month_filter() {
        global $typenow;
        
        if ($typenow == 'property' || $typenow == 'contact' || $typenow == 'appraisal' || $typenow == 'viewing' || $typenow == 'offer' || $typenow == 'sale')
        {
            add_filter('months_dropdown_results', '__return_empty_array');
        }
    }

	/**
	 * Disable the auto-save functionality for certain CPT's.
	 *
	 * @access public
	 * @return void
	 */
	public function disable_autosave(){
	    /*global $post;

	    if ( $post && get_post_type( $post->ID ) === 'enquiry' ) {
	        wp_dequeue_script( 'autosave' );
	    }*/
	}

    /**
     * Filters for post types
     */
    public function restrict_manage_posts() {
        global $typenow, $wp_query;

        switch ( $typenow ) {
            case 'property' :
                $this->property_filters();
                break;
            case 'contact' :
                $this->contact_filters();
                break;
            case 'enquiry' :
                $this->enquiry_filters();
                break;
            case 'appraisal' :
                $this->appraisal_filters();
                break;
            case 'viewing' :
                $this->viewing_filters();
                break;
            default :
                break;
        }
    }

    /**
     * Show a property filter box
     */
    public function property_filters() {
        global $wp_query;
        
        // Department filtering
        $output = '';
        
        $output .= $this->property_department_filter();
        $output .= $this->property_marketing_filter();
        $output .= $this->property_availability_filter();
        $output .= $this->property_location_filter();
        $output .= $this->property_office_filter();
        $output .= $this->property_negotiator_filter();

        echo apply_filters( 'propertyhive_property_filters', $output );
    }
    
    /**
     * Show a property department filter box
     */
    public function property_department_filter() {
        global $wp_query;

        $departments = ph_get_departments();

        $selected_department = isset( $_GET['_department'] ) && in_array( $_GET['_department'], array_keys($departments) ) ? $_GET['_department'] : '';
        
        // Department filtering
        $output  = '<select name="_department" id="dropdown_property_department">';
            
            $output .= '<option value="">' . __( 'All Departments', 'propertyhive' ) . '</option>';

            foreach ( $departments as $key => $value )
            {
                if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
                {
                    $output .= '<option value="' . $key . '"';
                    $output .= selected( $key, $selected_department, false );
                    $output .= '>' . $value . '</option>';
                }
            }

        $output .= '</select>';

        return $output;
    }

    /**
     * Show a property office filter box
     */
    public function property_office_filter() {
        global $wp_query, $post;
        
        // Department filtering
        $output  = '<select name="_office_id" id="dropdown_property_office_id">';
        
        $output .= '<option value="">' . __( 'All Offices', 'propertyhive' ) . '</option>';
        
        $args = array(
            'post_type' => 'office',
            'nopaging' => true,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        $office_query = new WP_Query($args);
        
        if ($office_query->have_posts())
        {
            while ($office_query->have_posts())
            {
                $office_query->the_post();
                
                $output .= '<option value="' . $post->ID . '"';
                if ( isset( $_GET['_office_id'] ) && ! empty( $_GET['_office_id'] ) )
                {
                    $output .= selected( $post->ID, (int)$_GET['_office_id'], false );
                }
                $output .= '>' . get_the_title() . '</option>';
            }
        }
        
        wp_reset_postdata();
        
        $output .= '</select>';

        return $output;
    }
    
    /**
     * Show a property negotiator filter box
     */
    public function property_negotiator_filter() {
        global $wp_query, $post;
        
        $selected = '';
        if ( isset( $_GET['_negotiator_id'] ) && ! empty( $_GET['_negotiator_id'] ) )
        {
            $selected = (int)$_GET['_negotiator_id'];
        }
        
        $args = array(
            'name' => '_negotiator_id', 
            'id' => 'dropdown_property_negotiator_id',
            'show_option_all' => __( 'All Negotiators', 'propertyhive' ),
            'selected' => $selected,
            'echo' => false,
            'role__not_in' => array('property_hive_contact') 
        );
        $output = wp_dropdown_users($args);

        return $output;
    }

    /**
     * Show a property location filter box
     */
    public function property_location_filter() {
        global $wp_query, $post;
        
        // Department filtering
        $output  = '<select name="_location_id" id="dropdown_property_location_id">';

        $options = array( );
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'location', $args );
        
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
                
                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                );
                $subterms = get_terms( 'location', $args );
                
                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = '- ' . $term->name;
                        
                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subsubterms = get_terms( 'location', $args );
                        
                        if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                        {
                            foreach ($subsubterms as $term)
                            {
                                $options[$term->term_id] = '- ' . $term->name;
                            }
                        }
                    }
                }
            }
        }
        
        $output .= '<option value="">' . __( 'All Locations', 'propertyhive' ) . '</option>';
        
        if ( !empty($options) )
        {
            foreach ( $options as $value => $label )
            {
                $output .= '<option value="' . $value . '"';
                if ( isset( $_GET['_location_id'] ) && ! empty( $_GET['_location_id'] ) )
                {
                    $output .= selected( $value, (int)$_GET['_location_id'], false );
                }
                $output .= '>' . $label . '</option>';
            }
        }
        
        $output .= '</select>';

        return $output;
    }

    /**
     * Show a property availability filter box
     */
    public function property_availability_filter() {
        global $wp_query, $post;
        
        // Availability filtering
        $output  = '<select name="_availability_id" id="dropdown_property_availability_id">';

        $options = array( );
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'availability', $args );
        
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
            }
        }
        
        $output .= '<option value="">' . __( 'All Availabilities', 'propertyhive' ) . '</option>';
        
        if ( !empty($options) )
        {
            foreach ( $options as $value => $label )
            {
                $output .= '<option value="' . $value . '"';
                if ( isset( $_GET['_availability_id'] ) && ! empty( $_GET['_availability_id'] ) )
                {
                    $output .= selected( $value, (int)$_GET['_availability_id'], false );
                }
                $output .= '>' . $label . '</option>';
            }
        }
        
        $output .= '</select>';

        return $output;
    }

    /**
     * Show a property marketing filter box
     */
    public function property_marketing_filter() {
        global $wp_query, $post;
        
        // Availability filtering
        $output  = '<select name="_marketing" id="dropdown_property_marketing">';

        $output .= '<option value="">' . __( 'All Marketing Statuses', 'propertyhive' ) . '</option>';

        $options = array(
            'on_market' => __( 'On Market Only', 'propertyhive' ),
            'off_market' => __( 'Not On Market Only', 'propertyhive' ),
            'featured' => __( 'Featured Only', 'propertyhive' ),
        );

        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'marketing_flag', $args );
        
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options['marketing_flag_' . $term->term_id] = __( 'Has Marketing Flag', 'propertyhive') . ' - ' . $term->name;
            }
        }

        $options = apply_filters( 'propertyhive_property_filter_marketing_options', $options );

        foreach ( $options as $key => $value )
        {
            $output .= '<option value="' . $key . '"';
            if ( isset( $_GET['_marketing'] ) && ! empty( $_GET['_marketing'] ) )
            {
                $output .= selected( $key, sanitize_text_field($_GET['_marketing']), false );
            }
            $output .= '>' . $value . '</option>';
        }

        $output .= '</select>';

        return $output;
    }
    
    /**
     * Show a contact filter box
     */
    public function contact_filters() {
        global $wp_query;

        $selected_contact_type = isset( $_GET['_contact_type'] ) && in_array( $_GET['_contact_type'], array( 'owner', 'potentialowner', 'applicant', 'hotapplicant', 'thirdparty' ) ) ? $_GET['_contact_type'] : '';
        
        // Type filtering        
        $options = array();

        // Owners
        $option = '<option value="owner"';
        $option .= selected( 'owner', $selected_contact_type, false );
        $option .= '>' . __( 'Owners and Landlords', 'propertyhive' ) . '</option>';

        $options[] = $option;

        // Potential Owners
        $option = '<option value="potentialowner"';
        $option .= selected( 'potentialowner', $selected_contact_type, false );
        $option .= '>' . __( 'Potential Owners and Landlords', 'propertyhive' ) . '</option>';

        $options[] = $option;

        // Applicants
        $option = '<option value="applicant"';
        $option .= selected( 'applicant', $selected_contact_type, false );
        $option .= '>' . __( 'Applicants', 'propertyhive' ) . '</option>';

        $options[] = $option;

        // Hot Applicants
        $option = '<option value="hotapplicant"';
        $option .= selected( 'hotapplicant', $selected_contact_type, false );
        $option .= '>- ' . __( 'Hot Applicants', 'propertyhive' ) . '</option>';

        $options[] = $option;

        // Third Parties
        $option = '<option value="thirdparty"';
        $option .= selected( 'thirdparty', $selected_contact_type, false );
        $option .= '>' . __( 'Third Party Contacts', 'propertyhive' ) . '</option>';

        $options[] = $option;

        $options = apply_filters( 'propertyhive_contact_filter_options', $options );

        $output = '';
        if (count($options) > 1)
        {
            $output  = '<select name="_contact_type" id="dropdown_contact_type">';
            
                $output .= '<option value="">' . __( 'Show all contact types', 'propertyhive' ) . '</option>';

                $output .= implode("", $options);
            
            $output .= '</select>';
        }

        echo $output;
    }
    
    /**
     * Show an enquiry filter box
     */
    public function enquiry_filters() {
        global $wp_query;
        
        // Department filtering
        $output = '';
        
        $output .= $this->enquiry_status_filter();
        $output .= $this->enquiry_source_filter();

        echo apply_filters( 'propertyhive_enquiry_filters', $output );
    }
    
    /**
     * Show an enquiry status filter box
     */
    public function enquiry_status_filter() {
        global $wp_query;

        $selected_status = isset( $_GET['_status'] ) && in_array( $_GET['_status'], array( 'open', 'closed' ) ) ? $_GET['_status'] : '';
        
        // Status filtering
        $output  = '<select name="_status" id="dropdown_enquiry_status">';
            
            $output .= '<option value="open"';
            $output .= selected( 'open', $selected_status, false );
            $output .= '>' . __( 'Open', 'propertyhive' ) . '</option>';

            $output .= '<option value="closed"';
            $output .= selected( 'closed', $selected_status, false );
            $output .= '>' . __( 'Closed', 'propertyhive' ) . '</option>';
            
        $output .= '</select>';

        return $output;
    }
    
    /**
     * Show an enquiry source filter box
     */
    public function enquiry_source_filter() {
        global $wp_query;

        $sources = array(
            'office' => __( 'Office', 'propertyhive' ),
            'website' => __( 'Website', 'propertyhive' )
        );

        $sources = apply_filters( 'propertyhive_enquiry_sources', $sources );
        
        // Status filtering
        $output  = '<select name="_source" id="dropdown_enquiry_source">';
            
            $output .= '<option value="">' . __( 'Show all sources', 'propertyhive' ) . '</option>';
            
            foreach ( $sources as $key => $value )
            {
                $output .= '<option value="' . $key . '"';
                if ( isset( $_GET['_source'] ) && ! empty( $_GET['_source'] ) )
                {
                    $output .= selected( $key, sanitize_text_field($_GET['_source']), false );
                }
                $output .= '>' . __( $value, 'propertyhive' ) . '</option>';
            }
            
        $output .= '</select>';

        return $output;
    }

    /**
     * Show am appraisal filter box
     */
    public function appraisal_filters() {
        global $wp_query;
        
        $output = '';
        
        $output .= $this->appraisal_status_filter();
        $output .= $this->appraisal_attending_negotiator_filter();

        echo apply_filters( 'propertyhive_appraisal_filters', $output );
    }

    /**
     * Show an appraisal status filter box
     */
    public function appraisal_status_filter() {
        global $wp_query;

        $selected_status = isset( $_GET['_status'] ) && in_array( $_GET['_status'], array( 'pending', 'carried_out', 'won', 'lost', 'instructed', 'cancelled' ) ) ? $_GET['_status'] : '';
        
        // Status filtering
        $output  = '<select name="_status" id="dropdown_appraisal_status">';
            
            $output .= '<option value="">All Statuses</option>';

            $output .= '<option value="pending"';
            $output .= selected( 'pending', $selected_status, false );
            $output .= '>' . __( 'Pending', 'propertyhive' ) . '</option>';

            $output .= '<option value="carried_out"';
            $output .= selected( 'carried_out', $selected_status, false );
            $output .= '>' . __( 'Carried Out', 'propertyhive' ) . '</option>';

            $output .= '<option value="won"';
            $output .= selected( 'won', $selected_status, false );
            $output .= '>- ' . __( 'Won', 'propertyhive' ) . '</option>';

            $output .= '<option value="lost"';
            $output .= selected( 'lost', $selected_status, false );
            $output .= '>- ' . __( 'Lost', 'propertyhive' ) . '</option>';

            $output .= '<option value="instructed"';
            $output .= selected( 'instructed', $selected_status, false );
            $output .= '>- ' . __( 'Instructed', 'propertyhive' ) . '</option>';

            $output .= '<option value="cancelled"';
            $output .= selected( 'cancelled', $selected_status, false );
            $output .= '>' . __( 'Cancelled', 'propertyhive' ) . '</option>';
            
        $output .= '</select>';

        return $output;
    }

    /**
     * Show an appraisal attending negotiator filter box
     */
    public function appraisal_attending_negotiator_filter() {
        global $wp_query;

        $selected_negotiator_id = isset( $_GET['_negotiator_id']) ? (int)$_GET['_negotiator_id'] : '';
        
        // Status filtering
        $output = '<select name="_negotiator_id" id="dropdown_appraisal_negotiator_id">';
            
            $output .= '<option value="">Attending Negotiator</option>';
            $output .= '<option value="">All Negotiators</option>';

            $args = array(
                'number' => 9999,
                'orderby' => 'display_name',
                'role__not_in' => array('property_hive_contact') 
            );
            $user_query = new WP_User_Query( $args );

            if ( ! empty( $user_query->results ) ) 
            {
                foreach ( $user_query->results as $user ) 
                {
                    $output .= '<option value="' . $user->ID . '"';
                    if ( $user->ID == $selected_negotiator_id )
                    {
                        $output .= ' selected';
                    }
                    $output .= '>' . $user->display_name . '</option>';
                }
            }
            
        $output .= '</select>';

        return $output;
    }

    /**
     * Show a viewing filter box
     */
    public function viewing_filters() {
        global $wp_query;
        
        // Department filtering
        $output = '';
        
        $output .= $this->viewing_status_filter();
        $output .= $this->viewing_attending_negotiator_filter();

        echo apply_filters( 'propertyhive_viewing_filters', $output );
    }

    /**
     * Show a viewing status filter box
     */
    public function viewing_status_filter() {
        global $wp_query;

        $selected_status = isset( $_GET['_status'] ) && in_array( $_GET['_status'], array( 'pending', 'confirmed', 'unconfirmed', 'carried_out', 'feedback_passed_on', 'feedback_not_passed_on', 'cancelled' ) ) ? $_GET['_status'] : '';
        
        // Status filtering
        $output  = '<select name="_status" id="dropdown_viewing_status">';
            
            $output .= '<option value="">All Statuses</option>';

            $output .= '<option value="pending"';
            $output .= selected( 'pending', $selected_status, false );
            $output .= '>' . __( 'Pending', 'propertyhive' ) . '</option>';

            $output .= '<option value="confirmed"';
            $output .= selected( 'confirmed', $selected_status, false );
            $output .= '>- ' . __( 'Confirmed', 'propertyhive' ) . '</option>';

            $output .= '<option value="unconfirmed"';
            $output .= selected( 'unconfirmed', $selected_status, false );
            $output .= '>- ' . __( 'Awaiting Confirmation', 'propertyhive' ) . '</option>';

            $output .= '<option value="carried_out"';
            $output .= selected( 'carried_out', $selected_status, false );
            $output .= '>' . __( 'Carried Out', 'propertyhive' ) . '</option>';

            $output .= '<option value="feedback_passed_on"';
            $output .= selected( 'feedback_passed_on', $selected_status, false );
            $output .= '>- ' . __( 'Feedback Passed On', 'propertyhive' ) . '</option>';

            $output .= '<option value="feedback_not_passed_on"';
            $output .= selected( 'feedback_not_passed_on', $selected_status, false );
            $output .= '>- ' . __( 'Feedback Not Passed On', 'propertyhive' ) . '</option>';

            $output .= '<option value="cancelled"';
            $output .= selected( 'cancelled', $selected_status, false );
            $output .= '>' . __( 'Cancelled', 'propertyhive' ) . '</option>';
            
        $output .= '</select>';

        return $output;
    }

    /**
     * Show a viewing attending negotiator filter box
     */
    public function viewing_attending_negotiator_filter() {
        global $wp_query;

        $selected_negotiator_id = isset( $_GET['_negotiator_id']) ? (int)$_GET['_negotiator_id'] : '';
        
        // Status filtering
        $output = '<select name="_negotiator_id" id="dropdown_viewing_negotiator_id">';
            
            $output .= '<option value="">Attending Negotiator</option>';
            $output .= '<option value="">All Negotiators</option>';

            $args = array(
                'number' => 9999,
                'orderby' => 'display_name',
                'role__not_in' => array('property_hive_contact') 
            );
            $user_query = new WP_User_Query( $args );

            if ( ! empty( $user_query->results ) ) 
            {
                foreach ( $user_query->results as $user ) 
                {
                    $output .= '<option value="' . $user->ID . '"';
                    if ( $user->ID == $selected_negotiator_id )
                    {
                        $output .= ' selected';
                    }
                    $output .= '>' . $user->display_name . '</option>';
                }
            }
            
        $output .= '</select>';

        return $output;
    }
    
    /**
     * Filters and sorting handler
     * @param  array $vars
     * @return array
     */
    public function request_query( $vars ) {
        global $typenow, $wp_query;

        if ( !isset($vars['meta_query']) ) { $vars['meta_query'] = array(); }
        if ( !isset($vars['tax_query']) ) { $vars['tax_query'] = array(); }

        if ( 'property' === $typenow ) 
        {
            if ( ! empty( $_GET['_department'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_department',
                    'value' => sanitize_text_field( $_GET['_department'] ),
                );
            }
            if ( ! empty( $_GET['_office_id'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_office_id',
                    'value' => (int)$_GET['_office_id'],
                );
            }
            if ( ! empty( $_GET['_negotiator_id'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_negotiator_id',
                    'value' => (int)$_GET['_negotiator_id'],
                );
            }
            if ( ! empty( $_GET['_location_id'] ) ) {
                $vars['tax_query'][] = array(
                    'taxonomy'  => 'location',
                    'terms' => ( (is_array($_GET['_location_id'])) ? (int)$_GET['_location_id'] : array( (int)$_GET['_location_id'] ) )
                );
            }
            if ( ! empty( $_GET['_availability_id'] ) ) {
                $vars['tax_query'][] = array(
                    'taxonomy'  => 'availability',
                    'terms' => ( (is_array($_GET['_availability_id'])) ? (int)$_GET['_availability_id'] : array( (int)$_GET['_availability_id'] ) )
                );
            }
            if ( ! empty( $_GET['_marketing'] ) && $_GET['_marketing'] == 'on_market' ) {
                $vars['meta_query'][] = array(
                    'key' => '_on_market',
                    'value' => 'yes',
                );
            }
            if ( ! empty( $_GET['_marketing'] ) && $_GET['_marketing'] == 'off_market' ) {
                $vars['meta_query'][] = array(
                    'key' => '_on_market',
                    'value' => 'yes',
                    'compare' => '!=',
                );
            }
            if ( ! empty( $_GET['_marketing'] ) && $_GET['_marketing'] == 'featured' ) {
                $vars['meta_query'][] = array(
                    'key' => '_featured',
                    'value' => 'yes',
                );
            }
            if ( ! empty( $_GET['_marketing'] ) && substr($_GET['_marketing'], 0, 15) == 'marketing_flag_' ) {
                $marketing_flag_id = sanitize_text_field( str_replace("marketing_flag_", "", $_GET['_marketing']) );
                $vars['tax_query'][] = array(
                    'taxonomy'  => 'marketing_flag',
                    'terms' => ( (is_array($marketing_flag_id)) ? $marketing_flag_id : array( $marketing_flag_id ) )
                );
            }
        }
        elseif ( 'contact' === $typenow ) 
        {
            if ( ! empty( $_GET['_contact_type'] ) ) 
            {
                $contact_type = ph_clean($_GET['_contact_type']);
                if ( $contact_type == 'hotapplicant' )
                {
                    $contact_type = 'applicant';

                    $vars['meta_query'][] = array(
                        'key' => '_hot_applicant',
                        'value' => 'yes',
                    );
                }
                $vars['meta_query'][] = array(
                    'key' => '_contact_types',
                    'value' => $contact_type,
                    'compare' => 'LIKE'
                );
            }
        }
        elseif ( 'enquiry' === $typenow ) 
        {
            if ( ! empty( $_GET['_status'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_status',
                    'value' => sanitize_text_field( $_GET['_status'] ),
                );
            }
            if ( ! empty( $_GET['_source'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_source',
                    'value' => sanitize_text_field( $_GET['_source'] ),
                );
            }
        }
        elseif ( 'appraisal' === $typenow ) 
        {
            if ( ! empty( $_GET['_status'] ) ) {
                switch ( sanitize_text_field( $_GET['_status'] ) )
                {
                    case "confirmed":
                    {
                        $vars['meta_query'][] = array(
                            'key' => '_status',
                            'value' => 'pending',
                        );
                        $vars['meta_query'][] = array(
                            'key' => '_all_confirmed',
                            'value' => 'yes',
                        );
                        break;
                    }
                    case "unconfirmed":
                    {
                        $vars['meta_query'][] = array(
                            'key' => '_status',
                            'value' => 'pending',
                        );
                        $vars['meta_query'][] = array(
                            'key' => '_all_confirmed',
                            'value' => '',
                        );
                        break;
                    }
                    default:
                    {
                        $vars['meta_query'][] = array(
                            'key' => '_status',
                            'value' => sanitize_text_field( $_GET['_status'] ),
                        );
                    }
                }
            }
            if ( ! empty( $_GET['_negotiator_id'] ) ) 
            {
                $vars['meta_query'][] = array(
                    'key' => '_negotiator_id',
                    'value' => (int)$_GET['_negotiator_id'],
                );
            }
        }
        elseif ( 'viewing' === $typenow ) 
        {
            if ( ! empty( $_GET['_status'] ) ) {
                switch ( sanitize_text_field( $_GET['_status'] ) )
                {
                    case "confirmed":
                    {
                        $vars['meta_query'][] = array(
                            'key' => '_status',
                            'value' => 'pending',
                        );
                        $vars['meta_query'][] = array(
                            'key' => '_all_confirmed',
                            'value' => 'yes',
                        );
                        break;
                    }
                    case "unconfirmed":
                    {
                        $vars['meta_query'][] = array(
                            'key' => '_status',
                            'value' => 'pending',
                        );
                        $vars['meta_query'][] = array(
                            'key' => '_all_confirmed',
                            'value' => '',
                        );
                        break;
                    }
                    case "feedback_passed_on":
                    {
                        $vars['meta_query'][] = array(
                            'key' => '_status',
                            'value' => 'carried_out',
                        );
                        $vars['meta_query'][] = array(
                            'key' => '_feedback_status',
                            'value' => array('interested', 'not_interested'),
                            'compare' => 'IN'
                        );
                        $vars['meta_query'][] = array(
                            'key' => '_feedback_passed_on',
                            'value' => 'yes',
                        );
                        break;
                    }
                    case "feedback_not_passed_on":
                    {
                         $vars['meta_query'][] = array(
                            'key' => '_status',
                            'value' => 'carried_out',
                        );
                        $vars['meta_query'][] = array(
                            'key' => '_feedback_status',
                            'value' => array('interested', 'not_interested'),
                            'compare' => 'IN'
                        );
                        $vars['meta_query'][] = array(
                            'key' => '_feedback_passed_on',
                            'value' => '',
                        );
                        break;
                    }
                    default:
                    {
                        $vars['meta_query'][] = array(
                            'key' => '_status',
                            'value' => sanitize_text_field( $_GET['_status'] ),
                        );
                    }
                }
            }
            if ( ! empty( $_GET['_negotiator_id'] ) ) 
            {
                $vars['meta_query'][] = array(
                    'key' => '_negotiator_id',
                    'value' => (int)$_GET['_negotiator_id'],
                );
            }
        }

        $vars = apply_filters( 'propertyhive_property_filter_query', $vars, $typenow );

        return $vars;
    }

	/**
	 * Removes variations etc belonging to a deleted post, and clears transients
	 *
	 * @access public
	 * @param mixed $id ID of post being deleted
	 * @return void
	 */
	public function delete_post( $id ) {
		/*global $propertyhive, $wpdb;

		if ( ! current_user_can( 'delete_posts' ) )
			return;

		if ( $id > 0 ) {

			$post_type = get_post_type( $id );

			switch( $post_type ) {
				case 'property' :
					ph_delete_property_transients();
				break;
                case 'contact' :
                    ph_delete_contact_transients();
                break;
                case 'enquiry' :
                    ph_delete_enquiry_transients();
                break;
			}
		}*/
	}

	/**
	 * propertyhive_trash_post function.
	 *
	 * @access public
	 * @param mixed $id
	 * @return void
	 */
	public function trash_post( $id ) {
		/*if ( $id > 0 ) {

			$post_type = get_post_type( $id );

			
		}*/
	}

	/**
	 * propertyhive_untrash_post function.
	 *
	 * @access public
	 * @param mixed $id
	 * @return void
	 */
	public function untrash_post( $id ) {
		/*if ( $id > 0 ) {

			$post_type = get_post_type( $id );

		}*/
	}
}

endif;

return new PH_Admin_Post_Types();