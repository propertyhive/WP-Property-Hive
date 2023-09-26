<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Office_Email_Address_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_office_email_address_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property Office Email Address', 'propertyhive' );
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

        $return = $property->office_email_address;

        return $this->_render_module_wrapper( $return, $render_slug );
    }
}