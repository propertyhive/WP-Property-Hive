<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyReceptionRooms;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyMeta/PropertyMetaModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyMeta\PropertyMetaModule;

class PropertyReceptionRooms extends PropertyMetaModule implements DependencyInterface {
    const MODULE_DIR = 'property-reception-rooms';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_reception_rooms';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-reception-rooms';
    const TEXT_ATTR = 'receptionRoomsText';
    const HAS_ICON = true;
    const DEFAULT_AFTER = 'reception rooms';

    public function load() {
        add_action( 'init', [ self::class, 'register_module' ] );
    }

    protected static function get_property_value( $property ) {
        return $property->reception_rooms ?? '';
    }
}

add_action(
    'divi_module_library_modules_dependency_tree',
    function( $dependency_tree ) {
        $dependency_tree->add_dependency( new PropertyReceptionRooms() );
    }
);
