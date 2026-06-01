<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyMeta;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyMeta extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-meta';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_meta';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-meta';
    const TITLE = 'Property Meta';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $ph_property, $attrs ) {
        if ( ! $ph_property ) { return ''; }

        if ( ! function_exists( 'propertyhive_template_single_meta' ) ) {
            return '';
        }

        global $property;

        $previous_property = $property ?? null;
        $property = $ph_property;

        ob_start();
        propertyhive_template_single_meta();
        $output = ob_get_clean();

        $property = $previous_property;

        return $output;
    }

}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyMeta() ); } );
