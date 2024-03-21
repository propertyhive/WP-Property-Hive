<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$css = '';

extract(shortcode_atts(array(
	"css" => "", 
	"font_container" => "", 
), $atts));

global $property;

if ( !isset($property->id) ) {
	return;
}

if ( $property->department == 'commercial' && ( ( $property->floor_area_to_sqft != '' && $property->floor_area_to_sqft != 0 ) || ( $property->floor_area_from_sqft != '' && $property->floor_area_from_sqft != 0 ) ) )
{
	$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ), $this->settings['base'], $atts );

	$style = '';
	if ( ! empty( $atts['font_container'] ) && isset($atts['font_container']) ) 
	{
		$style = ph_extract_font_style_from_salient_font_container( $font_container );
	}

	echo '<div class="' . esc_attr( $css_class ) . '" ' . $style . '>';

		echo $property->get_formatted_floor_area();

	echo '</div>';
}