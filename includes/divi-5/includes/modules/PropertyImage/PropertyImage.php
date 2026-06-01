<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyImage;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyImage extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-image';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_image';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-image';
    const TITLE = 'Property Image';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }

        $image_number = absint( static::get_attr_value( $attrs, 'imageNumber', 1 ) );
        if ( $image_number < 1 ) { $image_number = 1; }

        $image_size = static::get_attr_value( $attrs, 'imageSize', 'large' );
        $ratio      = static::get_attr_value( $attrs, 'outputRatio', '' );
        $link       = static::get_attr_value( $attrs, 'imageLink', '' );

        $images = static::get_property_image_items( $property, $image_size );
        if ( empty( $images[ $image_number - 1 ] ) ) { return ''; }

        $image = $images[ $image_number - 1 ];
        $html = '';

        if ( '' !== $ratio ) {
            $percent = static::ratio_to_percent( $ratio );
            $inner = '<span style="display:block;padding-bottom:' . esc_attr( $percent ) . ';"></span>';
            $html = '<div class="propertyhive-divi5-ratio-image" style="background:url(' . esc_url( $image['url'] ) . ') no-repeat center center;background-size:cover;">' . $inner . '</div>';
        } else {
            $html = $image['img'];
        }

        if ( '' !== $link ) {
            $html = '<a href="' . esc_url( $link ) . '">' . $html . '</a>';
        }

        return $html;
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyImage() ); } );
