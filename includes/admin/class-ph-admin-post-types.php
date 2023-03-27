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
		add_action( 'pre_get_posts', array( $this, 'refresh_property_office_filtering' ));
        add_action( 'admin_print_scripts', array( $this, 'remove_month_filter' ) );
		add_action( 'admin_print_scripts', array( $this, 'disable_autosave' ) );

        // Filters
        add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
        add_filter( 'request', array( $this, 'request_query' ) );
        add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
        add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );

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
        include( 'post-types/class-ph-admin-cpt-tenancy.php' );
        include( 'post-types/class-ph-admin-cpt-key-date.php' );
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
        
        if ( in_array($typenow, array('property', 'contact', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy', 'key_date')) )
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
            case 'offer' :
                $this->offer_filters();
                break;
            case 'sale' :
                $this->sale_filters();
                break;
            case 'tenancy' :
                $this->tenancy_filters();
                break;
            case 'key_date' :
                $this->key_date_filters();
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
        $output .= $this->negotiator_filter();

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
     * Show a negotiator filter box
     */
    public function negotiator_filter() {

	    return wp_dropdown_users(array(
            'name' => '_negotiator_id', 
            'id' => 'dropdown_property_negotiator_id',
            'show_option_all' => __( 'All Negotiators', 'propertyhive' ),
            'selected' => empty( $_GET['_negotiator_id'] ) ? '' : (int) $_GET['_negotiator_id'],
            'echo' => false,
            'role__not_in' => apply_filters( 'property_negotiator_exclude_roles', array('property_hive_contact', 'subscriber') )
        ));
    }

	/**
	 * Show a date range selector
	 */
	public function date_range_filter() {

		$date_range_label = empty( $_GET['_date_range_label'] ) ? 'Any Time' : $_GET['_date_range_label'];

		// The date picker doesn't have a concept of 'Any Time', so valid dates must be used
		// I've used the last and first date of the month (reversed) as it's a range that is not selectable, but is within the current month
		// If I used an already labelled date range (e.g. 'Today'), it would show as 'Today' when selected
		// If I use a nearby date range (e.g. 'Yesterday'), if someone actually selected that range it would show as 'Any Time'
		// If I use a unlikely date range (e.g. 01-01-1970 - 31-12-2070), the custom date range picker would open showing Jan 1970.
		$date_range_from = empty( $_GET['_date_range_from'] ) ? date('Y-m-d', strtotime('last day of this month')) : $_GET['_date_range_from'];
		$date_range_to = empty( $_GET['_date_range_to'] ) ? date('Y-m-d', strtotime('first day of this month')) : $_GET['_date_range_to'];

		return "
            <select name='_date_range_label' id='date_range' style='max-width:25rem;'>
                <option selected>$date_range_label</option>
            <select/>
            <input type='hidden' name='_date_range_from' id='date_range_from' value='$date_range_from'>
            <input type='hidden' name='_date_range_to' id='date_range_to' value='$date_range_to'>
        ";
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
        $output .= $this->enquiry_office_filter();
        $output .= $this->enquiry_negotiator_filter();

        echo apply_filters( 'propertyhive_enquiry_filters', $output );
    }
    
    /**
     * Show an enquiry status filter box
     */
    public function enquiry_status_filter() {
        global $wp_query;

        $selected_status = isset( $_GET['_status'] ) && in_array( $_GET['_status'], array( 'all', 'open', 'closed' ) ) ? $_GET['_status'] : '';

        // Status filtering
        $output  = '<select name="_status" id="dropdown_enquiry_status">
            <option value="all"' . selected( 'all', $selected_status, false ) . '>All</option>';

            $enquiry_statuses = ph_get_enquiry_statuses();

            foreach ( $enquiry_statuses as $status => $display_status )
            {
                $output .= '<option value="' . $status . '"';
                if ( $status == $selected_status || ( $status == 'open' && ( !isset($_GET['_status']) || empty($_GET['_status']) ) ) )
                {
                    $output .= ' selected';
                }
                $output .= selected( $status, $selected_status, false );
                $output .= '>' . $display_status . '</option>';
            }

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

        asort($sources);
        
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
     * Show an enquiry office filter box
     */
    public function enquiry_office_filter() {
        global $wp_query, $post;
        
        // Department filtering
        $output  = '<select name="_office_id" id="dropdown_enquiry_office_id">';
        
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
     * Show an enquiry negotiator filter box
     */
    public function enquiry_negotiator_filter() {
        return wp_dropdown_users(array(
            'name' => '_negotiator_id', 
            'id' => 'dropdown_enquiry_negotiator_id',
            'show_option_all' => __( 'All Negotiators', 'propertyhive' ),
            'selected' => empty( $_GET['_negotiator_id'] ) ? '' : (int) $_GET['_negotiator_id'],
            'echo' => false,
            'role__not_in' => apply_filters( 'property_negotiator_exclude_roles', array('property_hive_contact', 'subscriber') )
        ));
    }

    /**
     * Show am appraisal filter box
     */
    public function appraisal_filters() {
        global $wp_query;
        
        $output = '';
        
        $output .= $this->appraisal_status_filter();
        $output .= $this->negotiator_filter();
        $output .= $this->date_range_filter();

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
            
            $output .= '<option value="">' . __( 'All Statuses', 'propertyhive' ) . '</option>';

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
     * Show a viewing filter box
     */
    public function viewing_filters() {
        global $wp_query;

        // Department filtering
        $output = '';

        $output .= $this->viewing_status_filter();
        $output .= $this->property_office_filter();
        $output .= $this->negotiator_filter();
        $output .= $this->date_range_filter();

        echo apply_filters( 'propertyhive_viewing_filters', $output );
    }

    /**
     * Show a viewing status filter box
     */
    public function viewing_status_filter() {
        global $wp_query;

        $selected_status = isset( $_GET['_status'] ) && in_array( $_GET['_status'], array( 'pending', 'confirmed', 'unconfirmed', 'carried_out', 'awaiting_feedback', 'feedback_passed_on', 'feedback_not_passed_on', 'cancelled', 'no_show' ) ) ? $_GET['_status'] : '';
        
        // Status filtering
        $output  = '<select name="_status" id="dropdown_viewing_status">';

            $output .= '<option value="">' . __( 'All Statuses', 'propertyhive' ) . '</option>';

            $viewing_statuses = ph_get_viewing_statuses();

            foreach ( $viewing_statuses as $status => $display_status )
            {
                $output .= '<option value="' . $status . '"';
                $output .= selected( $status, $selected_status, false );
                $output .= '>' . $display_status . '</option>';
            }

        $output .= '</select>';

        return $output;
    }


    public function refresh_property_office_filtering( $query ) {
        remove_filter('posts_join', array( $this, 'filter_by_property_office')  );

        if ( ! empty( $_GET['_office_id'] ) && in_array( $query->query['post_type'], array(
	        'viewing',
	        'offer',
	        'sale',
        ))) {
            add_filter('posts_join', array( $this, 'filter_by_property_office' ) );
        };
    }


    public function filter_by_property_office($query) {
        global $wpdb;

        return $query . '
           INNER JOIN ' . $wpdb->postmeta . ' AS property_meta ON property_meta.post_id = ' . $wpdb->posts . '.ID AND property_meta.meta_key = "_property_id"
           INNER JOIN ' . $wpdb->postmeta . ' AS property_office_meta ON property_office_meta.post_id = property_meta.meta_value AND property_office_meta.meta_key = "_office_id"
             AND property_office_meta.meta_value = ' . (int) $_GET['_office_id'];
    }

    /**
     * Show an offer filter box
     */
    public function offer_filters() {
        global $wp_query;
        
        $output = '';
        
        $output .= $this->offer_status_filter();
        $output .= $this->property_office_filter();
        $output .= $this->date_range_filter();

        echo apply_filters( 'propertyhive_offer_filters', $output );
    }

    /**
     * Show an offer status filter box
     */
    public function offer_status_filter() {
        global $wp_query;

        $selected_status = isset( $_GET['_status'] ) && in_array( $_GET['_status'], array( 'pending', 'accepted', 'declined' ) ) ? $_GET['_status'] : '';
        
        // Status filtering
        $output  = '<select name="_status" id="dropdown_offer_status">';

            $output .= '<option value="">' . __( 'All Statuses', 'propertyhive' ) . '</option>';

            $offer_statuses = ph_get_offer_statuses();

            foreach ( $offer_statuses as $status => $display_status )
            {
                $output .= '<option value="' . $status . '"';
                $output .= selected( $status, $selected_status, false );
                $output .= '>' . $display_status . '</option>';
            }

        $output .= '</select>';

        return $output;
    }

    /**
     * Show an sale filter box
     */
    public function sale_filters() {
        global $wp_query;
        
        $output = '';
        
        $output .= $this->sale_status_filter();
        $output .= $this->property_office_filter();
        $output .= $this->date_range_filter();

        echo apply_filters( 'propertyhive_sale_filters', $output );
    }

    /**
     * Show an sale status filter box
     */
    public function sale_status_filter() {
        global $wp_query;

        $selected_status = isset( $_GET['_status'] ) && in_array( $_GET['_status'], array( 'current', 'exchanged', 'completed', 'fallen_through' ) ) ? $_GET['_status'] : '';
        
        // Status filtering
        $output  = '<select name="_status" id="dropdown_sale_status">';
            
            $output .= '<option value="">' . __( 'All Statuses', 'propertyhive' ) . '</option>';

            $sale_statuses = ph_get_sale_statuses();

            foreach ( $sale_statuses as $status => $display_status )
            {
                $output .= '<option value="' . $status . '"';
                $output .= selected( $status, $selected_status, false );
                $output .= '>' . $display_status . '</option>';
            }
            
        $output .= '</select>';

        return $output;
    }

    /**
     * Show an tenancy filter box
     */
    public function tenancy_filters() {
        global $wp_query;

        $output = '';

        $output .= $this->tenancy_status_filter();
        $output .= $this->tenancy_management_type_filter();

        echo apply_filters( 'propertyhive_tenancy_filters', $output );
    }

    /**
     * Show an tenancy status filter box
     */
    public function tenancy_status_filter() {
        global $wp_query;

        $selected_status = isset( $_GET['_status'] ) && in_array( $_GET['_status'], array( 'pending', 'current', 'finished') ) ? $_GET['_status'] : '';

        // Status filtering
        $output  = '<select name="_status" id="dropdown_tenancy_status">';

            $output .= '<option value="">' . __( 'All Statuses', 'propertyhive' ) . '</option>';

            $output .= '<option value="pending"';
            $output .= selected( 'pending', $selected_status, false );
            $output .= '>' . __( 'Pending', 'propertyhive' ) . '</option>';

            $output .= '<option value="current"';
            $output .= selected( 'current', $selected_status, false );
            $output .= '> ' . __( 'Current', 'propertyhive' ) . '</option>';

            $output .= '<option value="finished"';
            $output .= selected( 'finished', $selected_status, false );
            $output .= '> ' . __( 'Finished', 'propertyhive' ) . '</option>';

        $output .= '</select>';

        return $output;
    }

    /**
     * Show an tenancy management type filter box
     */
    public function tenancy_management_type_filter() {
        global $wp_query;

        $management_types = apply_filters( 'propertyhive_tenancy_management_types', array(
            'let_only' => 'Let Only',
            'fully_managed' => 'Fully Managed'
        ) );

        $selected_management_type = isset( $_GET['_management_type'] ) && in_array( $_GET['_management_type'], array_keys($management_types) ) ? $_GET['_management_type'] : '';

        // Status filtering
        $output  = '<select name="_management_type" id="dropdown_tenancy_management_type">';

            $output .= '<option value="">' . __( 'All Management Types', 'propertyhive' ) . '</option>';

            foreach ( $management_types as $key => $value )
            {
                $output .= '<option value="' . $key . '"';
                $output .= selected( $key, $selected_management_type, false );
                $output .= '>' . __( $value, 'propertyhive' ) . '</option>';
            }

        $output .= '</select>';

        return $output;
    }

	public function key_date_filters() {
		global $wp_query;

		$output = '';

		$output .= $this->key_date_type_filter();
		$output .= $this->key_date_status_filter();

		echo apply_filters( 'propertyhive_tenancy_filters', $output );
	}

	public function key_date_type_filter() {

		$selected_value = ! empty($_GET['_key_date_type_id']) ? (int) $_GET['_key_date_type_id'] : '';
		$terms = get_terms( 'management_key_date_type', array(
			'hide_empty' => false,
			'parent' => 0
		) );

		$output  = '<select name="_key_date_type_id">';
		$output .= '<option value="">' . __( 'All Types', 'propertyhive' ) . '</option>';

		if ( !empty( $terms ) && !is_wp_error( $terms ) )
		{
			foreach ($terms as $term)
			{
				$output .= '<option value="' . $term->term_id . '"';
				$output .= selected($term->term_id, $selected_value, false );
				$output .= '>' . $term->name . '</option>';
			}
		}

		$output .= '</select>';

		return $output;
	}


	public function key_date_status_filter() {

		$selected_status = isset( $_GET['status'] ) && in_array( $_GET['status'], array( 'upcoming_and_overdue', 'overdue', 'booked', 'complete', 'pending') ) ? $_GET['status'] : '';

		$output  = '<select name="status" id="dropdown_key_date_status">';

		$output .= '<option value="">' . __( 'All Statuses', 'propertyhive' ) . '</option>';

		$output .= '<option value="upcoming_and_overdue"';
		$output .= selected( 'upcoming_and_overdue', $selected_status, false );
		$output .= '>' . __( 'Upcoming & Overdue', 'propertyhive' ) . '</option>';

        $output .= '<option value="overdue"';
        $output .= selected( 'overdue', $selected_status, false );
        $output .= '>' . __( 'Overdue', 'propertyhive' ) . '</option>';

		$output .= '<option value="booked"';
		$output .= selected( 'booked', $selected_status, false );
		$output .= '> ' . __( 'Booked', 'propertyhive' ) . '</option>';

		$output .= '<option value="complete"';
		$output .= selected( 'complete', $selected_status, false );
		$output .= '> ' . __( 'Complete', 'propertyhive' ) . '</option>';

		$output .= '<option value="pending"';
		$output .= selected( 'pending', $selected_status, false );
		$output .= '> ' . __( 'Pending', 'propertyhive' ) . '</option>';

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
            if ( ! empty( $_GET['_status'] ) && ph_clean($_GET['_status']) != 'all' ) {

                $vars['meta_query'][] = array(
                    'key' => '_status',
                    'value' => sanitize_text_field( $_GET['_status'] ),
                );
            }
            else
            {
                if ( empty( $_GET['_status'] ) )
                {
                    $vars['meta_query'][] = array(
                        'key' => '_status',
                        'value' => 'open',
                    );
                }
            }
            if ( ! empty( $_GET['_source'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_source',
                    'value' => sanitize_text_field( $_GET['_source'] ),
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

            $vars = $this->filter_by_date_range($vars);
        }
        elseif ( 'viewing' === $typenow ) 
        {
            if ( ! empty( $_GET['_status'] ) ) {

                $vars['meta_query'] = add_viewing_status_meta_query( $vars['meta_query'], sanitize_text_field( $_GET['_status'] ) );

            }
            if ( ! empty( $_GET['_negotiator_id'] ) ) 
            {
                $vars['meta_query'][] = array(
                    'key' => '_negotiator_id',
                    'value' => (int)$_GET['_negotiator_id'],
                );
            }

            $vars = $this->filter_by_date_range($vars);
        }
        elseif ( 'offer' === $typenow ) 
        {
            if ( ! empty( $_GET['_status'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_status',
                    'value' => sanitize_text_field( $_GET['_status'] ),
                );
            }

            $vars = $this->filter_by_date_range($vars, '_offer_date_time');
        }
        elseif ( 'sale' === $typenow ) 
        {
            if ( ! empty( $_GET['_status'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_status',
                    'value' => sanitize_text_field( $_GET['_status'] ),
                );
            }

            $vars = $this->filter_by_date_range($vars, '_sale_date_time');
        }
        elseif ( 'tenancy' === $typenow )
        {
            if ( ! empty( $_GET['_status'] ) )
            {
                switch ( $_GET['_status'] )
                {
                    case 'pending' :
                        $vars['meta_query'][] = array(
                            'key' => '_start_date',
                            'value' => date('Y-m-d'),
                            'type'  => 'date',
                            'compare' => '>',
                        );
                        break;

                    case 'current' :
                        $vars['meta_query'][] = array(
                            'relation' => 'OR',
                            array(
                                array(
                                    'key' => '_start_date',
                                    'value' => date('Y-m-d'),
                                    'type'  => 'date',
                                    'compare' => '<=',
                                ),
                                array(
                                    'key' => '_end_date',
                                    'value' => date('Y-m-d'),
                                    'type'  => 'date',
                                    'compare' => '>=',
                                )
                            ),
                            array(
                                array(
                                    'key' => '_start_date',
                                    'value' => date('Y-m-d'),
                                    'type'  => 'date',
                                    'compare' => '<=',
                                ),
                                array(
                                    'key' => '_end_date',
                                    'value' => '',
                                    'compare' => '=',
                                )
                            )
                        );
                        break;

                    case 'finished':
                        $vars['meta_query'][] = array(
                            'key' => '_end_date',
                            'value' => date('Y-m-d'),
                            'type'  => 'date',
                            'compare' => '<',
                        );
                        break;
                }
            }

            if ( ! empty( $_GET['_management_type'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_management_type',
                    'value' => sanitize_text_field( $_GET['_management_type'] ),
                );
            }
        }
        elseif ( 'key_date' === $typenow )
        {
            if ( ! empty( $_GET['status'] ) ) {

                $value = sanitize_text_field( $_GET['status'] );

                switch ($value) {
                    case 'booked':
                    case 'complete':
                        $vars['meta_query'][] = array(
                            'key' => '_key_date_status',
                            'value' => $value,
                        );
                        break;
                    case 'pending':
                        $vars['meta_query'][] = array(
                            'key' => '_key_date_status',
                            'value' => 'pending',
                        );
                        break;
                    case 'overdue':
                        $vars['meta_query'][] = array(
                            'key' => '_key_date_status',
                            'value' => array('pending', 'booked'),
                            'compare' => 'IN'
                        );
                        $vars['meta_query'][] = array(
                            'key' => '_date_due',
                            'value' => date("Y-m-d"),
                            'type' => 'date',
                            'compare' => '<',
                        );
                        break;
                    case 'upcoming_and_overdue':
                        $vars['meta_query'][] = array(
                            'key' => '_key_date_status',
                            'value' => array('pending', 'booked'),
                            'compare' => 'IN'
                        );
                        $upcoming_threshold = new DateTime('+ ' . apply_filters( 'propertyhive_key_date_upcoming_days', 7 ) . ' DAYS');
                        $vars['meta_query'][] = array(
                            'key' => '_date_due',
                            'value' => $upcoming_threshold->format('Y-m-d'),
                            'type' => 'date',
                            'compare' => '<=',
                        );
                        break;
                }
            }

            if ( !empty( $_GET['_key_date_type_id'] ) )
            {
                $vars['meta_query'][] = array(
                    'key' => '_key_date_type_id',
                    'value' => (int)$_GET['_key_date_type_id'],
                );
            }
        }

        $vars = apply_filters( 'propertyhive_property_filter_query', $vars, $typenow );

        return $vars;
    }

    private function filter_by_date_range($vars, $meta_key = '_start_date_time')
    {
	    if (
		    ! empty( $_GET['_date_range_label'] )
		    && ! empty( $_GET['_date_range_from'] )
		    && ! empty( $_GET['_date_range_to'] )
		    && $_GET['_date_range_label'] !== 'Any Time'
		    && DateTime::createFromFormat('Y-m-d', $_GET['_date_range_from']) !== false
		    && DateTime::createFromFormat('Y-m-d', $_GET['_date_range_to']) !== false
	    )
	    {
		    $vars['meta_query'] = array_merge($vars['meta_query'], array (
			    array(
				    'key' => $meta_key,
				    'value' => $_GET['_date_range_from'],
				    'type'  => 'date',
				    'compare' => '>='
			    ),
			    array(
				    'key' => $meta_key,
				    'value' => $_GET['_date_range_to'],
				    'type'  => 'date',
				    'compare' => '<='
			    ),
		    ));
	    }

	    return $vars;
    }

    public function posts_join( $join, $q ) {
        global $typenow, $wp_query, $wpdb;

        if ( !$q->is_main_query() )
            return $join;

        if ( !isset($_GET['s']) || ( isset($_GET['s']) && ph_clean($_GET['s']) == '' ) )
            return $join;

        if ( 'property' === $typenow ) 
        {
            $join .= " 
LEFT JOIN " . $wpdb->postmeta . " AS ph_property_filter_meta_address_concatenated ON " . $wpdb->posts . ".ID = ph_property_filter_meta_address_concatenated.post_id AND ph_property_filter_meta_address_concatenated.meta_key = '_address_concatenated'
LEFT JOIN " . $wpdb->postmeta . " AS ph_property_filter_meta_reference_number ON " . $wpdb->posts . ".ID = ph_property_filter_meta_reference_number.post_id AND ph_property_filter_meta_reference_number.meta_key = '_reference_number'
";
        }
        elseif ( 'contact' === $typenow ) 
        {
            $phone_number = '';
            if ( is_numeric(substr(ph_clean($_GET['s']), 0, 1)) )
            {
                $phone_number = preg_replace( "/[^0-9,]/", "", ph_clean($_GET['s']) );
            }

            $join .= " 
LEFT JOIN " . $wpdb->postmeta . " AS ph_contact_filter_meta_address_concatenated ON " . $wpdb->posts . ".ID = ph_contact_filter_meta_address_concatenated.post_id AND ph_contact_filter_meta_address_concatenated.meta_key = '_address_concatenated'
LEFT JOIN " . $wpdb->postmeta . " AS ph_contact_filter_meta_email_address ON " . $wpdb->posts . ".ID = ph_contact_filter_meta_email_address.post_id AND ph_contact_filter_meta_email_address.meta_key = '_email_address' ";
            
            if ( $phone_number != '' )
            {
                $join .= " LEFT JOIN " . $wpdb->postmeta . " AS ph_contact_filter_meta_telephone_number ON " . $wpdb->posts . ".ID = ph_contact_filter_meta_telephone_number.post_id AND ph_contact_filter_meta_telephone_number.meta_key = '_telephone_number_clean'
                ";
            }
        }
        elseif ( 'appraisal' === $typenow ) 
        {
            $join .= " 
LEFT JOIN " . $wpdb->postmeta . " AS ph_appraisal_filter_meta_name_number ON " . $wpdb->posts . ".ID = ph_appraisal_filter_meta_name_number.post_id AND ph_appraisal_filter_meta_name_number.meta_key = '_address_name_number'
LEFT JOIN " . $wpdb->postmeta . " AS ph_appraisal_filter_meta_street ON " . $wpdb->posts . ".ID = ph_appraisal_filter_meta_street.post_id AND ph_appraisal_filter_meta_street.meta_key = '_address_street'
LEFT JOIN " . $wpdb->postmeta . " AS ph_appraisal_filter_meta_2 ON " . $wpdb->posts . ".ID = ph_appraisal_filter_meta_2.post_id AND ph_appraisal_filter_meta_2.meta_key = '_address_two'
LEFT JOIN " . $wpdb->postmeta . " AS ph_appraisal_filter_meta_3 ON " . $wpdb->posts . ".ID = ph_appraisal_filter_meta_3.post_id AND ph_appraisal_filter_meta_3.meta_key = '_address_three'
LEFT JOIN " . $wpdb->postmeta . " AS ph_appraisal_filter_meta_4 ON " . $wpdb->posts . ".ID = ph_appraisal_filter_meta_4.post_id AND ph_appraisal_filter_meta_4.meta_key = '_address_four'
LEFT JOIN " . $wpdb->postmeta . " AS ph_appraisal_filter_meta_postcode ON " . $wpdb->posts . ".ID = ph_appraisal_filter_meta_postcode.post_id AND ph_appraisal_filter_meta_postcode.meta_key = '_address_postcode'
";
        }
        elseif ( 'viewing' === $typenow || 'offer' === $typenow || 'sale' === $typenow || 'tenancy' === $typenow ) 
        {
            $join .= " 
LEFT JOIN " . $wpdb->postmeta . " AS ph_property_filter_meta ON " . $wpdb->posts . ".ID = ph_property_filter_meta.post_id AND ph_property_filter_meta.meta_key = '_property_id'
LEFT JOIN " . $wpdb->posts . " AS ph_property_filter_posts ON ph_property_filter_posts.ID = ph_property_filter_meta.meta_value
LEFT JOIN " . $wpdb->postmeta . " AS ph_property_filter_meta_address_concatenated ON ph_property_filter_posts.ID = ph_property_filter_meta_address_concatenated.post_id AND ph_property_filter_meta_address_concatenated.meta_key = '_address_concatenated'
LEFT JOIN " . $wpdb->postmeta . " AS ph_property_filter_meta_reference_number ON ph_property_filter_posts.ID = ph_property_filter_meta_reference_number.post_id AND ph_property_filter_meta_reference_number.meta_key = '_reference_number'
LEFT JOIN " . $wpdb->postmeta . " AS ph_applicant_filter_meta ON " . $wpdb->posts . ".ID = ph_applicant_filter_meta.post_id AND ph_applicant_filter_meta.meta_key = '_applicant_contact_id'
LEFT JOIN " . $wpdb->posts . " AS ph_applicant_filter_posts ON ph_applicant_filter_posts.ID = ph_applicant_filter_meta.meta_value
";
        }

        return $join;
    }

    public function posts_where( $where, $q ) {
        global $typenow, $wp_query, $wpdb;

        if ( !$q->is_main_query() )
            return $where;

        if ( !isset($_GET['s']) || ( isset($_GET['s']) && ph_clean($_GET['s']) == '' ) )
            return $where;

        if ( 'property' === $typenow ) 
        {
            $where = preg_replace(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "(
                    (" . $wpdb->posts . ".post_title LIKE $1) 
                    OR
                    (ph_property_filter_meta_address_concatenated.meta_value LIKE $1)
                    OR 
                    (ph_property_filter_meta_reference_number.meta_value LIKE '" . esc_sql($_GET['s']) . "%')
                )", 
                $where 
            );

            $where = preg_replace(
                "/\s+OR\s+\(\s*" . $wpdb->posts . ".post_excerpt\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "",
                $where
            );

            $where = preg_replace(
                "/\s+OR\s+\(\s*" . $wpdb->posts . ".post_content\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "",
                $where
            );
        }
        elseif ( 'contact' === $typenow ) 
        {
            $phone_number = '';
            if ( is_numeric(substr(ph_clean($_GET['s']), 0, 1)) )
            {
                $phone_number = preg_replace( "/[^0-9,]/", "", ph_clean($_GET['s']) );
            }

            $where = preg_replace(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "(
                    (" . $wpdb->posts . ".post_title LIKE $1) 
                    OR
                    (ph_contact_filter_meta_address_concatenated.meta_value LIKE $1)
                    OR 
                    (ph_contact_filter_meta_email_address.meta_value LIKE $1)
                    " . ( $phone_number != '' ? "OR (ph_contact_filter_meta_telephone_number.meta_value LIKE '%" . $phone_number . "%')" : '' ) . "
                )", 
                $where 
            );

            $where = preg_replace(
                "/\s+OR\s+\(\s*" . $wpdb->posts . ".post_excerpt\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "",
                $where
            );

            $where = preg_replace(
                "/\s+OR\s+\(\s*" . $wpdb->posts . ".post_content\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "",
                $where
            );
        }
        elseif ( 'appraisal' === $typenow ) 
        {
            $where = preg_replace(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "(
                    (" . $wpdb->posts . ".post_title LIKE $1) 
                    OR
                    (ph_appraisal_filter_meta_name_number.meta_value LIKE $1)
                    OR 
                    (ph_appraisal_filter_meta_street.meta_value LIKE $1)
                    OR 
                    (ph_appraisal_filter_meta_2.meta_value LIKE $1)
                    OR 
                    (ph_appraisal_filter_meta_3.meta_value LIKE $1)
                    OR 
                    (ph_appraisal_filter_meta_4.meta_value LIKE $1)
                    OR 
                    (ph_appraisal_filter_meta_postcode.meta_value LIKE $1)
                )", 
                $where 
            );
        }
        elseif ( 'viewing' === $typenow || 'offer' === $typenow || 'sale' === $typenow || 'tenancy' === $typenow ) 
        {
            $where = preg_replace(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "(
                    (" . $wpdb->posts . ".post_title LIKE $1) 
                    OR 
                    (ph_property_filter_posts.post_title LIKE $1) 
                    OR
                    (ph_property_filter_meta_address_concatenated.meta_value LIKE $1)
                    OR 
                    (ph_property_filter_meta_reference_number.meta_value = '" . esc_sql($_GET['s']) . "')
                    OR
                    (ph_applicant_filter_posts.post_title LIKE $1) 
                )", 
                $where 
            );
        }

        return $where;
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