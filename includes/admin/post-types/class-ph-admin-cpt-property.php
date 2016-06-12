<?php
/**
 * Admin functions for the property post type
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

if ( ! class_exists( 'PH_Admin_CPT_Property' ) ) :

/**
 * PH_Admin_CPT_Property Class
 */
class PH_Admin_CPT_Property extends PH_Admin_CPT {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->type = 'property';

		// Admin notices
		add_action( 'admin_notices', array( $this, 'property_admin_notices') );

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
		add_filter( 'manage_edit-property_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_property_posts_custom_column', array( $this, 'custom_columns' ), 2 );
		/*add_filter( 'manage_edit-property_sortable_columns', array( $this, 'custom_columns_sort' ) );
		add_filter( 'request', array( $this, 'custom_columns_orderby' ) );

		// Sort link
		add_filter( 'views_edit-property', array( $this, 'default_sorting_link' ) );

		// Prouct filtering
		add_action( 'restrict_manage_posts', array( $this, 'product_filters' ) );
		add_filter( 'parse_query', array( $this, 'property_filters_query' ) );

		// Enhanced search
		add_filter( 'posts_search', array( $this, 'product_search' ) );

		// Maintain hierarchy of terms
		add_filter( 'wp_terms_checklist_args', array( $this, 'disable_checked_ontop' ) );*/

		// Bulk / quick edit
		add_filter( 'bulk_actions-edit-property', array( $this, 'remove_bulk_actions') );
		/*add_action( 'bulk_edit_custom_box', array( $this, 'bulk_edit' ), 10, 2 );
		add_action( 'quick_edit_custom_box',  array( $this, 'quick_edit' ), 10, 2 );
		add_action( 'save_post', array( $this, 'bulk_and_quick_edit_save_post' ), 10, 2 );*/

		// Uploads
		add_filter( 'upload_dir', array( $this, 'upload_dir' ) );
		add_action( 'media_upload_downloadable_product', array( $this, 'media_upload_downloadable_product' ) );
		//add_filter( 'mod_rewrite_rules', array( $this, 'ms_protect_download_rewite_rules' ) );

		// Download permissions
		//add_action( 'propertyhive_process_product_file_download_paths', array( $this, 'process_product_file_download_paths' ), 10, 3 );*/

		// Call PH_Admin_CPT constructor
		parent::__construct();
	}

	/**
     * Output admin notices relating to property
     */
    public function property_admin_notices() 
    {
    	global $post;

		$screen = get_current_screen();
        if ($screen->id == 'property' && $post->post_type == 'property' && $post->post_parent != 0 && $post->post_parent != '')
        {
            $message = __( "This property is a commercial unit belonging to", 'propertyhive' ) . ' <a href="' . get_edit_post_link( $post->post_parent ) . '">' . get_the_title($post->post_parent) . '</a>';
            echo "<div class=\"notice notice-info\"> <p>$message</p></div>";
        }
    }

	/**
	 * Check if we're editing or adding a property
	 * @return boolean
	 */
	private function is_editing_property() {
		if ( ! empty( $_GET['post_type'] ) && 'property' == $_GET['post_type'] ) {
			return true;
		}
		if ( ! empty( $_GET['post'] ) && 'property' == get_post_type( $_GET['post'] ) ) {
			return true;
		}
		if ( ! empty( $_REQUEST['post_id'] ) && 'property' == get_post_type( $_REQUEST['post_id'] ) ) {
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
		if ( is_admin() && $post->post_type == 'property' ) {
			return __( 'Enter Display Address', 'propertyhive' );
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
		$columns['thumb'] = '<span class="ph-image tips" data-tip="' . __( 'Image', 'propertyhive' ) . '">' . __( 'Image', 'propertyhive' ) . '</span>';

		$columns['address'] = __( 'Address', 'propertyhive' );

		if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
        {
            $columns['size'] = __( 'Size', 'propertyhive' );
        }

		$columns['price'] = __( 'Price', 'propertyhive' );

		$columns['status'] = __( 'Marketing Status', 'propertyhive' );
        
        $columns['owner'] = __( 'Owner / Landlord', 'propertyhive' );
        
        $columns['negotiator_office'] = __( 'Neg / Office', 'propertyhive' );

		return array_merge( $columns, $existing_columns );
	}

	/**
	 * Define our custom columns shown in admin.
	 * @param  string $column
	 */
	public function custom_columns( $column ) {
		global $post, $propertyhive, $the_property;

		if ( empty( $the_property ) || $the_property->ID != $post->ID ) 
		{
			$the_property = new PH_Property( $post->ID );
		}

		switch ( $column ) {
			case 'thumb' :
                
                $thumb_src = $the_property->get_main_photo_src();
                
				echo '<a href="' . get_edit_post_link( $post->ID ) . '">';
				if ($thumb_src !== FALSE)
				{
				    echo '<img src="' . $thumb_src . '" alt="" width="50">';
                }
                else
                {
                    // placeholder image
                }
                echo '</a>';
				break;
			case 'address' :
				
				$edit_link        = get_edit_post_link( $post->ID );
				//$title            = _draft_or_post_title();
                $title            = $the_property->get_formatted_summary_address();
                if ( empty($title) )
                {
                    $title = __( '(no title)' );
                }
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . $title.'</a></strong>';

				// Excerpt view
				if ( isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ) {
					echo apply_filters( 'the_excerpt', $post->post_excerpt );
				}

				// Get actions
				$actions = array();

                $actions['ref'] = 'Ref: ' . $the_property->_reference_number;

				//$actions['id'] = 'ID: ' . $post->ID;
                
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
			case 'size' :
			    
                $floor_area = $the_property->get_formatted_floor_area();
                if ( $floor_area != '' )
                {
                	echo 'Floor Area: ' . $floor_area . '<br>';
            	}
                $site_area = $the_property->get_formatted_site_area();
                if ( $site_area != '' )
                {
                	echo 'Site Area: ' . $site_area;
            	}

            	if ( $floor_area == '' && $site_area == '' )
            	{
            		echo '-';
            	}
                
				break;
			case 'price' :
			    
                $price = $the_property->get_formatted_price();
                if ( $price == '' )
                {
                	$price = '-';
                }
                echo $price;
                
				break;
			case 'status' :

            	$term_list = wp_get_post_terms($post->ID, 'availability', array("fields" => "names"));
            
	            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
	            {
	               echo $term_list[0]. '<br>';
	            }

            	if (isset($the_property->_on_market) && $the_property->_on_market == 'yes')
            	{
            		echo 'On The Market';
            	}
            	else
            	{
            		echo 'Not On The Market';
            	}
            	
            	if (isset($the_property->_featured) && $the_property->_featured == 'yes')
            	{
            		echo '<br>Featured';
            	}
                
				break;
            case 'owner' :
                
                $owner_contact_id = $the_property->_owner_contact_id;
                if ($owner_contact_id !='' && $owner_contact_id != 0)
                {
	                echo get_the_title($owner_contact_id) . '<br>';
	                echo '<div class="row-actions">';
	                echo 'T: ' . get_post_meta($owner_contact_id, '_telephone_number', TRUE) . '<br>';
	                echo 'E: ' . get_post_meta($owner_contact_id, '_email_address', TRUE);
	                echo '</div>';
	            }
                break;
            case 'negotiator_office' :
                
                $user_info = get_userdata($the_property->_negotiator_id);
                
                if ($user_info !== FALSE)
                {
                    echo $user_info->display_name . '<br>';
                }
                
                if ($the_property->_office_id != '')
                {
                    echo get_the_title($the_property->_office_id);
                }
                
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
	 * Make property columns sortable
	 *
	 * https://gist.github.com/906872
	 *
	 * @access public
	 * @param mixed $columns
	 * @return array
	 */
	public function custom_columns_sort( $columns ) {
		$custom = array(
			'price'			=> 'price',
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
		if ( isset( $vars['orderby'] ) ) {
			if ( 'price' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
					'meta_key' 	=> '_price',
					'orderby' 	=> 'meta_value_num'
				) );
			}
		}

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
		$views['byorder'] = '<a href="'. $query_string . '" class="' . esc_attr( $class ) . '">' . __( 'Sort Properties', 'propertyhive' ) . '</a>';

		return $views;
	}

	/**
	 * Show a category filter box
	 */
	public function propertyhive_filters() {
		global $typenow, $wp_query;

		if ( 'property' != $typenow ) {
			return;
		}

		echo apply_filters( 'propertyhive_property_filters', $output );
	}

	/**
	 * Filter the products in admin based on options
	 *
	 * @param mixed $query
	 */
	public function property_filters_query( $query ) {
		global $typenow, $wp_query;

		if ( 'property' == $typenow ) {

			/*if ( isset( $query->query_vars['product_type'] ) ) {
				// Subtypes
				if ( 'downloadable' == $query->query_vars['product_type'] ) {
					$query->query_vars['product_type']  = '';
					$query->query_vars['meta_value']    = 'yes';
					$query->query_vars['meta_key']      = '_downloadable';
				} elseif ( 'virtual' == $query->query_vars['product_type'] ) {
					$query->query_vars['product_type']  = '';
					$query->query_vars['meta_value']    = 'yes';
					$query->query_vars['meta_key']      = '_virtual';
				}
			}

			// Categories
			if ( isset( $_GET['product_cat'] ) && '0' == $_GET['product_cat'] ) {
				$query->query_vars['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => get_terms( 'product_cat', array( 'fields' => 'ids' ) ),
					'operator' => 'NOT IN'
				);
			}*/
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
		if ( 'price' != $column_name || 'product' != $post_type ) {
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
		if ( 'price' != $column_name || 'product' != $post_type ) {
			return;
		}

		include( HP()->plugin_path() . '/includes/admin/views/html-quick-edit-property.php' );
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
		if ( 'property' != $post->post_type ) {
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
		$property = get_property( $post );

		if ( ! empty( $_REQUEST['propertyhive_quick_edit'] ) ) {
			$this->quick_edit_save( $post_id, $property );
		} else {
			$this->bulk_edit_save( $post_id, $property );
		}

		// Clear transient
		//ph_delete_property_transients( $post_id );

		return $post_id;
	}

	/**
	 * Quick edit
	 */
	private function quick_edit_save( $post_id, $property ) {
		global $wpdb;

		/*

		// Save fields
		if ( isset( $_REQUEST['_address_name_number'] ) ) {
			update_post_meta( $post_id, '_address_name_number', ph_clean( $_REQUEST['_address_name_number'] ) );
		}*/

		do_action( 'propertyhive_product_quick_edit_save', $property );
	}

	/**
	 * Bulk edit
	 */
	public function bulk_edit_save( $post_id, $property ) {

		// Save fields
		/*
		if ( isset( $_REQUEST['_address_name_number'] ) ) {
			update_post_meta( $post_id, '_address_name_number', ph_clean( $_REQUEST['_address_name_number'] ) );
		}*/

		do_action( 'propertyhive_property_bulk_edit_save', $property );
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
}

endif;

return new PH_Admin_CPT_Property();
