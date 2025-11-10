<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Images_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_images_widget';
    public $vb_support = 'partial';
    public $icon = '';

    public function init() {
        $this->name = esc_html__( 'Property Images', 'propertyhive' );
        $this->icon = "'";
    }

    public function get_fields()
    {
        $fields = array();

        return $fields;
    }

    public function render( $attrs, $content, $render_slug )
    {
        $post_id = get_the_ID();

        $property = new PH_Property($post_id);

        if ( !isset($property->id) ) {
            return;
        }

        ob_start();

        echo '<style type="text/css">
        .flexslider ul { list-style-type:none; margin:0; padding:0 }
        </style>';

        propertyhive_show_property_images();

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}