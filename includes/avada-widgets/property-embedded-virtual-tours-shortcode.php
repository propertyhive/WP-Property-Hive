<?php

add_shortcode( 'avada_property_embedded_virtual_tours', function( $atts ) {
    $atts = shortcode_atts( array(
        'show_title'    => '',
        'oembed'    => '',
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

    $virtual_tours = $property->get_virtual_tours();

    if ( !empty($virtual_tours) )
    {
        echo '<div class="embedded-virtual-tours">';

            if ( $atts['show_title'] == 'yes' ) { echo '<h4>' . esc_html(__( 'Virtual Tours', 'propertyhive' )) . '</h4>'; }

            foreach ( $virtual_tours as $virtual_tour )
            {
                if ( isset($atts['oembed']) && $atts['oembed'] == 'yes' )
                {
                    $embed_code = wp_oembed_get($virtual_tour['url']);
                    echo $embed_code;
                }
                else
                {
                    $virtual_tour['url'] = preg_replace(
                        "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
                        "//www.youtube.com/embed/$2",
                        $virtual_tour['url']
                    );

                    $virtual_tour['url'] = preg_replace(
                        '#https?://(www\.)?youtube\.com/shorts/([^/?]+)#', 
                        '//www.youtube.com/embed/$2', 
                        $virtual_tour['url']
                    );

                    $virtual_tour['url'] = preg_replace(
                        '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/?(showcase\/)*([0-9))([a-z]*\/)*([0-9]{6,11})[?]?.*/i',
                        "//player.vimeo.com/video/$6",
                        $virtual_tour['url']
                    );

                    echo '<iframe src="' . esc_url($virtual_tour['url']) . '" height="500" width="100%" allowfullscreen frameborder="0" allow="fullscreen"></iframe>';
                }
            }

        echo '</div>';
    }

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});