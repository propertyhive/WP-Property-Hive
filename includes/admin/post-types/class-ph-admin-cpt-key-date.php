<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Admin_CPT' ) ) {
	include( 'class-ph-admin-cpt.php' );
}

if ( ! class_exists( 'PH_Admin_CPT_Key_Date' ) )
{
	class PH_Admin_CPT_Key_Date extends PH_Admin_CPT {

		public function __construct() {
			$this->type = 'key_date';

			add_filter( 'manage_edit-key_date_columns', array( $this, 'edit_columns' ) );
			add_action( 'manage_key_date_posts_custom_column', array( $this, 'custom_columns' ) );
			add_filter( 'list_table_primary_column', array( $this, 'set_primary_column' ), 10, 2 );
			add_filter( 'post_row_actions', array( $this, 'remove_actions' ), 10, 2 );
			add_filter( 'manage_edit-key_date_sortable_columns', array( $this, 'sortable_columns' ) );
			add_filter( 'request', array( $this, 'custom_sorts' ) );
			add_action( 'quick_edit_custom_box', array( $this, 'key_date_custom_quick_edit_box' ), 10, 3 );
			add_action( 'save_post', array( $this, 'save_key_date' ) );

			add_filter( 'bulk_actions-edit-key_date', array( $this, 'remove_bulk_actions') );

			parent::__construct();
		}

		function key_date_custom_quick_edit_box( $column_name, $post_type, $taxonomy ) {
			global $post;

			if ($post_type == 'key_date' && $column_name == 'description')
			{
				?>
						<fieldset class="inline-edit-col-left inline-edit-ph inline-edit-key_date">
							<legend class="inline-edit-legend">Quick Edit</legend>
							<div class="inline-edit-col">
								<label>
									<span class="title">Description</span>
									<span class="input-text-wrap">
										<input type="text" name="_key_date_description" class="short" style="width:200px;" value="">
									</span>
								</label>
								<label>
									<span class="title">Property</span>
									<span class="key_date-property"></span>
								</label>
								<label>
									<span class="title">Status</span>
									<span class="input-text-wrap">
										<?php
											$selected_value = get_post_meta( $post->ID, '_key_date_status', true );

											$output = '<select name="_key_date_status">';

											foreach ( array( 'pending', 'booked', 'complete', 'on_hold', 'cancelled' ) as $status )
											{
													$output .= '<option value="' . esc_attr($status) . '"';
													$output .= selected($status, $selected_value, false );
													$output .= '>' . esc_html(ucwords(str_replace("_", " ", $status))) . '</option>';
											}

											$output .= '</select>';

											echo $output;
										?>
									</span>
								</label>
								<label id="book_next_key_date_label" style="display: none;">
									Book next key date?&nbsp;&nbsp;<input type="checkbox" id="book_next_key_date_checkbox" name="book_next_key_date" >
								</label>
								<label id="next_date_due_label" style="display: none;">
									<span class="title">Next Date</span>
									<span class="input-text-wrap">
										<input type="text" id="next_date_due" name="next_date_due" class="short" placeholder="yyyy-mm-dd" style="width:120px;" value="">
									</span>
								</label>
							</div>
						</fieldset>
					<?php
			}
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

			$columns                = array();
			$columns['cb']          = '<input type="checkbox" />';
			$columns['description'] = __( 'Description', 'propertyhive' );
			$columns['notes']       = __( 'Notes', 'propertyhive' );
			$columns['property']    = __( 'Property', 'propertyhive' );
			$columns['tenants']     = __( 'Tenants', 'propertyhive' );
			$columns['date_due']    = __( 'Date Due', 'propertyhive' );
			$columns['status']      = __( 'Status', 'propertyhive' );

			return array_merge( $columns, $existing_columns );
		}

		/**
		 * Define our custom columns shown in admin.
		 *
		 * @param string $column
		 */
		public function custom_columns( $column ) {
			global $post;

			$key_date = new PH_Key_Date( $post );
			$property = $key_date->property();
			$tenancy  = $key_date->tenancy();

			switch ( $column ) {

				case 'description' :

					$post_type_object = get_post_type_object( $post->post_type );

					echo '<div class="cell-main-content">';
					$opening_link_tag = false;
					if ( !empty($key_date->tenancy_id) ) 
					{
						echo '<a href="' . esc_url(get_edit_post_link((int)$key_date->tenancy_id)) . '#propertyhive-tenancy-management%7Cpropertyhive-management-dates">'; 
						$opening_link_tag = true; 
					}
					else
					{ 
						if ( !empty($key_date->property_id) ) 
						{
							echo '<a href="' . esc_url(get_edit_post_link((int)$key_date->property_id)) . '#propertyhive-property-tenancies%7Cpropertyhive-management-dates">'; 
							$opening_link_tag = true; 
						}
					}
					echo $key_date->description() . ( $opening_link_tag ? '</a>' : '' ) . '</div>';
					echo '<div class="row-actions">';
					break;

				case 'notes' :
					echo '<div class="cell-main-content">' . ( !empty($key_date->notes()) ? nl2br( $key_date->notes() ) : '-' ) . '</div>';
					break;

				case 'property' :
					echo '<div class="cell-main-content">' . esc_html($property->get_formatted_full_address()) . '</div>';
					break;

				case 'tenants' :
					if ( $tenancy->id )
					{
						echo $tenancy->get_tenants(false, true);
					}
					else
					{
						echo '-';
					}
					break;

				case 'date_due' :
					echo '<div class="cell-main-content">' . esc_html($key_date->date_due()->format( 'jS F Y' )) . '</div>';
					break;

				case 'status' :
					echo '<div class="cell-main-content">' . esc_html(ucwords( $key_date->status() )) . '</div>';
					break;

				default :
					break;
			}
		}

		public function set_primary_column( $default, $screen ) {
        
	        if ( 'edit-key_date' === $screen ) 
	        {
	            $default = 'description';
	        }

	        return $default;
	    }

	    public function remove_actions($actions, $post) {

	        if ( $post->post_type !== 'key_date' )
	        {
	            return $actions;
	        }

	        if ( isset($actions['edit']) ) 
	        {
	            unset($actions['edit']);
	        }

	        return $actions;
	    }

		public function sortable_columns( $columns ) {
			$custom = array(
				'date_due' => 'date_due',
			);

			return wp_parse_args( $custom, $columns );
		}

		function custom_sorts( $vars ) {

			if ( ! isset( $vars['orderby'] ) )
			{
				return $vars;
			}

			switch ( $vars['orderby'] )
			{
				case 'date_due':
					$vars['orderby']  = 'meta_value';
					$vars['meta_key'] = '_date_due';
					break;
			}

			return $vars;
		}

		/**
		 * Remove bulk edit actions
		 * @param  array $actions
		 */
		public function remove_bulk_actions( $actions ) {
			return array();
		}

		function save_key_date( $post_id ) {

			if ( $post_id == null || get_post_type($post_id) != 'key_date' || empty( $_POST['_key_date_status'] ) )
			{
				return;
			}

			update_post_meta( $post_id, '_key_date_status', $_POST['_key_date_status'] );

			$existing_description = get_the_title($post_id);

			if ( !empty( $_POST['_key_date_description'] ) && $_POST['_key_date_description'] != $existing_description )
			{
				$post_update = array(
					'ID'         => $post_id,
					'post_title' => $_POST['_key_date_description'],
				);

				wp_update_post( $post_update );
			}

			if ( isset($_POST['book_next_key_date']) && $_POST['book_next_key_date'] == 'on' && isset($_POST['next_date_due']) && $_POST['next_date_due'] != '' )
			{
				// Insert next key date record
				$next_key_date_post = array(
					'post_title' => $_POST['_key_date_description'],
					'post_content' => '',
					'post_type' => 'key_date',
					'post_status' => 'publish',
					'comment_status' => 'closed',
					'ping_status' => 'closed',
				);

				// Insert the post into the database
				// Remove save_post hook temporarily to prevent it running again on wp_insert_post
				remove_action( 'save_post', array( $this, 'save_key_date' ) );
				$next_key_date_post_id = wp_insert_post( $next_key_date_post );
				add_action( 'save_post', array( $this, 'save_key_date' ) );

				if ( !is_wp_error($next_key_date_post_id) && $next_key_date_post_id != 0 )
				{
					add_post_meta( $next_key_date_post_id, '_date_due', $_POST['next_date_due'] );
					add_post_meta( $next_key_date_post_id, '_key_date_status', 'pending' );
					add_post_meta( $next_key_date_post_id, '_key_date_type_id', get_post_meta($post_id, '_key_date_type_id', true) );

					if( metadata_exists('post', $post_id, '_property_id') ) {
						add_post_meta( $next_key_date_post_id, '_property_id', get_post_meta($post_id, '_property_id', true) );
					}

					if( metadata_exists('post', $post_id, '_tenancy_id') ) {
						add_post_meta( $next_key_date_post_id, '_tenancy_id', get_post_meta($post_id, '_tenancy_id', true) );
					}
				}
			}
		}
	}
}

return new PH_Admin_CPT_Key_Date();
