<?php
/**
 * Elementor Property Gallery Widget.
 *
 * @since 1.0.0
 */
class Elementor_Property_Gallery_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'property-gallery';
    }

    public function get_title() {
        return __( 'Gallery', 'propertyhive' );
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return [ 'property-hive' ];
    }

    public function get_keywords() {
        return [ 'property hive', 'propertyhive', 'property', 'images', 'photos', 'gallery', 'slideshow' ];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Gallery', 'propertyhive' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        /*$this->add_control(
            'padding',
            [
                'label' => __( 'Image Padding (px)', 'propertyhive' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'input_type' => 'number',
                'default' => 0,
            ]
        );*/

        $this->add_control(
            'gallery_layout',
            [
                'label' => __( 'Layout', 'propertyhive' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'grid' => __( 'Six Images', 'propertyhive' ),
                    'one_large_four_small' => __( 'One Large Image, Four Small', 'propertyhive' ),
                ],
                'default' => 'grid',
            ]
        );

        $this->add_control(
            'start_at_image',
            [
                'label' => __( 'Start at Image #', 'propertyhive' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
            ]
        );

        $this->add_control(
            'output_ratio',
            [
                'label' => __( 'Image Ratio', 'propertyhive' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '3:2' => __( '3:2', 'propertyhive' ),
                    '4:3' => __( '4:3', 'propertyhive' ),
                    '16:9' => __( '16:9', 'propertyhive' ),
                    '1:1' => __( 'Square', 'propertyhive' ),
                ],
                'default' => '4:3'
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {

        global $property;

        $settings = $this->get_settings_for_display();

        if ( !isset($property->id) ) {
            return;
        }

        $start_at_image = ( isset($settings['start_at_image']) && !empty($settings['start_at_image']) && is_numeric($settings['start_at_image']) ) ? ($settings['start_at_image'] - 1) : 0;
        
        $images = array();
        $images_hidden = array();
        if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        {
            $photo_urls = $property->_photo_urls;
            if ( !is_array($photo_urls) ) { $photo_urls = array(); }

            if ( $start_at_image > 0 )
            {
                $photo_urls_hidden = array_slice($photo_urls, 0, $start_at_image);

                foreach ( $photo_urls_hidden as $photo )
                {
                    $images_hidden[] = array(
                        'title' => isset($photo['title']) ? $photo['title'] : '',
                        'url'  => isset($photo['url']) ? $photo['url'] : '',
                        'image' => '<img src="' . ( isset($photo['url']) ? $photo['url'] : '' ) . '" alt="' . ( isset($photo['title']) ? $photo['title'] : '' ) . '">',
                    );
                }
            }
            $photo_urls = array_slice($photo_urls, $start_at_image);

            foreach ( $photo_urls as $photo )
            {
                $images[] = array(
                    'title' => isset($photo['title']) ? $photo['title'] : '',
                    'url'  => isset($photo['url']) ? $photo['url'] : '',
                    'image' => '<img src="' . ( isset($photo['url']) ? $photo['url'] : '' ) . '" alt="' . ( isset($photo['title']) ? $photo['title'] : '' ) . '">',
                );
            }
        }
        else
        {
            $gallery_attachments = $property->get_gallery_attachment_ids();

            if ( !empty($gallery_attachments) )
            {
                if ( $start_at_image > 0 )
                {
                    $gallery_attachments_hidden = array_slice($gallery_attachments, 0, $start_at_image);

                    foreach ($gallery_attachments_hidden as $gallery_attachment)
                    {
                        $images_hidden[] = array(
                            'title' => esc_attr( get_the_title( $gallery_attachment ) ),
                            'url'  => wp_get_attachment_url( $gallery_attachment ),
                            'image' => wp_get_attachment_image( $gallery_attachment, apply_filters( 'propertyhive_single_property_image_size', 'large' ) ),
                            'attachment_id' => $gallery_attachment,
                        );
                    }
                }

                $gallery_attachments = array_slice($gallery_attachments, $start_at_image);

                foreach ($gallery_attachments as $gallery_attachment)
                {
                    $images[] = array(
                        'title' => esc_attr( get_the_title( $gallery_attachment ) ),
                        'url'  => wp_get_attachment_url( $gallery_attachment ),
                        'image' => wp_get_attachment_image( $gallery_attachment, apply_filters( 'propertyhive_single_property_image_size', 'large' ) ),
                        'attachment_id' => $gallery_attachment,
                    );
                }
            }
        }

        if ( isset($images) && is_array($images) && !empty($images) ) 
        {
            $settings['padding'] = 0; // hardcode to 0 for now. Make an option going forward.
?>
        <style type="text/css">
            
            /* Clear floats after image containers */
            .ph-elementor-gallery::after {
                content: "";
                clear: both;
                display: table;
            }

            <?php $max_images = 6; if ( $settings['gallery_layout'] == 'grid' ) { ?>
            .gallery-column {
                position: relative;
                float: left;
                width: 33.33%;
                box-sizing: border-box;
                padding: <?php echo (int)$settings['padding']; ?>px;
            }
            @media (max-width: 1023px) {
                .gallery-column {
                    width: 50%;
                }
            }
            <?php }elseif ( $settings['gallery_layout'] == 'one_large_four_small' ) { $max_images = 5; ?>
            .gallery-column {
                position: relative;
                float: left;
                width: 25%;
                box-sizing: border-box;
                padding: <?php echo (int)$settings['padding']; ?>px;
            }
            .gallery-column:nth-child(1) {
                width: 50%;
            }
            @media (max-width: 1023px) {
                .gallery-column {
                    width: 50%;
                }
                .gallery-column:nth-child(1) {
                    width: 100%;
                }
            }
            <?php } ?>
            
            <?php
                $output_ratio = ( isset($settings['output_ratio']) && !empty($settings['output_ratio'])) ? $settings['output_ratio'] : '4:3';
                $padding_top = '75%';
                switch ($output_ratio)
                {
                    case "3:2": { $padding_top = '66.67%'; break; }
                    case "16:9": { $padding_top = '56.25%'; break; }
                    case "1:1": { $padding_top = '100%'; break; }
                }
            ?>
            .gallery-column > a { 
                display:block;
                height:100%; 
                padding-top:<?php echo $padding_top; ?>; 
                background:center center no-repeat; 
                background-size:cover; 
            }

            .more-images-container {
                position: absolute;
                top:<?php echo (int)$settings['padding']; ?>px;;
                left:<?php echo (int)$settings['padding']; ?>px;
                right:<?php echo (int)$settings['padding']; ?>px;
                bottom:<?php echo (int)$settings['padding']; ?>px;
            }
            .more-images-container.mobile {
                display:none;
            }
            .more-images {
                display: table;
                background: rgba(0, 0, 0, 0.5); /* Black see-through */
                height: 100%;
                width: 100%;
                opacity:1;
                font-size: 18px;
                text-align: center;
            }

            .more-images a {
                color: #f1f1f1;
                display: table-cell;
                vertical-align: middle;
                text-align:center;
                height: 100%;
            }

            @media (max-width: 767px) {
                .gallery-column {
                    width: 100%;
                }
                .gallery-column:nth-child(3),
                .gallery-column:nth-child(4),
                .gallery-column:nth-child(5),
                .gallery-column:nth-child(6) { display:none }

                .more-images-container.desktop {
                    display:none;
                }
                .more-images-container.mobile {
                    display:block;
                }
            }

        </style>

        <script>
            function openGallery()
            {
                if ( jQuery(window).width() <= 767 )
                {
                    jQuery('a#more-images-link-mobile').trigger('click');
                }
                else
                {
                    jQuery('a#more-images-link').trigger('click');
                }
                return false;
            }
        </script>

        <?php
            
            foreach ( $images_hidden as $image_hidden ) 
            {
                echo '<a href="' . esc_url($image_hidden['url']) . '" data-fancybox="elementor-gallery"></a>';
                ++$image_number;
            }
        ?>

        <div class="ph-elementor-gallery">
        <?php
            
            $image_number = 0;

            for ( $j = 0; $j < $max_images; $j++ ) 
            {
                echo '<div class="gallery-column">';

                if ( isset($images[$image_number]) )
                {
                    $id_text = $image_number == ($max_images - 1) ? 'id="more-images-link"' : '';
                    $id_text_mobile = $image_number == 1 ? 'id="more-images-link-mobile"' : '';

                    echo '<a ' . $id_text . ' ' . $id_text_mobile . ' href="' . esc_url($images[$image_number]['url']) . '" data-fancybox="elementor-gallery" style="background-image:url(' . esc_url($images[$image_number]['url']) . ')"></a>';

                    if ( $image_number == 1 )
                    {
                        echo '<div class="more-images-container mobile"><div class="more-images"><a href="javascript:;" onclick="openGallery();">';
                        printf( __( 'See all %d images', 'propertyhive' ), count($images) + count($images_hidden) );
                        echo '</a></div></div>';
                    }
                    if ( $image_number == ($max_images - 1) )
                    {
                        echo '<div class="more-images-container desktop"><div class="more-images"><a href="javascript:;" onclick="openGallery();">';
                        printf( __( 'See all %d images', 'propertyhive' ), count($images) + count($images_hidden) );
                        echo '</a></div></div>';
                    }
                }

                echo '</div>';

                ++$image_number;
            }

            while ( count($images) > ($image_number) )
            {
                echo '<a href="' . esc_url($images[$image_number]['url']) . '" data-fancybox="elementor-gallery"></a>';
                ++$image_number;
            }

            // Code second layout, for one main photo
        }
        ?>
        </div>
        <?php
    }
}