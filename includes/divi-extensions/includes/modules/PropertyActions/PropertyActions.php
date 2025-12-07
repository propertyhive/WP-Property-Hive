<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PH_Divi_Property_Actions_Module extends ET_Builder_Module {

    public $slug       = 'et_pb_property_actions_widget';
    public $vb_support = 'on';

    public function init() {
        $this->name             = __( 'Property Actions', 'propertyhive' );
        $this->main_css_element = '%%order_class%%';
    }

    public function get_fields() {
        return array(
            'display' => array(
                'label'   => __( 'Display As', 'propertyhive' ),
                'type'    => 'select',
                'options' => array(
                    'list'    => __( 'List', 'propertyhive' ),
                    'buttons' => __( 'Buttons', 'propertyhive' ),
                ),
                'default' => 'list',
            ),
        );
    }

    public function render( $attrs, $content = null, $render_slug = null ) {

        $post_id  = get_the_ID();
        $property = new PH_Property( $post_id );

        if ( ! isset( $property->id ) ) {
            return '';
        }

        ob_start();

        if ( 'buttons' === $this->props['display'] ) {
            echo '<style>.property_actions ul li { display:inline-block; }</style>';
        }

        propertyhive_template_single_actions();

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}

if ( class_exists( 'ET_Builder_Module' ) ) {
    new PH_Divi_Property_Actions_Module();
}