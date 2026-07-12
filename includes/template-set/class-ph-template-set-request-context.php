<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template set request, preview, and page context.
 */
class PH_Template_Set_Request_Context {

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
	 * Is the global template set enabled?
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$settings = PH_Template_Set_Settings::get_settings();

		if ( isset( $settings[ PH_Template_Set::OPTION_ENABLED ] ) && 'yes' === $settings[ PH_Template_Set::OPTION_ENABLED ] ) {
			return true;
		}

		return self::can_render_preview_request();
	}

	/**
	 * Get the selected detail template.
	 *
	 * @return string
	 */
	public static function get_detail_template() {
		$settings  = PH_Template_Set_Settings::get_settings();
		$templates = PH_Template_Set_Catalog::get_detail_templates();
		$template  = self::get_query_template( PH_Template_Set::DETAIL_QUERY_ARG, $templates );

		if ( empty( $template ) ) {
			$template = sanitize_title( $settings['template_set_detail_template'] );
		}

		return isset( $templates[ $template ] ) ? $template : PH_Template_Set_Catalog::get_default_detail_template();
	}

	/**
	 * Get the selected search template.
	 *
	 * @return string
	 */
	public static function get_search_template() {
		$settings  = PH_Template_Set_Settings::get_settings();
		$templates = PH_Template_Set_Catalog::get_search_templates();
		$template  = self::get_query_template( PH_Template_Set::SEARCH_QUERY_ARG, $templates );

		if ( empty( $template ) ) {
			$template = sanitize_title( $settings['template_set_search_template'] );
		}

		return isset( $templates[ $template ] ) ? $template : PH_Template_Set_Catalog::get_default_search_template();
	}

	/**
	 * Get the selected homepage/module template.
	 *
	 * @return string
	 */
	public static function get_module_template() {
		$templates = PH_Template_Set_Catalog::get_module_templates();
		$template  = self::get_query_template( PH_Template_Set::MODULE_QUERY_ARG, $templates );

		return isset( $templates[ $template ] ) ? $template : '';
	}

	/**
	 * Build a preview URL for a catalogue template.
	 *
	 * @param string $template Template slug.
	 * @return string
	 */
	public static function get_template_preview_url( $template ) {
		$catalog = PH_Template_Set_Catalog::get_template_catalog();
		$template = sanitize_title( $template );

		if ( ! isset( $catalog[ $template ] ) ) {
			return self::get_current_url();
		}

		if ( 'detail' === $catalog[ $template ]['type'] ) {
			$url = self::get_sample_property_url( $template );
			return add_query_arg( PH_Template_Set::DETAIL_QUERY_ARG, $template, $url );
		}

		$archive_url = get_post_type_archive_link( 'property' );
		if ( ! $archive_url ) {
			$archive_url = home_url( '/' );
		}

		if ( 'module' === $catalog[ $template ]['type'] ) {
			return add_query_arg( PH_Template_Set::MODULE_QUERY_ARG, $template, $archive_url );
		}

		return add_query_arg( PH_Template_Set::SEARCH_QUERY_ARG, $template, $archive_url );
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
			PH_Template_Set::DETAIL_QUERY_ARG => self::get_detail_template(),
		);

		if ( self::is_template_editor_active() ) {
			$args[ PH_Template_Set::EDIT_QUERY_ARG ] = '1';
		} elseif ( self::is_template_editor_closed_request() ) {
			$args[ PH_Template_Set::EDIT_CLOSED_QUERY_ARG ] = '1';
		}

		return add_query_arg( $args, $url );
	}

	/**
	 * Redirect generic catalogue preview requests to the correct page type.
	 */
	public static function redirect_catalog_preview_request() {
		if ( empty( $_GET[ PH_Template_Set::CATALOG_QUERY_ARG ] ) ) {
			return;
		}

		$template = sanitize_title( wp_unslash( $_GET[ PH_Template_Set::CATALOG_QUERY_ARG ] ) );
		$catalog  = PH_Template_Set_Catalog::get_template_catalog();

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

		$catalog         = PH_Template_Set_Catalog::get_template_catalog();
		$current_slug    = self::get_current_catalog_template();
		$current_label   = isset( $catalog[ $current_slug ] ) ? $catalog[ $current_slug ]['label'] : __( 'Template Set', 'propertyhive' );
		$settings_url    = admin_url( 'admin.php?page=ph-settings&tab=frontend&section=template-set' );
		$root_id         = 'ph-template-set';
		$inactive_suffix = self::is_enabled() ? '' : ' ' . __( '(inactive)', 'propertyhive' );
		$editor_url      = add_query_arg( PH_Template_Set::EDIT_QUERY_ARG, '1', remove_query_arg( PH_Template_Set::EDIT_CLOSED_QUERY_ARG, self::get_current_url() ) );
		$exit_editor_url = add_query_arg( PH_Template_Set::EDIT_CLOSED_QUERY_ARG, '1', remove_query_arg( PH_Template_Set::EDIT_QUERY_ARG, self::get_current_url() ) );

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
				PH_Template_Set_Catalog::get_short_template_group_label( $template['type'] ),
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
	 * Is the current archive rendering the module preview?
	 *
	 * @return bool
	 */
	public static function is_module_preview() {
		if ( ! is_post_type_archive( 'property' ) ) {
			return false;
		}

		return '' !== self::get_query_template( PH_Template_Set::MODULE_QUERY_ARG, PH_Template_Set_Catalog::get_module_templates() );
	}

	/**
	 * Is the current archive rendering a search-template preview?
	 *
	 * @return bool
	 */
	public static function is_search_preview() {
		if ( ! is_post_type_archive( 'property' ) ) {
			return false;
		}

		return '' !== self::get_query_template( PH_Template_Set::SEARCH_QUERY_ARG, PH_Template_Set_Catalog::get_search_templates() );
	}

	/**
	 * Is any template preview query active?
	 *
	 * @return bool
	 */
	public static function is_previewing_template() {
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
	public static function is_template_editor_active() {
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
	public static function is_template_editor_request() {
		return ! empty( $_GET[ PH_Template_Set::EDIT_QUERY_ARG ] );
	}

	/**
	 * Is this request explicitly asking to hide the front-end editor?
	 *
	 * @return bool
	 */
	public static function is_template_editor_closed_request() {
		return ! empty( $_GET[ PH_Template_Set::EDIT_CLOSED_QUERY_ARG ] );
	}

	/**
	 * Can a valid template preview render while the global setting is inactive?
	 *
	 * @return bool
	 */
	public static function can_render_preview_request() {
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
	public static function has_valid_preview_request() {
		if ( self::is_template_editor_request() && ( is_property() || is_post_type_archive( 'property' ) ) ) {
			return true;
		}

		if ( '' !== self::get_query_template( PH_Template_Set::DETAIL_QUERY_ARG, PH_Template_Set_Catalog::get_detail_templates() ) ) {
			return true;
		}

		if ( '' !== self::get_query_template( PH_Template_Set::SEARCH_QUERY_ARG, PH_Template_Set_Catalog::get_search_templates() ) ) {
			return true;
		}

		if ( '' !== self::get_query_template( PH_Template_Set::MODULE_QUERY_ARG, PH_Template_Set_Catalog::get_module_templates() ) ) {
			return true;
		}

		if ( empty( $_GET[ PH_Template_Set::CATALOG_QUERY_ARG ] ) ) {
			return false;
		}

		$template = sanitize_title( wp_unslash( $_GET[ PH_Template_Set::CATALOG_QUERY_ARG ] ) );
		$catalog  = PH_Template_Set_Catalog::get_template_catalog();

		return isset( $catalog[ $template ] );
	}

	/**
	 * Query args used by template preview routes.
	 *
	 * @return array
	 */
	public static function get_preview_query_args() {
		return array( PH_Template_Set::DETAIL_QUERY_ARG, PH_Template_Set::SEARCH_QUERY_ARG, PH_Template_Set::MODULE_QUERY_ARG, PH_Template_Set::CATALOG_QUERY_ARG, PH_Template_Set::EDIT_QUERY_ARG, 'ph_view' );
	}

	/**
	 * Query args to clear when leaving the preview/editor flow.
	 *
	 * @return array
	 */
	public static function get_preview_clear_query_args() {
		return array_merge( self::get_preview_query_args(), array( PH_Template_Set::EDIT_CLOSED_QUERY_ARG ) );
	}

	/**
	 * Get the currently represented catalogue template.
	 *
	 * @return string
	 */
	public static function get_current_catalog_template() {
		if ( is_property() ) {
			return self::get_detail_template();
		}

		if ( is_post_type_archive( 'property' ) ) {
			if ( self::is_module_preview() ) {
				return self::get_module_template();
			}

			return self::get_search_template();
		}

		$settings = PH_Template_Set_Settings::get_settings();
		return sanitize_title( $settings['template_set_detail_template'] );
	}

	/**
	 * Can the current user switch templates on this front-end page?
	 *
	 * @return bool
	 */
	public static function can_show_template_switcher() {
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
	public static function can_manage_template_set() {
		return current_user_can( 'manage_options' ) || current_user_can( 'manage_propertyhive' );
	}

	/**
	 * Get the selected gallery layout.
	 *
	 * @return string
	 */
	public static function get_gallery_layout() {
		$layout = sanitize_title( self::get_detail_setting( 'template_set_gallery_layout' ) );

		return isset( PH_Template_Set_Options::get_gallery_layouts()[ $layout ] ) ? $layout : 'showcase';
	}

	/**
	 * Get a setting resolved for the current detail template.
	 *
	 * @param string $key Setting key.
	 * @return mixed
	 */
	public static function get_detail_setting( $key ) {
		return PH_Template_Set_Settings::get_for_template( $key, self::get_detail_template() );
	}

	public static function get_button_style() {
		$value = sanitize_title( self::get_detail_setting( 'template_set_button_style' ) );
		return isset( PH_Template_Set_Options::get_button_styles()[ $value ] ) ? $value : 'filled';
	}

	public static function get_contact_card_style() {
		$value = sanitize_title( self::get_detail_setting( 'template_set_contact_card_style' ) );
		return isset( PH_Template_Set_Options::get_contact_card_styles()[ $value ] ) ? $value : 'classic';
	}

	public static function get_show_mobile_cta() {
		return 'yes' === self::get_detail_setting( 'template_set_show_mobile_cta' ) ? 'yes' : '';
	}

	public static function get_show_floorplans() {
		return 'yes' === self::get_detail_setting( 'template_set_show_floorplans' ) ? 'yes' : '';
	}

	public static function get_show_virtual_tours() {
		return 'yes' === self::get_detail_setting( 'template_set_show_virtual_tours' ) ? 'yes' : '';
	}

	public static function get_show_recommended() {
		return 'yes' === self::get_detail_setting( 'template_set_show_recommended' ) ? 'yes' : '';
	}

	public static function get_recommended_count() {
		$value = absint( self::get_detail_setting( 'template_set_recommended_count' ) );
		return isset( PH_Template_Set_Options::get_recommended_property_counts()[ $value ] ) ? $value : 3;
	}

	public static function get_recommended_layout() {
		$value = sanitize_title( self::get_detail_setting( 'template_set_recommended_layout' ) );
		return isset( PH_Template_Set_Options::get_recommended_property_layouts()[ $value ] ) ? $value : 'grid';
	}

	public static function get_recommended_image_size() {
		$value = sanitize_title( self::get_detail_setting( 'template_set_recommended_image_size' ) );
		return isset( PH_Template_Set_Options::get_recommended_property_image_sizes()[ $value ] ) ? $value : 'standard';
	}

	public static function get_portal_show_costs() {
		return 'yes' === self::get_detail_setting( 'template_set_portal_show_costs' ) ? 'yes' : '';
	}

	public static function get_cinema_card_position() {
		$value = sanitize_title( self::get_detail_setting( 'template_set_cinema_card_position' ) );
		return in_array( $value, array( 'right', 'left' ), true ) ? $value : 'right';
	}

	public static function get_editorial_show_brief() {
		return 'yes' === self::get_detail_setting( 'template_set_editorial_show_brief' ) ? 'yes' : '';
	}

	/**
	 * Get a valid preview template from the query string.
	 *
	 * @param string $query_arg Query arg name.
	 * @param array  $templates Allowed templates.
	 * @return string
	 */
	public static function get_query_template( $query_arg, $templates ) {
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
	public static function get_template_switch_url( $query_arg, $template ) {
		$url = remove_query_arg( self::get_preview_clear_query_args(), self::get_current_url() );

		return add_query_arg( $query_arg, sanitize_title( $template ), $url );
	}

	/**
	 * Get a published property URL for detail template previews.
	 *
	 * @param string $template Template slug.
	 * @return string
	 */
	public static function get_sample_property_url( $template = '' ) {
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
	public static function get_detail_template_sample_department( $template ) {
		return 'lettings-detail' === $template ? 'residential-lettings' : 'residential-sales';
	}

	/**
	 * Does a property match the preferred preview department?
	 *
	 * @param int    $property_id Property ID.
	 * @param string $department  Expected Property Hive department.
	 * @return bool
	 */
	public static function property_matches_sample_department( $property_id, $department ) {
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
	public static function get_current_url() {
		return home_url( add_query_arg( null, null ) );
	}

	/**
	 * Get the active search department from the request/settings.
	 *
	 * @return string
	 */
	public static function get_current_search_department() {
		if ( ! empty( $_REQUEST['department'] ) ) {
			return sanitize_text_field( wp_unslash( $_REQUEST['department'] ) );
		}

		$department = get_option( 'propertyhive_primary_department', 'residential-sales' );

		if ( '' !== $department ) {
			return sanitize_text_field( $department );
		}

		foreach ( ph_get_departments() as $key => $label ) {
			if ( 'yes' === get_option( 'propertyhive_active_departments_' . str_replace( 'residential-', '', $key ) ) ) {
				return sanitize_text_field( $key );
			}
		}

		return 'residential-sales';
	}

	/**
	 * Get the department constraint that should validate a taxonomy dropdown.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return string
	 */
	public static function get_search_department_for_taxonomy( $taxonomy ) {
		$department = self::get_current_search_department();
		$base       = ph_get_custom_department_based_on( $department );

		if ( false === $base ) {
			$base = $department;
		}

		if ( 'commercial_property_type' === $taxonomy ) {
			return 'commercial' === $base ? $department : '';
		}

		if ( 'property_type' === $taxonomy ) {
			return in_array( $base, array( 'residential-sales', 'residential-lettings' ), true ) ? $department : '';
		}

		return '';
	}

	/**
	 * Get current search view.
	 *
	 * @return string
	 */
	public static function get_search_view() {
		$view     = isset( $_GET['ph_view'] ) ? sanitize_title( wp_unslash( $_GET['ph_view'] ) ) : '';
		$template = self::get_search_template();
		$settings = PH_Template_Set_Settings::get_settings();

		$map_search_view = isset( $_GET['view'] ) ? sanitize_title( wp_unslash( $_GET['view'] ) ) : '';

		if (
			'map' === $map_search_view &&
			class_exists( 'PH_Map_Search' ) &&
			class_exists( 'PH_Template_Set' ) &&
			method_exists( 'PH_Template_Set', 'is_add_on_usable' ) &&
			PH_Template_Set::is_add_on_usable( 'propertyhive-map-search' )
		) {
			return 'map';
		}

		if ( 'compact-list-search-results' === $template ) {
			return 'list';
		}

		if ( isset( PH_Template_Set_Options::get_search_layouts()[ $view ] ) ) {
			return $view;
		}

		$view = isset( $settings['template_set_search_layout'] ) ? sanitize_title( $settings['template_set_search_layout'] ) : '';

		if ( isset( PH_Template_Set_Options::get_search_layouts()[ $view ] ) ) {
			return $view;
		}

		if ( 'brand-led-agency-search-results' === $template ) {
			return 'grid';
		}

		if ( 'map-led-search-results' === $template ) {
			return 'map';
		}

		return 'list';
	}
}
