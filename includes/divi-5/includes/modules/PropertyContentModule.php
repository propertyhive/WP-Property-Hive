<?php
namespace PropertyHive\Divi5Sim\Modules;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

abstract class PropertyContentModule {
    const MODULE_DIR = '';
    const MODULE_CLASS_NAME = '';
    const OUTPUT_CLASS = '';
    const TITLE = '';
    const TEXT_ATTR = 'contentText';

    abstract protected static function get_output( $property, $attrs );

    public static function register_module() {
        $module_json_folder_path = PH()->plugin_path() . '/includes/divi-5/includes/src/components/' . static::MODULE_DIR;
        ModuleRegistration::register_module( $module_json_folder_path, [ 'render_callback' => [ static::class, 'render_callback' ] ] );
    }

    public static function module_styles( $args ) {
        $elements = $args['elements'];
        Style::add([
            'id' => $args['id'], 'name' => $args['name'], 'orderIndex' => $args['orderIndex'], 'storeInstance' => $args['storeInstance'],
            'styles' => [
                $elements->style([ 'attrName' => 'module', 'styleProps' => [ 'disabledOn' => [ 'disabledModuleVisibility' => $args['settings']['disabledModuleVisibility'] ?? null ] ] ]),
                $elements->style([ 'attrName' => static::TEXT_ATTR ]),
            ],
        ]);
    }

    public static function module_script_data( $args ) {
        $args['elements']->script_data([ 'attrName' => 'module' ]);
        $args['elements']->script_data([ 'attrName' => static::TEXT_ATTR ]);
    }

    public static function module_classnames( $args ) {
        $args['classnamesInstance']->add( ElementClassnames::classnames([ 'attrs' => $args['attrs']['module']['decoration'] ?? [] ]) );
    }

    protected static function get_attr_value( $attrs, $name, $default = '' ) {
        return $attrs[$name]['innerContent']['desktop']['value'] ?? $attrs[$name]['desktop']['value'] ?? $attrs[$name]['value'] ?? $default;
    }

    protected static function esc_style_attr( $style ) { return esc_attr( $style ); }

    protected static function get_text_style( $attrs ) {
        $style = '';
        $align = self::get_attr_value( $attrs, 'textAlign', '' );
        $color = self::get_attr_value( $attrs, 'textColor', '' );
        if ( '' !== $align ) { $style .= 'text-align:' . esc_attr( $align ) . ';'; }
        if ( '' !== $color ) { $style .= 'color:' . esc_attr( $color ) . ';'; }
        return $style;
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

    protected static function get_property_image_items( $property, $size = 'large' ) {
        $images = array();

        if ( ! $property ) { return $images; }

        if ( get_option( 'propertyhive_images_stored_as', '' ) === 'urls' ) {
            $photos = isset( $property->_photo_urls ) && is_array( $property->_photo_urls ) ? $property->_photo_urls : array();
            foreach ( $photos as $photo ) {
                if ( empty( $photo['url'] ) ) { continue; }
                $images[] = array(
                    'url'   => $photo['url'],
                    'title' => isset( $photo['title'] ) ? $photo['title'] : '',
                    'img'   => '<img src="' . esc_url( $photo['url'] ) . '" alt="' . esc_attr( isset( $photo['title'] ) ? $photo['title'] : '' ) . '">',
                );
            }
            return $images;
        }

        if ( method_exists( $property, 'get_gallery_attachment_ids' ) ) {
            $ids = $property->get_gallery_attachment_ids();
            if ( is_array( $ids ) ) {
                foreach ( $ids as $id ) {
                    $url = wp_get_attachment_image_url( $id, $size );
                    if ( ! $url ) { continue; }
                    $images[] = array(
                        'url'   => $url,
                        'title' => get_the_title( $id ),
                        'img'   => wp_get_attachment_image( $id, $size ),
                    );
                }
            }
        }

        return $images;
    }


    protected static function get_negotiator_user( $property ) {
        if ( ! $property ) { return false; }

        $ids = array(
            $property->negotiator ?? 0,
            $property->negotiator_id ?? 0,
            get_post_meta( $property->id, '_negotiator', true ),
            get_post_meta( $property->id, '_negotiator_id', true ),
        );

        foreach ( $ids as $id ) {
            $id = absint( $id );
            if ( ! $id ) { continue; }
            $user = get_userdata( $id );
            if ( $user ) { return $user; }
        }

        return false;
    }

    protected static function get_office_post( $property ) {
        if ( ! $property ) { return null; }

        $ids = array(
            $property->office ?? 0,
            $property->office_id ?? 0,
            get_post_meta( $property->id, '_office', true ),
            get_post_meta( $property->id, '_office_id', true ),
        );

        foreach ( $ids as $id ) {
            $id = absint( $id );
            if ( ! $id ) { continue; }
            $office = get_post( $id );
            if ( $office ) { return $office; }
        }

        return null;
    }

    protected static function ratio_to_percent( $ratio, $default = '66.6667%' ) {
        if ( empty( $ratio ) || false === strpos( $ratio, ':' ) ) { return $default; }
        $parts = array_map( 'absint', explode( ':', $ratio ) );
        if ( count( $parts ) !== 2 || empty( $parts[0] ) || empty( $parts[1] ) ) { return $default; }
        return ( ( $parts[1] / $parts[0] ) * 100 ) . '%';
    }

    protected static function render_module( $attrs, $content, $block, $elements, $children ) {
        $module_elements  = $elements->style_components([ 'attrName' => 'module' ]);
        $module_elements .= $elements->style_components([ 'attrName' => static::TEXT_ATTR ]);
        return Module::render([
            'orderIndex' => $block->parsed_block['orderIndex'],
            'storeInstance' => $block->parsed_block['storeInstance'],
            'attrs' => $attrs,
            'elements' => $elements,
            'id' => $block->parsed_block['id'],
            'moduleClassName' => static::MODULE_CLASS_NAME,
            'name' => $block->block_type->name,
            'classnamesFunction' => [ static::class, 'module_classnames' ],
            'moduleCategory' => $block->block_type->category,
            'stylesComponent' => [ static::class, 'module_styles' ],
            'scriptDataComponent' => [ static::class, 'module_script_data' ],
            'children' => $module_elements . $children,
        ]);
    }

    public static function render_callback( $attrs, $content, $block, $elements ) {
        global $property, $post;

        $previous_property = $property ?? null;
        $previous_post     = $post ?? null;

        $property = static::get_property();

        if ( $property && ! empty( $property->id ) ) {
            $post_object = get_post( absint( $property->id ) );
            if ( $post_object ) {
                $post = $post_object;
                setup_postdata( $post );
            }
        }

        if ( ! $property ) {
            return static::property_debug_message( static::TITLE, 'No property could be resolved. Try ?ph_divi_debug=1 on the frontend and check candidate IDs.', static::get_property_candidates() );
        }

        $output = static::get_output( $property, $attrs );

        if ( $previous_post ) {
            $post = $previous_post;
            setup_postdata( $post );
        } else {
            wp_reset_postdata();
        }
        $property = $previous_property;

        if ( '' === trim( (string) $output ) ) { return static::property_debug_message( static::TITLE, 'Property resolved, but this widget produced empty output.' ); }
        $children  = '<div class="et_pb_module_inner">';
        $children .= '<div class="' . esc_attr( static::OUTPUT_CLASS ) . '" style="' . esc_attr( static::get_text_style( $attrs ) ) . '">' . $output . '</div>';
        $children .= '</div>';
        return static::render_module( $attrs, $content, $block, $elements, $children );
    }
}
