<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyBedrooms;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class PropertyBedrooms implements DependencyInterface {
    public function load() {
        add_action( 'init', [ self::class, 'register_module' ] );
    }

    public static function register_module() {
        $module_json_folder_path = PH()->plugin_path() . '/includes/divi-5/includes/src/components/property-bedrooms';

        ModuleRegistration::register_module(
            $module_json_folder_path,
            [
                'render_callback' => [ self::class, 'render_callback' ],
            ]
        );
    }

    public static function module_styles( $args ) {
        $elements = $args['elements'];

        Style::add(
            [
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => [
                    $elements->style(
                        [
                            'attrName'   => 'module',
                            'styleProps' => [
                                'disabledOn' => [
                                    'disabledModuleVisibility' => $args['settings']['disabledModuleVisibility'] ?? null,
                                ],
                            ],
                        ]
                    ),
                    $elements->style(
                        [
                            'attrName' => 'bedroomsText',
                        ]
                    ),
                ],
            ]
        );
    }

    public static function module_script_data( $args ) {
        $args['elements']->script_data( [ 'attrName' => 'module' ] );
        $args['elements']->script_data( [ 'attrName' => 'bedroomsText' ] );
    }

    public static function module_classnames( $args ) {
        $classnames_instance = $args['classnamesInstance'];
        $attrs               = $args['attrs'];

        $classnames_instance->add(
            ElementClassnames::classnames(
                [
                    'attrs' => $attrs['module']['decoration'] ?? [],
                ]
            )
        );
    }

    private static function get_attr_value( $attrs, $name, $default = '' ) {
        return $attrs[ $name ]['desktop']['value'] ?? $default;
    }

    private static function get_icon_value( $attrs, $default = '' ) {
        $value = $attrs['icon']['desktop']['value'] ?? $default;

        if ( is_array( $value ) ) {
            return $value['icon'] ?? $value['value'] ?? $value['unicode'] ?? $default;
        }

        return $value;
    }

    private static function css_rule( $property, $value ) {
        if ( '' === $value || null === $value ) {
            return '';
        }

        return $property . ':' . esc_attr( $value ) . ';';
    }

    private static function get_css_size_value( $attrs, $name, $default = '' ) {
        $value = $attrs[ $name ]['desktop']['value'] ?? $default;

        if ( is_array( $value ) ) {
            foreach ( [ 'value', 'size', 'fontSize', 'font-size', 'font_size', 'width' ] as $key ) {
                if ( isset( $value[ $key ] ) && '' !== $value[ $key ] ) {
                    return $value[ $key ];
                }
            }

            $amount = $value['amount'] ?? $value['number'] ?? $value['val'] ?? null;
            $unit   = $value['unit'] ?? $value['cssUnit'] ?? $value['css_unit'] ?? 'px';

            if ( null !== $amount && '' !== $amount ) {
                return $amount . $unit;
            }

            return $default;
        }

        if ( is_numeric( $value ) ) {
            return $value . 'px';
        }

        return $value;
    }

    private static function get_text_style( $attrs ) {
        $style  = '';
        $style .= self::css_rule( 'text-align', self::get_attr_value( $attrs, 'textAlign', 'left' ) );
        $style .= self::css_rule( 'color', self::get_attr_value( $attrs, 'textColor', '' ) );

        return $style;
    }

    private static function get_icon_style( $attrs ) {
        $style  = 'vertical-align:middle;margin-right:7px;';
        $style .= self::css_rule( 'color', self::get_attr_value( $attrs, 'iconColor', '' ) );
        $style .= self::css_rule( 'font-size', self::get_css_size_value( $attrs, 'iconSize', '24px' ) );

        return $style;
    }

    public static function render_callback( $attrs, $content, $block, $elements ) {
        $post_id = get_the_ID();

        if ( ! $post_id || ! class_exists( '\\PH_Property' ) ) {
            return '';
        }

        $property = new \PH_Property( $post_id );

        if ( empty( $property->id ) || empty( $property->bedrooms ) ) {
            return '';
        }

        $before = self::get_attr_value( $attrs, 'before', '' );
        $after = self::get_attr_value(
            $attrs['after']['innerContent'] ?? $attrs,
            isset( $attrs['after']['innerContent'] ) ? '' : 'after',
            'bedrooms'
        );
        $icon   = self::get_icon_value( $attrs, '' );

        $children  = '<div class="et_pb_module_inner">';
        $children .= '<div class="propertyhive-divi5-property-bedrooms" style="' . esc_attr( self::get_text_style( $attrs ) ) . '">';

        if ( '' !== $icon ) {
            $processed_icon = function_exists( 'et_pb_process_font_icon' ) ? et_pb_process_font_icon( $icon ) : $icon;
            $children .= '<span class="et-pb-icon propertyhive-divi5-property-bedrooms__icon" style="' . esc_attr( self::get_icon_style( $attrs ) ) . '">' . esc_html( $processed_icon ) . '</span>';
        }

        if ( '' !== $before ) {
            $children .= '<span class="propertyhive-divi5-property-bedrooms__before">' . esc_html( $before ) . ' </span>';
        }

        $children .= '<span class="propertyhive-divi5-property-bedrooms__value">' . esc_html( $property->bedrooms ) . '</span>';

        if ( '' !== $after ) {
            $children .= '<span class="propertyhive-divi5-property-bedrooms__after"> ' . esc_html( $after ) . '</span>';
        }

        $children .= '</div>';
        $children .= '</div>';

        $module_elements  = $elements->style_components( [ 'attrName' => 'module' ] );
        $module_elements .= $elements->style_components( [ 'attrName' => 'bedroomsText' ] );

        return Module::render(
            [
                'orderIndex'          => $block->parsed_block['orderIndex'],
                'storeInstance'       => $block->parsed_block['storeInstance'],
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'],
                'moduleClassName'     => 'propertyhive_divi5_property_bedrooms',
                'name'                => $block->block_type->name,
                'classnamesFunction'  => [ self::class, 'module_classnames' ],
                'moduleCategory'      => $block->block_type->category,
                'stylesComponent'     => [ self::class, 'module_styles' ],
                'scriptDataComponent' => [ self::class, 'module_script_data' ],
                'children'            => $module_elements . $children,
            ]
        );
    }
}

add_action(
    'divi_module_library_modules_dependency_tree',
    function( $dependency_tree ) {
        $dependency_tree->add_dependency( new PropertyBedrooms() );
    }
);
