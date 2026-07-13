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
			// Portal is the sole mainstream sales detail: conversion layout + full media/chrome controls.
			// standard-sales-detail remains only as an internal partial fallback (not selectable).
			'conversion-first-sales-detail'   => __( 'Portal Split', 'propertyhive' ),
			'immersive-cinema-detail'         => __( 'Immersive Cinema', 'propertyhive' ),
			'premium-editorial-detail'        => __( 'Private Office', 'propertyhive' ),
		) );
	}

	/**
	 * Get stable public aliases for bundled detail templates.
	 *
	 * The original internal slugs remain the storage and theme-override contract.
	 * These aliases match the names shown in the template catalogue and may be
	 * used in preview URLs and CSS without invalidating existing settings.
	 *
	 * @return array
	 */
	public static function get_detail_template_aliases() {
		return array(
			'portal-split'     => 'conversion-first-sales-detail',
			'immersive-cinema' => 'immersive-cinema-detail',
			'private-office'   => 'premium-editorial-detail',
		);
	}

	/**
	 * Get the public alias for a detail template.
	 *
	 * @param string $slug Internal or public detail template slug.
	 * @return string
	 */
	public static function get_detail_template_public_slug( $slug ) {
		$slug    = self::normalize_detail_template_slug( $slug );
		$aliases = array_flip( self::get_detail_template_aliases() );

		return isset( $aliases[ $slug ] ) ? $aliases[ $slug ] : $slug;
	}

	/**
	 * Get the control manifest for a detail template.
	 *
	 * @param string $slug Detail template slug.
	 * @return array
	 */
	public static function get_detail_template_manifest( $slug ) {
		$slug      = self::normalize_detail_template_slug( $slug );
		$templates = self::get_detail_templates();
		$shared    = array_keys( self::get_detail_shared_controls() );
		$manifest  = array(
			'label'       => isset( $templates[ $slug ] ) ? $templates[ $slug ] : '',
			'public_slug' => self::get_detail_template_public_slug( $slug ),
			'supports'    => array(),
			'locked'      => array(),
			'defaults'    => array(),
			'controls'    => array(),
		);

		switch ( $slug ) {
			case 'standard-sales-detail':
				// Retired from the picker; keep a full-control manifest for any
				// lingering saved settings / filters that still resolve this slug.
				$manifest['supports'] = $shared;
				break;

			case 'conversion-first-sales-detail':
				// Portal layout + Standard-level controls (gallery layout free).
				$manifest['supports'] = $shared;
				$manifest['defaults'] = array(
					'template_set_gallery_layout' => 'mosaic',
					'template_set_button_style'   => 'filled',
				);
				$manifest['controls'] = array(
					'template_set_portal_show_costs' => array(
						'type'                   => 'checkbox',
						'label'                  => __( 'Show purchase costs', 'propertyhive' ),
						'options'                => self::get_checkbox_options(),
						'default'                => 'yes',
						'group'                  => 'modules',
						'requires_any_shortcode' => array( 'stamp_duty_calculator', 'mortgage_calculator' ),
					),
				);
				break;

			case 'immersive-cinema-detail':
				// Cinema owns gallery layout + floating-card chrome; button/contact styles
				// are design-fixed and would be dead controls in the sidebar.
				$manifest['supports'] = array_values(
					array_diff(
						$shared,
						array(
							'template_set_gallery_layout',
							'template_set_button_style',
							'template_set_contact_card_style',
						)
					)
				);
				$manifest['locked'] = array(
					'template_set_gallery_layout'     => 'cinema',
					'template_set_button_style'       => 'filled',
					'template_set_contact_card_style' => 'classic',
				);
				$manifest['controls'] = array(
					'template_set_cinema_card_position' => array(
						'type'    => 'select',
						'label'   => __( 'Hero card position', 'propertyhive' ),
						'options' => array(
							'right' => __( 'Right', 'propertyhive' ),
							'left'  => __( 'Left', 'propertyhive' ),
						),
						'default' => 'right',
						'group'   => 'media',
					),
				);
				break;

			case 'premium-editorial-detail':
				// Private Office owns the editorial plate + closing letter chrome;
				// free gallery/button/contact styles would be dead or misleading.
				$manifest['supports'] = array_values(
					array_diff(
						$shared,
						array(
							'template_set_gallery_layout',
							'template_set_button_style',
							'template_set_contact_card_style',
						)
					)
				);
				$manifest['locked'] = array(
					'template_set_gallery_layout'     => 'editorial',
					'template_set_button_style'       => 'outline',
					'template_set_contact_card_style' => 'editorial',
				);
				$manifest['controls'] = array(
					'template_set_editorial_show_brief' => array(
						'type'    => 'checkbox',
						'label'   => __( 'Show masthead brief', 'propertyhive' ),
						'options' => self::get_checkbox_options(),
						'default' => 'yes',
						'group'   => 'media',
					),
				);
				break;
		}

		$manifest = apply_filters( 'propertyhive_template_set_detail_template_manifest', $manifest, $slug );

		return is_array( $manifest ) ? wp_parse_args( $manifest, array(
			'label'       => isset( $templates[ $slug ] ) ? $templates[ $slug ] : '',
			'public_slug' => self::get_detail_template_public_slug( $slug ),
			'supports'    => array(),
			'locked'      => array(),
			'defaults'    => array(),
			'controls'    => array(),
		) ) : array();
	}

	/**
	 * Shared detail controls available to template manifests.
	 *
	 * @return array
	 */
	public static function get_detail_shared_controls() {
		return array(
			'template_set_gallery_layout' => array( 'type' => 'select', 'label' => __( 'Gallery layout', 'propertyhive' ), 'options' => PH_Template_Set_Options::get_gallery_layouts(), 'default' => 'showcase', 'group' => 'media' ),
			'template_set_show_floorplans' => array( 'type' => 'checkbox', 'label' => __( 'Show floorplans', 'propertyhive' ), 'options' => self::get_checkbox_options(), 'default' => 'yes', 'group' => 'media' ),
			'template_set_show_virtual_tours' => array( 'type' => 'checkbox', 'label' => __( 'Show virtual tours', 'propertyhive' ), 'options' => self::get_checkbox_options(), 'default' => '', 'group' => 'media' ),
			'template_set_button_style' => array( 'type' => 'select', 'label' => __( 'Button style', 'propertyhive' ), 'options' => PH_Template_Set_Options::get_button_styles(), 'default' => 'filled', 'group' => 'enquiry' ),
			'template_set_contact_card_style' => array( 'type' => 'select', 'label' => __( 'Contact card style', 'propertyhive' ), 'options' => PH_Template_Set_Options::get_contact_card_styles(), 'default' => 'classic', 'group' => 'enquiry' ),
			'template_set_show_mobile_cta' => array( 'type' => 'checkbox', 'label' => __( 'Show mobile enquiry bar', 'propertyhive' ), 'options' => self::get_checkbox_options(), 'default' => 'yes', 'group' => 'enquiry' ),
			'template_set_show_recommended' => array( 'type' => 'checkbox', 'label' => __( 'Show related properties', 'propertyhive' ), 'options' => self::get_checkbox_options(), 'default' => 'yes', 'group' => 'recommended' ),
			'template_set_recommended_count' => array( 'type' => 'select', 'label' => __( 'Number of properties', 'propertyhive' ), 'options' => PH_Template_Set_Options::get_recommended_property_counts(), 'default' => 3, 'group' => 'recommended' ),
			'template_set_recommended_layout' => array( 'type' => 'select', 'label' => __( 'Card layout', 'propertyhive' ), 'options' => PH_Template_Set_Options::get_recommended_property_layouts(), 'default' => 'grid', 'group' => 'recommended' ),
			'template_set_recommended_image_size' => array( 'type' => 'select', 'label' => __( 'Image size', 'propertyhive' ), 'options' => PH_Template_Set_Options::get_recommended_property_image_sizes(), 'default' => 'standard', 'group' => 'recommended' ),
		);
	}

	/**
	 * Get all controls valid for a detail template, including locked controls.
	 *
	 * @param string $slug Detail template slug.
	 * @return array
	 */
	public static function get_detail_template_controls( $slug ) {
		$manifest = self::get_detail_template_manifest( $slug );
		$shared   = self::get_detail_shared_controls();
		$controls = array();

		foreach ( array_unique( array_merge( (array) $manifest['supports'], array_keys( (array) $manifest['locked'] ) ) ) as $key ) {
			if ( isset( $shared[ $key ] ) ) {
				$controls[ $key ] = $shared[ $key ];
			}
		}

		return array_merge( $controls, (array) $manifest['controls'] );
	}

	/**
	 * Get controls currently available for a detail template.
	 *
	 * This is intentionally separate from get_detail_template_controls(), which
	 * remains the stable schema used to validate and preserve stored settings.
	 *
	 * @param string $slug Detail template slug.
	 * @return array
	 */
	public static function get_available_detail_template_controls( $slug ) {
		$controls = self::get_detail_template_controls( $slug );

		foreach ( $controls as $key => $control ) {
			if ( ! self::is_detail_control_available( $control ) ) {
				unset( $controls[ $key ] );
			}
		}

		return $controls;
	}

	/**
	 * Check whether a detail control's runtime requirements are satisfied.
	 *
	 * @param array $control Control definition.
	 * @return bool
	 */
	private static function is_detail_control_available( $control ) {
		if ( empty( $control['requires_any_shortcode'] ) ) {
			return true;
		}

		foreach ( (array) $control['requires_any_shortcode'] as $shortcode ) {
			if ( is_string( $shortcode ) && '' !== $shortcode && shortcode_exists( $shortcode ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Stored values used by checkbox controls.
	 *
	 * @return array
	 */
	private static function get_checkbox_options() {
		return array( 'yes' => __( 'Yes', 'propertyhive' ), '' => __( 'No', 'propertyhive' ) );
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
		return self::get_default_template_from_list( self::get_detail_templates(), 'conversion-first-sales-detail' );
	}

	/**
	 * Map retired detail template slugs onto their replacement.
	 *
	 * Standard Sales was folded into Portal Split (same conversion shell with
	 * the former Standard control surface). Keep the old slug working when it
	 * appears in stored settings or query args.
	 *
	 * @param string $slug Detail template slug.
	 * @return string
	 */
	public static function normalize_detail_template_slug( $slug ) {
		$slug = sanitize_title( $slug );
		$aliases = self::get_detail_template_aliases();

		if ( isset( $aliases[ $slug ] ) ) {
			return $aliases[ $slug ];
		}

		if ( 'standard-sales-detail' === $slug ) {
			return 'conversion-first-sales-detail';
		}

		return $slug;
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
