<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Council_Tax_Band_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_council_tax_band_widget';
    public $vb_support = 'partial';

    public function init() {
        $this->name = esc_html__( 'Property Council Tax Band', 'propertyhive' );
        $this->icon = '&';
    }

    public function get_fields()
    {
        $fields = array(
            'image_number' => array(
                'label' => 'Image #',
                'type' => 'number',
                'toggle_slug' => 'main_content',
            ),
        );

        return $fields;
    }

    public function render($attrs, $render_slug, $content = null)
    {
        $post_id = get_the_ID();

        $property = new PH_Property($post_id);

        if ( !isset($property->id) ) {
            return;
        }

        $return = 'Council_Tax_Band';

        return $this->_render_module_wrapper( $return, $render_slug );
    }
}