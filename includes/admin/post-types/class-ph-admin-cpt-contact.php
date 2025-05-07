<?php
/**
 * Admin functions for the contact post type
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Post Types
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Admin_CPT' ) ) {
	include( 'class-ph-admin-cpt.php' );
}

if ( ! class_exists( 'PH_Admin_CPT_Contact' ) ) :

/**
 * PH_Admin_CPT_Contact Class
 */
class PH_Admin_CPT_Contact extends PH_Admin_CPT {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->type = 'contact';

		// Post title fields
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );

		// Featured image text
		//add_filter( 'gettext', array( $this, 'featured_image_gettext' ) );
		//add_filter( 'media_view_strings', array( $this, 'media_view_strings' ), 10, 2 );

		// Visibility option
		//add_action( 'post_submitbox_misc_actions', array( $this, 'property_data_visibility' ) );

		// Before data updates
		add_action( 'pre_post_update', array( $this, 'pre_post_update' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ) );

		// Admin Columns
		add_filter( 'manage_edit-contact_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_contact_posts_custom_column', array( $this, 'custom_columns' ), 2 );
		add_filter( 'list_table_primary_column', array( $this, 'set_primary_column' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'remove_actions' ), 10, 2 );
		add_filter( 'manage_edit-contact_sortable_columns', array( $this, 'custom_columns_sort' ) );
		add_filter( 'request', array( $this, 'custom_columns_orderby' ) );

		// Sort link
		// add_filter( 'views_edit-property', array( $this, 'default_sorting_link' ) );

		// Prouct filtering
		// add_action( 'restrict_manage_posts', array( $this, 'product_filters' ) );
		// add_filter( 'parse_query', array( $this, 'property_filters_query' ) );

		// Maintain hierarchy of terms
		// add_filter( 'wp_terms_checklist_args', array( $this, 'disable_checked_ontop' ) );

		// Bulk / quick edit
		add_filter( 'bulk_actions-edit-contact', array( $this, 'remove_bulk_actions') );
		add_filter( 'bulk_actions-edit-contact', array( $this, 'add_merge_contacts_action') );
		add_filter( 'handle_bulk_actions-edit-contact', array( $this, 'merge_contacts_redirect'), 10, 3 );
		/*add_action( 'bulk_edit_custom_box', array( $this, 'bulk_edit' ), 10, 2 );
		add_action( 'quick_edit_custom_box',  array( $this, 'quick_edit' ), 10, 2 );
		add_action( 'save_post', array( $this, 'bulk_and_quick_edit_save_post' ), 10, 2 );

		// Uploads
		add_filter( 'upload_dir', array( $this, 'upload_dir' ) );
		add_action( 'media_upload_downloadable_product', array( $this, 'media_upload_downloadable_product' ) );
		add_filter( 'mod_rewrite_rules', array( $this, 'ms_protect_download_rewite_rules' ) );

		// Download permissions
		//add_action( 'propertyhive_process_product_file_download_paths', array( $this, 'process_product_file_download_paths' ), 10, 3 );*/

		add_action( 'admin_notices', array( $this, 'ph_message_admin_notice') );

		add_action( 'manage_posts_extra_tablenav', array( $this, 'generate_applicant_list_action') );

		add_filter( 'admin_url', array( $this, 'append_contact_type_to_add_new_url' ), 10, 2 );


		// Call PH_Admin_CPT constructor
		parent::__construct();
	}

	public function append_contact_type_to_add_new_url( $url, $path ) 
	{
	    if ( $path === 'post-new.php?post_type=contact' ) 
	    {
	    	if ( isset($_GET['_contact_type']) )
	    	{
		        $url .= '&contact_type=' . ph_clean($_GET['_contact_type']);
		    }
	    }
	    return $url;
	}

	public function ph_message_admin_notice()
    {
        $message = '';

        if ( isset($_GET['ph_message']) )
        {
	        switch ( $_GET['ph_message'] )
	        {
	        	case "1": {
	        		$message = __( 'All done! The selected properties will be emailed to the applicant shortly.', 'propertyhive' );
	        		break;
	        	}
	        	case "2": {
	        		$message = __( 'Selected properties successfully removed from applicant matches', 'propertyhive' );
	        		break;
	        	}
	        }
	    }

	    if ( $message != '' )
	    {
	        echo "<div class=\"notice notice-success\">
	            <p>" . esc_html($message) . "</p>
	        </div>";
	    }
    }

	/**
	 * Check if we're editing or adding a contact
	 * @return boolean
	 */
	private function is_editing_contact() {
		if ( ! empty( $_GET['post_type'] ) && 'contact' == $_GET['post_type'] ) {
			return true;
		}
		if ( ! empty( $_GET['post'] ) && 'contact' == get_post_type( (int)$_GET['post'] ) ) {
			return true;
		}
		if ( ! empty( $_REQUEST['post_id'] ) && 'contact' == get_post_type( (int)$_REQUEST['post_id'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Change title boxes in admin.
	 * @param  string $text
	 * @param  object $post
	 * @return string
	 */
	public function enter_title_here( $text, $post ) {
		if ( is_admin() && $post->post_type == 'contact' ) {
			return __( 'Enter Contact Name(s)', 'propertyhive' );
		}

		return $text;
	}

	/**
	 * Some functions, like the term recount, require the visibility to be set prior. Lets save that here.
	 *
	 * @param int $post_id
	 */
	public function pre_post_update( $post_id ) {

	}

	/**
	 * Forces certain product data based on the product's type, e.g. grouped products cannot have a parent.
	 *
	 * @param array $data
	 * @return array
	 */
	public function wp_insert_post_data( $data ) {
		

		return $data;
	}

	/**
	 * Change the columns shown in admin.
	 */
	public function edit_columns( $existing_columns ) {

		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
			$existing_columns = array();
		}

		unset( $existing_columns['title'], $existing_columns['comments'], $existing_columns['date'] );

		$columns = array();
		$columns['cb'] = '<input type="checkbox" />';
		$columns['name'] = __( 'Name', 'propertyhive' );
        $columns['address'] = __( 'Address', 'propertyhive' );
        $columns['contact_details'] = __( 'Contact Details', 'propertyhive' );
        $columns['date'] = __( 'Date Created', 'propertyhive' );

		return array_merge( $columns, $existing_columns );
	}

	/**
	 * Define our custom columns shown in admin.
	 * @param  string $column
	 */
	public function custom_columns( $column ) {
		global $post, $propertyhive, $the_contact;

		if ( empty( $the_contact ) || $the_contact->ID != $post->ID ) 
		{
			$the_contact = new PH_Contact( $post->ID );
		}

		switch ( $column ) {
			case 'name' :
				
				$edit_link        = get_edit_post_link( $post->ID );
				//$title            = _draft_or_post_title();
                $title            = $the_contact->post_title;
                $contact_types    = $the_contact->_contact_types;
                
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . esc_html($title) . '</a>';
				if ( isset( $_GET['_contact_type'] ) && strpos( ph_clean($_GET['_contact_type']), 'applicant' ) !== FALSE && is_array($contact_types) && in_array('applicant', $contact_types) && $the_contact->_hot_applicant == 'yes' )
				{
					echo ' <span style="color:#C00;">(' . esc_html(__( 'Hot Applicant', 'propertyhive' )) . ')</span>';
				}
				echo '</strong>';

				/*if ( $post->post_parent > 0 ) {
					echo '&nbsp;&nbsp;&larr; <a href="'. get_edit_post_link( $post->post_parent ) .'">'. get_the_title( $post->post_parent ) .'</a>';
				}

				// Excerpt view
				if ( isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ) {
					echo apply_filters( 'the_excerpt', $post->post_excerpt );
				}*/

				/*get_inline_data( $post );*/

				/* Custom inline data for propertyhive */
				/*echo '
					<div class="hidden" id="propertyhive_inline_' . $post->ID . '">
						<div class="menu_order">' . $post->menu_order . '</div>
					</div>
				';*/

			 break;
            case 'address' :
                
                echo esc_html($the_contact->get_formatted_full_address());
                
                break;
            case 'contact_details' :
                
                echo 'T: ' . esc_html($the_contact->_telephone_number) . '<br>';
                echo 'E: ' . esc_html($the_contact->_email_address);
                
                break;
			default :
				break;
		}
	}

	public function set_primary_column( $default, $screen ) {
		
		if ( 'edit-contact' === $screen ) 
		{
	        $default = 'name';
	    }

		return $default;
	}

	public function remove_actions($actions, $post) {

    	if ( $post->post_type !== 'contact' )
    	{
        	return $actions;
     	}

	    // Remove 'Quick Edit' link
	    if ( isset($actions['inline hide-if-no-js']) ) 
	    {
	        unset($actions['inline hide-if-no-js']);
	    }

	    if ( isset($actions['trash']) ) 
	    {
	        unset($actions['trash']);
	    }

	    return $actions;
	}

	/**
	 * Remove bulk edit option
	 * @param  array $actions
	 */
	public function remove_bulk_actions( $actions ) {
        unset( $actions['edit'] );
        return $actions;
    }

	/**
	 * Add merge contacts option
	 * @param  array $actions
	 */
	public function add_merge_contacts_action( $actions ) {
		$actions['merge_contacts'] = __('Merge Selected', 'propertyhive');
		return $actions;
	}

	public function merge_contacts_redirect( $redirect_url, $action, $post_ids ) {

		if ( $action == 'merge_contacts' && count($post_ids) > 1 )
		{
			$redirect_url = add_query_arg( 'merge_ids', implode( '|', $post_ids ), admin_url('admin.php?page=ph-merge-duplicate-contacts') );
		}
		return $redirect_url;

	}

	/**
	 * Make contact columns sortable
	 *
	 * https://gist.github.com/906872
	 *
	 * @access public
	 * @param mixed $columns
	 * @return array
	 */
	public function custom_columns_sort( $columns ) {
		$custom = array(
			'name'			=> 'title'
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Product column orderby
	 *
	 * http://scribu.net/wordpress/custom-sortable-columns.html#comment-4732
	 *
	 * @access public
	 * @param mixed $vars
	 * @return array
	 */
	public function custom_columns_orderby( $vars ) {
		/*if ( isset( $vars['orderby'] ) ) {
			if ( 'price' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
					'meta_key' 	=> 'price',
					'orderby' 	=> 'meta_value_num'
				) );
			}
		}*/

		return $vars;
	}

	/**
	 * Product sorting link
	 *
	 * Based on Simple Page Ordering by 10up (http://wordpress.org/extend/plugins/simple-page-ordering/)
	 *
	 * @param array $views
	 * @return array
	 */
	public function default_sorting_link( $views ) {
		global $post_type, $wp_query;

		if ( ! current_user_can('edit_others_pages') ) {
			return $views;
		}

		$class            = ( isset( $wp_query->query['orderby'] ) && $wp_query->query['orderby'] == 'menu_order title' ) ? 'current' : '';
		$query_string     = remove_query_arg(array( 'orderby', 'order' ));
		$query_string     = add_query_arg( 'orderby', urlencode('menu_order title'), $query_string );
		$query_string     = add_query_arg( 'order', urlencode('ASC'), $query_string );
		$views['byorder'] = '<a href="'. $query_string . '" class="' . esc_attr( $class ) . '">' . __( 'Sort Contacts', 'propertyhive' ) . '</a>';

		return $views;
	}

	/**
	 * Maintain term hierarchy when editing a property.
	 * @param  array $args
	 * @return array
	 */
	public function disable_checked_ontop( $args ) {
		if ( 'product_cat' == $args['taxonomy'] ) {
			$args['checked_ontop'] = false;
		}

		return $args;
	}

	/**
	 * Custom bulk edit - form
	 *
	 * @access public
	 * @param mixed $column_name
	 * @param mixed $post_type
	 */
	public function bulk_edit( $column_name, $post_type ) {
		if ( 'price' != $column_name || 'property' != $post_type ) {
			return;
		}

		include( HP()->plugin_path() . '/includes/admin/views/html-bulk-edit-product.php' );
	}

	/**
	 * Custom quick edit - form
	 *
	 * @access public
	 * @param mixed $column_name
	 * @param mixed $post_type
	 */
	public function quick_edit( $column_name, $post_type ) {
		if ( 'price' != $column_name || 'property' != $post_type ) {
			return;
		}

		include( HP()->plugin_path() . '/includes/admin/views/html-quick-edit-contact.php' );
	}

	/**
	 * Quick and bulk edit saving
	 *
	 * @access public
	 * @param int $post_id
	 * @param WP_Post $post
	 * @return int
	 */
	public function bulk_and_quick_edit_save_post( $post_id, $post ) {
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Don't save revisions and autosaves
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return $post_id;
		}

		// Check post type is product
		if ( 'contact' != $post->post_type ) {
			return $post_id;
		}

		// Check user permission
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Check nonces
		if ( ! isset( $_REQUEST['propertyhive_quick_edit_nonce'] ) && ! isset( $_REQUEST['propertyhive_bulk_edit_nonce'] ) ) {
			return $post_id;
		}
		if ( isset( $_REQUEST['propertyhive_quick_edit_nonce'] ) && ! wp_verify_nonce( $_REQUEST['propertyhive_quick_edit_nonce'], 'propertyhive_quick_edit_nonce' ) ) {
			return $post_id;
		}
		if ( isset( $_REQUEST['propertyhive_bulk_edit_nonce'] ) && ! wp_verify_nonce( $_REQUEST['propertyhive_bulk_edit_nonce'], 'propertyhive_bulk_edit_nonce' ) ) {
			return $post_id;
		}

		// Get the product and save
		$contact = get_contact( $post );

		if ( ! empty( $_REQUEST['propertyhive_quick_edit'] ) ) {
			$this->quick_edit_save( $post_id, $contact );
		} else {
			$this->bulk_edit_save( $post_id, $contact );
		}

		// Clear transient
		//ph_delete_contact_transients( $post_id );

		return $post_id;
	}

	/**
	 * Quick edit
	 */
	private function quick_edit_save( $post_id, $contact ) {
		
		global $wpdb;

		/*
		// Save fields
		if ( isset( $_REQUEST['_availability'] ) ) {
			update_post_meta( $post_id, '_availability', ph_clean( $_REQUEST['_availability'] ) );
		}*/

		do_action( 'propertyhive_contact_quick_edit_save', $contact );
	}

	/**
	 * Bulk edit
	 */
	public function bulk_edit_save( $post_id, $contact ) {

		/*
		// Save fields
		if ( ! empty( $_REQUEST['_availability'] ) ) {
			update_post_meta( $post_id, '_availability', ph_clean( $_REQUEST['_availability'] ) );
		}*/

		do_action( 'propertyhive_contact_bulk_edit_save', $contact );
	}

	/**
	 * Filter the directory for uploads.
	 *
	 * @param array $pathdata
	 * @return array
	 */
	public function upload_dir( $pathdata ) {
		// Change upload dir for downloadable files
		if ( isset( $_POST['type'] ) && 'downloadable_product' == $_POST['type'] ) {
			if ( empty( $pathdata['subdir'] ) ) {
				$pathdata['path']   = $pathdata['path'] . '/propertyhive_uploads';
				$pathdata['url']    = $pathdata['url']. '/propertyhive_uploads';
				$pathdata['subdir'] = '/propertyhive_uploads';
			} else {
				$new_subdir = '/propertyhive_uploads' . $pathdata['subdir'];

				$pathdata['path']   = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['path'] );
				$pathdata['url']    = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['url'] );
				$pathdata['subdir'] = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['subdir'] );
			}
		}

		return $pathdata;
	}

	public function generate_applicant_list_action( $which )
	{
		global $typenow, $wp_query;

		if ( 'contact' != $typenow ) {
			return;
		}

		if ( $which == 'top' && isset($_GET['_contact_type']) && $_GET['_contact_type'] == 'applicant' )
		{
			echo '<div class="alignleft actions"><a href="' . esc_url(admin_url('admin.php?page=ph-generate-applicant-list')) . '" id="generate_applicant_list_button" class="button">' . esc_html(__( 'Generate Applicant List', 'propertyhive' )) . '</a></div>';
		}
	}
}

endif;

return new PH_Admin_CPT_Contact();
