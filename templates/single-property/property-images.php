<?php
/**
 * Single Property Images
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $propertyhive, $property;
?>
<div class="images">

    <?php do_action( 'propertyhive_before_single_property_images' ); ?>

    <?php
        if ( isset($images) && is_array($images) && !empty($images) ) {

            echo '<div id="slider" class="flexslider"><ul class="slides">';

            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
            foreach ($images as $image)
            {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Gallery URLs and attributes are escaped during assembly; preserve the trusted PHP gallery HTML filter.
                echo '<li>' . apply_filters( 'propertyhive_single_property_image_html', sprintf( '<a href="%s" class="propertyhive-main-image" title="%s" data-fancybox="gallery-' . (int)$post->ID . '">%s</a>', esc_url( $image['url'] ), esc_attr( $image['title'] ), $image['image'] ), $post->ID ) . '</li>';
            }

            echo '</ul></div>';

        } else {

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Gallery URLs and attributes are escaped during assembly; preserve the trusted PHP gallery HTML filter.
            echo apply_filters( 'propertyhive_single_property_image_html', sprintf( '<img src="%s" alt="Placeholder" />', esc_url( ph_placeholder_img_src() ) ), $post->ID );

        }
    ?>

    <?php do_action( 'propertyhive_product_thumbnails' ); ?>

    <?php do_action( 'propertyhive_after_single_property_images' ); ?>

</div>
