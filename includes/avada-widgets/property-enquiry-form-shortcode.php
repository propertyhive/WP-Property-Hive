<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


add_shortcode( 'avada_property_enquiry_form', function( $atts ) {
    $atts = shortcode_atts( array(

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

    ob_start();

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- FusionBuilder::attributes() returns the complete HTML attribute fragment and escapes each attribute name and value.
    echo '<div ' . FusionBuilder::attributes( 'property-enquiry-form-shortcode' ) . '>';
        propertyhive_enquiry_form();
    echo '</div>';

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});