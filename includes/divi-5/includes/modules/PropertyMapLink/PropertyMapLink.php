<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyMapLink;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyMapLink extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-map-link';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_map_link';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-map-link';
    const TITLE = 'Property Map Link';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        $label = static::get_attr_value( $attrs, 'label', __( 'View Map', 'propertyhive' ) ); if ( ! empty( $property->latitude ) && ! empty( $property->longitude ) ) { return '<a href="https://www.google.com/maps/?q=' . (float)$property->latitude . ',' . (float)$property->longitude . '&ll=' . (float)$property->latitude . ',' . (float)$property->longitude . '" target="_blank" rel="nofollow">' . esc_html( $label ) . '</a>'; } return '';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyMapLink() ); } );
