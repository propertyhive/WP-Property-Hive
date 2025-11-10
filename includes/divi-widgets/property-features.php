<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Features_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_features_widget';
    public $vb_support = 'partial';
    public $icon = '';

    public function init() {
        $this->name = esc_html__( 'Property Features', 'propertyhive' );
        $this->icon = '!';
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

        propertyhive_template_single_features();

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}