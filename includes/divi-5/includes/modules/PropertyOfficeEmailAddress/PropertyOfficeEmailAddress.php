<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyOfficeEmailAddress;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyOfficeEmailAddress extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-office-email-address';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_office_email_address';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-office-email-address';
    const TITLE = 'Office Email Address';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        $v = $property->office_email_address ?? '';
        if ( '' === $v ) {
            $office = static::get_office_post( $property );
            if ( $office ) { $v = get_post_meta( $office->ID, '_email_address', true ); }
        }
        return $v ? '<a href="mailto:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>' : '';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyOfficeEmailAddress() ); } );
