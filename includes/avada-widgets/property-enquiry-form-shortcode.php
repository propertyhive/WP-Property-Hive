<?php

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
        $property = new PH_Property(get_the_ID());
    }

    ob_start();

    echo '<div ' . FusionBuilder::attributes( 'property-enquiry-form-shortcode' ) . '>';
        propertyhive_enquiry_form();
    echo '</div>';

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});