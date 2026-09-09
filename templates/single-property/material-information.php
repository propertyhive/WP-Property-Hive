<?php
/**
 * Single Property Material Information
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
$material_information = $property->get_material_information();

if ( empty($material_information) )
{
    return;
}
// Built-in keys use literal messages so translation tools can discover every label.
$propertyhive_material_labels = array(
    'utilities' => __( 'Utilities', 'propertyhive' ),
    'accessibility' => __( 'Accessibility', 'propertyhive' ),
    'restrictions' => __( 'Restrictions', 'propertyhive' ),
    'rights' => __( 'Rights', 'propertyhive' ),
    'flood_risk' => __( 'Flood Risk', 'propertyhive' ),
    'electricity' => __( 'Electricity', 'propertyhive' ),
    'water' => __( 'Water', 'propertyhive' ),
    'heating' => __( 'Heating', 'propertyhive' ),
    'broadband' => __( 'Broadband', 'propertyhive' ),
    'sewerage' => __( 'Sewerage', 'propertyhive' ),
    'flooded_in_last_five_years' => __( 'Flooded In Last Five Years', 'propertyhive' ),
    'flood_source' => __( 'Flood Source', 'propertyhive' ),
    'flood_defences' => __( 'Flood Defences', 'propertyhive' ),
);
$propertyhive_material_label = static function ( $key ) use ( $propertyhive_material_labels ) {
    if ( isset( $propertyhive_material_labels[ $key ] ) ) {
        return $propertyhive_material_labels[ $key ];
    }
    // Preserve translations registered by extensions for their own material-information keys.
    // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Extension-defined labels have no built-in literal; preserve the existing translation contract.
    return __( ucwords( str_replace( '_', ' ', $key ) ), 'propertyhive' );
};
?>
<div class="property-material-information" style="min-width:400px">

    <h2><?php echo esc_html( __( 'Utilities & More', 'propertyhive' ) ); ?></h2>

	<?php do_action( 'propertyhive_property_material_information_start' ); ?>
    
    <?php
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
        foreach ( $material_information as $key => $value )
        {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable.
            $label = $propertyhive_material_label( $key );
            echo '<h4>' . esc_html( $label ) . '</h4>';

            if ( is_array($value) && !empty($value) )
            {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
                foreach ( $value as $subkey => $subvalue )
                {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable.
                    $label = $propertyhive_material_label( $subkey );

                    echo '<strong>' . esc_html( $label ) . ':</strong> ' . esc_html($subvalue) . '<br>';
                }
            }
            else
            {
                echo '<strong>' . esc_html( $label ) . ':</strong> ' . esc_html($value) . '<br>';
            }
        }
    ?>

	<?php do_action( 'propertyhive_property_material_information_end' ); ?>
    
</div>