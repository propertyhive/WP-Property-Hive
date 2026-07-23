<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Search result template set callbacks.
 */
trait PH_Template_Set_Search {

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

		$settings      = self::get_settings();
		$is_detail     = is_property();
		$button_style  = $is_detail ? PH_Template_Set_Request_Context::get_button_style() : $settings['template_set_button_style'];
		$contact_style = $is_detail ? PH_Template_Set_Request_Context::get_contact_card_style() : $settings['template_set_contact_card_style'];
		$classes[] = 'ph-template-set-active';
		$classes[] = 'ph-template-images-' . sanitize_html_class( $settings['template_set_image_style'] );
		$classes[] = 'ph-template-buttons-' . sanitize_html_class( $button_style );
		$classes[] = 'ph-template-contact-card-' . sanitize_html_class( $contact_style );
		$classes[] = 'ph-template-editor-mode-' . sanitize_html_class( $settings['template_set_editor_mode'] );
		$classes[] = 'yes' === $settings['template_set_show_branch'] ? 'ph-template-show-branch' : 'ph-template-hide-branch';
		$classes[] = 'yes' === $settings['template_set_show_badges'] ? 'ph-template-show-badges' : 'ph-template-hide-badges';
		$classes[] = 'yes' === ( $is_detail ? PH_Template_Set_Request_Context::get_show_mobile_cta() : $settings['template_set_show_mobile_cta'] ) ? 'ph-template-show-mobile-cta' : 'ph-template-hide-mobile-cta';
		$classes[] = 'yes' === ( $is_detail ? PH_Template_Set_Request_Context::get_show_floorplans() : $settings['template_set_show_floorplans'] ) ? 'ph-template-show-floorplans' : 'ph-template-hide-floorplans';
		$classes[] = 'yes' === ( $is_detail ? PH_Template_Set_Request_Context::get_show_virtual_tours() : $settings['template_set_show_virtual_tours'] ) ? 'ph-template-show-virtual-tours' : 'ph-template-hide-virtual-tours';
		$classes[] = 'yes' === ( $is_detail ? PH_Template_Set_Request_Context::get_show_recommended() : $settings['template_set_show_recommended'] ) ? 'ph-template-show-recommended' : 'ph-template-hide-recommended';
		$classes[] = 'ph-template-recommended-count-' . absint( $is_detail ? PH_Template_Set_Request_Context::get_recommended_count() : $settings['template_set_recommended_count'] );
		$classes[] = 'ph-template-recommended-layout-' . sanitize_html_class( $is_detail ? PH_Template_Set_Request_Context::get_recommended_layout() : $settings['template_set_recommended_layout'] );
		$classes[] = 'ph-template-recommended-images-' . sanitize_html_class( $is_detail ? PH_Template_Set_Request_Context::get_recommended_image_size() : $settings['template_set_recommended_image_size'] );

		if ( self::is_template_editor_active() ) {
			$classes[] = 'ph-template-editor-active';
		}

		if ( self::is_demo_preview() ) {
			$classes[] = 'ph-template-preview-mode';
		}

		if ( is_property() ) {
			$detail_template = self::get_detail_template();
			$public_template = PH_Template_Set_Catalog::get_detail_template_public_slug( $detail_template );
			$classes[] = 'ph-detail-template-' . sanitize_html_class( $detail_template );
			$classes[] = 'ph-detail-template-' . sanitize_html_class( $public_template );

			if ( 'conversion-first-sales-detail' === $detail_template ) {
				$classes[] = 'yes' === PH_Template_Set_Request_Context::get_portal_show_costs() ? 'ph-template-show-portal-costs' : 'ph-template-hide-portal-costs';
			}

			if ( 'immersive-cinema-detail' === $detail_template ) {
				$classes[] = 'ph-template-cinema-card-' . sanitize_html_class( PH_Template_Set_Request_Context::get_cinema_card_position() );
			}

			if ( 'premium-editorial-detail' === $detail_template ) {
				$classes[] = 'yes' === PH_Template_Set_Request_Context::get_editorial_show_brief() ? 'ph-template-show-editorial-brief' : 'ph-template-hide-editorial-brief';
			}
		}

		if ( is_post_type_archive( 'property' ) ) {
			$classes[] = 'ph-search-template-' . sanitize_html_class( self::get_search_template() );
			$classes[] = 'ph-search-view-' . sanitize_html_class( self::get_search_view() );
			$classes[] = 'ph-search-card-size-' . sanitize_html_class( $settings['template_set_search_card_size'] );
			$classes[] = 'ph-search-grid-columns-' . absint( $settings['template_set_search_grid_columns'] );

			if ( self::is_map_search_managing_search_view() ) {
				$classes[] = 'ph-template-map-search-active';
			}

			if ( self::is_real_map_search_view() ) {
				$classes[] = 'ph-template-map-search-real-map';
			}

			if ( class_exists( 'PH_Radial_Search' ) && self::is_add_on_usable( 'propertyhive-radial-search' ) ) {
				$classes[] = 'ph-template-radial-search-active';
			}

			if ( class_exists( 'PH_Location_Autocomplete' ) && self::is_add_on_usable( 'propertyhive-location-autocomplete' ) ) {
				$classes[] = 'ph-template-location-autocomplete-active';
			}
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
			$detail_template = self::get_detail_template();
			$public_template = PH_Template_Set_Catalog::get_detail_template_public_slug( $detail_template );
			$classes[] = 'ph-template-set';
			$classes[] = 'ph-template-detail';
			$classes[] = 'ph-detail-template-' . sanitize_html_class( $detail_template );
			$classes[] = 'ph-detail-template-' . sanitize_html_class( $public_template );
		}

		if ( ( self::is_enabled() && ! is_property() ) || self::$rendering_module ) {
			$settings  = self::get_settings();
			$classes[] = 'ph-template-card';
			$classes[] = 'ph-search-template-' . sanitize_html_class( self::get_search_template() );
			$classes[] = 'ph-template-images-' . sanitize_html_class( $settings['template_set_image_style'] );
			$classes[] = 'ph-search-card-size-' . sanitize_html_class( $settings['template_set_search_card_size'] );

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
	 * Align the base search-result card hooks to the template-set card pattern.
	 */
	public static function prepare_search_result_cards() {
		if ( ! self::is_enabled() || ! is_post_type_archive( 'property' ) ) {
			return;
		}

		$hook = 'propertyhive_after_search_results_loop_item_title';

		self::remove_named_action_callbacks( $hook, 'propertyhive_template_loop_price' );
		self::remove_named_action_callbacks( $hook, 'propertyhive_template_loop_actions' );
		remove_action( $hook, array( __CLASS__, 'render_linked_card_price' ), 10 );
		add_action( $hook, array( __CLASS__, 'render_linked_card_price' ), 10 );
	}

	/**
	 * Render a linked result price so the image, address and price all lead to the property.
	 */
	public static function render_linked_card_price() {
		global $property;

		if ( ! $property ) {
			return;
		}

		$fees = '';
		if ( 'yes' === get_option( 'propertyhive_lettings_fees_display_search_results', '' ) ) {
			if ( 'residential-lettings' === $property->department && '' !== get_option( 'propertyhive_lettings_fees', '' ) ) {
				$fees = nl2br( get_option( 'propertyhive_lettings_fees', '' ) );
			}

			if ( 'commercial' === $property->department && 'yes' === $property->to_rent && '' !== get_option( 'propertyhive_lettings_fees_commercial', '' ) ) {
				$fees = nl2br( get_option( 'propertyhive_lettings_fees_commercial', '' ) );
			}
		}

		$price_qualifier = '';
		if (
			(
				'residential-sales' === $property->department ||
				'residential-sales' === ph_get_custom_department_based_on( $property->department ) ||
				'commercial' === $property->department ||
				'commercial' === ph_get_custom_department_based_on( $property->department )
			) &&
			'' !== $property->price_qualifier
		) {
			$price_qualifier = $property->price_qualifier;
		}

		PH_Template_Set_Template_Loader::render(
			'search',
			self::get_search_template(),
			'linked-card-price',
			array(
				'property'        => $property,
				'price'           => $property->get_formatted_price(),
				'price_qualifier' => $price_qualifier,
				'fees'            => $fees,
				'permalink'       => get_permalink(),
			)
		);
	}

	/**
	 * Remove a named callback wherever the saved search-result field order placed it.
	 *
	 * @param string $hook_name     Hook name.
	 * @param string $callback_name Callback function name.
	 */
	private static function remove_named_action_callbacks( $hook_name, $callback_name ) {
		global $wp_filter;

		if ( empty( $wp_filter[ $hook_name ] ) || ! is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
			return;
		}

		$priorities = array();

		foreach ( $wp_filter[ $hook_name ]->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( isset( $callback['function'] ) && $callback_name === $callback['function'] ) {
					$priorities[] = (int) $priority;
				}
			}
		}

		foreach ( array_unique( $priorities ) as $priority ) {
			remove_action( $hook_name, $callback_name, $priority );
		}
	}

	/**
	 * Keep search type controls aligned to real searchable stock.
	 *
	 * @param array $fields Search form fields.
	 * @return array
	 */
	public static function prepare_search_form_fields( $fields ) {
		if ( ! self::is_enabled() || ! is_post_type_archive( 'property' ) ) {
			return $fields;
		}

		foreach ( array( 'property_type', 'commercial_property_type' ) as $field_id ) {
			if ( empty( $fields[ $field_id ] ) || ! is_array( $fields[ $field_id ] ) ) {
				continue;
			}

			$fields[ $field_id ]['hide_empty']   = true;
			$fields[ $field_id ]['blank_option'] = __( 'All property types', 'propertyhive' );
		}

		return $fields;
	}

	/**
	 * Match hidden empty-term checks to the currently selected department.
	 *
	 * @param array $query_args Empty-check query args.
	 * @param array $field      Form field config.
	 * @param int   $term_id    Term ID being checked.
	 * @return array
	 */
	public static function filter_search_taxonomy_empty_check_args( $query_args, $field, $term_id ) {
		if ( ! self::is_enabled() || ! is_post_type_archive( 'property' ) || empty( $field['type'] ) ) {
			return $query_args;
		}

		if ( ! in_array( $field['type'], array( 'property_type', 'commercial_property_type' ), true ) ) {
			return $query_args;
		}

		$department = self::get_search_department_for_taxonomy( $field['type'] );

		if ( '' === $department ) {
			return $query_args;
		}

		if ( empty( $query_args['meta_query'] ) || ! is_array( $query_args['meta_query'] ) ) {
			$query_args['meta_query'] = array();
		}

		$query_args['post_status']      = 'publish';
		$query_args['posts_per_page']  = 1;
		$query_args['no_found_rows']   = true;
		$query_args['meta_query'][]    = array(
			'key'     => '_department',
			'value'   => $department,
			'compare' => '=',
		);

		return $query_args;
	}

	/**
	 * Open search wrapper.
	 */
	public static function open_search_wrapper() {
		if ( ! self::is_enabled() || ! is_post_type_archive( 'property' ) ) {
			return;
		}

		$settings = self::get_settings();
		$classes = array(
			'ph-template-set',
			'ph-template-search',
			'ph-search-template-' . sanitize_html_class( self::get_search_template() ),
			'ph-search-view-' . sanitize_html_class( self::get_search_view() ),
			'ph-search-card-size-' . sanitize_html_class( $settings['template_set_search_card_size'] ),
			'ph-search-grid-columns-' . absint( $settings['template_set_search_grid_columns'] ),
		);

		if ( self::is_map_search_managing_search_view() ) {
			$classes[] = 'ph-template-map-search-active';
		}

		if ( self::is_real_map_search_view() ) {
			$classes[] = 'ph-template-map-search-real-map';
		}

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

		// Search layout is controlled from the visual template editor.
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

		PH_Template_Set_Template_Loader::render(
			'search',
			$template,
			'intro',
			array(
				'template' => $template,
				'content'  => $content,
			)
		);
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

		if ( self::is_map_search_managing_search_view() && ! self::is_template_editor_active() ) {
			return;
		}

		if ( 'map-led-search-results' !== self::get_search_template() && 'map' !== self::get_search_view() && ! self::is_template_editor_active() ) {
			return;
		}

		$template = self::get_search_template();

		PH_Template_Set_Template_Loader::render(
			'search',
			$template,
			'map-panel',
			array(
				'template'      => $template,
				'search_view'   => self::get_search_view(),
				'editor_active' => self::is_template_editor_active(),
			)
		);
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
		return self::is_module_preview() ? false : $show_results;
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

		PH_Template_Set_Template_Loader::render(
			'search',
			self::get_search_template(),
			'card-badges',
			array(
				'property' => $property,
				'badges'   => $badges,
				'template' => self::get_search_template(),
			)
		);
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
		$shortlist_button = self::get_shortlist_button_markup();

		if ( empty( $facts ) && ! $show_branch && '' === $shortlist_button ) {
			return;
		}

		$office = self::get_display_office_name( $property );

		PH_Template_Set_Template_Loader::render(
			'search',
			self::get_search_template(),
			'card-footer',
			array(
				'property'         => $property,
				'facts'            => $facts,
				'phone'            => $phone,
				'office'           => $office,
				'show_branch'      => $show_branch,
				'shortlist_button' => $shortlist_button,
				'template'         => self::get_search_template(),
			)
		);
	}

	/**
	 * Check whether a premium add-on can be used on this install.
	 *
	 * @param string $slug Add-on slug.
	 * @return bool
	 */
	public static function is_add_on_usable( $slug ) {
		$slug = sanitize_key( $slug );

		if ( '' === $slug ) {
			return false;
		}

		return apply_filters( 'propertyhive_add_on_can_be_used', true, $slug ) !== false;
	}

	/**
	 * Is Map Search active and configured to control the search map UI.
	 *
	 * @return bool
	 */
	private static function is_map_search_managing_search_view() {
		if ( ! class_exists( 'PH_Map_Search' ) || ! self::is_add_on_usable( 'propertyhive-map-search' ) ) {
			return false;
		}

		return in_array( self::get_map_search_format(), array( 'view', 'split' ), true );
	}

	/**
	 * Is a real Map Search map expected on the current results page.
	 *
	 * @return bool
	 */
	private static function is_real_map_search_view() {
		if ( ! self::is_map_search_managing_search_view() ) {
			return false;
		}

		$format = self::get_map_search_format();

		if ( 'split' === $format ) {
			return true;
		}

		$view = isset( $_GET['view'] ) ? sanitize_title( wp_unslash( $_GET['view'] ) ) : '';

		return 'map' === $view;
	}

	/**
	 * Get the Map Search display format.
	 *
	 * @return string
	 */
	private static function get_map_search_format() {
		$settings = get_option( 'propertyhive_map_search', array() );

		if ( ! is_array( $settings ) || empty( $settings['format'] ) ) {
			return '';
		}

		return sanitize_key( $settings['format'] );
	}

	/**
	 * Get a Shortlist add-on button for search result cards / detail panels.
	 *
	 * @param string $class  Anchor class attribute.
	 * @param array  $labels Optional add/remove labels: keys `add`, `remove`.
	 * @return string
	 */
	private static function get_shortlist_button_markup( $class = 'ph-template-shortlist-button', $labels = array() ) {
		if ( ! class_exists( 'PH_Shortlist' ) || ! self::is_add_on_usable( 'propertyhive-shortlist' ) || ! shortcode_exists( 'shortlist_button' ) ) {
			return '';
		}

		$class = trim( (string) $class );

		if ( '' === $class ) {
			$class = 'ph-template-shortlist-button';
		}

		$markup = do_shortcode( '[shortlist_button class="' . esc_attr( $class ) . '"]' );

		if ( '' === $markup || empty( $labels ) || ! is_array( $labels ) ) {
			return $markup;
		}

		$default_add    = __( 'Add To Shortlist', 'propertyhive' );
		$default_remove = __( 'Remove From Shortlist', 'propertyhive' );
		$add_label      = isset( $labels['add'] ) ? (string) $labels['add'] : $default_add;
		$remove_label   = isset( $labels['remove'] ) ? (string) $labels['remove'] : $default_remove;

		// Replace visible label text even when the shortcode wraps it in extra markup.
		$markup = preg_replace(
			'/(>(?:\s|&nbsp;)*)' . preg_quote( $default_add, '/' ) . '((?:\s|&nbsp;)*<\/)/u',
			'$1' . $add_label . '$2',
			$markup,
			1
		);
		$markup = preg_replace(
			'/(>(?:\s|&nbsp;)*)' . preg_quote( $default_remove, '/' ) . '((?:\s|&nbsp;)*<\/)/u',
			'$1' . $remove_label . '$2',
			$markup,
			1
		);
		$markup = str_replace(
			array( $default_add, $default_remove ),
			array( $add_label, $remove_label ),
			$markup
		);

		// Keep AJAX toggle labels in sync with custom microcopy.
		// Prefer late localization so later default shortlist buttons do not clobber cinema labels.
		$localize = static function() use ( $add_label, $remove_label ) {
			wp_localize_script(
				'ph-shortlist',
				'propertyhive_shortlist',
				array(
					'ajax_url'         => admin_url( 'admin-ajax.php' ),
					'add_link_text'    => $add_label,
					'remove_link_text' => $remove_label,
					'loading_text'     => __( 'Loading', 'propertyhive' ),
				)
			);
		};
		$localize();
		// Late footer pass so default shortlist localizations cannot clobber cinema labels.
		if ( ! has_action( 'wp_print_footer_scripts', $localize ) ) {
			add_action( 'wp_print_footer_scripts', $localize, 99 );
		}

		return $markup;
	}

	/**
	 * Whether the Send To Friend add-on is available for a Share control.
	 *
	 * @return bool
	 */
	private static function is_send_to_friend_available() {
		if ( shortcode_exists( 'send_to_friend_form' ) && self::is_add_on_usable( 'propertyhive-send-to-friend' ) ) {
			return true;
		}

		if ( class_exists( 'PH_Send_To_Friend' ) || class_exists( 'PH_Send_To_A_Friend' ) ) {
			return self::is_add_on_usable( 'propertyhive-send-to-friend' );
		}

		return false;
	}

	/**
	 * Get Share markup from the Send To Friend add-on (via propertyhive_single_property_actions).
	 *
	 * Returns empty string when the add-on is not active — no invented fallback.
	 *
	 * @param string $class Anchor class attribute.
	 * @return string
	 */
	private static function get_share_button_markup( $class = 'ph-template-button ph-template-button-secondary ph-template-share-button' ) {
		if ( ! self::is_send_to_friend_available() ) {
			return '';
		}

		$actions = apply_filters( 'propertyhive_single_property_actions', array() );

		if ( empty( $actions ) || ! is_array( $actions ) ) {
			return '';
		}

		$share_action = null;

		foreach ( $actions as $action ) {
			if ( empty( $action ) || ! is_array( $action ) ) {
				continue;
			}

			$action_class = isset( $action['class'] ) ? (string) $action['class'] : '';
			$label        = isset( $action['label'] ) ? (string) $action['label'] : '';
			$data_src     = '';

			if ( ! empty( $action['attributes'] ) && is_array( $action['attributes'] ) && isset( $action['attributes']['data-src'] ) ) {
				$data_src = (string) $action['attributes']['data-src'];
			}

			if (
				false !== stripos( $action_class, 'friend' )
				|| false !== stripos( $action_class, 'send-to-friend' )
				|| false !== stripos( $label, 'friend' )
				|| false !== stripos( $data_src, 'friend' )
				|| false !== stripos( $data_src, 'sendToFriend' )
			) {
				$share_action = $action;
				break;
			}
		}

		if ( ! $share_action ) {
			return '';
		}

		$class = trim( (string) $class );

		if ( '' === $class ) {
			$class = 'ph-template-button ph-template-button-secondary ph-template-share-button';
		}

		$href  = isset( $share_action['href'] ) ? (string) $share_action['href'] : '';
		$attrs = array();

		if ( ! empty( $share_action['attributes'] ) && is_array( $share_action['attributes'] ) ) {
			foreach ( $share_action['attributes'] as $key => $value ) {
				$key = sanitize_key( $key );
				if ( '' === $key ) {
					continue;
				}
				$attrs[] = esc_attr( $key ) . '="' . esc_attr( (string) $value ) . '"';
			}
		}

		return '<a href="' . esc_url( $href ? $href : 'javascript:;' ) . '" class="' . esc_attr( $class ) . '"'
			. ( $attrs ? ' ' . implode( ' ', $attrs ) : '' )
			. '>' . esc_html__( 'Share', 'propertyhive' ) . '</a>';
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
					'body'   => __( 'Compare listings with filters, sorting and a clear route into map view.', 'propertyhive' ),
					'items'  => array(
						__( 'Refine', 'propertyhive' ),
						__( 'Compare homes', 'propertyhive' ),
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
}
