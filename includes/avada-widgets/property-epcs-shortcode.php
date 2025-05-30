<?php

add_shortcode( 'avada_property_epcs', function( $atts ) {
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

    if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
    {
        $epc_urls = $property->_epc_urls;
        if ( is_array($epc_urls) && !empty( $epc_urls ) )
        {
            echo '<div class="epcs">';

            if ( $atts['show_title'] == 'yes' ) { echo '<h4>' . esc_html(__( 'EPCs', 'propertyhive' )) . '</h4>'; }

            foreach ($epc_urls as $epc)
            {
                echo '<a href="' . esc_url($epc['url']) . '" data-fancybox="epcs" rel="nofollow"><img src="' . esc_url($epc['url']) . '" alt=""></a>';
            }

            echo '</div>';
        }
    }
    else
    {
        $epc_attachment_ids = $property->get_epc_attachment_ids();

        if ( !empty($epc_attachment_ids) )
        {
            echo '<div class="epcs">';

            if ( $atts['show_title'] == 'yes' ) { echo '<h4>' . esc_html(__( 'EPCs', 'propertyhive' )) . '</h4>'; }

            foreach ( $epc_attachment_ids as $attachment_id )
            {
                if ( wp_attachment_is_image($attachment_id) )
                {
                    echo '<a href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" data-fancybox="epc" rel="nofollow"><img src="' . esc_url(wp_get_attachment_url($attachment_id)) . '" alt=""></a>';
                }
                else
                {
                    echo '<a href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" target="_blank" rel="nofollow">' . esc_html(__( 'View EPC', 'propertyhive' )) . '</a>';
                }
            }

            echo '</div>';
        }
    }

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});