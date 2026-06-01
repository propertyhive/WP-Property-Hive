<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyFloorplansLink;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyFloorplansLink extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-floorplans-link';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_floorplans_link';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-floorplans-link';
    const TITLE = 'Property Floorplans Link';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        $label = static::get_attr_value( $attrs, 'label', __( 'View Floorplan', 'propertyhive' ) ); if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' ) { $items = $property->_floorplan_urls ?? []; if ( is_array($items) && !empty($items[0]['url']) ) return '<a href="' . esc_url($items[0]['url']) . '" data-fancybox="floorplans" rel="nofollow">' . esc_html($label) . '</a>'; } if ( method_exists($property,'get_floorplan_attachment_ids') ) { $ids=$property->get_floorplan_attachment_ids(); if (!empty($ids[0])) return '<a href="' . esc_url(wp_get_attachment_url($ids[0])) . '" data-fancybox="floorplans" rel="nofollow">' . esc_html($label) . '</a>'; } return '';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyFloorplansLink() ); } );
