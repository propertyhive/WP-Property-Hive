<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
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

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery/Visual Composer framework filter constant; the integration requires the framework hook name unchanged. WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ), $this->settings['base'], $atts );

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
$style = '';
if ( ! empty( $atts['font_container'] ) && isset($atts['font_container']) ) 
{
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
	$style = ph_extract_font_style_from_salient_font_container( $font_container );
}

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The style fragment is built by ph_extract_font_style_from_salient_font_container() using safecss_filter_attr() and esc_attr().
echo '<div class="' . esc_attr( $css_class ) . '" ' . $style . '>';

	if ( isset($atts['display']) && $atts['display'] == 'buttons' )
	{
		echo '<style type="text/css">';
		echo '.property_actions ul { list-style-type:none; margin:0; padding:0; }';
		echo '.property_actions ul li { display:inline-block; margin-right:5px; }';
		echo '.property_actions ul li a { display:block; padding:7px 17px; text-decoration:none; background:' . esc_attr($atts['button_background_color']) . '; color:' . esc_attr($atts['button_text_color']) . ' }';
		echo '</style>';
	}

	propertyhive_template_single_actions();

echo '</div>';
