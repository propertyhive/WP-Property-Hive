<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Front-end visual editor integration for the default search form.
 */
class PH_Template_Set_Search_Form_Editor {

	const NONCE_ACTION = 'propertyhive_template_set_search_form_editor';
	const FORM_ID = 'default';

	/**
	 * Hook in AJAX endpoints.
	 */
	public static function init() {
		add_action( 'wp_ajax_propertyhive_search_form_editor_preview', array( __CLASS__, 'ajax_preview' ) );
		add_action( 'wp_ajax_propertyhive_search_form_editor_save', array( __CLASS__, 'ajax_save' ) );
	}

	/**
	 * Can the current user manage search forms from the visual editor?
	 *
	 * @return bool
	 */
	public static function can_manage() {
		$capability = apply_filters( 'propertyhive_template_set_search_form_editor_capability', 'manage_options' );

		return current_user_can( $capability );
	}

	/**
	 * Script data for the visual editor.
	 *
	 * @param string $context Editor context.
	 * @return array
	 */
	public static function get_script_data( $context ) {
		if ( 'search' !== $context || ! self::can_manage() ) {
			return array(
				'enabled' => false,
			);
		}

		$manager = new PH_Search_Form_Manager();
		$data    = $manager->get_editor_data( self::FORM_ID );

		$data['enabled']         = true;
		$data['security']        = wp_create_nonce( self::NONCE_ACTION );
		$data['previewAction']   = 'propertyhive_search_form_editor_preview';
		$data['saveAction']      = 'propertyhive_search_form_editor_save';
		$data['advancedUrl']     = admin_url( 'admin.php?page=ph-settings&tab=frontend&section=editsearchform&id=' . self::FORM_ID );
		$data['previewSelector'] = '.property-search-form-' . self::FORM_ID;

		return $data;
	}

	/**
	 * Render the sidebar shell.
	 */
	public static function render_sidebar_section() {
		if ( ! self::can_manage() ) {
			return;
		}

		echo '<section class="ph-template-editor-section ph-template-editor-source-section ph-template-editor-search-form-section">';
			echo '<h3>' . esc_html__( 'Search form', 'propertyhive' ) . '</h3>';
			echo '<div class="ph-template-editor-field-builder" data-ph-template-editor-panel-control="ph_search_form_builder" data-ph-search-form-builder>';
				echo '<div class="ph-search-form-builder-status" data-ph-search-form-builder-status>' . esc_html__( 'Loading search form fields...', 'propertyhive' ) . '</div>';
				echo '<noscript>' . esc_html__( 'JavaScript is required to edit the search form here.', 'propertyhive' ) . '</noscript>';
			echo '</div>';
		echo '</section>';
	}

	/**
	 * AJAX preview.
	 */
	public static function ajax_preview() {
		self::verify_ajax_request();

		$payload = self::get_payload_from_request();
		$manager = new PH_Search_Form_Manager();
		$html    = $manager->render_preview_from_payload( self::FORM_ID, $payload );

		if ( is_wp_error( $html ) ) {
			wp_send_json_error( array( 'message' => $html->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'html' => $html,
			)
		);
	}

	/**
	 * AJAX save.
	 */
	public static function ajax_save() {
		self::verify_ajax_request();

		$payload   = self::get_payload_from_request();
		$base_hash = isset( $_POST['base_hash'] ) ? sanitize_text_field( wp_unslash( $_POST['base_hash'] ) ) : '';
		$manager   = new PH_Search_Form_Manager();
		$result    = $manager->save_editor_payload( self::FORM_ID, $payload, $base_hash );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		$html = $manager->render_preview_from_payload(
			self::FORM_ID,
			array(
				'active_fields'   => $result['active'],
				'inactive_fields' => $result['inactive'],
			)
		);

		wp_send_json_success(
			array(
				'message' => __( 'Search form saved.', 'propertyhive' ),
				'editor'  => $result,
				'html'    => is_wp_error( $html ) ? '' : $html,
			)
		);
	}

	/**
	 * Verify nonce and capability.
	 */
	private static function verify_ajax_request() {
		if ( ! self::can_manage() ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to edit search forms.', 'propertyhive' ) ), 403 );
		}

		check_ajax_referer( self::NONCE_ACTION, 'security' );
	}

	/**
	 * Get JSON payload from the request.
	 *
	 * @return array
	 */
	private static function get_payload_from_request() {
		$raw = isset( $_POST['payload'] ) ? wp_unslash( $_POST['payload'] ) : '';

		if ( is_array( $raw ) ) {
			return $raw;
		}

		$payload = json_decode( $raw, true );

		return is_array( $payload ) ? $payload : array();
	}
}
