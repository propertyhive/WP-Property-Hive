<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Admin_Post_Types; preserving the existing PH_* class name is required for plugin and extension compatibility.
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

        add_action( 'admin_init', array( $this, 'handle_archive_action' ) );
        add_action( 'admin_init', array( $this, 'handle_unarchive_action' ) );

        $post_types = array('property', 'contact', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy', 'key_date');
        $post_types = apply_filters( 'propertyhive_post_types_with_archive', $post_types );

        foreach ( $post_types as $post_type )
        {
            add_filter( 'views_edit-' . $post_type, array( $this, 'adjust_post_status_views' ) );
            add_filter( "bulk_actions-edit-$post_type", array( $this, 'register_bulk_action_move_to_archive' ) );
            add_filter( "handle_bulk_actions-edit-$post_type", array( $this, 'handle_bulk_action_archive_and_unarchive' ), 10, 3 );
        }

        add_filter( 'post_row_actions', array( $this, 'modify_post_row_actions_for_archived' ), 10, 2 );
	}

	/**
	 * Read one scalar admin query value after WordPress unslashes and sanitizes it.
	 *
	 * Admin list filters are read-only, but their values still flow into markup and
	 * query arguments.  Returning an empty value for arrays keeps scalar filters
	 * from accidentally accepting a malformed request while preserving the
	 * existing empty-filter behaviour.
	 *
	 * @param string $key Query-string key.
	 * @return string
	 */
	private function get_admin_query_value( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
		if ( ! isset( $_GET[ $key ] ) || ! is_scalar( $_GET[ $key ] ) ) {
			return '';
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Read-only admin list value is copied, unslashed immediately below, and sanitized before use; the sniffer reports the source assignment instead of the sanitization boundary.
		$raw_value = $_GET[ $key ];
		$raw_value = wp_unslash( (string) $raw_value );

		return sanitize_text_field( $raw_value );
	}

    public function handle_bulk_action_archive_and_unarchive($redirect_to, $doaction, $post_ids) 
    {
        if ($doaction === 'move_to_archive') 
        {
            foreach ($post_ids as $post_id) 
            {
                // Check permissions
                if (!current_user_can('edit_post', $post_id)) {
                    continue;
                }

                // Update the post status to 'archive'
                $updated_post = array(
                    'ID'           => $post_id,
                    'post_status'  => 'archive',
                );

                wp_update_post($updated_post);
            }

            $redirect_to = add_query_arg('bulk_archived_posts', count($post_ids), $redirect_to);
        }
        elseif ($doaction === 'unarchive') 
        {
            foreach ($post_ids as $post_id) 
            {
                // Check permissions
                if (!current_user_can('edit_post', $post_id)) {
                    continue;
                }

                // Update the post status to 'publish' (or whatever the original status should be)
                $updated_post = array(
                    'ID'           => $post_id,
                    'post_status'  => 'publish',
                );

                wp_update_post($updated_post);
            }

            $redirect_to = add_query_arg('bulk_unarchived_posts', count($post_ids), $redirect_to);
        }

        return $redirect_to;
    }

    public function register_bulk_action_move_to_archive( $bulk_actions ) 
    {
        global $post_status;

        // Define our custom actions
        $custom_actions = array();

        if ($post_status === 'archive') {
            $custom_actions['unarchive'] = __('Unarchive', 'propertyhive');
        } else {
            $custom_actions['move_to_archive'] = __('Move to Archive', 'propertyhive');
        }

        // Check if 'trash' exists and insert custom actions before it
        if (isset($bulk_actions['trash'])) 
        {
            $new_actions = array();
            foreach ($bulk_actions as $key => $value) {
                if ($key === 'trash') {
                    $new_actions = array_merge($new_actions, $custom_actions);
                }
                $new_actions[$key] = $value;
            }
            return $new_actions;
        }
        elseif (isset($bulk_actions['untrash'])) 
        {
            $new_actions = array();
            foreach ($bulk_actions as $key => $value) {
                if ($key === 'untrash') {
                    $new_actions = array_merge($new_actions, $custom_actions);
                }
                $new_actions[$key] = $value;
            }
            return $new_actions;
        }
        else
        {
            // If 'trash' doesn't exist, append custom actions at the end
            return array_merge($bulk_actions, $custom_actions);
        }
    }

    public function modify_post_row_actions_for_archived( $actions, $post ) 
    {
        // Define the post types that can be archived
        $post_types = array('property', 'contact', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy', 'key_date');
        $post_types = apply_filters('propertyhive_post_types_with_archive', $post_types);

        // Check if the current post type is in the allowed post types and if the post is archived
        if ( in_array($post->post_type, $post_types) && $post->post_status == 'archive' ) 
        {
            // Remove the "View" link
            if (isset($actions['view'])) {
                unset($actions['view']);
            }

            // Add the "Unarchive" link
            $unarchive_url = wp_nonce_url(admin_url('post.php?post=' . $post->ID . '&action=unarchive&return=archive'), 'unarchive-post_' . $post->ID);
            $actions['unarchive'] = '<a href="' . esc_url($unarchive_url) . '">' . __('Unarchive', 'propertyhive') . '</a>';
        }

        return $actions;
    }

    public function adjust_post_status_views( $views ) 
    {
        if (isset($views['archive'])) 
        {
            $archive = $views['archive'];
            unset($views['archive']);

            $new_views = array();
            $bin_exists = false;
            
            foreach ($views as $key => $view) {
                if ($key === 'trash') {
                    $bin_exists = true;
                    $new_views['archive'] = $archive;
                }
                $new_views[$key] = $view;
            }

            // Ensure 'archive' is added to the end if 'trash' is not present
            if (!$bin_exists) {
                $new_views['archive'] = $archive;
            }

            return $new_views;
        }

        return $views;
    }

    public function handle_archive_action() 
    {
        // Check if the action and nonce are set and valid
        if ( !isset($_GET['action']) || $_GET['action'] !== 'archive_single' )
            return;
        
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        $post_type = get_post_type($post_id);

        if ( !wp_verify_nonce( ( isset( $_GET['_wpnonce'] ) && is_string( $_GET['_wpnonce'] ) ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '', 'archive-post_' . $post_id) )
        {
            wp_die(esc_html(__('Security check failed.', 'propertyhive')));
        }

        if ( !current_user_can('edit_post', $post_id) )
        {
            wp_die(esc_html(__('You do not have permission to edit this post.', 'propertyhive')));
        }

        // Update the post status to 'archive'
        $updated_post = array(
            'ID'           => $post_id,
            'post_status'  => 'archive',
        );

        $result = wp_update_post($updated_post, true);

        if ( is_wp_error($result) ) 
        {
            wp_die(esc_html(__('An error occurred while archiving the post.', 'propertyhive')));
        }

        // Redirect to the main list of contacts
        wp_safe_redirect(admin_url('edit.php?post_type=' . $post_type));
        exit;
    }

    public function handle_unarchive_action() 
    {
        // Check if the action and nonce are set and valid
        if ( !isset($_GET['action']) || $_GET['action'] !== 'unarchive_single' )
            return;
        
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        $post_type = get_post_type($post_id);

        if ( !wp_verify_nonce( ( isset( $_GET['_wpnonce'] ) && is_string( $_GET['_wpnonce'] ) ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '', 'unarchive-post_' . $post_id) )
        {
            wp_die(esc_html(__('Security check failed.', 'propertyhive')));
        }

        if ( !current_user_can('edit_post', $post_id) )
        {
            wp_die(esc_html(__('You do not have permission to edit this post.', 'propertyhive')));
        }

        // Update the post status to 'publish'
        $updated_post = array(
            'ID'           => $post_id,
            'post_status'  => 'publish',
        );

        $result = wp_update_post($updated_post, true);

        if ( is_wp_error($result) ) 
        {
            wp_die(esc_html(__('An error occurred while unarchiving the post.', 'propertyhive')));
        }

        // Redirect to the main list of contacts
        if ( isset($_GET['return']) && $_GET['return'] === 'archive' ) 
        {
            wp_safe_redirect(admin_url('edit.php?post_status=archive&post_type=' . get_post_type($post_id)));
        }
        else
        {
            wp_safe_redirect(admin_url('edit.php?post_type=' . get_post_type($post_id)));
        }
        exit;
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
            /* translators: %s: URL to view the property */
			1 => sprintf( __( 'Property updated. <a href="%s">View property</a>', 'propertyhive' ), esc_url( get_permalink($post_ID) ) ),
			2 => __( 'Custom field updated.', 'propertyhive' ),
			3 => __( 'Custom field deleted.', 'propertyhive' ),
			4 => __( 'Property updated.', 'propertyhive' ),
			5 => __( 'Revision restored.', 'propertyhive' ),
            /* translators: %s: URL to view the property */
			6 => sprintf( __( 'Property published. <a href="%s">View property</a>', 'propertyhive' ), esc_url( get_permalink($post_ID) ) ),
			7 => __( 'Property saved.', 'propertyhive' ),
            /* translators: %s: URL to preview the property */
			8 => sprintf( __( 'Property submitted. <a target="_blank" href="%s">Preview property</a>', 'propertyhive' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			/* translators: 1: formatted date, 2: URL to preview the property */
            9 => sprintf( __( 'Property scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview property</a>', 'propertyhive' ),
			  date_i18n( __( 'M j, Y @ G:i', 'propertyhive' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			/* translators: %s: URL to preview the property */
            10 => sprintf( __( 'Property draft updated. <a target="_blank" href="%s">Preview property</a>', 'propertyhive' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		$messages['contact'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => __( 'Contact updated.', 'propertyhive' ),
            2 => __( 'Custom field updated.', 'propertyhive' ),
            3 => __( 'Custom field deleted.', 'propertyhive' ),
            4 => __( 'Contact updated.', 'propertyhive' ),
            5 => __( 'Revision restored.', 'propertyhive' ),
            6 => __( 'Contact published.', 'propertyhive' ),
            7 => __( 'Contact saved.', 'propertyhive' ),
            8 => __( 'Contact submitted.', 'propertyhive' ),
            /* translators: 1: formatted date */
            9 => sprintf( __( 'Contact scheduled for: <strong>%1$s</strong>.', 'propertyhive' ), date_i18n( __( 'M j, Y @ G:i', 'propertyhive' ), strtotime( $post->post_date ) )),
            10 => __( 'Contact draft updated.', 'propertyhive' ),
        );
        
        $messages['office'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => __( 'Office updated.', 'propertyhive' ),
            2 => __( 'Custom field updated.', 'propertyhive' ),
            3 => __( 'Custom field deleted.', 'propertyhive' ),
            4 => __( 'Office updated.', 'propertyhive' ),
            5 => __( 'Revision restored.', 'propertyhive' ),
            6 => sprintf( __( 'Office published.', 'propertyhive' ), esc_url( get_permalink($post_ID) ) ),
            7 => __( 'Office saved.', 'propertyhive' ),
            8 => sprintf( __( 'Office submitted.', 'propertyhive' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
            /* translators: 1: formatted date */
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
            5 => __( 'Revision restored.', 'propertyhive' ),
            6 => sprintf( __( 'Enquiry published.', 'propertyhive' ), esc_url( get_permalink($post_ID) ) ),
            7 => __( 'Enquiry saved.', 'propertyhive' ),
            8 => sprintf( __( 'Enquiry submitted.', 'propertyhive' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
            /* translators: 1: formatted date */
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

        $post_types_to_hide_months_dropdown = array('property', 'contact', 'enquiry', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy', 'key_date');
        $post_types_to_hide_months_dropdown = apply_filters( 'propertyhive_post_types_to_hide_months_dropdown', $post_types_to_hide_months_dropdown );

        if ( in_array($typenow, $post_types_to_hide_months_dropdown) )
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

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built-in controls escape their text and attributes before this trusted PHP filter adds complete HTML controls.
        echo apply_filters( 'propertyhive_property_filters', $output );
    }
    
    /**
     * Show a property department filter box
     */
    public function property_department_filter() {
        global $wp_query;

        $departments = ph_get_departments();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
        $requested_value = isset( $_GET['_department'] ) && is_string( $_GET['_department'] ) ? sanitize_text_field( wp_unslash( $_GET['_department'] ) ) : '';
        $selected_department = array_key_exists( $requested_value, $departments ) ? $requested_value : '';
        
        // Department filtering
        $output  = '<select name="_department" id="dropdown_property_department">';
            
            $output .= '<option value="">' . esc_html__( 'All Departments', 'propertyhive' ) . '</option>';

            foreach ( $departments as $key => $value )
            {
                if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
                {
                    $output .= '<option value="' . esc_attr($key) . '"';
                    $output .= selected( $key, $selected_department, false );
                    $output .= '>' . esc_html($value) . '</option>';
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
        
        $output .= '<option value="">' . esc_html__( 'All Offices', 'propertyhive' ) . '</option>';
        
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
                
                $output .= '<option value="' . esc_attr($post->ID) . '"';
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                if ( isset( $_GET['_office_id'] ) && ! empty( $_GET['_office_id'] ) )
                {
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    $output .= selected( $post->ID, (int)$_GET['_office_id'], false );
                }
                $output .= '>' . esc_html(get_the_title()) . '</option>';
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
            'show_option_all' => esc_html__( 'All Negotiators', 'propertyhive' ),
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            'selected' => empty( $_GET['_negotiator_id'] ) ? '' : (int)$_GET['_negotiator_id'],
            'echo' => false,
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy Property Negotiator compatibility filter; existing role filters depend on this exact public hook name.
            'role__not_in' => apply_filters( 'property_negotiator_exclude_roles', array('property_hive_contact', 'subscriber') )
        ));
    }

	/**
	 * Show a date range selector
	 */
	public function date_range_filter() {

		$date_range_label = $this->get_admin_query_value( '_date_range_label' );
		$date_range_label = empty( $date_range_label ) ? __( 'Any Time', 'propertyhive' ) : $date_range_label;

		// The date picker doesn't have a concept of 'Any Time', so valid dates must be used
		// I've used the last and first date of the month (reversed) as it's a range that is not selectable, but is within the current month
		// If I used an already labelled date range (e.g. 'Today'), it would show as 'Today' when selected
		// If I use a nearby date range (e.g. 'Yesterday'), if someone actually selected that range it would show as 'Any Time'
		// If I use a unlikely date range (e.g. 01-01-1970 - 31-12-2070), the custom date range picker would open showing Jan 1970.
		$date_range_from = $this->get_admin_query_value( '_date_range_from' );
		$date_range_from = empty( $date_range_from ) ? gmdate('Y-m-d', strtotime('last day of this month')) : $date_range_from;
		$date_range_to = $this->get_admin_query_value( '_date_range_to' );
		$date_range_to = empty( $date_range_to ) ? gmdate('Y-m-d', strtotime('first day of this month')) : $date_range_to;

		return "
            <select name='_date_range_label' id='date_range' style='max-width:25rem;'>
                <option selected>" . esc_html($date_range_label) . "</option>
            <select/>
            <input type='hidden' name='_date_range_from' id='date_range_from' value='" . esc_attr($date_range_from) . "'>
            <input type='hidden' name='_date_range_to' id='date_range_to' value='" . esc_attr($date_range_to) . "'>
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
        $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
        
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
                
                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                );
                $subterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
                
                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = '- ' . $term->name;
                        
                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subsubterms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'location' ) ) );
                        
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
        
        $output .= '<option value="">' . esc_html(__( 'All Locations', 'propertyhive' )) . '</option>';
        
        if ( !empty($options) )
        {
            foreach ( $options as $value => $label )
            {
                $output .= '<option value="' . esc_attr($value) . '"';
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                if ( isset( $_GET['_location_id'] ) && ! empty( $_GET['_location_id'] ) )
                {
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    $output .= selected( $value, (int)$_GET['_location_id'], false );
                }
                $output .= '>' . esc_html($label) . '</option>';
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
        $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'availability' ) ) );
        
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
            }
        }
        
        $output .= '<option value="">' . esc_html(__( 'All Availabilities', 'propertyhive' )) . '</option>';
        
        if ( !empty($options) )
        {
            foreach ( $options as $value => $label )
            {
                $output .= '<option value="' . esc_attr($value) . '"';
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                if ( isset( $_GET['_availability_id'] ) && ! empty( $_GET['_availability_id'] ) )
                {
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    $output .= selected( $value, (int)$_GET['_availability_id'], false );
                }
                $output .= '>' . esc_html($label) . '</option>';
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

        $output .= '<option value="">' . esc_html__( 'All Marketing Statuses', 'propertyhive' ) . '</option>';

        $options = array(
            'on_market' => __( 'On Market Only', 'propertyhive' ),
            'off_market' => __( 'Not On Market Only', 'propertyhive' ),
            'featured' => __( 'Featured Only', 'propertyhive' ),
        );

        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'marketing_flag' ) ) );
        
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options['marketing_flag_' . $term->term_id] = __( 'Has Marketing Flag', 'propertyhive') . ' - ' . $term->name;
            }
        }

        $options = apply_filters( 'propertyhive_property_filter_marketing_options', $options );
		$selected_marketing = $this->get_admin_query_value( '_marketing' );

        foreach ( $options as $key => $value )
        {
            $output .= '<option value="' . esc_attr($key) . '"';
            if ( ! empty( $selected_marketing ) )
            {
				$output .= selected( $key, $selected_marketing, false );
            }
            $output .= '>' . esc_html($value) . '</option>';
        }

        $output .= '</select>';

        return $output;
    }
    
    /**
     * Show a contact filter box
     */
    public function contact_filters() {
        global $wp_query;

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
        $requested_value = isset( $_GET['_contact_type'] ) && is_string( $_GET['_contact_type'] ) ? sanitize_text_field( wp_unslash( $_GET['_contact_type'] ) ) : '';
        $selected_contact_type = in_array( $requested_value, array( 'owner', 'potentialowner', 'applicant', 'hotapplicant', 'thirdparty' ), true ) ? $requested_value : '';
        
        // Type filtering        
        $options = array();

        // Owners
        $option = '<option value="owner"';
        $option .= selected( 'owner', $selected_contact_type, false );
        $option .= '>' . esc_html(__( 'Owners and Landlords', 'propertyhive' )) . '</option>';

        $options[] = $option;

        // Potential Owners
        $option = '<option value="potentialowner"';
        $option .= selected( 'potentialowner', $selected_contact_type, false );
        $option .= '>' . esc_html(__( 'Potential Owners and Landlords', 'propertyhive' )) . '</option>';

        $options[] = $option;

        // Applicants
        $option = '<option value="applicant"';
        $option .= selected( 'applicant', $selected_contact_type, false );
        $option .= '>' . esc_html(__( 'Applicants', 'propertyhive' )) . '</option>';

        $options[] = $option;

        // Hot Applicants
        $option = '<option value="hotapplicant"';
        $option .= selected( 'hotapplicant', $selected_contact_type, false );
        $option .= '>- ' . esc_html(__( 'Hot Applicants', 'propertyhive' )) . '</option>';

        $options[] = $option;

        // Third Parties
        $option = '<option value="thirdparty"';
        $option .= selected( 'thirdparty', $selected_contact_type, false );
        $option .= '>' . esc_html(__( 'Third Party Contacts', 'propertyhive' )) . '</option>';

        $options[] = $option;

        $options = apply_filters( 'propertyhive_contact_filter_options', $options );

        $output = '';
        if (count($options) > 1)
        {
            $output  = '<select name="_contact_type" id="dropdown_contact_type">';
            
                $output .= '<option value="">' . esc_html(__( 'Show all contact types', 'propertyhive' )) . '</option>';

                $output .= implode("", $options);
            
            $output .= '</select>';
        }

        $output .= $this->date_range_filter('Date Created');

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built-in controls escape their text and attributes before this trusted PHP filter adds complete HTML controls.
        echo apply_filters( 'propertyhive_contact_filters', $output );
    }
    
    /**
     * Show an enquiry filter box
     */
    public function enquiry_filters() {
        global $wp_query;
        
        // Department filtering
        $output = '';
        
        $output .= $this->date_range_filter();
        $output .= $this->enquiry_status_filter();
        $output .= $this->enquiry_source_filter();
        $output .= $this->enquiry_office_filter();
        $output .= $this->enquiry_negotiator_filter();

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built-in controls escape their text and attributes before this trusted PHP filter adds complete HTML controls.
        echo apply_filters( 'propertyhive_enquiry_filters', $output );
    }
    
    /**
     * Show an enquiry status filter box
     */
    public function enquiry_status_filter() {
        global $wp_query;

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
        $requested_value = isset( $_GET['_status'] ) && is_string( $_GET['_status'] ) ? sanitize_text_field( wp_unslash( $_GET['_status'] ) ) : '';
        $selected_status = in_array( $requested_value, array( 'all', 'open', 'closed' ), true ) ? $requested_value : '';

        // Status filtering
        $output  = '<select name="_status" id="dropdown_enquiry_status">
            <option value="all"' . selected( 'all', $selected_status, false ) . '>All</option>';

            $enquiry_statuses = ph_get_enquiry_statuses();

            foreach ( $enquiry_statuses as $status => $display_status )
            {
                $output .= '<option value="' . esc_attr($status) . '"';
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                if ( $status == $selected_status || ( $status == 'open' && ( !isset($_GET['_status']) || empty($_GET['_status']) ) ) )
                {
                    $output .= ' selected';
                }
                $output .= selected( $status, $selected_status, false );
                $output .= '>' . esc_html($display_status) . '</option>';
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
		$selected_source = $this->get_admin_query_value( '_source' );
            
            $output .= '<option value="">' . esc_html__( 'Show all sources', 'propertyhive' ) . '</option>';
            
            foreach ( $sources as $key => $value )
            {
                $output .= '<option value="' . esc_attr($key) . '"';
                if ( ! empty( $selected_source ) )
                {
					$output .= selected( $key, $selected_source, false );
                }
                $output .= '>' . esc_html( $value ) . '</option>';
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
        
        $output .= '<option value="">' . esc_html__( 'All Offices', 'propertyhive' ) . '</option>';
        
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
                
                $output .= '<option value="' . esc_attr($post->ID) . '"';
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                if ( isset( $_GET['_office_id'] ) && ! empty( $_GET['_office_id'] ) )
                {
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    $output .= selected( $post->ID, (int)$_GET['_office_id'], false );
                }
                $output .= '>' . esc_html(get_the_title()) . '</option>';
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
            'show_option_all' => esc_html__( 'All Negotiators', 'propertyhive' ),
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            'selected' => empty( $_GET['_negotiator_id'] ) ? '' : (int)$_GET['_negotiator_id'],
            'echo' => false,
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy Property Negotiator compatibility filter; existing role filters depend on this exact public hook name.
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

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built-in controls escape their text and attributes before this trusted PHP filter adds complete HTML controls.
        echo apply_filters( 'propertyhive_appraisal_filters', $output );
    }

    /**
     * Show an appraisal status filter box
     */
    public function appraisal_status_filter() {
        global $wp_query;

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
        $requested_value = isset( $_GET['_status'] ) && is_string( $_GET['_status'] ) ? sanitize_text_field( wp_unslash( $_GET['_status'] ) ) : '';
        $selected_status = in_array( $requested_value, array( 'pending', 'carried_out', 'won', 'lost', 'instructed', 'cancelled' ), true ) ? $requested_value : '';
        
        // Status filtering
        $output  = '<select name="_status" id="dropdown_appraisal_status">';
            
            $output .= '<option value="">' . esc_html__( 'All Statuses', 'propertyhive' ) . '</option>';

            $output .= '<option value="pending"';
            $output .= selected( 'pending', $selected_status, false );
            $output .= '>' . esc_html(__( 'Pending', 'propertyhive' )) . '</option>';

            $output .= '<option value="carried_out"';
            $output .= selected( 'carried_out', $selected_status, false );
            $output .= '>' . esc_html(__( 'Carried Out', 'propertyhive' )) . '</option>';

            $output .= '<option value="won"';
            $output .= selected( 'won', $selected_status, false );
            $output .= '>- ' . esc_html(__( 'Won', 'propertyhive' )) . '</option>';

            $output .= '<option value="lost"';
            $output .= selected( 'lost', $selected_status, false );
            $output .= '>- ' . esc_html(__( 'Lost', 'propertyhive' )) . '</option>';

            $output .= '<option value="instructed"';
            $output .= selected( 'instructed', $selected_status, false );
            $output .= '>- ' . esc_html(__( 'Instructed', 'propertyhive' )) . '</option>';

            $output .= '<option value="cancelled"';
            $output .= selected( 'cancelled', $selected_status, false );
            $output .= '>' . esc_html(__( 'Cancelled', 'propertyhive' )) . '</option>';
            
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

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built-in controls escape their text and attributes before this trusted PHP filter adds complete HTML controls.
        echo apply_filters( 'propertyhive_viewing_filters', $output );
    }

    /**
     * Show a viewing status filter box
     */
    public function viewing_status_filter() {
        global $wp_query;

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
        $requested_value = isset( $_GET['_status'] ) && is_string( $_GET['_status'] ) ? sanitize_text_field( wp_unslash( $_GET['_status'] ) ) : '';
        $selected_status = in_array( $requested_value, array( 'pending', 'confirmed', 'unconfirmed', 'carried_out', 'awaiting_feedback', 'feedback_passed_on', 'feedback_not_passed_on', 'cancelled', 'no_show' ), true ) ? $requested_value : '';
        
        // Status filtering
        $output  = '<select name="_status" id="dropdown_viewing_status">';

            $output .= '<option value="">' . esc_html__( 'All Statuses', 'propertyhive' ) . '</option>';

            $viewing_statuses = ph_get_viewing_statuses();

            foreach ( $viewing_statuses as $status => $display_status )
            {
                $output .= '<option value="' . esc_attr($status) . '"';
                $output .= selected( $status, $selected_status, false );
                $output .= '>' . esc_html($display_status) . '</option>';
            }

        $output .= '</select>';

        return $output;
    }


    public function refresh_property_office_filtering( $query ) {
        remove_filter('posts_join', array( $this, 'filter_by_property_office')  );

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
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

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only office filtering; no state change.
        $office_id = isset( $_GET['_office_id'] ) && is_scalar( $_GET['_office_id'] ) ? absint( $_GET['_office_id'] ) : 0;

        return $query . '
           INNER JOIN ' . $wpdb->postmeta . ' AS property_meta ON property_meta.post_id = ' . $wpdb->posts . '.ID AND property_meta.meta_key = "_property_id"
           INNER JOIN ' . $wpdb->postmeta . ' AS property_office_meta ON property_office_meta.post_id = property_meta.meta_value AND property_office_meta.meta_key = "_office_id"
             AND property_office_meta.meta_value = ' . $office_id;
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

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built-in controls escape their text and attributes before this trusted PHP filter adds complete HTML controls.
        echo apply_filters( 'propertyhive_offer_filters', $output );
    }

    /**
     * Show an offer status filter box
     */
    public function offer_status_filter() {
        global $wp_query;

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
        $requested_value = isset( $_GET['_status'] ) && is_string( $_GET['_status'] ) ? sanitize_text_field( wp_unslash( $_GET['_status'] ) ) : '';
        $selected_status = in_array( $requested_value, array( 'pending', 'accepted', 'declined' ), true ) ? $requested_value : '';
        
        // Status filtering
        $output  = '<select name="_status" id="dropdown_offer_status">';

            $output .= '<option value="">' . esc_html(__( 'All Statuses', 'propertyhive' )) . '</option>';

            $offer_statuses = ph_get_offer_statuses();

            foreach ( $offer_statuses as $status => $display_status )
            {
                $output .= '<option value="' . esc_attr($status) . '"';
                $output .= selected( $status, $selected_status, false );
                $output .= '>' . esc_html($display_status) . '</option>';
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

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built-in controls escape their text and attributes before this trusted PHP filter adds complete HTML controls.
        echo apply_filters( 'propertyhive_sale_filters', $output );
    }

    /**
     * Show an sale status filter box
     */
    public function sale_status_filter() {
        global $wp_query;

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
        $requested_value = isset( $_GET['_status'] ) && is_string( $_GET['_status'] ) ? sanitize_text_field( wp_unslash( $_GET['_status'] ) ) : '';
        $selected_status = in_array( $requested_value, array( 'current', 'exchanged', 'completed', 'fallen_through' ), true ) ? $requested_value : '';
        
        // Status filtering
        $output  = '<select name="_status" id="dropdown_sale_status">';
            
            $output .= '<option value="">' . esc_html__( 'All Statuses', 'propertyhive' ) . '</option>';

            $sale_statuses = ph_get_sale_statuses();

            foreach ( $sale_statuses as $status => $display_status )
            {
                $output .= '<option value="' . esc_attr($status) . '"';
                $output .= selected( $status, $selected_status, false );
                $output .= '>' . esc_html($display_status) . '</option>';
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

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built-in controls escape their text and attributes before this trusted PHP filter adds complete HTML controls.
        echo apply_filters( 'propertyhive_tenancy_filters', $output );
    }

    /**
     * Show an tenancy status filter box
     */
    public function tenancy_status_filter() {
        global $wp_query;

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
        $requested_value = isset( $_GET['_status'] ) && is_string( $_GET['_status'] ) ? sanitize_text_field( wp_unslash( $_GET['_status'] ) ) : '';
        $selected_status = in_array( $requested_value, array( 'pending', 'current', 'finished'), true ) ? $requested_value : '';

        // Status filtering
        $output  = '<select name="_status" id="dropdown_tenancy_status">';

            $output .= '<option value="">' . esc_html(__( 'All Statuses', 'propertyhive' )) . '</option>';

            $output .= '<option value="pending"';
            $output .= selected( 'pending', $selected_status, false );
            $output .= '>' . esc_html(__( 'Pending', 'propertyhive' )) . '</option>';

            $output .= '<option value="current"';
            $output .= selected( 'current', $selected_status, false );
            $output .= '> ' . esc_html(__( 'Current', 'propertyhive' )) . '</option>';

            $output .= '<option value="finished"';
            $output .= selected( 'finished', $selected_status, false );
            $output .= '> ' . esc_html(__( 'Finished', 'propertyhive' )) . '</option>';

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

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
        $requested_value = isset( $_GET['_management_type'] ) && is_string( $_GET['_management_type'] ) ? sanitize_text_field( wp_unslash( $_GET['_management_type'] ) ) : '';
        $selected_management_type = array_key_exists( $requested_value, $management_types ) ? $requested_value : '';

        // Status filtering
        $output  = '<select name="_management_type" id="dropdown_tenancy_management_type">';

            $output .= '<option value="">' . esc_html(__( 'All Management Types', 'propertyhive' )) . '</option>';

            foreach ( $management_types as $key => $value )
            {
                $output .= '<option value="' . esc_attr($key) . '"';
                $output .= selected( $key, $selected_management_type, false );
                $output .= '>' . esc_html( $value ) . '</option>';
            }

        $output .= '</select>';

        return $output;
    }

	public function key_date_filters() {
		global $wp_query;

		$output = '';

		$output .= $this->key_date_type_filter();
		$output .= $this->key_date_status_filter();
        $output .= $this->date_range_filter();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built-in controls escape their text and attributes before this trusted PHP filter adds complete HTML controls.
		echo apply_filters( 'propertyhive_tenancy_filters', $output );
	}

	public function key_date_type_filter() {

  // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
		$selected_value = ! empty($_GET['_key_date_type_id']) ? (int)$_GET['_key_date_type_id'] : '';
		$terms = get_terms( array_merge( wp_parse_args( array(
			'hide_empty' => false,
			'parent' => 0
		) ), array( 'taxonomy' => 'management_key_date_type' ) ) );

		$output  = '<select name="_key_date_type_id">';
		$output .= '<option value="">' . esc_html(__( 'All Types', 'propertyhive' )) . '</option>';

		if ( !empty( $terms ) && !is_wp_error( $terms ) )
		{
			foreach ($terms as $term)
			{
				$output .= '<option value="' . esc_attr($term->term_id) . '"';
				$output .= selected($term->term_id, $selected_value, false );
				$output .= '>' . esc_html($term->name) . '</option>';
			}
		}

		$output .= '</select>';

		return $output;
	}


	public function key_date_status_filter() {

  // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
		$requested_value = isset( $_GET['status'] ) && is_string( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		$selected_status = in_array( $requested_value, array( 'upcoming_and_overdue', 'overdue', 'booked', 'complete', 'pending', 'on_hold', 'cancelled'), true ) ? $requested_value : '';

		$output  = '<select name="status" id="dropdown_key_date_status">';

		$output .= '<option value="">' . esc_html(__( 'All Statuses', 'propertyhive' )) . '</option>';

		$output .= '<option value="upcoming_and_overdue"';
		$output .= selected( 'upcoming_and_overdue', $selected_status, false );
		$output .= '>' . esc_html(__( 'Upcoming & Overdue', 'propertyhive' )) . '</option>';

        $output .= '<option value="overdue"';
        $output .= selected( 'overdue', $selected_status, false );
        $output .= '>' . esc_html(__( 'Overdue', 'propertyhive' )) . '</option>';

		$output .= '<option value="booked"';
		$output .= selected( 'booked', $selected_status, false );
		$output .= '> ' . esc_html(__( 'Booked', 'propertyhive' )) . '</option>';

		$output .= '<option value="complete"';
		$output .= selected( 'complete', $selected_status, false );
		$output .= '> ' . esc_html(__( 'Complete', 'propertyhive' )) . '</option>';

		$output .= '<option value="pending"';
		$output .= selected( 'pending', $selected_status, false );
		$output .= '> ' . esc_html(__( 'Pending', 'propertyhive' )) . '</option>';

        $output .= '<option value="on_hold"';
        $output .= selected( 'on_hold', $selected_status, false );
        $output .= '> ' . esc_html(__( 'On Hold', 'propertyhive' )) . '</option>';

        $output .= '<option value="cancelled"';
        $output .= selected( 'cancelled', $selected_status, false );
        $output .= '> ' . esc_html(__( 'Cancelled', 'propertyhive' )) . '</option>';

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

        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- These hooks add status/department/taxonomy/date filters to the main admin list query. WordPress supplies the list query’s pagination; values are sanitized or selected from fixed post-type/date keys. These are request_query/filter_by_date_range values consumed by the core list table query rather than independent nopaging loops. The date meta key is chosen by post type.
        if ( !isset($vars['meta_query']) ) { $vars['meta_query'] = array(); }
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- These hooks add status/department/taxonomy/date filters to the main admin list query. WordPress supplies the list query’s pagination; values are sanitized or selected from fixed post-type/date keys. These are request_query/filter_by_date_range values consumed by the core list table query rather than independent nopaging loops. The date meta key is chosen by post type.
        if ( !isset($vars['tax_query']) ) { $vars['tax_query'] = array(); }

		$department = $this->get_admin_query_value( '_department' );
		$marketing = $this->get_admin_query_value( '_marketing' );
		$contact_type = $this->get_admin_query_value( '_contact_type' );
		$status = $this->get_admin_query_value( '_status' );
		$source = $this->get_admin_query_value( '_source' );
		$management_type = $this->get_admin_query_value( '_management_type' );
		$key_date_status = $this->get_admin_query_value( 'status' );

        if ( 'property' === $typenow ) 
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $department ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_department',
					'value' => $department,
                );
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $_GET['_office_id'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_office_id',
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    'value' => (int)$_GET['_office_id'],
                );
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $_GET['_negotiator_id'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_negotiator_id',
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    'value' => (int)$_GET['_negotiator_id'],
                );
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $_GET['_location_id'] ) ) {
                $vars['tax_query'][] = array(
                    'taxonomy'  => 'location',
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    'terms' => ( (is_array($_GET['_location_id'])) ? (int)$_GET['_location_id'] : array( (int)$_GET['_location_id'] ) )
                );
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $_GET['_availability_id'] ) ) {
                $vars['tax_query'][] = array(
                    'taxonomy'  => 'availability',
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    'terms' => ( (is_array($_GET['_availability_id'])) ? (int)$_GET['_availability_id'] : array( (int)$_GET['_availability_id'] ) )
                );
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
			if ( 'on_market' === $marketing ) {
                $vars['meta_query'][] = array(
                    'key' => '_on_market',
                    'value' => 'yes',
                );
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
			if ( 'off_market' === $marketing ) {
                $vars['meta_query'][] = array(
                    'key' => '_on_market',
                    'value' => 'yes',
                    'compare' => '!=',
                );
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
			if ( 'featured' === $marketing ) {
                $vars['meta_query'][] = array(
                    'key' => '_featured',
                    'value' => 'yes',
                );
			}
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
			if ( 0 === strpos( $marketing, 'marketing_flag_' ) ) {
				$marketing_flag_id = str_replace( 'marketing_flag_', '', $marketing );
                $vars['tax_query'][] = array(
                    'taxonomy'  => 'marketing_flag',
                    'terms' => ( (is_array($marketing_flag_id)) ? $marketing_flag_id : array( $marketing_flag_id ) )
                );
            }
        }
        elseif ( 'contact' === $typenow ) 
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $contact_type ) )
            {
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

            $vars = $this->filter_by_date_range($vars, 'date_query');
        }
        elseif ( 'enquiry' === $typenow )
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $status ) && $status != 'all' ) {

                $vars['meta_query'][] = array(
                    'key' => '_status',
					'value' => $status,
                );
            }
            else
            {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                if ( empty( $status ) )
                {
                    $vars['meta_query'][] = array(
                        'key' => '_status',
                        'value' => 'open',
                    );
                }
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $source ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_source',
					'value' => $source,
                );
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $_GET['_office_id'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_office_id',
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    'value' => (int)$_GET['_office_id'],
                );
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $_GET['_negotiator_id'] ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_negotiator_id',
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    'value' => (int)$_GET['_negotiator_id'],
                );
            }

            $vars = $this->filter_by_date_range($vars, 'date_query');
        }
        elseif ( 'appraisal' === $typenow )
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $status ) ) {
                switch ( $status )
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
							'value' => $status,
                        );
                    }
                }
            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $_GET['_negotiator_id'] ) ) 
            {
                $vars['meta_query'][] = array(
                    'key' => '_negotiator_id',
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    'value' => (int)$_GET['_negotiator_id'],
                );
            }

            $vars = $this->filter_by_date_range($vars);
        }
        elseif ( 'viewing' === $typenow ) 
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $status ) ) {

                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query,WordPress.Security.NonceVerification.Recommended -- Read-only status filtering of the paginated core viewing list uses the existing viewing metadata schema; no state change.
                $vars['meta_query'] = add_viewing_status_meta_query( $vars['meta_query'], $status );

            }
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $_GET['_negotiator_id'] ) ) 
            {
                $vars['meta_query'][] = array(
                    'key' => '_negotiator_id',
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    'value' => (int)$_GET['_negotiator_id'],
                );
            }

            $vars = $this->filter_by_date_range($vars);
        }
        elseif ( 'offer' === $typenow ) 
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $status ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_status',
					'value' => $status,
                );
            }

            $vars = $this->filter_by_date_range($vars, '_offer_date_time');
        }
        elseif ( 'sale' === $typenow ) 
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $status ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_status',
					'value' => $status,
                );
            }

            $vars = $this->filter_by_date_range($vars, '_sale_date_time');
        }
        elseif ( 'tenancy' === $typenow )
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $status ) )
            {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                switch ( $status )
                {
                    case 'pending' :
                        $vars['meta_query'][] = array(
                            'key' => '_start_date',
                            'value' => gmdate('Y-m-d'),
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
                                    'value' => gmdate('Y-m-d'),
                                    'type'  => 'date',
                                    'compare' => '<=',
                                ),
                                array(
                                    'key' => '_end_date',
                                    'value' => gmdate('Y-m-d'),
                                    'type'  => 'date',
                                    'compare' => '>=',
                                )
                            ),
                            array(
                                array(
                                    'key' => '_start_date',
                                    'value' => gmdate('Y-m-d'),
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
                            'value' => gmdate('Y-m-d'),
                            'type'  => 'date',
                            'compare' => '<',
                        );
                        break;
                }
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $management_type ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_management_type',
					'value' => $management_type,
                );
            }
        }
        elseif ( 'key_date' === $typenow )
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( ! empty( $key_date_status ) ) {

				$value = $key_date_status;

                switch ($value) {
                    case 'booked':
                    case 'complete':
                    case 'on_hold':
                    case 'cancelled':
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
                            'value' => gmdate("Y-m-d"),
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

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( !empty( $_GET['_key_date_type_id'] ) )
            {
                $vars['meta_query'][] = array(
                    'key' => '_key_date_type_id',
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                    'value' => (int)$_GET['_key_date_type_id'],
                );
            }

            $vars = $this->filter_by_date_range($vars, '_date_due');
        }

        $vars = apply_filters( 'propertyhive_property_filter_query', $vars, $typenow );

        return $vars;
    }

    private function filter_by_date_range($vars, $meta_key = '_start_date_time')
    {
		$date_range_label = $this->get_admin_query_value( '_date_range_label' );
		$date_range_from = $this->get_admin_query_value( '_date_range_from' );
		$date_range_to = $this->get_admin_query_value( '_date_range_to' );

	    if (
		    ! empty( $date_range_label )
		    && ! empty( $date_range_from )
		    && ! empty( $date_range_to )
		    && $date_range_label !== 'Any Time'
		    && DateTime::createFromFormat('Y-m-d', $date_range_from) !== false
		    && DateTime::createFromFormat('Y-m-d', $date_range_to) !== false
	    )
	    {
            if ( $meta_key == 'date_query' )
            {
                $vars['date_query'] = array(
	                    'after' => $date_range_from . ' 00:00:00',
	                    'before' => $date_range_to . ' 23:59:59',
                );
            }
            else
            {
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Add validated date boundaries using the fixed date key selected for this paginated admin post-type list.
    		    $vars['meta_query'] = array_merge($vars['meta_query'], array (
    			    array(
    				    'key' => $meta_key,
				    'value' => $date_range_from,
    				    'type'  => 'date',
    				    'compare' => '>='
    			    ),
    			    array(
    				    'key' => $meta_key,
				    'value' => $date_range_to,
    				    'type'  => 'date',
    				    'compare' => '<='
    			    ),
    		    ));
            }
	    }

	    return $vars;
    }

    public function posts_join( $join, $q ) {
        global $typenow, $wp_query, $wpdb;

        if ( !$q->is_main_query() )
            return $join;

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
        $search = isset( $_GET['s'] ) && is_string( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
        if ( $search === '' ) {
            return $join;
        }

        if ( 'property' === $typenow ) 
        {
            $join .= " 
LEFT JOIN " . $wpdb->postmeta . " AS ph_property_filter_meta_address_concatenated ON " . $wpdb->posts . ".ID = ph_property_filter_meta_address_concatenated.post_id AND ph_property_filter_meta_address_concatenated.meta_key = '_address_concatenated'
LEFT JOIN " . $wpdb->postmeta . " AS ph_property_filter_meta_reference_number ON " . $wpdb->posts . ".ID = ph_property_filter_meta_reference_number.post_id AND ph_property_filter_meta_reference_number.meta_key = '_reference_number'
LEFT JOIN " . $wpdb->postmeta . " AS ph_property_filter_meta_owner_details ON " . $wpdb->posts . ".ID = ph_property_filter_meta_owner_details.post_id AND ph_property_filter_meta_owner_details.meta_key = '_owner_details'
";
        }
        elseif ( 'contact' === $typenow ) 
        {
            $phone_number = '';
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( is_numeric(substr($search, 0, 1)) )
            {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                $phone_number = preg_replace( "/[^0-9,]/", "", $search );
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

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
        $search = isset( $_GET['s'] ) && is_string( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
        if ( $search === '' ) {
            return $where;
        }
        $reference_like = $wpdb->prepare( '%s', $wpdb->esc_like( $search ) . '%' );
        $reference_exact = $wpdb->prepare( '%s', $search );
        $phone_number = '';

        if ( 'property' === $typenow ) 
        {
            $where = preg_replace_callback(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*('(?:\\\\.|[^'\\\\])*')\s*\)/",
                static function( $matches ) use ( $wpdb, $reference_like, $reference_exact, $phone_number ) {
                    return "(
                    (" . $wpdb->posts . ".post_title LIKE " . $matches[1] . ")
                    OR
                    (ph_property_filter_meta_address_concatenated.meta_value LIKE " . $matches[1] . ")
                    OR 
                    (ph_property_filter_meta_reference_number.meta_value LIKE " . $reference_like . ")
                    OR 
                    (ph_property_filter_meta_owner_details.meta_value LIKE " . $matches[1] . ")
                )";
                },
                $where 
            );

            $where = preg_replace(
                "/\s+OR\s+\(\s*" . $wpdb->posts . ".post_excerpt\s+LIKE\s*('(?:\\\\.|[^'\\\\])*')\s*\)/",
                "",
                $where
            );

            $where = preg_replace(
                "/\s+OR\s+\(\s*" . $wpdb->posts . ".post_content\s+LIKE\s*('(?:\\\\.|[^'\\\\])*')\s*\)/",
                "",
                $where
            );
        }
        elseif ( 'contact' === $typenow ) 
        {
            $phone_number = '';
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
            if ( is_numeric(substr($search, 0, 1)) )
            {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list display or query; no state change.
                $phone_number = preg_replace( "/[^0-9,]/", "", $search );
            }

            $where = preg_replace_callback(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*('(?:\\\\.|[^'\\\\])*')\s*\)/",
                static function( $matches ) use ( $wpdb, $reference_like, $reference_exact, $phone_number ) {
                    return "(
                    (" . $wpdb->posts . ".post_title LIKE " . $matches[1] . ")
                    OR
                    (ph_contact_filter_meta_address_concatenated.meta_value LIKE " . $matches[1] . ")
                    OR 
                    (ph_contact_filter_meta_email_address.meta_value LIKE " . $matches[1] . ")
                    " . ( $phone_number != '' ? "OR (ph_contact_filter_meta_telephone_number.meta_value LIKE '%" . $phone_number . "%')" : '' ) . "
                )";
                },
                $where 
            );

            $where = preg_replace(
                "/\s+OR\s+\(\s*" . $wpdb->posts . ".post_excerpt\s+LIKE\s*('(?:\\\\.|[^'\\\\])*')\s*\)/",
                "",
                $where
            );

            $where = preg_replace(
                "/\s+OR\s+\(\s*" . $wpdb->posts . ".post_content\s+LIKE\s*('(?:\\\\.|[^'\\\\])*')\s*\)/",
                "",
                $where
            );
        }
        elseif ( 'appraisal' === $typenow ) 
        {
            $where = preg_replace_callback(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*('(?:\\\\.|[^'\\\\])*')\s*\)/",
                static function( $matches ) use ( $wpdb, $reference_like, $reference_exact, $phone_number ) {
                    return "(
                    (" . $wpdb->posts . ".post_title LIKE " . $matches[1] . ")
                    OR
                    (ph_appraisal_filter_meta_name_number.meta_value LIKE " . $matches[1] . ")
                    OR 
                    (ph_appraisal_filter_meta_street.meta_value LIKE " . $matches[1] . ")
                    OR 
                    (ph_appraisal_filter_meta_2.meta_value LIKE " . $matches[1] . ")
                    OR 
                    (ph_appraisal_filter_meta_3.meta_value LIKE " . $matches[1] . ")
                    OR 
                    (ph_appraisal_filter_meta_4.meta_value LIKE " . $matches[1] . ")
                    OR 
                    (ph_appraisal_filter_meta_postcode.meta_value LIKE " . $matches[1] . ")
                )";
                },
                $where 
            );
        }
        elseif ( 'viewing' === $typenow || 'offer' === $typenow || 'sale' === $typenow || 'tenancy' === $typenow ) 
        {
            $where = preg_replace_callback(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*('(?:\\\\.|[^'\\\\])*')\s*\)/",
                static function( $matches ) use ( $wpdb, $reference_like, $reference_exact, $phone_number ) {
                    return "(
                    (" . $wpdb->posts . ".post_title LIKE " . $matches[1] . ")
                    OR 
                    (ph_property_filter_posts.post_title LIKE " . $matches[1] . ")
                    OR
                    (ph_property_filter_meta_address_concatenated.meta_value LIKE " . $matches[1] . ")
                    OR 
                    (ph_property_filter_meta_reference_number.meta_value = " . $reference_exact . ")
                    OR
                    (ph_applicant_filter_posts.post_title LIKE " . $matches[1] . ")
                )";
                },
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
