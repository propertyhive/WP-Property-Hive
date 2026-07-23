<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template set option lists.
 */
class PH_Template_Set_Options {

	/**
	 * Editor compatibility modes.
	 *
	 * @return array
	 */
	public static function get_editor_modes() {
		return array(
			PH_Template_Set::EDITOR_MODE_LEGACY => __( 'Legacy preview controls', 'propertyhive' ),
			PH_Template_Set::EDITOR_MODE_VISUAL => __( 'Visual editor', 'propertyhive' ),
		);
	}

	/**
	 * Gallery layout choices.
	 *
	 * @return array
	 */
	public static function get_gallery_layouts() {
		return array(
			'showcase'  => __( 'Showcase', 'propertyhive' ),
			'cinema'    => __( 'Cinema', 'propertyhive' ),
			'mosaic'    => __( 'Mosaic', 'propertyhive' ),
			'editorial' => __( 'Editorial', 'propertyhive' ),
			'strip'     => __( 'Filmstrip', 'propertyhive' ),
		);
	}

	/**
	 * Button style choices.
	 *
	 * @return array
	 */
	public static function get_button_styles() {
		return array(
			'filled'  => __( 'Filled', 'propertyhive' ),
			'outline' => __( 'Outline', 'propertyhive' ),
			'soft'    => __( 'Soft', 'propertyhive' ),
		);
	}

	/**
	 * Search listing layout choices.
	 *
	 * @return array
	 */
	public static function get_search_layouts() {
		return array(
			'list' => __( 'List', 'propertyhive' ),
			'grid' => __( 'Grid', 'propertyhive' ),
			'map'  => __( 'Map', 'propertyhive' ),
		);
	}

	/**
	 * Search result card size choices.
	 *
	 * @return array
	 */
	public static function get_search_card_sizes() {
		return array(
			'compact'  => __( 'Compact cards', 'propertyhive' ),
			'standard' => __( 'Standard cards', 'propertyhive' ),
			'large'    => __( 'Large cards', 'propertyhive' ),
		);
	}

	/**
	 * Grid column choices for search results.
	 *
	 * @return array
	 */
	public static function get_search_grid_column_options() {
		return array(
			2 => __( '2 per row', 'propertyhive' ),
			3 => __( '3 per row', 'propertyhive' ),
			4 => __( '4 per row', 'propertyhive' ),
		);
	}

	/**
	 * Image style choices.
	 *
	 * @return array
	 */
	public static function get_image_styles() {
		return array(
			'square'  => __( 'Square', 'propertyhive' ),
			'soft'    => __( 'Soft corners', 'propertyhive' ),
			'rounded' => __( 'Rounded corners', 'propertyhive' ),
		);
	}

	/**
	 * Contact-card style choices.
	 *
	 * @return array
	 */
	public static function get_contact_card_styles() {
		return array(
			'classic'   => __( 'Classic portal', 'propertyhive' ),
			'signature' => __( 'Agency signature', 'propertyhive' ),
			'concierge' => __( 'Private client', 'propertyhive' ),
			'editorial' => __( 'Editorial rail', 'propertyhive' ),
		);
	}

	/**
	 * Recommended-property count choices.
	 *
	 * @return array
	 */
	public static function get_recommended_property_counts() {
		return array(
			2 => __( '2 homes', 'propertyhive' ),
			3 => __( '3 homes', 'propertyhive' ),
			4 => __( '4 homes', 'propertyhive' ),
			6 => __( '6 homes', 'propertyhive' ),
		);
	}

	/**
	 * Recommended-property layout choices.
	 *
	 * @return array
	 */
	public static function get_recommended_property_layouts() {
		return array(
			'grid'    => __( 'Grid cards', 'propertyhive' ),
			'feature' => __( 'Featured lead', 'propertyhive' ),
			'list'    => __( 'List rows', 'propertyhive' ),
		);
	}

	/**
	 * Recommended-property image size choices.
	 *
	 * @return array
	 */
	public static function get_recommended_property_image_sizes() {
		return array(
			'compact'  => __( 'Compact images', 'propertyhive' ),
			'standard' => __( 'Standard images', 'propertyhive' ),
			'large'    => __( 'Large images', 'propertyhive' ),
		);
	}
}
