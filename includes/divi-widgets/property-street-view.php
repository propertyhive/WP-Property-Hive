<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Street_View_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_street_view_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property Street View', 'propertyhive' );
        $this->icon = 'Y';
    }

    public function get_fields()
    {
        $fields = array(
            'map_height' => array(
                'label' => __( 'Height (px)', 'propertyhive' ),
                'type' => 'number',
                'toggle_slug' => 'main_content',
                'default' => 400
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

        ob_start();

        $attributes = array();
        if ( isset($this->props['map_height']) && $this->props['map_height'] != '' )
        {
            $attributes['height'] = $this->props['map_height'];
        }

        get_property_street_view($attributes);

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}