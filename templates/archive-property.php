<?php
/**
 * The Template for displaying property archives, also referred to as 'Search Results'
 *
 * Override this template by copying it to yourtheme/propertyhive/archive-property.php
 *
 * @author      BIOSTALL
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header(); global $wpdb; ?>

    <?php
        /**
         * propertyhive_before_main_content hook
         *
         * @hooked propertyhive_output_content_wrapper - 10 (outputs opening divs for the content)
         */
        do_action( 'propertyhive_before_main_content' );
    ?>

        <?php if ( apply_filters( 'propertyhive_show_page_title', true ) ) : ?>

            <h1 class="page-title"><?php propertyhive_page_title(); ?></h1>

        <?php endif; ?>
        
        <?php
            /**
             * propertyhive_before_search_results_loop hook
             * @hooked propertyhive_search_form - 10
             * @hooked propertyhive_result_count - 20
             * @hooked propertyhive_catalog_ordering - 30
             */
            do_action( 'propertyhive_before_search_results_loop' );
        ?>
        
        <?php if ( have_posts() ) : ?>

            <?php propertyhive_property_loop_start(); ?>

                <?php while ( have_posts() ) : the_post(); ?>

                    <?php ph_get_template_part( 'content', 'property' ); ?>

                <?php endwhile; // end of the loop. ?>

            <?php propertyhive_property_loop_end(); ?>

        <?php else: ?>

            <?php ph_get_template( 'search/no-properties-found.php' ); ?>

        <?php endif; ?>

        <?php
            /**
             * propertyhive_after_search_results_loop hook
             *
             * @hooked propertyhive_pagination - 10
             */
            do_action( 'propertyhive_after_search_results_loop' );
        ?>

    <?php
        /**
         * propertyhive_after_main_content hook
         *
         * @hooked propertyhive_output_content_wrapper_end - 10 (outputs closing divs for the content)
         */
        do_action( 'propertyhive_after_main_content' );
    ?>

    <?php
        /**
         * propertyhive_sidebar hook
         *
         * @hooked propertyhive_get_sidebar - 10
         */
        do_action( 'propertyhive_sidebar' );
    ?>

<?php get_footer(); ?>