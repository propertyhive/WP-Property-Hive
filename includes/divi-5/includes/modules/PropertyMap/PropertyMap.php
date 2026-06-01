<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyMap;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class PropertyMap implements DependencyInterface {
    public function load() {
        add_action( 'init', [ self::class, 'register_module' ] );
    }

    public static function register_module() {
        $module_json_folder_path = PH()->plugin_path() . '/includes/divi-5/includes/src/components/property-map';

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
                ],
            ]
        );
    }

    public static function module_script_data( $args ) {
        $args['elements']->script_data( [ 'attrName' => 'module' ] );
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

    private static function normalise_range_value( $value, $default = '' ) {
        if ( is_array( $value ) ) {
            foreach ( [ 'value', 'size', 'amount', 'number', 'val' ] as $key ) {
                if ( isset( $value[ $key ] ) && '' !== $value[ $key ] ) {
                    $unit = $value['unit'] ?? $value['cssUnit'] ?? $value['css_unit'] ?? '';
                    return $value[ $key ] . $unit;
                }
            }

            return $default;
        }

        return ( '' !== $value && null !== $value ) ? $value : $default;
    }

    private static function normalise_height_for_property_map( $height ) {
        $height = self::normalise_range_value( $height, '400' );

        if ( is_string( $height ) && preg_match( '/^([0-9.]+)px$/', $height, $matches ) ) {
            return $matches[1];
        }

        return $height;
    }

    private static function normalise_zoom_for_property_map( $zoom ) {
        $zoom = self::normalise_range_value( $zoom, '14' );

        if ( is_array( $zoom ) ) {
            $zoom = $zoom['value'] ?? $zoom['size'] ?? $zoom['amount'] ?? '14';
        }

        if ( is_string( $zoom ) ) {
            $zoom = preg_replace( '/[^0-9]/', '', $zoom );
        }

        $zoom = absint( $zoom );

        return $zoom > 0 ? (string) $zoom : '14';
    }


    private static function debug_enabled() {
        return ! empty( $_GET['ph_divi_debug'] ) || ( defined( 'PROPERTYHIVE_DIVI5_DEBUG' ) && PROPERTYHIVE_DIVI5_DEBUG );
    }

    private static function debug_message( $message, $candidates = array() ) {
        if ( ! self::debug_enabled() ) { return ''; }
        $candidate_summary = array();
        foreach ( $candidates as $candidate ) {
            $candidate_summary[] = isset( $candidate['source'], $candidate['id'] ) ? $candidate['source'] . ':' . $candidate['id'] : wp_json_encode( $candidate );
        }
        return '<div class="propertyhive-divi5-debug" style="padding:10px;margin:10px 0;border:1px solid #cc8a00;background:#fff8e5;color:#4d3900;font:12px/1.4 monospace;">' . esc_html( 'Property Map: ' . $message . ( empty( $candidate_summary ) ? '' : ' Candidates: ' . implode( ', ', $candidate_summary ) ) ) . '</div>';
    }

    private static function get_property_candidates() {
        global $property, $post, $wp_query;
        $candidates = array();
        if ( is_object( $property ) && ! empty( $property->id ) ) { $candidates[] = array( 'source' => 'global_property', 'id' => absint( $property->id ), 'property' => $property ); }
        $ids = array(
            'queried_object_id' => function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0,
            'queried_object' => ( ( $queried = get_queried_object() ) && is_object( $queried ) && ! empty( $queried->ID ) ) ? absint( $queried->ID ) : 0,
            'wp_query_post' => ( is_object( $wp_query ) && is_object( $wp_query->post ?? null ) && ! empty( $wp_query->post->ID ) ) ? absint( $wp_query->post->ID ) : 0,
            'global_post' => ( is_object( $post ) && ! empty( $post->ID ) ) ? absint( $post->ID ) : 0,
            'get_the_ID' => function_exists( 'get_the_ID' ) ? absint( get_the_ID() ) : 0,
        );
        foreach ( $ids as $source => $id ) { if ( $id ) { $candidates[] = array( 'source' => $source, 'id' => $id ); } }
        return $candidates;
    }

    private static function get_property() {
        global $property;
        if ( ! class_exists( '\\PH_Property' ) ) { return null; }
        foreach ( self::get_property_candidates() as $candidate ) {
            if ( ! empty( $candidate['property'] ) && is_object( $candidate['property'] ) && ! empty( $candidate['property']->id ) ) { return $candidate['property']; }
            if ( empty( $candidate['id'] ) ) { continue; }
            $candidate_property = new \PH_Property( absint( $candidate['id'] ) );
            if ( ! empty( $candidate_property->id ) ) { $property = $candidate_property; return $candidate_property; }
        }
        return null;
    }

    public static function render_callback( $attrs, $content, $block, $elements ) {
        if ( ! function_exists( 'get_property_map' ) ) {
            return self::debug_message( 'get_property_map() is not available.' );
        }

        $property = self::get_property();

        if ( ! $property ) {
            return self::debug_message( 'No property could be resolved. Try ?ph_divi_debug=1 on the frontend and check candidate IDs.', self::get_property_candidates() );
        }

        $map_attributes = [];

        $height = self::normalise_height_for_property_map( self::get_attr_value( $attrs, 'height', '400px' ) );
        $zoom = self::normalise_zoom_for_property_map( self::get_attr_value( $attrs, 'zoom', '14' ) );
        $scrollwheel = self::get_attr_value( $attrs, 'scrollwheel', 'true' );

        if ( '' !== $height ) {
            $map_attributes['height'] = $height;
        }

        if ( '' !== $zoom ) {
            $map_attributes['zoom'] = $zoom;
        }

        if ( '' !== $scrollwheel ) {
            $map_attributes['scrollwheel'] = $scrollwheel;
        }

        $map_attributes = apply_filters( 'propertyhive_divi_property_map_attributes', $map_attributes, $attrs, $property );
        $map_attributes = apply_filters( 'propertyhive_elementor_property_map_attributes', $map_attributes );

        ob_start();
        get_property_map( $map_attributes );
        $map = ob_get_clean();

        if ( '' === trim( $map ) ) {
            return '';
        }

        $children  = '<div class="et_pb_module_inner">';
        $children .= $map;
        $children .= '</div>';

        $module_elements = $elements->style_components( [ 'attrName' => 'module' ] );

        return Module::render(
            [
                'orderIndex'          => $block->parsed_block['orderIndex'],
                'storeInstance'       => $block->parsed_block['storeInstance'],
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'],
                'moduleClassName'     => 'propertyhive_divi5_property_map',
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
        $dependency_tree->add_dependency( new PropertyMap() );
    }
);
