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

    protected function _register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Gallery', 'propertyhive' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'padding',
            [
                'label' => __( 'Image Padding (px)', 'propertyhive' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'input_type' => 'number',
                'default' => 0,
            ]
        );

        $this->add_control(
            'color',
            [
                'label' => __( 'Background Colour', 'propertyhive' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-widget-availability' => 'color: {{VALUE}}',
                ],
                'default' => '',
            ]
        );

        // Add in layouts option

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
                        'image' => wp_get_attachment_image( $gallery_attachment, apply_filters( 'propertyhive_single_property_image_size', 'original' ) ),
                        'attachment_id' => $gallery_attachment,
                    );
                }
            }
        }

        ph_get_template( 'single-property/property-gallery.php', array( 'images' => $images, 'settings' => $settings ) );
    }
}