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

		$new_fields = isset( $form['active_fields'] ) && is_array( $form['active_fields'] ) ? $form['active_fields'] : $fields;

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
			$field = $this->apply_display_contexts_to_field( $field );

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
					$field['options'] = $options;

					if ( 'multiselect' === $custom_field['field_type'] ) {
						$field['type'] = 'select';
					}
				}
			}

			$fields[ $field_id ] = $field;
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
			'formId'            => $form_id,
			'baseHash'          => $this->get_form_hash( $form_id ),
			'active'            => $this->prepare_editor_fields( $sets['active'] ),
			'inactive'          => $this->prepare_editor_fields( $sets['inactive'] ),
			'categories'        => $this->get_editor_categories(),
			'visibilityContexts' => $this->get_display_context_options(),
			'visibilityChoices'  => $this->get_selectable_display_context_choices( $sets['active'] ),
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
		$type             = isset( $field['type'] ) ? sanitize_title( $field['type'] ) : '';
		$display_contexts = $this->get_field_display_contexts( $field );

		return array(
			'id'         => $field_id,
			'title'      => $this->get_field_title( $field_id, $field ),
			'type'       => $type,
			'category'   => $this->catalog->get_field_category( $field_id, $field ),
			'visibility' => $this->get_field_visibility( $field ),
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
				'display_contexts'    => $display_contexts,
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
	 * Get editor visibility hints from the front-end control classes.
	 *
	 * @param array $field Field data.
	 * @return array
	 */
	private function get_field_visibility( $field ) {
		$contexts       = $this->get_field_display_contexts( $field );
		$all_contexts   = $this->get_all_display_contexts();
		$is_all_contexts = empty( array_diff( $all_contexts, $contexts ) ) && empty( array_diff( $contexts, $all_contexts ) );
		$visibility     = array(
			'scope'    => $is_all_contexts ? 'all' : 'custom',
			'label'    => $this->get_display_contexts_label( $contexts ),
			'contexts' => array_values( $contexts ),
		);

		if ( $is_all_contexts ) {
			return $visibility;
		}

		if ( in_array( 'residential_sales', $contexts, true ) ) {
			$visibility['preview_department'] = 'residential-sales';
			return $visibility;
		}

		if ( in_array( 'residential_lettings', $contexts, true ) ) {
			$visibility['preview_department'] = 'residential-lettings';
			return $visibility;
		}

		if ( in_array( 'commercial_sales', $contexts, true ) ) {
			$visibility['preview_department']      = 'commercial';
			$visibility['commercial_availability'] = 'for_sale';
			return $visibility;
		}

		if ( in_array( 'commercial_lettings', $contexts, true ) ) {
			$visibility['preview_department']      = 'commercial';
			$visibility['commercial_availability'] = 'to_rent';
		}

		return $visibility;
	}

	/**
	 * Get the selectable display contexts for field visibility controls.
	 *
	 * @return array
	 */
	private function get_display_context_options() {
		return array(
			'residential_sales'    => __( 'Residential sales', 'propertyhive' ),
			'residential_lettings' => __( 'Residential lettings', 'propertyhive' ),
			'commercial_sales'     => __( 'Commercial sale', 'propertyhive' ),
			'commercial_lettings'  => __( 'Commercial rent', 'propertyhive' ),
		);
	}

	/**
	 * Get the display context choices that can be reached in the current form.
	 *
	 * @param array $fields Active form fields.
	 * @return array
	 */
	private function get_selectable_display_context_choices( $fields ) {
		$choices          = array();
		$base_departments = $this->get_reachable_base_departments_from_fields( $fields );
		$labels           = $this->get_display_context_options();

		if ( in_array( 'residential-sales', $base_departments, true ) ) {
			$choices[] = array(
				'id'       => 'residential_sales',
				'label'    => $labels['residential_sales'],
				'contexts' => array( 'residential_sales' ),
			);
		}

		if ( in_array( 'residential-lettings', $base_departments, true ) ) {
			$choices[] = array(
				'id'       => 'residential_lettings',
				'label'    => $labels['residential_lettings'],
				'contexts' => array( 'residential_lettings' ),
			);
		}

		if ( in_array( 'commercial', $base_departments, true ) ) {
			$commercial_availability_values = $this->get_commercial_availability_values_from_fields( $fields );

			if ( in_array( 'for_sale', $commercial_availability_values, true ) && in_array( 'to_rent', $commercial_availability_values, true ) ) {
				$choices[] = array(
					'id'       => 'commercial_sales',
					'label'    => $labels['commercial_sales'],
					'contexts' => array( 'commercial_sales' ),
				);
				$choices[] = array(
					'id'       => 'commercial_lettings',
					'label'    => $labels['commercial_lettings'],
					'contexts' => array( 'commercial_lettings' ),
				);
			} elseif ( in_array( 'for_sale', $commercial_availability_values, true ) ) {
				$choices[] = array(
					'id'       => 'commercial_sales',
					'label'    => $labels['commercial_sales'],
					'contexts' => array( 'commercial_sales' ),
				);
			} elseif ( in_array( 'to_rent', $commercial_availability_values, true ) ) {
				$choices[] = array(
					'id'       => 'commercial_lettings',
					'label'    => $labels['commercial_lettings'],
					'contexts' => array( 'commercial_lettings' ),
				);
			} else {
				$choices[] = array(
					'id'       => 'commercial',
					'label'    => __( 'Commercial', 'propertyhive' ),
					'contexts' => array( 'commercial_sales', 'commercial_lettings' ),
				);
			}
		}

		if ( ! empty( $choices ) ) {
			return $choices;
		}

		foreach ( $labels as $context => $label ) {
			$choices[] = array(
				'id'       => $context,
				'label'    => $label,
				'contexts' => array( $context ),
			);
		}

		return $choices;
	}

	/**
	 * Get the base departments that can be selected by the current form.
	 *
	 * @param array $fields Active form fields.
	 * @return array
	 */
	private function get_reachable_base_departments_from_fields( $fields ) {
		$departments = array();
		$field       = isset( $fields['department'] ) && is_array( $fields['department'] ) ? $fields['department'] : array();

		if ( ! empty( $field ) && isset( $field['type'] ) && 'hidden' === $field['type'] ) {
			if ( ! empty( $field['value'] ) ) {
				$departments[] = $field['value'];
			}
		} elseif ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
			$departments = array_keys( $field['options'] );
		} elseif ( ! empty( $field['value'] ) ) {
			$departments[] = $field['value'];
		}

		if ( empty( $departments ) ) {
			$default_fields = ph_get_search_form_fields();
			$default_field  = isset( $default_fields['department'] ) && is_array( $default_fields['department'] ) ? $default_fields['department'] : array();

			if ( ! empty( $default_field['value'] ) ) {
				$departments[] = $default_field['value'];
			} elseif ( ! empty( $default_field['options'] ) && is_array( $default_field['options'] ) ) {
				$departments = array_keys( $default_field['options'] );
			}
		}

		$base_departments = array();

		foreach ( $departments as $department ) {
			$base_department = ph_get_custom_department_based_on( $department );
			$base_department = $base_department ? $base_department : $department;

			if ( in_array( $base_department, array( 'residential-sales', 'residential-lettings', 'commercial' ), true ) && ! in_array( $base_department, $base_departments, true ) ) {
				$base_departments[] = $base_department;
			}
		}

		return $base_departments;
	}

	/**
	 * Get reachable values for the commercial sale/rent selector.
	 *
	 * @param array $fields Active form fields.
	 * @return array
	 */
	private function get_commercial_availability_values_from_fields( $fields ) {
		$field = isset( $fields['commercial_for_sale_to_rent'] ) && is_array( $fields['commercial_for_sale_to_rent'] ) ? $fields['commercial_for_sale_to_rent'] : array();

		if ( empty( $field ) ) {
			return array();
		}

		if ( isset( $field['type'] ) && 'hidden' === $field['type'] ) {
			return ! empty( $field['value'] ) && in_array( $field['value'], array( 'for_sale', 'to_rent' ), true ) ? array( $field['value'] ) : array();
		}

		if ( empty( $field['options'] ) || ! is_array( $field['options'] ) ) {
			return array();
		}

		$values = array();

		foreach ( array_keys( $field['options'] ) as $value ) {
			if ( in_array( $value, array( 'for_sale', 'to_rent' ), true ) ) {
				$values[] = $value;
			}
		}

		return $values;
	}

	/**
	 * Get display context keys.
	 *
	 * @return array
	 */
	private function get_all_display_contexts() {
		return array_keys( $this->get_display_context_options() );
	}

	/**
	 * Get a field's display contexts from saved settings or legacy classes.
	 *
	 * @param array $field Field data.
	 * @return array
	 */
	private function get_field_display_contexts( $field ) {
		if ( isset( $field['display_contexts'] ) && is_array( $field['display_contexts'] ) ) {
			return $this->sanitize_display_contexts( $field['display_contexts'] );
		}

		$before = isset( $field['before'] ) ? (string) $field['before'] : '';

		if ( false !== strpos( $before, 'commercial-sales-only' ) ) {
			return array( 'commercial_sales' );
		}

		if ( false !== strpos( $before, 'commercial-lettings-only' ) ) {
			return array( 'commercial_lettings' );
		}

		if ( false !== strpos( $before, 'commercial-only' ) ) {
			return array( 'commercial_sales', 'commercial_lettings' );
		}

		if ( false !== strpos( $before, 'lettings-only' ) ) {
			return array( 'residential_lettings' );
		}

		if ( false !== strpos( $before, 'sales-only' ) ) {
			return array( 'residential_sales' );
		}

		if ( false !== strpos( $before, 'residential-only' ) ) {
			return array( 'residential_sales', 'residential_lettings' );
		}

		return $this->get_all_display_contexts();
	}

	/**
	 * Sanitize display contexts while ensuring a field remains visible somewhere.
	 *
	 * @param array $contexts Context keys.
	 * @return array
	 */
	private function sanitize_display_contexts( $contexts ) {
		$allowed = $this->get_all_display_contexts();
		$clean   = array();

		foreach ( is_array( $contexts ) ? $contexts : array() as $context ) {
			$context = sanitize_key( $context );

			if ( in_array( $context, $allowed, true ) && ! in_array( $context, $clean, true ) ) {
				$clean[] = $context;
			}
		}

		return ! empty( $clean ) ? $clean : $allowed;
	}

	/**
	 * Get compact label text for a set of display contexts.
	 *
	 * @param array $contexts Context keys.
	 * @return string
	 */
	private function get_display_contexts_label( $contexts ) {
		$contexts     = $this->sanitize_display_contexts( $contexts );
		$all_contexts = $this->get_all_display_contexts();

		if ( empty( array_diff( $all_contexts, $contexts ) ) && empty( array_diff( $contexts, $all_contexts ) ) ) {
			return '';
		}

		if ( empty( array_diff( array( 'residential_sales', 'residential_lettings' ), $contexts ) ) && empty( array_diff( $contexts, array( 'residential_sales', 'residential_lettings' ) ) ) ) {
			return __( 'Residential', 'propertyhive' );
		}

		if ( empty( array_diff( array( 'commercial_sales', 'commercial_lettings' ), $contexts ) ) && empty( array_diff( $contexts, array( 'commercial_sales', 'commercial_lettings' ) ) ) ) {
			return __( 'Commercial', 'propertyhive' );
		}

		$options = $this->get_display_context_options();
		$labels  = array();

		foreach ( $contexts as $context ) {
			if ( isset( $options[ $context ] ) ) {
				$labels[] = $options[ $context ];
			}
		}

		return implode( ', ', $labels );
	}

	/**
	 * Apply stored display contexts to the field wrapper classes.
	 *
	 * @param array $field Field data.
	 * @return array
	 */
	private function apply_display_contexts_to_field( $field ) {
		$field['display_contexts'] = $this->get_field_display_contexts( $field );

		if ( empty( $field['before'] ) ) {
			return $field;
		}

		$field['before'] = $this->replace_visibility_classes(
			(string) $field['before'],
			$this->get_visibility_classes_for_contexts( $field['display_contexts'] )
		);

		return $field;
	}

	/**
	 * Translate display contexts to existing front-end visibility classes.
	 *
	 * @param array $contexts Context keys.
	 * @return array
	 */
	private function get_visibility_classes_for_contexts( $contexts ) {
		$contexts     = $this->sanitize_display_contexts( $contexts );
		$all_contexts = $this->get_all_display_contexts();

		if ( empty( array_diff( $all_contexts, $contexts ) ) && empty( array_diff( $contexts, $all_contexts ) ) ) {
			return array();
		}

		$classes = array();

		if ( in_array( 'residential_sales', $contexts, true ) && in_array( 'residential_lettings', $contexts, true ) ) {
			$classes[] = 'residential-only';
		} else {
			if ( in_array( 'residential_sales', $contexts, true ) ) {
				$classes[] = 'sales-only';
			}

			if ( in_array( 'residential_lettings', $contexts, true ) ) {
				$classes[] = 'lettings-only';
			}
		}

		if ( in_array( 'commercial_sales', $contexts, true ) && in_array( 'commercial_lettings', $contexts, true ) ) {
			$classes[] = 'commercial-only';
		} else {
			if ( in_array( 'commercial_sales', $contexts, true ) ) {
				$classes[] = 'commercial-sales-only';
			}

			if ( in_array( 'commercial_lettings', $contexts, true ) ) {
				$classes[] = 'commercial-lettings-only';
			}
		}

		return $classes;
	}

	/**
	 * Replace legacy visibility classes in a field wrapper.
	 *
	 * @param string $before Wrapper HTML.
	 * @param array  $visibility_classes New visibility classes.
	 * @return string
	 */
	private function replace_visibility_classes( $before, $visibility_classes ) {
		$known_classes = array(
			'sales-only',
			'lettings-only',
			'residential-only',
			'commercial-only',
			'commercial-sales-only',
			'commercial-lettings-only',
		);

		return preg_replace_callback(
			'/class=(["\'])(.*?)\1/i',
			function( $matches ) use ( $known_classes, $visibility_classes ) {
				$classes = preg_split( '/\s+/', trim( $matches[2] ) );
				$classes = is_array( $classes ) ? $classes : array();
				$classes = array_values( array_diff( $classes, $known_classes ) );

				foreach ( $visibility_classes as $visibility_class ) {
					if ( ! in_array( $visibility_class, $classes, true ) ) {
						$classes[] = $visibility_class;
					}
				}

				return 'class=' . $matches[1] . esc_attr( implode( ' ', $classes ) ) . $matches[1];
			},
			$before,
			1
		);
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

		$field['display_contexts'] = $this->sanitize_display_contexts(
			isset( $raw['display_contexts'] ) && is_array( $raw['display_contexts'] ) ? $raw['display_contexts'] : $this->get_field_display_contexts( $field )
		);

		return $this->apply_display_contexts_to_field( $field );
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
