<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Negotiator_Photo_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_negotiator_photo_widget';
    public $vb_support = 'partial';
    public $icon = '';

    public function init() {
        $this->name = esc_html__( 'Property Negotiator Photo', 'propertyhive' );
        $this->icon = 'G';
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

        $return = $property->negotiator_photo;

        return $this->_render_module_wrapper( $return, $render_slug );
    }
}