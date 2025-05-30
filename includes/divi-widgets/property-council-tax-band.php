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
        $this->icon = '3';
        $this->icon_element_selector       = '%%order_class%% .et-pb-icon';
        $this->icon_element_classname      = 'et-pb-icon';
        $this->main_css_element            = '%%order_class%%';
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
            'font_icon' => array(
                'label' => __( 'Icon', 'propertyhive' ),
                'type' => 'select_icon',
                'option_category' => 'basic_option',
                'class' => array( 'et-pb-font-icon' ),
                'toggle_slug' => 'icon',
            ),
            'icon_width'     => array(
                'label'           => esc_html__( 'Icon Size', 'et_builder' ),
                'default'         => '24px',
                'range_settings'  => array(
                    'min'  => '1',
                    'max'  => '200',
                    'step' => '1',
                ),
                'toggle_slug'     => 'icon_settings',
                'description'     => esc_html__( 'Here you can choose icon width.', 'et_builder' ),
                'type'            => 'range',
                'option_category' => 'layout',
                'tab_slug'        => 'advanced',
                'mobile_options'  => true,
                'validate_unit'   => true,
                'allowed_units'   => array( '%', 'em', 'rem', 'px', 'cm', 'mm', 'in', 'pt', 'pc', 'ex', 'vh', 'vw' ),
                'responsive'      => true,
                'mobile_options'  => true,
                'sticky'          => true,
                'hover'           => 'tabs',
            ),
            'icon_color'     => array(
                'default'        => et_builder_accent_color(),
                'label'          => esc_html__( 'Icon Colour', 'et_builder' ),
                'type'           => 'color-alpha',
                'description'    => esc_html__( 'Here you can define a custom color for your icon.', 'et_builder' ),
                'tab_slug'       => 'advanced',
                'toggle_slug'    => 'icon_settings',
                'hover'          => 'tabs',
                'mobile_options' => true,
                'sticky'         => true,
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

        if ( $property->council_tax_band == '' )
        {
            return;
        }

        $icon_hover_selector = str_replace( $this->icon_element_classname, $this->icon_element_classname . ':hover', $this->icon_element_selector );

        // Font Icon Style.
        $this->generate_styles(
            array(
                'utility_arg'    => 'icon_font_family',
                'render_slug'    => $render_slug,
                'base_attr_name' => 'font_icon',
                'important'      => true,
                'selector'       => $this->icon_element_selector,
                'hover_selector' => $icon_hover_selector,
                'processor'      => array(
                    'ET_Builder_Module_Helper_Style_Processor',
                    'process_extended_icon',
                ),
            )
        );

        // Font Icon Color Style.
        $this->generate_styles(
            array(
                'base_attr_name' => 'icon_color',
                'selector'       => $this->icon_element_selector,
                'css_property'   => 'color',
                'render_slug'    => $render_slug,
                'type'           => 'color',
                'hover_selector' => $icon_hover_selector,
            )
        );

        // Font Icon Size Style.
        $this->generate_styles(
            array(
                'base_attr_name' => 'icon_width',
                'selector'       => $this->icon_element_selector,
                'css_property'   => 'font-size',
                'render_slug'    => $render_slug,
                'type'           => 'range',
                'hover_selector' => $icon_hover_selector,
            )
        );

        $return = '';

        $font_icon = $this->props['font_icon'];

        $image = ( '' !== $font_icon ) ? sprintf(
            '<span class="et-pb-icon%2$s%3$s" style="%4$s">%1$s</span>',
            esc_attr( et_pb_process_font_icon( $font_icon ) ),
            '',
            '',
            'vertical-align:middle; margin-right:7px;'
        ) : '';

        $return .= '<div class="divi-widget-council-tax-band">';

        $return .= $image;

        if ( isset($this->props['before']) && $this->props['before'] != '' ) { $return .= $this->props['before'] . ' '; }

        $return .= esc_html($property->council_tax_band);

        if ( isset($this->props['after']) && $this->props['after'] != '' ) { $return .= ' ' . $this->props['after']; }

        $return .= '</div>';

        return $this->_render_module_wrapper( $return, $render_slug );
    }
}