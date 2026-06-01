<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyAddressLine2;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyMeta/PropertyMetaModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyMeta\PropertyMetaModule;

class PropertyAddressLine2 extends PropertyMetaModule implements DependencyInterface {
    const MODULE_DIR = 'property-address-line-2';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_address_line_2';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-address-line-2';
    const TEXT_ATTR = 'addressLine2Text';
    const HAS_ICON = false;
    const DEFAULT_AFTER = '';

    public function load() {
        add_action( 'init', [ self::class, 'register_module' ] );
    }

    protected static function get_property_value( $property ) {
        return $property->address_two ?? '';
    }
}

add_action(
    'divi_module_library_modules_dependency_tree',
    function( $dependency_tree ) {
        $dependency_tree->add_dependency( new PropertyAddressLine2() );
    }
);
