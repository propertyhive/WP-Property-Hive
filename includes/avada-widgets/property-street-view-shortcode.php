<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


add_shortcode( 'avada_property_street_view', function( $atts ) {
    $atts = shortcode_atts( array(
        'height'    => '',
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

    $attributes = array();
    if ( isset($atts['height']) && $atts['height'] != '' )
    {
        $attributes['height'] = $atts['height'];
    }

    $attributes = apply_filters( 'propertyhive_avada_property_street_view_attributes', $attributes );

    get_property_street_view($attributes);

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});