<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyAddressCounty;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyMeta/PropertyMetaModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyMeta\PropertyMetaModule;

class PropertyAddressCounty extends PropertyMetaModule implements DependencyInterface {
    const MODULE_DIR = 'property-address-county';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_address_county';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-address-county';
    const TEXT_ATTR = 'addressCountyText';
    const HAS_ICON = false;
    const DEFAULT_AFTER = '';

    public function load() {
        add_action( 'init', [ self::class, 'register_module' ] );
    }

    protected static function get_property_value( $property ) {
        $value = $property->address_four;

        return ( '' === $value || null === $value ) ? '' : $value;
    }
}

add_action(
    'divi_module_library_modules_dependency_tree',
    function( $dependency_tree ) {
        $dependency_tree->add_dependency( new PropertyAddressCounty() );
    }
);
