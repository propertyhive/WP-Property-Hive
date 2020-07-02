<?php
/**
 * Honeycomb Property Hive Class
 *
 * @package  honeycomb
 * @author   Property Hive
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Honeycomb_Propertyhive' ) ) :

	/**
	 * The Honeycomb Property Hive Integration class
	 */
	class Honeycomb_Propertyhive {

		/**
		 * Setup class.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_filter( 'loop_search_results_columns', 				array( $this, 'loop_columns' ) );
			add_filter( 'body_class', 								array( $this, 'propertyhive_body_class' ) );
			add_action( 'wp_enqueue_scripts', 						array( $this, 'propertyhive_scripts' ),	20 );
			add_filter( 'loop_search_results_per_page', 			array( $this, 'properties_per_page' ) );

			// Integrations.
			add_action( 'wp_enqueue_scripts',                       array( $this, 'add_customizer_css' ), 140 );

			add_action( 'after_switch_theme',                       array( $this, 'set_honeycomb_style_theme_mods' ) );
			add_action( 'customize_save_after',                     array( $this, 'set_honeycomb_style_theme_mods' ) );

			//add_filter( 'propertyhive_single_property_actions', 	array( $this, 'add_button_class_to_actions' ) );
		}

		public function add_button_class_to_actions( $actions )
		{
			$new_actions = array();
			foreach ( $actions as $action )
			{
				if ( !isset($action['class']) ) { $action['class'] = ''; }
				$action['class'] .= ' button';

				$new_actions[] = $action;
			}

			return $new_actions;
		}

		/**
		 * Add CSS in <head> for styles handled by the theme customizer
		 * If the Customizer is active pull in the raw css. Otherwise pull in the prepared theme_mods if they exist.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function add_customizer_css() {
			$honeycomb_propertyhive_extension_styles = get_theme_mod( 'honeycomb_propertyhive_extension_styles' );

			if ( is_customize_preview() || ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) || ( false === $honeycomb_propertyhive_extension_styles ) ) {
				wp_add_inline_style( 'honeycomb-propertyhive-style', $this->get_propertyhive_extension_css() );
			} else {
				wp_add_inline_style( 'honeycomb-propertyhive-style', $honeycomb_propertyhive_extension_styles );
			}
		}

		/**
		 * Assign styles to individual theme mod.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function set_honeycomb_style_theme_mods() {
			set_theme_mod( 'honeycomb_propertyhive_extension_styles', $this->get_propertyhive_extension_css() );
		}

		/**
		 * Default loop columns on property archives
		 *
		 * @return integer properties per row
		 * @since  1.0.0
		 */
		public function loop_columns() {
			return apply_filters( 'honeycomb_loop_columns', 3 ); // 3 properties per row
		}

		/**
		 * Add 'propertyhive-active' class to the body tag
		 *
		 * @param  array $classes css classes applied to the body tag.
		 * @return array $classes modified to include 'propertyhive-active' class
		 */
		public function propertyhive_body_class( $classes ) {
			if ( honeycomb_is_propertyhive_activated() ) {
				$classes[] = 'propertyhive-active';
			}

			// Set template name if template selected on search results page
			if ( is_post_type_archive('property') )
			{
				$search_results_page_id = ph_get_page_id( 'search_results' );
				$template = get_page_template_slug( $search_results_page_id );
				
				if ( $template == 'template-fullwidth.php' )
				{
					$classes[] = 'page-template-template-fullwidth-php';
				}
			}

			return $classes;
		}

		/**
		 * Property Hive specific scripts & stylesheets
		 *
		 * @since 1.0.0
		 */
		public function propertyhive_scripts() {
			global $honeycomb_version;

			wp_enqueue_style( 'honeycomb-propertyhive-style', get_template_directory_uri() . '/assets/sass/propertyhive/propertyhive.css', $honeycomb_version );
			wp_style_add_data( 'honeycomb-propertyhive-style', 'rtl', 'replace' );
		}

		/**
		 * Properties per page
		 *
		 * @return integer number of properties
		 * @since  1.0.0
		 */
		public function properties_per_page() {
			return intval( apply_filters( 'honeycomb_properties_per_page', 12 ) );
		}

		/**
		 * Get extension css.
		 *
		 * @see get_honeycomb_theme_mods()
		 * @return array $styles the css
		 */
		public function get_propertyhive_extension_css() {
			$honeycomb_customizer = new Honeycomb_Customizer();
			$honeycomb_theme_mods = $honeycomb_customizer->get_honeycomb_theme_mods();

			$propertyhive_extension_style = '';

			return apply_filters( 'honeycomb_customizer_propertyhive_extension_css', $propertyhive_extension_style );
		}
	}

endif;

return new Honeycomb_Propertyhive();
