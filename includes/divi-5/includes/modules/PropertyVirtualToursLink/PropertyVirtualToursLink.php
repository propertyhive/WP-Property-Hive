<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyVirtualToursLink;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyVirtualToursLink extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-virtual-tours-link';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_virtual_tours_link';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-virtual-tours-link';
    const TITLE = 'Property Virtual Tours Link';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        $label = static::get_attr_value( $attrs, 'label', __( 'View Virtual Tour', 'propertyhive' ) ); $tours = method_exists($property,'get_virtual_tours') ? $property->get_virtual_tours() : []; if (!empty($tours[0]['url'])) return '<a href="' . esc_url($tours[0]['url']) . '" target="_blank" rel="nofollow">' . esc_html($label ?: ($tours[0]['label'] ?? __('Virtual Tour','propertyhive'))) . '</a>'; return '';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyVirtualToursLink() ); } );
