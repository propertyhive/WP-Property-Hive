<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyNegotiatorTelephoneNumber;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyNegotiatorTelephoneNumber extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-negotiator-telephone-number';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_negotiator_telephone_number';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-negotiator-telephone-number';
    const TITLE = 'Negotiator Telephone Number';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        $user = static::get_negotiator_user( $property );
        $v = '';
        if ( $user ) {
            $v = get_user_meta( $user->ID, 'telephone_number', true );
            if ( '' === $v ) { $v = get_user_meta( $user->ID, 'phone', true ); }
            if ( '' === $v ) { $v = get_user_meta( $user->ID, 'billing_phone', true ); }
        }
        if ( '' === $v ) { $v = $property->negotiator_telephone_number ?? ''; }
        return $v ? '<a href="tel:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>' : '';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyNegotiatorTelephoneNumber() ); } );
