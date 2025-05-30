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

            foreach ($images as $image)
            {
                echo '<li>' . apply_filters( 'propertyhive_single_property_image_html', sprintf( '<a href="%s" class="propertyhive-main-image" title="%s" data-fancybox="gallery-' . (int)$post->ID . '">%s</a>', esc_attr( $image['url'] ), esc_attr( $image['title'] ), $image['image'] ), $post->ID ) . '</li>';
            }

            echo '</ul></div>';

        } else {

            echo apply_filters( 'propertyhive_single_property_image_html', sprintf( '<img src="%s" alt="Placeholder" />', ph_placeholder_img_src() ), $post->ID );

        }
    ?>

    <?php do_action( 'propertyhive_product_thumbnails' ); ?>

    <?php do_action( 'propertyhive_after_single_property_images' ); ?>

</div>