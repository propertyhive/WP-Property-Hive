<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Map_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_map_widget';
    public $vb_support = 'partial';
    public $icon = '';

    public function init() {
        $this->name = esc_html__( 'Property Map', 'propertyhive' );
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
            'zoom' => array(
                'label' => __( 'Zoom', 'propertyhive' ),
                'type' => 'number',
                'toggle_slug' => 'main_content',
                'default' => 14
            ),
            'scrollwheel' => array(
                'label' => __( 'Scrollwheel Zoom', 'propertyhive' ),
                'type' => 'select',
                'toggle_slug' => 'main_content',
                'options' => [
                    'true'  => __( 'True', 'propertyhive' ),
                    'false' => __( 'False', 'propertyhive' ),
                ],
                'default' => 'true'
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
        if ( isset($this->props['zoom']) && isset($this->props['zoom']) && $this->props['zoom'] != '' )
        {
            $attributes['zoom'] = $this->props['zoom'];
        }
        if ( isset($this->props['scrollwheel']) && $this->props['scrollwheel'] != '' )
        {
            $attributes['scrollwheel'] = $this->props['scrollwheel'];
        }

        get_property_map($attributes);

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}