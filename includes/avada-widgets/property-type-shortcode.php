<?php

add_shortcode( 'avada_property_type', function( $atts ) {
    $atts = shortcode_atts( array(
        'content_align'    => 'left',
        'fusion_font_family_type_font' => '',
        'fusion_font_variant_type_font' => '',
        'font_size'  => '',
        'letter_spacing'  => '',
        'text_transform'  => '',
        'line_height'  => '',
        'text_color'       => '',
        'icon'       => '',
        'before'       => '',
        'after'       => '',
    ), $atts );

    if ( get_post_type( get_the_ID() ) != 'property' )
    {
    	return '';
    }

    fusion_element_rendering_elements( true );

    global $property;
    
    if ( empty($property) )
    {
        $property = new PH_Property(get_the_ID());
    }

    if ( $property->property_type == '' )
    {
        return '';
    }

    $style = Fusion_Builder_Element_Helper::get_font_styling( $atts, 'type_font' );

	if ( $atts['font_size'] ) {
		$style .= 'font-size:' . fusion_library()->sanitize->get_value_with_unit( $atts['font_size'] ) . ';';
	}

	if ( $atts['letter_spacing'] ) {
		$style .= 'letter-spacing:' . fusion_library()->sanitize->get_value_with_unit( $atts['letter_spacing'] ) . ';';
	}

	if ( ! empty( $atts['text_transform'] ) ) {
		$style .= 'text-transform:' . $atts['text_transform'] . ';';
	}

	if ( ! empty( $atts['content_align'] ) ) {
		$style .= 'text-align:' . $atts['content_align'] . ';';
	}

    ob_start();

    echo '<div ' . FusionBuilder::attributes( 'property-type-shortcode' ) . '>
    	<div style="' . $style . '">';

        if ( ! empty($atts['icon']) ) 
        {
            echo '<span class="' . esc_attr($atts['icon']) . '"></span> ';
        }

        if ( isset($atts['before']) && !empty($atts['before']) )
        {
            echo $atts['before'] . ' ';
        }
        echo esc_html($property->property_type);
        if ( isset($atts['after']) && !empty($atts['after']) )
        {
            echo ' ' . $atts['after'];
        }
    echo '
    	</div>
    </div>';

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});