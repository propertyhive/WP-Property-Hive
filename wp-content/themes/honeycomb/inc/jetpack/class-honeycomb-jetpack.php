<?php
/**
 * Honeycomb Jetpack Class
 *
 * @package  honecomb
 * @author   Property Hive
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Honeycomb_Jetpack' ) ) :

	/**
	 * The Honeycomb Jetpack integration class
	 */
	class Honeycomb_Jetpack {

		/**
		 * Setup class.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			add_action( 'after_setup_theme', 	array( $this, 'jetpack_setup' ) );
			add_action( 'wp_enqueue_scripts', 	array( $this, 'jetpack_scripts' ), 	10 );
		}

		/**
		 * Add theme support for Infinite Scroll.
		 * See: http://jetpack.me/support/infinite-scroll/
		 */
		public function jetpack_setup() {
			add_theme_support( 'infinite-scroll', apply_filters( 'honeycomb_jetpack_infinite_scroll_args', array(
				'container'      => 'main',
				'footer'         => 'page',
				'type'           => 'click',
				'posts_per_page' => '12',
				'render'         => array( $this, 'jetpack_infinite_scroll_loop' ),
				'footer_widgets' => array(
										'footer-1',
										'footer-2',
										'footer-3',
										'footer-4',
									),
			) ) );
		}

		/**
		 * A loop used to display content appended using Jetpack inifinte scroll
		 * @return void
		 */
		public function jetpack_infinite_scroll_loop() {
			if ( honeycomb_is_property_archive() ) {
				propertyhive_property_loop_start();
			}

			while ( have_posts() ) : the_post();
				if ( honeycomb_is_property_archive() ) {
					ph_get_template_part( 'content', 'property' );
				} else {
					get_template_part( 'content', get_post_format() );
				}
			endwhile; // end of the loop.

			if ( honeycomb_is_property_archive() ) {
				propertyhive_property_loop_end();
			}
		}

		/**
		 * Enqueue jetpack styles.
		 *
		 * @since  1.0.0
		 */
		public function jetpack_scripts() {
			global $honeycomb_version;

			wp_enqueue_style( 'honeycomb-jetpack-style', get_template_directory_uri() . '/assets/sass/jetpack/jetpack.css', '', $honeycomb_version );
			wp_style_add_data( 'honeycomb-jetpack-style', 'rtl', 'replace' );
		}
	}

endif;

return new Honeycomb_Jetpack();
