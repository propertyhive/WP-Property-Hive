<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyEmbeddedVirtualTours;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyEmbeddedVirtualTours extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-embedded-virtual-tours';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_embedded_virtual_tours';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-embedded-virtual-tours';
    const TITLE = 'Property Embedded Virtual Tours';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property || ! method_exists( $property, 'get_virtual_tours' ) ) { return ''; }

        $virtual_tours = $property->get_virtual_tours();
        if ( empty( $virtual_tours ) ) { return ''; }

        $show_title = static::get_attr_value( $attrs, 'hideTitle', 'no' ) !== 'yes';
        $oembed     = static::get_attr_value( $attrs, 'oembed', 'no' ) === 'yes';

        $out = '<div class="embedded-virtual-tours propertyhive-divi5-embedded-virtual-tours">';
        if ( $show_title ) {
            $out .= '<h4>' . esc_html__( 'Virtual Tours', 'propertyhive' ) . '</h4>';
        }

        foreach ( $virtual_tours as $virtual_tour ) {
            $url = $virtual_tour['url'] ?? '';
            if ( empty( $url ) && ! empty( $virtual_tour['embed_code'] ) ) {
                $out .= '<div class="propertyhive-divi5-video-ratio" style="position:relative;width:100%;padding-bottom:56.25%;height:0;overflow:hidden;">' . $virtual_tour['embed_code'] . '</div>';
                continue;
            }
            if ( empty( $url ) ) { continue; }

            if ( $oembed ) {
                $embed_code = wp_oembed_get( $url );
                if ( $embed_code ) {
                    $out .= '<div class="propertyhive-divi5-video-ratio" style="position:relative;width:100%;padding-bottom:56.25%;height:0;overflow:hidden;">' . $embed_code . '</div>';
                    continue;
                }
            }

            if ( strpos( $url, 'instagram.com/reel' ) !== false ) {
                $out .= '<blockquote class="instagram-media" data-instgrm-permalink="' . esc_url( $url ) . '" data-instgrm-version="14"></blockquote><script async src="https://www.instagram.com/embed.js"></script>';
                continue;
            }

            $url = preg_replace(
                "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
                "//www.youtube.com/embed/$2",
                $url
            );
            $url = preg_replace(
                '#https?://(www\.)?youtube\.com/shorts/([^/?]+)#',
                '//www.youtube.com/embed/$2',
                $url
            );
            $url = preg_replace(
                '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/?(showcase\/)*([0-9))([a-z]*\/)*([0-9]{6,11})[?]?.*/i',
                "//player.vimeo.com/video/$6",
                $url
            );

            $out .= '<div class="propertyhive-divi5-video-ratio" style="position:relative;width:100%;padding-bottom:56.25%;height:0;overflow:hidden;"><iframe src="' . esc_url( $url ) . '" style="position:absolute;inset:0;width:100%;height:100%;" allowfullscreen frameborder="0" allow="fullscreen"></iframe></div>';
        }

        return $out . '</div>';
    }

}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyEmbeddedVirtualTours() ); } );
