<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyStreetView;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyStreetView extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-street-view';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_street_view';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-street-view';
    const TITLE = 'Property Street View';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        $height = static::get_attr_value( $attrs, 'height', '400px' );
        $height = is_numeric( $height ) ? (int) $height : absint( $height );
        $attributes = array();
        if ( $height > 0 ) { $attributes['height'] = $height; }
        if ( function_exists( 'get_property_street_view' ) ) { ob_start(); get_property_street_view( $attributes ); return ob_get_clean(); }
        return '';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyStreetView() ); } );
