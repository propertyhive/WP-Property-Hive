<?php

add_shortcode( 'avada_property_floorplans', function( $atts ) {
    $atts = shortcode_atts( array(
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

    ob_start();

    if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
    {
        $floorplan_urls = $property->_floorplan_urls;
        if ( is_array($floorplan_urls) && !empty( $floorplan_urls ) )
        {
            echo '<div class="floorplans">';

            if ( $atts['show_title'] == 'yes' ) { echo '<h4>' . __( 'Floorplans', 'propertyhive' ) . '</h4>'; }

            foreach ($floorplan_urls as $floorplan)
            {
                echo '<a href="' . $floorplan['url'] . '" data-fancybox="floorplan" rel="nofollow"><img src="' . $floorplan['url'] . '" alt=""></a>';
            }

            echo '</div>';
        }
    }
    else
    {
        $floorplan_attachment_ids = $property->get_floorplan_attachment_ids();

        if ( !empty($floorplan_attachment_ids) )
        {
            echo '<div class="floorplans">';

            if ( $atts['show_title'] == 'yes' ) { echo '<h4>' . __( 'Floorplans', 'propertyhive' ) . '</h4>'; }

            foreach ( $floorplan_attachment_ids as $attachment_id )
            {
                if ( wp_attachment_is_image($attachment_id) )
                {
                    echo '<a href="' . wp_get_attachment_url($attachment_id) . '" data-fancybox="floorplan" rel="nofollow"><img src="' . wp_get_attachment_url($attachment_id) . '" alt=""></a>';
                }
                else
                {
                    echo '<a href="' . wp_get_attachment_url($attachment_id) . '" target="_blank" rel="nofollow">' . __( 'View Floorplan', 'propertyhive' ) . '</a>';
                }
            }

            echo '</div>';
        }
    }

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});