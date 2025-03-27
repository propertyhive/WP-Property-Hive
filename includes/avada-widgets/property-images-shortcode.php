<?php

add_shortcode( 'avada_property_images', function( $atts ) {
    $atts = shortcode_atts( array(
        'hide_thumbnails'    => '',
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

    /*$suffix               = '';
    $assets_path          = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';

    wp_enqueue_script( 'flexslider', $assets_path . 'js/flexslider/jquery.flexslider' . $suffix . '.js', array( 'jquery' ), '2.7.2', true );
    wp_enqueue_script( 'flexslider-init', $assets_path . 'js/flexslider/jquery.flexslider.init' . $suffix . '.js', array( 'jquery','flexslider' ), PH_VERSION, true );
    wp_enqueue_style( 'flexslider_css', $assets_path . 'css/flexslider.css', array(), '2.7.2' );
*/
    if ( 'yes' === $atts['hide_thumbnails'] ) 
    {
        remove_action( 'propertyhive_product_thumbnails', 'propertyhive_show_property_thumbnails', 20 );
    }

    //wp_enqueue_script( 'propertyhive_fancybox' );
    //wp_enqueue_style( 'propertyhive_fancybox_css' );
    
    propertyhive_show_property_images();

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});