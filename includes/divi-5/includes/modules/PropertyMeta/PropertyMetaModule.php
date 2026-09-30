<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyMeta;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

abstract class PropertyMetaModule {
    const MODULE_DIR = '';
    const MODULE_CLASS_NAME = '';
    const OUTPUT_CLASS = '';
    const TEXT_ATTR = '';
    const HAS_ICON = true;
    const DEFAULT_AFTER = '';

    abstract protected static function get_property_value( $property );

    public static function register_module() {
        $module_json_folder_path = PH()->plugin_path() . '/includes/divi-5/includes/src/components/' . static::MODULE_DIR;

        ModuleRegistration::register_module(
            $module_json_folder_path,
            [
                'render_callback' => [ static::class, 'render_callback' ],
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
                            'attrName' => static::TEXT_ATTR,
                        ]
                    ),
                ],
            ]
        );
    }

    public static function module_script_data( $args ) {
        $args['elements']->script_data( [ 'attrName' => 'module' ] );
        $args['elements']->script_data( [ 'attrName' => static::TEXT_ATTR ] );
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



    protected static function property_debug_enabled() {
        return ! empty( $_GET['ph_divi_debug'] ) || ( defined( 'PROPERTYHIVE_DIVI5_DEBUG' ) && PROPERTYHIVE_DIVI5_DEBUG );
    }

    protected static function property_debug_message( $module, $message, $candidates = array() ) {
        if ( ! static::property_debug_enabled() ) {
            return '';
        }

        $candidate_summary = array();
        foreach ( $candidates as $candidate ) {
            $candidate_summary[] = isset( $candidate['source'], $candidate['id'] ) ? $candidate['source'] . ':' . $candidate['id'] : wp_json_encode( $candidate );
        }

        return '<div class="propertyhive-divi5-debug" style="padding:10px;margin:10px 0;border:1px solid #cc8a00;background:#fff8e5;color:#4d3900;font:12px/1.4 monospace;">' . esc_html( $module . ': ' . $message . ( empty( $candidate_summary ) ? '' : ' Candidates: ' . implode( ', ', $candidate_summary ) ) ) . '</div>';
    }

    protected static function get_property_candidates() {
        global $property, $post, $wp_query;

        $candidates = array();

        if ( is_object( $property ) && ! empty( $property->id ) ) {
            $candidates[] = array( 'source' => 'global_property', 'id' => absint( $property->id ), 'property' => $property );
        }

        $ids = array(
            'queried_object_id' => function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0,
            'queried_object'    => ( ( $queried = get_queried_object() ) && is_object( $queried ) && ! empty( $queried->ID ) ) ? absint( $queried->ID ) : 0,
            'wp_query_post'     => ( is_object( $wp_query ) && is_object( $wp_query->post ?? null ) && ! empty( $wp_query->post->ID ) ) ? absint( $wp_query->post->ID ) : 0,
            'global_post'       => ( is_object( $post ) && ! empty( $post->ID ) ) ? absint( $post->ID ) : 0,
            'get_the_ID'        => function_exists( 'get_the_ID' ) ? absint( get_the_ID() ) : 0,
        );

        foreach ( $ids as $source => $id ) {
            if ( $id ) {
                $candidates[] = array( 'source' => $source, 'id' => $id );
            }
        }

        return $candidates;
    }

    protected static function get_property() {
        global $property;

        if ( ! class_exists( '\\PH_Property' ) ) {
            return null;
        }

        foreach ( static::get_property_candidates() as $candidate ) {
            if ( ! empty( $candidate['property'] ) && is_object( $candidate['property'] ) && ! empty( $candidate['property']->id ) ) {
                return $candidate['property'];
            }

            if ( empty( $candidate['id'] ) ) {
                continue;
            }

            $candidate_property = new \PH_Property( absint( $candidate['id'] ) );

            if ( ! empty( $candidate_property->id ) ) {
                $property = $candidate_property;
                return $candidate_property;
            }
        }

        return null;
    }

    protected static function get_attr_value( $attrs, $name, $default = '' ) {
        return $attrs[ $name ]['desktop']['value'] ?? $attrs[ $name ]['innerContent']['desktop']['value'] ?? $default;
    }

    protected static function get_inner_content_attr_value( $attrs, $name, $default = '' ) {
        return $attrs[ $name ]['innerContent']['desktop']['value'] ?? $attrs[ $name ]['desktop']['value'] ?? $default;
    }

    protected static function get_icon_value( $attrs, $default = '' ) {
        return self::get_icon_raw_value( $attrs['icon'] ?? $default, $default );
    }

    protected static function get_icon_raw_value( $icon_attr, $default = '' ) {
        if ( is_string( $icon_attr ) ) {
            if ( '' === $icon_attr ) {
                return $default;
            }

            $parts = explode( '||', $icon_attr );

            return array(
                'unicode' => $parts[0],
                'type'    => static::normalize_icon_type( $parts[1] ?? 'divi' ),
                'weight'  => (string) ( $parts[2] ?? '400' ),
            );
        }

        if ( ! is_array( $icon_attr ) ) {
            return $default;
        }

        if ( isset( $icon_attr['unicode'] ) && is_string( $icon_attr['unicode'] ) && '' !== $icon_attr['unicode'] ) {
            $icon_attr['type']   = static::normalize_icon_type( $icon_attr['type'] ?? $icon_attr['fontFamily'] ?? 'divi' );
            $icon_attr['weight'] = (string) ( $icon_attr['weight'] ?? $icon_attr['fontWeight'] ?? '400' );

            return $icon_attr;
        }

        $nested_icon = $icon_attr['icon'] ?? $icon_attr['fontIcon'] ?? $icon_attr['value'] ?? $icon_attr['selectedIcon'] ?? null;
        if ( is_string( $nested_icon ) && '' !== $nested_icon ) {
            $normalized_icon = self::get_icon_raw_value( $nested_icon, $default );

            if ( is_array( $normalized_icon ) ) {
                $normalized_icon['type']   = static::normalize_icon_type( $icon_attr['type'] ?? $icon_attr['fontFamily'] ?? $normalized_icon['type'] );
                $normalized_icon['weight'] = (string) ( $icon_attr['weight'] ?? $icon_attr['fontWeight'] ?? $normalized_icon['weight'] );
            }

            return $normalized_icon;
        }

        $candidates = array(
            $icon_attr['innerContent']['desktop']['value'] ?? null,
            $icon_attr['desktop']['value']['icon'] ?? null,
            $icon_attr['desktop']['value']['value'] ?? null,
            $icon_attr['desktop']['value'] ?? null,
            $icon_attr['value']['icon'] ?? null,
            $icon_attr['value']['value'] ?? null,
            $icon_attr['value'] ?? null,
            $icon_attr['icon'] ?? null,
            $icon_attr['fontIcon'] ?? null,
            $icon_attr['selectedIcon'] ?? null,
        );

        foreach ( $candidates as $candidate ) {
            $value = self::get_icon_raw_value( $candidate, '' );
            if ( '' !== $value ) {
                return $value;
            }
        }

        return $default;
    }

    protected static function normalize_icon_type( $type ) {
        $type = strtolower( (string) $type );

        if ( '' === $type || 'etmodules' === $type ) {
            return 'divi';
        }

        return false !== strpos( $type, 'fontawesome' ) ? 'fa' : $type;
    }

    protected static function get_icon_font_family( $icon ) {
        if ( is_array( $icon ) ) {
            return ! empty( $icon['type'] ) && 'divi' !== $icon['type'] ? 'FontAwesome' : 'ETmodules';
        }

        if ( ! is_string( $icon ) || '' === $icon || ! function_exists( 'et_pb_get_icon_font_family' ) ) {
            return '';
        }

        return et_pb_get_icon_font_family( $icon );
    }

    protected static function process_icon( $icon ) {
        if ( empty( $icon ) ) {
            return '';
        }

        if ( class_exists( '\\ET\\Builder\\Packages\\IconLibrary\\IconFont\\Utils' ) ) {
            $processed_icon = \ET\Builder\Packages\IconLibrary\IconFont\Utils::process_font_icon( $icon );
        } else {
            $legacy_icon    = is_array( $icon )
                ? ( $icon['unicode'] ?? '' ) . '||' . ( $icon['type'] ?? 'divi' ) . '||' . ( $icon['weight'] ?? '400' )
                : $icon;
            $processed_icon = function_exists( 'et_pb_process_font_icon' ) ? et_pb_process_font_icon( $legacy_icon ) : $legacy_icon;
        }

        return is_string( $processed_icon ) ? html_entity_decode( $processed_icon, ENT_QUOTES, 'UTF-8' ) : '';
    }

    protected static function css_rule( $property, $value ) {
        if ( '' === $value || null === $value ) {
            return '';
        }

        return $property . ':' . esc_attr( $value ) . ';';
    }

    protected static function get_css_size_value( $attrs, $name, $default = '' ) {
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

    protected static function get_text_style( $attrs ) {
        $style  = '';
        $style .= static::css_rule( 'text-align', static::get_attr_value( $attrs, 'textAlign', 'left' ) );
        $style .= static::css_rule( 'color', static::get_attr_value( $attrs, 'textColor', '' ) );

        return $style;
    }

    protected static function get_icon_style( $attrs, $icon = '' ) {
        $style  = 'vertical-align:middle;margin-right:7px;line-height:1;font-size:1em;';
        $style .= static::css_rule( 'color', static::get_attr_value( $attrs, 'iconColor', '' ) );
        $style .= static::css_rule( 'font-size', static::get_css_size_value( $attrs, 'iconSize', '1em' ) );

        $font_family = static::get_icon_font_family( $icon );
        if ( '' !== $font_family ) {
            $style .= 'font-family:' . esc_attr( $font_family ) . ';';
        }

        if ( is_array( $icon ) && ! empty( $icon['weight'] ) ) {
            $style .= 'font-weight:' . esc_attr( $icon['weight'] ) . ';';
        }

        return $style;
    }

    public static function render_callback( $attrs, $content, $block, $elements ) {
        global $property;
        $property = static::get_property();

        if ( ! $property ) {
            return static::property_debug_message( static::MODULE_CLASS_NAME, 'No property could be resolved. Try ?ph_divi_debug=1 on the frontend and check candidate IDs.' , static::get_property_candidates() );
        }

        $value = static::get_property_value( $property );

        if ( '' === $value || null === $value ) {
            return '';
        }

        $before = static::get_attr_value( $attrs, 'before', '' );
        $after  = static::get_inner_content_attr_value( $attrs, 'after', static::DEFAULT_AFTER );
        $icon   = static::HAS_ICON ? static::get_icon_value( $attrs, '' ) : '';

        $children  = '<div class="et_pb_module_inner">';
        $children .= '<div class="' . esc_attr( static::OUTPUT_CLASS ) . '" style="' . esc_attr( static::get_text_style( $attrs ) ) . '">';

        if ( '' !== $icon ) {
            $processed_icon = static::process_icon( $icon );
            if ( '' !== $processed_icon ) {
                $children .= '<span class="et-pb-icon ' . esc_attr( static::OUTPUT_CLASS . '__icon' ) . '" style="' . esc_attr( static::get_icon_style( $attrs, $icon ) ) . '">' . $processed_icon . '</span>';
            }
        }

        if ( '' !== $before ) {
            $children .= '<span class="' . esc_attr( static::OUTPUT_CLASS . '__before' ) . '">' . esc_html( $before ) . ' </span>';
        }

        $children .= '<span class="' . esc_attr( static::OUTPUT_CLASS . '__value' ) . '">' . esc_html( $value ) . '</span>';

        if ( '' !== $after ) {
            $children .= '<span class="' . esc_attr( static::OUTPUT_CLASS . '__after' ) . '"> ' . esc_html( $after ) . '</span>';
        }

        $children .= '</div>';
        $children .= '</div>';

        $module_elements  = $elements->style_components( [ 'attrName' => 'module' ] );
        $module_elements .= $elements->style_components( [ 'attrName' => static::TEXT_ATTR ] );

        return Module::render(
            [
                'orderIndex'          => $block->parsed_block['orderIndex'],
                'storeInstance'       => $block->parsed_block['storeInstance'],
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'],
                'moduleClassName'     => static::MODULE_CLASS_NAME,
                'name'                => $block->block_type->name,
                'classnamesFunction'  => [ static::class, 'module_classnames' ],
                'moduleCategory'      => $block->block_type->category,
                'stylesComponent'     => [ static::class, 'module_styles' ],
                'scriptDataComponent' => [ static::class, 'module_script_data' ],
                'children'            => $module_elements . $children,
            ]
        );
    }
}
