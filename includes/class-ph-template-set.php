<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once dirname( __FILE__ ) . '/template-set/class-ph-template-set-template-loader.php';
include_once dirname( __FILE__ ) . '/template-set/traits/trait-ph-template-set-search.php';
include_once dirname( __FILE__ ) . '/template-set/traits/trait-ph-template-set-preview.php';
include_once dirname( __FILE__ ) . '/template-set/traits/trait-ph-template-set-detail.php';
include_once dirname( __FILE__ ) . '/template-set/traits/trait-ph-template-set-shortcodes.php';
include_once dirname( __FILE__ ) . '/template-set/traits/trait-ph-template-set-property-data.php';

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

	use PH_Template_Set_Search;
	use PH_Template_Set_Preview;
	use PH_Template_Set_Detail;
	use PH_Template_Set_Shortcodes;
	use PH_Template_Set_Property_Data;

	/**
	 * Hook in methods.
	 */
	public static function init() {
		PH_Template_Set_Search_Form_Editor::init();

		add_filter( 'propertyhive_enqueue_styles', array( __CLASS__, 'enqueue_styles' ) );
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ) );
		add_filter( 'post_class', array( __CLASS__, 'post_classes' ), 25, 3 );
		add_filter( 'loop_search_results_columns', array( __CLASS__, 'search_result_columns' ), 20 );
		add_filter( 'post_type_link', array( __CLASS__, 'preserve_template_preview_on_property_links' ), 20, 2 );
		add_filter( 'propertyhive_search_form_fields', array( __CLASS__, 'prepare_search_form_fields' ), 20 );
		add_filter( 'propertyhive_taxonomy_hide_empty_args', array( __CLASS__, 'filter_search_taxonomy_empty_check_args' ), 20, 3 );

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
		add_action( 'propertyhive_before_search_results_loop', array( __CLASS__, 'prepare_search_result_cards' ), 1 );
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

	public static function is_demo_preview() {
		return PH_Template_Set_Request_Context::is_demo_preview();
	}

	private static function asset_version( $relative_path ) {
		return PH_Template_Set_Assets::asset_version( $relative_path );
	}

	public static function enqueue_styles( $styles ) {
		return PH_Template_Set_Assets::enqueue_styles( $styles );
	}

	public static function enqueue_scripts() {
		return PH_Template_Set_Assets::enqueue_scripts();
	}

	/**
	 * Register shortcodes.
	 */
	public static function register_shortcodes() {
		add_shortcode( 'propertyhive_featured_template', array( __CLASS__, 'featured_template_shortcode' ) );
	}

	public static function get_settings() {
		return PH_Template_Set_Settings::get_settings();
	}

	private static function get_default_editor_mode( $settings ) {
		return PH_Template_Set_Settings::get_default_editor_mode( $settings );
	}

	private static function has_legacy_frontend_settings( $settings ) {
		return PH_Template_Set_Settings::has_legacy_frontend_settings( $settings );
	}

	public static function sanitize_template_set_settings( $raw_settings, $current_settings = array(), $activate = false ) {
		return PH_Template_Set_Settings::sanitize_template_set_settings( $raw_settings, $current_settings, $activate );
	}

	private static function normalise_checkbox_value( $settings, $key ) {
		return PH_Template_Set_Settings::normalise_checkbox_value( $settings, $key );
	}

	public static function get_editor_modes() {
		return PH_Template_Set_Options::get_editor_modes();
	}

	public static function get_gallery_layouts() {
		return PH_Template_Set_Options::get_gallery_layouts();
	}

	public static function get_button_styles() {
		return PH_Template_Set_Options::get_button_styles();
	}

	public static function get_search_layouts() {
		return PH_Template_Set_Options::get_search_layouts();
	}

	public static function get_search_card_sizes() {
		return PH_Template_Set_Options::get_search_card_sizes();
	}

	public static function get_search_grid_column_options() {
		return PH_Template_Set_Options::get_search_grid_column_options();
	}

	public static function get_image_styles() {
		return PH_Template_Set_Options::get_image_styles();
	}

	public static function get_contact_card_styles() {
		return PH_Template_Set_Options::get_contact_card_styles();
	}

	public static function get_recommended_property_counts() {
		return PH_Template_Set_Options::get_recommended_property_counts();
	}

	public static function get_recommended_property_layouts() {
		return PH_Template_Set_Options::get_recommended_property_layouts();
	}

	public static function get_recommended_property_image_sizes() {
		return PH_Template_Set_Options::get_recommended_property_image_sizes();
	}

	public static function is_enabled() {
		return PH_Template_Set_Request_Context::is_enabled();
	}

	public static function get_detail_templates() {
		return PH_Template_Set_Catalog::get_detail_templates();
	}

	public static function get_search_templates() {
		return PH_Template_Set_Catalog::get_search_templates();
	}

	public static function get_module_templates() {
		return PH_Template_Set_Catalog::get_module_templates();
	}

	public static function get_template_catalog() {
		return PH_Template_Set_Catalog::get_template_catalog();
	}

	public static function get_detail_template() {
		return PH_Template_Set_Request_Context::get_detail_template();
	}

	public static function get_search_template() {
		return PH_Template_Set_Request_Context::get_search_template();
	}

	public static function get_module_template() {
		return PH_Template_Set_Request_Context::get_module_template();
	}

	public static function get_template_preview_url( $template ) {
		return PH_Template_Set_Request_Context::get_template_preview_url( $template );
	}

	public static function preserve_template_preview_on_property_links( $url, $post ) {
		return PH_Template_Set_Request_Context::preserve_template_preview_on_property_links( $url, $post );
	}

	public static function redirect_catalog_preview_request() {
		return PH_Template_Set_Request_Context::redirect_catalog_preview_request();
	}

	public static function add_admin_bar_menu( $wp_admin_bar ) {
		return PH_Template_Set_Request_Context::add_admin_bar_menu( $wp_admin_bar );
	}

	public static function print_style_variables() {
		return PH_Template_Set_Assets::print_style_variables( self::$rendering_module );
	}

	public static function render_template_editor() {
		return PH_Template_Set_Editor_Controller::render_template_editor();
	}

	private static function get_template_editor_context() {
		return PH_Template_Set_Editor_Controller::get_template_editor_context();
	}

	private static function get_template_editor_title( $context ) {
		return PH_Template_Set_Editor_Controller::get_template_editor_title( $context );
	}

	private static function render_template_editor_section_start( $title ) {
		return PH_Template_Set_Editor_Controller::render_template_editor_section_start( $title );
	}

	private static function render_template_editor_section_end() {
		return PH_Template_Set_Editor_Controller::render_template_editor_section_end();
	}

	private static function render_template_editor_select( $name, $label, $options, $selected, $option_urls = array() ) {
		return PH_Template_Set_Editor_Controller::render_template_editor_select( $name, $label, $options, $selected, $option_urls );
	}

	private static function get_template_editor_preview_urls( $templates ) {
		return PH_Template_Set_Editor_Controller::get_template_editor_preview_urls( $templates );
	}

	private static function render_template_editor_hidden( $name, $value ) {
		return PH_Template_Set_Editor_Controller::render_template_editor_hidden( $name, $value );
	}

	private static function render_template_editor_checkbox( $name, $label, $value ) {
		return PH_Template_Set_Editor_Controller::render_template_editor_checkbox( $name, $label, $value );
	}

	public static function ajax_save_template_editor() {
		return PH_Template_Set_Editor_Controller::ajax_save_template_editor();
	}

	private static function get_script_data() {
		return PH_Template_Set_Editor_Controller::get_script_data();
	}

	private static function get_editor_sidebar_layout() {
		return PH_Template_Set_Editor_Controller::get_editor_sidebar_layout();
	}

	private static function get_public_settings( $settings ) {
		return PH_Template_Set_Settings::get_public_settings( $settings );
	}

	private static function is_module_preview() {
		return PH_Template_Set_Request_Context::is_module_preview();
	}

	private static function is_search_preview() {
		return PH_Template_Set_Request_Context::is_search_preview();
	}

	private static function is_previewing_template() {
		return PH_Template_Set_Request_Context::is_previewing_template();
	}

	private static function is_template_editor_active() {
		return PH_Template_Set_Request_Context::is_template_editor_active();
	}

	private static function is_template_editor_request() {
		return PH_Template_Set_Request_Context::is_template_editor_request();
	}

	private static function is_template_editor_closed_request() {
		return PH_Template_Set_Request_Context::is_template_editor_closed_request();
	}

	private static function can_render_preview_request() {
		return PH_Template_Set_Request_Context::can_render_preview_request();
	}

	private static function has_valid_preview_request() {
		return PH_Template_Set_Request_Context::has_valid_preview_request();
	}

	private static function get_preview_query_args() {
		return PH_Template_Set_Request_Context::get_preview_query_args();
	}

	private static function get_preview_clear_query_args() {
		return PH_Template_Set_Request_Context::get_preview_clear_query_args();
	}

	private static function get_current_catalog_template() {
		return PH_Template_Set_Request_Context::get_current_catalog_template();
	}

	private static function get_short_template_group_label( $type ) {
		return PH_Template_Set_Catalog::get_short_template_group_label( $type );
	}

	private static function can_show_template_switcher() {
		return PH_Template_Set_Request_Context::can_show_template_switcher();
	}

	private static function can_manage_template_set() {
		return PH_Template_Set_Request_Context::can_manage_template_set();
	}

	private static function get_gallery_layout() {
		return PH_Template_Set_Request_Context::get_gallery_layout();
	}

	private static function get_query_template( $query_arg, $templates ) {
		return PH_Template_Set_Request_Context::get_query_template( $query_arg, $templates );
	}

	private static function get_template_switch_url( $query_arg, $template ) {
		return PH_Template_Set_Request_Context::get_template_switch_url( $query_arg, $template );
	}

	private static function get_sample_property_url( $template = '' ) {
		return PH_Template_Set_Request_Context::get_sample_property_url( $template );
	}

	private static function get_detail_template_sample_department( $template ) {
		return PH_Template_Set_Request_Context::get_detail_template_sample_department( $template );
	}

	private static function property_matches_sample_department( $property_id, $department ) {
		return PH_Template_Set_Request_Context::property_matches_sample_department( $property_id, $department );
	}

	private static function get_current_url() {
		return PH_Template_Set_Request_Context::get_current_url();
	}

	private static function get_current_search_department() {
		return PH_Template_Set_Request_Context::get_current_search_department();
	}

	private static function get_search_department_for_taxonomy( $taxonomy ) {
		return PH_Template_Set_Request_Context::get_search_department_for_taxonomy( $taxonomy );
	}

	private static function get_search_view() {
		return PH_Template_Set_Request_Context::get_search_view();
	}

}

include_once dirname( __FILE__ ) . '/template-set/class-ph-template-set-options.php';
include_once dirname( __FILE__ ) . '/template-set/class-ph-template-set-catalog.php';
include_once dirname( __FILE__ ) . '/template-set/class-ph-template-set-settings.php';
include_once dirname( __FILE__ ) . '/template-set/class-ph-template-set-request-context.php';
include_once dirname( __FILE__ ) . '/template-set/class-ph-template-set-search-form-editor.php';
include_once dirname( __FILE__ ) . '/template-set/class-ph-template-set-editor-controller.php';
include_once dirname( __FILE__ ) . '/template-set/class-ph-template-set-assets.php';

PH_Template_Set::init();
