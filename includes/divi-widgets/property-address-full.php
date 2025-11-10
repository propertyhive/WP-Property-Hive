<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Address_Full_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_address_full_widget';
    public $vb_support = 'partial';
    public $icon = '';

    public function init() {
        $this->name = esc_html__( 'Property Full Address', 'propertyhive' );
        $this->icon = '3';
        $this->main_css_element = '%%order_class%%';
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

        $return = '<div class="divi-widget-address-full">';

        $return .= esc_html($property->get_formatted_full_address());

        $return .= '</div>';

        return $this->_render_module_wrapper( $return, $render_slug );
    }
}