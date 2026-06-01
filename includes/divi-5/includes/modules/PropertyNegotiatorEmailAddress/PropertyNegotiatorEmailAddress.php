<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyNegotiatorEmailAddress;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyNegotiatorEmailAddress extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-negotiator-email-address';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_negotiator_email_address';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-negotiator-email-address';
    const TITLE = 'Negotiator Email Address';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        $user = static::get_negotiator_user( $property );
        $v = $user ? $user->user_email : ( $property->negotiator_email_address ?? '' );
        return $v ? '<a href="mailto:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>' : '';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyNegotiatorEmailAddress() ); } );
