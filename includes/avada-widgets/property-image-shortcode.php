<?php

add_shortcode( 'avada_property_image', function( $atts ) {
    $atts = shortcode_atts( array(
        'image_number'    => '',
        'image_size' => '',
        'output_ratio' => '',
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

    $image_number = 1;
    if ( isset($atts['image_number']) && $atts['image_number'] != '' && is_numeric($atts['image_number']) )
    {
        $image_number = (int)$atts['image_number'];
    }

    $output_ratio = isset($atts['output_ratio']) ? $atts['output_ratio'] : '';

    if ( $output_ratio != '' )
    {
        // output div with image as background
        $url = '';
        if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        {
            $photos = $property->_photo_urls;
            if ( isset($photos[$image_number-1]) )
            {
                $url = $photos[$image_number-1]['url'];
            }
        }
        else
        {
            $gallery_attachment_ids = $property->get_gallery_attachment_ids();

            if ( isset($gallery_attachment_ids[$image_number-1]) )
            {
                $url = wp_get_attachment_image_src( $gallery_attachment_ids[$image_number-1], $atts['image_size'] );
                $url = $url[0];
            }
        }

        // convert ratio to percentage
        $numbers = explode(':', $output_ratio);
        $percent = ( ( (int)$numbers[1] / (int)$numbers[0] ) * 100 ) . '%';

        
        if ( ! empty( $atts['image_link']['url'] ) ) 
        {
            echo '<div style="background:url(' . esc_url($url) . ') no-repeat center center; background-size:cover;">';
            $this->add_link_attributes( 'image_link', $atts['image_link'] );
            ?><a <?php $this->print_render_attribute_string( 'image_link' ); ?> style="display:block; <?php echo 'padding-bottom:' . esc_attr($percent); ?>"><?php
            echo '</a>';
            echo '</div>';
        }
        else
        {
            echo '<div style="background:url(' . esc_url($url) . ') no-repeat center center; background-size:cover; padding-bottom:' . esc_attr($percent) . '"></div>';
        }
    }
    else
    {
        // output <img>
        if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        {
            $photos = $property->_photo_urls;
            if ( isset($photos[$image_number-1]) )
            {
                if ( ! empty( $atts['image_link']['url'] ) ) 
                {
                    $this->add_link_attributes( 'image_link', $atts['image_link'] );
                    ?><a <?php $this->print_render_attribute_string( 'image_link' ); ?>><?php
                }
                echo '<img src="' . esc_url($photos[$image_number-1]['url']) . '" alt="">';
                if ( ! empty( $atts['image_link']['url'] ) ) {
                    echo '</a>';
                }
            }
        }
        else
        {
            $gallery_attachment_ids = $property->get_gallery_attachment_ids();

            if ( isset($gallery_attachment_ids[$image_number-1]) )
            {
                if ( ! empty( $atts['image_link']['url'] ) ) 
                {
                    $this->add_link_attributes( 'image_link', $atts['image_link'] );
                    ?><a <?php $this->print_render_attribute_string( 'image_link' ); ?>><?php
                }
                echo wp_get_attachment_image( $gallery_attachment_ids[$image_number-1], $atts['image_size'] );
                if ( ! empty( $atts['image_link']['url'] ) ) {
                    echo '</a>';
                }
            }
        }
    }

    do_action( 'propertyhive_avada_widget_property_image_render_after', $atts, $property );

    $html = ob_get_clean();

    fusion_element_rendering_elements( false );

    return $html;
});