<?php

add_shortcode( 'avada_property_summary_description', function( $atts ) {
    $atts = shortcode_atts( array(
        'content_align'    => 'left',
        'fusion_font_family_summary_description_font' => '',
        'fusion_font_variant_summary_description_font' => '',
        'font_size'  => '',
        'letter_spacing'  => '',
        'text_transform'  => '',
        'line_height'  => '',
        'text_color'       => '',
        'show_title'    => '',
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
    
    $style = Fusion_Builder_Element_Helper::get_font_styling( $atts, 'summary_description_font' );

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

    if ( $atts['show_title'] != 'yes' )
    {
?>
<style type="text/css">
.summary-description h4 { display:none; }
</style>
<?php
    }

    echo '<div ' . FusionBuilder::attributes( 'property-summary-description-shortcode' ) . '>
    	<div style="' . $style . '">';
        propertyhive_template_single_summary();
    echo '
    	</div>
    </div>';

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});