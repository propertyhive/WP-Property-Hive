<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyEnquiryFormLink;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyEnquiryFormLink extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-enquiry-form-link';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_enquiry_form_link';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-enquiry-form-link';
    const TITLE = 'Property Enquiry Form Link';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        $label = static::get_attr_value( $attrs, 'label', __( 'Make Enquiry', 'propertyhive' ) ); $id = isset($property->id) ? (int) $property->id : 0; ob_start(); echo '<a data-fancybox data-src="#makeEnquiry' . $id . '" href="javascript:;">' . esc_html( $label ) . '</a>'; echo '<div id="makeEnquiry' . $id . '" style="display:none;"><h2>' . esc_html( $label ) . '</h2>'; if ( function_exists('propertyhive_enquiry_form') ) { propertyhive_enquiry_form(); } echo '</div>'; return ob_get_clean();
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyEnquiryFormLink() ); } );
