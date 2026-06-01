<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyEpcs;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyEpcs extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-epcs';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_epcs';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-epcs';
    const TITLE = 'Property EPCs';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function image_link( $url, $group = 'epcs', $label = 'View EPC' ) {
        if ( empty( $url ) ) { return ''; }
        $path = strtolower( parse_url( $url, PHP_URL_PATH ) );
        $is_image = (bool) preg_match( '/\.(jpe?g|png|gif|webp|bmp|svg)$/', $path );
        if ( $is_image ) {
            return '<a href="' . esc_url( $url ) . '" data-fancybox="' . esc_attr( $group ) . '" rel="nofollow" style="display:block;width:100%;margin-bottom:16px;"><img src="' . esc_url( $url ) . '" alt="" style="display:block;width:100%;height:auto;"></a>';
        }
        return '<a href="' . esc_url( $url ) . '" target="_blank" rel="nofollow">' . esc_html( $label ) . '</a>';
    }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }

        $show_title = static::get_attr_value( $attrs, 'hideTitle', 'no' ) !== 'yes';
        $out = '<div class="epcs">';
        if ( $show_title ) {
            $out .= '<h4>' . esc_html__( 'EPCs', 'propertyhive' ) . '</h4>';
        }

        if ( get_option( 'propertyhive_epcs_stored_as', '' ) === 'urls' ) {
            $items = isset( $property->_epc_urls ) && is_array( $property->_epc_urls ) ? $property->_epc_urls : array();
            foreach ( $items as $item ) {
                $url = is_array( $item ) ? ( $item['url'] ?? '' ) : $item;
                if ( empty( $url ) ) { continue; }
                $out .= '<a href="' . esc_url( $url ) . '" data-fancybox="epcs" rel="nofollow" style="display:block;width:100%;margin-bottom:16px;"><img src="' . esc_url( $url ) . '" alt="" style="display:block;width:100%;height:auto;"></a>';
            }
        } else {
            $ids = method_exists( $property, 'get_epc_attachment_ids' ) ? $property->get_epc_attachment_ids() : array();
            foreach ( $ids as $attachment_id ) {
                $url = wp_get_attachment_url( $attachment_id );
                if ( ! $url ) { continue; }
                if ( wp_attachment_is_image( $attachment_id ) ) {
                    $out .= '<a href="' . esc_url( $url ) . '" data-fancybox="epcs" rel="nofollow" style="display:block;width:100%;margin-bottom:16px;"><img src="' . esc_url( $url ) . '" alt="" style="display:block;width:100%;height:auto;"></a>';
                } else {
                    $out .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="nofollow">' . esc_html__( 'View EPC', 'propertyhive' ) . '</a>';
                }
            }
        }

        $out .= '</div>';
        return false === strpos( $out, '<img ' ) && false === strpos( $out, '<a ' ) ? '' : $out;
    }

}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyEpcs() ); } );
