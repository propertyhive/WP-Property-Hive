<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Office_Name_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_office_name_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property Office Name', 'propertyhive' );
        $this->icon = '&';
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

        $return = esc_html($property->office_name);

        return $this->_render_module_wrapper( $return, $render_slug );
    }
}