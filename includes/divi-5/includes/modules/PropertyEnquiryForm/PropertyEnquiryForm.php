<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyEnquiryForm;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyEnquiryForm extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-enquiry-form';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_enquiry_form';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-enquiry-form';
    const TITLE = 'Property Enquiry Form';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        if ( function_exists( 'propertyhive_enquiry_form' ) ) { ob_start(); propertyhive_enquiry_form(); return ob_get_clean(); } return '';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyEnquiryForm() ); } );
