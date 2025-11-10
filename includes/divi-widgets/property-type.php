<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Type_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_type_widget';
    public $vb_support = 'partial';
    public $icon = '';

    public function init() {
        $this->name = esc_html__( 'Property Type', 'propertyhive' );
        $this->icon = '&';
    }

    public function get_fields()
    {
        $fields = array(
            'before' => array(
                'label' => __( 'Before', 'propertyhive' ),
                'type' => 'text',
                'toggle_slug' => 'main_content',
            ),
            'after' => array(
                'label' => __( 'After', 'propertyhive' ),
                'type' => 'text',
                'toggle_slug' => 'main_content',
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

        if ( $property->property_type == '' )
        {
            return;
        }

        $return = '';

        if ( isset($this->props['before']) && $this->props['before'] != '' ) { $return .= $this->props['before'] . ' '; }

        $return .= esc_html($property->property_type);

        if ( isset($this->props['after']) && $this->props['after'] != '' ) { $return .= ' ' . $this->props['after']; }

        return $this->_render_module_wrapper( $return, $render_slug );
    }
}