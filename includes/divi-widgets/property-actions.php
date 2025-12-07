<?php
if (!defined('ABSPATH')) {
    exit;
}

class Divi_Property_Actions_Widget extends ET_Builder_Module
{
    public $slug       = 'et_pb_property_actions_widget';
    public $vb_support = 'on';
    public $icon = '';

    public function init() {
        $this->name = esc_html__( 'Property Actions', 'propertyhive' );
        $this->icon = '|';
        $this->main_css_element = '%%order_class%%';
    }

    public function get_fields()
    {
        $fields = array(
            'display' => array(
                'label' => __( 'Display As', 'propertyhive' ),
                'type' => 'select',
                'options' => [
                    'list' => __( 'List', 'propertyhive' ),
                    'buttons' => __( 'Buttons', 'propertyhive' ),
                ],
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

        ob_start();

        if ( isset($this->props['display']) && $this->props['display'] == 'buttons' )
        {
            echo '<style type="text/css">';
            echo '.property_actions ul { list-style-type:none; margin:0; padding:0; }';
            echo '.property_actions ul li { display:inline-block; }';
            echo '.property_actions ul li a { display:block; }';
            echo '</style>';
        }

        propertyhive_template_single_actions();

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}