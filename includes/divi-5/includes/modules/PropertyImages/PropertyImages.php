<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyImages;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyImages extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-images';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_images';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-images';
    const TITLE = 'Property Images';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }


    protected static function get_flexslider_reset_css() {
        return '<style>
            .propertyhive-divi5-property-images .flexslider ul,
            .propertyhive-divi5-property-images .flexslider ol,
            .propertyhive-divi5-property-images .flexslider li,
            .propertyhive-divi5-property-images .flex-viewport ul,
            .propertyhive-divi5-property-images .flex-viewport ol,
            .propertyhive-divi5-property-images .flex-viewport li,
            .propertyhive-divi5-property-images .slides,
            .propertyhive-divi5-property-images .slides > li,
            .propertyhive-divi5-property-images .flex-control-nav,
            .propertyhive-divi5-property-images .flex-direction-nav {
                margin: 0;
                padding: 0;
                list-style: none;
                line-height: normal;
            }
            .propertyhive-divi5-property-images .slides > li {
                display: none;
                -webkit-backface-visibility: hidden;
            }
            .propertyhive-divi5-property-images .slides > li:first-child,
            .propertyhive-divi5-property-images .flexslider .slides > li {
                display: block;
            }
            .propertyhive-divi5-property-images .flexslider,
            .propertyhive-divi5-property-images .flex-viewport {
                max-width: 100%;
            }
            .propertyhive-divi5-property-images .flexslider img,
            .propertyhive-divi5-property-images .slides img {
                display: block;
                width: 100%;
                height: auto;
            }
            .propertyhive-divi5-property-images .thumbnails,
            .propertyhive-divi5-property-images .property-thumbnails,
            .propertyhive-divi5-property-images .propertyhive-thumbnails {
                margin-top: 12px;
            }
        </style>';
    }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }

        $num = absint( static::get_attr_value( $attrs, 'numImages', 0 ) );
        $hide_thumbnails = static::get_attr_value( $attrs, 'hideThumbnails', 'no' );
        $link_to = static::get_attr_value( $attrs, 'linkTo', 'lightbox' );

        if ( 'yes' === $hide_thumbnails ) {
            remove_action( 'propertyhive_product_thumbnails', 'propertyhive_show_property_thumbnails', 20 );
        }

        if ( function_exists( 'propertyhive_show_property_images' ) ) {
            $suffix      = '';
            $assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
            wp_enqueue_script( 'flexslider', $assets_path . 'js/flexslider/jquery.flexslider' . $suffix . '.js', array( 'jquery' ), '2.7.2', true );
            wp_enqueue_script( 'flexslider-init', $assets_path . 'js/flexslider/jquery.flexslider.init' . $suffix . '.js', array( 'jquery', 'flexslider' ), defined( 'PH_VERSION' ) ? PH_VERSION : null, true );
            wp_enqueue_style( 'flexslider_css', $assets_path . 'css/flexslider.css', array(), '2.7.2' );

            if ( 'blank' === $link_to ) {
                add_filter( 'propertyhive_single_property_image_html', array( __CLASS__, 'customise_property_images_html_blank' ), 10, 2 );
            } elseif ( 'none' === $link_to ) {
                add_filter( 'propertyhive_single_property_image_html', array( __CLASS__, 'customise_property_images_html_none' ), 10, 2 );
            } elseif ( 'property' === $link_to ) {
                add_filter( 'propertyhive_single_property_image_html', array( __CLASS__, 'customise_property_images_html_property' ), 10, 2 );
            } else {
                wp_enqueue_script( 'propertyhive_fancybox' );
                wp_enqueue_style( 'propertyhive_fancybox_css' );
            }

            ob_start();
            propertyhive_show_property_images( $num > 0 ? $num : '' );
            $html = ob_get_clean();

            remove_filter( 'propertyhive_single_property_image_html', array( __CLASS__, 'customise_property_images_html_blank' ), 10 );
            remove_filter( 'propertyhive_single_property_image_html', array( __CLASS__, 'customise_property_images_html_none' ), 10 );
            remove_filter( 'propertyhive_single_property_image_html', array( __CLASS__, 'customise_property_images_html_property' ), 10 );

            return static::get_flexslider_reset_css() . $html;
        }

        $images = static::get_property_image_items( $property, 'large' );
        if ( $num > 0 ) { $images = array_slice( $images, 0, $num ); }
        if ( empty( $images ) ) { return ''; }

        $out = '<div class="propertyhive-divi5-property-images-list">';
        foreach ( $images as $image ) {
            $html = $image['img'];
            if ( 'none' !== $link_to ) {
                $href = 'property' === $link_to ? get_permalink( get_the_ID() ) : $image['url'];
                $target = 'blank' === $link_to ? ' target="_blank"' : '';
                $lightbox = 'lightbox' === $link_to ? ' data-fancybox="gallery-' . esc_attr( get_the_ID() ) . '"' : '';
                $html = '<a href="' . esc_url( $href ) . '"' . $target . $lightbox . '>' . $html . '</a>';
            }
            $out .= $html;
        }
        return static::get_flexslider_reset_css() . $out . '</div>';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyImages() ); } );
