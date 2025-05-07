<?php
/**
 * Admin functions for the tenancy post type
 *
 * @author    PropertyHive
 * @category  Admin
 * @package   PropertyHive/Admin/Post Types
 * @version   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Admin_CPT' ) ) {
	include( 'class-ph-admin-cpt.php' );
}

if ( ! class_exists( 'PH_Admin_CPT_Tenancy' ) ) :

/**
 * PH_Admin_CPT_Tenancy Class
 */
class PH_Admin_CPT_Tenancy extends PH_Admin_CPT {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->type = 'tenancy';

		// Before data updates
		add_action( 'pre_post_update', array( $this, 'pre_post_update' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ) );

		// Admin Columns
		add_filter( 'manage_edit-tenancy_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_tenancy_posts_custom_column', array( $this, 'custom_columns' ), 2 );
		add_filter( 'list_table_primary_column', array( $this, 'set_primary_column' ), 10, 2 );
        add_filter( 'post_row_actions', array( $this, 'remove_actions' ), 10, 2 );
		add_filter( 'manage_edit-tenancy_sortable_columns', array( $this, 'custom_columns_sort' ) );
		add_filter( 'request', array( $this, 'custom_columns_orderby' ) );

		// Bulk / quick edit
		add_filter( 'bulk_actions-edit-tenancy', array( $this, 'remove_bulk_actions' ) );

		// Call PH_Admin_CPT constructor
		parent::__construct();
	}

	/**
	 * Check if we're editing or adding a tenancy
	 * @return boolean
	 */
	private function is_editing_tenancy() {
		if ( ! empty( $_GET['post_type'] ) && 'tenancy' == $_GET['post_type'] ) {
			return true;
		}
		if ( ! empty( $_GET['post'] ) && 'tenancy' == get_post_type( (int) $_GET['post'] ) ) {
			return true;
		}
		if ( ! empty( $_REQUEST['post_id'] ) && 'tenancy' == get_post_type( (int) $_REQUEST['post_id'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param int $post_id
	 */
	public function pre_post_update( $post_id ) {

	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public function wp_insert_post_data( $data ) {


		return $data;
	}

	/**
	 * Change the columns shown in admin.
	 */
	public function edit_columns( $existing_columns ) {

		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) )
		{
			$existing_columns = array();
		}

		unset( $existing_columns['title'], $existing_columns['comments'], $existing_columns['date'] );

		$columns                   = array();
		$columns['cb']             = '<input type="checkbox" />';
		$columns['property']       = __( 'Property', 'propertyhive' );
		$columns['property_owner'] = __( 'Landlord', 'propertyhive' );
		$columns['applicant']      = __( 'Tenants', 'propertyhive' );
		$columns['start_date']     = __( 'Start Date', 'propertyhive' );
		$columns['end_date']       = __( 'End Date', 'propertyhive' );
		$columns['rent']           = __( 'Rent', 'propertyhive' );
		$columns['status']         = __( 'Status', 'propertyhive' );

		return array_merge( $columns, $existing_columns );
	}

	/**
	 * Define our custom columns shown in admin.
	 *
	 * @param string $column
	 */
	public function custom_columns( $column ) {
		global $post, $propertyhive, $the_tenancy;

		if ( empty( $the_tenancy ) || $the_tenancy->ID != $post->ID )
		{
			$the_tenancy = new PH_Tenancy( $post->ID );
		}

		switch ( $column ) {

			case 'property' :

				$edit_link        = get_edit_post_link( $post->ID );
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

				$property = new PH_Property( (int) $the_tenancy->property_id );
				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) . '">' . esc_html($property->get_formatted_full_address()) . '</a></strong>';
				break;
			case 'property_owner' :

				$the_property      = new PH_Property( (int) $the_tenancy->property_id );
				$owner_contact_ids = $the_property->_owner_contact_id;
				if (
					( ! is_array( $owner_contact_ids ) && $owner_contact_ids != '' && $owner_contact_ids != 0 )
					||
					( is_array( $owner_contact_ids ) && ! empty( $owner_contact_ids ) )
				) {
					if ( ! is_array( $owner_contact_ids ) )
					{
						$owner_contact_ids = array( $owner_contact_ids );
					}

					foreach ( $owner_contact_ids as $owner_contact_id )
					{
						echo esc_html(get_the_title( $owner_contact_id )) . '<br>';
						if ( count( $owner_contact_ids ) == 1 )
						{
							echo '<div class="row-actions">';
							echo 'T: ' . esc_html(get_post_meta( $owner_contact_id, '_telephone_number', true )) . '<br>';
							echo 'E: ' . esc_html(get_post_meta( $owner_contact_id, '_email_address', true ));
							echo '</div>';
						}
					}
				}
				else
				{
					echo '-';
				}

				break;
			case 'applicant' :

				echo $the_tenancy->get_tenants(false, true);

				break;
			case 'start_date' :
				echo ( $the_tenancy->_start_date != '' ? esc_html(date( "d/m/Y", strtotime( $the_tenancy->_start_date ) )) : '-' );

				break;
			case 'end_date' :
				echo ( $the_tenancy->_end_date != '' ? esc_html(date( "d/m/Y", strtotime( $the_tenancy->_end_date ) )) : '-' );

				break;
			case 'rent' :

				echo $the_tenancy->get_formatted_rent();

				break;
			case 'status' :

				echo $the_tenancy->get_status();

				break;
			default :
				break;
		}
	}

	public function set_primary_column( $default, $screen ) {
        
        if ( 'edit-tenancy' === $screen ) 
        {
            $default = 'property';
        }

        return $default;
    }

    public function remove_actions($actions, $post) {

        if ( $post->post_type !== 'tenancy' )
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
			'start_date' => '_start_date',
			'end_date' => '_end_date'
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
			if ( '_start_date' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
					'meta_key' 	=> '_start_date',
					'orderby' 	=> 'meta_value'
				) );
			}
			elseif ( '_end_date' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
					'meta_key' 	=> '_end_date',
					'orderby' 	=> 'meta_value'
				) );
			}
		}

		return $vars;
	}

	/**
	 * Remove bulk edit option
	 *
	 * @param array $actions
	 */
	public function remove_bulk_actions( $actions ) {
		unset( $actions['edit'] );

		return $actions;
	}
}

endif;

return new PH_Admin_CPT_Tenancy();
