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
		$columns['dates']          = __( 'Start / End Dates', 'propertyhive' );
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
				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) . '">' . $property->get_formatted_full_address() . '</a></strong>';

				// Get actions
				$actions = array();

				if ( $can_edit_post && 'trash' != $post->post_status )
				{
					$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item', 'propertyhive' ) ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>';
				}

				if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) )
				{
					if ( 'trash' == $post->post_status ) {
						$actions['untrash'] = '<a title="' . esc_attr( __( 'Restore this item from the Trash', 'propertyhive' ) ) . '" href="' . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . '">' . __( 'Restore', 'propertyhive' ) . '</a>';
					} elseif ( EMPTY_TRASH_DAYS ) {
						//$actions['trash'] = '<a class="submitdelete" title="' . esc_attr( __( 'Move this item to the Trash', 'propertyhive' ) ) . '" href="' . get_delete_post_link( $post->ID ) . '">' . __( 'Trash', 'propertyhive' ) . '</a>';
					}

					if ( 'trash' == $post->post_status || ! EMPTY_TRASH_DAYS ) {
						$actions['delete'] = '<a class="submitdelete" title="' . esc_attr( __( 'Delete this item permanently', 'propertyhive' ) ) . '" href="' . get_delete_post_link( $post->ID, '', true ) . '">' . __( 'Delete Permanently', 'propertyhive' ) . '</a>';
					}
				}

				$actions = apply_filters( 'post_row_actions', $actions, $post );

				echo '<div class="row-actions">';

				$i = 0;
				$action_count = sizeof( $actions );

				foreach ( $actions as $action => $link )
				{
					++ $i;
					( $i == $action_count ) ? $sep = '' : $sep = ' | ';
					echo '<span class="' . $action . '">' . $link . $sep . '</span>';
				}
				echo '</div>';

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
						echo get_the_title( $owner_contact_id ) . '<br>';
						if ( count( $owner_contact_ids ) == 1 )
						{
							echo '<div class="row-actions">';
							echo 'T: ' . get_post_meta( $owner_contact_id, '_telephone_number', true ) . '<br>';
							echo 'E: ' . get_post_meta( $owner_contact_id, '_email_address', true );
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
			case 'dates' :
				echo 'Start Date: ' . ( $the_tenancy->_start_date != '' ? date( "d/m/Y", strtotime( $the_tenancy->_start_date ) ) : '-' ) . '<br>';
				echo 'End Date: ' . ( $the_tenancy->_end_date != '' ? date( "d/m/Y", strtotime( $the_tenancy->_end_date ) ) : '-' );

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
