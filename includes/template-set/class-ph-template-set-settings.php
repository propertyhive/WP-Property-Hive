<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template set settings and sanitisation.
 */
class PH_Template_Set_Settings {

	/**
	 * Get stored template set settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		$stored_settings = get_option( 'propertyhive_template_assistant', null );
		$settings        = is_array( $stored_settings ) ? $stored_settings : array();

		$default_editor_mode = self::get_default_editor_mode( $settings );

		$settings = wp_parse_args(
			$settings,
			array(
				PH_Template_Set::OPTION_ENABLED       => '',
				'template_set_editor_mode'            => $default_editor_mode,
				'template_set_detail_template'        => 'conversion-first-sales-detail',
				'template_set_search_template'        => 'portal-style-search-results',
				'template_set_search_layout'          => '',
				'template_set_search_card_size'       => 'standard',
				'template_set_search_grid_columns'    => 3,
				'template_set_gallery_layout'         => 'mosaic',
				'template_set_brand_colour'           => '#155e63',
				'template_set_accent_colour'          => '#b7791f',
				'template_set_button_style'           => 'filled',
				'template_set_image_style'            => 'soft',
				'template_set_contact_card_style'     => 'classic',
				'template_set_show_branch'            => 'yes',
				'template_set_show_badges'            => 'yes',
				'template_set_show_mobile_cta'        => 'yes',
				'template_set_show_floorplans'        => 'yes',
				'template_set_show_virtual_tours'     => '',
				'template_set_show_recommended'       => 'yes',
				'template_set_recommended_count'      => 3,
				'template_set_recommended_layout'     => 'grid',
				'template_set_recommended_image_size' => 'standard',
				'template_overrides'                  => array(),
			)
		);

		if ( ! isset( PH_Template_Set_Options::get_contact_card_styles()[ $settings['template_set_contact_card_style'] ] ) ) {
			$settings['template_set_contact_card_style'] = 'classic';
		}

		$settings['template_set_detail_template'] = PH_Template_Set_Catalog::normalize_detail_template_slug( $settings['template_set_detail_template'] );

		if ( ! isset( PH_Template_Set_Catalog::get_detail_templates()[ $settings['template_set_detail_template'] ] ) ) {
			$settings['template_set_detail_template'] = PH_Template_Set_Catalog::get_default_detail_template();
		}

		if ( ! isset( PH_Template_Set_Catalog::get_search_templates()[ $settings['template_set_search_template'] ] ) ) {
			$settings['template_set_search_template'] = PH_Template_Set_Catalog::get_default_search_template();
		}

		// Fold any retired Standard Sales overrides into Portal Split.
		if ( isset( $settings['template_overrides']['standard-sales-detail'] ) && is_array( $settings['template_overrides']['standard-sales-detail'] ) ) {
			$portal_overrides = isset( $settings['template_overrides']['conversion-first-sales-detail'] ) && is_array( $settings['template_overrides']['conversion-first-sales-detail'] )
				? $settings['template_overrides']['conversion-first-sales-detail']
				: array();
			$settings['template_overrides']['conversion-first-sales-detail'] = array_merge(
				$settings['template_overrides']['standard-sales-detail'],
				$portal_overrides
			);
			unset( $settings['template_overrides']['standard-sales-detail'] );
		}

		return $settings;
	}

	/**
	 * Resolve a detail setting for a specific template.
	 *
	 * Resolution order is locked value, template override, manifest default,
	 * global setting, then the control's global default.
	 *
	 * @param string $key Setting key.
	 * @param string $template_slug Detail template slug.
	 * @param array  $settings Optional settings, primarily for deterministic callers/tests.
	 * @return mixed
	 */
	public static function get_for_template( $key, $template_slug, $settings = null ) {
		$template_slug = PH_Template_Set_Catalog::normalize_detail_template_slug( $template_slug );
		$manifest      = PH_Template_Set_Catalog::get_detail_template_manifest( $template_slug );
		$controls      = PH_Template_Set_Catalog::get_detail_template_controls( $template_slug );
		$settings      = is_array( $settings ) ? $settings : self::get_settings();

		if ( array_key_exists( $key, (array) $manifest['locked'] ) ) {
			return $manifest['locked'][ $key ];
		}

		if ( isset( $settings['template_overrides'][ $template_slug ] ) && array_key_exists( $key, (array) $settings['template_overrides'][ $template_slug ] ) ) {
			return $settings['template_overrides'][ $template_slug ][ $key ];
		}

		if ( array_key_exists( $key, (array) $manifest['defaults'] ) ) {
			return $manifest['defaults'][ $key ];
		}

		if ( array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}

		return isset( $controls[ $key ] ) && array_key_exists( 'default', $controls[ $key ] ) ? $controls[ $key ]['default'] : null;
	}

	/**
	 * Get the safe default editor mode for this site.
	 *
	 * Existing sites with legacy Template Assistant/front-end settings stay on
	 * the old path until they opt in. Fresh installs default to the visual
	 * editor.
	 *
	 * @param array $settings Stored template assistant settings.
	 * @return string
	 */
	public static function get_default_editor_mode( $settings ) {
		if ( ! is_array( $settings ) || empty( $settings ) ) {
			return PH_Template_Set::EDITOR_MODE_VISUAL;
		}

		if ( isset( $settings['template_set_editor_mode'] ) && isset( PH_Template_Set_Options::get_editor_modes()[ $settings['template_set_editor_mode'] ] ) ) {
			return sanitize_title( $settings['template_set_editor_mode'] );
		}

		return self::has_legacy_frontend_settings( $settings ) ? PH_Template_Set::EDITOR_MODE_LEGACY : PH_Template_Set::EDITOR_MODE_VISUAL;
	}

	/**
	 * Does this site already have settings from the older front-end system?
	 *
	 * @param array $settings Stored template assistant settings.
	 * @return bool
	 */
	public static function has_legacy_frontend_settings( $settings ) {
		$legacy_keys = array(
			'search_result_default_order',
			'search_result_columns',
			'search_result_layout',
			'search_result_fields',
			'search_result_image_size',
			'search_result_css',
			'search_result_css_all_pages',
			'flags_active',
			'flags_active_single',
			'flag_position',
			'flag_bg_color',
			'flag_text_color',
		);

		foreach ( $legacy_keys as $key ) {
			if ( array_key_exists( $key, $settings ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Sanitise template set settings from admin or front-end editor input.
	 *
	 * @param array $raw_settings Raw request values.
	 * @param array $current_settings Existing stored settings.
	 * @param bool  $activate Whether the save should activate the template set.
	 * @return array
	 */
	public static function sanitize_template_set_settings( $raw_settings, $current_settings = array(), $activate = false ) {
		$raw_settings = is_array( $raw_settings ) ? wp_unslash( $raw_settings ) : array();
		$current      = wp_parse_args( is_array( $current_settings ) ? $current_settings : array(), self::get_settings() );

		$detail_templates = PH_Template_Set_Catalog::get_detail_templates();
		$search_templates = PH_Template_Set_Catalog::get_search_templates();
		$gallery_layouts  = PH_Template_Set_Options::get_gallery_layouts();
		$editor_modes     = PH_Template_Set_Options::get_editor_modes();

		$detail_template = isset( $raw_settings['template_set_detail_template'] ) ? sanitize_title( $raw_settings['template_set_detail_template'] ) : sanitize_title( $current['template_set_detail_template'] );
		$detail_template = PH_Template_Set_Catalog::normalize_detail_template_slug( $detail_template );
		if ( ! isset( $detail_templates[ $detail_template ] ) ) {
			$detail_template = PH_Template_Set_Catalog::get_default_detail_template();
		}

		$search_template = isset( $raw_settings['template_set_search_template'] ) ? sanitize_title( $raw_settings['template_set_search_template'] ) : sanitize_title( $current['template_set_search_template'] );
		if ( ! isset( $search_templates[ $search_template ] ) ) {
			$search_template = PH_Template_Set_Catalog::get_default_search_template();
		}

		$search_layout = isset( $raw_settings['template_set_search_layout'] ) ? sanitize_title( $raw_settings['template_set_search_layout'] ) : sanitize_title( $current['template_set_search_layout'] );
		if ( 'compact-list-search-results' === $search_template ) {
			$search_layout = 'list';
		} elseif ( ! isset( PH_Template_Set_Options::get_search_layouts()[ $search_layout ] ) ) {
			$search_layout = '';
		}

		$gallery_layout = isset( $raw_settings['template_set_gallery_layout'] ) ? sanitize_title( $raw_settings['template_set_gallery_layout'] ) : sanitize_title( $current['template_set_gallery_layout'] );
		if ( ! isset( $gallery_layouts[ $gallery_layout ] ) ) {
			$gallery_layout = 'showcase';
		}

		$editor_mode = isset( $raw_settings['template_set_editor_mode'] ) ? sanitize_title( $raw_settings['template_set_editor_mode'] ) : sanitize_title( $current['template_set_editor_mode'] );
		if ( $activate ) {
			$editor_mode = PH_Template_Set::EDITOR_MODE_VISUAL;
		}
		if ( ! isset( $editor_modes[ $editor_mode ] ) ) {
			$editor_mode = PH_Template_Set::EDITOR_MODE_LEGACY;
		}

		$brand_colour = isset( $raw_settings['template_set_brand_colour'] ) ? sanitize_hex_color( $raw_settings['template_set_brand_colour'] ) : sanitize_hex_color( $current['template_set_brand_colour'] );
		if ( empty( $brand_colour ) ) {
			$brand_colour = '#155e63';
		}

		$accent_colour = isset( $raw_settings['template_set_accent_colour'] ) ? sanitize_hex_color( $raw_settings['template_set_accent_colour'] ) : sanitize_hex_color( $current['template_set_accent_colour'] );
		if ( empty( $accent_colour ) ) {
			$accent_colour = '#b7791f';
		}

		$button_style = isset( $raw_settings['template_set_button_style'] ) ? sanitize_title( $raw_settings['template_set_button_style'] ) : sanitize_title( $current['template_set_button_style'] );
		if ( ! isset( PH_Template_Set_Options::get_button_styles()[ $button_style ] ) ) {
			$button_style = 'filled';
		}

		$search_card_size = isset( $raw_settings['template_set_search_card_size'] ) ? sanitize_title( $raw_settings['template_set_search_card_size'] ) : sanitize_title( $current['template_set_search_card_size'] );
		if ( ! isset( PH_Template_Set_Options::get_search_card_sizes()[ $search_card_size ] ) ) {
			$search_card_size = 'standard';
		}

		$search_grid_columns = isset( $raw_settings['template_set_search_grid_columns'] ) ? absint( $raw_settings['template_set_search_grid_columns'] ) : absint( $current['template_set_search_grid_columns'] );
		if ( ! isset( PH_Template_Set_Options::get_search_grid_column_options()[ $search_grid_columns ] ) ) {
			$search_grid_columns = 3;
		}

		$image_style = isset( $raw_settings['template_set_image_style'] ) ? sanitize_title( $raw_settings['template_set_image_style'] ) : sanitize_title( $current['template_set_image_style'] );
		if ( ! isset( PH_Template_Set_Options::get_image_styles()[ $image_style ] ) ) {
			$image_style = 'soft';
		}

		$contact_card_style = isset( $raw_settings['template_set_contact_card_style'] ) ? sanitize_title( $raw_settings['template_set_contact_card_style'] ) : sanitize_title( $current['template_set_contact_card_style'] );
		if ( ! isset( PH_Template_Set_Options::get_contact_card_styles()[ $contact_card_style ] ) ) {
			$contact_card_style = 'classic';
		}

		$recommended_count = isset( $raw_settings['template_set_recommended_count'] ) ? absint( $raw_settings['template_set_recommended_count'] ) : absint( $current['template_set_recommended_count'] );
		if ( ! isset( PH_Template_Set_Options::get_recommended_property_counts()[ $recommended_count ] ) ) {
			$recommended_count = 3;
		}

		$recommended_layout = isset( $raw_settings['template_set_recommended_layout'] ) ? sanitize_title( $raw_settings['template_set_recommended_layout'] ) : sanitize_title( $current['template_set_recommended_layout'] );
		if ( ! isset( PH_Template_Set_Options::get_recommended_property_layouts()[ $recommended_layout ] ) ) {
			$recommended_layout = 'grid';
		}

		$recommended_image_size = isset( $raw_settings['template_set_recommended_image_size'] ) ? sanitize_title( $raw_settings['template_set_recommended_image_size'] ) : sanitize_title( $current['template_set_recommended_image_size'] );
		if ( ! isset( PH_Template_Set_Options::get_recommended_property_image_sizes()[ $recommended_image_size ] ) ) {
			$recommended_image_size = 'standard';
		}

		$template_set_settings = array(
			PH_Template_Set::OPTION_ENABLED           => $activate || ! empty( $raw_settings[ PH_Template_Set::OPTION_ENABLED ] ) ? 'yes' : '',
			'template_set_editor_mode'                => $editor_mode,
			'template_set_detail_template'            => $detail_template,
			'template_set_search_template'            => $search_template,
			'template_set_search_layout'              => $search_layout,
			'template_set_gallery_layout'             => $gallery_layout,
			'template_set_brand_colour'               => $brand_colour,
			'template_set_accent_colour'              => $accent_colour,
			'template_set_button_style'               => $button_style,
			'template_set_search_card_size'           => $search_card_size,
			'template_set_search_grid_columns'        => $search_grid_columns,
			'template_set_image_style'                => $image_style,
			'template_set_contact_card_style'         => $contact_card_style,
			'template_set_show_branch'                => self::normalise_checkbox_value( $raw_settings, 'template_set_show_branch' ),
			'template_set_show_badges'                => self::normalise_checkbox_value( $raw_settings, 'template_set_show_badges' ),
			'template_set_show_mobile_cta'            => self::normalise_checkbox_value( $raw_settings, 'template_set_show_mobile_cta' ),
			'template_set_show_floorplans'            => self::normalise_checkbox_value( $raw_settings, 'template_set_show_floorplans' ),
			'template_set_show_virtual_tours'         => self::normalise_checkbox_value( $raw_settings, 'template_set_show_virtual_tours' ),
			'template_set_show_recommended'           => self::normalise_checkbox_value( $raw_settings, 'template_set_show_recommended' ),
			'template_set_recommended_count'          => $recommended_count,
			'template_set_recommended_layout'         => $recommended_layout,
			'template_set_recommended_image_size'     => $recommended_image_size,
		);

		$template_scoped_keys = array_keys( PH_Template_Set_Catalog::get_detail_shared_controls() );
		$editor_context       = isset( $raw_settings['template_set_editor_context'] ) ? sanitize_title( $raw_settings['template_set_editor_context'] ) : '';
		$is_detail_editor     = 'detail' === $editor_context;
		$override_source      = isset( $raw_settings['template_overrides'] ) ? $raw_settings['template_overrides'] : ( isset( $current_settings['template_overrides'] ) ? $current_settings['template_overrides'] : array() );
		$template_overrides   = self::sanitize_template_overrides( $override_source );

		if ( in_array( $editor_context, array( 'detail', 'search' ), true ) ) {
			foreach ( $template_scoped_keys as $key ) {
				if ( array_key_exists( $key, $current ) ) {
					$template_set_settings[ $key ] = $current[ $key ];
				}
			}

		}

		if ( $is_detail_editor ) {
			$manifest = PH_Template_Set_Catalog::get_detail_template_manifest( $detail_template );
			$controls = PH_Template_Set_Catalog::get_detail_template_controls( $detail_template );
			$editable = array_unique( array_merge( (array) $manifest['supports'], array_keys( (array) $manifest['controls'] ) ) );

			foreach ( $editable as $key ) {
				if ( ! isset( $controls[ $key ] ) ) {
					continue;
				}

				if ( 'checkbox' === $controls[ $key ]['type'] ) {
					$value = self::normalise_checkbox_value( $raw_settings, $key );
				} elseif ( array_key_exists( $key, $raw_settings ) ) {
					$value = self::sanitize_manifest_control_value( $raw_settings[ $key ], $controls[ $key ] );
				} else {
					continue;
				}

				if ( null !== $value ) {
					$template_overrides[ $detail_template ][ $key ] = $value;
				}
			}
		}

		$template_set_settings['template_overrides'] = $template_overrides;

		return array_merge( $current_settings, $template_set_settings );
	}

	/**
	 * Validate all stored per-template overrides against their manifests.
	 *
	 * @param array $overrides Raw overrides.
	 * @return array
	 */
	public static function sanitize_template_overrides( $overrides ) {
		$clean     = array();
		$templates = PH_Template_Set_Catalog::get_detail_templates();
		$overrides = is_array( $overrides ) ? $overrides : array();

		foreach ( $overrides as $slug => $values ) {
			$slug = PH_Template_Set_Catalog::normalize_detail_template_slug( $slug );

			if ( ! isset( $templates[ $slug ] ) || ! is_array( $values ) ) {
				continue;
			}

			$manifest = PH_Template_Set_Catalog::get_detail_template_manifest( $slug );
			$controls = PH_Template_Set_Catalog::get_detail_template_controls( $slug );
			$allowed  = array_unique( array_merge( (array) $manifest['supports'], array_keys( (array) $manifest['controls'] ) ) );

			foreach ( $allowed as $key ) {
				if ( ! array_key_exists( $key, $values ) || ! isset( $controls[ $key ] ) ) {
					continue;
				}

				$value = self::sanitize_manifest_control_value( $values[ $key ], $controls[ $key ] );

				if ( null !== $value ) {
					$clean[ $slug ][ $key ] = $value;
				}
			}
		}

		return $clean;
	}

	/**
	 * Validate one manifest control value against its declared options.
	 *
	 * @param mixed $value Raw value.
	 * @param array $control Control definition.
	 * @return mixed|null
	 */
	private static function sanitize_manifest_control_value( $value, $control ) {
		if ( ! is_scalar( $value ) || ! isset( $control['options'] ) || ! is_array( $control['options'] ) ) {
			return null;
		}

		$value = 'checkbox' === $control['type'] ? ( in_array( $value, array( '1', 1, 'yes', 'on', true ), true ) ? 'yes' : '' ) : sanitize_title( $value );

		return array_key_exists( $value, $control['options'] ) ? $value : null;
	}

	/**
	 * Normalise checkbox-style request values to Property Hive setting values.
	 *
	 * @param array  $settings Settings.
	 * @param string $key Setting key.
	 * @return string
	 */
	public static function normalise_checkbox_value( $settings, $key ) {
		if ( ! isset( $settings[ $key ] ) ) {
			return '';
		}

		return in_array( $settings[ $key ], array( '1', 1, 'yes', 'on', true ), true ) ? 'yes' : '';
	}

	/**
	 * Public settings for editor JavaScript.
	 *
	 * @param array $settings Settings.
	 * @return array
	 */
	public static function get_public_settings( $settings ) {
		$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), self::get_settings() );

		return array(
			'template_set_detail_template'        => sanitize_title( $settings['template_set_detail_template'] ),
			'template_set_search_template'        => sanitize_title( $settings['template_set_search_template'] ),
			'template_set_search_layout'          => sanitize_title( $settings['template_set_search_layout'] ),
			'template_set_gallery_layout'         => sanitize_title( $settings['template_set_gallery_layout'] ),
			'template_set_brand_colour'           => sanitize_hex_color( $settings['template_set_brand_colour'] ),
			'template_set_accent_colour'          => sanitize_hex_color( $settings['template_set_accent_colour'] ),
			'template_set_button_style'           => sanitize_title( $settings['template_set_button_style'] ),
			'template_set_search_card_size'       => sanitize_title( $settings['template_set_search_card_size'] ),
			'template_set_search_grid_columns'    => absint( $settings['template_set_search_grid_columns'] ),
			'template_set_image_style'            => sanitize_title( $settings['template_set_image_style'] ),
			'template_set_contact_card_style'     => sanitize_title( $settings['template_set_contact_card_style'] ),
			'template_set_show_branch'            => 'yes' === $settings['template_set_show_branch'] ? 'yes' : '',
			'template_set_show_badges'            => 'yes' === $settings['template_set_show_badges'] ? 'yes' : '',
			'template_set_show_mobile_cta'        => 'yes' === $settings['template_set_show_mobile_cta'] ? 'yes' : '',
			'template_set_show_floorplans'        => 'yes' === $settings['template_set_show_floorplans'] ? 'yes' : '',
			'template_set_show_virtual_tours'     => 'yes' === $settings['template_set_show_virtual_tours'] ? 'yes' : '',
			'template_set_show_recommended'       => 'yes' === $settings['template_set_show_recommended'] ? 'yes' : '',
			'template_set_recommended_count'      => absint( $settings['template_set_recommended_count'] ),
			'template_set_recommended_layout'     => sanitize_title( $settings['template_set_recommended_layout'] ),
			'template_set_recommended_image_size' => sanitize_title( $settings['template_set_recommended_image_size'] ),
		);
	}
}
