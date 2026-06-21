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

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'print_style_variables' ), 20 );
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_catalog_preview_request' ), 1 );
		add_action( 'wp', array( __CLASS__, 'prepare_module_preview' ) );
		add_action( 'wp', array( __CLASS__, 'prepare_detail_preview' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_bar_menu' ), 80 );
		add_action( 'wp_footer', array( __CLASS__, 'render_context_switcher' ), 30 );

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
			'version' => PH_VERSION,
			'media'   => 'all',
		);

		return $styles;
	}

	/**
	 * Register template-set scripts.
	 */
	public static function enqueue_scripts() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		wp_enqueue_script(
			'propertyhive-template-set',
			str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/js/frontend/template-set.js',
			array(),
			PH_VERSION,
			true
		);
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
		$settings = get_option( 'propertyhive_template_assistant', array() );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		return wp_parse_args(
			$settings,
			array(
				self::OPTION_ENABLED             => '',
				'template_set_detail_template'  => 'standard-sales-detail',
				'template_set_search_template'  => 'portal-style-search-results',
				'template_set_module_template'  => 'featured-properties-homepage-module',
				'template_set_brand_colour'     => '#155e63',
				'template_set_accent_colour'    => '#b7791f',
				'template_set_button_style'     => 'filled',
				'template_set_card_density'     => 'standard',
				'template_set_image_style'      => 'soft',
				'template_set_show_branch'      => 'yes',
				'template_set_show_badges'      => 'yes',
				'template_set_show_mobile_cta'  => 'yes',
			)
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
			'conversion-first-sales-detail'   => __( 'Conversion-First Sales Detail', 'propertyhive' ),
			'premium-editorial-detail'        => __( 'Premium Editorial Detail', 'propertyhive' ),
			'lettings-detail'                 => __( 'Lettings Detail', 'propertyhive' ),
			'new-homes-development-detail'    => __( 'New Homes / Development Detail', 'propertyhive' ),
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
			'brand-led-agency-search-results'  => __( 'Brand-Led Agency Search Results', 'propertyhive' ),
			'map-led-search-results'           => __( 'Map-Led Search Results', 'propertyhive' ),
			'compact-list-search-results'      => __( 'Compact List Search Results', 'propertyhive' ),
		);
	}

	/**
	 * Homepage/module template profiles.
	 *
	 * @return array
	 */
	public static function get_module_templates() {
		return array(
			'featured-properties-homepage-module' => __( 'Featured Properties / Homepage Module', 'propertyhive' ),
		);
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
		$settings  = self::get_settings();
		$templates = self::get_module_templates();
		$template  = self::get_query_template( self::MODULE_QUERY_ARG, $templates );

		if ( empty( $template ) ) {
			$template = sanitize_title( $settings['template_set_module_template'] );
		}

		return isset( $templates[ $template ] ) ? $template : 'featured-properties-homepage-module';
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

		if ( 'module' === $catalog[ $template ]['type'] ) {
			return add_query_arg( self::MODULE_QUERY_ARG, $template, $archive_url );
		}

		return add_query_arg( self::SEARCH_QUERY_ARG, $template, $archive_url );
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
				'href'   => remove_query_arg( self::get_preview_query_args(), self::get_current_url() ),
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
	 * Render an admin-only in-context template switcher on template pages.
	 */
	public static function render_context_switcher() {
		if ( ! self::can_show_template_switcher() ) {
			return;
		}

		$catalog       = self::get_template_catalog();
		$current_slug  = self::get_current_catalog_template();
		$default_url   = remove_query_arg( self::get_preview_query_args(), self::get_current_url() );
		$using_preview = self::is_previewing_template();
		$groups        = array();

		foreach ( $catalog as $slug => $template ) {
			$groups[ $template['group'] ][ $slug ] = $template;
		}

		echo '<form class="ph-template-context-switcher" action="' . esc_url( $default_url ) . '" method="get" aria-label="' . esc_attr__( 'Template preview switcher', 'propertyhive' ) . '">';
			echo '<label for="ph-template-context-switcher-select">' . esc_html__( 'Template example', 'propertyhive' ) . '</label>';
			echo '<select id="ph-template-context-switcher-select" name="' . esc_attr( self::CATALOG_QUERY_ARG ) . '" data-ph-template-switcher>';
				foreach ( $groups as $group_label => $templates ) {
					echo '<optgroup label="' . esc_attr( $group_label ) . '">';
					foreach ( $templates as $slug => $template ) {
						echo '<option value="' . esc_attr( $slug ) . '" data-preview-url="' . esc_url( self::get_template_preview_url( $slug ) ) . '"' . selected( $slug, $current_slug, false ) . '>' . esc_html( $template['label'] ) . '</option>';
					}
					echo '</optgroup>';
				}
			echo '</select>';
			if ( $using_preview ) {
				echo '<a class="ph-template-context-reset" href="' . esc_url( $default_url ) . '">' . esc_html__( 'Saved default', 'propertyhive' ) . '</a>';
			}
			echo '<noscript><button type="submit">' . esc_html__( 'Apply', 'propertyhive' ) . '</button></noscript>';
		echo '</form>';
		echo '<script>(function(){var select=document.querySelector("[data-ph-template-switcher]");if(!select){return;}select.addEventListener("change",function(){var option=select.options[select.selectedIndex];var url=option?option.getAttribute("data-preview-url"):"";if(url){window.location.assign(url);}});})();</script>';
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

		echo '<style id="propertyhive-template-set-vars">:root{--ph-template-brand:' . esc_html( $brand ) . ';--ph-template-accent:' . esc_html( $accent ) . ';}</style>' . "\n";
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
	 * Swap dummy database content for a curated demo presentation while the
	 * detail page is being previewed by a capable user.
	 */
	public static function prepare_detail_preview() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		remove_action( 'propertyhive_before_single_property_summary', 'propertyhive_show_property_images', 10 );
		add_action( 'propertyhive_before_single_property_summary', array( __CLASS__, 'render_demo_gallery' ), 10 );

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

		add_filter( 'the_title', array( __CLASS__, 'filter_demo_title' ), 20, 2 );
	}

	/**
	 * Replace the previewed property's headline with a curated demo address so
	 * placeholder import titles never surface in a screenshot. Scoped to the
	 * single queried property only; every other title on the page is untouched.
	 *
	 * @param string $title Original title.
	 * @param int    $id    Post ID.
	 * @return string
	 */
	public static function filter_demo_title( $title, $id = 0 ) {
		if ( ! self::is_demo_preview() || (int) $id !== (int) get_queried_object_id() ) {
			return $title;
		}

		$listing = self::get_demo_listing( self::get_detail_template() );

		return $listing['title'];
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

		$agency = self::get_demo_agency();

		echo '<div class="ph-template-masthead"><div class="ph-template-masthead-inner">';
			echo '<span class="ph-template-masthead-brand">' . esc_html( $agency['office'] ) . '</span>';
			echo '<nav class="ph-template-masthead-nav" aria-hidden="true">';
				echo '<span>Buy</span><span>Let</span><span>New Homes</span><span>About</span>';
			echo '</nav>';
			echo '<a class="ph-template-masthead-phone" href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $agency['phone'] ) ) . '">' . esc_html( $agency['phone'] ) . '</a>';
		echo '</div></div>';
	}

	/**
	 * Demo gallery image set for a detail template.
	 *
	 * @param string $template Template slug.
	 * @return array
	 */
	private static function get_demo_gallery_images( $template ) {
		$cavendish = array(
			array( 'cavendish-living-room.png', __( 'Reception room', 'propertyhive' ) ),
			array( 'cavendish-kitchen-dining.png', __( 'Kitchen / dining', 'propertyhive' ) ),
			array( 'cavendish-principal-bedroom.png', __( 'Principal bedroom', 'propertyhive' ) ),
			array( 'cavendish-garden-terrace.png', __( 'Garden terrace', 'propertyhive' ) ),
			array( 'cavendish-exterior.png', __( 'Exterior', 'propertyhive' ) ),
		);

		if ( 'lettings-detail' === $template ) {
			return array(
				array( 'atlas-apartment-living.png', __( 'Open-plan living', 'propertyhive' ) ),
				array( 'cavendish-kitchen-dining.png', __( 'Kitchen / dining', 'propertyhive' ) ),
				array( 'cavendish-principal-bedroom.png', __( 'Bedroom', 'propertyhive' ) ),
				array( 'cavendish-living-room.png', __( 'Reception', 'propertyhive' ) ),
			);
		}

		if ( 'new-homes-development-detail' === $template ) {
			return array(
				array( 'elm-yard-development.png', __( 'Elm Yard development', 'propertyhive' ) ),
				array( 'cavendish-living-room.png', __( 'Show home reception', 'propertyhive' ) ),
				array( 'cavendish-kitchen-dining.png', __( 'Show home kitchen', 'propertyhive' ) ),
				array( 'cavendish-garden-terrace.png', __( 'Landscaped terrace', 'propertyhive' ) ),
			);
		}

		return $cavendish;
	}

	/**
	 * Render a hero/gallery presentation using bundled demo photography.
	 */
	public static function render_demo_gallery() {
		global $property;

		$template = self::get_detail_template();
		$images   = self::get_demo_gallery_images( $template );

		if ( empty( $images ) ) {
			return;
		}

		$is_editorial = ( 'premium-editorial-detail' === $template );
		$hero         = reset( $images );
		$rail         = array_slice( $images, 0, 5 );
		$count        = 9 + count( $images );

		$gallery_variants = array(
			'showcase'  => __( 'Showcase', 'propertyhive' ),
			'cinema'    => __( 'Cinema', 'propertyhive' ),
			'mosaic'    => __( 'Mosaic', 'propertyhive' ),
			'editorial' => __( 'Editorial', 'propertyhive' ),
			'strip'     => __( 'Filmstrip', 'propertyhive' ),
		);

		echo '<div class="images ph-template-gallery ph-template-gallery-' . esc_attr( sanitize_html_class( $template ) ) . ' ph-gallery-variant-showcase" data-ph-template-gallery data-ph-gallery-current-variant="showcase">';

			echo '<div class="ph-template-gallery-direction-switcher" data-ph-gallery-variant-switcher aria-label="' . esc_attr__( 'Gallery layout options', 'propertyhive' ) . '">';
				echo '<span>' . esc_html__( 'Gallery direction', 'propertyhive' ) . '</span>';
				foreach ( $gallery_variants as $variant => $label ) {
					echo '<button type="button" data-ph-gallery-variant="' . esc_attr( $variant ) . '" aria-pressed="' . ( 'showcase' === $variant ? 'true' : 'false' ) . '" class="' . ( 'showcase' === $variant ? 'is-active' : '' ) . '">' . esc_html( $label ) . '</button>';
				}
			echo '</div>';

			echo '<figure class="ph-template-gallery-hero">';
				echo '<button type="button" class="ph-template-gallery-photo-trigger" data-ph-gallery-open aria-label="' . esc_attr( sprintf(
					/* translators: %s: image label */
					__( 'Open larger photo: %s', 'propertyhive' ),
					$hero[1]
				) ) . '">';
					echo '<img src="' . esc_url( self::demo_asset( $hero[0] ) ) . '" alt="' . esc_attr( $hero[1] ) . '" loading="lazy" data-ph-gallery-hero-image>';
					echo '<span class="ph-template-gallery-expand-label" aria-hidden="true">' . esc_html__( 'View larger', 'propertyhive' ) . '</span>';
				echo '</button>';
				echo '<div class="ph-template-gallery-panel ph-template-gallery-panel-floorplan" hidden data-ph-gallery-panel="floorplan" aria-label="' . esc_attr__( 'Floorplan preview', 'propertyhive' ) . '">';
					echo '<div class="ph-template-floorplan" aria-hidden="true">';
						echo '<span class="ph-template-floorplan-room ph-template-room-reception">' . esc_html__( 'Reception', 'propertyhive' ) . '</span>';
						echo '<span class="ph-template-floorplan-room ph-template-room-kitchen">' . esc_html__( 'Kitchen', 'propertyhive' ) . '</span>';
						echo '<span class="ph-template-floorplan-room ph-template-room-bed-one">' . esc_html__( 'Bed 1', 'propertyhive' ) . '</span>';
						echo '<span class="ph-template-floorplan-room ph-template-room-bed-two">' . esc_html__( 'Bed 2', 'propertyhive' ) . '</span>';
						echo '<span class="ph-template-floorplan-room ph-template-room-bath">' . esc_html__( 'Bath', 'propertyhive' ) . '</span>';
					echo '</div>';
				echo '</div>';
				echo '<div class="ph-template-gallery-panel ph-template-gallery-panel-map" hidden data-ph-gallery-panel="map" aria-label="' . esc_attr__( 'Map preview', 'propertyhive' ) . '"><span class="ph-template-map-pin"></span><span class="ph-template-map-label">' . esc_html( self::get_demo_listing( $template )['area'] ) . '</span></div>';

				if ( ! $is_editorial ) {
					echo '<span class="ph-template-gallery-count"><span class="ph-template-gallery-count-icon" aria-hidden="true"></span>' . sprintf(
						/* translators: %d: number of property photos */
						esc_html__( '%d photos', 'propertyhive' ),
						(int) $count
						) . '</span>';

						echo '<div class="ph-template-gallery-tabs" role="tablist" aria-label="' . esc_attr__( 'Gallery views', 'propertyhive' ) . '">';
							echo '<button type="button" class="is-active" data-ph-gallery-tab="photos" aria-selected="true">' . esc_html__( 'Photos', 'propertyhive' ) . '</button>';
							echo '<button type="button" data-ph-gallery-tab="floorplan" aria-selected="false">' . esc_html__( 'Floorplan', 'propertyhive' ) . '</button>';
							echo '<button type="button" data-ph-gallery-tab="map" aria-selected="false">' . esc_html__( 'Map', 'propertyhive' ) . '</button>';
						echo '</div>';
					} else {
						echo '<figcaption data-ph-gallery-caption>' . esc_html( $hero[1] ) . '</figcaption>';
					}
				echo '</figure>';

				if ( ! empty( $rail ) ) {
					echo '<div class="ph-template-gallery-rail">';
					foreach ( $rail as $index => $image ) {
						$is_active = ( 0 === $index );
						echo '<button type="button" class="ph-template-gallery-thumb' . ( $is_active ? ' is-active' : '' ) . '" data-ph-gallery-thumb data-src="' . esc_url( self::demo_asset( $image[0] ) ) . '" data-alt="' . esc_attr( $image[1] ) . '" data-caption="' . esc_attr( $image[1] ) . '" aria-label="' . esc_attr( sprintf(
							/* translators: %s: image label */
							__( 'Show %s', 'propertyhive' ),
							$image[1]
						) ) . '"' . ( $is_active ? ' aria-current="true"' : '' ) . '>';
							echo '<img src="' . esc_url( self::demo_asset( $image[0] ) ) . '" alt="' . esc_attr( $image[1] ) . '" loading="lazy">';
							if ( $is_editorial ) {
								echo '<span>' . esc_html( $image[1] ) . '</span>';
							}
						echo '</button>';
					}
					echo '</div>';
				}

		echo '</div>';
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
	 * Render curated preview price.
	 */
	public static function render_demo_price() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$listing = self::get_demo_listing( self::get_detail_template() );

		echo '<p class="price ph-template-demo-price">' . esc_html( $listing['price'] ) . '</p>';
	}

	/**
	 * Render curated preview meta.
	 */
	public static function render_demo_meta() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$listing = self::get_demo_listing( self::get_detail_template() );

		if ( empty( $listing['meta'] ) ) {
			return;
		}

		echo '<div class="property_meta ph-template-demo-meta"><ul>';
		foreach ( $listing['meta'] as $item ) {
			echo '<li>' . esc_html( $item ) . '</li>';
		}
		echo '</ul></div>';
	}

	/**
	 * Render curated features in preview mode.
	 */
	public static function render_demo_features() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$listing = self::get_demo_listing( self::get_detail_template() );

		if ( empty( $listing['features'] ) ) {
			return;
		}

		echo '<div class="features ph-template-demo-features">';
			echo '<h4>' . esc_html__( 'Key features', 'propertyhive' ) . '</h4>';
			echo '<ul>';
			foreach ( $listing['features'] as $feature ) {
				echo '<li>' . esc_html( $feature ) . '</li>';
			}
			echo '</ul>';
		echo '</div>';
	}

	/**
	 * Render curated summary in preview mode.
	 */
	public static function render_demo_summary() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$listing = self::get_demo_listing( self::get_detail_template() );

		if ( empty( $listing['summary'] ) ) {
			return;
		}

		echo '<div class="summary ph-template-demo-summary">';
			echo '<h4>' . esc_html__( 'Overview', 'propertyhive' ) . '</h4>';
			echo '<div class="summary-contents">' . esc_html( $listing['summary'] ) . '</div>';
		echo '</div>';
	}

	/**
	 * Render curated description in preview mode.
	 */
	public static function render_demo_description() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$listing = self::get_demo_listing( self::get_detail_template() );

		if ( empty( $listing['description'] ) ) {
			return;
		}

		echo '<div class="description ph-template-demo-description">';
			echo '<h4>' . esc_html__( 'Full details', 'propertyhive' ) . '</h4>';
			echo '<div class="description-contents">';
			foreach ( $listing['description'] as $paragraph ) {
				echo '<p>' . esc_html( $paragraph ) . '</p>';
			}
			echo '</div>';
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
			echo '<div class="thumbnail"><a href="javascript:;" aria-label="' . esc_attr( $card['title'] ) . '"><img src="' . esc_url( self::demo_asset( $card['image'] ) ) . '" alt="' . esc_attr( $card['title'] ) . '" loading="lazy"></a><span class="ph-template-badges"><span class="ph-template-badge">' . esc_html( $card['badge'] ) . '</span></span></div>';
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

		$listing = self::get_demo_listing( self::get_detail_template() );
		$agency  = self::get_demo_agency();

		echo '<section class="ph-template-modules" aria-label="' . esc_attr__( 'Property information', 'propertyhive' ) . '">';
			echo '<article class="ph-template-module-card ph-template-module-floorplan">';
				echo '<h4>' . esc_html__( 'Floorplan', 'propertyhive' ) . '</h4>';
				echo '<div class="ph-template-floorplan" aria-hidden="true">';
					echo '<span class="ph-template-floorplan-room ph-template-room-reception">' . esc_html__( 'Reception', 'propertyhive' ) . '</span>';
					echo '<span class="ph-template-floorplan-room ph-template-room-kitchen">' . esc_html__( 'Kitchen', 'propertyhive' ) . '</span>';
					echo '<span class="ph-template-floorplan-room ph-template-room-bed-one">' . esc_html__( 'Bed 1', 'propertyhive' ) . '</span>';
					echo '<span class="ph-template-floorplan-room ph-template-room-bed-two">' . esc_html__( 'Bed 2', 'propertyhive' ) . '</span>';
					echo '<span class="ph-template-floorplan-room ph-template-room-bath">' . esc_html__( 'Bath', 'propertyhive' ) . '</span>';
				echo '</div>';
				echo '<p class="ph-template-module-foot">' . esc_html( $listing['floor_area'] ) . '</p>';
			echo '</article>';

			echo '<article class="ph-template-module-card ph-template-module-epc">';
				echo '<h4>' . esc_html__( 'Energy performance', 'propertyhive' ) . '</h4>';
				echo '<div class="ph-template-epc">';
					$bands = array( 'A', 'B', 'C', 'D', 'E', 'F', 'G' );
					foreach ( $bands as $index => $band ) {
						$score_min = array( 92, 81, 69, 55, 39, 21, 1 );
						$score_max = array( 100, 91, 80, 68, 54, 38, 20 );
						$marker    = '';
						if ( $listing['epc_now'] >= $score_min[ $index ] && $listing['epc_now'] <= $score_max[ $index ] ) {
							$marker = '<span class="ph-template-epc-now">' . (int) $listing['epc_now'] . '</span>';
						}
						echo '<span class="ph-template-epc-band ph-template-epc-band-' . esc_attr( strtolower( $band ) ) . '">' . esc_html( $band ) . $marker . '</span>';
					}
				echo '</div>';
				echo '<p class="ph-template-module-foot">' . sprintf(
					/* translators: 1: current EPC score, 2: potential EPC score */
					esc_html__( 'Current %1$d / Potential %2$d', 'propertyhive' ),
					(int) $listing['epc_now'],
					(int) $listing['epc_next']
				) . '</p>';
			echo '</article>';

			echo '<article class="ph-template-module-card ph-template-module-map">';
				echo '<h4>' . esc_html__( 'Location', 'propertyhive' ) . '</h4>';
				echo '<div class="ph-template-module-map-surface" aria-hidden="true"><span class="ph-template-map-pin"></span><span class="ph-template-map-label">' . esc_html( $listing['area'] ) . '</span></div>';
				echo '<ul class="ph-template-area-list">';
				foreach ( $listing['connections'] as $row ) {
					echo '<li><span>' . esc_html( $row[0] ) . '</span><strong>' . esc_html( $row[1] ) . '</strong></li>';
				}
				echo '</ul>';
			echo '</article>';

			echo '<article class="ph-template-module-card ph-template-module-documents">';
				echo '<h4>' . esc_html__( 'Documents and viewing', 'propertyhive' ) . '</h4>';
				echo '<div class="ph-template-doc-row">';
					echo '<span class="ph-template-doc-pill"><span class="ph-template-doc-icon" aria-hidden="true"></span>' . esc_html__( 'Brochure', 'propertyhive' ) . '</span>';
					echo '<span class="ph-template-doc-pill"><span class="ph-template-doc-icon" aria-hidden="true"></span>' . esc_html__( 'Floorplan', 'propertyhive' ) . '</span>';
					echo '<span class="ph-template-doc-pill"><span class="ph-template-doc-icon" aria-hidden="true"></span>' . esc_html__( 'EPC', 'propertyhive' ) . '</span>';
				echo '</div>';
				echo '<p class="ph-template-module-foot">' . sprintf(
					/* translators: %s: agency name */
					esc_html__( 'Brochure pack available from %s.', 'propertyhive' ),
					esc_html( $agency['office'] )
				) . '</p>';
			echo '</article>';
		echo '</section>';
	}

	/**
	 * Render a similar-properties strip in preview mode.
	 */
	public static function render_similar_properties() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$template = self::get_detail_template();
		$is_let   = ( 'lettings-detail' === $template );
		$is_new   = ( 'new-homes-development-detail' === $template );

		if ( $is_let ) {
			$cards = array(
				array( 'atlas-apartment-living.png', __( 'Two bedroom apartment, Wharf Lane', 'propertyhive' ), self::demo_price( '2,450' ) . __( ' pcm', 'propertyhive' ), __( '2 bed / 2 bath', 'propertyhive' ) ),
				array( 'cavendish-living-room.png', __( 'One bedroom flat, Quay Street', 'propertyhive' ), self::demo_price( '1,795' ) . __( ' pcm', 'propertyhive' ), __( '1 bed / balcony', 'propertyhive' ) ),
				array( 'cavendish-kitchen-dining.png', __( 'Two bedroom duplex, Mill Court', 'propertyhive' ), self::demo_price( '2,950' ) . __( ' pcm', 'propertyhive' ), __( '2 bed / concierge', 'propertyhive' ) ),
			);
		} elseif ( $is_new ) {
			$cards = array(
				array( 'elm-yard-development.png', __( 'Elm Yard Plot 4', 'propertyhive' ), __( 'From ', 'propertyhive' ) . self::demo_price( '485,000' ), __( '2 bed apartment', 'propertyhive' ) ),
				array( 'cavendish-kitchen-dining.png', __( 'Elm Yard Plot 11', 'propertyhive' ), __( 'From ', 'propertyhive' ) . self::demo_price( '625,000' ), __( '3 bed townhouse', 'propertyhive' ) ),
				array( 'cavendish-garden-terrace.png', __( 'Elm Yard Garden Home', 'propertyhive' ), __( 'From ', 'propertyhive' ) . self::demo_price( '710,000' ), __( '3 bed / courtyard', 'propertyhive' ) ),
			);
		} else {
			$cards = array(
				array( 'cavendish-exterior.png', __( 'Period townhouse, Cavendish Road', 'propertyhive' ), self::demo_price( '1,250,000' ), __( '4 bed / terrace', 'propertyhive' ) ),
				array( 'cavendish-living-room.png', __( 'Garden maisonette, Devonshire Street', 'propertyhive' ), self::demo_price( '925,000' ), __( '2 bed / garden', 'propertyhive' ) ),
				array( 'cavendish-kitchen-dining.png', __( 'Upper floor apartment, Weymouth Street', 'propertyhive' ), self::demo_price( '1,100,000' ), __( '3 bed / lift', 'propertyhive' ) ),
			);
		}

		echo '<section class="ph-template-similar-properties" aria-label="' . esc_attr__( 'Similar properties', 'propertyhive' ) . '">';
			echo '<div class="ph-template-section-heading">';
				echo '<p>' . esc_html__( 'Also available', 'propertyhive' ) . '</p>';
				echo '<h2>' . esc_html__( 'Similar homes nearby', 'propertyhive' ) . '</h2>';
			echo '</div>';
			echo '<div class="ph-template-similar-grid">';
			foreach ( $cards as $card ) {
				echo '<article class="ph-template-similar-card">';
					echo '<img src="' . esc_url( self::demo_asset( $card[0] ) ) . '" alt="' . esc_attr( $card[1] ) . '" loading="lazy">';
					echo '<div>';
						echo '<h3>' . esc_html( $card[1] ) . '</h3>';
						echo '<p>' . esc_html( $card[2] ) . '</p>';
						echo '<span>' . esc_html( $card[3] ) . '</span>';
					echo '</div>';
				echo '</article>';
			}
			echo '</div>';
		echo '</section>';
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
		if ( 'yes' !== $settings['template_set_show_badges'] ) {
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
		$facts    = self::get_fact_items( $property, self::get_search_fact_limit() );
		$phone    = $property->get_negotiator_telephone_number();

		if ( empty( $facts ) && 'yes' !== $settings['template_set_show_branch'] ) {
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

			if ( 'yes' === $settings['template_set_show_branch'] && ( $property->get_office_name() || $phone ) ) {
				echo '<div class="ph-template-card-branch">';
					if ( $property->get_office_name() ) {
						echo '<span>' . esc_html( $property->get_office_name() ) . '</span>';
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

		global $post, $property;

		if ( ! $property ) {
			return;
		}

		$template = self::get_detail_template();
		$button   = self::get_primary_cta_label( $property, $template );
		$hint     = self::get_contact_hint( $template );
		$is_demo  = self::is_demo_preview();

		if ( $is_demo ) {
			$agency  = self::get_demo_agency();
			$phone   = $agency['phone'];
			$email   = $agency['email'];
			$office  = $agency['office'];
			$address = $agency['address'];
		} else {
			$phone   = $property->get_negotiator_telephone_number();
			$email   = $property->get_negotiator_email_address();
			$office  = $property->get_office_name();
			$address = $property->get_office_address();
		}

		$office_alt = $office ? $office : __( 'Agent', 'propertyhive' );

		echo '<aside class="ph-template-detail-contact-card' . ( $is_demo ? ' is-demo' : '' ) . '" aria-label="' . esc_attr__( 'Property contact', 'propertyhive' ) . '">';

			if ( $is_demo ) {
				echo '<div class="ph-template-contact-agent">';
					echo '<span class="ph-template-contact-portrait"><img src="' . esc_url( self::demo_asset( $agency['portrait'] ) ) . '" alt="' . esc_attr( $agency['agent'] ) . '" loading="lazy"></span>';
					echo '<span class="ph-template-contact-agent-meta">';
						echo '<span class="ph-template-contact-agent-name">' . esc_html( $agency['agent'] ) . '</span>';
						echo '<span class="ph-template-contact-agent-role">' . esc_html( $agency['role'] . ', ' . $agency['branch'] ) . '</span>';
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

				echo '<a class="ph-template-button ph-template-button-secondary" data-fancybox data-src="#makeEnquiry' . (int) $post->ID . '" href="javascript:;">' . esc_html( $button ) . '</a>';

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
		if ( 'yes' !== $settings['template_set_show_mobile_cta'] ) {
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
	 * Can a valid template preview render while the global setting is inactive?
	 *
	 * @return bool
	 */
	private static function can_render_preview_request() {
		if ( is_admin() || ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_propertyhive' ) ) ) {
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
		if ( '' !== self::get_query_template( self::DETAIL_QUERY_ARG, self::get_detail_templates() ) ) {
			return true;
		}

		if ( '' !== self::get_query_template( self::SEARCH_QUERY_ARG, self::get_search_templates() ) ) {
			return true;
		}

		if ( '' !== self::get_query_template( self::MODULE_QUERY_ARG, self::get_module_templates() ) ) {
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
		return array( self::DETAIL_QUERY_ARG, self::SEARCH_QUERY_ARG, self::MODULE_QUERY_ARG, self::CATALOG_QUERY_ARG, 'ph_view' );
	}

	/**
	 * Get the currently represented catalogue template.
	 *
	 * @return string
	 */
	private static function get_current_catalog_template() {
		if ( self::is_module_preview() ) {
			return self::get_module_template();
		}

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
		if ( is_admin() || ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_propertyhive' ) ) ) {
			return false;
		}

		return is_property() || is_post_type_archive( 'property' );
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
		$url = remove_query_arg( self::get_preview_query_args(), self::get_current_url() );

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
		$documents = array();

		if ( self::has_floorplan( $property ) ) {
			$documents[] = __( 'Floorplan', 'propertyhive' );
		}

		if ( self::has_epc( $property ) ) {
			$documents[] = __( 'EPC', 'propertyhive' );
		}

		if ( self::has_brochure( $property ) ) {
			$documents[] = __( 'Brochure', 'propertyhive' );
		}

		if ( ! empty( $property->get_virtual_tours() ) ) {
			$documents[] = __( 'Virtual tour', 'propertyhive' );
		}

		if ( empty( $documents ) ) {
			return __( 'Ask agent', 'propertyhive' );
		}

		return implode( ', ', array_slice( $documents, 0, 3 ) );
	}

	/**
	 * Summarise core facts in one short string.
	 *
	 * @param PH_Property $property Property object.
	 * @return string
	 */
	private static function get_fact_summary( $property ) {
		$facts = self::get_fact_items( $property, 3 );

		if ( empty( $facts ) ) {
			return __( 'Details available', 'propertyhive' );
		}

		$parts = array();
		foreach ( $facts as $fact ) {
			$parts[] = trim( $fact['value'] . ' ' . strtolower( $fact['label'] ) );
		}

		return implode( ' / ', $parts );
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
		$links = array();

		if ( ! empty( $property->get_floorplan_attachment_ids() ) || ( is_array( $property->_floorplan_urls ) && ! empty( $property->_floorplan_urls ) ) ) {
			$links[] = __( 'Floorplan', 'propertyhive' );
		}

		if ( ! empty( $property->get_epc_attachment_ids() ) || ( is_array( $property->_epc_urls ) && ! empty( $property->_epc_urls ) ) ) {
			$links[] = __( 'EPC', 'propertyhive' );
		}

		if ( ! empty( $property->get_brochure_attachment_ids() ) || ( is_array( $property->_brochure_urls ) && ! empty( $property->_brochure_urls ) ) ) {
			$links[] = __( 'Brochure', 'propertyhive' );
		}

		if ( ! empty( $property->get_virtual_tours() ) ) {
			$links[] = __( 'Virtual tour', 'propertyhive' );
		}

		if ( empty( $links ) ) {
			return;
		}

		echo '<ul class="ph-template-media-links">';
		foreach ( array_slice( $links, 0, 4 ) as $link ) {
			echo '<li>' . esc_html( $link ) . '</li>';
		}
		echo '</ul>';
	}
}

PH_Template_Set::init();
