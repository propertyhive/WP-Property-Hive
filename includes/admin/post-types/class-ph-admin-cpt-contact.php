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
		// add_filter( 'manage_edit-property_sortable_columns', array( $this, 'custom_columns_sort' ) );
		// add_filter( 'request', array( $this, 'custom_columns_orderby' ) );

		// Sort link
		// add_filter( 'views_edit-property', array( $this, 'default_sorting_link' ) );

		// Prouct filtering
		// add_action( 'restrict_manage_posts', array( $this, 'product_filters' ) );
		// add_filter( 'parse_query', array( $this, 'property_filters_query' ) );

		// Enhanced search
		add_filter( 'posts_search', array( $this, 'contact_search' ) );

		// Maintain hierarchy of terms
		// add_filter( 'wp_terms_checklist_args', array( $this, 'disable_checked_ontop' ) );

		// Bulk / quick edit
		add_filter( 'bulk_actions-edit-contact', array( $this, 'remove_bulk_actions') );
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

		// Call PH_Admin_CPT constructor
		parent::__construct();
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
	            <p>" . $message . "</p>
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
                
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . $title.'</a></strong>';

				/*if ( $post->post_parent > 0 ) {
					echo '&nbsp;&nbsp;&larr; <a href="'. get_edit_post_link( $post->post_parent ) .'">'. get_the_title( $post->post_parent ) .'</a>';
				}

				// Excerpt view
				if ( isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ) {
					echo apply_filters( 'the_excerpt', $post->post_excerpt );
				}*/

				// Get actions
				$actions = array();
                
				if ( $can_edit_post && 'trash' != $post->post_status ) {
					$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item', 'propertyhive' ) ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>';
				}
				if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
					if ( 'trash' == $post->post_status ) {
						$actions['untrash'] = '<a title="' . esc_attr( __( 'Restore this item from the Trash', 'propertyhive' ) ) . '" href="' . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . '">' . __( 'Restore', 'propertyhive' ) . '</a>';
					} elseif ( EMPTY_TRASH_DAYS ) {
						//$actions['trash'] = '<a class="submitdelete" title="' . esc_attr( __( 'Move this item to the Trash', 'propertyhive' ) ) . '" href="' . get_delete_post_link( $post->ID ) . '">' . __( 'Trash', 'propertyhive' ) . '</a>';
					}

					if ( 'trash' == $post->post_status || ! EMPTY_TRASH_DAYS ) {
						$actions['delete'] = '<a class="submitdelete" title="' . esc_attr( __( 'Delete this item permanently', 'propertyhive' ) ) . '" href="' . get_delete_post_link( $post->ID, '', true ) . '">' . __( 'Delete Permanently', 'propertyhive' ) . '</a>';
					}
				}
				if ( $post_type_object->public ) {
					if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) {
						if ( $can_edit_post )
							$actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'propertyhive' ), $title ) ) . '" rel="permalink">' . __( 'Preview', 'propertyhive' ) . '</a>';
					} elseif ( 'trash' != $post->post_status ) {
						$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'propertyhive' ), $title ) ) . '" rel="permalink">' . __( 'View', 'propertyhive' ) . '</a>';
					}
				}

				$actions = apply_filters( 'post_row_actions', $actions, $post );

				echo '<div class="row-actions">';

				$i = 0;
				$action_count = sizeof($actions);

				foreach ( $actions as $action => $link ) {
					++$i;
					( $i == $action_count ) ? $sep = '' : $sep = ' | ';
					echo '<span class="' . $action . '">' . $link . $sep . '</span>';
				}
				echo '</div>';

				/*get_inline_data( $post );*/

				/* Custom inline data for propertyhive */
				/*echo '
					<div class="hidden" id="propertyhive_inline_' . $post->ID . '">
						<div class="menu_order">' . $post->menu_order . '</div>
					</div>
				';*/

			 break;
            case 'address' :
                
                echo $the_contact->get_formatted_full_address();
                
                break;
            case 'contact_details' :
                
                echo 'T: ' . $the_contact->_telephone_number . '<br>';
                echo 'E: ' . $the_contact->_email_address;
                
                break;
			default :
				break;
		}
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
	 * Search by email and phone number
	 * Phone numbers are stripped of any none numeric or comma charactors
	 *
	 * @param string $where
	 * @return string
	 */
	public function contact_search( $where ) {

		global $pagenow, $wpdb, $wp;
		
		if ( 'edit.php' != $pagenow || ! is_search() || ! isset( $wp->query_vars['s'] ) || 'contact' != $wp->query_vars['post_type'] ) {
			return $where;
		}

		if ( trim($wp->query_vars['s']) == '' )
		{
			return $where;
		}

		$search_ids = array();
		$terms      = explode( ',', $wp->query_vars['s'] );

		foreach ( $terms as $term )
		{
			if ( is_numeric( $term ) )
			{
				$search_ids[] = $term;
			}

            $phone_number = preg_replace( "/[^0-9,]/", "", $term );

			// Attempt to get an ID by searching for phone and email address
			$query = $wpdb->prepare( 
				"SELECT 
					ID
				FROM 
					{$wpdb->posts} 
				INNER JOIN {$wpdb->postmeta} AS mt1 ON {$wpdb->posts}.ID = mt1.post_id
				WHERE 
					(
						(mt1.meta_key='_telephone_number_clean' AND mt1.meta_value LIKE NULLIF(%s,'%%%'))
						OR
						(mt1.meta_key='_email_address' AND mt1.meta_value LIKE %s)
					)
				AND 
					post_type='contact'
				GROUP BY ID
				",
				'%' . $wpdb->esc_like( ph_clean( ph_clean_telephone_number( $term ) ) ) . '%',
				'%' . $wpdb->esc_like( ph_clean( $term ) ) . '%'
			);

			$search_posts = $wpdb->get_results( $query );
			$search_posts = wp_list_pluck( $search_posts, 'ID' );

			if ( sizeof( $search_posts ) > 0 )
			{
				$search_ids = array_merge( $search_ids, $search_posts );
			}
		}
		$search_ids = array_filter( array_unique( array_map( 'absint', $search_ids ) ) );
		if ( sizeof( $search_ids ) > 0 ) 
		{
			$where = str_replace( 'AND (((', "AND ( ({$wpdb->posts}.ID IN (" . implode( ',', $search_ids ) . ")) OR ((", $where );
		}
		return $where;
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
	 * Show a category filter box
	 */
	public function propertyhive_filters() {
		global $typenow, $wp_query;

		if ( 'contact' != $typenow ) {
			return;
		}


		echo apply_filters( 'propertyhive_contact_filters', $output );
	}

	/**
	 * Filter the contacts in admin based on options
	 *
	 * @param mixed $query
	 */
	public function contact_filters_query( $query ) {
		global $typenow, $wp_query;

		if ( 'contact' == $typenow ) {
			
		}
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

		do_action( 'propertyhive_product_quick_edit_save', $contact );
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
			echo '<div class="alignleft actions"><a href="' . admin_url('admin.php?page=ph-generate-applicant-list') . '" id="generate_applicant_list_button" class="button">' . __( 'Generate Applicant List', 'propertyhive' ) . '</a></div>';
		}
	}
}

endif;

return new PH_Admin_CPT_Contact();
