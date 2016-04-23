<?php
/**
* PropertyHive Meta Box Functions
*
* @author      PropertyHive
* @category    Core
* @package     PropertyHive/Admin/Functions
* @version     1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Output a text input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function propertyhive_wp_text_input( $field ) {
	global $thepostid, $post, $propertyhive;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
	$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

	switch ( $data_type ) {
		case 'price' :
			$field['class'] .= ' ph_input_price';
			$field['value']  = ph_format_localized_price( $field['value'] );
		break;
		case 'decimal' :
			$field['class'] .= ' ph_input_decimal';
			$field['value']  = ph_format_localized_decimal( $field['value'] );
		break;
	}

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) )
		foreach ( $field['custom_attributes'] as $attribute => $value )
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';

	echo '
	<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
	   <label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>
	   <input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( PH()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

	}
	echo '</p>';
}

/**
 * Output a file input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function propertyhive_wp_photo_upload( $field ) {
	global $thepostid, $post, $propertyhive;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['id']  			= isset( $field['id'] ) ? $field['id'] : '';
	$field['button_label']  = isset( $field['button_label'] ) ? $field['button_label'] : __('Select Photo', 'propertyhive');
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) )
		foreach ( $field['custom_attributes'] as $attribute => $value )
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';

	propertyhive_wp_hidden_input( $field );

	echo '
	<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"';
	if ( $field['value'] == '' )
	{
		echo ' style="display:none;"';
	}
	echo '>
	   <label for="' . esc_attr( $field['id'] ) . '">' . __( 'Uploaded', 'propertyhive' ) . ' ' . wp_kses_post( $field['label'] ) . '</label>
	   <span>';
	if ( $field['value'] != '' )
	{
		$image = wp_get_attachment_image_src( $field['value'], 'thumbnail' );
		if ($image !== FALSE)
		{
			echo '<img src="' . $image[0] . '" width="150" alt="">';
		}
		else
		{
			echo 'Image doesn\'t exist';
		}
	}
	echo '</span>
	</p>';

	echo '
	<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
	   <label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>
	   <a href="" class="button button-primary ph_upload_photo_button' . $field['id'] . '">' . $field['button_label'] . '</a>';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( PH()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

	}
	echo '</p>';

	echo '<script>

		var file_frame' . $field['id'] . ';

		jQuery(document).ready(function()
        {
        	jQuery(\'.ph_upload_photo_button' . $field['id'] . '\').live(\'click\', function( event ){
                 
	            event.preventDefault();
	         
	            // If the media frame already exists, reopen it.
	            if ( file_frame' . $field['id'] . ' ) {
	              file_frame' . $field['id'] . '.open();
	              return;
	            }
	         
	            // Create the media frame.
	            file_frame' . $field['id'] . ' = wp.media.frames.file_frame' . $field['id'] . ' = wp.media({
	              title: jQuery( this ).data( \'uploader_title\' ),
	              button: {
	                text: jQuery( this ).data( \'uploader_button_text\' ),
	              },
	              multiple: false  // Set to true to allow multiple files to be selected
	            });
	         
	            // When an image is selected, run a callback.
	            file_frame' . $field['id'] . '.on( \'select\', function() {
	                var selection = file_frame' . $field['id'] . '.state().get(\'selection\');

	                selection.map( function( attachment ) {
	             
	                    attachment = attachment.toJSON();
	             
	                    // Do something with attachment.id and/or attachment.url here
	                    console.log(attachment.url);
	                    
	                    // Add selected image to page
	                    //add_photo_attachment_to_grid(attachment);

	                    jQuery(\'.form-field.' . esc_attr( $field['id'] ) . '_field\').show();
	                    jQuery(\'.form-field.' . esc_attr( $field['id'] ) . '_field span\').html(\'<img src="\' + attachment.url + \'" width="150" alt="">\');
	                    jQuery(\'#' . esc_attr( $field['id'] ) . '\').val(attachment.id);
	                });
	            });
	         
	            // Finally, open the modal
	            file_frame' . $field['id'] . '.open();
	        });
		});

	</script>';
}

/**
 * Output a hidden input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function propertyhive_wp_hidden_input( $field ) {
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['value'] = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );

	echo '<input type="hidden" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) .  '" /> ';
}

/**
 * Output a textarea input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function propertyhive_wp_textarea_input( $field ) {
	global $thepostid, $post, $propertyhive;

	$thepostid 				= empty( $thepostid ) ? $post->ID : $thepostid;
	$field['placeholder'] 	= isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$field['class'] 		= isset( $field['class'] ) ? $field['class'] : 'short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value'] 		= isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
    $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
    
	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) )
		foreach ( $field['custom_attributes'] as $attribute => $value )
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';

	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><textarea class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" rows="2" cols="20" ' . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $field['value'] ) . '</textarea> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( PH()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

	}
	echo '</p>';
}

/**
 * Output a checkbox input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function propertyhive_wp_checkbox( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
	$field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'yes';
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><input type="checkbox" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], false ) . ' /> ';

	if ( ! empty( $field['description'] ) ) echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';

	echo '</p>';
}

/**
 * Output a groupd of checkbox input boxes.
 *
 * @access public
 * @param array $field
 * @return void
 */
function propertyhive_wp_checkboxes( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['name'], true );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	echo '<fieldset class="form-field ' . esc_attr( $field['wrapper_class'] ) . '"><legend>' . wp_kses_post( $field['label'] ) . '</legend><ul class="ph-radios">';

	foreach ( $field['options'] as $key => $value ) {
		echo '<li><label><input type="checkbox" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '[]" id="' . esc_attr( $field['name'] ) . '_' . $key . '" value="' . esc_attr( $key ) . '" ' . ( ( !empty($field['value']) && in_array( $key, $field['value'] ) ) ? 'checked' : '' ) . ' /> ' . $value . '</label></li>';
	}

	echo '</ul>';

	if ( ! empty( $field['description'] ) ) echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';

	echo '</fieldset>';
}

/**
 * Output a select input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function propertyhive_wp_select( $field ) {
	global $thepostid, $post, $propertyhive;

	$thepostid 				= empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class'] 		= isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value'] 		= isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
	
	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '">';

	foreach ( $field['options'] as $key => $value ) {

		echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';

	}

	echo '</select> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( PH()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

	}
	echo '</p>';
}

/**
 * Output a select input box with optgroups.
 *
 * @access public
 * @param array $field
 * @return void
 */
function propertyhive_wp_select_optgroups( $field ) {
	global $thepostid, $post, $propertyhive;

	$thepostid 				= empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class'] 		= isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value'] 		= isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
	
	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '">';

	echo '<option value="" ' . selected( esc_attr( $field['value'] ), esc_attr( '' ), false ) . '></option>';

	foreach ( $field['options'] as $optgroup => $options ) {

		echo '<optgroup label="' . esc_html( $optgroup ) . '">';

		foreach ( $options as $key => $value ) {

			echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';

		}

		echo '</optgroup>';

	}

	echo '</select> ';

	if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( PH()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

	}
	echo '</p>';
}

/**
 * Output a radio input box.
 *
 * @access public
 * @param array $field
 * @return void
 */
function propertyhive_wp_radio( $field ) {
	global $thepostid, $post, $propertyhive;

	$thepostid 				= empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class'] 		= isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value'] 		= isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	echo '<fieldset class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><legend>' . wp_kses_post( $field['label'] ) . '</legend><ul class="ph-radios">';

    foreach ( $field['options'] as $key => $value ) {

		echo '<li><label><input
        		name="' . esc_attr( $field['name'] ) . '"
        		value="' . esc_attr( $key ) . '"
        		type="radio"
        		class="' . esc_attr( $field['class'] ) . '"
        		' . checked( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '
        		/> ' . esc_html( $value ) . '</label>
    	</li>';
	}
    echo '</ul>';

    if ( ! empty( $field['description'] ) ) {

		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( PH()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}

	}

    echo '</fieldset>';
}