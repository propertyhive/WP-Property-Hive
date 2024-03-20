<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class PH_Salient {

	public function __construct()
	{
		add_action( 'init', array( $this, 'register_widgets' ), 1 );
	}

	public function register_widgets()
	{
		if ( !class_exists('Salient_Core') )
		{
			return;
		}

		$widgets = array(
			'Property Search Form',
			'Property Images',
			/*'Property Image',
			'Property Gallery',
			'Property Address Name Number',
			'Property Address Street',
			'Property Address Line 2',
			'Property Address Town City',*/
			'Property Address County',
			/*'Property Address Postcode',
			'Property Address Full',
			'Property Price',
			'Property Price Qualifier',
			'Property Features',
			'Property Summary Description',
			'Property Full Description',
			'Property Actions',
			'Property Meta',
			'Property Availability',
			'Property Type',
			'Property Bedrooms',
			'Property Bathrooms',
			'Property Reception Rooms',
			'Property Reference Number',
			'Property Floor Area',
			'Property Tenure',
			'Property Council Tax Band',
			'Property Let Available Date',
			'Property Deposit',
			'Property Map',
			'Property Map Link',
			'Property Street View',
			'Property Floorplans',
			'Property Floorplans Link',
			'Property EPCs',
			'Property EPCs Link',
			'Property Enquiry Form',
			'Property Enquiry Form Link',
			'Property Brochures Link',
			'Property Embedded Virtual Tours',
			'Property Virtual Tours Link',*/
			/*'Property Office Name',
			'Property Office Telephone Number',
			'Property Office Email Address',
			'Property Office Address',
			'Property Negotiator Name',
			'Property Negotiator Telephone Number',
			'Property Negotiator Email Address',
			'Property Negotiator Photo',
			'Back To Search',
			'Property Search Result Count',
			'Property Search Order',*/
		);

		$widgets = apply_filters( 'propertyhive_salient_widgets', $widgets );

		foreach ( $widgets as $widget )
		{
			$widget_dir = 'salient-widgets';
			$widget_dir = apply_filters( 'propertyhive_salient_widget_directory', dirname(__FILE__) . "/" . $widget_dir, $widget );
			if ( file_exists( $widget_dir . "/" . sanitize_title($widget) . ".php" ) )
			{
				require_once( $widget_dir . "/" . sanitize_title($widget) . ".php" );
			}
		}
	}
}

new PH_Salient();

function ph_extract_font_style_from_salient_font_container( $font_container = '' )
{
	$style = '';

	if ( empty($font_container) || !function_exists('vc_parse_multi_attribute') )
	{
		return $style;
	}

	$font_container = vc_parse_multi_attribute($font_container);

	if ( !empty($font_container) )
	{
		foreach ( $font_container as $key => $value ) {
			if ( 'tag' !== $key && strlen( $value ) ) {
				if ( preg_match( '/description/', $key ) ) {
					continue;
				}
				if ( 'font_size' === $key || 'line_height' === $key ) {
					$value = preg_replace( '/\s+/', '', $value );
				}
				if ( 'font_size' === $key ) {
					$pattern = '/^(\d*(?:\.\d+)?)\s*(px|\%|in|cm|mm|em|rem|ex|pt|pc|vw|vh|vmin|vmax)?$/';
					// allowed metrics: http://www.w3schools.com/cssref/css_units.asp
					preg_match( $pattern, $value, $matches );
					$value = isset( $matches[1] ) ? (float) $matches[1] : (float) $value;
					$unit = isset( $matches[2] ) ? $matches[2] : 'px';
					$value = $value . $unit;
				}
				if ( strlen( $value ) > 0 ) {
					$styles[] = str_replace( '_', '-', $key ) . ': ' . $value;
				}
			}
		}
	}

	if ( ! empty( $styles ) ) {
		$style = 'style="' . esc_attr( implode( ';', $styles ) ) . '"';
	} else {
		$style = '';
	}

	return $style;
}