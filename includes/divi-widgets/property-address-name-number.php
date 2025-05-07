<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Address_Name_Number_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_address_name_number_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property Address Name/Number', 'propertyhive' );
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

        if ( $property->address_name_number == '' )
        {
            return;
        }

        $return = '<div class="divi-widget-address-name-number">';

        $return .= esc_html($property->address_name_number);

        $return .= '</div>';

        return $this->_render_module_wrapper( $return, $render_slug );
    }
}