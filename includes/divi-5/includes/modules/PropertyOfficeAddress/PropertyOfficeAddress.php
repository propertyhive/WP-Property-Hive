<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyOfficeAddress;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyOfficeAddress extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-office-address';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_office_address';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-office-address';
    const TITLE = 'Office Address';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        $v = $property->office_address ?? '';
        if ( '' === $v ) {
            $office = static::get_office_post( $property );
            if ( $office ) {
                $parts = array_filter( array(
                    get_post_meta( $office->ID, '_address_name_number', true ),
                    get_post_meta( $office->ID, '_address_street', true ),
                    get_post_meta( $office->ID, '_address_two', true ),
                    get_post_meta( $office->ID, '_address_three', true ),
                    get_post_meta( $office->ID, '_address_four', true ),
                    get_post_meta( $office->ID, '_address_postcode', true ),
                ) );
                $v = implode( '<br>', array_map( 'esc_html', $parts ) );
            }
        }
        return wp_kses_post( $v );
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyOfficeAddress() ); } );
