<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyOfficeName;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyOfficeName extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-office-name';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_office_name';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-office-name';
    const TITLE = 'Office Name';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        $name = $property->office_name ?? '';
        if ( '' === $name ) {
            $office = static::get_office_post( $property );
            if ( $office ) { $name = get_the_title( $office ); }
        }
        return esc_html( $name );
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyOfficeName() ); } );
