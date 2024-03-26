<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$css = '';

extract(shortcode_atts(array(
	"display" => "", 
	"button_background_color" => "#000",
	"button_text_color" => "#FFF",
	"css" => "", 
	"font_container" => "", 
), $atts));

global $property;

if ( !isset($property->id) ) {
	return;
}

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ), $this->settings['base'], $atts );

$style = '';
if ( ! empty( $atts['font_container'] ) && isset($atts['font_container']) ) 
{
	$style = ph_extract_font_style_from_salient_font_container( $font_container );
}

echo '<div class="' . esc_attr( $css_class ) . '" ' . $style . '>';

	if ( isset($atts['display']) && $atts['display'] == 'buttons' )
	{
		echo '<style type="text/css">';
		echo '.property_actions ul { list-style-type:none; margin:0; padding:0; }';
		echo '.property_actions ul li { display:inline-block; margin-right:5px; }';
		echo '.property_actions ul li a { display:block; padding:7px 17px; text-decoration:none; background:' . $atts['button_background_color'] . '; color:' . $atts['button_text_color'] . ' }';
		echo '</style>';
	}

	propertyhive_template_single_actions();

echo '</div>';