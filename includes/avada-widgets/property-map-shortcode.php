<?php

add_shortcode( 'avada_property_map', function( $atts ) {
    $atts = shortcode_atts( array(
        'height'    => '',
        'zoom' => '',
        'scrollwheel' => '',
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
    
    ob_start();

    $attributes = array();
    if ( isset($atts['height']) && $atts['height'] != '' )
    {
        $attributes['height'] = $atts['height'];
    }
    if ( isset($atts['zoom']) && isset($atts['zoom']['size']) && $atts['zoom']['size'] != '' )
    {
        $attributes['zoom'] = $atts['zoom']['size'];
    }
    if ( isset($atts['scrollwheel']) && $atts['scrollwheel'] != '' )
    {
        $attributes['scrollwheel'] = $atts['scrollwheel'];
    }

    $attributes = apply_filters( 'propertyhive_avada_property_map_attributes', $attributes );

    get_property_map($attributes);

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});