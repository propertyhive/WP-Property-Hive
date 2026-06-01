<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyFullDescription;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyFullDescription extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-full-description';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_full_description';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-full-description';
    const TITLE = 'Property Full Description';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }

        $hide_title = static::get_attr_value( $attrs, 'hideTitle', 'no' );

        ob_start();

        if ( 'yes' === $hide_title ) {
            echo '<style type="text/css">.description h4 { display:none; }</style>';
        }

        if ( function_exists( 'propertyhive_template_single_description' ) ) {
            propertyhive_template_single_description();
        }

        return ob_get_clean();
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyFullDescription() ); } );
