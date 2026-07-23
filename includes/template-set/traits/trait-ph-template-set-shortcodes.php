<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template set shortcodes.
 */
trait PH_Template_Set_Shortcodes {

	/**
	 * Featured/homepage module shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function featured_template_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'title'       => __( 'Featured properties', 'propertyhive' ),
				'intro'       => '',
				'button_text' => __( 'View all properties', 'propertyhive' ),
				'button_url'  => get_post_type_archive_link( 'property' ),
				'per_page'    => 3,
				'columns'     => 3,
				'department'  => '',
				'show_search' => '',
				'source'      => 'featured',
			),
			$atts,
			'propertyhive_featured_template'
		);

		$source = in_array( $atts['source'], array( 'featured', 'recent', 'properties' ), true ) ? $atts['source'] : 'featured';
		$template = self::get_module_template();

		if ( '' === $template ) {
			$template = 'featured-properties-homepage-module';
		}

		self::$rendering_module = true;

		try {
			$search_html = '';

			if ( 'yes' === $atts['show_search'] ) {
				$search_html = PH_Shortcodes::property_search_form( array( 'id' => 'template-module', 'default_department' => $atts['department'] ) );
			}

			ob_start();

			if ( self::is_demo_preview() && self::is_module_preview() ) {
				self::render_demo_property_cards( array_slice( self::get_demo_property_cards(), 0, 3 ), 'ph-template-demo-module-results' );
			} else {
				$property_atts = array(
					'per_page'          => absint( $atts['per_page'] ),
					'posts_per_page'    => absint( $atts['per_page'] ),
					'columns'           => absint( $atts['columns'] ),
					'department'        => sanitize_text_field( $atts['department'] ),
					'no_results_output' => '',
				);

				if ( 'recent' === $source ) {
					echo PH_Shortcodes::recent_properties( $property_atts );
				} elseif ( 'properties' === $source ) {
					echo PH_Shortcodes::properties( $property_atts );
				} else {
					echo PH_Shortcodes::featured_properties( $property_atts );
				}
			}

			$properties_html = ob_get_clean();

			ob_start();

			PH_Template_Set_Template_Loader::render(
				'module',
				$template,
				'featured-properties',
				array(
					'atts'            => $atts,
					'source'          => $source,
					'search_html'     => $search_html,
					'properties_html' => $properties_html,
				)
			);

			return ob_get_clean();
		} finally {
			self::$rendering_module = false;
		}
	}
}
