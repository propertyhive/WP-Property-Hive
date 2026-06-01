<?php
namespace PropertyHive\Divi5Sim\Modules\PropertyAdditionalField;

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once ABSPATH . 'wp-content/themes/Divi/includes/builder-5/server/Framework/DependencyManagement/Interfaces/DependencyInterface.php';
require_once __DIR__ . '/../PropertyContentModule.php';

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use PropertyHive\Divi5Sim\Modules\PropertyContentModule;

class PropertyAdditionalField extends PropertyContentModule implements DependencyInterface {
    const MODULE_DIR = 'property-additional-field';
    const MODULE_CLASS_NAME = 'propertyhive_divi5_property_additional_field';
    const OUTPUT_CLASS = 'propertyhive-divi5-property-additional-field';
    const TITLE = 'Property Additional Field';
    const TEXT_ATTR = 'contentText';

    public function load() { add_action( 'init', [ self::class, 'register_module' ] ); }

    protected static function get_output( $property, $attrs ) {
        if ( ! $property ) { return ''; }

        $field = static::get_attr_value( $attrs, 'field', '' );
        if ( '' === $field ) { return ''; }

        $field_name = $field;
        $value = '';

        if ( substr( $field, 0, 7 ) === 'office-' ) {
            $field_name = str_replace( 'office-', '', $field );
            $office_id = isset( $property->_office_id ) ? absint( $property->_office_id ) : 0;
            if ( $office_id ) {
                $value = get_post_meta( $office_id, $field_name, true );
            }
        } else {
            if ( isset( $property->{$field} ) && '' !== $property->{$field} ) {
                $value = $property->{$field};
            } else {
                $value = get_post_meta( get_the_ID(), $field, true );
            }
        }

        if ( is_array( $value ) ) {
            $value = implode( ', ', array_filter( array_map( 'strval', $value ) ) );
        }

        if ( '' === (string) $value ) { return ''; }

        $current_settings = get_option( 'propertyhive_template_assistant', array() );
        $custom_fields = isset( $current_settings['custom_fields'] ) && is_array( $current_settings['custom_fields'] ) ? $current_settings['custom_fields'] : array();
        foreach ( $custom_fields as $custom_field ) {
            if ( isset( $custom_field['field_name'] ) && $custom_field['field_name'] === $field_name && isset( $custom_field['field_type'] ) && 'image' === $custom_field['field_type'] ) {
                $image = wp_get_attachment_image_src( $value, 'full' );
                if ( false !== $image ) {
                    $value = '<img src="' . esc_url( $image[0] ) . '" alt="">';
                }
                break;
            }
        }

        $before = static::get_attr_value( $attrs, 'before', '' );
        $after = static::get_attr_value( $attrs, 'after', '' );
        return ( $before !== '' ? esc_html( $before ) . ' ' : '' ) . wp_kses_post( $value ) . ( $after !== '' ? ' ' . esc_html( $after ) : '' );
    }
}

add_action( 'divi_module_library_modules_dependency_tree', function( $dependency_tree ) { $dependency_tree->add_dependency( new PropertyAdditionalField() ); } );
