<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
$css = '';

extract(shortcode_atts(array(
	"css" => "", 
	"font_container" => "", 
), $atts));

global $property;

if ( !isset($property->id) ) {
	return;
}

if ( $property->address_name_number != '' )
{
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

		echo esc_html($property->address_name_number);

	echo '</div>';
}
