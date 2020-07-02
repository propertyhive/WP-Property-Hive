<?php
/**
 * The template for displaying property content in the single-property.php template
 *
 * Override this template by copying it to yourtheme/propertyhive/content-single-property.php
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $property;
?>

<?php
     if ( post_password_required() ) 
     {
        echo get_the_password_form();
        return;
     }
?>

<div id="property-<?php the_ID(); ?>" <?php post_class(); ?>>

    <?php
        /**
         * propertyhive_before_single_property_summary hook
         *
         * @hooked propertyhive_template_not_on_market - 5
         * @hooked propertyhive_show_property_images - 10
         */
        do_action( 'propertyhive_before_single_property_summary' );
    ?>

    <div class="summary entry-summary">

        <?php
            /**
             * propertyhive_single_property_summary hook
             *
             * @hooked propertyhive_template_single_title - 5
             * @hooked propertyhive_template_single_floor_area - 7
             * @hooked propertyhive_template_single_price - 10
             * @hooked propertyhive_template_single_meta - 20
             * @hooked propertyhive_template_single_sharing - 30
             */
            do_action( 'propertyhive_single_property_summary' );
        ?>

    </div><!-- .summary -->

    <?php
        /**
         * propertyhive_after_single_property_summary hook
         *
         * @hooked propertyhive_template_single_actions - 10
         * @hooked propertyhive_template_single_features - 20
         * @hooked propertyhive_template_single_summary - 30
         * @hooked propertyhive_template_single_description - 40
         */
       do_action( 'propertyhive_after_single_property_summary' );
    ?>

</div><!-- #property-<?php the_ID(); ?> -->