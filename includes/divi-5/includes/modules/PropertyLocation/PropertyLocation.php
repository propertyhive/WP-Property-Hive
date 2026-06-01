<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyLocation;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyLocation extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-location';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_location';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-location';
    const TITLE = 'Property Location';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        return esc_html( $property->location ?? '' );
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyLocation() ); } );
