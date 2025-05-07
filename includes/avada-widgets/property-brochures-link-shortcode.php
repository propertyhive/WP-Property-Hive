<?php

add_shortcode( 'avada_property_brochures_link', function( $atts ) {
    $atts = shortcode_atts( array(
        'content_align'    => 'left',
        'fusion_font_family_brochures_link_font' => '',
        'fusion_font_variant_brochures_link_font' => '',
        'font_size'  => '',
        'letter_spacing'  => '',
        'text_transform'  => '',
        'line_height'  => '',
        'text_color'       => '',
        'label'       => '',
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

    $style = Fusion_Builder_Element_Helper::get_font_styling( $atts, 'bedrooms_font' );

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

    if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    {
        $brochure_urls = $property->brochure_urls;
        if ( !is_array($brochure_urls) ) { $brochure_urls = array(); }

        if ( !empty($brochure_urls) )
        {
            echo '<div ' . FusionBuilder::attributes( 'property-brochures-link-shortcode' ) . '>';
            foreach ( $brochure_urls as $brochure )
            {
                echo '<a href="' . esc_url($brochure['url']) . '" target="_blank" rel="nofollow" style="' . $style . '">' . esc_html($label) . '</a>';
            }
            echo '</div>';
        }
    }
    else
    {
        $brochure_attachment_ids = $property->get_brochure_attachment_ids();

        if ( !empty($brochure_attachment_ids) )
        {
            echo '<div ' . FusionBuilder::attributes( 'property-brochures-link-shortcode' ) . '>';
            foreach ( $brochure_attachment_ids as $attachment_id )
            {
                echo '<a href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" target="_blank" rel="nofollow" style="' . $style . '">' . esc_html($label) . '</a>';
            }
            echo '</div>';
        }
    }

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});