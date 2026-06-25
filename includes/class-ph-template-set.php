<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Fixed Property Hive template set.
 *
 * Adds selectable front-end template profiles without replacing the existing
 * hook/template architecture.
 */
class PH_Template_Set {

	const OPTION_ENABLED = 'template_set_enabled';
	const DETAIL_QUERY_ARG = 'ph_detail_template';
	const SEARCH_QUERY_ARG = 'ph_search_template';
	const MODULE_QUERY_ARG = 'ph_module_template';
	const CATALOG_QUERY_ARG = 'ph_template_preview';
	const EDIT_QUERY_ARG = 'ph_template_edit';
	const EDIT_CLOSED_QUERY_ARG = 'ph_template_editor_closed';
	const EDITOR_MODE_LEGACY = 'legacy';
	const EDITOR_MODE_VISUAL = 'visual_editor';
	const EDITOR_NONCE_ACTION = 'propertyhive_template_set_editor';

	/**
	 * True while rendering the homepage module shortcode.
	 *
	 * @var bool
	 */
	private static $rendering_module = false;

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_filter( 'propertyhive_enqueue_styles', array( __CLASS__, 'enqueue_styles' ) );
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ) );
		add_filter( 'post_class', array( __CLASS__, 'post_classes' ), 25, 3 );
		add_filter( 'loop_search_results_columns', array( __CLASS__, 'search_result_columns' ), 20 );
		add_filter( 'post_type_link', array( __CLASS__, 'preserve_template_preview_on_property_links' ), 20, 2 );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'print_style_variables' ), 20 );
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_catalog_preview_request' ), 1 );
		add_action( 'wp', array( __CLASS__, 'prepare_module_preview' ) );
		add_action( 'wp', array( __CLASS__, 'prepare_detail_preview' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_bar_menu' ), 80 );
		add_action( 'wp_footer', array( __CLASS__, 'render_template_editor' ), 20 );
		add_action( 'wp_ajax_propertyhive_template_set_save', array( __CLASS__, 'ajax_save_template_editor' ) );

		add_action( 'propertyhive_before_main_content', array( __CLASS__, 'open_search_wrapper' ), 11 );
		add_action( 'propertyhive_after_main_content', array( __CLASS__, 'close_search_wrapper' ), 9 );
		add_action( 'propertyhive_before_search_results_loop', array( __CLASS__, 'render_module_preview' ), 12 );
		add_action( 'propertyhive_before_search_results_loop', array( __CLASS__, 'render_search_template_intro' ), 18 );
		add_action( 'propertyhive_before_search_results_loop', array( __CLASS__, 'render_search_tools' ), 25 );
		add_action( 'propertyhive_before_search_results_loop', array( __CLASS__, 'render_map_panel' ), 35 );
		add_action( 'propertyhive_before_search_results_loop', array( __CLASS__, 'render_demo_search_results' ), 45 );
		add_filter( 'propertyhive_show_results', array( __CLASS__, 'maybe_hide_results_for_module_preview' ), 20 );
		add_filter( 'propertyhive_show_page_title', array( __CLASS__, 'maybe_hide_title_for_module_preview' ), 20 );

		add_action( 'propertyhive_before_search_results_loop_item_title', array( __CLASS__, 'render_card_badges' ), 5 );
		add_action( 'propertyhive_before_search_results_loop_item_title', array( __CLASS__, 'render_card_gallery_data' ), 15 );
		add_action( 'propertyhive_after_search_results_loop_item', array( __CLASS__, 'render_card_footer' ), 5 );

		add_action( 'propertyhive_single_property_summary', array( __CLASS__, 'render_detail_template_kicker' ), 3 );
		add_action( 'propertyhive_single_property_summary', array( __CLASS__, 'render_detail_highlights' ), 25 );
		add_action( 'propertyhive_after_single_property_summary', array( __CLASS__, 'render_detail_contact_panel' ), 5 );
		add_action( 'propertyhive_after_single_property_summary', array( __CLASS__, 'render_detail_context_panel' ), 7 );
		add_action( 'propertyhive_after_single_property_summary', array( __CLASS__, 'render_detail_modules' ), 50 );
		add_action( 'propertyhive_after_single_property_summary', array( __CLASS__, 'render_similar_properties' ), 60 );
		add_action( 'propertyhive_after_single_property_summary', array( __CLASS__, 'render_mobile_cta_bar' ), 95 );
		add_action( 'propertyhive_property_actions_end', array( __CLASS__, 'render_trust_note' ), 20 );

		add_action( 'propertyhive_before_main_content', array( __CLASS__, 'render_preview_masthead' ), 5 );
	}

	/**
	 * Is the request a capability-gated template preview (query-arg driven)?
	 *
	 * Demo/sample content only renders in this context so that a live site that
	 * simply enables the template set never has its real data overwritten.
	 *
	 * @return bool
	 */
	public static function is_demo_preview() {
		return self::is_enabled() && self::is_previewing_template();
	}

	/**
	 * Absolute (protocol-relative) URL for a bundled demo image.
	 *
	 * @param string $file File name within assets/images/template-demo.
	 * @return string
	 */
	private static function demo_asset( $file ) {
		return str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/images/template-demo/' . $file;
	}

	/**
	 * Version preview assets by modified time so local design changes are not cached.
	 *
	 * @param string $relative_path Asset path relative to the plugin root.
	 * @return string
	 */
	private static function asset_version( $relative_path ) {
		$path = PH()->plugin_path() . '/' . ltrim( $relative_path, '/' );

		return file_exists( $path ) ? (string) filemtime( $path ) : PH_VERSION;
	}

	/**
	 * Credible demo agency / negotiator identity used in preview mode.
	 *
	 * @return array
	 */
	private static function get_demo_agency() {
		return array(
			'office'  => 'Ashford & Rowe Prime',
			'branch'  => 'Marylebone',
			'agent'   => 'James Ashford',
			'role'    => 'Associate Director',
			'phone'   => '020 7946 0958',
			'email'   => 'james.ashford@ashfordrowe.co.uk',
			'address' => '18 Cavendish Parade, Marylebone, London W1U 4QT',
			'portrait' => 'agent-james-ashford.png',
		);
	}

	/**
	 * Register styles.
	 *
	 * @param array $styles Existing styles.
	 * @return array
	 */
	public static function enqueue_styles( $styles ) {
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
		if ( ! is_property() && ! is_post_type_archive( 'property' ) ) {
			return;
		}

		if ( ! self::is_enabled() && ! self::can_show_template_switcher() ) {
			return;
		}

		wp_enqueue_script(
			'propertyhive-template-set',
			str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/js/frontend/template-set.js',
			array(),
			self::asset_version( 'assets/js/frontend/template-set.js' ),
			true
		);

		wp_localize_script( 'propertyhive-template-set', 'phTemplateSet', self::get_script_data() );
	}

	/**
	 * Register shortcodes.
	 */
	public static function register_shortcodes() {
		add_shortcode( 'propertyhive_featured_template', array( __CLASS__, 'featured_template_shortcode' ) );
	}

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
				self::OPTION_ENABLED             => '',
				'template_set_editor_mode'            => $default_editor_mode,
				'template_set_detail_template'        => 'standard-sales-detail',
				'template_set_search_template'        => 'portal-style-search-results',
				'template_set_gallery_layout'         => 'showcase',
				'template_set_brand_colour'           => '#155e63',
				'template_set_accent_colour'          => '#b7791f',
				'template_set_button_style'           => 'filled',
				'template_set_card_density'           => 'standard',
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
			)
		);

		if ( ! isset( self::get_contact_card_styles()[ $settings['template_set_contact_card_style'] ] ) ) {
			$settings['template_set_contact_card_style'] = 'classic';
		}

		return $settings;
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
	private static function get_default_editor_mode( $settings ) {
		if ( ! is_array( $settings ) || empty( $settings ) ) {
			return self::EDITOR_MODE_VISUAL;
		}

		if ( isset( $settings['template_set_editor_mode'] ) && isset( self::get_editor_modes()[ $settings['template_set_editor_mode'] ] ) ) {
			return sanitize_title( $settings['template_set_editor_mode'] );
		}

		return self::has_legacy_frontend_settings( $settings ) ? self::EDITOR_MODE_LEGACY : self::EDITOR_MODE_VISUAL;
	}

	/**
	 * Does this site already have settings from the older front-end system?
	 *
	 * @param array $settings Stored template assistant settings.
	 * @return bool
	 */
	private static function has_legacy_frontend_settings( $settings ) {
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

		$detail_templates = self::get_detail_templates();
		$search_templates = self::get_search_templates();
		$gallery_layouts  = self::get_gallery_layouts();
		$editor_modes     = self::get_editor_modes();

		$detail_template = isset( $raw_settings['template_set_detail_template'] ) ? sanitize_title( $raw_settings['template_set_detail_template'] ) : sanitize_title( $current['template_set_detail_template'] );
		if ( ! isset( $detail_templates[ $detail_template ] ) ) {
			$detail_template = 'standard-sales-detail';
		}

		$search_template = isset( $raw_settings['template_set_search_template'] ) ? sanitize_title( $raw_settings['template_set_search_template'] ) : sanitize_title( $current['template_set_search_template'] );
		if ( ! isset( $search_templates[ $search_template ] ) ) {
			$search_template = 'portal-style-search-results';
		}

		$gallery_layout = isset( $raw_settings['template_set_gallery_layout'] ) ? sanitize_title( $raw_settings['template_set_gallery_layout'] ) : sanitize_title( $current['template_set_gallery_layout'] );
		if ( ! isset( $gallery_layouts[ $gallery_layout ] ) ) {
			$gallery_layout = 'showcase';
		}

		$editor_mode = isset( $raw_settings['template_set_editor_mode'] ) ? sanitize_title( $raw_settings['template_set_editor_mode'] ) : sanitize_title( $current['template_set_editor_mode'] );
		if ( $activate ) {
			$editor_mode = self::EDITOR_MODE_VISUAL;
		}
		if ( ! isset( $editor_modes[ $editor_mode ] ) ) {
			$editor_mode = self::EDITOR_MODE_LEGACY;
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
		if ( ! isset( self::get_button_styles()[ $button_style ] ) ) {
			$button_style = 'filled';
		}

		$card_density = isset( $raw_settings['template_set_card_density'] ) ? sanitize_title( $raw_settings['template_set_card_density'] ) : sanitize_title( $current['template_set_card_density'] );
		if ( ! isset( self::get_card_densities()[ $card_density ] ) ) {
			$card_density = 'standard';
		}

		$image_style = isset( $raw_settings['template_set_image_style'] ) ? sanitize_title( $raw_settings['template_set_image_style'] ) : sanitize_title( $current['template_set_image_style'] );
		if ( ! isset( self::get_image_styles()[ $image_style ] ) ) {
			$image_style = 'soft';
		}

		$contact_card_style = isset( $raw_settings['template_set_contact_card_style'] ) ? sanitize_title( $raw_settings['template_set_contact_card_style'] ) : sanitize_title( $current['template_set_contact_card_style'] );
		if ( ! isset( self::get_contact_card_styles()[ $contact_card_style ] ) ) {
			$contact_card_style = 'classic';
		}

		$recommended_count = isset( $raw_settings['template_set_recommended_count'] ) ? absint( $raw_settings['template_set_recommended_count'] ) : absint( $current['template_set_recommended_count'] );
		if ( ! isset( self::get_recommended_property_counts()[ $recommended_count ] ) ) {
			$recommended_count = 3;
		}

		$recommended_layout = isset( $raw_settings['template_set_recommended_layout'] ) ? sanitize_title( $raw_settings['template_set_recommended_layout'] ) : sanitize_title( $current['template_set_recommended_layout'] );
		if ( ! isset( self::get_recommended_property_layouts()[ $recommended_layout ] ) ) {
			$recommended_layout = 'grid';
		}

		$recommended_image_size = isset( $raw_settings['template_set_recommended_image_size'] ) ? sanitize_title( $raw_settings['template_set_recommended_image_size'] ) : sanitize_title( $current['template_set_recommended_image_size'] );
		if ( ! isset( self::get_recommended_property_image_sizes()[ $recommended_image_size ] ) ) {
			$recommended_image_size = 'standard';
		}

		$template_set_settings = array(
			self::OPTION_ENABLED                       => $activate || ! empty( $raw_settings[ self::OPTION_ENABLED ] ) ? 'yes' : '',
			'template_set_editor_mode'                => $editor_mode,
			'template_set_detail_template'            => $detail_template,
			'template_set_search_template'            => $search_template,
			'template_set_gallery_layout'             => $gallery_layout,
			'template_set_brand_colour'               => $brand_colour,
			'template_set_accent_colour'              => $accent_colour,
			'template_set_button_style'               => $button_style,
			'template_set_card_density'               => $card_density,
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

		return array_merge( $current_settings, $template_set_settings );
	}

	/**
	 * Normalise checkbox-style request values to Property Hive setting values.
	 *
	 * @param array  $settings Settings.
	 * @param string $key Setting key.
	 * @return string
	 */
	private static function normalise_checkbox_value( $settings, $key ) {
		if ( ! isset( $settings[ $key ] ) ) {
			return '';
		}

		return in_array( $settings[ $key ], array( '1', 1, 'yes', 'on', true ), true ) ? 'yes' : '';
	}

	/**
	 * Editor compatibility modes.
	 *
	 * @return array
	 */
	public static function get_editor_modes() {
		return array(
			self::EDITOR_MODE_LEGACY => __( 'Legacy preview controls', 'propertyhive' ),
			self::EDITOR_MODE_VISUAL => __( 'Visual editor', 'propertyhive' ),
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
	 * Card density choices.
	 *
	 * @return array
	 */
	public static function get_card_densities() {
		return array(
			'spacious' => __( 'Spacious', 'propertyhive' ),
			'standard' => __( 'Standard', 'propertyhive' ),
			'compact'  => __( 'Compact', 'propertyhive' ),
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
			'rounded' => __( 'Rounded media', 'propertyhive' ),
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

	/**
	 * Is the global template set enabled?
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$settings = self::get_settings();

		if ( isset( $settings[ self::OPTION_ENABLED ] ) && 'yes' === $settings[ self::OPTION_ENABLED ] ) {
			return true;
		}

		return self::can_render_preview_request();
	}

	/**
	 * Detail template profiles.
	 *
	 * @return array
	 */
	public static function get_detail_templates() {
		return array(
			'standard-sales-detail'           => __( 'Standard Sales Detail', 'propertyhive' ),
		);
	}

	/**
	 * Search template profiles.
	 *
	 * @return array
	 */
	public static function get_search_templates() {
		return array(
			'portal-style-search-results'      => __( 'Portal-Style Search Results', 'propertyhive' ),
		);
	}

	/**
	 * Homepage/module template profiles.
	 *
	 * @return array
	 */
	public static function get_module_templates() {
		return array();
	}

	/**
	 * Full template catalogue used by the front-end preview switcher.
	 *
	 * @return array
	 */
	public static function get_template_catalog() {
		$catalog = array();

		foreach ( self::get_detail_templates() as $slug => $label ) {
			$catalog[ $slug ] = array(
				'type'  => 'detail',
				'group' => __( 'Property detail templates', 'propertyhive' ),
				'label' => $label,
			);
		}

		foreach ( self::get_search_templates() as $slug => $label ) {
			$catalog[ $slug ] = array(
				'type'  => 'search',
				'group' => __( 'Search result templates', 'propertyhive' ),
				'label' => $label,
			);
		}

		foreach ( self::get_module_templates() as $slug => $label ) {
			$catalog[ $slug ] = array(
				'type'  => 'module',
				'group' => __( 'Homepage module templates', 'propertyhive' ),
				'label' => $label,
			);
		}

		return $catalog;
	}

	/**
	 * Get the selected detail template.
	 *
	 * @return string
	 */
	public static function get_detail_template() {
		$settings  = self::get_settings();
		$templates = self::get_detail_templates();
		$template  = self::get_query_template( self::DETAIL_QUERY_ARG, $templates );

		if ( empty( $template ) ) {
			$template = sanitize_title( $settings['template_set_detail_template'] );
		}

		return isset( $templates[ $template ] ) ? $template : 'standard-sales-detail';
	}

	/**
	 * Get the selected search template.
	 *
	 * @return string
	 */
	public static function get_search_template() {
		$settings  = self::get_settings();
		$templates = self::get_search_templates();
		$template  = self::get_query_template( self::SEARCH_QUERY_ARG, $templates );

		if ( empty( $template ) ) {
			$template = sanitize_title( $settings['template_set_search_template'] );
		}

		return isset( $templates[ $template ] ) ? $template : 'portal-style-search-results';
	}

	/**
	 * Get the selected homepage/module template.
	 *
	 * @return string
	 */
	public static function get_module_template() {
		$templates = self::get_module_templates();
		$template  = self::get_query_template( self::MODULE_QUERY_ARG, $templates );

		return isset( $templates[ $template ] ) ? $template : '';
	}

	/**
	 * Build a preview URL for a catalogue template.
	 *
	 * @param string $template Template slug.
	 * @return string
	 */
	public static function get_template_preview_url( $template ) {
		$catalog = self::get_template_catalog();
		$template = sanitize_title( $template );

		if ( ! isset( $catalog[ $template ] ) ) {
			return self::get_current_url();
		}

		if ( 'detail' === $catalog[ $template ]['type'] ) {
			$url = self::get_sample_property_url( $template );
			return add_query_arg( self::DETAIL_QUERY_ARG, $template, $url );
		}

		$archive_url = get_post_type_archive_link( 'property' );
		if ( ! $archive_url ) {
			$archive_url = home_url( '/' );
		}

		return add_query_arg( self::SEARCH_QUERY_ARG, $template, $archive_url );
	}

	/**
	 * Keep the selected template active when previewing/editing and opening another property.
	 *
	 * @param string  $url  Property URL.
	 * @param WP_Post $post Post object.
	 * @return string
	 */
	public static function preserve_template_preview_on_property_links( $url, $post ) {
		if ( is_admin() || ! $post || 'property' !== get_post_type( $post ) ) {
			return $url;
		}

		if ( ! self::can_show_template_switcher() ) {
			return $url;
		}

		if ( ! self::is_previewing_template() && ! self::is_template_editor_active() ) {
			return $url;
		}

		$args = array(
			self::DETAIL_QUERY_ARG => self::get_detail_template(),
		);

		if ( self::is_template_editor_active() ) {
			$args[ self::EDIT_QUERY_ARG ] = '1';
		} elseif ( self::is_template_editor_closed_request() ) {
			$args[ self::EDIT_CLOSED_QUERY_ARG ] = '1';
		}

		return add_query_arg( $args, $url );
	}

	/**
	 * Redirect generic catalogue preview requests to the correct page type.
	 */
	public static function redirect_catalog_preview_request() {
		if ( empty( $_GET[ self::CATALOG_QUERY_ARG ] ) ) {
			return;
		}

		$template = sanitize_title( wp_unslash( $_GET[ self::CATALOG_QUERY_ARG ] ) );
		$catalog  = self::get_template_catalog();

		if ( ! isset( $catalog[ $template ] ) ) {
			return;
		}

		wp_safe_redirect( self::get_template_preview_url( $template ) );
		exit;
	}

	/**
	 * Add a front-end WP admin bar menu for switching template previews.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public static function add_admin_bar_menu( $wp_admin_bar ) {
		if ( ! self::can_show_template_switcher() ) {
			return;
		}

		$is_detail = is_property();
		$is_search = is_post_type_archive( 'property' );

		if ( ! $is_detail && ! $is_search ) {
			return;
		}

		$catalog         = self::get_template_catalog();
		$current_slug    = self::get_current_catalog_template();
		$current_label   = isset( $catalog[ $current_slug ] ) ? $catalog[ $current_slug ]['label'] : __( 'Template Set', 'propertyhive' );
		$settings_url    = admin_url( 'admin.php?page=ph-settings&tab=frontend&section=template-set' );
		$root_id         = 'ph-template-set';
		$inactive_suffix = self::is_enabled() ? '' : ' ' . __( '(inactive)', 'propertyhive' );
		$editor_url      = add_query_arg( self::EDIT_QUERY_ARG, '1', remove_query_arg( self::EDIT_CLOSED_QUERY_ARG, self::get_current_url() ) );
		$exit_editor_url = add_query_arg( self::EDIT_CLOSED_QUERY_ARG, '1', remove_query_arg( self::EDIT_QUERY_ARG, self::get_current_url() ) );

		$wp_admin_bar->add_node(
			array(
				'id'    => $root_id,
				'title' => sprintf(
					/* translators: %s: current template name */
					__( 'Template: %s', 'propertyhive' ),
					$current_label
				) . $inactive_suffix,
				'href'  => $settings_url,
			)
		);

		$wp_admin_bar->add_node(
			array(
				'id'     => 'ph-template-set-editor',
				'parent' => $root_id,
				'title'  => self::is_template_editor_active() ? __( 'Template editor active', 'propertyhive' ) : __( 'Edit template visually', 'propertyhive' ),
				'href'   => $editor_url,
			)
		);

		if ( self::is_template_editor_active() ) {
			$wp_admin_bar->add_node(
				array(
					'id'     => 'ph-template-set-exit-editor',
					'parent' => $root_id,
					'title'  => __( 'Exit template editor', 'propertyhive' ),
					'href'   => $exit_editor_url,
				)
			);
		}

		foreach ( $catalog as $slug => $template ) {
			$title = sprintf(
				/* translators: 1: template group, 2: template name */
				__( '%1$s: %2$s', 'propertyhive' ),
				self::get_short_template_group_label( $template['type'] ),
				$template['label']
			);
			if ( $slug === $current_slug ) {
				$title = sprintf(
					/* translators: %s: current template name */
					__( '%s (current)', 'propertyhive' ),
					$title
				);
			}

			$wp_admin_bar->add_node(
				array(
					'id'     => 'ph-template-set-' . sanitize_key( $slug ),
					'parent' => $root_id,
					'title'  => $title,
					'href'   => self::get_template_preview_url( $slug ),
				)
			);
		}

		$wp_admin_bar->add_node(
			array(
				'id'     => 'ph-template-set-use-saved-default',
				'parent' => $root_id,
				'title'  => __( 'Use saved default', 'propertyhive' ),
				'href'   => remove_query_arg( self::get_preview_clear_query_args(), self::get_current_url() ),
			)
		);

		$wp_admin_bar->add_node(
			array(
				'id'     => 'ph-template-set-settings',
				'parent' => $root_id,
				'title'  => __( 'Open Template Set settings', 'propertyhive' ),
				'href'   => $settings_url,
			)
		);
	}

	/**
	 * Add template set body classes.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public static function body_classes( $classes ) {
		if ( ! self::is_enabled() ) {
			return $classes;
		}

		$settings  = self::get_settings();
		$classes[] = 'ph-template-set-active';
		$classes[] = 'ph-template-density-' . sanitize_html_class( $settings['template_set_card_density'] );
		$classes[] = 'ph-template-images-' . sanitize_html_class( $settings['template_set_image_style'] );
		$classes[] = 'ph-template-buttons-' . sanitize_html_class( $settings['template_set_button_style'] );
		$classes[] = 'ph-template-contact-card-' . sanitize_html_class( $settings['template_set_contact_card_style'] );
		$classes[] = 'ph-template-editor-mode-' . sanitize_html_class( $settings['template_set_editor_mode'] );
		$classes[] = 'yes' === $settings['template_set_show_branch'] ? 'ph-template-show-branch' : 'ph-template-hide-branch';
		$classes[] = 'yes' === $settings['template_set_show_badges'] ? 'ph-template-show-badges' : 'ph-template-hide-badges';
		$classes[] = 'yes' === $settings['template_set_show_mobile_cta'] ? 'ph-template-show-mobile-cta' : 'ph-template-hide-mobile-cta';
		$classes[] = 'yes' === $settings['template_set_show_floorplans'] ? 'ph-template-show-floorplans' : 'ph-template-hide-floorplans';
		$classes[] = 'yes' === $settings['template_set_show_virtual_tours'] ? 'ph-template-show-virtual-tours' : 'ph-template-hide-virtual-tours';
		$classes[] = 'yes' === $settings['template_set_show_recommended'] ? 'ph-template-show-recommended' : 'ph-template-hide-recommended';
		$classes[] = 'ph-template-recommended-count-' . absint( $settings['template_set_recommended_count'] );
		$classes[] = 'ph-template-recommended-layout-' . sanitize_html_class( $settings['template_set_recommended_layout'] );
		$classes[] = 'ph-template-recommended-images-' . sanitize_html_class( $settings['template_set_recommended_image_size'] );

		if ( self::is_template_editor_active() ) {
			$classes[] = 'ph-template-editor-active';
		}

		if ( self::is_demo_preview() ) {
			$classes[] = 'ph-template-preview-mode';
		}

		if ( is_property() ) {
			$classes[] = 'ph-detail-template-' . sanitize_html_class( self::get_detail_template() );
		}

		if ( is_post_type_archive( 'property' ) ) {
			$classes[] = 'ph-search-template-' . sanitize_html_class( self::get_search_template() );
			$classes[] = 'ph-search-view-' . sanitize_html_class( self::get_search_view() );
		}

		if ( self::is_module_preview() ) {
			$classes[] = 'ph-module-template-' . sanitize_html_class( self::get_module_template() );
			$classes[] = 'ph-module-template-preview-active';
		}

		return $classes;
	}

	/**
	 * Add template set classes to property cards/details.
	 *
	 * @param array       $classes Post classes.
	 * @param string|array $class Extra classes.
	 * @param int         $post_id Post ID.
	 * @return array
	 */
	public static function post_classes( $classes, $class = '', $post_id = 0 ) {
		if ( 'property' !== get_post_type( $post_id ) ) {
			return $classes;
		}

		if ( self::is_enabled() && is_property() && (int) get_the_ID() === (int) $post_id ) {
			$classes[] = 'ph-template-set';
			$classes[] = 'ph-template-detail';
			$classes[] = 'ph-detail-template-' . sanitize_html_class( self::get_detail_template() );
		}

		if ( ( self::is_enabled() && ! is_property() ) || self::$rendering_module ) {
			$settings  = self::get_settings();
			$classes[] = 'ph-template-card';
			$classes[] = 'ph-search-template-' . sanitize_html_class( self::get_search_template() );
			$classes[] = 'ph-template-density-' . sanitize_html_class( $settings['template_set_card_density'] );
			$classes[] = 'ph-template-images-' . sanitize_html_class( $settings['template_set_image_style'] );

			if ( self::$rendering_module ) {
				$classes[] = 'ph-template-module-card';
				$classes[] = 'ph-home-template-' . sanitize_html_class( self::get_module_template() );
			}
		}

		return $classes;
	}

	/**
	 * Template set can control search result columns safely.
	 *
	 * @param int $columns Existing columns.
	 * @return int
	 */
	public static function search_result_columns( $columns ) {
		if ( ! self::is_enabled() ) {
			return $columns;
		}

		$template = self::get_search_template();

		if ( 'brand-led-agency-search-results' === $template ) {
			return 2;
		}

		if ( 'compact-list-search-results' === $template || 'map-led-search-results' === $template ) {
			return 1;
		}

		return $columns;
	}

	/**
	 * Print CSS variables from safe style controls.
	 */
	public static function print_style_variables() {
		if ( ! self::is_enabled() && ! self::$rendering_module ) {
			return;
		}

		$settings = self::get_settings();
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

	/**
	 * Open search wrapper.
	 */
	public static function open_search_wrapper() {
		if ( ! self::is_enabled() || ! is_post_type_archive( 'property' ) ) {
			return;
		}

		$classes = array(
			'ph-template-set',
			'ph-template-search',
			'ph-search-template-' . sanitize_html_class( self::get_search_template() ),
			'ph-search-view-' . sanitize_html_class( self::get_search_view() ),
		);

		if ( self::is_module_preview() ) {
			$classes[] = 'ph-template-module-preview-shell';
			$classes[] = 'ph-module-template-' . sanitize_html_class( self::get_module_template() );
		}

		echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';
	}

	/**
	 * Close search wrapper.
	 */
	public static function close_search_wrapper() {
		if ( ! self::is_enabled() || ! is_post_type_archive( 'property' ) ) {
			return;
		}

		echo '</div>';
	}

	/**
	 * Render search tools.
	 */
	public static function render_search_tools() {
		if ( ! self::is_enabled() || ! is_post_type_archive( 'property' ) ) {
			return;
		}

		if ( self::is_module_preview() ) {
			return;
		}

		$template = self::get_search_template();
		$view     = self::get_search_view();
		$base_url = remove_query_arg( 'ph_view' );
		$views    = array(
			'list' => __( 'List', 'propertyhive' ),
			'grid' => __( 'Grid', 'propertyhive' ),
			'map'  => __( 'Map', 'propertyhive' ),
		);

		if ( 'compact-list-search-results' === $template ) {
			unset( $views['grid'], $views['map'] );
		}

		echo '<div class="ph-template-search-tools">';
			echo '<div class="ph-template-view-toggle" role="group" aria-label="' . esc_attr__( 'Results view', 'propertyhive' ) . '">';
			foreach ( $views as $view_key => $label ) {
				$url        = esc_url( add_query_arg( 'ph_view', $view_key, $base_url ) );
				$is_active  = $view_key === $view;
				$class_name = 'ph-template-view-button' . ( $is_active ? ' is-active' : '' );
				echo '<a class="' . esc_attr( $class_name ) . '" href="' . $url . '">' . esc_html( $label ) . '</a>';
			}
			echo '</div>';

			echo '<div class="ph-template-search-actions">';
				echo '<a class="ph-template-soft-action" href="' . esc_url( add_query_arg( 'property_alert', '1', $base_url ) ) . '">' . esc_html__( 'Create alert', 'propertyhive' ) . '</a>';
				echo '<a class="ph-template-soft-action" href="' . esc_url( add_query_arg( 'save_search', '1', $base_url ) ) . '">' . esc_html__( 'Save search', 'propertyhive' ) . '</a>';
			echo '</div>';
		echo '</div>';
	}

	/**
	 * Render a template-specific search header.
	 */
	public static function render_search_template_intro() {
		if ( ! self::is_enabled() || ! is_post_type_archive( 'property' ) ) {
			return;
		}

		if ( self::is_module_preview() ) {
			return;
		}

		$template = self::get_search_template();
		$content  = self::get_search_intro_content( $template );

		if ( empty( $content ) ) {
			return;
		}

		echo '<section class="ph-template-search-intro ph-template-search-intro-' . esc_attr( sanitize_html_class( $template ) ) . '">';
			echo '<span class="ph-template-search-kicker">' . esc_html( $content['kicker'] ) . '</span>';
			echo '<div class="ph-template-search-intro-copy">';
				echo '<h2>' . esc_html( $content['title'] ) . '</h2>';
				echo '<p>' . esc_html( $content['body'] ) . '</p>';
			echo '</div>';

			if ( ! empty( $content['items'] ) ) {
				echo '<ul class="ph-template-search-intro-items">';
				foreach ( $content['items'] as $item ) {
					echo '<li>' . esc_html( $item ) . '</li>';
				}
				echo '</ul>';
			}
		echo '</section>';
	}

	/**
	 * Render a lightweight map panel for map-led layouts.
	 */
	public static function render_map_panel() {
		if ( ! self::is_enabled() || ! is_post_type_archive( 'property' ) ) {
			return;
		}

		if ( self::is_module_preview() ) {
			return;
		}

		if ( 'map-led-search-results' !== self::get_search_template() && 'map' !== self::get_search_view() ) {
			return;
		}

		echo '<div class="ph-template-map-panel">';
			echo '<div class="ph-template-map-surface">';
				echo '<span class="ph-template-map-pin ph-template-map-pin-1"></span>';
				echo '<span class="ph-template-map-pin ph-template-map-pin-2"></span>';
				echo '<span class="ph-template-map-pin ph-template-map-pin-3"></span>';
				echo '<span class="ph-template-map-label">' . esc_html__( 'Map view', 'propertyhive' ) . '</span>';
			echo '</div>';
		echo '</div>';
	}

	/**
	 * Prepare the archive page as a clean homepage-module preview.
	 */
	public static function prepare_module_preview() {
		if ( ! self::is_module_preview() ) {
			return;
		}

		remove_action( 'propertyhive_before_search_results_loop', 'propertyhive_search_form', 10 );
		remove_action( 'propertyhive_before_search_results_loop', 'propertyhive_result_count', 20 );
		remove_action( 'propertyhive_before_search_results_loop', 'propertyhive_catalog_ordering', 30 );
	}

	/**
	 * Render the detail preview with the current property record, while keeping
	 * the gallery sandbox available for layout exploration.
	 */
	public static function prepare_detail_preview() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		remove_action( 'propertyhive_before_single_property_summary', 'propertyhive_show_property_images', 10 );
		add_action( 'propertyhive_before_single_property_summary', array( __CLASS__, 'render_demo_gallery' ), 10 );
		add_action( 'propertyhive_before_single_property_summary', array( __CLASS__, 'render_detail_facts_strip' ), 11 );

		remove_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_price', 10 );
		add_action( 'propertyhive_single_property_summary', array( __CLASS__, 'render_demo_price' ), 10 );

		remove_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_meta', 20 );
		add_action( 'propertyhive_single_property_summary', array( __CLASS__, 'render_demo_meta' ), 15 );

		remove_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_sharing', 30 );
		remove_action( 'propertyhive_after_single_property_summary', 'propertyhive_template_single_actions', 10 );

		remove_action( 'propertyhive_after_single_property_summary', 'propertyhive_template_single_features', 20 );
		add_action( 'propertyhive_after_single_property_summary', array( __CLASS__, 'render_demo_features' ), 20 );

		remove_action( 'propertyhive_after_single_property_summary', 'propertyhive_template_single_summary', 30 );
		add_action( 'propertyhive_after_single_property_summary', array( __CLASS__, 'render_demo_summary' ), 30 );

		remove_action( 'propertyhive_after_single_property_summary', 'propertyhive_template_single_description', 40 );
		add_action( 'propertyhive_after_single_property_summary', array( __CLASS__, 'render_demo_description' ), 40 );

	}

	/**
	 * Deprecated no-op retained for compatibility with older preview hooks.
	 *
	 * @param string $title Original title.
	 * @param int    $id    Post ID.
	 * @return string
	 */
	public static function filter_demo_title( $title, $id = 0 ) {
		return $title;
	}

	/**
	 * Render a quiet agency masthead at the top of preview pages so the demo
	 * brand, not the generic site title, anchors the screenshot.
	 */
	public static function render_preview_masthead() {
		if ( ! self::is_demo_preview() ) {
			return;
		}

		if ( ! is_property() && ! is_post_type_archive( 'property' ) ) {
			return;
		}

		$brand = '';
		$phone = '';

		if ( is_property() ) {
			$property = self::get_current_property();

			if ( $property ) {
				$brand = self::get_display_office_name( $property );
				$phone = $property->get_negotiator_telephone_number();
			}
		}

		if ( ! $brand ) {
			$agency = self::get_demo_agency();
			$brand  = $agency['office'];
			$phone  = $agency['phone'];
		}

		echo '<div class="ph-template-masthead"><div class="ph-template-masthead-inner">';
			echo '<span class="ph-template-masthead-brand">' . esc_html( $brand ) . '</span>';
			if ( $phone ) {
				echo '<a class="ph-template-masthead-phone" href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a>';
			}
		echo '</div></div>';
	}

	/**
	 * Build gallery image data from the current property's attached photos.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_property_gallery_images( $property ) {
		$images = array();

		if ( ! $property ) {
			return $images;
		}

		if ( get_option( 'propertyhive_images_stored_as', '' ) === 'urls' ) {
			$photos = $property->_photo_urls;

			if ( ! is_array( $photos ) ) {
				return $images;
			}

			foreach ( $photos as $index => $photo ) {
				if ( empty( $photo['url'] ) ) {
					continue;
				}

				$label = ! empty( $photo['title'] ) ? $photo['title'] : sprintf(
					/* translators: %d: photo number */
					__( 'Photo %d', 'propertyhive' ),
					(int) $index + 1
				);

				$images[] = array(
					'src'     => $photo['url'],
					'thumb'   => $photo['url'],
					'alt'     => $label,
					'caption' => $label,
				);
			}

			return $images;
		}

		foreach ( $property->get_gallery_attachment_ids() as $index => $attachment_id ) {
			$src   = wp_get_attachment_image_src( $attachment_id, apply_filters( 'propertyhive_single_property_image_size', 'large' ) );
			$thumb = wp_get_attachment_image_src( $attachment_id, apply_filters( 'single_property_small_thumbnail_size', 'medium' ) );

			if ( empty( $src[0] ) ) {
				continue;
			}

			$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			if ( '' === trim( (string) $alt ) ) {
				$alt = get_the_title( $attachment_id );
			}
			if ( '' === trim( (string) $alt ) ) {
				$alt = sprintf(
					/* translators: %d: photo number */
					__( 'Photo %d', 'propertyhive' ),
					(int) $index + 1
				);
			}

			$caption = wp_get_attachment_caption( $attachment_id );
			if ( '' === trim( (string) $caption ) ) {
				$caption = $alt;
			}

			$images[] = array(
				'src'     => $src[0],
				'thumb'   => ! empty( $thumb[0] ) ? $thumb[0] : $src[0],
				'alt'     => $alt,
				'caption' => $caption,
			);
		}

		return $images;
	}

	/**
	 * Render search-card gallery data for inline image navigation.
	 */
	public static function render_card_gallery_data() {
		if ( ! self::should_render_card_extras() ) {
			return;
		}

		global $property;

		if ( ! $property ) {
			return;
		}

		self::render_card_gallery_data_script( self::get_property_gallery_images( $property ) );
	}

	/**
	 * Render card gallery data as JSON for the front-end script.
	 *
	 * @param array $images Image data.
	 */
	private static function render_card_gallery_data_script( $images ) {
		if ( empty( $images ) || count( $images ) < 2 ) {
			return;
		}

		$payload = array();

		foreach ( array_slice( $images, 0, 12 ) as $image ) {
			if ( empty( $image['src'] ) ) {
				continue;
			}

			$payload[] = array(
				'src'     => esc_url_raw( $image['src'] ),
				'alt'     => isset( $image['alt'] ) ? wp_strip_all_tags( $image['alt'] ) : '',
				'caption' => isset( $image['caption'] ) ? wp_strip_all_tags( $image['caption'] ) : '',
			);
		}

		if ( count( $payload ) < 2 ) {
			return;
		}

		echo '<script type="application/json" data-ph-card-gallery-data>' . wp_json_encode( $payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . '</script>';
	}

	/**
	 * Render a hero/gallery presentation using the property's attached photos.
	 */
	public static function render_demo_gallery() {
		$property = self::get_current_property();

		$template = self::get_detail_template();
		$images   = self::get_property_gallery_images( $property );

		if ( empty( $images ) ) {
			return;
		}

		$is_editorial     = ( 'premium-editorial-detail' === $template );
		$hero             = reset( $images );
		$rail             = array_slice( $images, 0, 5 );
		$count            = count( $images );
		$location         = self::get_property_location_label( $property );
		$has_floor_map    = self::should_render_floorplans( $property );
		$has_virtual_tour = self::should_render_virtual_tours( $property );
		$gallery_layout   = self::get_gallery_layout();

		echo '<div class="images ph-template-gallery ph-template-gallery-' . esc_attr( sanitize_html_class( $template ) ) . ' ph-gallery-variant-' . esc_attr( sanitize_html_class( $gallery_layout ) ) . '" data-ph-template-gallery data-ph-gallery-current-variant="' . esc_attr( $gallery_layout ) . '">';

			echo '<figure class="ph-template-gallery-hero">';
				echo '<button type="button" class="ph-template-gallery-photo-trigger" data-ph-gallery-open aria-label="' . esc_attr( sprintf(
					/* translators: %s: image label */
					__( 'Open larger photo: %s', 'propertyhive' ),
					$hero['caption']
				) ) . '">';
					echo '<img src="' . esc_url( $hero['src'] ) . '" alt="' . esc_attr( $hero['alt'] ) . '" loading="lazy" data-ph-gallery-hero-image>';
					echo '<span class="ph-template-gallery-expand-label" aria-hidden="true">' . esc_html__( 'View larger', 'propertyhive' ) . '</span>';
				echo '</button>';
				if ( $has_floor_map ) {
					echo '<div class="ph-template-gallery-panel ph-template-gallery-panel-floorplan" hidden data-ph-gallery-panel="floorplan" aria-label="' . esc_attr__( 'Floor map preview', 'propertyhive' ) . '">';
						echo '<div class="ph-template-floorplan" aria-hidden="true">';
							echo '<span class="ph-template-floorplan-room ph-template-room-reception">' . esc_html__( 'Reception', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-floorplan-room ph-template-room-kitchen">' . esc_html__( 'Kitchen', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-floorplan-room ph-template-room-bed-one">' . esc_html__( 'Bed 1', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-floorplan-room ph-template-room-bed-two">' . esc_html__( 'Bed 2', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-floorplan-room ph-template-room-bath">' . esc_html__( 'Bath', 'propertyhive' ) . '</span>';
						echo '</div>';
					echo '</div>';
				}
				if ( $has_virtual_tour ) {
					echo '<div class="ph-template-gallery-panel ph-template-gallery-panel-virtual-tour" hidden data-ph-gallery-panel="virtual-tour" aria-label="' . esc_attr__( 'Virtual tour preview', 'propertyhive' ) . '">';
						echo '<div class="ph-template-virtual-tour-preview" aria-hidden="true">';
							echo '<span class="ph-template-virtual-tour-scene ph-template-virtual-tour-scene-living">' . esc_html__( 'Living room', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-virtual-tour-scene ph-template-virtual-tour-scene-kitchen">' . esc_html__( 'Kitchen', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-virtual-tour-scene ph-template-virtual-tour-scene-bedroom">' . esc_html__( 'Bedroom', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-virtual-tour-hotspot ph-template-virtual-tour-hotspot-one"></span>';
							echo '<span class="ph-template-virtual-tour-hotspot ph-template-virtual-tour-hotspot-two"></span>';
							echo '<span class="ph-template-virtual-tour-label">' . esc_html__( '360 virtual tour', 'propertyhive' ) . '</span>';
						echo '</div>';
					echo '</div>';
				}
				if ( $location ) {
					echo '<div class="ph-template-gallery-panel ph-template-gallery-panel-map" hidden data-ph-gallery-panel="map" aria-label="' . esc_attr__( 'Map preview', 'propertyhive' ) . '"><span class="ph-template-map-pin"></span><span class="ph-template-map-label">' . esc_html( $location ) . '</span></div>';
				}

				if ( ! $is_editorial ) {
					echo '<span class="ph-template-gallery-count"><span class="ph-template-gallery-count-icon" aria-hidden="true"></span>' . esc_html( sprintf(
						/* translators: %d: number of property photos */
						_n( '%d photo', '%d photos', $count, 'propertyhive' ),
						(int) $count
					) ) . '</span>';

						echo '<div class="ph-template-gallery-tabs" role="tablist" aria-label="' . esc_attr__( 'Gallery views', 'propertyhive' ) . '">';
							echo '<button type="button" class="is-active" data-ph-gallery-tab="photos" aria-selected="true">' . esc_html__( 'Photos', 'propertyhive' ) . '</button>';
							if ( $has_floor_map ) {
								echo '<button type="button" data-ph-gallery-tab="floorplan" aria-selected="false">' . esc_html__( 'Floor map', 'propertyhive' ) . '</button>';
							}
							if ( $has_virtual_tour ) {
								echo '<button type="button" data-ph-gallery-tab="virtual-tour" aria-selected="false">' . esc_html__( 'Virtual tour', 'propertyhive' ) . '</button>';
							}
							if ( $location ) {
								echo '<button type="button" data-ph-gallery-tab="map" aria-selected="false">' . esc_html__( 'Map', 'propertyhive' ) . '</button>';
							}
						echo '</div>';
					} else {
						echo '<figcaption data-ph-gallery-caption>' . esc_html( $hero['caption'] ) . '</figcaption>';
					}
				echo '</figure>';

				if ( ! empty( $rail ) ) {
					echo '<div class="ph-template-gallery-rail">';
					foreach ( $rail as $index => $image ) {
						$is_active = ( 0 === $index );
						echo '<button type="button" class="ph-template-gallery-thumb' . ( $is_active ? ' is-active' : '' ) . '" data-ph-gallery-thumb data-src="' . esc_url( $image['src'] ) . '" data-alt="' . esc_attr( $image['alt'] ) . '" data-caption="' . esc_attr( $image['caption'] ) . '" aria-label="' . esc_attr( sprintf(
							/* translators: %s: image label */
							__( 'Show %s', 'propertyhive' ),
							$image['caption']
						) ) . '"' . ( $is_active ? ' aria-current="true"' : '' ) . '>';
							echo '<img src="' . esc_url( $image['thumb'] ) . '" alt="' . esc_attr( $image['alt'] ) . '" loading="lazy">';
							if ( $is_editorial ) {
								echo '<span>' . esc_html( $image['caption'] ) . '</span>';
							}
						echo '</button>';
					}
					echo '</div>';
				}

		echo '</div>';
	}

	/**
	 * Render Rightmove-style detail facts below the gallery.
	 */
	public static function render_detail_facts_strip() {
		if ( ! self::is_demo_preview() || ! is_property() || 'standard-sales-detail' !== self::get_detail_template() ) {
			return;
		}

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$facts = self::get_detail_facts_strip_items( $property );

		if ( empty( $facts ) ) {
			return;
		}

		echo '<section class="ph-template-detail-facts-strip ph-template-detail-facts-count-' . esc_attr( count( $facts ) ) . '" aria-label="' . esc_attr__( 'Property facts', 'propertyhive' ) . '">';
			echo '<ul>';
			foreach ( $facts as $fact ) {
				echo '<li class="ph-template-detail-fact ph-template-detail-fact-' . esc_attr( sanitize_html_class( $fact['icon'] ) ) . '">';
					echo '<span class="ph-template-detail-fact-label">' . esc_html( $fact['label'] ) . '</span>';
					echo '<span class="ph-template-detail-fact-content">';
						echo '<span class="ph-template-detail-fact-icon ph-template-detail-fact-icon-' . esc_attr( sanitize_html_class( $fact['icon'] ) ) . '" aria-hidden="true"></span>';
						echo '<span class="ph-template-detail-fact-values">';
							echo '<strong>' . esc_html( $fact['value'] ) . '</strong>';
							if ( ! empty( $fact['secondary'] ) ) {
								echo '<span>' . esc_html( $fact['secondary'] ) . '</span>';
							}
						echo '</span>';
					echo '</span>';
				echo '</li>';
			}
			echo '</ul>';
		echo '</section>';
	}

	/**
	 * Currency helper for ASCII source files.
	 *
	 * @param string $amount Amount without currency symbol.
	 * @return string
	 */
	private static function demo_price( $amount ) {
		return html_entity_decode( '&pound;' . $amount, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Curated listing content for preview pages.
	 *
	 * @param string $template Template slug.
	 * @return array
	 */
	private static function get_demo_listing( $template ) {
		$listing = array(
			'title'       => __( 'Cavendish House, Marylebone W1', 'propertyhive' ),
			'price'       => __( 'Guide price ', 'propertyhive' ) . self::demo_price( '1,250,000' ),
			'summary'     => __( 'A restored period home on a quiet Marylebone street, arranged for modern family living with generous entertaining space, calm bedrooms and a landscaped west-facing terrace.', 'propertyhive' ),
			'description' => array(
				__( 'Cavendish House sits behind cast-iron railings on a tree-lined address moments from the village shops, restaurants and transport links of Marylebone. The house has been finished with a measured hand, retaining original proportion while introducing modern lighting, fitted storage and warm natural materials throughout.', 'propertyhive' ),
				__( 'The raised ground floor opens into a double reception with tall sash windows and a working fireplace. To the rear, a full-width kitchen and dining room connects directly to the terrace, creating a natural space for day-to-day family life and informal entertaining.', 'propertyhive' ),
				__( 'The principal suite occupies the quieter upper floor and includes a dressing area and marble-lined shower room. Two further bedrooms, a family bathroom, guest cloakroom and practical utility storage complete the accommodation.', 'propertyhive' ),
			),
			'features'    => array(
				__( 'Three double bedrooms', 'propertyhive' ),
				__( 'Double reception room', 'propertyhive' ),
				__( 'Full-width kitchen and dining room', 'propertyhive' ),
				__( 'Landscaped west-facing terrace', 'propertyhive' ),
				__( 'Share of freehold', 'propertyhive' ),
				__( 'No onward chain', 'propertyhive' ),
			),
			'area'        => __( 'Marylebone', 'propertyhive' ),
			'floor_area'  => __( '1,480 sq ft / 137.5 sq m', 'propertyhive' ),
			'meta'        => array(
				__( '3 bedrooms', 'propertyhive' ),
				__( '2 bathrooms', 'propertyhive' ),
				__( '2 reception rooms', 'propertyhive' ),
				__( 'Freehold', 'propertyhive' ),
			),
			'epc_now'     => 72,
			'epc_next'    => 86,
			'connections' => array(
				array( __( 'Baker Street Underground', 'propertyhive' ), __( '0.3 miles', 'propertyhive' ) ),
				array( __( 'Marylebone station', 'propertyhive' ), __( '0.6 miles', 'propertyhive' ) ),
				array( __( 'Regent\'s Park', 'propertyhive' ), __( '0.5 miles', 'propertyhive' ) ),
				array( __( 'Marylebone High Street', 'propertyhive' ), __( '0.4 miles', 'propertyhive' ) ),
			),
		);

		if ( 'conversion-first-sales-detail' === $template ) {
			$listing['price']   = __( 'Offers over ', 'propertyhive' ) . self::demo_price( '1,250,000' );
			$listing['summary'] = __( 'A chain-free Marylebone house with immediate viewing availability, polished presentation and the right blend of period detail, outside space and practical family accommodation.', 'propertyhive' );
		}

		if ( 'premium-editorial-detail' === $template ) {
			$listing['title']       = __( 'A Restored Marylebone House with a Private Terrace', 'propertyhive' );
			$listing['summary']     = __( 'A composed period home where original volume, restored detailing and a warm contemporary finish come together in one of Marylebone\'s most walkable pockets.', 'propertyhive' );
			$listing['description'] = array(
				__( 'The approach is deliberately understated: railings, sash windows and a handsome brick facade give little away from the street. Inside, the plan opens into a sequence of calm, well-proportioned rooms designed around light, storage and a direct relationship with the terrace.', 'propertyhive' ),
				__( 'Materials have been chosen for longevity rather than effect. Stone, timber, bronze ironmongery and soft neutral wall finishes create a quietly refined setting for furniture, art and family life.', 'propertyhive' ),
				__( 'The result is a house that feels established rather than staged, with the comfort of a complete renovation and the character expected from a central London period address.', 'propertyhive' ),
			);
		}

		if ( 'lettings-detail' === $template ) {
			$listing = array(
				'title'       => __( 'Atlas Apartment, Riverside Quarter SW18', 'propertyhive' ),
				'price'       => self::demo_price( '2,450' ) . __( ' pcm', 'propertyhive' ),
				'summary'     => __( 'A furnished riverside apartment with concierge, gym access and a private balcony, positioned for quick links into the City and west London.', 'propertyhive' ),
				'description' => array(
					__( 'This fourth-floor apartment has been prepared for a professional tenant or couple seeking a well-managed building with strong amenities and easy access to the river path.', 'propertyhive' ),
					__( 'The open-plan reception and kitchen is furnished with considered pieces, integrated appliances and direct balcony access. Both bedrooms are proper doubles, with the principal bedroom benefitting from fitted wardrobes and an en suite shower room.', 'propertyhive' ),
					__( 'Residents have access to a twenty-four hour concierge, gym, secure cycle storage and landscaped communal terrace. The apartment is available furnished on a long let.', 'propertyhive' ),
				),
				'features'    => array(
					__( 'Two double bedrooms', 'propertyhive' ),
					__( 'Private balcony', 'propertyhive' ),
					__( 'Furnished', 'propertyhive' ),
					__( 'Concierge and gym', 'propertyhive' ),
					__( 'Secure cycle storage', 'propertyhive' ),
					__( 'Available on a long let', 'propertyhive' ),
				),
				'area'        => __( 'Riverside Quarter', 'propertyhive' ),
				'floor_area'  => __( '842 sq ft / 78.2 sq m', 'propertyhive' ),
				'meta'        => array(
					__( '2 bedrooms', 'propertyhive' ),
					__( '2 bathrooms', 'propertyhive' ),
					__( 'Furnished', 'propertyhive' ),
					__( 'Long let', 'propertyhive' ),
				),
				'epc_now'     => 84,
				'epc_next'    => 91,
				'connections' => array(
					array( __( 'Riverside station', 'propertyhive' ), __( '0.3 miles', 'propertyhive' ) ),
					array( __( 'City terminus', 'propertyhive' ), __( '24 min', 'propertyhive' ) ),
					array( __( 'River path', 'propertyhive' ), __( '2 min walk', 'propertyhive' ) ),
					array( __( 'Wharf shops and cafes', 'propertyhive' ), __( '0.2 miles', 'propertyhive' ) ),
				),
			);
		}

		if ( 'new-homes-development-detail' === $template ) {
			$listing = array(
				'title'       => __( 'Elm Yard, Wokingham RG40', 'propertyhive' ),
				'price'       => __( 'Prices from ', 'propertyhive' ) . self::demo_price( '485,000' ),
				'summary'     => __( 'A boutique collection of energy-efficient townhouses and apartments around a landscaped residents\' courtyard, with a furnished show home now open by appointment.', 'propertyhive' ),
				'description' => array(
					__( 'Elm Yard brings a small-scale, design-led new homes scheme to a well-connected Wokingham address, balancing brick architecture, generous glazing and planted communal spaces.', 'propertyhive' ),
					__( 'Each home includes open-plan living space, high-performance glazing, underfloor heating to principal rooms and a carefully selected kitchen and bathroom specification.', 'propertyhive' ),
					__( 'The first phase is available to reserve, with incentives on selected plots and completion dates staged across the coming season.', 'propertyhive' ),
				),
				'features'    => array(
					__( 'Boutique new homes development', 'propertyhive' ),
					__( 'One, two and three bedroom homes', 'propertyhive' ),
					__( 'Landscaped residents\' courtyard', 'propertyhive' ),
					__( 'EV charging to selected plots', 'propertyhive' ),
					__( 'High-performance glazing', 'propertyhive' ),
					__( 'Show home open by appointment', 'propertyhive' ),
				),
				'area'        => __( 'Wokingham', 'propertyhive' ),
				'floor_area'  => __( '642 to 1,280 sq ft', 'propertyhive' ),
				'meta'        => array(
					__( '1-3 bedrooms', 'propertyhive' ),
					__( 'New build', 'propertyhive' ),
					__( 'EPC A-rated', 'propertyhive' ),
					__( 'Reservation open', 'propertyhive' ),
				),
				'epc_now'     => 91,
				'epc_next'    => 96,
				'connections' => array(
					array( __( 'Wokingham station', 'propertyhive' ), __( '0.5 miles', 'propertyhive' ) ),
					array( __( 'Town centre', 'propertyhive' ), __( '0.4 miles', 'propertyhive' ) ),
					array( __( 'Primary school', 'propertyhive' ), __( '0.3 miles', 'propertyhive' ) ),
					array( __( 'Reading connection', 'propertyhive' ), __( '16 min', 'propertyhive' ) ),
				),
			);
		}

		return $listing;
	}

	/**
	 * Render preview price from the current property record.
	 */
	public static function render_demo_price() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$price = $property->get_formatted_price();

		if ( '' === $price ) {
			return;
		}

		$price_qualifier = method_exists( $property, 'get_price_qualifier' ) ? $property->get_price_qualifier() : $property->price_qualifier;
		$price_qualifier = self::format_price_qualifier_label( $price_qualifier );

		echo '<div class="price ph-template-demo-price">';
			if ( $price_qualifier ) {
				echo "\n" . '<span class="price-qualifier ph-template-price-qualifier">' . esc_html( $price_qualifier ) . '</span>';
			}
			echo "\n" . '<span class="ph-template-price-value">' . wp_kses_post( $price ) . '</span>';
		echo "\n" . '</div>';
	}

	/**
	 * Format a backend price qualifier for front-end display.
	 *
	 * @param string $price_qualifier Price qualifier.
	 * @return string
	 */
	private static function format_price_qualifier_label( $price_qualifier ) {
		$price_qualifier = trim( wp_strip_all_tags( (string) $price_qualifier ) );

		if ( '' === $price_qualifier ) {
			return '';
		}

		if ( false === strpos( $price_qualifier, ' ' ) && strtoupper( $price_qualifier ) === $price_qualifier ) {
			return $price_qualifier;
		}

		return ucfirst( strtolower( $price_qualifier ) );
	}

	/**
	 * Render preview meta from the current property record.
	 */
	public static function render_demo_meta() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		if ( 'standard-sales-detail' === self::get_detail_template() ) {
			return;
		}

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$meta = self::get_detail_meta_items( $property );

		if ( empty( $meta ) ) {
			return;
		}

		echo '<div class="property_meta ph-template-demo-meta"><ul>';
		foreach ( $meta as $item ) {
			echo '<li>' . esc_html( $item ) . '</li>';
		}
		echo '</ul></div>';
	}

	/**
	 * Render current property features in preview mode.
	 */
	public static function render_demo_features() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$features = $property->get_features();

		if ( empty( $features ) ) {
			return;
		}

		echo '<div class="features ph-template-demo-features">';
			echo '<h4>' . esc_html__( 'Key features', 'propertyhive' ) . '</h4>';
			echo '<ul>';
			foreach ( $features as $feature ) {
				echo '<li>' . esc_html( $feature ) . '</li>';
			}
			echo '</ul>';
		echo '</div>';
	}

	/**
	 * Render current property summary in preview mode.
	 */
	public static function render_demo_summary() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$summary = get_post_field( 'post_excerpt', get_queried_object_id() );

		if ( '' === trim( wp_strip_all_tags( $summary ) ) ) {
			return;
		}

		echo '<div class="summary ph-template-demo-summary">';
			echo '<h4>' . esc_html__( 'Overview', 'propertyhive' ) . '</h4>';
			echo '<div class="summary-contents">' . wp_kses_post( wpautop( $summary ) ) . '</div>';
		echo '</div>';
	}

	/**
	 * Render current property description in preview mode.
	 */
	public static function render_demo_description() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$description = $property->get_formatted_description( false );

		if ( '' === trim( wp_strip_all_tags( $description ) ) ) {
			return;
		}

		echo '<div class="description ph-template-demo-description">';
			echo '<h4>' . esc_html__( 'Full details', 'propertyhive' ) . '</h4>';
			echo '<div class="description-contents">' . wp_kses_post( $description ) . '</div>';
		echo '</div>';
	}

	/**
	 * Curated cards for archive/module preview pages.
	 *
	 * @return array
	 */
	private static function get_demo_property_cards() {
		return array(
			array(
				'image'   => 'cavendish-living-room.png',
				'badge'   => __( 'For sale', 'propertyhive' ),
				'title'   => __( 'Cavendish House, Marylebone W1', 'propertyhive' ),
				'price'   => __( 'Guide price ', 'propertyhive' ) . self::demo_price( '1,250,000' ),
				'summary' => __( 'A restored period home with generous entertaining space, calm bedrooms and a landscaped west-facing terrace.', 'propertyhive' ),
				'facts'   => array( __( '3 beds', 'propertyhive' ), __( '2 baths', 'propertyhive' ), __( '1,480 sq ft', 'propertyhive' ), __( 'Freehold', 'propertyhive' ) ),
			),
			array(
				'image'   => 'cavendish-kitchen-dining.png',
				'badge'   => __( 'New instruction', 'propertyhive' ),
				'title'   => __( 'Upper Maisonette, Devonshire Street', 'propertyhive' ),
				'price'   => self::demo_price( '925,000' ),
				'summary' => __( 'A bright two bedroom maisonette with a refined kitchen, private entrance and a quiet garden outlook.', 'propertyhive' ),
				'facts'   => array( __( '2 beds', 'propertyhive' ), __( '2 baths', 'propertyhive' ), __( 'Share of freehold', 'propertyhive' ) ),
			),
			array(
				'image'   => 'cavendish-exterior.png',
				'badge'   => __( 'Viewing slots', 'propertyhive' ),
				'title'   => __( 'Period House, Cavendish Road', 'propertyhive' ),
				'price'   => __( 'Offers over ', 'propertyhive' ) . self::demo_price( '1,175,000' ),
				'summary' => __( 'A handsome family house with balanced reception space, off-street parking and a sheltered rear garden.', 'propertyhive' ),
				'facts'   => array( __( '4 beds', 'propertyhive' ), __( '2 baths', 'propertyhive' ), __( 'Garden', 'propertyhive' ) ),
			),
			array(
				'image'   => 'atlas-apartment-living.png',
				'badge'   => __( 'To let', 'propertyhive' ),
				'title'   => __( 'Atlas Apartment, Riverside Quarter', 'propertyhive' ),
				'price'   => self::demo_price( '2,450' ) . __( ' pcm', 'propertyhive' ),
				'summary' => __( 'A furnished riverside apartment with concierge, gym access, balcony and fast links into central London.', 'propertyhive' ),
				'facts'   => array( __( '2 beds', 'propertyhive' ), __( '2 baths', 'propertyhive' ), __( 'Furnished', 'propertyhive' ) ),
			),
			array(
				'image'   => 'elm-yard-development.png',
				'badge'   => __( 'New homes', 'propertyhive' ),
				'title'   => __( 'Elm Yard, Wokingham RG40', 'propertyhive' ),
				'price'   => __( 'From ', 'propertyhive' ) . self::demo_price( '485,000' ),
				'summary' => __( 'A boutique courtyard development with efficient homes, considered materials and a furnished show home.', 'propertyhive' ),
				'facts'   => array( __( '1-3 beds', 'propertyhive' ), __( 'EPC A-rated', 'propertyhive' ), __( 'Show home open', 'propertyhive' ) ),
			),
			array(
				'image'   => 'cavendish-garden-terrace.png',
				'badge'   => __( 'Private outside space', 'propertyhive' ),
				'title'   => __( 'Garden Flat, Weymouth Street', 'propertyhive' ),
				'price'   => self::demo_price( '1,050,000' ),
				'summary' => __( 'A lateral apartment with a planted terrace, open-plan living space and a calm position close to the park.', 'propertyhive' ),
				'facts'   => array( __( '2 beds', 'propertyhive' ), __( '2 baths', 'propertyhive' ), __( 'Terrace', 'propertyhive' ) ),
			),
		);
	}

	/**
	 * Render one curated preview card.
	 *
	 * @param array $card Card data.
	 */
	private static function render_demo_property_card( $card ) {
		$agency = self::get_demo_agency();

		echo '<li class="property ph-template-card ph-template-demo-card">';
			echo '<div class="thumbnail"><a href="javascript:;" aria-label="' . esc_attr( $card['title'] ) . '"><img src="' . esc_url( self::demo_asset( $card['image'] ) ) . '" alt="' . esc_attr( $card['title'] ) . '" loading="lazy">';
				self::render_card_gallery_data_script( self::get_demo_card_gallery_images( $card ) );
			echo '</a><span class="ph-template-badges"><span class="ph-template-badge">' . esc_html( $card['badge'] ) . '</span></span></div>';
			echo '<div class="details">';
				echo '<p class="status">' . esc_html( $card['badge'] ) . '</p>';
				echo '<h3><a href="javascript:;">' . esc_html( $card['title'] ) . '</a></h3>';
				echo '<p class="price">' . esc_html( $card['price'] ) . '</p>';
				echo '<p class="property_summary">' . esc_html( $card['summary'] ) . '</p>';
			echo '</div>';
			echo '<div class="ph-template-card-footer">';
				echo '<ul class="ph-template-facts">';
				foreach ( $card['facts'] as $fact ) {
					echo '<li>' . esc_html( $fact ) . '</li>';
				}
				echo '</ul>';
				echo '<div class="ph-template-card-branch"><span>' . esc_html( $agency['office'] ) . '</span><a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $agency['phone'] ) ) . '">' . esc_html( $agency['phone'] ) . '</a></div>';
			echo '</div>';
		echo '</li>';
	}

	/**
	 * Build image data for a demo search card.
	 *
	 * @param array $card Card data.
	 * @return array
	 */
	private static function get_demo_card_gallery_images( $card ) {
		$files = array_values(
			array_unique(
				array_filter(
					array_merge(
						array( isset( $card['image'] ) ? $card['image'] : '' ),
						array(
							'cavendish-living-room.png',
							'cavendish-kitchen-dining.png',
							'cavendish-principal-bedroom.png',
							'cavendish-garden-terrace.png',
							'cavendish-exterior.png',
							'atlas-apartment-living.png',
							'elm-yard-development.png',
						)
					)
				)
			)
		);

		$images = array();

		foreach ( array_slice( $files, 0, 5 ) as $index => $file ) {
			$label = sprintf(
				/* translators: 1: property title, 2: photo number */
				__( '%1$s photo %2$d', 'propertyhive' ),
				isset( $card['title'] ) ? $card['title'] : __( 'Property', 'propertyhive' ),
				(int) $index + 1
			);

			$images[] = array(
				'src'     => self::demo_asset( $file ),
				'alt'     => $label,
				'caption' => $label,
			);
		}

		return $images;
	}

	/**
	 * Render curated cards as a Property Hive result list.
	 *
	 * @param array  $cards Cards.
	 * @param string $class Extra class.
	 */
	private static function render_demo_property_cards( $cards, $class = '' ) {
		echo '<ul class="properties ph-template-demo-results ' . esc_attr( $class ) . '">';
		foreach ( $cards as $card ) {
			self::render_demo_property_card( $card );
		}
		echo '</ul>';
	}

	/**
	 * Render curated search cards in preview mode.
	 */
	public static function render_demo_search_results() {
		if ( ! self::is_demo_preview() || ! self::is_search_preview() ) {
			return;
		}

		self::render_demo_property_cards( self::get_demo_property_cards(), 'ph-template-demo-search-results' );
	}

	/**
	 * Render supporting property modules in preview mode.
	 */
	public static function render_detail_modules() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$template       = self::get_detail_template();
		$facts          = ( 'standard-sales-detail' === $template ) ? array() : self::get_detail_meta_items( $property );
		$location_label = self::get_property_location_label( $property );
		$address        = $property->get_formatted_full_address();
		$documents      = self::get_property_document_labels( $property );
		$office         = self::get_display_office_name( $property );
		$has_floorplan  = self::should_render_floorplans( $property );

		if ( empty( $facts ) && ! $location_label && ! $address && empty( $documents ) && ! $has_floorplan ) {
			return;
		}

		echo '<section class="ph-template-modules" aria-label="' . esc_attr__( 'Property information', 'propertyhive' ) . '">';
			if ( ! empty( $facts ) ) {
				echo '<article class="ph-template-module-card ph-template-module-facts">';
					echo '<h4>' . esc_html__( 'At a glance', 'propertyhive' ) . '</h4>';
					echo '<ul class="ph-template-area-list">';
					foreach ( $facts as $fact ) {
						echo '<li><span>' . esc_html( $fact ) . '</span></li>';
					}
					echo '</ul>';
				echo '</article>';
			}

			if ( $has_floorplan ) {
				echo '<article class="ph-template-module-card ph-template-module-floorplan">';
					echo '<h4>' . esc_html__( 'Floorplan', 'propertyhive' ) . '</h4>';
					echo '<p class="ph-template-module-foot">' . esc_html__( 'Floorplan available for this property.', 'propertyhive' ) . '</p>';
				echo '</article>';
			}

			if ( $location_label || $address ) {
				echo '<article class="ph-template-module-card ph-template-module-map">';
					echo '<h4>' . esc_html__( 'Location', 'propertyhive' ) . '</h4>';
					if ( $location_label ) {
						echo '<div class="ph-template-module-map-surface" aria-hidden="true"><span class="ph-template-map-pin"></span><span class="ph-template-map-label">' . esc_html( $location_label ) . '</span></div>';
					}
					if ( $address ) {
						echo '<p class="ph-template-module-foot">' . esc_html( $address ) . '</p>';
					}
				echo '</article>';
			}

			if ( ! empty( $documents ) ) {
				echo '<article class="ph-template-module-card ph-template-module-documents">';
					echo '<h4>' . esc_html__( 'Documents and viewing', 'propertyhive' ) . '</h4>';
					echo '<div class="ph-template-doc-row">';
					foreach ( $documents as $document ) {
						echo '<span class="ph-template-doc-pill ph-template-doc-pill-' . esc_attr( sanitize_html_class( $document['type'] ) ) . '"><span class="ph-template-doc-icon" aria-hidden="true"></span>' . esc_html( $document['label'] ) . '</span>';
					}
					echo '</div>';
					if ( $office ) {
						echo '<p class="ph-template-module-foot">' . sprintf(
							/* translators: %s: office name */
							esc_html__( 'Available from %s.', 'propertyhive' ),
							esc_html( $office )
						) . '</p>';
					}
				echo '</article>';
			}
		echo '</section>';
	}

	/**
	 * Render a similar-properties strip in preview mode.
	 */
	public static function render_similar_properties() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$settings = self::get_settings();
		if ( 'yes' !== $settings['template_set_show_recommended'] && ! self::is_template_editor_active() ) {
			return;
		}

		$current_property = self::get_current_property();

		if ( ! $current_property ) {
			return;
		}

		$display_count = self::get_recommended_property_count();
		$query_count   = self::is_template_editor_active() ? self::get_recommended_property_max_count() : $display_count;
		$image_size    = self::get_recommended_property_wp_image_size();
		$layout        = self::get_recommended_property_layout();
		$image_style   = self::get_recommended_property_image_size();
		$properties    = self::get_nearby_similar_property_ids( $current_property, $query_count );

		if ( empty( $properties ) ) {
			return;
		}

		echo '<section class="ph-template-similar-properties ph-template-similar-layout-' . esc_attr( sanitize_html_class( $layout ) ) . ' ph-template-similar-images-' . esc_attr( sanitize_html_class( $image_style ) ) . '" aria-label="' . esc_attr__( 'Similar properties', 'propertyhive' ) . '" data-ph-recommended-properties>';
			echo '<div class="ph-template-section-heading">';
				echo '<p>' . esc_html__( 'Also available', 'propertyhive' ) . '</p>';
				echo '<h2>' . esc_html__( 'Similar homes nearby', 'propertyhive' ) . '</h2>';
			echo '</div>';
			echo '<div class="ph-template-similar-grid" data-ph-recommended-grid>';
			foreach ( $properties as $index => $property_id ) {
				$related = new PH_Property( $property_id );
				$image   = $related->get_main_photo_src( $image_size );
				$price   = $related->get_formatted_price();
				$facts   = self::get_fact_summary( $related );
				$title   = get_the_title( $property_id );
				$url     = get_permalink( $property_id );
				$hidden  = $index >= $display_count ? ' hidden' : '';

				echo '<article class="ph-template-similar-card" data-ph-recommended-card data-ph-recommended-index="' . esc_attr( (string) $index ) . '"' . $hidden . '>';
					if ( $image ) {
						echo '<a href="' . esc_url( $url ) . '"><img src="' . esc_url( $image ) . '" alt="' . esc_attr( $title ) . '" loading="lazy"></a>';
					} elseif ( ph_placeholder_img_src() ) {
						echo '<a href="' . esc_url( $url ) . '"><img src="' . esc_url( ph_placeholder_img_src() ) . '" alt="' . esc_attr( $title ) . '" loading="lazy"></a>';
					}
					echo '<div>';
						echo '<h3><a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a></h3>';
						if ( $price ) {
							echo '<p>' . wp_kses_post( $price ) . '</p>';
						}
						if ( $facts ) {
							echo '<span>' . esc_html( $facts ) . '</span>';
						}
					echo '</div>';
				echo '</article>';
			}
			echo '</div>';
		echo '</section>';
	}

	/**
	 * Get nearby/similar properties for the template-set detail preview.
	 *
	 * @param PH_Property $property Current property object.
	 * @return array Property IDs.
	 */
	private static function get_nearby_similar_property_ids( $property, $limit = null ) {
		$property_id = absint( isset( $property->id ) ? $property->id : get_queried_object_id() );

		if ( ! $property_id ) {
			return array();
		}

		$limit = null === $limit ? self::get_recommended_property_count() : absint( $limit );
		$limit = absint( apply_filters( 'propertyhive_template_set_similar_properties_limit', $limit, $property ) );

		if ( ! $limit ) {
			return array();
		}

		$selected = array();
		$tiers    = array(
			'location'      => self::get_location_term_ids( $property_id ),
			'address_three' => sanitize_text_field( get_post_meta( $property_id, '_address_three', true ) ),
			'address_four'  => sanitize_text_field( get_post_meta( $property_id, '_address_four', true ) ),
			'postcode'      => self::get_outward_postcode( get_post_meta( $property_id, '_address_postcode', true ) ),
			'fallback'      => true,
		);

		foreach ( $tiers as $tier => $tier_value ) {
			if ( empty( $tier_value ) ) {
				continue;
			}

			$remaining = $limit - count( $selected );

			if ( $remaining <= 0 ) {
				break;
			}

			$matches = self::query_nearby_similar_properties( $property, $tier, $tier_value, $remaining, $selected );

			foreach ( $matches as $match_id ) {
				$match_id = absint( $match_id );

				if ( $match_id && $match_id !== $property_id && ! in_array( $match_id, $selected, true ) ) {
					$selected[] = $match_id;
				}
			}
		}

		$selected = array_slice( $selected, 0, $limit );
		$selected = apply_filters( 'propertyhive_template_set_similar_properties_ids', $selected, $property, $limit );
		$selected = array_values( array_unique( array_filter( array_map( 'absint', (array) $selected ) ) ) );
		$selected = array_values( array_diff( $selected, array( $property_id ) ) );

		return array_slice( $selected, 0, $limit );
	}

	/**
	 * Get the selected recommended-property count.
	 *
	 * @return int
	 */
	private static function get_recommended_property_count() {
		$settings = self::get_settings();
		$count    = absint( $settings['template_set_recommended_count'] );

		return isset( self::get_recommended_property_counts()[ $count ] ) ? $count : 3;
	}

	/**
	 * Get the largest recommended-property count available in the editor.
	 *
	 * @return int
	 */
	private static function get_recommended_property_max_count() {
		return max( array_map( 'absint', array_keys( self::get_recommended_property_counts() ) ) );
	}

	/**
	 * Get the selected recommended-property layout.
	 *
	 * @return string
	 */
	private static function get_recommended_property_layout() {
		$settings = self::get_settings();
		$layout   = sanitize_title( $settings['template_set_recommended_layout'] );

		return isset( self::get_recommended_property_layouts()[ $layout ] ) ? $layout : 'grid';
	}

	/**
	 * Get the selected recommended-property image treatment.
	 *
	 * @return string
	 */
	private static function get_recommended_property_image_size() {
		$settings   = self::get_settings();
		$image_size = sanitize_title( $settings['template_set_recommended_image_size'] );

		return isset( self::get_recommended_property_image_sizes()[ $image_size ] ) ? $image_size : 'standard';
	}

	/**
	 * Map the recommended-property image treatment to a WordPress image size.
	 *
	 * @return string
	 */
	private static function get_recommended_property_wp_image_size() {
		$image_size = self::get_recommended_property_image_size();

		if ( 'compact' === $image_size ) {
			return 'medium';
		}

		if ( 'large' === $image_size ) {
			return 'large';
		}

		return 'medium_large';
	}

	/**
	 * Query one matching tier for nearby/similar properties.
	 *
	 * @param PH_Property       $property Current property object.
	 * @param string            $tier Matching tier name.
	 * @param string|array|bool $tier_value Value for the matching tier.
	 * @param int               $limit Number of properties to fetch.
	 * @param array             $selected Already selected property IDs.
	 * @return array Property IDs.
	 */
	private static function query_nearby_similar_properties( $property, $tier, $tier_value, $limit, $selected ) {
		$property_id = absint( isset( $property->id ) ? $property->id : get_queried_object_id() );
		$department  = get_post_meta( $property_id, '_department', true );
		$meta_query  = array(
			array(
				'key'   => '_on_market',
				'value' => 'yes',
			),
		);

		if ( '' !== $department ) {
			$meta_query[] = array(
				'key'   => '_department',
				'value' => $department,
			);
		}

		$args = array(
			'post_type'           => 'property',
			'post_status'         => self::get_similar_property_post_statuses(),
			'posts_per_page'      => $limit,
			'post__not_in'        => array_values( array_unique( array_merge( array( $property_id ), array_map( 'absint', $selected ) ) ) ),
			'fields'              => 'ids',
			'ignore_sticky_posts' => 1,
			'has_password'        => false,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'meta_query'          => $meta_query,
		);

		if ( 'location' === $tier ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'location',
					'terms'    => array_map( 'absint', (array) $tier_value ),
					'operator' => 'IN',
				),
			);
		} elseif ( 'address_three' === $tier ) {
			$args['meta_query'][] = array(
				'key'   => '_address_three',
				'value' => $tier_value,
			);
		} elseif ( 'address_four' === $tier ) {
			$args['meta_query'][] = array(
				'key'   => '_address_four',
				'value' => $tier_value,
			);
		} elseif ( 'postcode' === $tier ) {
			$args['meta_query'][] = array(
				'key'     => '_address_postcode',
				'value'   => $tier_value,
				'compare' => 'LIKE',
			);
		}

		$args = apply_filters( 'propertyhive_template_set_similar_properties_query_args', $args, $property, $tier, $selected );

		if ( ! is_array( $args ) || empty( $args ) ) {
			return array();
		}

		$posts = get_posts( $args );
		$ids   = array();

		foreach ( (array) $posts as $post ) {
			$ids[] = is_object( $post ) && isset( $post->ID ) ? $post->ID : $post;
		}

		return array_values( array_filter( array_map( 'absint', $ids ) ) );
	}

	/**
	 * Get post statuses visible to the current visitor.
	 *
	 * @return string|array
	 */
	private static function get_similar_property_post_statuses() {
		return ( is_user_logged_in() && current_user_can( 'manage_propertyhive' ) ) ? array( 'publish', 'private' ) : 'publish';
	}

	/**
	 * Get assigned location term IDs for a property.
	 *
	 * @param int $property_id Property ID.
	 * @return array
	 */
	private static function get_location_term_ids( $property_id ) {
		if ( ! taxonomy_exists( 'location' ) ) {
			return array();
		}

		$terms = wp_get_post_terms( $property_id, 'location', array( 'fields' => 'ids' ) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		return array_values( array_filter( array_map( 'absint', $terms ) ) );
	}

	/**
	 * Get a useful outward postcode prefix for nearby matching.
	 *
	 * @param string $postcode Full postcode.
	 * @return string
	 */
	private static function get_outward_postcode( $postcode ) {
		$postcode = strtoupper( trim( (string) $postcode ) );

		if ( '' === $postcode ) {
			return '';
		}

		if ( false !== strpos( $postcode, ' ' ) ) {
			$parts = preg_split( '/\s+/', $postcode );
			return sanitize_text_field( $parts[0] );
		}

		if ( strlen( $postcode ) > 3 ) {
			return sanitize_text_field( substr( $postcode, 0, -3 ) );
		}

		return sanitize_text_field( $postcode );
	}

	/**
	 * Render the featured/homepage module preview on the property archive.
	 */
	public static function render_module_preview() {
		if ( ! self::is_enabled() || ! self::is_module_preview() ) {
			return;
		}

		echo '<section class="ph-template-module-preview">';
			echo '<div class="ph-template-module-preview-copy">';
				echo '<span>' . esc_html__( 'Property search', 'propertyhive' ) . '</span>';
				echo '<h1>' . esc_html__( 'Find your next home', 'propertyhive' ) . '</h1>';
				echo '<p>' . esc_html__( 'Search by location, price and type, or browse a selection of homes currently available through our offices.', 'propertyhive' ) . '</p>';
			echo '</div>';
			echo self::featured_template_shortcode(
				array(
					'title'       => __( 'Featured properties', 'propertyhive' ),
					'intro'       => __( 'A selection of homes currently available through our offices.', 'propertyhive' ),
					'show_search' => 'yes',
					'source'      => 'properties',
					'per_page'    => 3,
					'columns'     => 3,
				)
			);
		echo '</section>';
	}

	/**
	 * Hide archive results when rendering only a module preview.
	 *
	 * @param bool $show_results Existing state.
	 * @return bool
	 */
	public static function maybe_hide_results_for_module_preview( $show_results ) {
		return ( self::is_module_preview() || self::is_search_preview() ) ? false : $show_results;
	}

	/**
	 * Hide archive title when rendering only a module preview.
	 *
	 * @param bool $show_title Existing state.
	 * @return bool
	 */
	public static function maybe_hide_title_for_module_preview( $show_title ) {
		return self::is_module_preview() ? false : $show_title;
	}

	/**
	 * Render card badges over thumbnails.
	 */
	public static function render_card_badges() {
		if ( ! self::should_render_card_extras() ) {
			return;
		}

		$settings = self::get_settings();
		if ( 'yes' !== $settings['template_set_show_badges'] && ! self::is_template_editor_active() ) {
			return;
		}

		global $property;

		if ( ! $property ) {
			return;
		}

		$badges = array();

		if ( 'yes' === $property->featured ) {
			$badges[] = __( 'Featured', 'propertyhive' );
		}

		if ( $property->availability ) {
			$badges[] = $property->availability;
		}

		if ( $property->marketing_flag ) {
			$badges[] = $property->marketing_flag;
		}

		$photo_count = self::get_photo_count( $property );
		if ( $photo_count > 1 ) {
			$badges[] = sprintf(
				/* translators: %d: number of property photos */
				__( '%d photos', 'propertyhive' ),
				$photo_count
			);
		}

		$badges = array_slice( array_unique( array_filter( $badges ) ), 0, 3 );

		if ( empty( $badges ) ) {
			return;
		}

		echo '<span class="ph-template-badges">';
		foreach ( $badges as $badge ) {
			echo '<span class="ph-template-badge">' . esc_html( $badge ) . '</span>';
		}
		echo '</span>';
	}

	/**
	 * Render card footer with branch contact and facts.
	 */
	public static function render_card_footer() {
		if ( ! self::should_render_card_extras() ) {
			return;
		}

		global $property;

		if ( ! $property ) {
			return;
		}

		$settings = self::get_settings();
		$facts       = self::get_fact_items( $property, self::get_search_fact_limit() );
		$phone       = $property->get_negotiator_telephone_number();
		$show_branch = 'yes' === $settings['template_set_show_branch'] || self::is_template_editor_active();

		if ( empty( $facts ) && ! $show_branch ) {
			return;
		}

		echo '<div class="ph-template-card-footer">';
			if ( ! empty( $facts ) ) {
				echo '<ul class="ph-template-facts">';
				foreach ( $facts as $fact ) {
					echo '<li><span>' . esc_html( $fact['label'] ) . '</span> ' . esc_html( $fact['value'] ) . '</li>';
				}
				echo '</ul>';
			}

			$office = self::get_display_office_name( $property );

			if ( $show_branch && ( $office || $phone ) ) {
				echo '<div class="ph-template-card-branch">';
					if ( $office ) {
						echo '<span>' . esc_html( $office ) . '</span>';
					}
					if ( $phone ) {
						echo '<a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a>';
					}
				echo '</div>';
			}
		echo '</div>';
	}

	/**
	 * Render single-property contact panel.
	 */
	public static function render_detail_contact_panel() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		global $post;

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$template = self::get_detail_template();
		$button   = self::get_primary_cta_label( $property, $template );
		$hint     = self::get_contact_hint( $template );
		$is_demo  = self::is_demo_preview();
		$phone    = $property->get_negotiator_telephone_number();
		$email    = $property->get_negotiator_email_address();
		$office   = $property->get_office_name();
		$address  = $property->get_office_address();
		$agent    = $property->get_negotiator_name();
		$portrait = $property->get_negotiator_photo();

		$office_alt = $office ? $office : __( 'Agent', 'propertyhive' );
		$agent_role = self::get_contact_agent_role( $agent, $office_alt, $office );

		echo '<aside class="ph-template-detail-contact-card' . ( $is_demo ? ' is-demo' : '' ) . '" aria-label="' . esc_attr__( 'Property contact', 'propertyhive' ) . '">';

			if ( $agent || $portrait ) {
				echo '<div class="ph-template-contact-agent">';
					if ( $portrait ) {
						echo '<span class="ph-template-contact-portrait ph-template-contact-portrait-image">' . wp_kses_post( $portrait ) . '</span>';
					} elseif ( $agent ) {
						echo '<span class="ph-template-contact-portrait ph-template-contact-portrait-initials" aria-hidden="true">' . esc_html( self::get_contact_agent_initials( $agent ) ) . '</span>';
					}
					echo '<span class="ph-template-contact-agent-meta">';
						if ( $agent ) {
							echo '<span class="ph-template-contact-agent-name">' . esc_html( $agent ) . '</span>';
						}
						if ( $agent_role ) {
							echo '<span class="ph-template-contact-agent-role">' . esc_html( $agent_role ) . '</span>';
						}
					echo '</span>';
				echo '</div>';
			}

			echo '<p class="ph-template-contact-kicker">' . esc_html__( 'Marketed by', 'propertyhive' ) . '</p>';
			echo '<h2>' . esc_html( $office_alt ) . '</h2>';

			if ( $address ) {
				echo '<p class="ph-template-contact-address">' . esc_html( $address ) . '</p>';
			}

			if ( $hint ) {
				echo '<p class="ph-template-contact-hint">' . esc_html( $hint ) . '</p>';
			}

			echo '<div class="ph-template-contact-actions">';
				if ( $phone ) {
					echo '<a class="ph-template-button ph-template-button-primary" href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html__( 'Call agent', 'propertyhive' ) . '</a>';
				}

				echo '<a class="ph-template-button ' . esc_attr( $phone ? 'ph-template-button-secondary' : 'ph-template-button-primary' ) . '" data-fancybox data-src="#makeEnquiry' . (int) $post->ID . '" href="javascript:;">' . esc_html( $button ) . '</a>';

				if ( $email ) {
					echo '<a class="ph-template-contact-link" href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html__( 'Email agent', 'propertyhive' ) . '</a>';
				}
			echo '</div>';

			self::render_detail_media_links( $property );
		echo '</aside>';
	}

	/**
	 * Render a small detail label before the title.
	 */
	public static function render_detail_template_kicker() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		global $property;

		if ( ! $property ) {
			return;
		}

		$template = self::get_detail_template();
		$label    = self::get_detail_kicker_label( $property, $template );

		if ( ! $label ) {
			return;
		}

		echo '<p class="ph-template-detail-kicker ph-template-detail-kicker-' . esc_attr( sanitize_html_class( $template ) ) . '">' . esc_html( $label ) . '</p>';
	}

	/**
	 * Render template-specific detail highlights.
	 */
	public static function render_detail_highlights() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		global $property;

		if ( ! $property ) {
			return;
		}

		$template   = self::get_detail_template();
		$highlights = self::get_detail_highlights( $property, $template );

		if ( empty( $highlights ) ) {
			return;
		}

		echo '<ul class="ph-template-detail-highlights ph-template-detail-highlights-' . esc_attr( sanitize_html_class( $template ) ) . '">';
		foreach ( $highlights as $highlight ) {
			echo '<li><span>' . esc_html( $highlight['label'] ) . '</span> ' . esc_html( $highlight['value'] ) . '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Render a context panel for detail templates that need extra emphasis.
	 */
	public static function render_detail_context_panel() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		global $property;

		if ( ! $property ) {
			return;
		}

		$template = self::get_detail_template();
		$panel    = self::get_detail_context_panel( $property, $template );

		if ( empty( $panel ) ) {
			return;
		}

		echo '<section class="ph-template-detail-context-panel ph-template-detail-context-panel-' . esc_attr( sanitize_html_class( $template ) ) . '">';
			echo '<p class="ph-template-panel-kicker">' . esc_html( $panel['kicker'] ) . '</p>';
			echo '<h2>' . esc_html( $panel['title'] ) . '</h2>';
			echo '<p>' . esc_html( $panel['body'] ) . '</p>';

			if ( ! empty( $panel['items'] ) ) {
				echo '<dl class="ph-template-panel-list">';
				foreach ( $panel['items'] as $item ) {
					echo '<div>';
						echo '<dt>' . esc_html( $item['label'] ) . '</dt>';
						echo '<dd>' . esc_html( $item['value'] ) . '</dd>';
					echo '</div>';
				}
				echo '</dl>';
			}
		echo '</section>';
	}

	/**
	 * Render sticky mobile CTA bar.
	 */
	public static function render_mobile_cta_bar() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		$settings = self::get_settings();
		if ( 'yes' !== $settings['template_set_show_mobile_cta'] && ! self::is_template_editor_active() ) {
			return;
		}

		global $post, $property;

		if ( ! $property ) {
			return;
		}

		$phone    = $property->get_negotiator_telephone_number();
		$template = self::get_detail_template();
		$button   = self::get_primary_cta_label( $property, $template );

		echo '<div class="ph-template-mobile-cta" aria-label="' . esc_attr__( 'Property actions', 'propertyhive' ) . '">';
			if ( $phone ) {
				echo '<a class="ph-template-button ph-template-button-secondary" href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html__( 'Call', 'propertyhive' ) . '</a>';
			}
			echo '<a class="ph-template-button ph-template-button-primary" data-fancybox data-src="#makeEnquiry' . (int) $post->ID . '" href="javascript:;">' . esc_html( $button ) . '</a>';
		echo '</div>';
	}

	/**
	 * Render short trust note near enquiry actions.
	 */
	public static function render_trust_note() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		echo '<p class="ph-template-trust-note">' . esc_html__( 'Your details are sent to the agent handling this property.', 'propertyhive' ) . '</p>';
	}

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

		self::$rendering_module = true;

		ob_start();

		echo '<section class="propertyhive ph-template-set ph-template-featured-module ph-home-template-featured-properties-homepage-module">';
			echo '<div class="ph-template-module-header">';
				if ( '' !== $atts['title'] ) {
					echo '<h2>' . esc_html( $atts['title'] ) . '</h2>';
				}
				if ( '' !== $atts['intro'] ) {
					echo '<p>' . esc_html( $atts['intro'] ) . '</p>';
				}
			echo '</div>';

			if ( 'yes' === $atts['show_search'] ) {
				echo '<div class="ph-template-module-search">';
				echo PH_Shortcodes::property_search_form( array( 'id' => 'template-module', 'default_department' => $atts['department'] ) );
				echo '</div>';
			}

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

			if ( $atts['button_url'] && $atts['button_text'] ) {
				echo '<p class="ph-template-module-action"><a class="ph-template-button ph-template-button-primary" href="' . esc_url( $atts['button_url'] ) . '">' . esc_html( $atts['button_text'] ) . '</a></p>';
			}
		echo '</section>';

		self::$rendering_module = false;

		return ob_get_clean();
	}

	/**
	 * Render the front-end template editor shell.
	 */
	public static function render_template_editor() {
		if ( ! self::is_template_editor_active() ) {
			return;
		}

		$settings     = self::get_settings();
		$context      = self::get_template_editor_context();
		$exit_url     = add_query_arg( self::EDIT_CLOSED_QUERY_ARG, '1', remove_query_arg( self::EDIT_QUERY_ARG, self::get_current_url() ) );
		$settings_url = admin_url( 'admin.php?page=ph-settings&tab=frontend&section=template-set' );

		echo '<aside class="ph-template-editor ph-template-editor-' . esc_attr( sanitize_html_class( $context ) ) . '" data-ph-template-editor data-ph-template-editor-context="' . esc_attr( $context ) . '" aria-label="' . esc_attr__( 'Template editor', 'propertyhive' ) . '">';
			echo '<form class="ph-template-editor-form" data-ph-template-editor-form>';
				echo '<header class="ph-template-editor-header">';
					echo '<div>';
						echo '<span>' . esc_html__( 'Property Hive', 'propertyhive' ) . '</span>';
						echo '<h2>' . esc_html( self::get_template_editor_title( $context ) ) . '</h2>';
					echo '</div>';
					echo '<a href="' . esc_url( $exit_url ) . '" aria-label="' . esc_attr__( 'Exit template editor', 'propertyhive' ) . '">&times;</a>';
				echo '</header>';

				echo '<input type="hidden" name="template_set_enabled" value="yes">';
				echo '<input type="hidden" name="template_set_editor_mode" value="' . esc_attr( self::EDITOR_MODE_VISUAL ) . '">';
				echo '<input type="hidden" name="template_set_editor_context" value="' . esc_attr( $context ) . '">';

				self::render_template_editor_section_start( __( 'Template', 'propertyhive' ) );
				if ( 'search' === $context ) {
					self::render_template_editor_hidden( 'template_set_detail_template', self::get_detail_template() );
					self::render_template_editor_select( 'template_set_search_template', __( 'Search results', 'propertyhive' ), self::get_search_templates(), self::get_search_template(), self::get_template_editor_preview_urls( self::get_search_templates() ) );
				} else {
					self::render_template_editor_hidden( 'template_set_search_template', self::get_search_template() );
					self::render_template_editor_select( 'template_set_detail_template', __( 'Detail template', 'propertyhive' ), self::get_detail_templates(), self::get_detail_template(), self::get_template_editor_preview_urls( self::get_detail_templates() ) );
				}
				self::render_template_editor_section_end();

				if ( 'search' === $context ) {
					self::render_template_editor_hidden( 'template_set_gallery_layout', $settings['template_set_gallery_layout'] );
					self::render_template_editor_hidden( 'template_set_button_style', $settings['template_set_button_style'] );
					self::render_template_editor_hidden( 'template_set_contact_card_style', $settings['template_set_contact_card_style'] );
					self::render_template_editor_hidden( 'template_set_show_mobile_cta', $settings['template_set_show_mobile_cta'] );
					self::render_template_editor_hidden( 'template_set_show_floorplans', $settings['template_set_show_floorplans'] );
					self::render_template_editor_hidden( 'template_set_show_virtual_tours', $settings['template_set_show_virtual_tours'] );
					self::render_template_editor_hidden( 'template_set_show_recommended', $settings['template_set_show_recommended'] );
					self::render_template_editor_hidden( 'template_set_recommended_count', $settings['template_set_recommended_count'] );
					self::render_template_editor_hidden( 'template_set_recommended_layout', $settings['template_set_recommended_layout'] );
					self::render_template_editor_hidden( 'template_set_recommended_image_size', $settings['template_set_recommended_image_size'] );

					self::render_template_editor_section_start( __( 'Results cards', 'propertyhive' ) );
					self::render_template_editor_select( 'template_set_card_density', __( 'Cards', 'propertyhive' ), self::get_card_densities(), $settings['template_set_card_density'] );
					self::render_template_editor_select( 'template_set_image_style', __( 'Images', 'propertyhive' ), self::get_image_styles(), $settings['template_set_image_style'] );
					self::render_template_editor_checkbox( 'template_set_show_branch', __( 'Branch details', 'propertyhive' ), $settings['template_set_show_branch'] );
					self::render_template_editor_checkbox( 'template_set_show_badges', __( 'Property badges', 'propertyhive' ), $settings['template_set_show_badges'] );
					self::render_template_editor_section_end();
				} else {
					self::render_template_editor_hidden( 'template_set_card_density', $settings['template_set_card_density'] );
					self::render_template_editor_hidden( 'template_set_image_style', $settings['template_set_image_style'] );
					self::render_template_editor_hidden( 'template_set_show_branch', $settings['template_set_show_branch'] );
					self::render_template_editor_hidden( 'template_set_show_badges', $settings['template_set_show_badges'] );

					self::render_template_editor_section_start( __( 'Gallery', 'propertyhive' ) );
						echo '<div class="ph-template-editor-segmented" role="radiogroup" aria-label="' . esc_attr__( 'Gallery layout', 'propertyhive' ) . '">';
							foreach ( self::get_gallery_layouts() as $layout => $label ) {
								echo '<label class="' . ( $layout === $settings['template_set_gallery_layout'] ? 'is-active' : '' ) . '">';
									echo '<input type="radio" name="template_set_gallery_layout" value="' . esc_attr( $layout ) . '"' . checked( $layout, $settings['template_set_gallery_layout'], false ) . ' data-ph-template-editor-control>';
									echo '<span>' . esc_html( $label ) . '</span>';
								echo '</label>';
							}
						echo '</div>';
					self::render_template_editor_section_end();

					self::render_template_editor_section_start( __( 'Property page', 'propertyhive' ) );
					self::render_template_editor_select( 'template_set_button_style', __( 'Buttons', 'propertyhive' ), self::get_button_styles(), $settings['template_set_button_style'] );
					self::render_template_editor_select( 'template_set_contact_card_style', __( 'Contact card', 'propertyhive' ), self::get_contact_card_styles(), $settings['template_set_contact_card_style'] );
					self::render_template_editor_checkbox( 'template_set_show_mobile_cta', __( 'Mobile enquiry bar', 'propertyhive' ), $settings['template_set_show_mobile_cta'] );
					self::render_template_editor_checkbox( 'template_set_show_floorplans', __( 'Show floorplans', 'propertyhive' ), $settings['template_set_show_floorplans'] );
					self::render_template_editor_checkbox( 'template_set_show_virtual_tours', __( 'Show virtual tours', 'propertyhive' ), $settings['template_set_show_virtual_tours'] );
					self::render_template_editor_section_end();

					self::render_template_editor_section_start( __( 'Recommended homes', 'propertyhive' ) );
					self::render_template_editor_checkbox( 'template_set_show_recommended', __( 'Show recommended homes', 'propertyhive' ), $settings['template_set_show_recommended'] );
					self::render_template_editor_select( 'template_set_recommended_count', __( 'Number shown', 'propertyhive' ), self::get_recommended_property_counts(), $settings['template_set_recommended_count'] );
					self::render_template_editor_select( 'template_set_recommended_layout', __( 'Layout', 'propertyhive' ), self::get_recommended_property_layouts(), $settings['template_set_recommended_layout'] );
					self::render_template_editor_select( 'template_set_recommended_image_size', __( 'Images', 'propertyhive' ), self::get_recommended_property_image_sizes(), $settings['template_set_recommended_image_size'] );
					self::render_template_editor_section_end();
				}

				echo '<footer class="ph-template-editor-footer">';
					echo '<span data-ph-template-editor-status>' . esc_html__( 'Ready', 'propertyhive' ) . '</span>';
					echo '<div>';
						echo '<a class="ph-template-editor-secondary" href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'propertyhive' ) . '</a>';
						echo '<button type="submit" class="ph-template-editor-save" data-ph-template-editor-save>' . esc_html__( 'Save', 'propertyhive' ) . '</button>';
					echo '</div>';
				echo '</footer>';
			echo '</form>';
		echo '</aside>';
	}

	/**
	 * Get the current visual editor page context.
	 *
	 * @return string
	 */
	private static function get_template_editor_context() {
		return is_post_type_archive( 'property' ) ? 'search' : 'detail';
	}

	/**
	 * Get the editor title for the current page context.
	 *
	 * @param string $context Editor context.
	 * @return string
	 */
	private static function get_template_editor_title( $context ) {
		return 'search' === $context ? __( 'Search template editor', 'propertyhive' ) : __( 'Property template editor', 'propertyhive' );
	}

	/**
	 * Render editor section start.
	 *
	 * @param string $title Section title.
	 */
	private static function render_template_editor_section_start( $title ) {
		echo '<section class="ph-template-editor-section">';
			echo '<h3>' . esc_html( $title ) . '</h3>';
	}

	/**
	 * Render editor section end.
	 */
	private static function render_template_editor_section_end() {
		echo '</section>';
	}

	/**
	 * Render an editor select control.
	 *
	 * @param string $name Control name.
	 * @param string $label Control label.
	 * @param array  $options Options.
	 * @param string $selected Selected value.
	 */
	private static function render_template_editor_select( $name, $label, $options, $selected, $option_urls = array() ) {
		echo '<label class="ph-template-editor-field">';
			echo '<span>' . esc_html( $label ) . '</span>';
			echo '<select name="' . esc_attr( $name ) . '" data-ph-template-editor-control>';
				foreach ( $options as $value => $option_label ) {
					$option_url = isset( $option_urls[ $value ] ) ? $option_urls[ $value ] : '';
					echo '<option value="' . esc_attr( $value ) . '"' . selected( $value, $selected, false ) . ( $option_url ? ' data-ph-template-preview-url="' . esc_url( $option_url ) . '"' : '' ) . '>' . esc_html( $option_label ) . '</option>';
				}
			echo '</select>';
		echo '</label>';
	}

	/**
	 * Build editor preview URLs for a template select.
	 *
	 * @param array $templates Template choices.
	 * @return array
	 */
	private static function get_template_editor_preview_urls( $templates ) {
		$urls = array();

		foreach ( $templates as $template => $label ) {
			$urls[ $template ] = add_query_arg( self::EDIT_QUERY_ARG, '1', self::get_template_preview_url( $template ) );
		}

		return $urls;
	}

	/**
	 * Preserve a setting when it is not shown in the current editor context.
	 *
	 * @param string $name  Control name.
	 * @param string $value Current value.
	 */
	private static function render_template_editor_hidden( $name, $value ) {
		echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
	}

	/**
	 * Render an editor checkbox control.
	 *
	 * @param string $name Control name.
	 * @param string $label Control label.
	 * @param string $value Current value.
	 */
	private static function render_template_editor_checkbox( $name, $label, $value ) {
		echo '<label class="ph-template-editor-toggle">';
			echo '<input type="checkbox" name="' . esc_attr( $name ) . '" value="yes"' . checked( 'yes', $value, false ) . ' data-ph-template-editor-control>';
			echo '<span>' . esc_html( $label ) . '</span>';
		echo '</label>';
	}

	/**
	 * Save template editor settings.
	 */
	public static function ajax_save_template_editor() {
		if ( ! self::can_manage_template_set() ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to edit templates.', 'propertyhive' ) ), 403 );
		}

		check_ajax_referer( self::EDITOR_NONCE_ACTION, 'security' );

		$current_settings = get_option( 'propertyhive_template_assistant', array() );
		$settings         = self::sanitize_template_set_settings( $_POST, $current_settings, true );

		update_option( 'propertyhive_template_assistant', $settings );

		wp_send_json_success(
			array(
				'message'  => __( 'Template saved.', 'propertyhive' ),
				'settings' => self::get_public_settings( $settings ),
			)
		);
	}

	/**
	 * Front-end script data.
	 *
	 * @return array
	 */
	private static function get_script_data() {
		$settings = self::get_settings();

		return array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'security'     => wp_create_nonce( self::EDITOR_NONCE_ACTION ),
			'editorActive' => self::is_template_editor_active(),
			'editorMode'   => $settings['template_set_editor_mode'],
			'settings'     => self::get_public_settings( $settings ),
			'labels'       => array(
				'ready'   => __( 'Ready', 'propertyhive' ),
				'changed' => __( 'Unsaved changes', 'propertyhive' ),
				'loading' => __( 'Loading...', 'propertyhive' ),
				'saving'  => __( 'Saving...', 'propertyhive' ),
				'saved'   => __( 'Saved', 'propertyhive' ),
				'error'   => __( 'Could not save', 'propertyhive' ),
			),
		);
	}

	/**
	 * Public settings for editor JavaScript.
	 *
	 * @param array $settings Settings.
	 * @return array
	 */
	private static function get_public_settings( $settings ) {
		$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), self::get_settings() );

		return array(
			'template_set_detail_template'        => sanitize_title( $settings['template_set_detail_template'] ),
			'template_set_search_template'        => sanitize_title( $settings['template_set_search_template'] ),
			'template_set_gallery_layout'         => sanitize_title( $settings['template_set_gallery_layout'] ),
			'template_set_brand_colour'           => sanitize_hex_color( $settings['template_set_brand_colour'] ),
			'template_set_accent_colour'          => sanitize_hex_color( $settings['template_set_accent_colour'] ),
			'template_set_button_style'           => sanitize_title( $settings['template_set_button_style'] ),
			'template_set_card_density'           => sanitize_title( $settings['template_set_card_density'] ),
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

	/**
	 * Should card extras render?
	 *
	 * @return bool
	 */
	private static function should_render_card_extras() {
		return ( self::is_enabled() && ! is_property() ) || self::$rendering_module;
	}

	/**
	 * Is the current archive rendering the module preview?
	 *
	 * @return bool
	 */
	private static function is_module_preview() {
		if ( ! is_post_type_archive( 'property' ) ) {
			return false;
		}

		return '' !== self::get_query_template( self::MODULE_QUERY_ARG, self::get_module_templates() );
	}

	/**
	 * Is the current archive rendering a search-template preview?
	 *
	 * @return bool
	 */
	private static function is_search_preview() {
		if ( ! is_post_type_archive( 'property' ) ) {
			return false;
		}

		return '' !== self::get_query_template( self::SEARCH_QUERY_ARG, self::get_search_templates() );
	}

	/**
	 * Is any template preview query active?
	 *
	 * @return bool
	 */
	private static function is_previewing_template() {
		foreach ( self::get_preview_query_args() as $arg ) {
			if ( ! empty( $_GET[ $arg ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Is the visual template editor active on this request?
	 *
	 * @return bool
	 */
	private static function is_template_editor_active() {
		if ( ! self::can_show_template_switcher() ) {
			return false;
		}

		if ( self::is_template_editor_closed_request() ) {
			return false;
		}

		return self::is_template_editor_request();
	}

	/**
	 * Is this request explicitly asking for the front-end editor?
	 *
	 * @return bool
	 */
	private static function is_template_editor_request() {
		return ! empty( $_GET[ self::EDIT_QUERY_ARG ] );
	}

	/**
	 * Is this request explicitly asking to hide the front-end editor?
	 *
	 * @return bool
	 */
	private static function is_template_editor_closed_request() {
		return ! empty( $_GET[ self::EDIT_CLOSED_QUERY_ARG ] );
	}

	/**
	 * Can a valid template preview render while the global setting is inactive?
	 *
	 * @return bool
	 */
	private static function can_render_preview_request() {
		if ( is_admin() || ! self::can_manage_template_set() ) {
			return false;
		}

		return self::has_valid_preview_request();
	}

	/**
	 * Is the current request asking for a known preview template?
	 *
	 * @return bool
	 */
	private static function has_valid_preview_request() {
		if ( self::is_template_editor_request() && ( is_property() || is_post_type_archive( 'property' ) ) ) {
			return true;
		}

		if ( '' !== self::get_query_template( self::DETAIL_QUERY_ARG, self::get_detail_templates() ) ) {
			return true;
		}

		if ( '' !== self::get_query_template( self::SEARCH_QUERY_ARG, self::get_search_templates() ) ) {
			return true;
		}

		if ( empty( $_GET[ self::CATALOG_QUERY_ARG ] ) ) {
			return false;
		}

		$template = sanitize_title( wp_unslash( $_GET[ self::CATALOG_QUERY_ARG ] ) );
		$catalog  = self::get_template_catalog();

		return isset( $catalog[ $template ] );
	}

	/**
	 * Query args used by template preview routes.
	 *
	 * @return array
	 */
	private static function get_preview_query_args() {
		return array( self::DETAIL_QUERY_ARG, self::SEARCH_QUERY_ARG, self::MODULE_QUERY_ARG, self::CATALOG_QUERY_ARG, self::EDIT_QUERY_ARG, 'ph_view' );
	}

	/**
	 * Query args to clear when leaving the preview/editor flow.
	 *
	 * @return array
	 */
	private static function get_preview_clear_query_args() {
		return array_merge( self::get_preview_query_args(), array( self::EDIT_CLOSED_QUERY_ARG ) );
	}

	/**
	 * Get the currently represented catalogue template.
	 *
	 * @return string
	 */
	private static function get_current_catalog_template() {
		if ( is_property() ) {
			return self::get_detail_template();
		}

		if ( is_post_type_archive( 'property' ) ) {
			return self::get_search_template();
		}

		$settings = self::get_settings();
		return sanitize_title( $settings['template_set_detail_template'] );
	}

	/**
	 * Short label for admin bar template groups.
	 *
	 * @param string $type Template type.
	 * @return string
	 */
	private static function get_short_template_group_label( $type ) {
		if ( 'search' === $type ) {
			return __( 'Search', 'propertyhive' );
		}

		if ( 'module' === $type ) {
			return __( 'Module', 'propertyhive' );
		}

		return __( 'Detail', 'propertyhive' );
	}

	/**
	 * Can the current user switch templates on this front-end page?
	 *
	 * @return bool
	 */
	private static function can_show_template_switcher() {
		if ( is_admin() || ! self::can_manage_template_set() ) {
			return false;
		}

		return is_property() || is_post_type_archive( 'property' );
	}

	/**
	 * Can the current user manage the template set?
	 *
	 * @return bool
	 */
	private static function can_manage_template_set() {
		return current_user_can( 'manage_options' ) || current_user_can( 'manage_propertyhive' );
	}

	/**
	 * Get the selected gallery layout.
	 *
	 * @return string
	 */
	private static function get_gallery_layout() {
		$settings = self::get_settings();
		$layout   = sanitize_title( $settings['template_set_gallery_layout'] );

		return isset( self::get_gallery_layouts()[ $layout ] ) ? $layout : 'showcase';
	}

	/**
	 * Get a valid preview template from the query string.
	 *
	 * @param string $query_arg Query arg name.
	 * @param array  $templates Allowed templates.
	 * @return string
	 */
	private static function get_query_template( $query_arg, $templates ) {
		if ( empty( $_GET[ $query_arg ] ) ) {
			return '';
		}

		$template = sanitize_title( wp_unslash( $_GET[ $query_arg ] ) );

		return isset( $templates[ $template ] ) ? $template : '';
	}

	/**
	 * Build a switch URL for the current template page.
	 *
	 * @param string $query_arg Query arg name.
	 * @param string $template Template slug.
	 * @return string
	 */
	private static function get_template_switch_url( $query_arg, $template ) {
		$url = remove_query_arg( self::get_preview_clear_query_args(), self::get_current_url() );

		return add_query_arg( $query_arg, sanitize_title( $template ), $url );
	}

	/**
	 * Get a published property URL for detail template previews.
	 *
	 * @param string $template Template slug.
	 * @return string
	 */
	private static function get_sample_property_url( $template = '' ) {
		$preferred_department = self::get_detail_template_sample_department( $template );

		if ( is_property() ) {
			$property_id = get_queried_object_id();

			if ( self::property_matches_sample_department( $property_id, $preferred_department ) ) {
				return get_permalink( $property_id );
			}
		}

		$properties = get_posts(
			array(
				'post_type'      => 'property',
				'post_status'    => 'publish',
				'posts_per_page' => 25,
				'fields'         => 'ids',
			)
		);

		foreach ( $properties as $property_id ) {
			if ( self::property_matches_sample_department( $property_id, $preferred_department ) ) {
				return get_permalink( $property_id );
			}
		}

		if ( ! empty( $properties ) ) {
			return get_permalink( $properties[0] );
		}

		$archive_url = get_post_type_archive_link( 'property' );
		return $archive_url ? $archive_url : home_url( '/' );
	}

	/**
	 * Preferred sample property department for a detail template.
	 *
	 * @param string $template Template slug.
	 * @return string
	 */
	private static function get_detail_template_sample_department( $template ) {
		return 'lettings-detail' === $template ? 'residential-lettings' : 'residential-sales';
	}

	/**
	 * Does a property match the preferred preview department?
	 *
	 * @param int    $property_id Property ID.
	 * @param string $department  Expected Property Hive department.
	 * @return bool
	 */
	private static function property_matches_sample_department( $property_id, $department ) {
		if ( empty( $property_id ) || empty( $department ) ) {
			return false;
		}

		$property = new PH_Property( $property_id );

		return $department === $property->department || $department === ph_get_custom_department_based_on( $property->department );
	}

	/**
	 * Get the current front-end URL.
	 *
	 * @return string
	 */
	private static function get_current_url() {
		return home_url( add_query_arg( null, null ) );
	}

	/**
	 * Get current search view.
	 *
	 * @return string
	 */
	private static function get_search_view() {
		$view = isset( $_GET['ph_view'] ) ? sanitize_title( wp_unslash( $_GET['ph_view'] ) ) : '';
		$template = self::get_search_template();

		if ( 'compact-list-search-results' === $template ) {
			return 'list';
		}

		if ( ! in_array( $view, array( 'list', 'grid', 'map' ), true ) ) {
			if ( 'brand-led-agency-search-results' === $template ) {
				$view = 'grid';
			} elseif ( 'map-led-search-results' === $template ) {
				$view = 'map';
			} else {
				$view = 'list';
			}
		}

		return $view;
	}

	/**
	 * Get fact limit for current search template.
	 *
	 * @return int
	 */
	private static function get_search_fact_limit() {
		$template = self::get_search_template();

		if ( 'compact-list-search-results' === $template ) {
			return 3;
		}

		if ( 'brand-led-agency-search-results' === $template ) {
			return 4;
		}

		return 5;
	}

	/**
	 * Get search intro content for the active search template.
	 *
	 * @param string $template Template slug.
	 * @return array
	 */
	private static function get_search_intro_content( $template ) {
		$content = array(
			'portal-style-search-results'     => array(
				'kicker' => __( 'Property search', 'propertyhive' ),
				'title'  => __( 'Homes matching your search', 'propertyhive' ),
				'body'   => __( 'Compare listings with filters, alerts, sorting and a clear route into map view.', 'propertyhive' ),
				'items'  => array(
					__( 'Refine', 'propertyhive' ),
					__( 'Save search', 'propertyhive' ),
					__( 'Map view', 'propertyhive' ),
				),
			),
			'brand-led-agency-search-results' => array(
				'kicker' => __( 'Selected homes', 'propertyhive' ),
				'title'  => __( 'Browse our latest properties', 'propertyhive' ),
				'body'   => __( 'A calmer view of current stock with larger photography and the key facts kept close to each home.', 'propertyhive' ),
				'items'  => array(
					__( 'Featured stock', 'propertyhive' ),
					__( 'Larger photos', 'propertyhive' ),
					__( 'Branch contact', 'propertyhive' ),
				),
			),
			'map-led-search-results'          => array(
				'kicker' => __( 'Explore the area', 'propertyhive' ),
				'title'  => __( 'Search by location', 'propertyhive' ),
				'body'   => __( 'Keep the map and listings visible together while comparing streets, stations and neighbourhoods.', 'propertyhive' ),
				'items'  => array(
					__( 'Map first', 'propertyhive' ),
					__( 'List beside map', 'propertyhive' ),
					__( 'Location context', 'propertyhive' ),
				),
			),
			'compact-list-search-results'     => array(
				'kicker' => __( 'Quick comparison', 'propertyhive' ),
				'title'  => __( 'Scan the shortlist', 'propertyhive' ),
				'body'   => __( 'A tighter list for comparing price, location and core facts with less scrolling.', 'propertyhive' ),
				'items'  => array(
					__( 'Compact rows', 'propertyhive' ),
					__( 'Core facts', 'propertyhive' ),
					__( 'Fast browsing', 'propertyhive' ),
				),
			),
		);

		return isset( $content[ $template ] ) ? $content[ $template ] : $content['portal-style-search-results'];
	}

	/**
	 * Get detail kicker label.
	 *
	 * @param PH_Property $property Property object.
	 * @param string      $template Template slug.
	 * @return string
	 */
	private static function get_detail_kicker_label( $property, $template ) {
		if ( 'lettings-detail' === $template ) {
			return __( 'To let', 'propertyhive' );
		}

		$labels = array(
			'standard-sales-detail'         => __( 'For sale', 'propertyhive' ),
			'conversion-first-sales-detail' => __( 'Viewing available', 'propertyhive' ),
			'premium-editorial-detail'      => __( 'Featured home', 'propertyhive' ),
			'new-homes-development-detail'  => __( 'New homes release', 'propertyhive' ),
		);

		if ( isset( $labels[ $template ] ) ) {
			return $labels[ $template ];
		}

		if ( 'residential-lettings' === $property->department || 'residential-lettings' === ph_get_custom_department_based_on( $property->department ) ) {
			return __( 'To let', 'propertyhive' );
		}

		return __( 'Property', 'propertyhive' );
	}

	/**
	 * Get detail highlights for the active detail template.
	 *
	 * @param PH_Property $property Property object.
	 * @param string      $template Template slug.
	 * @return array
	 */
	private static function get_detail_highlights( $property, $template ) {
		$photo_count = self::get_photo_count( $property );
		$photos      = $photo_count > 1 ? sprintf(
			/* translators: %d: number of property photos */
			_n( '%d photo', '%d photos', $photo_count, 'propertyhive' ),
			$photo_count
		) : __( 'Photos available', 'propertyhive' );
		$button      = self::get_primary_cta_label( $property, $template );
		$phone       = $property->get_negotiator_telephone_number();

		if ( 'conversion-first-sales-detail' === $template ) {
			return array(
				array(
					'label' => __( 'Next step', 'propertyhive' ),
					'value' => $button,
				),
				array(
					'label' => __( 'Phone', 'propertyhive' ),
					'value' => $phone ? __( 'Call agent', 'propertyhive' ) : __( 'Send enquiry', 'propertyhive' ),
				),
				array(
					'label' => __( 'Route', 'propertyhive' ),
					'value' => __( 'Short enquiry', 'propertyhive' ),
				),
			);
		}

		if ( 'premium-editorial-detail' === $template ) {
			return array(
				array(
					'label' => __( 'Gallery', 'propertyhive' ),
					'value' => $photos,
				),
				array(
					'label' => __( 'Documents', 'propertyhive' ),
					'value' => self::get_document_summary( $property ),
				),
				array(
					'label' => __( 'Interest', 'propertyhive' ),
					'value' => $button,
				),
			);
		}

		if ( 'lettings-detail' === $template ) {
			return self::get_rental_highlights( $property );
		}

		if ( 'new-homes-development-detail' === $template ) {
			return array(
				array(
					'label' => __( 'Release', 'propertyhive' ),
					'value' => __( 'Development enquiry', 'propertyhive' ),
				),
				array(
					'label' => __( 'Documents', 'propertyhive' ),
					'value' => self::get_document_summary( $property ),
				),
				array(
					'label' => __( 'Interest', 'propertyhive' ),
					'value' => $button,
				),
			);
		}

		if ( 'standard-sales-detail' === $template ) {
			return array();
		}

		return array(
			array(
				'label' => __( 'Gallery', 'propertyhive' ),
				'value' => $photos,
			),
			array(
				'label' => __( 'Facts', 'propertyhive' ),
				'value' => self::get_fact_summary( $property ),
			),
			array(
				'label' => __( 'Viewing', 'propertyhive' ),
				'value' => $button,
			),
		);
	}

	/**
	 * Get template-specific context panel content for detail pages.
	 *
	 * @param PH_Property $property Property object.
	 * @param string      $template Template slug.
	 * @return array
	 */
	private static function get_detail_context_panel( $property, $template ) {
		$button = self::get_primary_cta_label( $property, $template );
		$phone  = $property->get_negotiator_telephone_number();

		if ( 'conversion-first-sales-detail' === $template ) {
			return array(
				'kicker' => __( 'Ready to enquire', 'propertyhive' ),
				'title'  => __( 'Book the next viewing', 'propertyhive' ),
				'body'   => __( 'The main actions stay close to the price and key facts so interested buyers can move quickly.', 'propertyhive' ),
				'items'  => array(
					array(
						'label' => __( 'Primary action', 'propertyhive' ),
						'value' => $button,
					),
					array(
						'label' => __( 'Phone route', 'propertyhive' ),
						'value' => $phone ? $phone : __( 'Ask agent', 'propertyhive' ),
					),
					array(
						'label' => __( 'Supporting docs', 'propertyhive' ),
						'value' => self::get_document_summary( $property ),
					),
				),
			);
		}

		if ( 'premium-editorial-detail' === $template ) {
			return array(
				'kicker' => __( 'Property story', 'propertyhive' ),
				'title'  => __( 'Register your interest', 'propertyhive' ),
				'body'   => __( 'Photography, brochure links and a quieter contact path give higher-value homes more room to breathe.', 'propertyhive' ),
				'items'  => array(
					array(
						'label' => __( 'Gallery', 'propertyhive' ),
						'value' => self::get_photo_count( $property ) > 1 ? self::get_photo_count( $property ) : __( 'Available', 'propertyhive' ),
					),
					array(
						'label' => __( 'Documents', 'propertyhive' ),
						'value' => self::get_document_summary( $property ),
					),
					array(
						'label' => __( 'Enquiry', 'propertyhive' ),
						'value' => $button,
					),
				),
			);
		}

		if ( 'lettings-detail' === $template ) {
			return array(
				'kicker' => __( 'Rental details', 'propertyhive' ),
				'title'  => __( 'Costs and availability', 'propertyhive' ),
				'body'   => __( 'Renters can check the key practical details before arranging a viewing.', 'propertyhive' ),
				'items'  => self::get_rental_panel_items( $property ),
			);
		}

		if ( 'new-homes-development-detail' === $template ) {
			return array(
				'kicker' => __( 'Development', 'propertyhive' ),
				'title'  => __( 'Register for release details', 'propertyhive' ),
				'body'   => __( 'Use this enquiry route for plot updates, brochures, floorplans and appointment requests.', 'propertyhive' ),
				'items'  => array(
					array(
						'label' => __( 'Primary action', 'propertyhive' ),
						'value' => $button,
					),
					array(
						'label' => __( 'Brochure', 'propertyhive' ),
						'value' => self::has_brochure( $property ) ? __( 'Available', 'propertyhive' ) : __( 'Ask agent', 'propertyhive' ),
					),
					array(
						'label' => __( 'Floorplan', 'propertyhive' ),
						'value' => self::has_floorplan( $property ) ? __( 'Available', 'propertyhive' ) : __( 'Ask agent', 'propertyhive' ),
					),
				),
			);
		}

		return array();
	}

	/**
	 * Get short contact-card hint copy.
	 *
	 * @param string $template Template slug.
	 * @return string
	 */
	private static function get_contact_hint( $template ) {
		$hints = array(
			'conversion-first-sales-detail' => __( 'Call now or send a quick viewing request.', 'propertyhive' ),
			'premium-editorial-detail'      => __( 'Ask for the brochure, floorplan or viewing details.', 'propertyhive' ),
			'lettings-detail'               => __( 'Check availability and arrange a rental viewing.', 'propertyhive' ),
			'new-homes-development-detail'  => __( 'Register for plot, brochure and appointment updates.', 'propertyhive' ),
		);

		return isset( $hints[ $template ] ) ? $hints[ $template ] : __( 'Call or request a viewing with the agent.', 'propertyhive' );
	}

	/**
	 * Build initials for the contact-card photo fallback.
	 *
	 * @param string $name Negotiator display name.
	 * @return string
	 */
	private static function get_contact_agent_initials( $name ) {
		$name = trim( wp_strip_all_tags( (string) $name ) );

		if ( '' === $name ) {
			return '';
		}

		$parts    = preg_split( '/\s+/', $name );
		$first    = isset( $parts[0] ) ? $parts[0] : '';
		$last     = count( $parts ) > 1 ? $parts[ count( $parts ) - 1 ] : '';
		$initials = self::get_string_initial( $first );

		if ( '' !== $last ) {
			$initials .= self::get_string_initial( $last );
		} else {
			$initials .= self::get_string_initial( self::get_string_slice( $first, 1 ) );
		}

		return strtoupper( substr( $initials, 0, 2 ) );
	}

	/**
	 * Get a non-repeating subline for the negotiator row.
	 *
	 * @param string $agent Agent display name.
	 * @param string $office_heading Marketed-by heading.
	 * @param string $office Office name.
	 * @return string
	 */
	private static function get_contact_agent_role( $agent, $office_heading, $office ) {
		$office         = trim( wp_strip_all_tags( (string) $office ) );
		$office_heading = trim( wp_strip_all_tags( (string) $office_heading ) );

		if ( '' === $office || '' === $agent ) {
			return '';
		}

		if ( 0 === strcasecmp( $office, $office_heading ) ) {
			return '';
		}

		return $office;
	}

	/**
	 * Get the first character from a string.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	private static function get_string_initial( $value ) {
		$value = trim( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		return self::get_string_slice( $value, 0, 1 );
	}

	/**
	 * Slice strings with mbstring support when available.
	 *
	 * @param string   $value Value.
	 * @param int      $start Start offset.
	 * @param int|null $length Length.
	 * @return string
	 */
	private static function get_string_slice( $value, $start, $length = null ) {
		if ( function_exists( 'mb_substr' ) ) {
			return null === $length ? mb_substr( $value, $start ) : mb_substr( $value, $start, $length );
		}

		return null === $length ? substr( $value, $start ) : substr( $value, $start, $length );
	}

	/**
	 * Get photo count.
	 *
	 * @param PH_Property $property Property object.
	 * @return int
	 */
	private static function get_photo_count( $property ) {
		if ( get_option( 'propertyhive_images_stored_as', '' ) === 'urls' ) {
			$photos = $property->_photo_urls;
			return is_array( $photos ) ? count( array_filter( $photos ) ) : 0;
		}

		return count( $property->get_gallery_attachment_ids() );
	}

	/**
	 * Get the current property object even before Property Hive sets the global.
	 *
	 * @return PH_Property|false
	 */
	private static function get_current_property() {
		global $property;

		if ( is_a( $property, 'PH_Property' ) ) {
			return $property;
		}

		$property_id = get_queried_object_id();

		if ( ! $property_id || 'property' !== get_post_type( $property_id ) ) {
			return false;
		}

		return new PH_Property( $property_id );
	}

	/**
	 * Get an office name suitable for front-end display.
	 *
	 * @param PH_Property $property Property object.
	 * @return string
	 */
	private static function get_display_office_name( $property ) {
		$office = $property->get_office_name();

		if ( $office && false === stripos( $office, 'codex' ) ) {
			return $office;
		}

		return __( 'Property Hive', 'propertyhive' );
	}

	/**
	 * Build readable facts for the detail preview from the property record.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_detail_meta_items( $property ) {
		$items = array();

		if ( $property->bedrooms > 0 ) {
			$items[] = sprintf(
				/* translators: %d: number of bedrooms */
				_n( '%d bedroom', '%d bedrooms', (int) $property->bedrooms, 'propertyhive' ),
				(int) $property->bedrooms
			);
		}

		if ( $property->bathrooms > 0 ) {
			$items[] = sprintf(
				/* translators: %d: number of bathrooms */
				_n( '%d bathroom', '%d bathrooms', (int) $property->bathrooms, 'propertyhive' ),
				(int) $property->bathrooms
			);
		}

		if ( $property->reception_rooms > 0 ) {
			$items[] = sprintf(
				/* translators: %d: number of reception rooms */
				_n( '%d reception room', '%d reception rooms', (int) $property->reception_rooms, 'propertyhive' ),
				(int) $property->reception_rooms
			);
		}

		if ( $property->property_type ) {
			$items[] = $property->property_type;
		}

		if ( $property->tenure ) {
			$items[] = $property->tenure;
		}

		if ( $property->availability ) {
			$items[] = $property->availability;
		}

		if ( $property->furnished ) {
			$items[] = $property->furnished;
		}

		if ( $property->available_date ) {
			$items[] = $property->get_available_date();
		}

		if ( $property->floor_area ) {
			$items[] = $property->get_formatted_floor_area();
		}

		return array_values( array_unique( array_filter( $items ) ) );
	}

	/**
	 * Build labelled facts for the below-gallery detail facts strip.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_detail_facts_strip_items( $property ) {
		$size  = self::split_detail_fact_size( $property->get_formatted_floor_area() );
		$items = array(
			'type'      => array(
				'label'     => __( 'Property type', 'propertyhive' ),
				'value'     => $property->property_type ? $property->property_type : '',
				'secondary' => '',
				'icon'      => 'type',
			),
			'bedrooms'  => array(
				'label'     => __( 'Bedrooms', 'propertyhive' ),
				'value'     => $property->bedrooms > 0 ? (string) (int) $property->bedrooms : '',
				'secondary' => '',
				'icon'      => 'bedrooms',
			),
			'bathrooms' => array(
				'label'     => __( 'Bathrooms', 'propertyhive' ),
				'value'     => $property->bathrooms > 0 ? (string) (int) $property->bathrooms : '',
				'secondary' => '',
				'icon'      => 'bathrooms',
			),
			'receptions' => array(
				'label'     => __( 'Receptions', 'propertyhive' ),
				'value'     => $property->reception_rooms > 0 ? (string) (int) $property->reception_rooms : '',
				'secondary' => '',
				'icon'      => 'receptions',
			),
			'size'      => array(
				'label'     => __( 'Size', 'propertyhive' ),
				'value'     => $size['value'],
				'secondary' => $size['secondary'],
				'icon'      => 'size',
			),
			'tenure'    => array(
				'label'     => __( 'Tenure', 'propertyhive' ),
				'value'     => $property->tenure ? $property->tenure : '',
				'secondary' => '',
				'icon'      => 'tenure',
			),
		);

		return array_values(
			array_filter(
				$items,
				function( $item ) {
					return '' !== trim( (string) $item['value'] );
				}
			)
		);
	}

	/**
	 * Split a floor-area value into primary and secondary display values.
	 *
	 * @param string $size Floor area label.
	 * @return array
	 */
	private static function split_detail_fact_size( $size ) {
		$size = trim( wp_strip_all_tags( (string) $size ) );

		if ( '' === $size ) {
			return array(
				'value'     => '',
				'secondary' => '',
			);
		}

		if ( false !== strpos( $size, '/' ) ) {
			$parts = array_map( 'trim', explode( '/', $size, 2 ) );

			return array(
				'value'     => $parts[0],
				'secondary' => isset( $parts[1] ) ? $parts[1] : '',
			);
		}

		return array(
			'value'     => $size,
			'secondary' => '',
		);
	}

	/**
	 * Get a compact location label from the real address data.
	 *
	 * @param PH_Property $property Property object.
	 * @return string
	 */
	private static function get_property_location_label( $property ) {
		$candidates = array(
			$property->_address_three,
			$property->_address_four,
			$property->_address_two,
			$property->_address_postcode,
			$property->get_formatted_summary_address(),
		);

		foreach ( $candidates as $candidate ) {
			if ( '' !== trim( (string) $candidate ) ) {
				return trim( (string) $candidate );
			}
		}

		return '';
	}

	/**
	 * Does the property have at least one floorplan?
	 *
	 * @param PH_Property $property Property object.
	 * @return bool
	 */
	private static function has_floorplan( $property ) {
		if ( ! empty( $property->get_floorplan_attachment_ids() ) ) {
			return true;
		}

		$floorplan_urls = $property->_floorplan_urls;

		return is_array( $floorplan_urls ) && ! empty( array_filter( $floorplan_urls ) );
	}

	/**
	 * Should floorplans render for this property template request?
	 *
	 * @param PH_Property $property Property object.
	 * @return bool
	 */
	private static function should_render_floorplans( $property ) {
		if ( ! $property || ! self::has_floorplan( $property ) ) {
			return false;
		}

		$settings = self::get_settings();

		return 'yes' === $settings['template_set_show_floorplans'] || self::is_template_editor_active();
	}

	/**
	 * Does the property have at least one virtual tour?
	 *
	 * @param PH_Property $property Property object.
	 * @return bool
	 */
	private static function has_virtual_tour( $property ) {
		return $property && ! empty( $property->get_virtual_tours() );
	}

	/**
	 * Should virtual tours render for this property template request?
	 *
	 * @param PH_Property $property Property object.
	 * @return bool
	 */
	private static function should_render_virtual_tours( $property ) {
		if ( ! $property || ! self::has_virtual_tour( $property ) ) {
			return false;
		}

		$settings = self::get_settings();

		return 'yes' === $settings['template_set_show_virtual_tours'] || self::is_template_editor_active();
	}

	/**
	 * Does the property have at least one EPC?
	 *
	 * @param PH_Property $property Property object.
	 * @return bool
	 */
	private static function has_epc( $property ) {
		if ( ! empty( $property->get_epc_attachment_ids() ) ) {
			return true;
		}

		$epc_urls = $property->_epc_urls;

		return is_array( $epc_urls ) && ! empty( array_filter( $epc_urls ) );
	}

	/**
	 * Does the property have at least one brochure?
	 *
	 * @param PH_Property $property Property object.
	 * @return bool
	 */
	private static function has_brochure( $property ) {
		if ( ! empty( $property->get_brochure_attachment_ids() ) ) {
			return true;
		}

		$brochure_urls = $property->_brochure_urls;

		return is_array( $brochure_urls ) && ! empty( array_filter( $brochure_urls ) );
	}

	/**
	 * Summarise available supporting documents.
	 *
	 * @param PH_Property $property Property object.
	 * @return string
	 */
	private static function get_document_summary( $property ) {
		$documents = self::get_property_document_labels( $property );

		if ( empty( $documents ) ) {
			return __( 'Ask agent', 'propertyhive' );
		}

		return implode( ', ', array_slice( wp_list_pluck( $documents, 'label' ), 0, 3 ) );
	}

	/**
	 * Summarise core facts in one short string.
	 *
	 * @param PH_Property $property Property object.
	 * @return string
	 */
	private static function get_fact_summary( $property ) {
		$facts = array_slice( self::get_detail_meta_items( $property ), 0, 3 );

		if ( empty( $facts ) ) {
			return __( 'Details available', 'propertyhive' );
		}

		return implode( ' / ', $facts );
	}

	/**
	 * Get rental highlight items for lettings templates.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_rental_highlights( $property ) {
		return array_slice( self::get_rental_panel_items( $property ), 0, 4 );
	}

	/**
	 * Get rental-focused detail items.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_rental_panel_items( $property ) {
		$deposit = method_exists( $property, 'get_formatted_deposit' ) ? $property->get_formatted_deposit() : '';
		$items   = array(
			array(
				'label' => __( 'Available', 'propertyhive' ),
				'value' => $property->available_date ? $property->get_available_date() : __( 'Ask agent', 'propertyhive' ),
			),
			array(
				'label' => __( 'Deposit', 'propertyhive' ),
				'value' => $deposit ? $deposit : __( 'Ask agent', 'propertyhive' ),
			),
			array(
				'label' => __( 'Furnished', 'propertyhive' ),
				'value' => $property->furnished ? $property->furnished : __( 'Ask agent', 'propertyhive' ),
			),
		);

		if ( $property->council_tax_band ) {
			$items[] = array(
				'label' => __( 'Council tax', 'propertyhive' ),
				'value' => sprintf(
					/* translators: %s: council tax band */
					__( 'Band %s', 'propertyhive' ),
					$property->council_tax_band
				),
			);
		}

		return $items;
	}

	/**
	 * Build reusable property fact items.
	 *
	 * @param PH_Property $property Property object.
	 * @param int         $limit Maximum facts.
	 * @return array
	 */
	private static function get_fact_items( $property, $limit = 5 ) {
		$facts = array();

		if ( $property->bedrooms > 0 ) {
			$facts[] = array(
				'label' => __( 'Beds', 'propertyhive' ),
				'value' => $property->bedrooms,
			);
		}

		if ( $property->bathrooms > 0 ) {
			$facts[] = array(
				'label' => __( 'Baths', 'propertyhive' ),
				'value' => $property->bathrooms,
			);
		}

		if ( $property->reception_rooms > 0 ) {
			$facts[] = array(
				'label' => __( 'Receptions', 'propertyhive' ),
				'value' => $property->reception_rooms,
			);
		}

		if ( $property->property_type ) {
			$facts[] = array(
				'label' => __( 'Type', 'propertyhive' ),
				'value' => $property->property_type,
			);
		}

		if ( $property->tenure ) {
			$facts[] = array(
				'label' => __( 'Tenure', 'propertyhive' ),
				'value' => $property->tenure,
			);
		}

		if ( $property->furnished ) {
			$facts[] = array(
				'label' => __( 'Furnished', 'propertyhive' ),
				'value' => $property->furnished,
			);
		}

		if ( $property->available_date ) {
			$facts[] = array(
				'label' => __( 'Available', 'propertyhive' ),
				'value' => $property->get_available_date(),
			);
		}

		if ( $property->floor_area ) {
			$facts[] = array(
				'label' => __( 'Floor area', 'propertyhive' ),
				'value' => $property->get_formatted_floor_area(),
			);
		}

		return array_slice( array_filter( $facts ), 0, absint( $limit ) );
	}

	/**
	 * Get primary CTA label for a property/template.
	 *
	 * @param PH_Property $property Property object.
	 * @param string      $template Template slug.
	 * @return string
	 */
	private static function get_primary_cta_label( $property, $template ) {
		if ( 'premium-editorial-detail' === $template || 'new-homes-development-detail' === $template ) {
			return __( 'Register interest', 'propertyhive' );
		}

		if ( 'conversion-first-sales-detail' === $template ) {
			return __( 'Book viewing', 'propertyhive' );
		}

		if ( 'lettings-detail' === $template || 'residential-lettings' === $property->department || 'residential-lettings' === ph_get_custom_department_based_on( $property->department ) ) {
			return __( 'Arrange viewing', 'propertyhive' );
		}

		return __( 'Request viewing', 'propertyhive' );
	}

	/**
	 * Render media/document links in the contact card.
	 *
	 * @param PH_Property $property Property object.
	 */
	private static function render_detail_media_links( $property ) {
		$links = self::get_property_document_labels( $property );

		if ( empty( $links ) ) {
			return;
		}

		echo '<ul class="ph-template-media-links">';
		foreach ( array_slice( $links, 0, 4 ) as $link ) {
			echo '<li>' . esc_html( $link['label'] ) . '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Get supporting document labels that are available for the property.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_property_document_labels( $property ) {
		$documents = array();

		if ( self::should_render_floorplans( $property ) ) {
			$documents[] = array(
				'label' => __( 'Floorplan', 'propertyhive' ),
				'type'  => 'floorplan',
			);
		}

		if ( self::should_render_virtual_tours( $property ) ) {
			$documents[] = array(
				'label' => __( 'Virtual tour', 'propertyhive' ),
				'type'  => 'virtual-tour',
			);
		}

		if ( self::has_epc( $property ) ) {
			$documents[] = array(
				'label' => __( 'EPC', 'propertyhive' ),
				'type'  => 'epc',
			);
		}

		if ( self::has_brochure( $property ) ) {
			$documents[] = array(
				'label' => __( 'Brochure', 'propertyhive' ),
				'type'  => 'brochure',
			);
		}

		return $documents;
	}
}

PH_Template_Set::init();
