<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * PropertyHive Admin Settings Class.
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Admin_Settings' ) ) :

/**
 * PH_Admin_Settings
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Admin_Settings; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Admin_Settings {

	private static $settings = array();
	private static $errors   = array();
	private static $messages = array();

	/**
	 * Include the settings page classes
	 */
	public static function get_settings_pages() {
		if ( empty( self::$settings ) ) {
			$settings = array();

			include_once( 'settings/class-ph-settings-page.php' );

			$settings[] = include( 'settings/class-ph-settings-general.php' );
            $settings[] = include( 'settings/class-ph-settings-offices.php' );
            $settings[] = include( 'settings/class-ph-settings-custom-fields.php' );
            $propertyhive_template_assistant_auto_deactivated = get_option('propertyhive_template_assistant_auto_deactivated', '');
            if ( !empty($propertyhive_template_assistant_auto_deactivated) )
            {
            	// Only show if they had the TA active and we deactived it. Don't want it showing for new users
	            $settings[] = include( 'settings/class-ph-settings-template-assistant.php' ); // Maybe temporary after migrating TA code into core. Remove in future version
	        }
	        $settings[] = include( 'settings/class-ph-settings-frontend.php' );
            $settings[] = include( 'settings/class-ph-settings-emails.php' );
            $settings[] = include( 'settings/class-ph-settings-features.php' );
            $settings[] = include( 'settings/class-ph-settings-licenses.php' );

			// Only show demo data tab if demo data add on not active, tab not dismissed and if newly installed since 2021-04-13 00:00:00
            if ( 
            	!class_exists('PH_Demo_Data') && 
            	get_option( 'propertyhive_install_timestamp', '' ) >= 1618268400 &&
            	get_option( 'propertyhive_hide_demo_data_tab', '' ) != 'yes'
            )
            {
            	$settings[] = include( 'settings/class-ph-settings-demo-data.php' );
            }

			self::$settings = apply_filters( 'propertyhive_get_settings_pages', $settings );
		}
		return self::$settings;
	}

	/**
	 * Save the settings
	 */
	public static function save() {
		global $current_section, $current_tab;

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'propertyhive' ), '', array( 'response' => 403 ) );
		}

		if ( empty( $_REQUEST['_wpnonce'] ) || ! is_string( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'propertyhive-settings' ) )
	    		die( esc_html(__( 'Action failed. Please refresh the page and retry.', 'propertyhive' )) );

	    // Trigger actions
	   	do_action( 'propertyhive_settings_save_' . $current_tab );
	    do_action( 'propertyhive_update_options_' . $current_tab );
	    do_action( 'propertyhive_update_options' );

		self::add_message( __( 'Your settings have been saved.', 'propertyhive' ) );

		update_option( 'propertyhive_queue_flush_rewrite_rules', 'yes' );

		do_action( 'propertyhive_settings_saved' );
	}

	/**
	 * Add a message
	 * @param string $text
	 */
	public static function add_message( $text ) {
		self::$messages[] = $text;
	}

	/**
	 * Add an error
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$errors[] = $text;
	}

	/**
	 * Output messages + errors
	 */
	public static function show_messages() {
		if ( sizeof( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error )
			{
				$allowed_tags = array(
				    'a'      => array(
				        'href' => array(),
				    ),
				);

				$error = wp_kses($error, $allowed_tags);

				echo '<div id="message" class="error fade"><p><strong>' . wp_kses( $error, $allowed_tags ) . '</strong></p></div>';
			}
		} elseif ( sizeof( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message )
			{
				$allowed_tags = array(
				    'a'      => array(
				        'href' => array(),
				    ),
				);

				$message = wp_kses($message, $allowed_tags);

				echo '<div id="message" class="updated fade"><p><strong>' . wp_kses( $message, $allowed_tags ) . '</strong></p></div>';
			}
		}
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main propertyhive settings page in admin.
	 *
	 * @access public
	 * @return void
	 */
	public static function output() {
	    global $current_section, $current_tab, $redirect_after_save;

	    do_action( 'propertyhive_settings_start' );

	    //wp_enqueue_script( 'propertyhive_settings', PH()->plugin_url() . '/assets/js/admin/settings.min.js', array( 'jquery'/*, 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris', 'chosen'*/ ), PH()->version, true );

		/*wp_localize_script( 'propertyhive_settings', 'propertyhive_settings_params', array(
			'i18n_nav_warning' => __( 'The changes you made will be lost if you navigate away from this page.', 'propertyhive' )
		) );*/

		// Include settings pages
		self::get_settings_pages();

		// Get current tab/section
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These values only select the read-only settings view; settings writes are handled by save_fields() after the settings nonce and capability checks.
		$request_get = wp_unslash( $_GET );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- These values only select the read-only settings view; settings writes are handled by save_fields() after the settings nonce and capability checks.
		$request_request = wp_unslash( $_REQUEST );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Shared admin settings-view state; this global is intentionally used to control the common settings template and is not an arbitrary application global.
		$current_tab     = ( isset( $request_get['tab'] ) && is_string( $request_get['tab'] ) && '' !== $request_get['tab'] ) ? sanitize_title( $request_get['tab'] ) : 'general';
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Shared admin settings-view state; this global is intentionally used to control the common settings template and is not an arbitrary application global.
		$current_section = ( isset( $request_request['section'] ) && is_string( $request_request['section'] ) ) ? sanitize_title( $request_request['section'] ) : '';

	    // Save settings if data has been posted
	    //if ( ! empty( $_POST ) )
	    //	self::save();

	    // Add any posted messages
	    $message_allowed_tags = array(
	        'a' => array(
	            'href' => array(),
	        ),
	    );
	    if ( isset( $request_get['ph_error'] ) && is_scalar( $request_get['ph_error'] ) && '' !== (string) $request_get['ph_error'] )
	        self::add_error( wp_kses( (string) $request_get['ph_error'], $message_allowed_tags ) );

	     if ( isset( $request_get['ph_message'] ) && is_scalar( $request_get['ph_message'] ) && '' !== (string) $request_get['ph_message'] )
	        self::add_message( wp_kses( (string) $request_get['ph_message'], $message_allowed_tags ) );

	    self::show_messages();

	    // Get tabs for the settings page
	    $tabs = apply_filters( 'propertyhive_settings_tabs_array', array() );

	    include 'views/html-admin-settings.php';
	}

	/**
	 * Get a setting from the settings API.
	 *
	 * @param mixed $option
	 * @return string
	 */
	public static function get_option( $option_name, $default = '' ) {
		// Array value
		if ( strstr( $option_name, '[' ) ) {

			parse_str( $option_name, $option_array );

			// Option name is first key
			$option_name = current( array_keys( $option_array ) );

			// Get value
			$option_values = get_option( $option_name, '' );

			$key = key( $option_array[ $option_name ] );

			if ( isset( $option_values[ $key ] ) )
				$option_value = $option_values[ $key ];
			else
				$option_value = null;

		// Single value
		} else {
			$option_value = get_option( $option_name, null );
		}

		if ( is_array( $option_value ) )
			$option_value = array_map( 'stripslashes', $option_value );
		elseif ( ! is_null( $option_value ) )
			$option_value = stripslashes( $option_value );

		return $option_value === null ? $default : $option_value;
	}

	/**
	 * Output admin fields.
	 *
	 * Loops though the propertyhive options array and outputs each field.
	 *
	 * @access public
	 * @param array $options Opens array to output
	 */
	public static function output_fields( $options ) {
	    foreach ( $options as $value ) {
	    	if ( ! isset( $value['type'] ) ) continue;
	    	if ( ! isset( $value['id'] ) ) $value['id'] = '';
	    	if ( ! isset( $value['title'] ) ) $value['title'] = isset( $value['name'] ) ? $value['name'] : '';
	    	if ( ! isset( $value['class'] ) ) $value['class'] = '';
	    	if ( ! isset( $value['css'] ) ) $value['css'] = '';
	    	if ( ! isset( $value['default'] ) ) $value['default'] = '';
	    	if ( ! isset( $value['desc'] ) ) $value['desc'] = '';
	    	if ( ! isset( $value['desc_tip'] ) ) $value['desc_tip'] = false;

	    	// Custom attribute handling
			$custom_attributes = array();

			if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) )
				foreach ( $value['custom_attributes'] as $attribute => $attribute_value )
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';

			// Description handling
			if ( $value['desc_tip'] === true ) {
				$description = '';
				$tip = $value['desc'];
			} elseif ( ! empty( $value['desc_tip'] ) ) {
				$description = $value['desc'];
				$tip = $value['desc_tip'];
			} elseif ( ! empty( $value['desc'] ) ) {
				$description = $value['desc'];
				$tip = '';
			} else {
				$description = $tip = '';
			}

			if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ) ) ) {
				$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
			} elseif ( $description && in_array( $value['type'], array( 'checkbox' ) ) ) {
				$description =  wp_kses_post( $description );
			} elseif ( $description ) {
				$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
			}

			if ( $tip && in_array( $value['type'], array( 'checkbox' ) ) ) {

				$tip = '<p class="description">' . wp_kses_post( $tip ) . '</p>';

			} elseif ( $tip ) {

				$tip = '<img class="help_tip" data-tip="' . esc_attr( wp_kses_post( $tip ) ) . '" src="' . esc_url( PH()->plugin_url() . '/assets/images/help.png' ) . '" height="16" width="16" />';

			}

			// Switch based on type
	        switch( $value['type'] ) {

	        	// Section Titles
	            case 'title':
	            	if ( ! empty( $value['title'] ) ) {
	            		echo '<h3>' . esc_html( $value['title'] ) . '</h3>';
	            	}
	            	if ( ! empty( $value['desc'] ) ) {
                        echo wp_kses_post( wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) ) );
	            	}
	            	echo '<table class="form-table">'. "\n\n";
	            	if ( ! empty( $value['id'] ) ) {
	            		do_action( 'propertyhive_settings_' . sanitize_title( $value['id'] ) );
	            	}
	            break;

	            // Section Ends
	            case 'sectionend':
	            	if ( ! empty( $value['id'] ) ) {
	            		do_action( 'propertyhive_settings_' . sanitize_title( $value['id'] ) . '_end' );
	            	}
	            	echo '</table>';
	            	if ( ! empty( $value['id'] ) ) {
	            		do_action( 'propertyhive_settings_' . sanitize_title( $value['id'] ) . '_after' );
	            	}
	            break;
                
                case 'html':
                	$full_width = ( isset($value['full_width']) && is_bool($value['full_width']) ) ? $value['full_width'] : false;
                ?>
                <tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
                		<?php if ( $full_width !== true ) { ?>
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo wp_kses_post($tip); ?>
                        </th>
                    	<?php } ?>
                        <td class="forminp forminp-<?php echo esc_attr(sanitize_title( $value['type'] )); ?>">
                            <?php
                                $allowed_html = wp_kses_allowed_html( 'post' );

								$allowed_html['fieldset'] = array(
									'id'    => true,
									'class' => true,
								);

								$allowed_html['legend'] = array(
									'class' => true,
								);

								$allowed_html['label'] = array(
									'for'   => true,
									'class' => true,
								);

								$allowed_html['input'] = array(
									'type'        => true,
									'name'        => true,
									'id'          => true,
									'value'       => true,
									'class'       => true,
									'style'       => true,
									'checked'     => true,
									'disabled'    => true,
									'placeholder' => true,
								);

								$allowed_html['select'] = array(
									'name'     => true,
									'id'       => true,
									'class'    => true,
									'style'    => true,
									'multiple' => true,
									'disabled' => true,
								);

								$allowed_html['option'] = array(
									'value'    => true,
									'selected' => true,
									'disabled' => true,
								);

								/**
								 * Scripts are permitted for backward compatibility because existing
								 * Property Hive extensions use HTML settings fields to output inline
								 * administration scripts. To be revised in future after mentioned
								 * extensions have been updated
								 */
								$allowed_html['script'] = array(
									'type' => true,
									'src'  => true,
								);

								$allowed_html['a']['data-department'] = true;

								$allowed_html = apply_filters(
									'propertyhive_admin_settings_html_allowed_tags',
									$allowed_html,
									$value
								);

								echo wp_kses( $value['html'], $allowed_html );
                            ?>
                        </td>
                    </tr>
                <?php
                break;

	            // Standard text inputs and subtypes like 'number'
	            case 'text':
	            case 'email':
	            case 'number':
	            case 'color' :
	            case 'password' :

	            	$type 			= $value['type'];
	            	$class 			= '';
	            	$option_value 	= self::get_option( $value['id'], $value['default'] );

	            	if ( $value['type'] == 'color' ) {
	            		$type = 'text';
	            		$value['class'] .= 'colorpick';
		            	$description .= '<div id="colorPickerDiv_' . esc_attr( $value['id'] ) . '" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>';
	            	}

	            	?><tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo wp_kses_post( $tip ); ?>
						</th>
	                    <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ) ?>">
	                    	<input
	                    		name="<?php echo esc_attr( $value['id'] ); ?>"
	                    		id="<?php echo esc_attr( $value['id'] ); ?>"
	                    		type="<?php echo esc_attr( $type ); ?>"
	                    		style="<?php echo esc_attr( $value['css'] ); ?>"
	                    		value="<?php echo esc_attr( $option_value ); ?>"
	                    		class="<?php echo esc_attr( $value['class'] ); ?>"
                                <?php
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Every custom attribute name and value is escaped when assembled above; retain trusted PHP settings attributes.
                                echo implode( ' ', $custom_attributes );
                            ?>
                                /> <?php echo wp_kses_post( $description ); ?>
	                    </td>
	                </tr><?php
	            break;
                
                // Hidden
                case 'hidden':
                    
                    $option_value   = self::get_option( $value['id'], $value['default'] );
                    
                    ?><input type="hidden" 
                        name="<?php echo esc_attr( $value['id'] ); ?>" 
                        value="<?php echo esc_attr( $option_value ); ?>"
                        /><?php
                    
                break;
                    
	            // Textarea
	            case 'textarea':

	            	$option_value 	= self::get_option( $value['id'], $value['default'] );

	            	?><tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo wp_kses_post( $tip ); ?>
						</th>
	                    <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ) ?>">
                            <?php echo wp_kses_post( $description ); ?>

	                        <textarea
	                        	name="<?php echo esc_attr( $value['id'] ); ?>"
	                        	id="<?php echo esc_attr( $value['id'] ); ?>"
	                        	style="<?php echo esc_attr( $value['css'] ); ?>"
	                        	class="<?php echo esc_attr( $value['class'] ); ?>"
                                <?php
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Every custom attribute name and value is escaped when assembled above; retain trusted PHP settings attributes.
                                echo implode( ' ', $custom_attributes );
                            ?>
	                        	><?php echo esc_textarea( $option_value );  ?></textarea>
	                    </td>
	                </tr><?php
	            break;

	            // WYSIWYG
	            case 'wysiwyg':

	            	$option_value 	= self::get_option( $value['id'], $value['default'] );

	            	?><tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo wp_kses_post( $tip ); ?>
						</th>
	                    <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ) ?>">
	                    	
	                    	<?php wp_editor( $option_value, esc_attr( $value['id'] ), array( 'media_buttons' => false, 'textarea_rows' => 3, 'teeny' => true ) ); ?>

                            <?php echo '<br>' . wp_kses_post( $description ); ?>

	                        <?php /*<textarea
	                        	name="<?php echo esc_attr( $value['id'] ); ?>"
	                        	id="<?php echo esc_attr( $value['id'] ); ?>"
                                <?php
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Every custom attribute name and value is escaped when assembled above; retain trusted PHP settings attributes.
                                echo implode( ' ', $custom_attributes );
                            ?>
	                        	><?php echo esc_textarea( $option_value );  ?></textarea>*/ ?>
	                    </td>
	                </tr><?php
	            break;

	            // Select boxes
	            case 'select' :
	            case 'multiselect' :

	            	$option_value 	= self::get_option( $value['id'], $value['default'] );

	            	?><tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo wp_kses_post( $tip ); ?>
						</th>
	                    <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ) ?>">
	                    	<select
	                    		name="<?php echo esc_attr( $value['id'] ); ?><?php if ( $value['type'] == 'multiselect' ) echo '[]'; ?>"
	                    		id="<?php echo esc_attr( $value['id'] ); ?>"
	                    		style="<?php echo esc_attr( $value['css'] ); ?>"
	                    		class="<?php echo esc_attr( $value['class'] ); ?>"
                                <?php
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Every custom attribute name and value is escaped when assembled above; retain trusted PHP settings attributes.
                                echo implode( ' ', $custom_attributes );
                            ?>
	                    		<?php if ( $value['type'] == 'multiselect' ) echo 'multiple="multiple"'; ?>
	                    		>
		                    	<?php
			                        foreach ( $value['options'] as $key => $val ) {
			                        	?>
			                        	<option value="<?php echo esc_attr( $key ); ?>" <?php

				                        	if ( is_array( $option_value ) )
				                        		selected( in_array( $key, $option_value ), true );
				                        	else
				                        		selected( $option_value, $key );

                                        ?>><?php echo esc_html( $val ); ?></option>
			                        	<?php
			                        }
			                    ?>
	                       </select> <?php echo wp_kses_post( $description ); ?>
	                    </td>
	                </tr><?php
	            break;

	            // Radio inputs
	            case 'radio' :

	            	$option_value 	= self::get_option( $value['id'], $value['default'] );

	            	?><tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo wp_kses_post( $tip ); ?>
						</th>
	                    <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ) ?>">
	                    	<fieldset>
                                <?php echo wp_kses_post( $description ); ?>
	                    		<ul>
	                    		<?php
	                    			foreach ( $value['options'] as $key => $val ) {
			                        	?>
			                        	<li>
			                        		<label><input
				                        		name="<?php echo esc_attr( $value['id'] ); ?>"
                                                value="<?php echo esc_attr( $key ); ?>"
				                        		type="radio"
					                    		style="<?php echo esc_attr( $value['css'] ); ?>"
					                    		class="<?php echo esc_attr( $value['class'] ); ?>"
                                                <?php
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Every custom attribute name and value is escaped when assembled above; retain trusted PHP settings attributes.
                                echo implode( ' ', $custom_attributes );
                            ?>
					                    		<?php checked( $key, $option_value ); ?>
                                                /> <?php echo wp_kses_post( $val ); ?></label>
			                        	</li>
			                        	<?php
			                        }
	                    		?>
	                    		</ul>
	                    	</fieldset>
	                    </td>
	                </tr><?php
	            break;

	            // Checkbox input
	            case 'checkbox' :

	            	$name  = isset($value['name']) && $value['name'] != '' ? ph_clean($value['name']) : $value['id'];
					$option_value = isset($value['value']) ? ph_clean($value['value']) : self::get_option( $value['id'], $value['default'] );
					$fieldset_css = isset($value['fieldset_css']) ? ph_clean($value['fieldset_css']) : '';

					$visbility_class = array();

	            	if ( ! isset( $value['hide_if_checked'] ) ) {
	            		$value['hide_if_checked'] = false;
	            	}
	            	if ( ! isset( $value['show_if_checked'] ) ) {
	            		$value['show_if_checked'] = false;
	            	}
	            	if ( $value['hide_if_checked'] == 'yes' || $value['show_if_checked'] == 'yes' ) {
	            		$visbility_class[] = 'hidden_option';
	            	}
	            	if ( $value['hide_if_checked'] == 'option' ) {
	            		$visbility_class[] = 'hide_options_if_checked';
	            	}
	            	if ( $value['show_if_checked'] == 'option' ) {
	            		$visbility_class[] = 'show_options_if_checked';
	            	}

	            	if ( ! isset( $value['checkboxgroup'] ) || 'start' == $value['checkboxgroup'] ) {
	            		?>
		            		<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>" id="row_<?php echo esc_attr( $value['id'] ); ?>">
								<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
								<td class="forminp forminp-checkbox">
									<fieldset style="<?php echo esc_attr( $fieldset_css ); ?>">
						<?php
	            	} else { 
	            		?>
                            <fieldset style="<?php echo esc_attr( $fieldset_css ); ?>" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
	            		<?php
	            	}

	            	if ( ! empty( $value['title'] ) ) {
	            		?>
	            			<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ) ?></span></legend>
	            		<?php
	            	}

	            	?>
						<label for="<?php echo esc_attr( $value['id'] ); ?>">
							<input
								name="<?php echo esc_attr( $name ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="checkbox"
								value="1"
								<?php checked( $option_value, 'yes'); ?>
								<?php
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Every custom attribute name and value is escaped when assembled above; retain trusted PHP settings attributes.
                                echo implode( ' ', $custom_attributes );
                            ?>
							/> <?php echo wp_kses_post( $description ); ?>
						</label> <?php echo wp_kses_post( $tip ); ?>
					<?php

					if ( ! isset( $value['checkboxgroup'] ) || 'end' == $value['checkboxgroup'] ) {
									?>
									</fieldset>
								</td>
							</tr>
						<?php
					} else {
						?>
							</fieldset>
						<?php
					}
	            break;
                
	            // Image width settings
	            case 'image_width' :

	            	$width 	= self::get_option( $value['id'] . '[width]', $value['default']['width'] );
	            	$height = self::get_option( $value['id'] . '[height]', $value['default']['height'] );

	            	?><tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
						<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?> <?php echo wp_kses_post( $tip ); ?></th>
	                    <td class="forminp image_width_settings">

                            <input name="<?php echo esc_attr( $value['id'] ); ?>[width]" id="<?php echo esc_attr( $value['id'] ); ?>-width" type="text" size="3" value="<?php echo esc_attr( $width ); ?>" /> &times; <input name="<?php echo esc_attr( $value['id'] ); ?>[height]" id="<?php echo esc_attr( $value['id'] ); ?>-height" type="text" size="3" value="<?php echo esc_attr( $height ); ?>" />px

                            <label><input name="<?php echo esc_attr( $value['id'] ); ?>[crop]" id="<?php echo esc_attr( $value['id'] ); ?>-crop" type="checkbox" <?php checked( 1, self::get_option( $value['id'] . '[crop]', $value['default']['crop'] ) ); ?> /> <?php esc_html_e( 'Hard Crop?', 'propertyhive' ); ?></label>

	                    	</td>
	                </tr><?php
	            break;

	            // Image
	            case 'image' :

	            	$option_value = self::get_option( $value['id'], $value['default'] );

	            	?>
	            	<tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>_uploaded" <?php if ( $option_value == '' ) { echo ' style="display:none"'; } ?>>
						<th scope="row" class="titledesc"><?php echo esc_html( __( 'Uploaded', 'propertyhive' ) . ' ' . $value['title'] ); ?></th>
	                    <td class="forminp image_settings">
	                    <?php
	                    	$image = wp_get_attachment_image_src( $option_value, 'thumbnail' );
							if ($image !== FALSE)
							{
								echo '<img src="' . esc_url( $image[0] ) . '" width="150" alt="">';
							}
							else
							{
								echo 'Image doesn\'t exist';
							}
	                    ?>
	                    </td>
	                </tr>
	            	<tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
						<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?> <?php echo wp_kses_post( $tip ); ?></th>
	                    <td class="forminp image_settings">

                            <a href="" data-ph-image-field="<?php echo esc_attr( $value['id'] ); ?>" class="button button-primary ph_upload_photo_button<?php echo esc_attr( $value['id'] ); ?>">Select Image</a>
                            <input name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" type="hidden" value="<?php echo esc_attr( $option_value ); ?>" />

	                    </td>
	                </tr><?php
                echo '<script>
(function(fieldId) {
    jQuery(function($) {
        $(document.body).on("click", "[data-ph-image-field]", function(event) {
            if ($(this).attr("data-ph-image-field") !== fieldId) { return; }
            event.preventDefault();
            var frameKey = "file_frame" + fieldId;
            var frame = wp.media.frames[frameKey] || window[frameKey];
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: $(this).data("uploader_title"),
                button: { text: $(this).data("uploader_button_text") },
                multiple: false
            });
            wp.media.frames[frameKey] = window[frameKey] = frame;
            frame.on("select", function() {
                frame.state().get("selection").map(function(attachment) {
                    attachment = attachment.toJSON();
                    var row = $(document.getElementById("row_" + fieldId + "_uploaded"));
                    row.show().find("td").empty().append($("<img>", { src: attachment.url, width: 150, alt: "" }));
                    $(document.getElementById(fieldId)).val(attachment.id);
                });
            });
            frame.open();
        });
    });
})(' . wp_json_encode( (string) $value['id'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ');
</script>';
	            break;

	            // Single page selects
	            case 'single_select_page' :

	            	$args = array( 'name'				=> $value['id'],
	            				   'id'					=> $value['id'],
	            				   'sort_column' 		=> 'menu_order',
	            				   'sort_order'			=> 'ASC',
	            				   'show_option_none' 	=> ' ',
	            				   'class'				=> $value['class'],
	            				   'echo' 				=> false,
	            				   'selected'			=> absint( self::get_option( $value['id'] ) )
	            				   );

	            	if( isset( $value['args'] ) )
	            		$args = wp_parse_args( $value['args'], $args );

	            	?><tr valign="top" class="single_select_page" id="row_<?php echo esc_attr( $value['id'] ); ?>">
	                    <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?> <?php echo wp_kses_post( $tip ); ?></th>
	                    <td class="forminp">
                            <?php
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_dropdown_pages() produces escaped select HTML; all inserted attribute values are escaped here and trusted core filters retain their HTML contract.
                                echo str_replace(' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'propertyhive' ) .  "' style='" . esc_attr( $value['css'] ) . "' class='" . esc_attr( $value['class'] ) . "' id=", wp_dropdown_pages( $args ) ); ?> <?php echo wp_kses_post( $description ); ?>
				        </td>
	               	</tr><?php
	            break;

	            // Single country selects
	            case 'single_select_country' :
					$country_setting = (string) self::get_option( $value['id'] );
					$countries       = PH()->countries->countries;

	            	if ( strstr( $country_setting, ':' ) ) {
						$country_setting = explode( ':', $country_setting );
						$country         = current( $country_setting );
	            	} else {
						$country = $country_setting;
	            	}
	            	?><tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo wp_kses_post( $tip ); ?>
						</th>
	                    <td class="forminp">
		                    <select name="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>">
					        	<?php PH()->countries->country_dropdown_options( $country ); ?>
					        </select>
					        <?php echo wp_kses_post( $description ); ?>
	               		</td>
	               	</tr><?php
	            break;

	            // Country multiselects
	            case 'multi_select_countries' :

	            	$selections = (array) self::get_option( $value['id'] );

	            	if ( ! empty( $value['options'] ) )
	            		$countries = $value['options'];
	            	else
	            		$countries = PH()->countries->countries;

	            	asort( $countries );
	            	?><tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo wp_kses_post( $tip ); ?>
						</th>
	                    <td class="forminp">
		                    <select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" style="<?php echo esc_attr( $value['css'] ); ?>">
					        	<?php
					        		if ( $countries )
					        			foreach ( $countries as $key => $val )
                                            echo '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $selections ), true, false ).'>' . esc_html( $val['name'] ) . '</option>';
		                    	?>
					        </select> <?php if ( $description ) echo wp_kses_post( $description ); ?>
	               		</td>
	               	</tr><?php
	            break;

	            // Default: run an action
	            default:
	            	do_action( 'propertyhive_admin_field_' . $value['type'], $value );
	            break;
	    	}
		}
	}

	/**
	 * Save admin fields.
	 *
	 * Loops though the propertyhive options array and outputs each field.
	 *
	 * @access public
	 * @param array $options Opens array to output
	 * @return bool
	 */
	public static function save_fields( $options ) {
        if ( ! current_user_can( 'manage_options' ) || ! isset( $_REQUEST['_wpnonce'] ) || ! is_string( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'propertyhive-settings' ) ) {
            return;
        }

	    if ( empty( $_POST ) )
	    	return false;

	    // The settings nonce and manage_options capability were verified above.
	    $request_post = wp_unslash( $_POST );

	    // Options to update will be stored here
	    $update_options = array();

	    // Loop options and get values to save
	    foreach ( $options as $value ) {

	    	if ( ! isset( $value['id'] ) )
	    		continue;

	    	$type = isset( $value['type'] ) ? sanitize_title( $value['type'] ) : '';

	    	// Get the option name
	    	$option_value = null;

	    	switch ( $type ) {

		    	// Standard types
		    	case "checkbox" :

				if ( isset( $request_post[ $value['id'] ] ) ) {
		    			$option_value = 'yes';
		            } else {
		            	$option_value = 'no';
		            }

		    	break;

		    	case "textarea" :
		    	case "wysiwyg" :

				    if ( isset( $request_post[$value['id']] ) && is_scalar( $request_post[$value['id']] ) ) {
					$option_value = wp_kses_post( trim( $request_post[ $value['id'] ] ) );
		            } else {
		                $option_value = '';
		            }

		    	break;

		    	case "text" :
		    	case 'email':
	            case 'number':
		    	case "select" :
		    	case "color" :
	            case 'password' :
		    	case "single_select_page" :
		    	case "single_select_country" :
		    	case 'radio' :

				       if ( isset( $request_post[$value['id']] ) && is_scalar( $request_post[$value['id']] ) ) {
			$option_value = sanitize_text_field( $request_post[ $value['id'] ] );
		            } else {
		                $option_value = '';
		            }

		    	break;

		    	// Special types
		    	case "multiselect" :
		    	case "multi_select_countries" :

					// Get countries array
					$selected_countries = array();
					if ( isset( $request_post[ $value['id'] ] ) ) {
						foreach ( (array) $request_post[ $value['id'] ] as $selected_country ) {
							if ( is_scalar( $selected_country ) ) {
								$selected_countries[] = ph_clean( $selected_country );
							}
						}
					}

					$option_value = $selected_countries;

		    	break;

		    	case "image_width" :

				$image_dimensions = ( isset( $request_post[ $value['id'] ] ) && is_array( $request_post[ $value['id'] ] ) ) ? $request_post[ $value['id'] ] : array();
				if ( isset( $image_dimensions['width'] ) && is_scalar( $image_dimensions['width'] ) ) {

			$update_options[ $value['id'] ]['width']  = ph_clean( $image_dimensions['width'] );
			$update_options[ $value['id'] ]['height'] = ( isset( $image_dimensions['height'] ) && is_scalar( $image_dimensions['height'] ) ) ? ph_clean( $image_dimensions['height'] ) : $value['default']['height'];

						if ( isset( $image_dimensions['crop'] ) )
							$update_options[ $value['id'] ]['crop'] = 1;
						else
							$update_options[ $value['id'] ]['crop'] = 0;

		            } else {
		            	$update_options[ $value['id'] ]['width'] 	= $value['default']['width'];
		            	$update_options[ $value['id'] ]['height'] 	= $value['default']['height'];
		            	$update_options[ $value['id'] ]['crop'] 	= $value['default']['crop'];
		            }

		    	break;

		    	// Custom handling
		    	default :

		    		do_action( 'propertyhive_update_option_' . $type, $value );

		    	break;

	    	}

	    	if ( ! is_null( $option_value ) ) {
		    	// Check if option is an array
				if ( strstr( $value['id'], '[' ) ) {

					parse_str( $value['id'], $option_array );

		    		// Option name is first key
		    		$option_name = current( array_keys( $option_array ) );

		    		// Get old option value
		    		if ( ! isset( $update_options[ $option_name ] ) )
		    			 $update_options[ $option_name ] = get_option( $option_name, array() );

		    		if ( ! is_array( $update_options[ $option_name ] ) )
		    			$update_options[ $option_name ] = array();

		    		// Set keys and value
		    		$key = key( $option_array[ $option_name ] );

		    		$update_options[ $option_name ][ $key ] = $option_value;

				// Single value
				} else {
					$update_options[ $value['id'] ] = $option_value;
				}
			}

	    	// Custom handling
	    	do_action( 'propertyhive_update_option', $value );
	    }

	    // Now save the options
	    foreach( $update_options as $name => $value )
	    	update_option( $name, $value );

	    return true;
	}
}

endif;
