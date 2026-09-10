<?php
/**
 * Single Property Meta
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;
?>
<div class="property_meta">

	<?php do_action( 'propertyhive_property_meta_start' ); ?>
    
    <ul>

        <?php do_action( 'propertyhive_property_meta_list_start' ); ?>
    
    	<?php
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
            foreach ( $meta as $key => $value )
            {
                echo '<li class="' . esc_attr( $key ) . '"><span>' . esc_html($value['label']) . ':</span> ' . esc_html($value['value']) . '</li>';
            }
        ?>

        <?php do_action( 'propertyhive_property_meta_list_end' ); ?>

    </ul>

	<?php do_action( 'propertyhive_property_meta_end' ); ?>
    
</div>