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

if ( $property->bathrooms == '' || $property->bathrooms == '0' ) {
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

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
	$label = __( 'Brochure', 'propertyhive' );

	if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
    	$brochure_urls = $property->brochure_urls;
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
        if ( !is_array($brochure_urls) ) { $brochure_urls = array(); }

        if ( !empty($brochure_urls) )
		{
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
			foreach ( $brochure_urls as $brochure )
			{
				echo '<a href="' . esc_url($brochure['url']) . '" target="_blank" rel="nofollow">' . esc_html($label) . '</a>';
			}
		}
    }
    else
    {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
		$brochure_attachment_ids = $property->get_brochure_attachment_ids();

		if ( !empty($brochure_attachment_ids) )
		{
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
			foreach ( $brochure_attachment_ids as $attachment_id )
			{
				echo '<a href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" target="_blank" rel="nofollow">' . esc_html($label) . '</a>';
			}
		}
	}

echo '</div>';
