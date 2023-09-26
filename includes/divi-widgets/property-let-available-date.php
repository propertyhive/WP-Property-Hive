<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Let_Available_Date_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_let_available_date_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property Let Available Date', 'propertyhive' );
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

        if ( $property->department != 'residential-lettings' && ph_get_custom_department_based_on( $property->department ) != 'residential-lettings' )
        {
            return;
        }

        if ( $property->available_date == '' )
        {
            return;
        }

        $return = $property->get_available_date();

        return $this->_render_module_wrapper( $return, $render_slug );
    }
}