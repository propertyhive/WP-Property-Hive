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

$material_information = $property->get_material_information();

if ( empty($material_information) )
{
    return;
}
?>
<div class="property-material-information" style="min-width:400px">

    <h2><?php echo esc_html( __( 'Utilities & More', 'propertyhive' ) ); ?></h2>

	<?php do_action( 'propertyhive_property_material_information_start' ); ?>
    
    <?php
        foreach ( $material_information as $key => $value )
        {
            $label = $key;
            $label = str_replace("_", " ", $label);
            $label = ucwords($label);
            echo '<h4>' . esc_html( __( $label, 'propertyhive' ) ) . '</h4>';

            if ( is_array($value) && !empty($value) )
            {
                foreach ( $value as $subkey => $subvalue )
                {
                    $label = $subkey;
                    $label = str_replace("_", " ", $label);
                    $label = ucwords($label);

                    echo '<strong>' . esc_html( __( $label, 'propertyhive' ) ) . ':</strong> ' . esc_html($subvalue) . '<br>';
                }
            }
            else
            {
                echo '<strong>' . esc_html( __( $label, 'propertyhive' ) ) . ':</strong> ' . esc_html($value) . '<br>';
            }
        }
    ?>

	<?php do_action( 'propertyhive_property_material_information_end' ); ?>
    
</div>