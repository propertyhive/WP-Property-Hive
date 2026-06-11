<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyFeatures;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyFeatures extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-features';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_features';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-features';
    const TITLE = 'Property Features';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    private static function get_icon_value( $attrs, $name, $default = '' ) {
        $value = $attrs[ $name ]['desktop']['value'] ?? $default;

        if ( is_array( $value ) ) {
            return $value['icon'] ?? $value['value'] ?? $value['unicode'] ?? $default;
        }

        return $value;
    }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }

        $hide_title   = static::get_attr_value( $attrs, 'hideTitle', 'no' ) === 'yes';
        $bullet_type  = static::get_attr_value( $attrs, 'bulletType', 'disc' );
        $bullet_color = static::get_attr_value( $attrs, 'bulletColor', '' );
        $columns      = absint( static::get_attr_value( $attrs, 'columns', 1 ) );
        if ( $columns < 1 ) { $columns = 1; }

        ob_start();
        if ( function_exists( 'propertyhive_template_single_features' ) ) {
            propertyhive_template_single_features();
        }
        $output = ob_get_clean();

        if ( '' === trim( $output ) ) { return ''; }

        if ( $hide_title ) {
            $output = preg_replace( '/<h4[^>]*>.*?<\/h4>/is', '', $output );
        }

        $ul_style = 'columns:' . $columns . ';';

        if ( 'icon' === $bullet_type ) {
            $icon_attr = $attrs['bulletIcon']['desktop']['value'] ?? '';
            if ( is_array( $icon_attr ) ) {
                $icon_attr = $icon_attr['icon'] ?? $icon_attr['value'] ?? $icon_attr['unicode'] ?? '';
            }
            $processed_icon = '';
            if ( '' !== $icon_attr ) {
                $processed_icon = function_exists( 'et_pb_process_font_icon' ) ? et_pb_process_font_icon( $icon_attr ) : $icon_attr;
            }
            if ( '' === $processed_icon ) { $processed_icon = '✓'; }

            $ul_style .= 'list-style:none;padding-left:0;';
            $icon_style = 'display:inline-block;margin-right:8px;line-height:1;font-size:1em;';
            if ( '' !== $bullet_color ) { $icon_style .= 'color:' . esc_attr( $bullet_color ) . ';'; }
            $icon_html = '<span class="et-pb-icon ph-feature-icon" aria-hidden="true" style="' . esc_attr( $icon_style ) . '">' . html_entity_decode( $processed_icon, ENT_QUOTES, 'UTF-8' ) . '</span>';
            $output = preg_replace( '/<li([^>]*)>/i', '<li$1 style="break-inside:avoid;margin-bottom:6px;">' . $icon_html, $output );
        } else {
            $ul_style .= 'list-style-type:' . ( 'square' === $bullet_type ? 'square' : 'disc' ) . ';';
            if ( '' !== $bullet_color ) {
                $ul_style .= 'color:' . esc_attr( $bullet_color ) . ';';
            }
            $output = preg_replace( '/<li([^>]*)>/i', '<li$1 style="break-inside:avoid;margin-bottom:6px;">', $output );
        }

        $output = preg_replace( '/<ul([^>]*)>/i', '<ul$1 style="' . esc_attr( $ul_style ) . '">', $output, 1 );

        return $output;
    }

}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyFeatures() ); } );
