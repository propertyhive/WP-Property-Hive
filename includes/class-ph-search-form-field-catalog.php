<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Builds the editable Property Hive search form field catalogue.
 */
class PH_Search_Form_Field_Catalog {

	/**
	 * Get every field that can be offered by the search form builder.
	 *
	 * @param array|null $settings Template Assistant settings.
	 * @return array
	 */
	public function get_fields( $settings = null ) {
		$settings   = is_array( $settings ) ? $settings : get_option( 'propertyhive_template_assistant', array() );
		$all_fields = ph_get_search_form_fields();

		$all_fields['address_keyword'] = array(
			'type'       => 'text',
			'label'      => __( 'Location', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-address_keyword">',
		);

		if ( class_exists( 'PH_Radial_Search' ) ) {
			$all_fields['radius'] = array(
				'type'       => 'select',
				'label'      => __( 'Radius', 'propertyhive' ),
				'show_label' => true,
				'before'     => '<div class="control control-radius">',
				'options'    => array(
					''   => __( 'This Area Only', 'propertyhive' ),
					'1'  => __( 'Within 1 Mile', 'propertyhive' ),
					'2'  => __( 'Within 2 Miles', 'propertyhive' ),
					'3'  => __( 'Within 3 Miles', 'propertyhive' ),
					'5'  => __( 'Within 5 Miles', 'propertyhive' ),
					'10' => __( 'Within 10 Miles', 'propertyhive' ),
				),
			);
		}

		$all_fields['location'] = array(
			'type'       => 'location',
			'label'      => __( 'Location', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-location">',
		);
		$all_fields['parking'] = array(
			'type'       => 'parking',
			'label'      => __( 'Parking', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-parking residential-only">',
		);
		$all_fields['outside_space'] = array(
			'type'       => 'outside_space',
			'label'      => __( 'Outside Space', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-outside_space residential-only">',
		);
		$all_fields['availability'] = array(
			'type'       => 'availability',
			'label'      => __( 'Status', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-availability">',
		);
		$all_fields['marketing_flag'] = array(
			'type'       => 'marketing_flag',
			'label'      => __( 'Marketing Flag', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-marketing_flag">',
		);
		$all_fields['tenure'] = array(
			'type'       => 'tenure',
			'label'      => __( 'Tenure', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-tenure residential-only">',
		);
		$all_fields['commercial_tenure'] = array(
			'type'       => 'commercial_tenure',
			'label'      => __( 'Commercial Tenure', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-commercial_tenure commercial-only">',
		);
		$all_fields['commercial_for_sale_to_rent'] = array(
			'type'       => 'select',
			'label'      => __( 'For Sale / To Rent', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-commercial_for_sale_to_rent commercial-only">',
			'options'    => array(
				''         => __( 'No Preference', 'propertyhive' ),
				'for_sale' => __( 'For Sale', 'propertyhive' ),
				'to_rent'  => __( 'To Rent', 'propertyhive' ),
			),
		);

		$commercial_prices = array(
			''       => __( 'No preference', 'propertyhive' ),
			'100000' => '£100,000',
			'200000' => '£200,000',
			'300000' => '£300,000',
			'400000' => '£400,000',
			'500000' => '£500,000',
			'750000' => '£750,000',
		);
		$all_fields['commercial_minimum_price'] = array(
			'type'       => 'select',
			'label'      => __( 'Minimum Price', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-commercial_minimum_price commercial-sales-only">',
			'options'    => $commercial_prices,
		);
		$all_fields['commercial_maximum_price'] = array(
			'type'       => 'select',
			'label'      => __( 'Maximum Price', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-commercial_maximum_price commercial-sales-only">',
			'options'    => $commercial_prices,
		);

		$commercial_rents = array(
			''     => __( 'No preference', 'propertyhive' ),
			'500'  => '£500',
			'750'  => '£750',
			'1000' => '£1,000',
			'1500' => '£1,500',
			'2000' => '£2,000',
			'3000' => '£3,000',
		);
		$all_fields['commercial_minimum_rent'] = array(
			'type'       => 'select',
			'label'      => __( 'Minimum Rent', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-commercial_minimum_rent commercial-lettings-only">',
			'options'    => $commercial_rents,
		);
		$all_fields['commercial_maximum_rent'] = array(
			'type'       => 'select',
			'label'      => __( 'Maximum Rent', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-commercial_maximum_rent commercial-lettings-only">',
			'options'    => $commercial_rents,
		);

		$all_fields['sale_by'] = array(
			'type'       => 'sale_by',
			'label'      => __( 'Sale By', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-sale_by">',
		);
		$all_fields['furnished'] = array(
			'type'       => 'furnished',
			'label'      => __( 'Furnished', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-furnished lettings-only">',
		);

		$price_ranges = array(
			''                => __( 'No preference', 'propertyhive' ),
			'100000-200000'   => '£100,000 - £200,000',
			'200000-300000'   => '£200,000 - £300,000',
			'300000-400000'   => '£300,000 - £400,000',
			'400000-500000'   => '£400,000 - £500,000',
			'500000-750000'   => '£500,000 - £750,000',
			'750000-1000000'  => '£750,000 - £1,000,000',
		);
		$all_fields['price_range'] = array(
			'type'       => 'select',
			'label'      => __( 'Price', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-price-range sales-only">',
			'options'    => $price_ranges,
		);
		$all_fields['price_slider'] = array(
			'type'       => 'slider',
			'label'      => __( 'Price', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-price-slider sales-only">',
			'min'        => '0',
			'max'        => '1000000',
			'step'       => '10000',
		);

		$rent_ranges = array(
			''          => __( 'No preference', 'propertyhive' ),
			'100-200'   => '£100 - £200 PCM',
			'200-300'   => '£200 - £300 PCM',
			'300-400'   => '£300 - £400 PCM',
			'400-500'   => '£400 - £500 PCM',
			'500-750'   => '£500 - £750 PCM',
			'750-1000'  => '£750 - £1,000 PCM',
		);
		$all_fields['rent_range'] = array(
			'type'       => 'select',
			'label'      => __( 'Rent', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-rent-range lettings-only">',
			'options'    => $rent_ranges,
		);
		$all_fields['rent_slider'] = array(
			'type'       => 'slider',
			'label'      => __( 'Rent', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-rent-slider lettings-only">',
			'min'        => '0',
			'max'        => '1000',
			'step'       => '100',
		);

		$bedrooms = array(
			''  => __( 'No preference', 'propertyhive' ),
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5',
		);
		$all_fields['bedrooms'] = array(
			'type'       => 'select',
			'label'      => __( 'Bedrooms', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-bedrooms residential-only">',
			'options'    => $bedrooms,
		);
		$all_fields['maximum_bedrooms'] = array(
			'type'       => 'select',
			'label'      => __( 'Max Beds', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-maximum_bedrooms residential-only">',
			'options'    => $bedrooms,
		);

		$rooms = array(
			''  => __( 'No preference', 'propertyhive' ),
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5',
		);
		$all_fields['minimum_bathrooms'] = array(
			'type'       => 'select',
			'label'      => __( 'Min Bathrooms', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-minimum_bathrooms residential-only">',
			'options'    => $rooms,
		);
		$all_fields['maximum_bathrooms'] = array(
			'type'       => 'select',
			'label'      => __( 'Max Bathrooms', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-maximum_bathrooms residential-only">',
			'options'    => $rooms,
		);
		$all_fields['minimum_reception_rooms'] = array(
			'type'       => 'select',
			'label'      => __( 'Min Receptions', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-minimum_reception_rooms residential-only">',
			'options'    => $rooms,
		);
		$all_fields['maximum_reception_rooms'] = array(
			'type'       => 'select',
			'label'      => __( 'Max Receptions', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-maximum_reception_rooms residential-only">',
			'options'    => $rooms,
		);
		$all_fields['bedrooms_slider'] = array(
			'type'       => 'slider',
			'label'      => __( 'Bedrooms', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-bedrooms-slider residential-only">',
			'min'        => '0',
			'max'        => '10',
		);
		$all_fields['available_date_from'] = array(
			'type'       => 'date',
			'label'      => __( 'Available From', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-available_date_from lettings-only">',
		);
		$all_fields['office'] = array(
			'type'       => 'office',
			'label'      => __( 'Office', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-office">',
		);
		$all_fields['keyword'] = array(
			'type'       => 'text',
			'label'      => __( 'Keyword', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-keyword">',
		);

		if ( 'checkbox' === get_option( 'propertyhive_features_type' ) ) {
			$all_fields['property_feature'] = array(
				'type'        => 'property_feature',
				'label'       => __( 'Property Features', 'propertyhive' ),
				'show_label'  => true,
				'before'      => '<div class="control control-property_feature">',
				'multiselect' => true,
			);
		}

		$all_fields = apply_filters( 'propertyhive_search_form_all_fields', $all_fields );

		$all_fields['currency'] = array(
			'type'       => 'select',
			'label'      => __( 'Currency', 'propertyhive' ),
			'show_label' => true,
			'before'     => '<div class="control control-currency">',
			'options'    => array(
				''    => '',
				'GBP' => 'GBP',
				'EUR' => 'EUR',
				'USD' => 'USD',
			),
		);
		$all_fields['date_added'] = array(
			'type'       => 'select',
			'show_label' => true,
			'label'      => __( 'Date Added', 'propertyhive' ),
			'options'    => array(
				''   => __( 'No preference', 'propertyhive' ),
				'1'  => __( 'Last 24 Hours', 'propertyhive' ),
				'3'  => __( 'Last 3 Days', 'propertyhive' ),
				'7'  => __( 'Last 7 Days', 'propertyhive' ),
				'14' => __( 'Last 14 Days', 'propertyhive' ),
			),
		);

		return $this->add_custom_fields( $all_fields, $settings );
	}

	/**
	 * Attach custom fields from Template Assistant settings.
	 *
	 * @param array $fields Fields.
	 * @param array $settings Settings.
	 * @return array
	 */
	private function add_custom_fields( $fields, $settings ) {
		if ( empty( $settings['custom_fields'] ) || ! is_array( $settings['custom_fields'] ) ) {
			return $fields;
		}

		foreach ( $settings['custom_fields'] as $custom_field ) {
			if ( empty( $custom_field['field_name'] ) ) {
				continue;
			}

			$field_type = 'text';
			if ( isset( $custom_field['field_type'] ) ) {
				if ( in_array( $custom_field['field_type'], array( 'select', 'multiselect' ), true ) ) {
					$field_type = 'select';
				} elseif ( 'checkbox' === $custom_field['field_type'] ) {
					$field_type = 'checkbox';
				}
			}

			$field_name            = sanitize_title( str_replace( '_', '-', $custom_field['field_name'] ) );
			$field_name            = str_replace( '-', '_', $field_name );
			$fields[ $field_name ] = array(
				'type'         => $field_type,
				'label'        => isset( $custom_field['field_label'] ) ? $custom_field['field_label'] : $field_name,
				'show_label'   => true,
				'before'       => '<div class="control control-' . trim( $field_name, '_' ) . '">',
				'custom_field' => true,
			);
		}

		return $fields;
	}

	/**
	 * Get a broad category for editor grouping.
	 *
	 * @param string $id Field id.
	 * @param array  $field Field data.
	 * @return string
	 */
	public function get_field_category( $id, $field ) {
		if ( ! empty( $field['custom_field'] ) || in_array( $id, array( 'radius', 'property_feature' ), true ) ) {
			return 'custom';
		}

		if ( 0 === strpos( $id, 'commercial_' ) || in_array( $id, array( 'minimum_floor_area', 'maximum_floor_area', 'commercial_property_type' ), true ) ) {
			return 'commercial';
		}

		if ( in_array( $id, array( 'furnished', 'available_date_from', 'minimum_rent', 'maximum_rent', 'rent_range', 'rent_slider' ), true ) ) {
			return 'lettings';
		}

		if ( in_array( $id, array( 'parking', 'outside_space', 'tenure', 'sale_by', 'minimum_bathrooms', 'maximum_bathrooms', 'minimum_reception_rooms', 'maximum_reception_rooms', 'bedrooms', 'maximum_bedrooms', 'bedrooms_slider' ), true ) ) {
			return 'residential';
		}

		if ( in_array( $id, array( 'office', 'marketing_flag', 'date_added', 'currency', 'availability' ), true ) ) {
			return 'admin';
		}

		return 'core';
	}
}
