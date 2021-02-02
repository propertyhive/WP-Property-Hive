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

			add_filter( 'manage_edit-key_date_columns', array( $this, 'columns' ) );
			add_action( 'manage_key_date_posts_custom_column', array( $this, 'custom_columns' ) );
			add_filter( 'manage_edit-key_date_sortable_columns', array( $this, 'sortable_columns' ) );
			add_filter( 'request', array( $this, 'custom_sorts' ) );
			add_action( 'quick_edit_custom_box', array( $this, 'key_date_custom_quick_edit_box' ), 10, 3 );
			add_action( 'save_post', array( $this, 'save_key_date' ) );
			add_action( 'bulk_edit_custom_box', array( $this, 'key_date_custom_bulk_edit_box' ), 10, 3 );
			add_action( 'wp_ajax_save_key_date_bulk', array( $this, 'save_key_date_bulk' ) );

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
									<span class="title">Type</span>
									<span class="key_date-type"></span>
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

											foreach ( array( 'pending', 'booked', 'complete' ) as $status )
											{
													$output .= '<option value="' . $status . '"';
													$output .= selected($status, $selected_value, false );
													$output .= '>' . ucwords($status) . '</option>';
											}

											$output .= '</select>';

											echo $output;
										?>
									</span>
								</label>
							</div>
						</fieldset>
					<?php
			}
		}

		function key_date_custom_bulk_edit_box( $column_name, $post_type ) {
			global $post;

			if ( $post_type == 'key_date' && $column_name == 'description' )
			{
				?>
				<fieldset class="inline-edit-col-left inline-edit-ph inline-edit-key_date">
					<legend class="inline-edit-legend">Bulk Edit</legend>
					<div id="bulk-title-div">
						<div id="bulk-titles"></div>
					</div>
					<div class="inline-edit-col">
						<label>
							<span class="title">Status</span>
							<span class="input-text-wrap">
								<?php
									$selected_value = get_post_meta( $post->ID, '_key_date_status', true );
									$output = '<select name="_key_date_status">';

									foreach ( array('pending', 'booked', 'complete') as $status )
									{
										$output .= '<option value="' . $status . '"';
										$output .= selected($status, $selected_value, false );
										$output .= '>' . ucwords($status) . '</option>';
									}

									$output .= '</select>';

									echo $output;
								?>
							</span>
						</label>
					</div>
				</fieldset>
				<?php
			}
		}

		public function columns( array $columns ) {

			return array_merge( $columns, array(
				'description' => __( 'Description', 'propertyhive' ),
				'property'    => __( 'Property', 'propertyhive' ),
				'tenants'     => __( 'Tenants', 'propertyhive' ),
				'date_due'    => __( 'Date Due', 'propertyhive' ),
				'status'      => __( 'Status', 'propertyhive' ),
			) );
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
					echo '<div class="cell-main-content">' . $key_date->description() . '</div>';
					echo '<div class="row-actions">';

					$actions = array();
					$actions['inline hide-if-no-js'] = sprintf(
						'<button type="button" class="button-link editinline" aria-label="%s" aria-expanded="false">%s</button>',
						esc_attr( sprintf( __( 'Quick edit &#8220;%s&#8221; inline' ), $key_date->description() ) ),
						__( 'Quick&nbsp;Edit' )
					);
					$i = 0;
					$action_count = sizeof($actions);

					foreach ( $actions as $action => $link )
					{
						++$i;
						( $i == $action_count ) ? $sep = '' : $sep = ' | ';
						echo '<span class="' . $action . '">' . $link . $sep . '</span>';
					}
					echo '</div>';

					break;

				case 'property' :
					echo '<div class="cell-main-content">' . $property->get_formatted_full_address() . '</div>';
					break;

				case 'tenants' :
					if ( $tenancy->id )
					{
						$applicant_contact_ids = get_post_meta( $tenancy->id, '_applicant_contact_id' );
						if ( is_array($applicant_contact_ids) && !empty($applicant_contact_ids) )
						{
							$applicants = array();
							foreach ( $applicant_contact_ids as $applicant_contact_id )
							{
								$applicants[] = get_the_title($applicant_contact_id);
							}
							echo implode("<br>", $applicants);
						}
						else
						{
							echo '-';
						}
					}
					else
					{
						echo '-';
					}
					break;

				case 'date_due' :
					echo '<div class="cell-main-content">' . $key_date->date_due()->format( 'jS F Y' ) . '</div>';
					break;

				case 'status' :
					echo '<div class="cell-main-content">' . ucwords( $key_date->status() ) . '</div>';
					break;

				default :
					break;
			}
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

		function save_key_date( $post_id ) {

			if ( $post_id == null || get_post_type($post_id) != 'key_date' || empty( $_POST['_key_date_status'] ) )
			{
				return;
			}

			update_post_meta( $post_id, '_key_date_status', $_POST['_key_date_status'] );
		}

		function save_key_date_bulk() {

			if ( empty( $_POST[ 'post_ids' ] ) )
			{
				return;
			}

			foreach ( $_POST[ 'post_ids' ] as $id )
			{
				$this->save_key_date($id);
			}
		}
	}
}

return new PH_Admin_CPT_Key_Date();
