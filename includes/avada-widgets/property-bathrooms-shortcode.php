<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


add_shortcode( 'avada_property_bathrooms', function( $atts ) {
    $atts = shortcode_atts( array(
        'content_align'    => 'left',
        'fusion_font_family_bathrooms_font' => '',
        'fusion_font_variant_bathrooms_font' => '',
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
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Shared frontend property global used by the Avada shortcode contract; changing $property would break the existing property context passed to these widgets.
        $property = new PH_Property(get_the_ID());
    }

    if ( $property->bathrooms == '' || $property->bathrooms == 0 )
    {
        return '';
    }

    $style = Fusion_Builder_Element_Helper::get_font_styling( $atts, 'bathrooms_font' );

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

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- FusionBuilder::attributes() returns the complete HTML attribute fragment and escapes each attribute name and value.
    echo '<div ' . FusionBuilder::attributes( 'property-bathrooms-shortcode' ) . '>
        <div style="' . esc_attr( safecss_filter_attr( $style ) ) . '">';

        if ( ! empty($atts['icon']) ) 
        {
            echo '<span class="' . esc_attr($atts['icon']) . '"></span> ';
        }

        if ( isset($atts['before']) && !empty($atts['before']) )
        {
            echo wp_kses_post( $atts['before'] ) . ' ';
        }
        echo esc_html($property->bathrooms);
        if ( isset($atts['after']) && !empty($atts['after']) )
        {
            echo ' ' . wp_kses_post( $atts['after'] );
        }
    echo '
    	</div>
    </div>';

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});