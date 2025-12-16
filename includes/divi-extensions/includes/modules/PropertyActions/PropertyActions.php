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
            'button_background_color' => array(
                'label'           => esc_html__( 'Button Background Color', 'propertyhive' ),
                'type'            => 'color',
                'custom_color'    => true,
                'show_if'         => array(
                    'display' => 'buttons',
                ),
                'default'         => '#000',
            ),

            'button_text_color' => array(
                'label'           => esc_html__( 'Button Text Color', 'propertyhive' ),
                'type'            => 'color',
                'custom_color'    => true,
                'show_if'         => array(
                    'display' => 'buttons',
                ),
                'default'         => '#ffffff',
            ),

            'button_padding' => array(
                'label'           => esc_html__( 'Button Padding', 'propertyhive' ),
                'type'            => 'text',
                'show_if'         => array(
                    'display' => 'buttons',
                ),
                'default'         => '10px 15px',
                'description'     => esc_html__( 'CSS padding value, e.g. 10px 15px', 'propertyhive' ),
            ),

            'button_margin' => array(
                'label'           => esc_html__( 'Button Margin', 'propertyhive' ),
                'type'            => 'text',
                'show_if'         => array(
                    'display' => 'buttons',
                ),
                'default'         => '0 5px 0 0',
                'description'     => esc_html__( 'CSS margin value, e.g. 0 5px 0 0', 'propertyhive' ),
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

        $display                 = $this->props['display'];
        $button_bg_color         = $this->props['button_background_color'];
        $button_text_color       = $this->props['button_text_color'];
        $button_padding          = $this->props['button_padding'];
        $button_margin           = $this->props['button_margin'];

        ob_start();

        if ( 'buttons' === $display ) {
            ?>
            <style>
                .property_actions ul { list-style-type:none; margin:0; padding:0;  }
                .property_actions ul li {
                    display: inline-block;
                }
                .property_actions ul li a {
                    display: block;
                    background: <?php echo esc_html( $button_bg_color ); ?>;
                    color: <?php echo esc_html( $button_text_color ); ?>;
                    padding: <?php echo esc_html( $button_padding ); ?>;
                    margin: <?php echo esc_html( $button_margin ); ?>;
                    text-decoration: none;
                }
            </style>
            <?php
        }

        propertyhive_template_single_actions();

        return $this->_render_module_wrapper( ob_get_clean(), $render_slug );
    }
}

if ( class_exists( 'ET_Builder_Module' ) ) {
    new PH_Divi_Property_Actions_Module();
}