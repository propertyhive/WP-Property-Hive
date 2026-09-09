<?php
/**
 * Admin functions for the appraisal post type
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

if ( ! class_exists( 'PH_Admin_CPT_Appraisal' ) ) :

/**
 * PH_Admin_CPT_Appraisal Class
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Admin_CPT_Appraisal; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Admin_CPT_Appraisal extends PH_Admin_CPT {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->type = 'appraisal';

		// Before data updates
		add_action( 'pre_post_update', array( $this, 'pre_post_update' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ) );

		// Admin Columns
		add_filter( 'manage_edit-appraisal_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_appraisal_posts_custom_column', array( $this, 'custom_columns' ), 2 );
		add_filter( 'list_table_primary_column', array( $this, 'set_primary_column' ), 10, 2 );
        add_filter( 'post_row_actions', array( $this, 'remove_actions' ), 10, 2 );
		add_filter( 'manage_edit-appraisal_sortable_columns', array( $this, 'custom_columns_sort' ) );
		add_filter( 'request', array( $this, 'custom_columns_orderby' ) );

		// Bulk / quick edit
		add_filter( 'bulk_actions-edit-appraisal', array( $this, 'remove_bulk_actions') );
		/*add_action( 'bulk_edit_custom_box', array( $this, 'bulk_edit' ), 10, 2 );
		add_action( 'quick_edit_custom_box',  array( $this, 'quick_edit' ), 10, 2 );
		add_action( 'save_post', array( $this, 'bulk_and_quick_edit_save_post' ), 10, 2 );*/

		// Call PH_Admin_CPT constructor
		parent::__construct();
	}

	/**
	 * Check if we're editing or adding a appraisal
	 * @return boolean
	 */
    private function is_editing_appraisal() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen detection; mutations have separate save guards.
        $post_type = isset( $_GET['post_type'] ) && is_string( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen detection.
        $post_id = isset( $_GET['post'] ) && is_string( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen detection for AJAX requests.
        $request_id = isset( $_REQUEST['post_id'] ) && is_string( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : 0;
        return 'appraisal' === $post_type || ( $post_id > 0 && 'appraisal' === get_post_type( $post_id ) ) || ( $request_id > 0 && 'appraisal' === get_post_type( $request_id ) );
    }

	/**
	 * @param int $post_id
	 */
	public function pre_post_update( $post_id ) {

	}

	/**
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
		$columns['start_date_time'] = __( 'Appraisal Date / Time', 'propertyhive' );
        $columns['property'] = __( 'Property', 'propertyhive' );
        $columns['owner'] = __( 'Owner / Landlord', 'propertyhive' );
        $columns['price'] = __( 'Valued Price', 'propertyhive' );
        $columns['status'] = __( 'Status', 'propertyhive' );
        $columns['negotiators'] = __( 'Attending Negotiators', 'propertyhive' );

		return array_merge( $columns, $existing_columns );
	}

	/**
	 * Define our custom columns shown in admin.
	 * @param  string $column
	 */
	public function custom_columns( $column ) {
		global $post, $propertyhive, $the_appraisal;

		if ( empty( $the_appraisal ) || $the_appraisal->ID != $post->ID )
		{
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Retain the legacy global name for compatibility with external admin column callbacks.
			$the_appraisal = new PH_Appraisal( $post->ID );
		}

		switch ( $column ) {
			case 'start_date_time' :
				
				$edit_link        = get_edit_post_link( $post->ID );
				//$title            = _draft_or_post_title();
                $title            = gmdate("H:i jS F Y", strtotime($the_appraisal->start_date_time));
                
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . esc_html($title) . '</a></strong>';
			 break;
            case 'property' :
                
                echo esc_html($the_appraisal->get_formatted_full_address());
                break;
            case 'owner' :
                
                $owner_contact_ids = $the_appraisal->_property_owner_contact_id;
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
            case 'price' :
                
                if ( $the_appraisal->status == 'carried_out' )
                {
                    echo wp_kses_post( $the_appraisal->get_formatted_price() );
                }
                else
                {
                	echo '-';
                }
                break;
            case 'status' :
                
                echo esc_html(ucwords(str_replace("_", " ", $the_appraisal->status)));

                if ( $the_appraisal->status == 'pending' )
                {
                	echo '<br>';
                	// confirmation status
                    if ( $the_appraisal->all_confirmed == 'yes' )
                	{
                		echo esc_html(__( 'All Parties Confirmed', 'propertyhive' ));
                	}
                	else
                	{
                		echo esc_html(__( 'Awaiting Confirmation', 'propertyhive' ));
                	}
                }

                break;
            case 'negotiators' :
            	$negotiator_ids = get_post_meta( $post->ID, '_negotiator_id' );
            	if ( is_array($negotiator_ids) && !empty($negotiator_ids) )
            	{
            		$negotiators = array();
            		foreach ( $negotiator_ids as $negotiator_id )
            		{
            			$user_info = get_userdata($negotiator_id);
            			if ( $user_info === false )
            			{
            				$negotiators[] = __( 'Unknown negotiator', 'propertyhive' );
            			}
            			else
            			{
	            			$negotiators[] = $user_info->display_name;
            			}
            		}
            		echo esc_html(implode(", ", $negotiators));
            	}
            	else
            	{
            		echo '<em>- ' . esc_html(__( 'None Set', 'propertyhive' )) . ' -</em>';
            	}
            	break;
			default :
				break;
		}
	}

	public function set_primary_column( $default, $screen ) {
        
        if ( 'edit-appraisal' === $screen ) 
        {
            $default = 'start_date_time';
        }

        return $default;
    }

    public function remove_actions($actions, $post) {

        if ( $post->post_type !== 'appraisal' )
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
	 * Make appraisal columns sortable
	 *
	 * @access public
	 * @param mixed $columns
	 * @return array
	 */
	public function custom_columns_sort( $columns ) {
		$custom = array(
			'start_date_time' => '_start_date_time',
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Appraisal column orderby
	 *
	 * @access public
	 * @param mixed $vars
	 * @return array
	 */
	public function custom_columns_orderby( $vars ) {
		if ( is_admin() && $vars['post_type'] == 'appraisal' )
		{
			$vars = array_merge( $vars, array(
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- All these meta_key values are literal, supported CPT date/status/price keys used by paginated WordPress admin list ordering. Core admin post queries provide pagination; no arbitrary request key is copied into these lines.
				'meta_key' 	=> '_start_date_time',
				'orderby' 	=> 'meta_value'
			) );
		}
		return $vars;
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
	 * Show a status filter box
	 */
	public function propertyhive_filters() {
		global $typenow, $wp_query;

		if ( 'appraisal' != $typenow ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Empty base is safe; trusted PHP extensions supply admin form controls through this HTML filter.
		echo apply_filters( 'propertyhive_appraisal_filters', '' );
	}

	/**
	 * Filter the appraisal in admin based on options
	 *
	 * @param mixed $query
	 */
	public function appraisal_filters_query( $query ) {
		global $typenow, $wp_query;

		if ( 'appraisal' == $typenow ) {

		}
	}
}

endif;

return new PH_Admin_CPT_Appraisal();
