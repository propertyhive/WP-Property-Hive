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
        return 'fa fa-images';
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
                'default' => 'six_image',
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

        $images = array();
        if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        {
            $photo_urls = $property->_photo_urls;
            if ( !is_array($photo_urls) ) { $photo_urls = array(); }

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
            @media (max-width: 767px) {
                .gallery-column {
                    width: 100%;
                }
            }

            .gallery-column > a { display:block; height:100%; padding-top:75%; background:center center no-repeat; background-size:cover; }

            .more-images-container {
                position: absolute;
                top:<?php echo (int)$settings['padding']; ?>px;;
                left:<?php echo (int)$settings['padding']; ?>px;
                right:<?php echo (int)$settings['padding']; ?>px;
                bottom:<?php echo (int)$settings['padding']; ?>px;
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
        </style>

        <script>
            function openGallery()
            {
                jQuery('a#more-images-link').trigger('click');
                return false;
            }
        </script>

        <div class="ph-elementor-gallery">
        <?php
            $image_number = 0;
            for ( $j = 0; $j < $max_images; $j++ ) 
            {
                echo '<div class="gallery-column">';

                if ( isset($images[$image_number]) )
                {
                    $id_text = $image_number == ($max_images - 1) ? 'id="more-images-link"' : '';

                    echo '<a ' . $id_text . ' href="' . $images[$image_number]['url'] . '" data-fancybox="elementor-gallery" style="background-image:url(' . $images[$image_number]['url'] . ')"></a>';

                    if ( $image_number == ($max_images - 1) )
                    {
                        echo '<div class="more-images-container"><div class="more-images"><a href="javascript:;" onclick="openGallery();">';
                        printf( __( 'See all %d images', 'propertyhive' ), count($images) );
                        echo '</a></div></div>';
                    }
                }

                echo '</div>';

                ++$image_number;
            }

            while ( count($images) > ($image_number) )
            {
                echo '<a href="' . $images[$image_number]['url'] . '" data-fancybox="elementor-gallery"></a>';
                ++$image_number;
            }

            // Code second layout, for one main photo
        }
        ?>
        </div>
        <?php
    }
}