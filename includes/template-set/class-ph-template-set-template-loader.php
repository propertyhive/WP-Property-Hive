<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Locates and renders overrideable Template Set template parts.
 */
class PH_Template_Set_Template_Loader {

	/**
	 * Render a Template Set template part.
	 *
	 * @param string $type Template type.
	 * @param string $slug Selected template slug.
	 * @param string $part Template part name.
	 * @param array  $args Template arguments.
	 * @return bool
	 */
	public static function render( $type, $slug, $part, $args = array() ) {
		$type = sanitize_key( $type );
		$slug = sanitize_title( $slug );
		$part = sanitize_key( $part );
		$args = is_array( $args ) ? $args : array();

		if ( '' === $type || '' === $slug || '' === $part ) {
			return false;
		}

		$template_names = self::get_template_names( $type, $slug, $part );
		$located        = self::locate( $type, $slug, $part, $args, $template_names );

		if ( ! $located || ! file_exists( $located ) ) {
			return false;
		}

		$filtered_args = apply_filters( 'propertyhive_template_set_template_args', $args, $type, $slug, $part, $located );
		$args          = is_array( $filtered_args ) ? $filtered_args : $args;

		do_action( 'propertyhive_before_template_set_template', $type, $slug, $part, $located, $args );
		do_action( 'propertyhive_before_template_part', $template_names[0], PH()->template_path(), $located, $args );

		if ( $args ) {
			extract( $args );
		}

		include $located;

		do_action( 'propertyhive_after_template_part', $template_names[0], PH()->template_path(), $located, $args );
		do_action( 'propertyhive_after_template_set_template', $type, $slug, $part, $located, $args );

		return true;
	}

	/**
	 * Locate a Template Set template part.
	 *
	 * @param string $type Template type.
	 * @param string $slug Selected template slug.
	 * @param string $part Template part name.
	 * @param array  $args Template arguments.
	 * @param array  $template_names Candidate template names.
	 * @return string
	 */
	public static function locate( $type, $slug, $part, $args = array(), $template_names = array() ) {
		$type = sanitize_key( $type );
		$slug = sanitize_title( $slug );
		$part = sanitize_key( $part );

		if ( empty( $template_names ) ) {
			$template_names = self::get_template_names( $type, $slug, $part );
		}

		$template_path = PH()->template_path();
		$located       = locate_template(
			array(
				trailingslashit( $template_path ) . $template_names[0],
				trailingslashit( $template_path ) . $template_names[1],
				$template_names[0],
				$template_names[1],
			)
		);

		if ( ! $located ) {
			$default_path = trailingslashit( PH()->plugin_path() ) . 'templates/';

			foreach ( $template_names as $template_name ) {
				$template = $default_path . $template_name;

				if ( file_exists( $template ) ) {
					$located = $template;
					break;
				}
			}

			if ( ! $located ) {
				foreach ( self::get_fallback_template_names( $type, $slug, $part ) as $fallback_template_name ) {
					$template = $default_path . $fallback_template_name;

					if ( file_exists( $template ) ) {
						$located = $template;
						break;
					}
				}
			}
		}

		$located = apply_filters( 'propertyhive_locate_template', $located, $template_names[0], $template_path );

		return apply_filters( 'propertyhive_template_set_locate_template', $located, $type, $slug, $part, $template_names, $args );
	}

	/**
	 * Get specific and shared template names for a part.
	 *
	 * @param string $type Template type.
	 * @param string $slug Selected template slug.
	 * @param string $part Template part name.
	 * @return array
	 */
	private static function get_template_names( $type, $slug, $part ) {
		return array(
			'template-set/' . sanitize_key( $type ) . '/' . sanitize_title( $slug ) . '/' . sanitize_key( $part ) . '.php',
			'template-set/' . sanitize_key( $type ) . '/' . sanitize_key( $part ) . '.php',
		);
	}

	/**
	 * Get fallback template names for a filtered custom slug.
	 *
	 * @param string $type Template type.
	 * @param string $slug Selected template slug.
	 * @param string $part Template part name.
	 * @return array
	 */
	private static function get_fallback_template_names( $type, $slug, $part ) {
		$type  = sanitize_key( $type );
		$slug  = sanitize_title( $slug );
		$part  = sanitize_key( $part );
		$names = array();
		$slugs = array_unique(
			array_filter(
				array(
					self::get_default_slug( $type ),
					self::get_bundled_default_slug( $type ),
				)
			)
		);

		foreach ( $slugs as $fallback_slug ) {
			$fallback_slug = sanitize_title( $fallback_slug );

			if ( '' === $fallback_slug || $fallback_slug === $slug ) {
				continue;
			}

			$names[] = 'template-set/' . $type . '/' . $fallback_slug . '/' . $part . '.php';
		}

		return $names;
	}

	/**
	 * Get the filtered default slug for a template type.
	 *
	 * @param string $type Template type.
	 * @return string
	 */
	private static function get_default_slug( $type ) {
		if ( ! class_exists( 'PH_Template_Set_Catalog' ) ) {
			return '';
		}

		if ( 'search' === $type ) {
			return PH_Template_Set_Catalog::get_default_search_template();
		}

		if ( 'module' === $type ) {
			return PH_Template_Set_Catalog::get_default_module_template();
		}

		return PH_Template_Set_Catalog::get_default_detail_template();
	}

	/**
	 * Get the bundled default slug for a template type.
	 *
	 * @param string $type Template type.
	 * @return string
	 */
	private static function get_bundled_default_slug( $type ) {
		if ( 'search' === $type ) {
			return 'portal-style-search-results';
		}

		if ( 'module' === $type ) {
			return 'featured-properties-homepage-module';
		}

		return 'standard-sales-detail';
	}
}
