<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyMapLink;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyMapLink extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-map-link';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_map_link';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-map-link';
    const TITLE = 'Property Map Link';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }
        if ( empty( $property->latitude ) || empty( $property->longitude ) ) { return ''; }

        $label     = static::get_attr_value( $attrs, 'label', __( 'View Map', 'propertyhive' ) );
        $link_type = static::get_attr_value( $attrs, 'mapLinkType', '_blank' );
        $latitude  = (float) $property->latitude;
        $longitude = (float) $property->longitude;

        if ( 'embedded' === $link_type ) {
            return '<a href="#map_lightbox" data-fancybox>' . esc_html( $label ) . '</a>'
                . '<div id="map_lightbox" style="display:none;width:90%;max-width:800px;">'
                . do_shortcode( '[property_map]' )
                . '</div>';
        }

        if ( 'iframe' === $link_type ) {
            $map_url = 'https://maps.google.com/?output=embed&f=q&q=' . $latitude . ',' . $longitude . '&ll=' . $latitude . ',' . $longitude . '&layer=t&hq=&t=m&z=15';
            return '<a href="#" data-fancybox data-type="iframe" data-src="' . esc_url( $map_url ) . '">' . esc_html( $label ) . '</a>';
        }

        $map_url = 'https://www.google.com/maps/?q=' . $latitude . ',' . $longitude . '&ll=' . $latitude . ',' . $longitude;
        return '<a href="' . esc_url( $map_url ) . '" target="_blank" rel="nofollow noopener">' . esc_html( $label ) . '</a>';
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyMapLink() ); } );
