<?php
/**
 * The Template for displaying a single property.
 *
 * Override this template by copying it to yourtheme/propertyhive/single-property.php
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header( 'propertyhive' ); ?>

    <?php
        /**
         * propertyhive_before_main_content hook
         *
         * @hooked propertyhive_output_content_wrapper - 10 (outputs opening divs for the content)
         */
        do_action( 'propertyhive_before_main_content' );
    ?>

    <?php while ( have_posts() ) : the_post(); ?>

        <?php ph_get_template_part( 'content', 'single-property' ); ?>

    <?php endwhile; // end of the loop. ?>
    
    <?php
        /**
         * propertyhive_after_main_content hook
         *
         * @hooked propertyhive_output_content_wrapper_end - 10 (outputs closing divs for the content)
         */
        do_action( 'propertyhive_after_main_content' );
    ?>

<?php get_footer( 'propertyhive' ); ?>