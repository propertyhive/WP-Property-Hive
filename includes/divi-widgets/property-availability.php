<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Availability_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_availability_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property Availability', 'propertyhive' );
        $this->icon = '3';
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

        $return = $property->availability;

        return $this->_render_module_wrapper( $return, $render_slug );
    }
}