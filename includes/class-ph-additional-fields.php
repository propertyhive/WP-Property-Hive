<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PH_Additional_Fields {

	public function __construct() {

		$current_settings = get_option( 'propertyhive_template_assistant', array() );

		if ( isset($current_settings['custom_fields']) && is_array($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
			add_action( 'propertyhive_property_meta_list_end', array( $this, 'display_custom_fields_on_website' ) );
	        add_filter( 'propertyhive_user_details_form_fields', array( $this, 'display_custom_fields_on_user_details' ), 10, 1 );
	        add_action( 'propertyhive_applicant_registered', array( $this, 'save_custom_fields_on_user_details' ), 10, 2 );
	        add_action( 'propertyhive_account_details_updated', array( $this, 'save_custom_fields_on_user_details' ), 10, 2 );

	        add_filter( 'propertyhive_property_query_meta_query', array( $this, 'custom_fields_in_meta_query' ) );

	        $post_types = array(
	        	'property',
	        	'contact',
	        	'enquiry',
	        	'appraisal',
	        	'viewing',
	        	'offer',
	        	'sale',
	        	'tenancy',
	        );

	        foreach ( $post_types as $post_type )
	        {
	        	add_filter( 'manage_edit-' . $post_type . '_columns', function($existing_columns) use ($post_type)
	        	{
	        		if ( !is_array( $existing_columns ) ) 
	        		{
			            $existing_columns = array();
			        }

			        $current_settings = get_option( 'propertyhive_template_assistant', array() );

			        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
			        {
			            foreach ( $current_settings['custom_fields'] as $custom_field )
			            {
			                if ( 
			                	isset($custom_field['admin_list']) && 
			                	$custom_field['admin_list'] == '1' && 
			                	substr($custom_field['meta_box'], 0, (strlen($post_type)+1)) == $post_type .'_' 
			                )
			                {
			                    $existing_columns[$custom_field['field_name']] = __( $custom_field['field_label'], 'propertyhive' );
			                }
			            }
			        }

			        return $existing_columns;
	        	} );

		        add_action( 'manage_' . $post_type . '_posts_custom_column', function($column, $post_id) use ($post_type)
	        	{
			        $current_settings = get_option( 'propertyhive_template_assistant', array() );

			        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
			        {
			            foreach ( $current_settings['custom_fields'] as $custom_field )
			            {
			                if ( 
			                	isset($custom_field['admin_list']) && 
			                	$custom_field['admin_list'] == '1' && 
			                	substr($custom_field['meta_box'], 0, (strlen($post_type)+1)) == $post_type . '_' && 
			                	$custom_field['field_name'] == $column 
			                )
			                {
			                    if ( get_post_meta( $post_id, $custom_field['field_name'], true ) != '' )
			                    {
			                        if ( $custom_field['field_type'] == 'multiselect' )
			                        {
			                            $values = get_post_meta( $post_id, $custom_field['field_name'], TRUE );
			                            if ( !empty($values) )
			                            {
			                                echo esc_html(is_array($values) ? implode(", ", $values) : $values);
			                            }
			                        }
			                        elseif ( $custom_field['field_type'] == 'date' )
			                        {
			                            echo date(get_option( 'date_format' ), strtotime(get_post_meta( $post_id, $custom_field['field_name'], true )));
			                        }
			                        elseif ( $custom_field['field_type'] == 'image' )
			                        {
			                            $image_id = get_post_meta( $post_id, $custom_field['field_name'], true );
			                            if ( $image_id != '' )
			                            {
			                                $image = wp_get_attachment_image_src( $image_id, 'thumbnail' );
			                                if ($image !== FALSE)
			                                {
			                                    echo '<img src="' . esc_url($image[0]) . '" width="150" alt="">';
			                                }
			                                else
			                                {
			                                    echo 'Image doesn\'t exist';
			                                }
			                            }
			                        }
			                        elseif ( $custom_field['field_type'] == 'file' )
			                        {
			                            $file = get_attached_file( get_post_meta( $post_id, $custom_field['field_name'], true ) );
			                            if ( $file !== FALSE )
			                            {
			                                $filename = basename( $file );
			                                echo '<a href="' . esc_url(wp_get_attachment_url(get_post_meta( $post_id, $custom_field['field_name'], true ))) . '" rel="noopener noreferrer" target="_blank">' . esc_html($filename) . '</a>';
			                            }
			                            else
			                            {
			                                echo 'File doesn\'t exist';
			                            }
			                        }
			                        else
			                        {
			                            echo nl2br(esc_html(get_post_meta( $post_id, $custom_field['field_name'], true )));
			                        }
			                    }
			                    break;
			                }
			            }
			        }
	        	}, 2, 2 );
		        
		        add_filter( 'manage_edit-' . $post_type . '_sortable_columns', function($columns) use ($post_type)
		        {
		        	$custom = array();

			        $current_settings = get_option( 'propertyhive_template_assistant', array() );

			        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
			        {
			            foreach ( $current_settings['custom_fields'] as $custom_field )
			            {
			                if ( 
			                	isset($custom_field['admin_list']) && 
			                	$custom_field['admin_list'] == '1' && 
			                	isset($custom_field['admin_list_sortable']) && 
			                	$custom_field['admin_list_sortable'] == '1' && 
			                	substr($custom_field['meta_box'], 0, (strlen($post_type)+1)) == $post_type . '_' 
			                )
			                {
			                    $custom[$custom_field['field_name']] = $custom_field['field_name'];
			                }
			            }
			        }

			        return wp_parse_args( $custom, $columns );
		        } );
		        
		        add_filter( 'request', function($vars) use ($post_type)
		        {
		        	if (
				        !is_admin() ||
				        !isset($vars['post_type']) ||
				        $vars['post_type'] !== $post_type ||
				        !isset($vars['orderby'])
				    ) {
				        return $vars;
				    }

		            $current_settings = get_option( 'propertyhive_template_assistant', array() );

		            if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
		            {
		                foreach ( $current_settings['custom_fields'] as $custom_field )
		                {
		                    if ( 
		                    	isset($custom_field['admin_list']) && 
		                    	$custom_field['admin_list'] == '1' && 
		                    	isset($custom_field['admin_list_sortable']) && 
		                    	$custom_field['admin_list_sortable'] == '1' && 
		                    	substr($custom_field['meta_box'], 0, (strlen($post_type)+1)) == $post_type . '_' 
		                    )
		                    {
		                        if ( $custom_field['field_name'] == $vars['orderby'] )
		                        {
		                            $vars = array_merge( $vars, array(
		                                'meta_key'  => $custom_field['field_name'],
		                                'orderby'   => 'meta_value'
		                            ) );
		                        }
		                    }
		                }
		            }

			        return $vars;
		        } );
	        }
	        
	        add_action( 'propertyhive_office_table_header_columns', array( $this, 'add_office_additional_field_table_header_column' ), 10 );
	        add_action( 'propertyhive_office_table_row_columns', array( $this, 'add_office_additional_field_table_row_column' ), 10 );

	        add_action( 'propertyhive_contact_applicant_requirements_details_fields', array( $this, 'add_applicant_requirements_fields' ), 10, 2 );
	        add_action( 'propertyhive_contact_applicant_requirements_residential_details_fields', array( $this, 'add_applicant_requirements_residential_fields' ), 10, 2 );
	        add_action( 'propertyhive_contact_applicant_requirements_residential_sales_details_fields', array( $this, 'add_applicant_requirements_residential_sales_fields' ), 10, 2 );
	        add_action( 'propertyhive_contact_applicant_requirements_residential_lettings_details_fields', array( $this, 'add_applicant_requirements_residential_lettings_fields' ), 10, 2 );
	        add_action( 'propertyhive_contact_applicant_requirements_commercial_details_fields', array( $this, 'add_applicant_requirements_commercial_fields' ), 10, 2 );

	        add_action( 'propertyhive_save_contact_applicant_requirements', array( $this, 'save_applicant_requirements_fields' ), 10, 2 );

	        add_filter( 'propertyhive_applicant_requirements_display', array( $this, 'applicant_requirements_display' ), 10, 3 );
	        add_filter( 'propertyhive_matching_properties_args', array( $this, 'matching_properties_args' ), 10, 3 );
	        add_filter( 'propertyhive_matching_applicants_check', array( $this, 'matching_applicants_check' ), 10, 4 );
	        add_filter( 'propertyhive_applicant_requirements_form_fields', array( $this, 'applicant_requirements_form_fields' ), 10, 2 );
	        add_action( 'propertyhive_applicant_registered', array( $this, 'applicant_registered' ), 10, 2 );
	        add_action( 'propertyhive_account_requirements_updated', array( $this, 'applicant_registered' ), 10, 2 );

	        add_filter( 'propertyhive_applicant_list_check', array( $this, 'applicant_list_check' ), 10, 3 );

	        add_filter( 'propertyhive_room_breakdown_data', array( $this, 'add_custom_fields_to_room_breakdown' ), 10, 3 ); // Applicable when Rooms / Student Accommodation add on active

	        $meta_boxes_done = array();
            $office_details_fields_exist = false;
            $offices_opening_section_done = false;
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( !in_array( $custom_field['meta_box'], $meta_boxes_done ) )
                {
                    if ( substr( $custom_field['meta_box'], 0, 6 ) == 'office' )
                    {
                        add_filter( 'propertyhive_' . $custom_field['meta_box'] . '_settings', function( $settings )
                        {
                            global $offices_opening_section_done;
                            
                            $current_id = empty( $_REQUEST['id'] ) ? '' : (int)$_REQUEST['id'];

                            $meta_box_being_done = str_replace( "propertyhive_", "", current_filter() );
                            $meta_box_being_done = str_replace( "_settings", "", $meta_box_being_done );

                            $current_settings = get_option( 'propertyhive_template_assistant', array() );

                            foreach ( $current_settings['custom_fields'] as $custom_field )
                            {
                                if ( $custom_field['meta_box'] == $meta_box_being_done )
                                {
                                    if ( !$offices_opening_section_done )
                                    {
                                        $settings[] = array( 'title' => __( 'Additional Fields', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'office_template_assistant_additional_field' );
                                        $offices_opening_section_done = true;
                                    }

                                    switch ( $custom_field['field_type'] )
                                    {
                                        case "image":
                                        {
                                            if ( !did_action( 'wp_enqueue_media' ) )
                                                wp_enqueue_media();

                                            $settings[] = array(
                                                'title'     => $custom_field['field_label'],
                                                'id'        => $custom_field['field_name'],
                                                'default'   => get_post_meta($current_id, $custom_field['field_name'], TRUE),
                                                'type'      => $custom_field['field_type'],
                                                'desc_tip'  =>  false,
                                            );
                                            break;
                                        }
                                        case "select":
                                        {
                                            $options = array('' => '');
                                            if ( isset($custom_field['dropdown_options']) && is_array($custom_field['dropdown_options']) && !empty($custom_field['dropdown_options']) )
                                            {
                                                foreach ( $custom_field['dropdown_options'] as $dropdown_option )
                                                {
                                                    $options[$dropdown_option] = $dropdown_option;
                                                }
                                            }

                                            $settings[] = array(
                                                'title'     => $custom_field['field_label'],
                                                'id'        => $custom_field['field_name'],
                                                'default'   => get_post_meta($current_id, $custom_field['field_name'], TRUE),
                                                'type'      => $custom_field['field_type'],
                                                'desc_tip'  => false,
                                                'options'   => $options,
                                            );
                                            break;
                                        }
                                        default:
                                        {
                                            $settings[] = array(
                                                'title'     => $custom_field['field_label'],
                                                'id'        => $custom_field['field_name'],
                                                'default'   => get_post_meta($current_id, $custom_field['field_name'], TRUE),
                                                'type'      => $custom_field['field_type'],
                                                'desc_tip'  =>  false,
                                            );
                                        }
                                    }
                                }
                            }

                            if ( $offices_opening_section_done )
                            {
                                $settings[] = array( 'type' => 'sectionend', 'id' => 'office_template_assistant_additional_field');
                            }

                            return $settings;
                        });

                        $office_details_fields_exist = true;

                        add_action( 'propertyhive_save_office', function( $post_id )
                        {
                            $meta_box_being_done = 'office_details';

                            $current_settings = get_option( 'propertyhive_template_assistant', array() );

                            if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
                            {
                                $current_settings['custom_fields'] = apply_filters( 'propertyhive_template_assistant_custom_fields_to_save', $current_settings['custom_fields'] );

                                foreach ( $current_settings['custom_fields'] as $custom_field )
                                {
                                    if ( $custom_field['meta_box'] == $meta_box_being_done )
                                    {
                                        update_post_meta( $post_id, $custom_field['field_name'], (isset($_POST[$custom_field['field_name']]) ? $_POST[$custom_field['field_name']] : '') );
                                    }
                                }
                            }
                        });
                    }
                    else
                    {
                        add_action( 'propertyhive_' . $custom_field['meta_box'] . '_fields', function()
                        {
                            global $thepostid;

                            $meta_box_being_done = str_replace( "propertyhive_", "", current_filter() );
                            $meta_box_being_done = str_replace( "_fields", "", $meta_box_being_done );

                            $current_settings = get_option( 'propertyhive_template_assistant', array() );

                            foreach ( $current_settings['custom_fields'] as $custom_field )
                            {
                                if ( $custom_field['meta_box'] == $meta_box_being_done )
                                {
                                    if ( isset($custom_field['field_type']) && $custom_field['field_type'] == 'select' )
                                    {
                                        $options = array('' => '');
                                        if ( isset($custom_field['dropdown_options']) && is_array($custom_field['dropdown_options']) && !empty($custom_field['dropdown_options']) )
                                        {
                                            foreach ( $custom_field['dropdown_options'] as $dropdown_option )
                                            {
                                                $options[$dropdown_option] = $dropdown_option;
                                            }
                                        }
                                        propertyhive_wp_select( apply_filters( 'propertyhive_template_assistant_custom_field_args_' . ltrim($custom_field['field_name'], '_'), array( 
                                            'id' => $custom_field['field_name'], 
                                            'label' => $custom_field['field_label'], 
                                            'desc_tip' => false,
                                            'options' => $options
                                        ), $thepostid ) );
                                    }
                                    elseif ( isset($custom_field['field_type']) && $custom_field['field_type'] == 'multiselect' )
                                    {
    ?>
    <p class="form-field <?php echo esc_attr($custom_field['field_name']); ?>_field"><label for="<?php echo esc_attr($custom_field['field_name']); ?>"><?php echo esc_html(__( $custom_field['field_label'], 'propertyhive' )); ?></label>
            <select id="<?php echo esc_attr($custom_field['field_name']); ?>" name="<?php echo esc_attr($custom_field['field_name']); ?>[]" multiple="multiple" data-placeholder="<?php echo esc_attr(__( 'Select ' . $custom_field['field_label'], 'propertyhive' )); ?>" class="multiselect attribute_values">
                <?php
                    $selected_values = get_post_meta( $thepostid, $custom_field['field_name'], true );
                    if ( !is_array($selected_values) && $selected_values == '' )
                    {
                        $selected_values = array();
                    }
                    elseif ( !is_array($selected_values) && $selected_values != '' )
                    {
                        $selected_values = array($selected_values);
                    }
                    
                    if ( isset($custom_field['dropdown_options']) && is_array($custom_field['dropdown_options']) && !empty($custom_field['dropdown_options']) )
                    {
                        foreach ( $custom_field['dropdown_options'] as $dropdown_option )
                        {
                            echo '<option value="' . esc_attr( $dropdown_option ) . '"';
                            if ( in_array( $dropdown_option, $selected_values ) )
                            {
                                echo ' selected';
                            }
                            echo '>' . esc_html( $dropdown_option ) . '</option>';
                        }
                    }
                ?>
            </select>
    <?php
                                    }
                                    elseif ( isset($custom_field['field_type']) && $custom_field['field_type'] == 'textarea' )
                                    {
                                        propertyhive_wp_textarea_input( apply_filters( 'propertyhive_template_assistant_custom_field_args_' . ltrim($custom_field['field_name'], '_'), array( 
                                            'id' => $custom_field['field_name'], 
                                            'label' => $custom_field['field_label'], 
                                            'desc_tip' => false,
                                            'type' => 'text'
                                        ), $thepostid ) );
                                    }
                                    elseif ( isset($custom_field['field_type']) && $custom_field['field_type'] == 'date' )
                                    {
                                        propertyhive_wp_text_input( apply_filters( 'propertyhive_template_assistant_custom_field_args_' . ltrim($custom_field['field_name'], '_'), array( 
                                            'id' => $custom_field['field_name'], 
                                            'label' => $custom_field['field_label'], 
                                            'desc_tip' => false,
                                            'type' => 'date',
                                            'class' => 'small',
                                        ), $thepostid ) );
                                    }
                                    elseif ( isset($custom_field['field_type']) && $custom_field['field_type'] == 'checkbox' )
                                    {
                                        propertyhive_wp_checkbox( apply_filters( 'propertyhive_template_assistant_custom_field_args_' . ltrim($custom_field['field_name'], '_'), array( 
                                            'id' => $custom_field['field_name'], 
                                            'label' => $custom_field['field_label'], 
                                            'desc_tip' => false,
                                        ), $thepostid ) );
                                    }
                                    elseif ( isset($custom_field['field_type']) && $custom_field['field_type'] == 'image' )
                                    {
                                        propertyhive_wp_photo_upload( apply_filters( 'propertyhive_template_assistant_custom_field_args_' . ltrim($custom_field['field_name'], '_'), array( 
                                            'id' => $custom_field['field_name'], 
                                            'label' => $custom_field['field_label'], 
                                            'desc_tip' => false,
                                            'button_label' => __( 'Select Image', 'propertyhive' )
                                        ), $thepostid ) );
                                    }
                                    elseif ( isset($custom_field['field_type']) && $custom_field['field_type'] == 'file' )
                                    {
                                        propertyhive_wp_file_upload( apply_filters( 'propertyhive_template_assistant_custom_field_args_' . ltrim($custom_field['field_name'], '_'), array( 
                                            'id' => $custom_field['field_name'], 
                                            'label' => $custom_field['field_label'], 
                                            'desc_tip' => false,
                                            'button_label' => __( 'Select File', 'propertyhive' )
                                        ), $thepostid ) );
                                    }
                                    else
                                    {
                                        propertyhive_wp_text_input( apply_filters( 'propertyhive_template_assistant_custom_field_args_' . ltrim($custom_field['field_name'], '_'), array( 
                                            'id' => $custom_field['field_name'], 
                                            'label' => $custom_field['field_label'], 
                                            'desc_tip' => false,
                                            'type' => 'text'
                                        ), $thepostid ) );
                                    }
                                }
                            }
                        });

                        add_action( 'propertyhive_save_' . $custom_field['meta_box'],  function( $post_id )
                        {
                            $meta_box_being_done = str_replace( "propertyhive_save_", "", current_filter() );

                            $current_settings = get_option( 'propertyhive_template_assistant', array() );

                            if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
                            {
                                $current_settings['custom_fields'] = apply_filters( 'propertyhive_template_assistant_custom_fields_to_save', $current_settings['custom_fields'] );

                                foreach ( $current_settings['custom_fields'] as $custom_field )
                                {
                                    if ( $custom_field['meta_box'] == $meta_box_being_done )
                                    {
                                    	if ( isset($custom_field['field_type']) && $custom_field['field_type'] == 'textarea' )
                                    	{
                                    		update_post_meta( $post_id, $custom_field['field_name'], (isset($_POST[$custom_field['field_name']]) ? sanitize_textarea_field($_POST[$custom_field['field_name']]) : '') );
                                    	}
                                    	else
                                    	{
	                                        update_post_meta( $post_id, $custom_field['field_name'], (isset($_POST[$custom_field['field_name']]) ? ph_clean($_POST[$custom_field['field_name']]) : '') );
	                                    }
                                    }
                                }
                            }
                        });
                    }

                    $meta_boxes_done[] = $custom_field['meta_box'];
                }
            }

            if ( $office_details_fields_exist  )
            {
                add_filter( 'propertyhive_' . $custom_field['meta_box'] . '_settings', function( $settings )
                {
                    $settings[] = array( 'type' => 'sectionend', 'id' => 'office_location_options' );

                    return $settings;
                });
            }

            $shortcodes = array(
                'properties',
                'recent_properties',
                'featured_properties',
                'similar_properties',
            );

            foreach ( $shortcodes as $shortcode )
            {
                add_filter( 'shortcode_atts_' . $shortcode, function ($out, $pairs, $atts, $shortcode)
                {
                    $current_settings = get_option( 'propertyhive_template_assistant', array() );

                    if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
                    {
                        foreach ( $current_settings['custom_fields'] as $custom_field )
                        {
                            if ( strpos($custom_field['meta_box'], 'property') !== FALSE )
                            {
                                $out[trim($custom_field['field_name'], '_')] = ( isset($atts[trim($custom_field['field_name'], '_')]) ? $atts[trim($custom_field['field_name'], '_')] : '' );
                            }
                        }
                    }

                    return $out;
                }, 10, 4 );

                add_filter( 'propertyhive_shortcode_' . $shortcode . '_query', function ($args, $atts)
                {
                    $current_settings = get_option( 'propertyhive_template_assistant', array() );

                    if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
                    {
                        foreach ( $current_settings['custom_fields'] as $custom_field )
                        {
                            if ( strpos($custom_field['meta_box'], 'property') !== FALSE )
                            {
                                if (
                                    isset($atts[trim($custom_field['field_name'], '_')]) && 
                                    $atts[trim($custom_field['field_name'], '_')] != ''
                                )
                                {
                                    if ( !isset($args['meta_query']) )
                                    {
                                        $args['meta_query'] = array();
                                    }

                                    // Format meta query as "= value" or "LIKE value"
                                    $value = $atts[trim($custom_field['field_name'], '_')];
                                    $compare = $custom_field['field_type'] == 'multiselect' ? 'LIKE' : '=';

                                    // A comma-delimited list of values has been specified
                                    if ( strpos($value, ',') !== false )
                                    {
                                        if ( $custom_field['field_type'] == 'multiselect' )
                                        {
                                            // Format meta query as "REGEXP value1|value2"
                                            $value = '"' . str_replace(',', '"|"', $value) . '"';
                                            $compare = 'REGEXP';
                                        }
                                        else
                                        {
                                            // Format meta query as "IN array(value1, value2)"
                                            $value = explode(',', $value);
                                            $compare = 'IN';
                                        }
                                    }

                                    $args['meta_query'][] = array(
                                        'key' => $custom_field['field_name'],
                                        'value' => $value,
                                        'compare' => $compare,
                                    );
                                }
                            }
                        }
                    }
                    return $args;
                }, 99, 2 );
            }
		}
	}

	public function display_custom_fields_on_website()
    {
        global $property;

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        $custom_fields = ( (isset($current_settings['custom_fields'])) ? $current_settings['custom_fields'] : array() );

        foreach ( $custom_fields as $custom_field )
        {
            if ( isset($custom_field['display_on_website']) && $custom_field['display_on_website'] == '1' && substr($custom_field['meta_box'], 0, 9) == 'property_' )
            {
                $label = '<span class="' . esc_attr(trim($custom_field['field_name'], '_')) . '_label">' . esc_html($custom_field['field_label']) . ': </span>';

                if ( $custom_field['field_type'] == 'multiselect' )
                {
                    $values = get_post_meta( $property->id, $custom_field['field_name'], TRUE );

                    if ( !empty($values) )
                    {
                        echo '<li class="' . esc_attr(trim($custom_field['field_name'], '_')) . '">' . $label;
                        echo esc_html(is_array($values) ? implode(", ", $values) : $values);
                        echo '</li>';
                    }
                }
                elseif ( $custom_field['field_type'] == 'date' )
                {
                    if ( $property->{$custom_field['field_name']} != '' )
                    {
                        ?>
                        <li class="<?php echo esc_attr(trim($custom_field['field_name'], '_')); ?>">
                            <?php echo $label . date(get_option( 'date_format' ), strtotime($property->{$custom_field['field_name']})); ?>
                        </li>
                        <?php
                    }
                }
                elseif ( $custom_field['field_type'] == 'image' )
                {
                    if ( $property->{$custom_field['field_name']} != '' )
                    {
                        ?>
                        <li class="<?php echo esc_attr(trim($custom_field['field_name'], '_')); ?>">
                            <?php echo $label . wp_get_attachment_image($property->{$custom_field['field_name']}); ?>
                        </li>
                        <?php
                        }
                }
                elseif ( $custom_field['field_type'] == 'file' )
                {
                    if ( $property->{$custom_field['field_name']} != '' )
                    {
                        ?>
                        <li class="<?php echo esc_attr(trim($custom_field['field_name'], '_')); ?>">
                            <?php echo $label . '<a href="' . esc_url(wp_get_attachment_url($property->{$custom_field['field_name']})) . '" rel="noopener noreferrer" target="_blank">' . esc_html(__( 'View', 'propertyhive' )) . '</a>'; ?>
                        </li>
                        <?php
                    }
                }
                else
                {
                    if ( $property->{$custom_field['field_name']} != '' )
                    {
                        $value = trim( $property->{$custom_field['field_name']} );

                        // If the custom field value is an email address or a URL, automatically make it a link
                        if ( apply_filters( 'propertyhive_auto_hyperlink_custom_fields', true ) )
                        {
                            if ( filter_var($value, FILTER_VALIDATE_URL) )
                            {
                                $value = '<a href="' . esc_url($value) . '" rel="noopener noreferrer" target="_blank">' . esc_html($value) . '</a>';
                            }
                            elseif ( filter_var($value, FILTER_VALIDATE_EMAIL) )
                            {
                                $value = '<a href="mailto:' . antispambot( sanitize_email( $value ) ) . '">' . esc_html($value) . '</a>';
                            }
                        }
                        ?>
                        <li class="<?php echo esc_attr(trim($custom_field['field_name'], '_')); ?>">
                            <?php echo $label . $value; ?>
                        </li>
                        <?php
                    }
                }
            }
        }
    }

    public function display_custom_fields_on_user_details( $form_controls )
    {
        if ( is_user_logged_in() )
        {
            $current_user = wp_get_current_user();

            if ( $current_user instanceof WP_User )
            {
                $contact = new PH_Contact( '', $current_user->ID );
            }
        }

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        $custom_fields = ( (isset($current_settings['custom_fields'])) ? $current_settings['custom_fields'] : array() );

        foreach ( $custom_fields as $custom_field )
        {
            if ( isset($custom_field['display_on_user_details']) && $custom_field['display_on_user_details'] == '1' && substr($custom_field['meta_box'], 0, 8) == 'contact_' )
            {
                $form_controls[$custom_field['field_name']] = array(
                    'type' => $custom_field['field_type'],
                    'label' => $custom_field['field_label'],
                );

                if ( is_user_logged_in() && $current_user instanceof WP_User )
                {
                    $form_controls[$custom_field['field_name']]['value'] = $contact->{$custom_field['field_name']};
                }
                
                switch ( $custom_field['field_type'] )
                {
                    case 'select':
                    case 'multiselect':
                    {
                        $options = array('' => '');
                        if ( isset($custom_field['dropdown_options']) && is_array($custom_field['dropdown_options']) && !empty($custom_field['dropdown_options']) )
                        {
                            foreach ( $custom_field['dropdown_options'] as $dropdown_option )
                            {
                                $options[$dropdown_option] = $dropdown_option;
                            }
                        }
                        $form_controls[$custom_field['field_name']]['options'] = $options;
                        break;
                    }
                }
            }
        }
        return $form_controls;
    }

    public function save_custom_fields_on_user_details( $contact_post_id, $user_id )
    {
        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        $custom_fields = ( (isset($current_settings['custom_fields'])) ? $current_settings['custom_fields'] : array() );

        foreach ( $custom_fields as $custom_field )
        {
            if ( isset($custom_field['display_on_user_details']) && $custom_field['display_on_user_details'] == '1' && substr($custom_field['meta_box'], 0, 8) == 'contact_' )
            {
                update_post_meta( $contact_post_id, $custom_field['field_name'], (isset($_POST[$custom_field['field_name']]) ? ph_clean($_POST[$custom_field['field_name']]) : '') );
            }
        }
    }

    public function custom_fields_in_meta_query( $meta_query )
    {
        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( 
                    $custom_field['meta_box'] == 'property_residential_sales_details' 
                    ||
                    $custom_field['meta_box'] == 'property_residential_lettings_details' 
                    ||
                    $custom_field['meta_box'] == 'property_commercial_details' 
                )
                {
                    // this is a department specific field. Make sure department in question in being searched
                    $meta_box_department = str_replace("property_", "", $custom_field['meta_box']);
                    $meta_box_department = str_replace("_details", "", $meta_box_department);
                    $meta_box_department = str_replace("_", "-", $meta_box_department);

                    if ( 
                        isset( $_REQUEST['department'] ) && 
                        ( $_REQUEST['department'] == $meta_box_department || ph_get_custom_department_based_on($_REQUEST['department']) == $meta_box_department )
                    )
                    {

                    }
                    else
                    {
                        continue;
                    }
                }

                if ( $custom_field['field_type'] == 'checkbox' )
                {
                    if ( $custom_field['exact_match'] == '' )
                    {
                        // not exact match (i.e. pets allowed)
                        if ( isset($_REQUEST[$custom_field['field_name']]) && ph_clean( $_REQUEST[$custom_field['field_name']] ) == 'yes' )
                        {
                            $meta_query[] = array(
                                'key'     => $custom_field['field_name'],
                                'value'   => ph_clean( $_REQUEST[$custom_field['field_name']] ),
                            );
                        }
                    }
                    else
                    {
                        // should match exactly only (i.e. something only)
                        if ( isset($_REQUEST[$custom_field['field_name']]) && ph_clean( $_REQUEST[$custom_field['field_name']] ) == 'yes' )
                        {
                            $meta_query[] = array(
                                'key' => $custom_field['field_name'],
                                'value' => 'yes',
                            );
                        }
                        else
                        {
                            $meta_query[] = array(
                                'relation' => 'OR',
                                array(
                                    'key' => $custom_field['field_name'],
                                    'value' => '',
                                ),
                                array(
                                    'key' => $custom_field['field_name'],
                                    'compare' => 'NOT EXISTS',
                                )
                            );

                        }
                    }
                }
                else
                {
                    if ( 
                        isset( $_REQUEST[$custom_field['field_name']] ) && $_REQUEST[$custom_field['field_name']] != '' 
                    )
                    {
                        if ( 
                            ( $custom_field['field_type'] == 'select' || $custom_field['field_type'] == 'multiselect' ) &&
                            is_array($_REQUEST[$custom_field['field_name']])
                        )
                        {
                            $sub_meta_query = array('relation' => 'OR');
                            foreach ( $_REQUEST[$custom_field['field_name']] as $value )
                            {
                                $sub_meta_query[] = array(
                                    'key'     => $custom_field['field_name'],
                                    'value'   => ph_clean( $value ),
                                    'compare' => '=',
                                );
                                $sub_meta_query[] = array(
                                    'key'     => $custom_field['field_name'],
                                    'value'   => '"' . ph_clean( $value ) . '"',
                                    'compare' => 'LIKE',
                                );
                            }
                            $meta_query[] = $sub_meta_query;
                        }
                        elseif ( $custom_field['field_type'] == 'select' )
                        {
                            $meta_query[] = array(
                                'key'     => $custom_field['field_name'],
                                'value'   => ph_clean( $_REQUEST[$custom_field['field_name']] ),
                                'compare' => '=',
                            );
                        }
                        else
                        {
                            $meta_query[] = array(
                                'key'     => $custom_field['field_name'],
                                'value'   => ph_clean( $_REQUEST[$custom_field['field_name']] ),
                                'compare' => 'LIKE',
                            );
                        }
                    }
                }
            }
        }

        return $meta_query;
    }

    public function add_office_additional_field_table_header_column()
    {
        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( 
                	isset($custom_field['admin_list']) && 
                	$custom_field['admin_list'] == '1' && 
                	substr($custom_field['meta_box'], 0, 6) == 'office' 
                )
                {
                    echo '<th>' . esc_html($custom_field['field_label']) . '</th>';
                }
            }
        }
    }

    public function add_office_additional_field_table_row_column( $office_id )
    {
        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( isset($custom_field['admin_list']) && $custom_field['admin_list'] == '1' && substr($custom_field['meta_box'], 0, 6) == 'office' )
                {
                    echo '<td>';
                    switch ( $custom_field['field_type'] )
                    {
                        case "image":
                        {   
                            $image_id = get_post_meta( $office_id, $custom_field['field_name'], TRUE );
                            if ( $image_id != '' )
                            {
                                $image = wp_get_attachment_image_src( $image_id, 'thumbnail' );
                                if ($image !== FALSE)
                                {
                                    echo '<img src="' . esc_url($image[0]) . '" width="150" alt="">';
                                }
                                else
                                {
                                    echo 'Image doesn\'t exist';
                                }
                            }
                            break;
                        }
                        default:
                        {
                            echo esc_html(get_post_meta( $office_id, $custom_field['field_name'], TRUE ));
                        }
                    }
                    echo '</td>';
                }
            }
        }
    }

    public function add_applicant_requirements_fields( $contact_post_id, $applicant_profile_id )
    {
        $applicant_profile = get_post_meta( $contact_post_id, '_applicant_profile_' . $applicant_profile_id, TRUE );

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( 
                    isset($custom_field['display_on_applicant_requirements']) && 
                    $custom_field['display_on_applicant_requirements'] == '1' && 
                    substr($custom_field['meta_box'], 0, 9) == 'property_' &&
                    !in_array( $custom_field['meta_box'], array('property_residential_details', 'property_residential_sales_details', 'property_residential_lettings_details', 'property_commercial_details') )
                )
                {
                    $this->add_applicant_requirements_field( $custom_field, $applicant_profile, $applicant_profile_id );
                }
            }
        }
    }

    private function add_applicant_requirements_field( $custom_field, $applicant_profile, $applicant_profile_id )
    {
        switch ( $custom_field['field_type'] )
        {
            case "select":
            {
                $options = array('' => '');
                foreach ($custom_field['dropdown_options'] as $dropdown_option)
                {
                    $options[$dropdown_option] = ph_clean($dropdown_option);
                }

                propertyhive_wp_select( array( 
                    'id' => '_applicant' . $custom_field['field_name'] . '_' . $applicant_profile_id, 
                    'label' => $custom_field['field_label'], 
                    'desc_tip' => false, 
                    'custom_attributes' => array(
                        'style' => 'width:100%; max-width:150px;'
                    ),
                    'value' => ( ( isset($applicant_profile[$custom_field['field_name']]) ) ? $applicant_profile[$custom_field['field_name']] : '' ),
                    'options' => $options,
                ) );

                break;
            }
            case "multiselect":
            {
                $options = array('' => '');
                foreach ($custom_field['dropdown_options'] as $dropdown_option)
                {
                    $options[$dropdown_option] = ph_clean($dropdown_option);
                }
?>
                <p class="form-field">
                    <label for="_applicant<?php echo esc_attr($custom_field['field_name']); ?>_<?php echo $applicant_profile_id; ?>"><?php echo esc_html($custom_field['field_label']); ?></label>
                    <select id="_applicant<?php echo esc_attr($custom_field['field_name']); ?>_<?php echo $applicant_profile_id; ?>" name="_applicant<?php echo esc_attr($custom_field['field_name']); ?>_<?php echo $applicant_profile_id; ?>[]" multiple="multiple" data-placeholder="Start typing to add <?php echo esc_attr($custom_field['field_label']); ?>..." class="multiselect attribute_values">
                        <?php
                            foreach ( $options as $option )
                            {
                                echo '<option value="' . esc_attr( $option ) . '"';
                                if ( 
                                    isset($applicant_profile[$custom_field['field_name']]) 
                                )
                                {
                                    if ( !is_array($applicant_profile[$custom_field['field_name']]) && $applicant_profile[$custom_field['field_name']] != '' )
                                    {
                                        $applicant_profile[$custom_field['field_name']] = array($applicant_profile[$custom_field['field_name']]);
                                    }

                                    if ( in_array( $option, $applicant_profile[$custom_field['field_name']] ) )
                                    {
                                        echo ' selected';
                                    }
                                }
                                echo '>' . esc_html( $option ) . '</option>';
                            }
                        ?>
                    </select>
                </p>
<?php
                break;
            }
            case "checkbox":
            {
                propertyhive_wp_checkbox( array( 
                    'id' => '_applicant' . $custom_field['field_name'] . '_' . $applicant_profile_id, 
                    'label' => $custom_field['field_label'], 
                    'desc_tip' => false, 
                    'value' => ( ( isset($applicant_profile[$custom_field['field_name']]) && $applicant_profile[$custom_field['field_name']] == 'yes' ) ? 'yes' : '' ),
                ) );

                break;
            }
        }
    }

    public function add_applicant_requirements_residential_fields( $contact_post_id, $applicant_profile_id )
    {
        $applicant_profile = get_post_meta( $contact_post_id, '_applicant_profile_' . $applicant_profile_id, TRUE );

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( 
                    isset($custom_field['display_on_applicant_requirements']) && 
                    $custom_field['display_on_applicant_requirements'] == '1' && 
                    substr($custom_field['meta_box'], 0, 9) == 'property_' &&
                    in_array( $custom_field['meta_box'], array('property_residential_details') )
                )
                {
                    $this->add_applicant_requirements_field( $custom_field, $applicant_profile, $applicant_profile_id );
                }
            }
        }
    }

    public function add_applicant_requirements_residential_sales_fields( $contact_post_id, $applicant_profile_id )
    {
        $applicant_profile = get_post_meta( $contact_post_id, '_applicant_profile_' . $applicant_profile_id, TRUE );

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( 
                    isset($custom_field['display_on_applicant_requirements']) && 
                    $custom_field['display_on_applicant_requirements'] == '1' && 
                    substr($custom_field['meta_box'], 0, 9) == 'property_' &&
                    in_array( $custom_field['meta_box'], array('property_residential_sales_details') )
                )
                {
                    $this->add_applicant_requirements_field( $custom_field, $applicant_profile, $applicant_profile_id );
                }
            }
        }
    }

    public function add_applicant_requirements_residential_lettings_fields( $contact_post_id, $applicant_profile_id )
    {
        $applicant_profile = get_post_meta( $contact_post_id, '_applicant_profile_' . $applicant_profile_id, TRUE );

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( 
                    isset($custom_field['display_on_applicant_requirements']) && 
                    $custom_field['display_on_applicant_requirements'] == '1' && 
                    substr($custom_field['meta_box'], 0, 9) == 'property_' &&
                    in_array( $custom_field['meta_box'], array('property_residential_lettings_details') )
                )
                {
                    $this->add_applicant_requirements_field( $custom_field, $applicant_profile, $applicant_profile_id );
                }
            }
        }
    }

    public function add_applicant_requirements_commercial_fields( $contact_post_id, $applicant_profile_id )
    {
        $applicant_profile = get_post_meta( $contact_post_id, '_applicant_profile_' . $applicant_profile_id, TRUE );

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( 
                    isset($custom_field['display_on_applicant_requirements']) && 
                    $custom_field['display_on_applicant_requirements'] == '1' && 
                    substr($custom_field['meta_box'], 0, 9) == 'property_' &&
                    in_array( $custom_field['meta_box'], array('property_commercial_details') )
                )
                {
                    $this->add_applicant_requirements_field( $custom_field, $applicant_profile, $applicant_profile_id );
                }
            }
        }
    }

    public function save_applicant_requirements_fields( $contact_post_id, $applicant_profile_id )
    {
        $applicant_profile = get_post_meta( $contact_post_id, '_applicant_profile_' . $applicant_profile_id, TRUE );

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( isset($custom_field['display_on_applicant_requirements']) && $custom_field['display_on_applicant_requirements'] == '1' && substr($custom_field['meta_box'], 0, 9) == 'property_' )
                {
                    switch ( $custom_field['field_type'] )
                    {
                        case "select":
                        case "multiselect":
                        {
                            if ( isset($_POST['_applicant' . $custom_field['field_name'] . '_' . $applicant_profile_id]) )
                            {
                                $applicant_profile[$custom_field['field_name']] = ph_clean($_POST['_applicant' . $custom_field['field_name'] . '_' . $applicant_profile_id]);
                            }
                            break;
                        }
                        case "checkbox":
                        {
                            if ( isset($_POST['_applicant' . $custom_field['field_name'] . '_' . $applicant_profile_id]) )
                            {
                                $applicant_profile[$custom_field['field_name']] = ph_clean($_POST['_applicant' . $custom_field['field_name'] . '_' . $applicant_profile_id]);
                            }
                            else
                            {
                                $applicant_profile[$custom_field['field_name']] = '';
                            }
                            break;
                        }
                    }
                }
            }
        }

        update_post_meta( $contact_post_id, '_applicant_profile_' . $applicant_profile_id, $applicant_profile );
    }

    public function applicant_requirements_display( $requirements, $contact_post_id, $applicant_profile )
    {
        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( isset($custom_field['display_on_applicant_requirements']) && $custom_field['display_on_applicant_requirements'] == '1' && substr($custom_field['meta_box'], 0, 9) == 'property_' )
                {
                    if ( isset($applicant_profile[$custom_field['field_name']]) )
                    {
                        switch ( $custom_field['field_type'] )
                        {
                            case "select":
                            case "checkbox":
                            {
                                if ( $applicant_profile[$custom_field['field_name']] != '' )
                                {
                                    $requirements[] = array(
                                        'label' => $custom_field['field_label'],
                                        'value' => ph_clean($applicant_profile[$custom_field['field_name']]),
                                    );
                                }
                                break;
                            }
                            case "multiselect":
                            {
                                if ( !is_array($applicant_profile[$custom_field['field_name']]) && $applicant_profile[$custom_field['field_name']] != '' )
                                {
                                    $applicant_profile[$custom_field['field_name']] = array($applicant_profile[$custom_field['field_name']]);
                                }

                                if ( !empty($applicant_profile[$custom_field['field_name']]) )
                                {
                                    $sliced_terms = array_slice( ph_clean($applicant_profile[$custom_field['field_name']]), 0, 2 );
                                    $requirements[] = array(
                                        'label' => $custom_field['field_label'],
                                        'value' => implode(", ", $sliced_terms) . ( (count($applicant_profile[$custom_field['field_name']]) > 2) ? '<span title="' . esc_attr( implode(", ", $applicant_profile[$custom_field['field_name']]) ) .'"> + ' . (count($applicant_profile[$custom_field['field_name']]) - 2) . ' more</span>' : '' )
                                    );
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $requirements;
    }

    public function matching_properties_args( $args, $contact_post_id, $applicant_profile )
    {
        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( isset($custom_field['display_on_applicant_requirements']) && $custom_field['display_on_applicant_requirements'] == '1' && substr($custom_field['meta_box'], 0, 9) == 'property_' )
                {
                    if ( isset($applicant_profile[$custom_field['field_name']]) )
                    {
                        // ensure if field is specific to department it's taken into account, else ignored
                        if ( 
                            $custom_field['meta_box'] == 'property_residential_sales_details' 
                            ||
                            $custom_field['meta_box'] == 'property_residential_lettings_details' 
                            ||
                            $custom_field['meta_box'] == 'property_commercial_details' 
                        )
                        {
                            $meta_box_department = str_replace("property_", "", $custom_field['meta_box']);
                            $meta_box_department = str_replace("_details", "", $meta_box_department);
                            $meta_box_department = str_replace("_", "-", $meta_box_department);

                            if ( 
                                isset( $applicant_profile['department'] ) && 
                                ( $applicant_profile['department'] == $meta_box_department || ph_get_custom_department_based_on($applicant_profile['department']) == $meta_box_department )
                            )
                            {

                            }
                            else
                            {
                                continue;
                            }
                        }

                        switch ( $custom_field['field_type'] )
                        {
                            case "select":
                            {
                                if ( $applicant_profile[$custom_field['field_name']] != '' )
                                {
                                    $args['meta_query'][] = array(
                                        'key' => $custom_field['field_name'],
                                        'value' => $applicant_profile[$custom_field['field_name']],
                                    );
                                }
                                break;
                            }
                            case "multiselect":
                            {
                                if ( !is_array($applicant_profile[$custom_field['field_name']]) && $applicant_profile[$custom_field['field_name']] != '' )
                                {
                                    $applicant_profile[$custom_field['field_name']] = array($applicant_profile[$custom_field['field_name']]);
                                }

                                if ( !empty($applicant_profile[$custom_field['field_name']]) )
                                {
                                    $sub_meta_query = array(
                                        'relation' => 'OR'
                                    );

                                    foreach ( $applicant_profile[$custom_field['field_name']] as $option )
                                    {
                                        $sub_meta_query[] = array(
                                            'key' => $custom_field['field_name'],
                                            'value' => $option,
                                            'compare' => 'LIKE',
                                        );
                                    }
                                    
                                    $args['meta_query'][] = $sub_meta_query;
                                }
                                break;
                            }
                            case "checkbox":
                            {
                                if ( $custom_field['exact_match'] == '' )
                                {
                                    if ( $applicant_profile[$custom_field['field_name']] != '' )
                                    {
                                        $args['meta_query'][] = array(
                                            'key' => $custom_field['field_name'],
                                            'value' => $applicant_profile[$custom_field['field_name']],
                                        );
                                    }
                                }
                                else
                                {
                                    // should match exactly only
                                    if ( $applicant_profile[$custom_field['field_name']] == 'yes' )
                                    {
                                        $args['meta_query'][] = array(
                                            'key' => $custom_field['field_name'],
                                            'value' => 'yes',
                                        );
                                    }
                                    else
                                    {
                                        $args['meta_query'][] = array(
                                            'relation' => 'OR',
                                            array(
                                                'key' => $custom_field['field_name'],
                                                'value' => '',
                                            ),
                                            array(
                                                'key' => $custom_field['field_name'],
                                                'compare' => 'NOT EXISTS',
                                            )
                                        );

                                    }
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $args;
    }

    public function matching_applicants_check( $check, $property, $contact_post_id, $applicant_profile )
    {
        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( isset($custom_field['display_on_applicant_requirements']) && $custom_field['display_on_applicant_requirements'] == '1' && substr($custom_field['meta_box'], 0, 9) == 'property_' )
                {
                    // ensure if field is specific to department it's taken into account, else ignored
                    if ( 
                        $custom_field['meta_box'] == 'property_residential_sales_details' 
                        ||
                        $custom_field['meta_box'] == 'property_residential_lettings_details' 
                        ||
                        $custom_field['meta_box'] == 'property_commercial_details' 
                    )
                    {
                        $meta_box_department = str_replace("property_", "", $custom_field['meta_box']);
                        $meta_box_department = str_replace("_details", "", $meta_box_department);
                        $meta_box_department = str_replace("_", "-", $meta_box_department);

                        if ( 
                            isset( $applicant_profile['department'] ) && 
                            ( $applicant_profile['department'] == $meta_box_department || ph_get_custom_department_based_on($applicant_profile['department']) == $meta_box_department )
                        )
                        {

                        }
                        else
                        {
                            continue;
                        }
                    }

                    if ( isset($applicant_profile[$custom_field['field_name']]) )
                    {
                        switch ( $custom_field['field_type'] )
                        {
                            case "select":
                            {
                                if ( 
                                    $applicant_profile[$custom_field['field_name']] == '' ||
                                    $property->{$custom_field['field_name']} == $applicant_profile[$custom_field['field_name']]
                                )
                                {

                                }
                                else
                                {
                                    return false;
                                }
                                break;
                            }
                            case "multiselect":
                            {
                                if ( !is_array($applicant_profile[$custom_field['field_name']]) && $applicant_profile[$custom_field['field_name']] != '' )
                                {
                                    $applicant_profile[$custom_field['field_name']] = array($applicant_profile[$custom_field['field_name']]);
                                }

                                if ( empty($applicant_profile[$custom_field['field_name']]) )
                                {

                                }
                                else
                                {
                                    $property_values = !is_array($property->{$custom_field['field_name']}) ? array($property->{$custom_field['field_name']}) : $property->{$custom_field['field_name']};
                                    if ( empty($property_values) )
                                    {
                                        return false;
                                    }

                                    $applicant_values = $applicant_profile[$custom_field['field_name']];

                                    $value_exists = false;

                                    foreach ( $property_values as $property_value )
                                    {
                                        foreach ( $applicant_values as $applicant_value )
                                        {
                                            if ( $property_value == $applicant_value )
                                            {
                                                $value_exists = true;
                                            }
                                        }
                                    }

                                    if ( !$value_exists )
                                    {
                                        return false;
                                    }
                                }

                                break;
                            }
                            case "checkbox":
                            {
                                if ( $custom_field['exact_match'] == '' )
                                {
                                    // not exact match (i.e. pets allowed)
                                    if ( 
                                        $applicant_profile[$custom_field['field_name']] == '' ||
                                        $property->{$custom_field['field_name']} == $applicant_profile[$custom_field['field_name']]
                                    )
                                    {

                                    }
                                    else
                                    {
                                        return false;
                                    }
                                }
                                else
                                {
                                    // exact match
                                    if (
                                        $property->{$custom_field['field_name']} == $applicant_profile[$custom_field['field_name']]
                                    )
                                    {

                                    }
                                    else
                                    {
                                        return false;
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $check;
    }

    public function applicant_requirements_form_fields( $form_controls, $applicant_profile = false )
    {
        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( isset($custom_field['display_on_applicant_requirements']) && $custom_field['display_on_applicant_requirements'] == '1' && substr($custom_field['meta_box'], 0, 9) == 'property_' )
                {
                    switch ( $custom_field['field_type'] )
                    {
                        case "select":
                        case "multiselect":
                        {
                            $options = array('' => '');
                            foreach ($custom_field['dropdown_options'] as $dropdown_option)
                            {
                                $options[$dropdown_option] = ph_clean($dropdown_option);
                            }

                            $value = isset($applicant_profile[$custom_field['field_name']]) ? $applicant_profile[$custom_field['field_name']] : '';
                            /*if ( is_array($value) && !empty($value) )
                            {
                                $value = $value[0];
                            }*/

                            $form_controls[$custom_field['field_name']] = array(
                                'type' => 'select',
                                'label' => $custom_field['field_label'],
                                'required' => false,
                                'show_label' => true,
                                'value' => $value,
                                'options' => $options,
                                'multiselect' => $custom_field['field_type'] == 'multiselect' ? true : false
                            );

                            break;
                        }
                        case "checkbox":
                        {
                            $value = isset($applicant_profile[$custom_field['field_name']]) ? $applicant_profile[$custom_field['field_name']] : '';

                            $form_controls[$custom_field['field_name']] = array(
                                'type' => 'checkbox',
                                'label' => $custom_field['field_label'],
                                'required' => false,
                                'show_label' => true,
                                'value' => $value,
                            );

                            break;
                        }
                    }
                }
            }
        }

        return $form_controls;
    }

    public function applicant_registered( $contact_post_id, $user_id )
    {
        $applicant_profile = get_post_meta( $contact_post_id, '_applicant_profile_' . ( isset($_POST['profile_id']) && $_POST['profile_id'] != '' ? (int)$_POST['profile_id'] : '0' ), TRUE );

        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( isset($custom_field['display_on_applicant_requirements']) && $custom_field['display_on_applicant_requirements'] == '1' && substr($custom_field['meta_box'], 0, 9) == 'property_' )
                {
                    switch ( $custom_field['field_type'] )
                    {
                        case "select":
                        {
                            $applicant_profile[$custom_field['field_name']] = isset($_POST[$custom_field['field_name']]) ? ph_clean($_POST[$custom_field['field_name']]) : '';
                            break;
                        }
                        case "multiselect":
                        {
                            if ( isset($_POST[$custom_field['field_name']]) )
                            {
                                if ( !is_array($_POST[$custom_field['field_name']]) )
                                {
                                    $_POST[$custom_field['field_name']] = array($_POST[$custom_field['field_name']]);
                                }
                            }
                            $applicant_profile[$custom_field['field_name']] = isset($_POST[$custom_field['field_name']]) ? ph_clean($_POST[$custom_field['field_name']]) : array();
                            break;
                        }
                        case "checkbox":
                        {
                            $applicant_profile[$custom_field['field_name']] = isset($_POST[$custom_field['field_name']]) ? ph_clean($_POST[$custom_field['field_name']]) : '';
                            break;
                        }
                    }
                }
            }
        }

        update_post_meta( $contact_post_id, '_applicant_profile_' . ( isset($_POST['profile_id']) && $_POST['profile_id'] != '' ? (int)$_POST['profile_id'] : '0' ), $applicant_profile );
    }

    public function applicant_list_check( $check, $contact_post_id, $applicant_profile )
    {
        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( isset($custom_field['display_on_applicant_requirements']) && $custom_field['display_on_applicant_requirements'] == '1' && substr($custom_field['meta_box'], 0, 9) == 'property_' )
                {
                    // ensure if field is specific to department it's taken into account, else ignored
                    if ( 
                        $custom_field['meta_box'] == 'property_residential_sales_details' 
                        ||
                        $custom_field['meta_box'] == 'property_residential_lettings_details' 
                        ||
                        $custom_field['meta_box'] == 'property_commercial_details' 
                    )
                    {
                        $meta_box_department = str_replace("property_", "", $custom_field['meta_box']);
                        $meta_box_department = str_replace("_details", "", $meta_box_department);
                        $meta_box_department = str_replace("_", "-", $meta_box_department);

                        if ( 
                            isset( $_POST['department'] ) && 
                            ( $_POST['department'] == $meta_box_department || ph_get_custom_department_based_on($_POST['department']) == $meta_box_department )
                        )
                        {

                        }
                        else
                        {
                            continue;
                        }
                    }

                    
                    if ( isset($applicant_profile[$custom_field['field_name']]) )
                    {
                        switch ( $custom_field['field_type'] )
                        {
                            case "select":
                            {
                                if ( !empty($_POST[$custom_field['field_name']]) )
                                {
                                    if ( 
                                        $applicant_profile[$custom_field['field_name']] == '' ||
                                        $_POST[$custom_field['field_name']] == $applicant_profile[$custom_field['field_name']]
                                    )
                                    {

                                    }
                                    else
                                    {
                                        return false;
                                    }
                                }
                                break;
                            }
                            case "multiselect":
                            {
                                if ( !empty($_POST[$custom_field['field_name']]) )
                                {
                                    if ( !is_array($applicant_profile[$custom_field['field_name']]) && $applicant_profile[$custom_field['field_name']] != '' )
                                    {
                                        $applicant_profile[$custom_field['field_name']] = array($applicant_profile[$custom_field['field_name']]);
                                    }

                                    if ( empty($applicant_profile[$custom_field['field_name']]) )
                                    {

                                    }
                                    else
                                    {
                                        $property_values = $_POST[$custom_field['field_name']];
                                        if ( empty($property_values) )
                                        {
                                            return false;
                                        }

                                        $applicant_values = $applicant_profile[$custom_field['field_name']];

                                        $value_exists = false;

                                        foreach ( $property_values as $property_value )
                                        {
                                            foreach ( $applicant_values as $applicant_value )
                                            {
                                                if ( $property_value == $applicant_value )
                                                {
                                                    $value_exists = true;
                                                }
                                            }
                                        }

                                        if ( !$value_exists )
                                        {
                                            return false;
                                        }
                                    }
                                }

                                break;
                            }
                            case "checkbox":
                            {
                                if ( $custom_field['exact_match'] == '' )
                                {
                                    // not exact match (i.e. pets allowed)
                                    if ( 
                                        $applicant_profile[$custom_field['field_name']] == '' ||
                                        $_POST[$custom_field['field_name']] == $applicant_profile[$custom_field['field_name']]
                                    )
                                    {

                                    }
                                    else
                                    {
                                        return false;
                                    }
                                }
                                else
                                {
                                    // exact match
                                    if ( isset($_POST[$custom_field['field_name']]) )
                                    {
                                        if (
                                            $_POST[$custom_field['field_name']] == $applicant_profile[$custom_field['field_name']]
                                        )
                                        {

                                        }
                                        else
                                        {
                                            return false;
                                        }
                                    }
                                    else
                                    {
                                        if (
                                            '' == $applicant_profile[$custom_field['field_name']]
                                        )
                                        {

                                        }
                                        else
                                        {
                                            return false;
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $check;
    }

    public function add_custom_fields_to_room_breakdown( $room_data, $post_id, $room )
    {
        $current_settings = get_option( 'propertyhive_template_assistant', array() );

        if ( isset($current_settings['custom_fields']) && !empty($current_settings['custom_fields']) )
        {
            foreach ( $current_settings['custom_fields'] as $custom_field )
            {
                if ( isset($custom_field['display_on_website']) && $custom_field['display_on_website'] == '1' && $custom_field['meta_box'] == 'property_rooms_breakdown' )
                {
                    if ( $room->{$custom_field['field_name']} != '' )
                    {
                        $room_data[] = array(
                            'class' => sanitize_title($custom_field['field_name']),
                            'label' => __( $custom_field['field_label'], 'propertyhive' ),
                            'value' => $room->{$custom_field['field_name']}
                        );
                    }
                }
            }
        }

        return $room_data;
    }
}

new PH_Additional_Fields();