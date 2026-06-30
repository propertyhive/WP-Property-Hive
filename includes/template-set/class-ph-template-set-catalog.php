<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template set catalogue definitions.
 */
class PH_Template_Set_Catalog {

	/**
	 * Detail template profiles.
	 *
	 * @return array
	 */
	public static function get_detail_templates() {
		return apply_filters( 'propertyhive_template_set_detail_templates', array(
			'standard-sales-detail'           => __( 'Standard Sales Detail', 'propertyhive' ),
		) );
	}

	/**
	 * Search template profiles.
	 *
	 * @return array
	 */
	public static function get_search_templates() {
		return apply_filters( 'propertyhive_template_set_search_templates', array(
			'portal-style-search-results'      => __( 'Portal-Style Search Results', 'propertyhive' ),
		) );
	}

	/**
	 * Homepage/module template profiles.
	 *
	 * @return array
	 */
	public static function get_module_templates() {
		return apply_filters( 'propertyhive_template_set_module_templates', array() );
	}

	/**
	 * Get the default detail template from the filtered template list.
	 *
	 * @return string
	 */
	public static function get_default_detail_template() {
		return self::get_default_template_from_list( self::get_detail_templates(), 'standard-sales-detail' );
	}

	/**
	 * Get the default search template from the filtered template list.
	 *
	 * @return string
	 */
	public static function get_default_search_template() {
		return self::get_default_template_from_list( self::get_search_templates(), 'portal-style-search-results' );
	}

	/**
	 * Get the default module template from the filtered template list.
	 *
	 * @return string
	 */
	public static function get_default_module_template() {
		return self::get_default_template_from_list( self::get_module_templates(), '' );
	}

	/**
	 * Full template catalogue used by the front-end preview switcher.
	 *
	 * @return array
	 */
	public static function get_template_catalog() {
		$catalog          = array();
		$allowed_slugs    = array();
		$typed_catalog    = array();
		$detail_templates = self::get_detail_templates();
		$search_templates = self::get_search_templates();
		$module_templates = self::get_module_templates();

		foreach ( $detail_templates as $slug => $label ) {
			$catalog[ $slug ] = array(
				'type'  => 'detail',
				'group' => __( 'Property detail templates', 'propertyhive' ),
				'label' => $label,
			);
			$typed_catalog[ $slug ] = $catalog[ $slug ];
			$allowed_slugs[ $slug ] = true;
		}

		foreach ( $search_templates as $slug => $label ) {
			$catalog[ $slug ] = array(
				'type'  => 'search',
				'group' => __( 'Search result templates', 'propertyhive' ),
				'label' => $label,
			);
			$typed_catalog[ $slug ] = $catalog[ $slug ];
			$allowed_slugs[ $slug ] = true;
		}

		foreach ( $module_templates as $slug => $label ) {
			$catalog[ $slug ] = array(
				'type'  => 'module',
				'group' => __( 'Homepage module templates', 'propertyhive' ),
				'label' => $label,
			);
			$typed_catalog[ $slug ] = $catalog[ $slug ];
			$allowed_slugs[ $slug ] = true;
		}

		$catalog = apply_filters( 'propertyhive_template_set_template_catalog', $catalog, $detail_templates, $search_templates, $module_templates );

		foreach ( $catalog as $slug => $template ) {
			if ( ! isset( $allowed_slugs[ $slug ] ) ) {
				unset( $catalog[ $slug ] );
				continue;
			}

			$template = is_array( $template ) ? $template : array();
			$template = wp_parse_args( $template, $typed_catalog[ $slug ] );
			$template['type'] = $typed_catalog[ $slug ]['type'];

			$catalog[ $slug ] = $template;
		}

		return $catalog;
	}

	/**
	 * Short label for admin bar template groups.
	 *
	 * @param string $type Template type.
	 * @return string
	 */
	public static function get_short_template_group_label( $type ) {
		if ( 'search' === $type ) {
			return __( 'Search', 'propertyhive' );
		}

		if ( 'module' === $type ) {
			return __( 'Module', 'propertyhive' );
		}

		return __( 'Detail', 'propertyhive' );
	}

	/**
	 * Prefer the built-in slug when it still exists, otherwise use the first filtered template.
	 *
	 * @param array  $templates Template list.
	 * @param string $preferred Preferred built-in slug.
	 * @return string
	 */
	private static function get_default_template_from_list( $templates, $preferred ) {
		$templates = is_array( $templates ) ? $templates : array();
		$preferred = sanitize_title( $preferred );

		if ( '' !== $preferred && isset( $templates[ $preferred ] ) ) {
			return $preferred;
		}

		$slugs = array_keys( $templates );
		$slug  = reset( $slugs );

		return $slug ? sanitize_title( $slug ) : '';
	}
}
