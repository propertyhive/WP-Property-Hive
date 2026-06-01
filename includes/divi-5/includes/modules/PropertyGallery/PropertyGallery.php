<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyGallery;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyGallery extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-gallery';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_gallery';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-gallery';
    const TITLE = 'Property Gallery';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }

        $layout = static::get_attr_value( $attrs, 'galleryLayout', 'grid' );
        $start  = absint( static::get_attr_value( $attrs, 'startAtImage', 1 ) );
        $ratio  = static::get_attr_value( $attrs, 'outputRatio', '4:3' );
        if ( $start < 1 ) { $start = 1; }

        $images = array_slice( static::get_property_image_items( $property, 'large' ), $start - 1 );
        if ( empty( $images ) ) { return ''; }

        $visible_count = $layout === 'one_large_four_small' ? 5 : 6;
        $visible = array_slice( $images, 0, $visible_count );
        $percent = static::ratio_to_percent( $ratio, '75%' );

        $classes = 'propertyhive-divi5-gallery propertyhive-divi5-gallery--' . sanitize_html_class( $layout );
        $grid_columns = $layout === 'one_large_four_small' ? 'repeat(4,1fr)' : 'repeat(3,1fr)';
        $out = '<div class="' . esc_attr( $classes ) . '" style="display:grid;gap:8px;grid-template-columns:' . esc_attr( $grid_columns ) . ';">';

        $total_images = count( $images );
        $last_visible_index = count( $visible ) - 1;

        foreach ( $visible as $i => $image ) {
            $span = ( $layout === 'one_large_four_small' && $i === 0 ) ? 'grid-column:span 2;grid-row:span 2;' : '';
            $out .= '<a href="' . esc_url( $image['url'] ) . '" data-fancybox="gallery-' . esc_attr( get_the_ID() ) . '" style="display:block;position:relative;overflow:hidden;' . esc_attr( $span ) . 'background:url(' . esc_url( $image['url'] ) . ') no-repeat center center;background-size:cover;">';
            $out .= '<span style="display:block;padding-bottom:' . esc_attr( $percent ) . ';"></span>';
            if ( $total_images > count( $visible ) && $i === $last_visible_index ) {
                $out .= '<span class="propertyhive-divi5-gallery-overlay" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.55);color:#fff;font-weight:600;text-align:center;padding:10px;">' . esc_html( sprintf( __( 'See all %d images', 'propertyhive' ), $total_images ) ) . '</span>';
            }
            $out .= '</a>';
        }

        $hidden = array_slice( $images, $visible_count );
        foreach ( $hidden as $image ) {
            $out .= '<a href="' . esc_url( $image['url'] ) . '" data-fancybox="gallery-' . esc_attr( get_the_ID() ) . '" style="display:none"></a>';
        }

        return $out . '</div>';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyGallery() ); } );
