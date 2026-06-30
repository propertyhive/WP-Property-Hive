<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Shared search form storage, sanitizing, preview and rendering helpers.
 */
class PH_Search_Form_Manager {

	const OPTION_NAME = 'propertyhive_template_assistant';
	const REVISION_LIMIT = 10;

	/**
	 * Field catalog.
	 *
	 * @var PH_Search_Form_Field_Catalog
	 */
	private $catalog;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->catalog = new PH_Search_Form_Field_Catalog();
	}

	/**
	 * Get full Template Assistant settings.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = get_option( self::OPTION_NAME, array() );

		return is_array( $settings ) ? $settings : array();
	}

	/**
	 * Get stored search forms with a default form entry.
	 *
	 * @return array
	 */
	public function get_forms() {
		$settings = $this->get_settings();
		$forms    = isset( $settings['search_forms'] ) && is_array( $settings['search_forms'] ) ? $settings['search_forms'] : array();

		if ( ! isset( $forms['default'] ) ) {
			$forms['default'] = array();
		}

		return $forms;
	}

	/**
	 * Get one stored search form.
	 *
	 * @param string $form_id Form id.
	 * @return array
	 */
	public function get_form( $form_id ) {
		$form_id = $this->normalize_form_id( $form_id );
		$forms   = $this->get_forms();

		return isset( $forms[ $form_id ] ) && is_array( $forms[ $form_id ] ) ? $forms[ $form_id ] : array();
	}

	/**
	 * Normalize a form id using legacy Search Forms behaviour.
	 *
	 * @param string $form_id Form id.
	 * @return string
	 */
	public function normalize_form_id( $form_id ) {
		$form_id = str_replace( '-', '_', sanitize_title( $form_id ) );

		return '' !== $form_id ? $form_id : 'default';
	}

	/**
	 * Get a stable hash for conflict detection.
	 *
	 * @param string $form_id Form id.
	 * @return string
	 */
	public function get_form_hash( $form_id ) {
		return sha1( wp_json_encode( $this->get_form( $form_id ) ) );
	}

	/**
	 * Apply stored form settings to live form fields.
	 *
	 * @param array  $fields Existing fields.
	 * @param string $form_id Form id.
	 * @param array|null $settings Optional full settings.
	 * @return array
	 */
	public function apply_form_settings_to_fields( $fields, $form_id, $settings = null ) {
		$settings = is_array( $settings ) ? $settings : $this->get_settings();
		$form_id  = $this->normalize_form_id( $form_id );
		$form     = isset( $settings['search_forms'][ $form_id ] ) && is_array( $settings['search_forms'][ $form_id ] ) ? $settings['search_forms'][ $form_id ] : array();

		$new_fields = isset( $form['active_fields'] ) && is_array( $form['active_fields'] ) && ! empty( $form['active_fields'] ) ? $form['active_fields'] : $fields;

		foreach ( $fields as $field_id => $field ) {
			$field_type = isset( $field['type'] ) ? $field['type'] : '';
			if ( ! isset( $new_fields[ $field_id ] ) && 'hidden' !== $field_type ) {
				unset( $fields[ $field_id ] );
			}

			if ( 'hidden' === $field_type && ! isset( $new_fields[ $field_id ] ) ) {
				$new_fields[ $field_id ] = $field;
			}
		}

		foreach ( $new_fields as $field_id => $new_field ) {
			$fields[ $field_id ] = array_merge( isset( $fields[ $field_id ] ) ? $fields[ $field_id ] : array(), is_array( $new_field ) ? $new_field : array() );
		}

		$ordered_fields = array();
		foreach ( $new_fields as $field_id => $new_field ) {
			if ( isset( $fields[ $field_id ] ) ) {
				$ordered_fields[ $field_id ] = $fields[ $field_id ];
			}
		}

		return $this->apply_custom_field_options( $ordered_fields, $settings );
	}

	/**
	 * Add custom-field dropdown options to rendered fields.
	 *
	 * @param array $fields Fields.
	 * @param array $settings Settings.
	 * @return array
	 */
	public function apply_custom_field_options( $fields, $settings = null ) {
		$settings      = is_array( $settings ) ? $settings : $this->get_settings();
		$custom_fields = isset( $settings['custom_fields'] ) && is_array( $settings['custom_fields'] ) ? $settings['custom_fields'] : array();

		foreach ( $fields as $field_id => $field ) {
			foreach ( $custom_fields as $custom_field ) {
				if (
					isset( $custom_field['field_name'], $custom_field['field_type'], $custom_field['dropdown_options'] ) &&
					$custom_field['field_name'] === $field_id &&
					in_array( $custom_field['field_type'], array( 'select', 'multiselect' ), true ) &&
					is_array( $custom_field['dropdown_options'] )
				) {
					$options = array( '' => isset( $field['blank_option'] ) ? $field['blank_option'] : '' );
					foreach ( $custom_field['dropdown_options'] as $dropdown_option ) {
						$options[ $dropdown_option ] = $dropdown_option;
					}
					$fields[ $field_id ]['options'] = $options;

					if ( 'multiselect' === $custom_field['field_type'] ) {
						$fields[ $field_id ]['type'] = 'select';
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * Build active and inactive field sets for editors.
	 *
	 * @param string $form_id Form id.
	 * @return array
	 */
	public function get_field_sets( $form_id ) {
		$form_id  = $this->normalize_form_id( $form_id );
		$settings = $this->get_settings();
		$form     = $this->get_form( $form_id );
		$catalog  = $this->catalog->get_fields( $settings );
		$active   = apply_filters( 'propertyhive_search_form_fields_' . $form_id, ph_get_search_form_fields() );

		foreach ( $catalog as $field_id => $field ) {
			if ( ! isset( $active[ $field_id ] ) && isset( $form['active_fields'][ $field_id ] ) ) {
				$active[ $field_id ] = array_merge( $field, $form['active_fields'][ $field_id ] );
			}
		}

		$inactive = array();
		foreach ( $catalog as $field_id => $field ) {
			if ( isset( $active[ $field_id ] ) ) {
				continue;
			}

			if ( isset( $form['inactive_fields'][ $field_id ] ) && is_array( $form['inactive_fields'][ $field_id ] ) ) {
				$field = array_merge( $field, $form['inactive_fields'][ $field_id ] );
			}

			$inactive[ $field_id ] = $field;
		}

		return array(
			'catalog'  => $catalog,
			'active'   => $active,
			'inactive' => $inactive,
		);
	}

	/**
	 * Get data for the visual editor.
	 *
	 * @param string $form_id Form id.
	 * @return array
	 */
	public function get_editor_data( $form_id = 'default' ) {
		$form_id = $this->normalize_form_id( $form_id );
		$sets    = $this->get_field_sets( $form_id );

		return array(
			'formId'     => $form_id,
			'baseHash'   => $this->get_form_hash( $form_id ),
			'active'     => $this->prepare_editor_fields( $sets['active'] ),
			'inactive'   => $this->prepare_editor_fields( $sets['inactive'] ),
			'categories' => $this->get_editor_categories(),
		);
	}

	/**
	 * Prepare fields for JavaScript.
	 *
	 * @param array $fields Fields.
	 * @return array
	 */
	private function prepare_editor_fields( $fields ) {
		$prepared = array();

		foreach ( $fields as $field_id => $field ) {
			if ( isset( $field['type'] ) && 'hidden' === $field['type'] ) {
				continue;
			}

			$prepared[] = $this->prepare_editor_field( $field_id, $field );
		}

		return $prepared;
	}

	/**
	 * Prepare one field for JavaScript.
	 *
	 * @param string $field_id Field id.
	 * @param array  $field Field data.
	 * @return array
	 */
	private function prepare_editor_field( $field_id, $field ) {
		$type = isset( $field['type'] ) ? sanitize_title( $field['type'] ) : '';

		return array(
			'id'       => $field_id,
			'title'    => $this->get_field_title( $field_id, $field ),
			'type'     => $type,
			'category' => $this->catalog->get_field_category( $field_id, $field ),
			'settings' => array(
				'type'                => $type,
				'show_label'          => ! empty( $field['show_label'] ),
				'label'               => isset( $field['label'] ) ? (string) $field['label'] : '',
				'placeholder'         => isset( $field['placeholder'] ) ? (string) $field['placeholder'] : '',
				'blank_option'        => isset( $field['blank_option'] ) ? (string) $field['blank_option'] : '',
				'min'                 => isset( $field['min'] ) ? (string) $field['min'] : '',
				'max'                 => isset( $field['max'] ) ? (string) $field['max'] : '',
				'step'                => isset( $field['step'] ) ? (string) $field['step'] : '',
				'multiselect'         => ! empty( $field['multiselect'] ),
				'parent_terms_only'   => ! empty( $field['parent_terms_only'] ),
				'hide_empty'          => ! empty( $field['hide_empty'] ),
				'dynamic_population'  => ! empty( $field['dynamic_population'] ),
			),
			'supports' => array(
				'placeholder'        => in_array( $type, array( 'text', 'email', 'date', 'number', 'password' ), true ),
				'blank_option'       => 'office' === $type || taxonomy_exists( $field_id ) || ( ! empty( $field['custom_field'] ) && 'select' === $type ),
				'department_type'    => 'department' === $field_id,
				'slider'             => 'slider' === $type,
				'taxonomy_settings'  => taxonomy_exists( $field_id ),
				'multiselect'        => 'office' === $field_id || taxonomy_exists( $field_id ) || ( ! empty( $field['custom_field'] ) && 'select' === $type ),
			),
		);
	}

	/**
	 * Get a field title.
	 *
	 * @param string $field_id Field id.
	 * @param array  $field Field data.
	 * @return string
	 */
	private function get_field_title( $field_id, $field ) {
		if ( isset( $field['label'] ) && '' !== trim( $field['label'] ) ) {
			return html_entity_decode( wp_strip_all_tags( $field['label'] ) );
		}

		return ucwords( str_replace( '_', ' ', trim( $field_id, '_' ) ) );
	}

	/**
	 * Editor category labels.
	 *
	 * @return array
	 */
	private function get_editor_categories() {
		return array(
			'core'        => __( 'Core', 'propertyhive' ),
			'residential' => __( 'Residential', 'propertyhive' ),
			'lettings'    => __( 'Lettings', 'propertyhive' ),
			'commercial'  => __( 'Commercial', 'propertyhive' ),
			'admin'       => __( 'Office / Admin', 'propertyhive' ),
			'custom'      => __( 'Custom / Add-ons', 'propertyhive' ),
		);
	}

	/**
	 * Save editor payload for one form.
	 *
	 * @param string $form_id Form id.
	 * @param array  $payload Payload.
	 * @param string $base_hash Base hash.
	 * @return array
	 */
	public function save_editor_payload( $form_id, $payload, $base_hash = '' ) {
		$form_id = $this->normalize_form_id( $form_id );

		if ( '' !== $base_hash && ! hash_equals( $this->get_form_hash( $form_id ), $base_hash ) ) {
			return new WP_Error( 'search_form_conflict', __( 'The search form changed while the editor was open. Reload and try again.', 'propertyhive' ) );
		}

		$entry = $this->sanitize_editor_payload( $form_id, $payload );
		if ( is_wp_error( $entry ) ) {
			return $entry;
		}

		$settings = $this->get_settings();
		$forms    = isset( $settings['search_forms'] ) && is_array( $settings['search_forms'] ) ? $settings['search_forms'] : array();
		if ( ! isset( $forms['default'] ) ) {
			$forms['default'] = array();
		}

		$this->store_revision( $settings, $form_id, 'save' );

		$forms[ $form_id ]          = $entry;
		$settings['search_forms']   = $forms;
		$updated                    = update_option( self::OPTION_NAME, $settings );
		$current_settings_after     = $this->get_settings();
		$current_form_after         = isset( $current_settings_after['search_forms'][ $form_id ] ) ? $current_settings_after['search_forms'][ $form_id ] : array();

		if ( false === $updated && $current_form_after !== $entry ) {
			return new WP_Error( 'search_form_save_failed', __( 'Could not save the search form.', 'propertyhive' ) );
		}

		return $this->get_editor_data( $form_id );
	}

	/**
	 * Reset a form to default behaviour.
	 *
	 * @param string $form_id Form id.
	 * @return true|WP_Error
	 */
	public function reset_form( $form_id ) {
		$form_id  = $this->normalize_form_id( $form_id );
		$settings = $this->get_settings();
		$forms    = isset( $settings['search_forms'] ) && is_array( $settings['search_forms'] ) ? $settings['search_forms'] : array();

		if ( ! isset( $forms[ $form_id ] ) ) {
			return new WP_Error( 'search_form_missing', __( 'Trying to reset a non-existent search form. Please go back and try again.', 'propertyhive' ) );
		}

		$this->store_revision( $settings, $form_id, 'reset' );

		$forms[ $form_id ]        = array();
		$settings['search_forms'] = $forms;

		update_option( self::OPTION_NAME, $settings );

		return true;
	}

	/**
	 * Delete a custom form.
	 *
	 * @param string $form_id Form id.
	 * @return true|WP_Error
	 */
	public function delete_form( $form_id ) {
		$form_id = $this->normalize_form_id( $form_id );

		if ( 'default' === $form_id ) {
			return new WP_Error( 'search_form_default_delete', __( 'The default search form cannot be deleted.', 'propertyhive' ) );
		}

		$settings = $this->get_settings();
		$forms    = isset( $settings['search_forms'] ) && is_array( $settings['search_forms'] ) ? $settings['search_forms'] : array();

		if ( ! isset( $forms[ $form_id ] ) ) {
			return new WP_Error( 'search_form_missing', __( 'Trying to delete a non-existent search form. Please go back and try again.', 'propertyhive' ) );
		}

		$this->store_revision( $settings, $form_id, 'delete' );

		unset( $forms[ $form_id ] );
		$settings['search_forms'] = $forms;

		update_option( self::OPTION_NAME, $settings );

		return true;
	}

	/**
	 * Sanitize an editor payload into the stored format.
	 *
	 * @param string $form_id Form id.
	 * @param array  $payload Payload.
	 * @return array|WP_Error
	 */
	public function sanitize_editor_payload( $form_id, $payload ) {
		$payload  = is_array( $payload ) ? $payload : array();
		$current  = $this->get_form( $form_id );
		$settings = $this->get_settings();
		$catalog  = $this->catalog->get_fields( $settings );
		$known    = array_merge(
			$catalog,
			isset( $current['active_fields'] ) && is_array( $current['active_fields'] ) ? $current['active_fields'] : array(),
			isset( $current['inactive_fields'] ) && is_array( $current['inactive_fields'] ) ? $current['inactive_fields'] : array()
		);
		$seen     = array();
		$entry    = array(
			'active_fields'   => array(),
			'inactive_fields' => array(),
		);

		foreach ( array( 'active_fields', 'inactive_fields' ) as $list_key ) {
			$list = isset( $payload[ $list_key ] ) && is_array( $payload[ $list_key ] ) ? $payload[ $list_key ] : array();

			foreach ( $list as $raw_field ) {
				if ( ! is_array( $raw_field ) || empty( $raw_field['id'] ) ) {
					continue;
				}

				$field_id = sanitize_key( $raw_field['id'] );
				if ( isset( $seen[ $field_id ] ) ) {
					return new WP_Error( 'search_form_duplicate_field', __( 'A search form field was included more than once.', 'propertyhive' ) );
				}

				if ( ! isset( $known[ $field_id ] ) ) {
					return new WP_Error( 'search_form_unknown_field', __( 'A search form field is no longer available.', 'propertyhive' ) );
				}

				$seen[ $field_id ] = true;
				$base              = $this->get_existing_or_catalog_field( $field_id, $current, $catalog );
				$settings_payload  = isset( $raw_field['settings'] ) && is_array( $raw_field['settings'] ) ? $raw_field['settings'] : array();
				$entry[ $list_key ][ $field_id ] = $this->sanitize_field_settings( $field_id, $base, $settings_payload );
			}
		}

		foreach ( array( 'active_fields', 'inactive_fields' ) as $list_key ) {
			if ( empty( $current[ $list_key ] ) || ! is_array( $current[ $list_key ] ) ) {
				continue;
			}

			foreach ( $current[ $list_key ] as $field_id => $field ) {
				if ( isset( $seen[ $field_id ] ) ) {
					continue;
				}

				$entry['inactive_fields'][ $field_id ] = is_array( $field ) ? $field : array();
			}
		}

		return $entry;
	}

	/**
	 * Get an existing field if present, otherwise the field catalog entry.
	 *
	 * @param string $field_id Field id.
	 * @param array  $current Current form.
	 * @param array  $catalog Catalog.
	 * @return array
	 */
	private function get_existing_or_catalog_field( $field_id, $current, $catalog ) {
		if ( isset( $current['active_fields'][ $field_id ] ) && is_array( $current['active_fields'][ $field_id ] ) ) {
			return $current['active_fields'][ $field_id ];
		}

		if ( isset( $current['inactive_fields'][ $field_id ] ) && is_array( $current['inactive_fields'][ $field_id ] ) ) {
			return $current['inactive_fields'][ $field_id ];
		}

		return isset( $catalog[ $field_id ] ) && is_array( $catalog[ $field_id ] ) ? $catalog[ $field_id ] : array();
	}

	/**
	 * Sanitize one field settings object while preserving unsupported existing data.
	 *
	 * @param string $field_id Field id.
	 * @param array  $base Existing/catalog field.
	 * @param array  $raw Raw settings.
	 * @return array
	 */
	private function sanitize_field_settings( $field_id, $base, $raw ) {
		$field = is_array( $base ) ? $base : array();
		$type  = isset( $field['type'] ) ? sanitize_title( $field['type'] ) : '';

		if ( 'department' === $field_id && isset( $raw['type'] ) && in_array( $raw['type'], array( 'radio', 'select' ), true ) ) {
			$field['type'] = sanitize_title( $raw['type'] );
		} elseif ( '' !== $type ) {
			$field['type'] = $type;
		}

		$field['show_label'] = ! empty( $raw['show_label'] );
		$field['label']      = isset( $raw['label'] ) ? sanitize_text_field( $raw['label'] ) : '';

		if ( isset( $raw['placeholder'] ) ) {
			$field['placeholder'] = sanitize_text_field( $raw['placeholder'] );
		}

		if ( isset( $raw['blank_option'] ) ) {
			$field['blank_option'] = sanitize_text_field( $raw['blank_option'] );
		}

		foreach ( array( 'min', 'max', 'step' ) as $number_key ) {
			if ( isset( $raw[ $number_key ] ) && '' !== $raw[ $number_key ] ) {
				$field[ $number_key ] = (string) floatval( $raw[ $number_key ] );
			}
		}

		foreach ( array( 'parent_terms_only', 'dynamic_population', 'hide_empty', 'multiselect' ) as $flag_key ) {
			if ( ! empty( $raw[ $flag_key ] ) ) {
				$field[ $flag_key ] = true;
			} else {
				unset( $field[ $flag_key ] );
			}
		}

		return $field;
	}

	/**
	 * Render a server-side preview for an unsaved payload.
	 *
	 * @param string $form_id Form id.
	 * @param array  $payload Payload.
	 * @return string|WP_Error
	 */
	public function render_preview_from_payload( $form_id, $payload ) {
		$form_id = $this->normalize_form_id( $form_id );
		$entry   = $this->sanitize_editor_payload( $form_id, $payload );

		if ( is_wp_error( $entry ) ) {
			return $entry;
		}

		$callback = function( $fields ) use ( $form_id, $entry ) {
			return $this->apply_entry_to_fields( $fields, $form_id, $entry );
		};

		add_filter( 'propertyhive_search_form_fields_' . $form_id, $callback, 1000, 1 );

		ob_start();
		ph_get_search_form( $form_id );
		$html = ob_get_clean();

		remove_filter( 'propertyhive_search_form_fields_' . $form_id, $callback, 1000 );

		return $html;
	}

	/**
	 * Apply a draft entry to fields.
	 *
	 * @param array $fields Fields.
	 * @param array $entry Entry.
	 * @return array
	 */
	private function apply_entry_to_fields( $fields, $form_id, $entry ) {
		$draft_settings = $this->get_settings();
		if ( ! isset( $draft_settings['search_forms'] ) || ! is_array( $draft_settings['search_forms'] ) ) {
			$draft_settings['search_forms'] = array();
		}

		$draft_settings['search_forms'][ $form_id ] = $entry;

		return $this->apply_form_settings_to_fields( $fields, $form_id, $draft_settings );
	}

	/**
	 * Store a capped rollback revision for form-changing actions.
	 *
	 * @param array  $settings Current settings.
	 * @param string $form_id Form id.
	 * @param string $operation Operation.
	 */
	private function store_revision( &$settings, $form_id, $operation ) {
		$revisions = isset( $settings['search_form_revisions'] ) && is_array( $settings['search_form_revisions'] ) ? $settings['search_form_revisions'] : array();
		$forms     = isset( $settings['search_forms'] ) && is_array( $settings['search_forms'] ) ? $settings['search_forms'] : array();

		array_unshift(
			$revisions,
			array(
				'form_id'   => $form_id,
				'operation' => sanitize_key( $operation ),
				'time'      => current_time( 'mysql' ),
				'user_id'   => get_current_user_id(),
				'form'      => isset( $forms[ $form_id ] ) ? $forms[ $form_id ] : array(),
			)
		);

		$settings['search_form_revisions'] = array_slice( $revisions, 0, self::REVISION_LIMIT );
	}
}
