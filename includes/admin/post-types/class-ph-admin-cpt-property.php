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
		add_filter( 'list_table_primary_column', array( $this, 'set_primary_column' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'remove_actions' ), 10, 2 );
		add_filter( 'manage_edit-property_sortable_columns', array( $this, 'custom_columns_sort' ) );
		add_filter( 'request', array( $this, 'custom_columns_orderby' ) );

		// Sort link
		add_filter( 'views_edit-property', array( $this, 'remove_mine' ) );

		// Prouct filtering
		/*add_action( 'restrict_manage_posts', array( $this, 'property_filters' ) );
		add_filter( 'parse_query', array( $this, 'property_filters_query' ) );*/

		// Maintain hierarchy of terms
		/*add_filter( 'wp_terms_checklist_args', array( $this, 'disable_checked_ontop' ) );*/

		// Bulk / quick edit
		add_action( 'bulk_edit_custom_box', array( $this, 'bulk_edit' ), 10, 2 );
		/*add_action( 'quick_edit_custom_box',  array( $this, 'quick_edit' ), 10, 2 );*/
		add_action( 'save_post', array( $this, 'bulk_and_quick_edit_save_post' ), 10, 2 );

		// Uploads
		add_filter( 'upload_dir', array( $this, 'upload_dir' ) );
		add_action( 'media_upload_downloadable_product', array( $this, 'media_upload_downloadable_product' ) );
		//add_filter( 'mod_rewrite_rules', array( $this, 'ms_protect_download_rewite_rules' ) );

		// Download permissions
		//add_action( 'propertyhive_process_product_file_download_paths', array( $this, 'process_product_file_download_paths' ), 10, 3 );*/

		add_action( 'load-edit.php', array( $this, 'render_blank' ) );

		// Call PH_Admin_CPT constructor
		parent::__construct();
	}

	public function render_blank()
	{
		// Check if we are on the 'property' post type listing page
	    $screen = get_current_screen();
	    if ($screen->post_type !== 'property') {
	        return;
	    }

	    // Check if there are any properties
	    $query = new WP_Query([
	        'post_type'      => 'property',
	        'post_status'    => 'any',
	        'posts_per_page' => 1
	    ]);

	    if ($query->have_posts()) {
	        return; // Let the default table load
	    }

	    // No properties found, replace the table with a custom splash screen
	    add_filter('views_edit-property', function ($views) {
	        // Clear default view filters (All, Published, etc.)
	        return [];
	    });

	    add_action('manage_posts_extra_tablenav', function ($which) {
	    	if ( $which == 'bottom' )
	    	{
	    		return;
	    	}
	        echo '
	        <div style="padding: 50px; text-align: center;">

	        	<img style="max-width:400px; margin-bottom:45px;" src="' . esc_url(PH()->plugin_url()) . '/assets/images/no-properties.png" alt="' . esc_attr( __( 'Your property journey begins here!', 'propertyhive' ) ) . '">

	            <h2 style="font-size:1.8em; color:#444; margin:0 0 1.5em">' . esc_html( __( 'Your property journey begins here!', 'propertyhive' ) ) . '</h2>
	            <a href="' . esc_url(admin_url('post-new.php?post_type=property&tutorial=yes')) . '" class="button button-primary button-hero" style="font-size:1.2em; padding:0 24px;">
	                ' . esc_html( __( 'Add Your First Property', 'propertyhive' ) ) . '
	            </a>&nbsp;
	            <a href="' . esc_url(admin_url('admin.php?page=ph-settings&tab=demo_data')) . '" class="button button-hero" style="font-size:1.2em; padding:0 24px;">
	                ' . esc_html( __( 'Create Demo Data', 'propertyhive' ) ) . '
	            </a>&nbsp; ';

	            if ( apply_filters( 'propertyhive_no_properties_property_import_button', true ) === true )
	            {
		            $button_to_output = false;

		            if ( class_exists('PH_Property_Import') )
					{
						// Already activated. Check can be used
						if ( apply_filters( 'propertyhive_add_on_can_be_used', true, 'propertyhive-property-import' ) === true )
			        	{
			        		$button_to_output = 'normal';
						}
					}

					if ( !$button_to_output )
					{
						$license_type = get_option( 'propertyhive_license_type', '' );
						
						switch ( $license_type )
						{
							case "": { $button_to_output = 'dummy'; break; }
							case "pro": 
							{
								if ( PH()->license->is_valid_pro_license_key() )
								{
									// It should never get this far if import add on already activated, that's why show activate page
									$button_to_output = 'activate'; 
								}
								else
								{
									$button_to_output = 'dummy'; 
								}
								break; 
							}
						}
					}

					// only show dummy button to administrators
					if ( $button_to_output == 'dummy' || $button_to_output == 'activate' )
					{
						if ( !current_user_can( 'manage_options' ) ) 
						{  
							// not an admin
							$button_to_output = false;
						}
					}

					// only show dummy button to people with it installed eyond 1st nov 2023 (when PRO was introduced)
					if ( $button_to_output == 'dummy' )
					{
						$propertyhive_install_timestamp = get_option( 'propertyhive_install_timestamp', '' );
					    if ( !empty($propertyhive_install_timestamp) )
					    {
					    	$november_first_2023 = strtotime('2023-11-01 00:00:00');
					    	if ( $propertyhive_install_timestamp < $november_first_2023 )
					    	{
					    		$button_to_output = false;
					    	}
					    }
					}

					switch ( $button_to_output )
					{
						case "normal":
						{
							echo '<a href="' . esc_url(admin_url('admin.php?page=propertyhive_import_properties')) . '" class="button button-hero" style="font-size:1.2em; padding:0 24px;">
				                ' . esc_html( __( 'Automatically Import Properties', 'propertyhive' ) ) . '
				            </a>';
							break;
						}
						case "dummy":
						{
							echo '<a href="' . esc_url(admin_url('admin.php?page=ph-import_properties_dummy')) . '" class="button button-hero" style="font-size:1.2em; padding:0 24px;">
				                ' . esc_html( __( 'Automatically Import Properties', 'propertyhive' ) ) . ' <span style="color:#FFF; font-size:10px; font-weight:500; border-radius:12px; padding:2px 8px; letter-spacing:1px; background:#00a32a;">PRO</span>
				            </a>';
							break;
						}
						case "activate":
						{
							echo '<a href="' . esc_url(admin_url('admin.php?page=ph-settings&tab=features&profilter=import')) . '" class="button button-hero" style="font-size:1.2em; padding:0 24px;">
				                ' . esc_html( __( 'Activate Property Imports', 'propertyhive' ) ) . '
				            </a>';
							break;
						}
					}
	            }

	        echo '</div>';
	    }, 99);

	    // Remove the filters
	    remove_all_actions('restrict_manage_posts');

	    // Hide the search box
	    add_action('admin_head', function () {
	        echo '<style>
	        	.page-title-action,
	        	.wrap .wp-list-table,
	            .search-box { display: none !important; }
	        </style>';
	    });

	    add_filter('bulk_actions-edit-property', function ($bulk_actions) {
		    // Clear all bulk actions
		    return [];
		});
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
        	$property = new PH_Property((int)$post->post_parent);
            $message = __( "This property is a unit belonging to", 'propertyhive' ) . ' <a href="' . esc_url(get_edit_post_link( $post->post_parent )) . '">' . esc_html($property->get_formatted_full_address()) . '</a>';
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
		if ( ! empty( $_GET['post'] ) && 'property' == get_post_type( (int)$_GET['post'] ) ) {
			return true;
		}
		if ( ! empty( $_REQUEST['post_id'] ) && 'property' == get_post_type( (int)$_REQUEST['post_id'] ) ) {
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

		$commercial_department_active = false;
		if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
		{
			$commercial_department_active = true;
		}
		else
		{
			$custom_departments = ph_get_custom_departments();
			if ( !empty($custom_departments) )
			{
				foreach ( $custom_departments as $key => $department )
				{
					if ( isset($department['based_on']) && $department['based_on'] == 'commercial' )
					{
						$commercial_department_active = true;
					}
				}
			}
		}
		if ( $commercial_department_active )
        {
            $columns['size'] = __( 'Size', 'propertyhive' );
        }

		$columns['price'] = __( 'Price', 'propertyhive' );

		$columns['status'] = __( 'Marketing Status', 'propertyhive' );
        
        if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
        {
        	$columns['owner'] = __( 'Owner / Landlord', 'propertyhive' );
        }
        
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
                
				echo '<a href="' . esc_url(get_edit_post_link( $post->ID )) . '">';
				if ($thumb_src !== FALSE)
				{
				    echo '<img src="' . esc_url($thumb_src) . '" alt="" width="50">';
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
                    $title = __( '(no address entered)', 'propertyhive' );
                }
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . esc_html($title) . '</a>';

				$post_status = get_post_status( $post->ID );
				$post_title_output = '';
				if ( $post_status == 'draft' || $post_status == 'private' )
				{
					$post_title_output = ucfirst($post_status);
				}
				$post_title_output = apply_filters( 'propertyhive_admin_property_column_post_address_output', $post_title_output );
				if ( $post_title_output != '' )	
				{
					echo ' - ' . esc_html($post_title_output);
				}

				echo '</strong>';

				// Excerpt view
				if ( isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ) {
					echo apply_filters( 'the_excerpt', $post->post_excerpt );
				}

				if ( $the_property->bedrooms != '' || $the_property->property_type != '' || $the_property->reference_number != '' )
				{
					echo '<br>';

					$details = array();

					if ( $the_property->bedrooms != '' || $the_property->property_type != '' )
					{
						$details[] = ( 
							( 
								( 
									$the_property->department == 'residential-sales' || 
									$the_property->department == 'residential-lettings' ||
									ph_get_custom_department_based_on($the_property->department) == 'residential-sales' || 
									ph_get_custom_department_based_on($the_property->department) == 'residential-lettings'
								)
								&& 
								$the_property->bedrooms != '' 
							) ? esc_html($the_property->bedrooms . ' ' . __( 'bedroom', 'propertyhive' )) . ' ' : '' 
						) . esc_html($the_property->property_type);
					}

					if ( $the_property->reference_number )
					{
						$details[] = '<span style="opacity:0.6">' . esc_html(__( 'Ref', 'propertyhive' )) . ': ' . esc_html($the_property->reference_number) . '</span>';
					}

					$details = apply_filters( 'propertyhive_admin_property_column_address_details', $details, $post->ID );

					echo implode("<br>", $details);
				}

				get_inline_data( $post );

				/* Custom inline data for propertyhive */
				/*echo '
					<div class="hidden" id="propertyhive_inline_' . $post->ID . '">
						<div class="on_market">' . $the_property->on_market . '</div>
						<div class="featured">' . $the_property->featured . '</div>
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
                else
                {
                	if ( 
                		( $the_property->_department == 'residential-sales' || ph_get_custom_department_based_on($the_property->_department) == 'residential-sales' )
                		&& 
                		$the_property->price_qualifier != '' 
                	)
                	{
                		$price .= '<br>' . esc_html($the_property->price_qualifier);
                	}
                }
                echo $price;
                
				break;
			case 'status' :

            	$term_list = wp_get_post_terms($post->ID, 'availability', array("fields" => "names"));
            
	            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
	            {
	               echo esc_html($term_list[0]). '<br>';
	            }

            	if (isset($the_property->_on_market) && $the_property->_on_market == 'yes')
            	{
            		echo esc_html(__( 'On The Market', 'propertyhive' ));
            	}
            	else
            	{
            		echo esc_html(__( 'Not On The Market', 'propertyhive' ));
            	}
            	
            	if (isset($the_property->_featured) && $the_property->_featured == 'yes')
            	{
            		echo '<br>' . esc_html(__( 'Featured', 'propertyhive' ));
            	}

            	$marketing_flags = $the_property->marketing_flag;
            	if ( $marketing_flags != '' )
            	{
            		echo '<br>' . implode( "<br>", explode( ",", esc_html($marketing_flags) ) );
            	}
                
				break;
            case 'owner' :
                
                $owner_contact_ids = $the_property->_owner_contact_id;
                if ( 
                	( !is_array($owner_contact_ids) && $owner_contact_ids != '' && $owner_contact_ids != 0 ) 
                	||
                	( is_array($owner_contact_ids) && !empty($owner_contact_ids) )
                )
                {
                	if ( !is_array($owner_contact_ids) )
                	{
                		$owner_contact_ids = array($owner_contact_ids);
                	}

                	foreach ( $owner_contact_ids as $owner_contact_id )
                	{
		                echo esc_html(get_the_title($owner_contact_id)) . '<br>';
		                if ( count($owner_contact_ids) == 1 )
		                {
			                echo '<div class="row-actions">';
			                echo 'T: ' . esc_html(get_post_meta($owner_contact_id, '_telephone_number', TRUE)) . '<br>';
			                echo 'E: ' . esc_html(get_post_meta($owner_contact_id, '_email_address', TRUE));
			                echo '</div>';
			            }
		            }
	            }
	            else
	            {
	            	echo '-';
	            }
                break;
            case 'negotiator_office' :
                
                $user_info = get_userdata($the_property->_negotiator_id);
                
                if ($user_info !== FALSE)
                {
                    echo esc_html($user_info->display_name) . '<br>';
                }
                
                if ($the_property->_office_id != '')
                {
                    echo esc_html(get_the_title($the_property->_office_id));
                }
                
                break;
			default :
				break;
		}
	}

	public function set_primary_column( $default, $screen ) {
		
		if ( 'edit-property' === $screen ) 
		{
	        $default = 'address';
	    }

		return $default;
	}

	public function remove_actions($actions, $post) {

    	if ( $post->post_type !== 'property' )
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
			'price'			=> '_price_actual',
			'size'			=> '_floor_area_from_sqft'
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Property column orderby
	 *
	 * @access public
	 * @param mixed $vars
	 * @return array
	 */
	public function custom_columns_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) ) {
			if ( '_price_actual' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
					'meta_key' 	=> '_price_actual',
					'orderby' 	=> 'meta_value_num'
				) );
			}
			elseif ( '_floor_area_from_sqft' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
					'meta_key' 	=> '_floor_area_from_sqft',
					'orderby' 	=> 'meta_value_num'
				) );
			}
		}

		return $vars;
	}

	/**
	 * Remove 'Mine' view option
	 *
	 * @param array $views
	 * @return array
	 */
	public function remove_mine( $views ) {
		global $post_type, $wp_query;

		if ( ! current_user_can('edit_others_pages') ) {
			return $views;
		}

		if( isset( $views['mine'] ) )
	        unset( $views['mine'] );

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

		include( PH()->plugin_path() . '/includes/admin/views/html-bulk-edit-property.php' );
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

		include( PH()->plugin_path() . '/includes/admin/views/html-quick-edit-property.php' );
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

		do_action( 'propertyhive_property_quick_edit_save', $property );
	}

	/**
	 * Bulk edit
	 */
	public function bulk_edit_save( $post_id, $property ) {

		// Save fields
		if ( ! empty( $_REQUEST['_on_market'] ) ) 
		{
			$on_market = ph_clean( $_REQUEST['_on_market'] );
			if ( $_REQUEST['_on_market'] != 'yes' ) { $on_market = ''; } // can only be 'yes' or blank
			update_post_meta( $post_id, '_on_market', ph_clean( $on_market ) );
		}

		if ( ! empty( $_REQUEST['_featured'] ) ) 
		{
			$featured = ph_clean( $_REQUEST['_featured'] );
			if ( $_REQUEST['_featured'] != 'yes' ) { $featured = ''; } // can only be 'yes' or blank
			update_post_meta( $post_id, '_featured', ph_clean( $featured ) );
		}

		if ( ! empty( $_REQUEST['_availability'] ) && is_numeric( $_REQUEST['_availability'] ) ) 
		{
			wp_set_post_terms( $post_id, (int)$_REQUEST['_availability'], 'availability' );
		}

		if ( ! empty( $_REQUEST['_negotiator_id'] ) && is_numeric( $_REQUEST['_negotiator_id'] ) && $_REQUEST['_negotiator_id'] != '-1' ) 
		{
			update_post_meta( $post_id, '_negotiator_id', (int)$_REQUEST['_negotiator_id'] );
		}

		if ( ! empty( $_REQUEST['_office_id'] ) && is_numeric( $_REQUEST['_office_id'] ) ) 
		{
			update_post_meta( $post_id, '_office_id', (int)$_REQUEST['_office_id'] );
		}

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
