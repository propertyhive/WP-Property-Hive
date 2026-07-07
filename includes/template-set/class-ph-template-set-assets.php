<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template set front-end assets and style variables.
 */
class PH_Template_Set_Assets {

	/**
	 * Version preview assets by modified time so local design changes are not cached.
	 *
	 * @param string $relative_path Asset path relative to the plugin root.
	 * @return string
	 */
	public static function asset_version( $relative_path ) {
		$path = PH()->plugin_path() . '/' . ltrim( $relative_path, '/' );

		return file_exists( $path ) ? (string) filemtime( $path ) : PH_VERSION;
	}

	/**
	 * Register styles.
	 *
	 * @param array $styles Existing styles.
	 * @return array
	 */
	public static function enqueue_styles( $styles ) {
		if ( ! self::should_enqueue_frontend_assets( true ) ) {
			return $styles;
		}

		$styles['propertyhive-template-set'] = array(
			'src'     => str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/css/template-set.css',
			'deps'    => array( 'propertyhive-general' ),
			'version' => self::asset_version( 'assets/css/template-set.css' ),
			'media'   => 'all',
		);

		return $styles;
	}

	/**
	 * Register template-set scripts.
	 */
	public static function enqueue_scripts() {
		if ( ! self::should_enqueue_frontend_assets() ) {
			return;
		}

		$script_base_url = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/js/frontend/';
		$module_scripts  = array(
			'propertyhive-template-set-gallery'             => array(
				'path' => 'assets/js/frontend/template-set/gallery.js',
				'deps' => array(),
			),
			'propertyhive-template-set-editor-preview'      => array(
				'path' => 'assets/js/frontend/template-set/editor-preview.js',
				'deps' => array( 'propertyhive-template-set-gallery' ),
			),
			'propertyhive-template-set-editor-sidebar'      => array(
				'path' => 'assets/js/frontend/template-set/editor-sidebar.js',
				'deps' => array(),
			),
			'propertyhive-template-set-search-form-builder' => array(
				'path' => 'assets/js/frontend/template-set/search-form-builder.js',
				'deps' => array( 'propertyhive-template-set-editor-sidebar' ),
			),
		);

		foreach ( $module_scripts as $handle => $script ) {
			wp_enqueue_script(
				$handle,
				$script_base_url . str_replace( 'assets/js/frontend/', '', $script['path'] ),
				$script['deps'],
				self::asset_version( $script['path'] ),
				true
			);
		}

		wp_enqueue_script(
			'propertyhive-template-set',
			$script_base_url . 'template-set.js',
			array_keys( $module_scripts ),
			self::asset_version( 'assets/js/frontend/template-set.js' ),
			true
		);

		wp_localize_script( 'propertyhive-template-set', 'phTemplateSet', PH_Template_Set_Editor_Controller::get_script_data() );
	}

	/**
	 * Should Template Set frontend assets be loaded for this request?
	 *
	 * @param bool $include_modules Whether module shortcode pages should match.
	 * @return bool
	 */
	private static function should_enqueue_frontend_assets( $include_modules = false ) {
		if ( is_property() || is_post_type_archive( 'property' ) ) {
			return PH_Template_Set_Request_Context::is_enabled() || PH_Template_Set_Request_Context::can_show_template_switcher();
		}

		if ( $include_modules && is_singular() ) {
			$post = get_post();

			if ( $post && has_shortcode( $post->post_content, 'propertyhive_featured_template' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Print CSS variables from safe style controls.
	 */
	public static function print_style_variables( $rendering_module = false ) {
		if ( ! PH_Template_Set_Request_Context::is_enabled() && ! $rendering_module ) {
			return;
		}

		$settings = PH_Template_Set_Settings::get_settings();
		$brand    = sanitize_hex_color( $settings['template_set_brand_colour'] );
		$accent   = sanitize_hex_color( $settings['template_set_accent_colour'] );

		if ( empty( $brand ) ) {
			$brand = '#155e63';
		}

		if ( empty( $accent ) ) {
			$accent = '#b7791f';
		}

		echo '<style id="propertyhive-template-set-vars">:root,.ph-template-set,.ph-template-set-active{--ph-template-brand:' . esc_html( $brand ) . ';--ph-template-accent:' . esc_html( $accent ) . ';}</style>' . "\n";
	}
}
