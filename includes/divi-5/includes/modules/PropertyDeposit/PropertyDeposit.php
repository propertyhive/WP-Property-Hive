<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyDeposit;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyDeposit extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-deposit';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_deposit';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-deposit';
    const TITLE = 'Property Deposit';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        $value = method_exists( $property, 'get_formatted_deposit' ) ? $property->get_formatted_deposit() : ''; return esc_html( $value );
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyDeposit() ); } );
