<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template set front-end editor shell and AJAX save.
 */
class PH_Template_Set_Editor_Controller {

	/**
	 * Render the front-end template editor shell.
	 */
	public static function render_template_editor() {
		if ( ! PH_Template_Set_Request_Context::is_template_editor_active() ) {
			return;
		}

		$settings     = PH_Template_Set_Settings::get_settings();
		$context      = self::get_template_editor_context();
		$exit_url     = add_query_arg( PH_Template_Set::EDIT_CLOSED_QUERY_ARG, '1', remove_query_arg( PH_Template_Set::EDIT_QUERY_ARG, PH_Template_Set_Request_Context::get_current_url() ) );
		$settings_url = admin_url( 'admin.php?page=ph-settings&tab=frontend&section=template-set' );

		echo '<aside class="ph-template-editor ph-template-editor-' . esc_attr( sanitize_html_class( $context ) ) . '" data-ph-template-editor data-ph-template-editor-context="' . esc_attr( $context ) . '" aria-label="' . esc_attr__( 'Template editor', 'propertyhive' ) . '">';
			echo '<form class="ph-template-editor-form" data-ph-template-editor-form>';
				echo '<header class="ph-template-editor-header">';
					echo '<div class="ph-template-editor-brand">';
						echo '<img src="' . esc_url( PH()->plugin_url() . '/assets/images/admin/propertyhive-logo-onboarding.png' ) . '" alt="' . esc_attr__( 'Property Hive', 'propertyhive' ) . '">';
						echo '<span>' . esc_html__( 'Template editor', 'propertyhive' ) . '</span>';
						echo '<h2>' . esc_html( self::get_template_editor_title( $context ) ) . '</h2>';
					echo '</div>';
					echo '<a href="' . esc_url( $exit_url ) . '" aria-label="' . esc_attr__( 'Exit template editor', 'propertyhive' ) . '">&times;</a>';
				echo '</header>';

				echo '<input type="hidden" name="template_set_enabled" value="yes">';
				echo '<input type="hidden" name="template_set_editor_mode" value="' . esc_attr( PH_Template_Set::EDITOR_MODE_VISUAL ) . '">';
				echo '<input type="hidden" name="template_set_editor_context" value="' . esc_attr( $context ) . '">';

				self::render_template_editor_section_start( __( 'Template', 'propertyhive' ) );
				if ( 'search' === $context ) {
					self::render_template_editor_hidden( 'template_set_detail_template', PH_Template_Set_Request_Context::get_detail_template() );
					self::render_template_editor_select( 'template_set_search_template', __( 'Search template', 'propertyhive' ), PH_Template_Set_Catalog::get_search_templates(), PH_Template_Set_Request_Context::get_search_template(), self::get_template_editor_preview_urls( PH_Template_Set_Catalog::get_search_templates() ) );
				} else {
					self::render_template_editor_hidden( 'template_set_search_template', PH_Template_Set_Request_Context::get_search_template() );
					self::render_template_editor_select( 'template_set_detail_template', __( 'Detail template', 'propertyhive' ), PH_Template_Set_Catalog::get_detail_templates(), PH_Template_Set_Request_Context::get_detail_template(), self::get_template_editor_preview_urls( PH_Template_Set_Catalog::get_detail_templates() ) );
				}
				self::render_template_editor_section_end();

				if ( 'search' === $context ) {
					PH_Template_Set_Search_Form_Editor::render_sidebar_section();

					self::render_template_editor_section_start( __( 'Search result cards', 'propertyhive' ) );
					self::render_template_editor_select( 'template_set_search_layout', __( 'Results layout', 'propertyhive' ), PH_Template_Set_Options::get_search_layouts(), PH_Template_Set_Request_Context::get_search_view() );
					self::render_template_editor_select( 'template_set_search_card_size', __( 'Card size', 'propertyhive' ), PH_Template_Set_Options::get_search_card_sizes(), $settings['template_set_search_card_size'] );
					self::render_template_editor_select( 'template_set_search_grid_columns', __( 'Cards per row', 'propertyhive' ), PH_Template_Set_Options::get_search_grid_column_options(), $settings['template_set_search_grid_columns'] );
					self::render_template_editor_select( 'template_set_image_style', __( 'Photo shape', 'propertyhive' ), PH_Template_Set_Options::get_image_styles(), $settings['template_set_image_style'] );
					self::render_template_editor_checkbox( 'template_set_show_branch', __( 'Show branch contact details', 'propertyhive' ), $settings['template_set_show_branch'] );
					self::render_template_editor_checkbox( 'template_set_show_badges', __( 'Show property labels', 'propertyhive' ), $settings['template_set_show_badges'] );
					self::render_template_editor_section_end();
				} else {
					self::render_template_editor_hidden( 'template_set_search_layout', $settings['template_set_search_layout'] );
					self::render_template_editor_hidden( 'template_set_search_card_size', $settings['template_set_search_card_size'] );
					self::render_template_editor_hidden( 'template_set_search_grid_columns', $settings['template_set_search_grid_columns'] );
					self::render_template_editor_hidden( 'template_set_image_style', $settings['template_set_image_style'] );
					self::render_template_editor_hidden( 'template_set_show_branch', $settings['template_set_show_branch'] );
					self::render_template_editor_hidden( 'template_set_show_badges', $settings['template_set_show_badges'] );

					self::render_detail_manifest_controls( PH_Template_Set_Request_Context::get_detail_template() );
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
	public static function get_template_editor_context() {
		return is_post_type_archive( 'property' ) ? 'search' : 'detail';
	}

	/**
	 * Get the editor title for the current page context.
	 *
	 * @param string $context Editor context.
	 * @return string
	 */
	public static function get_template_editor_title( $context ) {
		return 'search' === $context ? __( 'Search results', 'propertyhive' ) : __( 'Property pages', 'propertyhive' );
	}

	/**
	 * Render editor section start.
	 *
	 * @param string $title Section title.
	 */
	public static function render_template_editor_section_start( $title ) {
		echo '<section class="ph-template-editor-section ph-template-editor-source-section">';
			echo '<h3>' . esc_html( $title ) . '</h3>';
	}

	/**
	 * Render editor section end.
	 */
	public static function render_template_editor_section_end() {
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
	public static function render_template_editor_select( $name, $label, $options, $selected, $option_urls = array() ) {
		echo '<label class="ph-template-editor-field ph-template-editor-field-' . esc_attr( sanitize_html_class( $name ) ) . '">';
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
	public static function get_template_editor_preview_urls( $templates ) {
		$urls = array();

		foreach ( $templates as $template => $label ) {
			$urls[ $template ] = add_query_arg( PH_Template_Set::EDIT_QUERY_ARG, '1', PH_Template_Set_Request_Context::get_template_preview_url( $template ) );
		}

		return $urls;
	}

	/**
	 * Preserve a setting when it is not shown in the current editor context.
	 *
	 * @param string $name  Control name.
	 * @param string $value Current value.
	 */
	public static function render_template_editor_hidden( $name, $value ) {
		echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
	}

	/**
	 * Render an editor checkbox control.
	 *
	 * @param string $name Control name.
	 * @param string $label Control label.
	 * @param string $value Current value.
	 */
	public static function render_template_editor_checkbox( $name, $label, $value ) {
		echo '<label class="ph-template-editor-toggle">';
			echo '<input type="checkbox" name="' . esc_attr( $name ) . '" value="yes"' . checked( 'yes', $value, false ) . ' data-ph-template-editor-control>';
			echo '<span>' . esc_html( $label ) . '</span>';
		echo '</label>';
	}

	/**
	 * Render only the controls declared by the current detail manifest.
	 *
	 * @param string $template Detail template slug.
	 */
	private static function render_detail_manifest_controls( $template ) {
		$manifest = PH_Template_Set_Catalog::get_detail_template_manifest( $template );
		$controls = PH_Template_Set_Catalog::get_detail_template_controls( $template );
		$groups   = self::get_detail_control_groups( $template );

		foreach ( $groups as $group ) {
			self::render_template_editor_section_start( $group['label'] );

			foreach ( $group['controls'] as $key ) {
				if ( ! isset( $controls[ $key ] ) ) {
					continue;
				}

				$control = $controls[ $key ];

				if ( array_key_exists( $key, (array) $manifest['locked'] ) ) {
					$value_label = isset( $control['options'][ $manifest['locked'][ $key ] ] ) ? $control['options'][ $manifest['locked'][ $key ] ] : $manifest['locked'][ $key ];
					echo '<div class="ph-template-editor-field ph-template-editor-field-locked" data-ph-template-editor-panel-control="' . esc_attr( $key ) . '">';
						echo '<span>' . esc_html( $control['label'] ) . '</span>';
						echo '<input type="text" value="' . esc_attr( $value_label ) . '" disabled>';
						echo '<small>' . esc_html__( "Set by this template's design", 'propertyhive' ) . '</small>';
						echo '</div>';
					continue;
				}

				$value = PH_Template_Set_Settings::get_for_template( $key, $template );

				if ( 'checkbox' === $control['type'] ) {
					self::render_template_editor_checkbox( $key, $control['label'], $value );
				} else {
					self::render_template_editor_select( $key, $control['label'], $control['options'], $value );
				}
			}

			self::render_template_editor_section_end();
		}
	}

	/**
	 * Save template editor settings.
	 */
	public static function ajax_save_template_editor() {
		if ( ! PH_Template_Set_Request_Context::can_manage_template_set() ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to edit templates.', 'propertyhive' ) ), 403 );
		}

		check_ajax_referer( PH_Template_Set::EDITOR_NONCE_ACTION, 'security' );

		$current_settings = get_option( 'propertyhive_template_assistant', array() );
		$settings         = PH_Template_Set_Settings::sanitize_template_set_settings( $_POST, $current_settings, true );

		update_option( 'propertyhive_template_assistant', $settings );

		wp_send_json_success(
			array(
				'message'  => __( 'Template saved.', 'propertyhive' ),
				'settings' => PH_Template_Set_Settings::get_public_settings( $settings ),
			)
		);
	}

	/**
	 * Front-end script data.
	 *
	 * @return array
	 */
	public static function get_script_data() {
		$settings = PH_Template_Set_Settings::get_settings();

		return array(
			'ajaxUrl'             => admin_url( 'admin-ajax.php' ),
			'security'            => wp_create_nonce( PH_Template_Set::EDITOR_NONCE_ACTION ),
			'editorActive'        => PH_Template_Set_Request_Context::is_template_editor_active(),
			'editorMode'          => $settings['template_set_editor_mode'],
			'settings'            => PH_Template_Set_Settings::get_public_settings( $settings ),
			'searchFormEditor'    => PH_Template_Set_Search_Form_Editor::get_script_data( self::get_template_editor_context() ),
			'editorSidebarLayout' => self::get_editor_sidebar_layout(),
			'labels'              => array(
				'ready'             => __( 'Ready', 'propertyhive' ),
				'changed'           => __( 'Unsaved changes', 'propertyhive' ),
				'loading'           => __( 'Loading...', 'propertyhive' ),
				'saving'            => __( 'Saving...', 'propertyhive' ),
				'saved'             => __( 'Saved', 'propertyhive' ),
				'error'             => __( 'Could not save', 'propertyhive' ),
				'unsavedNavigation' => __( 'You have unsaved changes. Leave this page without saving?', 'propertyhive' ),
			),
		);
	}

	/**
	 * Editor sidebar layout data for front-end JavaScript.
	 *
	 * @return array
	 */
	public static function get_editor_sidebar_layout() {
		$detail_groups = array_merge(
			array(
				array(
					'id'       => 'template',
					'label'    => __( 'Template', 'propertyhive' ),
					'controls' => array( 'template_set_detail_template' ),
				),
			),
			self::get_detail_control_groups( PH_Template_Set_Request_Context::get_detail_template() )
		);
		$detail_active = isset( $detail_groups[1]['id'] ) ? $detail_groups[1]['id'] : 'template';

		return array(
			'active' => array(
				'search' => 'layout',
				'detail' => $detail_active,
			),
			'groups' => array(
				'search' => array(
					array(
						'id'       => 'template',
						'label'    => __( 'Template', 'propertyhive' ),
						'controls' => array( 'template_set_search_template' ),
					),
					array(
						'id'       => 'search-form',
						'label'    => __( 'Search form', 'propertyhive' ),
						'controls' => array( 'ph_search_form_builder' ),
					),
					array(
						'id'       => 'layout',
						'label'    => __( 'Layout', 'propertyhive' ),
						'controls' => array( 'template_set_search_layout', 'template_set_search_grid_columns' ),
					),
					array(
						'id'       => 'card-appearance',
						'label'    => __( 'Card appearance', 'propertyhive' ),
						'controls' => array( 'template_set_search_card_size', 'template_set_image_style' ),
					),
					array(
						'id'       => 'details',
						'label'    => __( 'Details shown', 'propertyhive' ),
						'controls' => array( 'template_set_show_branch', 'template_set_show_badges' ),
					),
				),
				'detail' => $detail_groups,
			),
		);
	}

	/**
	 * Build grouped detail controls directly from the current manifest.
	 *
	 * @param string $template Detail template slug.
	 * @return array
	 */
	private static function get_detail_control_groups( $template ) {
		$controls = PH_Template_Set_Catalog::get_detail_template_controls( $template );
		$labels   = array(
			'media'       => __( 'Media', 'propertyhive' ),
			'enquiry'     => __( 'Enquiries', 'propertyhive' ),
			'modules'     => __( 'Modules', 'propertyhive' ),
			'recommended' => __( 'Related properties', 'propertyhive' ),
		);
		$groups = array();

		foreach ( $controls as $key => $control ) {
			$group = isset( $control['group'] ) ? sanitize_title( $control['group'] ) : 'details';

			if ( ! isset( $groups[ $group ] ) ) {
				$groups[ $group ] = array(
					'id'       => $group,
					'label'    => isset( $labels[ $group ] ) ? $labels[ $group ] : ucwords( str_replace( '-', ' ', $group ) ),
					'controls' => array(),
				);
			}

			$groups[ $group ]['controls'][] = $key;
		}

		return array_values( $groups );
	}
}
