<?php
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
            $settings[] = include( 'settings/class-ph-settings-template-assistant.php' ); // Maybe temporary after migrating TA code into core. Remove in future version
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

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'propertyhive-settings' ) )
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

				echo '<div id="message" class="error fade"><p><strong>' . $error . '</strong></p></div>';
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

				echo '<div id="message" class="updated fade"><p><strong>' . $message . '</strong></p></div>';
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
	    global $current_section, $current_tab;

	    do_action( 'propertyhive_settings_start' );

	    //wp_enqueue_script( 'propertyhive_settings', PH()->plugin_url() . '/assets/js/admin/settings.min.js', array( 'jquery'/*, 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris', 'chosen'*/ ), PH()->version, true );

		/*wp_localize_script( 'propertyhive_settings', 'propertyhive_settings_params', array(
			'i18n_nav_warning' => __( 'The changes you made will be lost if you navigate away from this page.', 'propertyhive' )
		) );*/

		// Include settings pages
		self::get_settings_pages();

		// Get current tab/section
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

	    // Save settings if data has been posted
	    //if ( ! empty( $_POST ) )
	    //	self::save();

	    // Add any posted messages
	    if ( ! empty( $_GET['ph_error'] ) )
	    	self::add_error( stripslashes( $_GET['ph_error'] ) );

	     if ( ! empty( $_GET['ph_message'] ) )
	    	self::add_message( stripslashes( $_GET['ph_message'] ) );

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

				$tip = '<p class="description">' . $tip . '</p>';

			} elseif ( $tip ) {

				$tip = '<img class="help_tip" data-tip="' . esc_attr( $tip ) . '" src="' . PH()->plugin_url() . '/assets/images/help.png" height="16" width="16" />';

			}

			// Switch based on type
	        switch( $value['type'] ) {

	        	// Section Titles
	            case 'title':
	            	if ( ! empty( $value['title'] ) ) {
	            		echo '<h3>' . esc_html( $value['title'] ) . '</h3>';
	            	}
	            	if ( ! empty( $value['desc'] ) ) {
	            		echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
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
                ?>
                <tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo $tip; ?>
                        </th>
                        <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                            <?php echo $value['html']; ?>
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
							<?php echo $tip; ?>
						</th>
	                    <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
	                    	<input
	                    		name="<?php echo esc_attr( $value['id'] ); ?>"
	                    		id="<?php echo esc_attr( $value['id'] ); ?>"
	                    		type="<?php echo esc_attr( $type ); ?>"
	                    		style="<?php echo esc_attr( $value['css'] ); ?>"
	                    		value="<?php echo esc_attr( $option_value ); ?>"
	                    		class="<?php echo esc_attr( $value['class'] ); ?>"
	                    		<?php echo implode( ' ', $custom_attributes ); ?>
	                    		/> <?php echo $description; ?>
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
							<?php echo $tip; ?>
						</th>
	                    <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
	                    	<?php echo $description; ?>

	                        <textarea
	                        	name="<?php echo esc_attr( $value['id'] ); ?>"
	                        	id="<?php echo esc_attr( $value['id'] ); ?>"
	                        	style="<?php echo esc_attr( $value['css'] ); ?>"
	                        	class="<?php echo esc_attr( $value['class'] ); ?>"
	                        	<?php echo implode( ' ', $custom_attributes ); ?>
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
							<?php echo $tip; ?>
						</th>
	                    <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
	                    	
	                    	<?php wp_editor( $option_value, esc_attr( $value['id'] ), array( 'media_buttons' => false, 'textarea_rows' => 3, 'teeny' => true ) ); ?>

	                    	<?php echo '<br>' . $description; ?>

	                        <?php /*<textarea
	                        	name="<?php echo esc_attr( $value['id'] ); ?>"
	                        	id="<?php echo esc_attr( $value['id'] ); ?>"
	                        	<?php echo implode( ' ', $custom_attributes ); ?>
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
							<?php echo $tip; ?>
						</th>
	                    <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
	                    	<select
	                    		name="<?php echo esc_attr( $value['id'] ); ?><?php if ( $value['type'] == 'multiselect' ) echo '[]'; ?>"
	                    		id="<?php echo esc_attr( $value['id'] ); ?>"
	                    		style="<?php echo esc_attr( $value['css'] ); ?>"
	                    		class="<?php echo esc_attr( $value['class'] ); ?>"
	                    		<?php echo implode( ' ', $custom_attributes ); ?>
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

			                        	?>><?php echo $val ?></option>
			                        	<?php
			                        }
			                    ?>
	                       </select> <?php echo $description; ?>
	                    </td>
	                </tr><?php
	            break;

	            // Radio inputs
	            case 'radio' :

	            	$option_value 	= self::get_option( $value['id'], $value['default'] );

	            	?><tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tip; ?>
						</th>
	                    <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
	                    	<fieldset>
	                    		<?php echo $description; ?>
	                    		<ul>
	                    		<?php
	                    			foreach ( $value['options'] as $key => $val ) {
			                        	?>
			                        	<li>
			                        		<label><input
				                        		name="<?php echo esc_attr( $value['id'] ); ?>"
				                        		value="<?php echo $key; ?>"
				                        		type="radio"
					                    		style="<?php echo esc_attr( $value['css'] ); ?>"
					                    		class="<?php echo esc_attr( $value['class'] ); ?>"
					                    		<?php echo implode( ' ', $custom_attributes ); ?>
					                    		<?php checked( $key, $option_value ); ?>
				                        		/> <?php echo $val ?></label>
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
									<fieldset style="<?php echo $fieldset_css; ?>">
						<?php
	            	} else { 
	            		?>
		            		<fieldset style="<?php echo $fieldset_css; ?>" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
	            		<?php
	            	}

	            	if ( ! empty( $value['title'] ) ) {
	            		?>
	            			<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ) ?></span></legend>
	            		<?php
	            	}

	            	?>
						<label for="<?php echo $value['id'] ?>">
							<input
								name="<?php echo esc_attr( $name ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="checkbox"
								value="1"
								<?php checked( $option_value, 'yes'); ?>
								<?php echo implode( ' ', $custom_attributes ); ?>
							/> <?php echo $description ?>
						</label> <?php echo $tip; ?>
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
	            	$crop 	= checked( 1, self::get_option( $value['id'] . '[crop]', $value['default']['crop'] ), false );

	            	?><tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
						<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?> <?php echo $tip; ?></th>
	                    <td class="forminp image_width_settings">

	                    	<input name="<?php echo esc_attr( $value['id'] ); ?>[width]" id="<?php echo esc_attr( $value['id'] ); ?>-width" type="text" size="3" value="<?php echo $width; ?>" /> &times; <input name="<?php echo esc_attr( $value['id'] ); ?>[height]" id="<?php echo esc_attr( $value['id'] ); ?>-height" type="text" size="3" value="<?php echo $height; ?>" />px

	                    	<label><input name="<?php echo esc_attr( $value['id'] ); ?>[crop]" id="<?php echo esc_attr( $value['id'] ); ?>-crop" type="checkbox" <?php echo $crop; ?> /> <?php _e( 'Hard Crop?', 'propertyhive' ); ?></label>

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
								echo '<img src="' . $image[0] . '" width="150" alt="">';
							}
							else
							{
								echo 'Image doesn\'t exist';
							}
	                    ?>
	                    </td>
	                </tr>
	            	<tr valign="top" id="row_<?php echo esc_attr( $value['id'] ); ?>">
						<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?> <?php echo $tip; ?></th>
	                    <td class="forminp image_settings">

	                    	<a href="" class="button button-primary ph_upload_photo_button<?php echo esc_attr( $value['id'] ); ?>">Select Image</a>
	                    	<input name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" type="hidden" value="<?php echo $option_value; ?>" />

	                    </td>
	                </tr><?php
	                echo '<script>

		var file_frame' . $value['id'] . ';

		jQuery(document).ready(function()
        {
        	jQuery(\'body\').on(\'click\', \'.ph_upload_photo_button' . $value['id'] . '\', function( event ){
                 
	            event.preventDefault();
	         
	            // If the media frame already exists, reopen it.
	            if ( file_frame' . $value['id'] . ' ) {
	              file_frame' . $value['id'] . '.open();
	              return;
	            }
	         
	            // Create the media frame.
	            file_frame' . $value['id'] . ' = wp.media.frames.file_frame' . $value['id'] . ' = wp.media({
	              title: jQuery( this ).data( \'uploader_title\' ),
	              button: {
	                text: jQuery( this ).data( \'uploader_button_text\' ),
	              },
	              multiple: false  // Set to true to allow multiple files to be selected
	            });
	         
	            // When an image is selected, run a callback.
	            file_frame' . $value['id'] . '.on( \'select\', function() {
	                var selection = file_frame' . $value['id'] . '.state().get(\'selection\');

	                selection.map( function( attachment ) {
	             
	                    attachment = attachment.toJSON();
	             
	                    // Do something with attachment.id and/or attachment.url here
	                    console.log(attachment.url);
	                    
	                    // Add selected image to page
	                    //add_photo_attachment_to_grid(attachment);

	                    jQuery(\'#row_' . esc_attr( $value['id'] ) . '_uploaded\').show();
	                    jQuery(\'#row_' . esc_attr( $value['id'] ) . '_uploaded td\').html(\'<img src="\' + attachment.url + \'" width="150" alt="">\');
	                    jQuery(\'#' . esc_attr( $value['id'] ) . '\').val(attachment.id);
	                });
	            });
	         
	            // Finally, open the modal
	            file_frame' . $value['id'] . '.open();
	        });
		});

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
	                    <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?> <?php echo $tip; ?></th>
	                    <td class="forminp">
				        	<?php echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'propertyhive' ) .  "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ); ?> <?php echo $description; ?>
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
							<?php echo $tip; ?>
						</th>
	                    <td class="forminp">
		                    <select name="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>">
					        	<?php PH()->countries->country_dropdown_options( $country ); ?>
					        </select>
					        <?php echo $description; ?>
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
							<?php echo $tip; ?>
						</th>
	                    <td class="forminp">
		                    <select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" style="<?php echo esc_attr( $value['css'] ); ?>">
					        	<?php
					        		if ( $countries )
					        			foreach ( $countries as $key => $val )
		                    				echo '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $selections ), true, false ).'>' . $val['name'] . '</option>';
		                    	?>
					        </select> <?php if ( $description ) echo $description; ?>
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
	    if ( empty( $_POST ) )
	    	return false;

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

		    		if ( isset( $_POST[ $value['id'] ] ) ) {
		    			$option_value = 'yes';
		            } else {
		            	$option_value = 'no';
		            }

		    	break;

		    	case "textarea" :
		    	case "wysiwyg" :

			    	if ( isset( $_POST[$value['id']] ) ) {
			    		$option_value = wp_kses_post( trim( stripslashes( $_POST[ $value['id'] ] ) ) );
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

			       if ( isset( $_POST[$value['id']] ) ) {
		            	$option_value = sanitize_text_field( stripslashes( $_POST[ $value['id'] ] ) );
		            } else {
		                $option_value = '';
		            }

		    	break;

		    	// Special types
		    	case "multiselect" :
		    	case "multi_select_countries" :

		    		// Get countries array
					if ( isset( $_POST[ $value['id'] ] ) )
						$selected_countries = array_map( 'ph_clean', array_map( 'stripslashes', (array) $_POST[ $value['id'] ] ) );
					else
						$selected_countries = array();

					$option_value = $selected_countries;

		    	break;

		    	case "image_width" :

			    	if ( isset( $_POST[$value['id'] ]['width'] ) ) {

		              	$update_options[ $value['id'] ]['width']  = ph_clean( stripslashes( $_POST[ $value['id'] ]['width'] ) );
		              	$update_options[ $value['id'] ]['height'] = ph_clean( stripslashes( $_POST[ $value['id'] ]['height'] ) );

						if ( isset( $_POST[ $value['id'] ]['crop'] ) )
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

	/**
	 * Checks which method we're using to serve downloads
	 *
	 * If using force or x-sendfile, this ensures the .htaccess is in place
	 *
	 * @access public
	 * @return void
	 */
	/*public static function check_download_folder_protection() {
		$upload_dir 		= wp_upload_dir();
		$downloads_url 		= $upload_dir['basedir'] . '/propertyhive_uploads';
		$download_method	= get_option('propertyhive_file_download_method');

		if ( $download_method == 'redirect' ) {

			// Redirect method - don't protect
			if ( file_exists( $downloads_url . '/.htaccess' ) )
				unlink( $downloads_url . '/.htaccess' );

		} else {

			// Force method - protect, add rules to the htaccess file
			if ( ! file_exists( $downloads_url . '/.htaccess' ) ) {
				if ( $file_handle = @fopen( $downloads_url . '/.htaccess', 'w' ) ) {
					fwrite( $file_handle, 'deny from all' );
					fclose( $file_handle );
				}
			}
		}
	}*/
}

endif;
