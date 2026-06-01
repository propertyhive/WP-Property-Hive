<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyNegotiatorPhoto;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyNegotiatorPhoto extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-negotiator-photo';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_negotiator_photo';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-negotiator-photo';
    const TITLE = 'Negotiator Photo';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        $user = static::get_negotiator_user( $property );
        if ( $user ) {
            $avatar = get_avatar( $user->ID, 300 );
            if ( $avatar ) { return $avatar; }
        }
        return $property->negotiator_photo ?? '';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyNegotiatorPhoto() ); } );
