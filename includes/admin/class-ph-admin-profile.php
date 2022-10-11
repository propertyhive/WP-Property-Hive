<?php
/**
 * Add extra profile fields for users in admin
 *
 * @author   PropertyHive
 * @category Admin
 * @package  PropertyHive/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Admin_Profile', false ) ) :

	/**
	 * PH_Admin_Profile Class.
	 */
	class PH_Admin_Profile {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			add_action( 'show_user_profile', array( $this, 'extra_user_meta_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'extra_user_meta_fields' ) );

			add_action( 'personal_options_update', array( $this, 'save_extra_user_meta_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_extra_user_meta_fields' ) );
		}

		/**
		 * Get fields for the edit user pages.
		 *
		 * @return array Fields to display which are filtered through propertyhive_user_meta_fields before being returned
		 */
		public function get_user_meta_fields() {

			$args = array(
				'post_type' => 'office',
				'nopaging' => true,
				'orderby' => 'post_title',
				'order' => 'ASC',
			);

			$offices = array();

			$offices_query = new WP_Query( $args );

			if ( $offices_query->have_posts() )
			{
				while ( $offices_query->have_posts() )
				{
					$offices_query->the_post();

					$offices[get_the_ID()] = get_the_title();
				}
			}
			wp_reset_postdata();

			$fields = array(
				'office_id'    => array(
					'label'       => __( 'Office', 'propertyhive' ),
					'description' => '',
					'type'        => 'select',
					'options'     => array( '' => __( 'Select an office', 'property' ) ) + $offices,
				),
				'telephone_number'    => array(
					'label'       => __( 'Telephone Number', 'propertyhive' ),
					'description' => '',
					'type'        => 'text',
				),
				'photo_attachment_id'    => array(
					'label'       => __( 'Photo', 'propertyhive' ),
					'description' => '',
					'type'        => 'image',
				),
			);

			$user = wp_get_current_user();
			$roles = (array)$user->roles;

			$fields['crm_only_mode'] = array(
				'label'       => __( 'Property Hive-Only Mode', 'propertyhive' ),
				'description' => __( 'Enabling this option will remove all top level WordPress menu items leaving just Property Hive options making it easier to navigate and use as a CRM', 'propertyhive' ),
				'type'        => in_array('administrator', $roles) ? 'checkbox' : 'hidden',
			);

			$show_fields = array(
				'negotiator'  => array(
					'title'  => __( 'Property Hive Negotiator Details', 'propertyhive' ),
					'fields' => $fields,
				),
			);

			$show_fields = apply_filters( 'propertyhive_user_meta_fields', $show_fields );

			return $show_fields;
		}

		/**
		 * Show fields on edit user pages.
		 *
		 * @param WP_User $user
		 */
		public function extra_user_meta_fields( $user ) {

			if ( ! current_user_can( 'manage_propertyhive' ) ) {
				return;
			}

			$user_meta = get_userdata($user->ID); 
			$user_roles = $user_meta->roles;

			if ( ! in_array("administrator", $user_roles) && ! in_array("editor", $user_roles) ) {
				return;
			}

			$show_fields = $this->get_user_meta_fields();

			foreach ( $show_fields as $fieldset_key => $fieldset ) :
				?>
				<h2><?php echo $fieldset['title']; ?></h2>
				<table class="form-table" id="<?php echo esc_attr( 'fieldset-' . $fieldset_key ); ?>">
					<?php foreach ( $fieldset['fields'] as $key => $field ) : ?>
						<?php
							if ( ! empty( $field['type'] ) && 'hidden' === $field['type'] )
							{
						?>
							<input type="hidden" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $this->get_user_meta( $user->ID, $key ) ); ?>" />
						<?php
							}
							else
							{
						?>
						<tr>
							<th>
								<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							</th>
							<td>
								<?php if ( ! empty( $field['type'] ) && 'select' === $field['type'] ) : ?>
									<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $field['class'] ); ?>" style="width: 25em;">
										<?php
											$selected = esc_attr( get_user_meta( $user->ID, $key, true ) );
										foreach ( $field['options'] as $option_key => $option_value ) :
											?>
											<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $selected, $option_key, true ); ?>><?php echo esc_html( $option_value ); ?></option>
										<?php endforeach; ?>
									</select>
								<?php elseif ( ! empty( $field['type'] ) && 'checkbox' === $field['type'] ) : ?>
									<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="1" class="<?php echo esc_attr( $field['class'] ); ?>" <?php checked( (int) get_user_meta( $user->ID, $key, true ), 1, true ); ?> />
								<?php elseif ( ! empty( $field['type'] ) && 'button' === $field['type'] ) : ?>
									<button type="button" id="<?php echo esc_attr( $key ); ?>" class="button <?php echo esc_attr( $field['class'] ); ?>"><?php echo esc_html( $field['text'] ); ?></button>
								<?php elseif ( ! empty( $field['type'] ) && 'image' === $field['type'] ) : ?>
									<input type="hidden" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" class="photo-attachment-id" value="<?php echo esc_attr( $this->get_user_meta( $user->ID, $key ) ); ?>" />
									<div class="photo-attachment-image">
				                        <?php if ( !empty($this->get_user_meta( $user->ID, $key )) ) { echo wp_get_attachment_image( $this->get_user_meta( $user->ID, $key ), 'thumbnail'); } ?>
				                    </div>
									<div class="wp-media-buttons">
				                        <button class="button propertyhive-add-media" id="propertyhive-add-media"><?php _e('Select', 'propertyhive'); ?></button>
				                    </div>
                    				<script>

                    					var file_frame;

                    					jQuery(document).ready(function() 
                    					{
										    jQuery('body').on('click', '.propertyhive-add-media', function( event ){
                       
						                        event.preventDefault();
						                       
						                        // If the media frame already exists, reopen it.
						                        if ( file_frame ) {
						                            file_frame.open();
						                            return;
						                        }

						                        // Create the media frame.
					                          	file_frame = wp.media.frames.file_frame = wp.media({
					                            	multiple: false
					                          	});

					                          	// When an image is selected, run a callback.
					                          	file_frame.on( 'select', function() {
					                              	var selection = file_frame.state().get('selection');
					           
					                              	selection.map( function( attachment ) {
					                           
					                                  	attachment = attachment.toJSON();
					                           
							                            jQuery('#<?php echo esc_attr( $key ); ?>').val(attachment.id);
							                            
							                            var photo_html = '<img src="' + attachment.url + '" style="max-width:150px; max-height:150px;" alt=""></li>';
							                            
							                            jQuery('.photo-attachment-image').html(photo_html);
					                              	});
					                          	});
					                       
					                          	// Finally, open the modal
					                          	file_frame.open();
						                    });
										});
                    				</script>
                    			<?php else: ?>
									<input type="text" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $this->get_user_meta( $user->ID, $key ) ); ?>" class="<?php echo ( ! empty( $field['class'] ) ? esc_attr( $field['class'] ) : 'regular-text' ); ?>" />
								<?php endif; ?>
								<p class="description"><?php echo wp_kses_post( $field['description'] ); ?></p>
							</td>
						</tr>
						<?php
							}
						?>
					<?php endforeach; ?>
				</table>
				<?php
			endforeach;
		}

		/**
		 * Save Address Fields on edit user pages.
		 *
		 * @param int $user_id User ID of the user being saved
		 */
		public function save_extra_user_meta_fields( $user_id ) {
			
			if ( ! current_user_can( 'manage_propertyhive' ) ) {
				return;
			}

			$user_meta = get_userdata($user_id); 
			$user_roles = $user_meta->roles;

			if ( ! in_array("administrator", $user_roles) && ! in_array("editor", $user_roles) ) {
				return;
			}

			$save_fields = $this->get_user_meta_fields();

			foreach ( $save_fields as $fieldset ) {

				foreach ( $fieldset['fields'] as $key => $field ) {

					if ( isset( $field['type'] ) && 'checkbox' === $field['type'] ) {
						update_user_meta( $user_id, $key, isset( $_POST[ $key ] ) );
					} elseif ( isset( $_POST[ $key ] ) ) {
						update_user_meta( $user_id, $key, ph_clean( $_POST[ $key ] ) );
					}
				}
			}
		}

		/**
		 * Get user meta for a given key, with fallbacks to core user info for pre-existing fields.
		 *
		 * @param int    $user_id User ID of the user being edited
		 * @param string $key     Key for user meta field
		 * @return string
		 */
		protected function get_user_meta( $user_id, $key ) {
			$value           = get_user_meta( $user_id, $key, true );

			return $value;
		}
	}

endif;

return new PH_Admin_Profile();
