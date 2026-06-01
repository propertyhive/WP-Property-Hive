<?php
namespace PropertyHive\Divi5Sim\Modules\PropertySearchForm;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertySearchForm extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-search-form';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_search_form';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-search-form';
    const TITLE = 'Property Search Form';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        
        $form_id = static::get_attr_value( $attrs, 'formId', 'default' ); if ( function_exists( 'ph_get_search_form' ) ) { ob_start(); ph_get_search_form( $form_id ?: 'default' ); return ob_get_clean(); } return '';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertySearchForm() ); } );
