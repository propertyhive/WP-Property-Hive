<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Image_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_image_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property Image', 'propertyhive' );
        $this->icon = '&';
    }

    public function get_fields()
    {
        $fields = array(
            'image_number' => array(
                'label' => __( 'Image #', 'propertyhive' ),
                'type' => 'number',
                'toggle_slug' => 'main_content',
            ),
            'image_size' => array(
                'label' => __( 'Image Size', 'propertyhive' ),
                'type' => 'select',
                'options' => [
                    'thumbnail' => __( 'Thumbnail', 'propertyhive' ),
                    'medium' => __( 'Medium', 'propertyhive' ),
                    'large' => __( 'Large', 'propertyhive' ),
                    'full' => __( 'Full', 'propertyhive' ),
                ],
                'default_on_front' => 'large',
                'toggle_slug' => 'main_content',
            ),
            'output_ratio' => array(
                'label' => __( 'Image Ratio', 'propertyhive' ),
                'type' => 'select',
                'options' => [
                    '' => __( 'Uploaded Ratio', 'propertyhive' ),
                    '3:2' => __( '3:2', 'propertyhive' ),
                    '4:3' => __( '4:3', 'propertyhive' ),
                    '16:9' => __( '16:9', 'propertyhive' ),
                    '1:1' => __( 'Square', 'propertyhive' ),
                ],
                'default_on_front' => '',
                'toggle_slug' => 'main_content',
            ),
        );

        return $fields;
    }

    public function render( $attrs, $content, $render_slug )
    {
        $post_id = get_the_ID();

        $property = new PH_Property($post_id);

        if ( !isset($property->id) ) {
            return;
        }

        $return = '';

        $image_number = 1;
        if ( isset($this->props['image_number']) && $this->props['image_number'] != '' && is_numeric($this->props['image_number']) )
        {
            $image_number = (int)$this->props['image_number'];
        }

        $output_ratio = isset($this->props['output_ratio']) ? $this->props['output_ratio'] : '';

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
                    $url = wp_get_attachment_image_src( $gallery_attachment_ids[$image_number-1], $this->props['image_size'] );
                    $url = $url[0];
                }
            }

            // convert ratio to percentage
            $numbers = explode(':', $output_ratio);
            $percent = ( ( (int)$numbers[1] / (int)$numbers[0] ) * 100 ) . '%';

            $return .= '<div style="background:url(' . $url . ') no-repeat center center; background-size:cover; padding-bottom:' . $percent . '">';
        }
        else
        {
            // output <img>
            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
            {
                $photos = $property->_photo_urls;
                if ( isset($photos[$image_number-1]) )
                {
                    $return .= '<img src="' . $photos[$image_number-1]['url'] . '" alt="">';
                }
            }
            else
            {
                $gallery_attachment_ids = $property->get_gallery_attachment_ids();

                if ( isset($gallery_attachment_ids[$image_number-1]) )
                {
                    $return .= wp_get_attachment_image( $gallery_attachment_ids[$image_number-1], $this->props['image_size'] );
                }
            }
        }

        return $this->_render_module_wrapper( $return, $render_slug );
    }
}