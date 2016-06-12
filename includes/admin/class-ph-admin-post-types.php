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

        add_filter( 'posts_where', array( $this, 'search_property_reference' ) );

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
        
        if ($typenow == 'property' || $typenow == 'contact')
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
        $output .= $this->property_office_filter();
        $output .= $this->property_negotiator_filter();

        echo apply_filters( 'propertyhive_property_filters', $output );
    }
    
    /**
     * Show a property department filter box
     */
    public function property_department_filter() {
        global $wp_query;
        
        // Department filtering
        $output  = '<select name="_department" id="dropdown_property_department">';
            
            $output .= '<option value="">' . __( 'All Departments', 'propertyhive' ) . '</option>';
            
            if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
            {
                $output .= '<option value="residential-sales"';
                if ( isset( $_GET['_department'] ) && ! empty( $_GET['_department'] ) )
                {
                    $output .= selected( 'residential-sales', $_GET['_department'], false );
                }
                $output .= '>' . __( 'Residential Sales', 'propertyhive' ) . '</option>';
            }
            if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
            {
                $output .= '<option value="residential-lettings"';
                if ( isset( $_GET['_department'] ) && ! empty( $_GET['_department'] ) )
                {
                    $output .= selected( 'residential-lettings', $_GET['_department'], false );
                }
                $output .= '>' . __( 'Residential Lettings', 'propertyhive' ) . '</option>';
            }
            if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
            {
                $output .= '<option value="commercial"';
                if ( isset( $_GET['_department'] ) && ! empty( $_GET['_department'] ) )
                {
                    $output .= selected( 'commercial', $_GET['_department'], false );
                }
                $output .= '>' . __( 'Commercial', 'propertyhive' ) . '</option>';
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
                    $output .= selected( $post->ID, $_GET['_office_id'], false );
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
            $selected = $_GET['_negotiator_id'];
        }
        
        $args = array(
            'name' => '_negotiator_id', 
            'id' => 'dropdown_property_negotiator_id',
            'show_option_all' => __( 'All Negotiators', 'propertyhive' ),
            'selected' => $selected,
            'echo' => false
        );
        $output = wp_dropdown_users($args);

        return $output;
    }
    
    /**
     * Show a contact filter box
     */
    public function contact_filters() {
        global $wp_query;
        
        // Type filtering        
        $options = array();

        // Owners
        $option = '<option value="owner"';
        if ( isset( $_GET['_contact_type'] ) && ! empty( $_GET['_contact_type'] ) )
        {
            $option .= selected( 'owner', $_GET['_contact_type'], false );
        }
        $option .= '>' . __( 'Owners and Landlords', 'propertyhive' ) . '</option>';

        $options[] = $option;

        // Applicants
        $option = '<option value="applicant"';
        if ( isset( $_GET['_contact_type'] ) && ! empty( $_GET['_contact_type'] ) )
        {
            $option .= selected( 'applicant', $_GET['_contact_type'], false );
        }
        $option .= '>' . __( 'Applicants', 'propertyhive' ) . '</option>';

        $options[] = $option;

        $options = apply_filters( 'propertyhive_contact_filter_options', $options );

        // Third Parties
        $option = '<option value="thirdparty"';
        if ( isset( $_GET['_contact_type'] ) && ! empty( $_GET['_contact_type'] ) )
        {
            $option .= selected( 'thirdparty', $_GET['_contact_type'], false );
        }
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
        
        // Status filtering
        $output  = '<select name="_status" id="dropdown_enquiry_status">';
            
            $output .= '<option value="open"';
            if ( isset( $_GET['_status'] ) && ! empty( $_GET['_status'] ) )
            {
                $output .= selected( 'open', $_GET['_status'], false );
            }
            $output .= '>' . __( 'Open', 'propertyhive' ) . '</option>';
            $output .= '<option value="closed"';
            if ( isset( $_GET['_status'] ) && ! empty( $_GET['_status'] ) )
            {
                $output .= selected( 'closed', $_GET['_status'], false );
            }
            $output .= '>' . __( 'Closed', 'propertyhive' ) . '</option>';
            
        $output .= '</select>';

        return $output;
    }
    
    /**
     * Show an enquiry source filter box
     */
    public function enquiry_source_filter() {
        global $wp_query;
        
        // Status filtering
        $output  = '<select name="_source" id="dropdown_enquiry_source">';
            
            $output .= '<option value="">' . __( 'Show all sources', 'propertyhive' ) . '</option>';
            
            $output .= '<option value="office"';
            if ( isset( $_GET['_source'] ) && ! empty( $_GET['_source'] ) )
            {
                $output .= selected( 'office', $_GET['_source'], false );
            }
            $output .= '>' . __( 'Office', 'propertyhive' ) . '</option>';
            $output .= '<option value="website"';
            if ( isset( $_GET['_source'] ) && ! empty( $_GET['_source'] ) )
            {
                $output .= selected( 'website', $_GET['_source'], false );
            }
            $output .= '>' . __( 'Website', 'propertyhive' ) . '</option>';
            
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

        if ( 'property' === $typenow ) 
        {
            if ( ! empty( $_GET['_department'] ) ) {
                $vars['meta_key'] = '_department';
                $vars['meta_value'] = sanitize_text_field( $_GET['_department'] );
            }

        }
        elseif ( 'contact' === $typenow ) 
        {
            if ( ! empty( $_GET['_contact_type'] ) ) {
                //$vars['meta_key '] = '_contact_types';
                //$vars['meta_value'] = sanitize_text_field( $_GET['_contact_type'] );
                //$vars['meta_compare '] = 'LIKE';
                $vars['meta_query'] = array(
                    array(
                        'key' => '_contact_types',
                        'value' => sanitize_text_field( $_GET['_contact_type'] ),
                        'compare' => 'LIKE'
                    )
                );
            }
        }
        elseif ( 'enquiry' === $typenow ) 
        {
            if ( ! empty( $_GET['_status'] ) ) {
                $vars['meta_key'] = '_status';
                $vars['meta_value'] = sanitize_text_field( $_GET['_status'] );
            }
            if ( ! empty( $_GET['_status'] ) ) {
                $vars['meta_key'] = '_source';
                $vars['meta_value'] = sanitize_text_field( $_GET['_source'] );
            }
        }

        return $vars;
    }
    
    /**
     * search_property_reference function.
     *
     * @access public
     * @param string $where (default: '')
     * @return string (modified where clause)
     */
    public function search_property_reference( $where = '' ) {
        global $typenow;
        
        if ( $typenow == 'property' && isset( $_GET['s'] ) && ! empty( $_GET['s'] ) )
        {
            //$where .= 'hel';
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