<?php
/**
 * Admin View: Bulk Edit Properties
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<fieldset class="inline-edit-col-right">
	<div id="propertyhive-fields-bulk" class="inline-edit-col">

		<?php do_action( 'propertyhive_property_bulk_edit_start' ); ?>

		<div class="inline-edit-group">
			<label class="alignleft">
				<span class="title"><?php echo esc_html(__( 'On Market', 'propertyhive' )); ?></span>
				<span class="input-text-wrap">
					<select class="on_market" name="_on_market">
					<?php
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$options = array(
							'' 	=> __( '— No Change —', 'propertyhive' ),
							'yes' => __( 'Yes', 'propertyhive' ),
							'no' => __( 'No', 'propertyhive' ),
						);
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						foreach ($options as $key => $value) {
							echo '<option value="' . esc_attr( $key ) . '">' . esc_html($value) . '</option>';
						}
					?>
					</select>
				</span>
			</label>
		</div>

		<div class="inline-edit-group">
			<label class="alignleft">
				<span class="title"><?php echo esc_html(__( 'Featured', 'propertyhive' )); ?></span>
				<span class="input-text-wrap">
					<select class="featured" name="_featured">
					<?php
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$options = array(
							'' 	=> __( '— No Change —', 'propertyhive' ),
							'yes' => __( 'Yes', 'propertyhive' ),
							'no' => __( 'No', 'propertyhive' ),
						);
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						foreach ($options as $key => $value) {
							echo '<option value="' . esc_attr( $key ) . '">' . esc_html($value) . '</option>';
						}
					?>
					</select>
				</span>
			</label>
		</div>

		<div class="inline-edit-group">
			<label class="alignleft">
				<span class="title"><?php echo esc_html(__( 'Availability', 'propertyhive' )); ?></span>
				<span class="input-text-wrap">
					<select class="availability" name="_availability">
					<?php

						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$options = array( '' => __( '— No Change —', 'propertyhive' ) );
		                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		                $args = array(
		                    'hide_empty' => false,
		                    'parent' => 0
		                );
		                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		                $terms = get_terms( array_merge( wp_parse_args( $args ), array( 'taxonomy' => 'availability' ) ) );

		                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		                $selected_value = '';
		                if ( !empty( $terms ) && !is_wp_error( $terms ) )
		                {
		                    foreach ($terms as $term)
		                    {
		                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		                        $options[$term->term_id] = $term->name;
		                    }
		                }

						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						foreach ($options as $key => $value) {
							echo '<option value="' . esc_attr( $key ) . '">' . esc_html($value) . '</option>';
						}
					?>
					</select>
				</span>
			</label>
		</div>

		<div class="inline-edit-group">
			<label class="alignleft">
				<span class="title"><?php echo esc_html(__( 'Negotiator', 'propertyhive' )); ?></span>
				<span class="input-text-wrap">
				<?php
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
					$args = array(
	                'name' => '_negotiator_id', 
	                'id' => '_negotiator_id', 
	                'show_option_none' => '— No Change —',
	                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy Property Negotiator compatibility filter; existing role filters depend on this exact public hook name.
	                'role__not_in' => apply_filters( 'property_negotiator_exclude_roles', array('property_hive_contact', 'subscriber') )
	            );
	            wp_dropdown_users($args);
				?>
				</span>
			</label>
		</div>

		<div class="inline-edit-group">
			<label class="alignleft">
				<span class="title"><?php echo esc_html(__( 'Office', 'propertyhive' )); ?></span>
				<span class="input-text-wrap">
					<select class="office_id" name="_office_id">
					<?php

						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$options = array( '' => __( '— No Change —', 'propertyhive' ) );
		                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		                $args = array(
				            'post_type' => 'office',
				            'nopaging' => true,
				            'orderby' => 'title',
				            'order' => 'ASC'
				        );
				        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
				        $office_query = new WP_Query($args);
				        
				        if ($office_query->have_posts())
				        {
				            while ($office_query->have_posts())
				            {
				                $office_query->the_post();
				                
				                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
				                $options[get_the_ID()] = get_the_title();
				            }
				        }
				        
				        $office_query->reset_postdata();

						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						foreach ($options as $key => $value) {
							echo '<option value="' . esc_attr( $key ) . '">' . esc_html($value) . '</option>';
						}
					?>
					</select>
				</span>
			</label>
		</div>

		<?php do_action( 'propertyhive_property_bulk_edit_end' ); ?>

		<input type="hidden" name="propertyhive_bulk_edit" value="1" />
		<input type="hidden" name="propertyhive_bulk_edit_nonce" value="<?php echo esc_attr(wp_create_nonce( 'propertyhive_bulk_edit_nonce' )); ?>" />
	</div>
</fieldset>
