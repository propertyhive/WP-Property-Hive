<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
$css = '';

extract(shortcode_atts(array(
	'map_link_type' => '_blank',
	"css" => "", 
	"font_container" => "", 
), $atts));

global $property;

if ( !isset($property->id) ) {
	return;
}

if ( $property->latitude == '' || $property->longitude == '' || $property->latitude == '0' || $property->longitude == '0' )
{
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
	$link_type = ( isset($atts['map_link_type']) && !empty($atts['map_link_type']) ) ? $atts['map_link_type'] : '_blank';

	switch ($link_type)
	{
		case "_blank":
		{
			echo '<a href="https://www.google.com/maps/?q=' . (float)$property->latitude . ',' . (float)$property->longitude . '&ll=' . (float)$property->latitude . ',' . (float)$property->longitude . '" target="_blank">' . esc_html(__( 'View Map', 'propertyhive' )) . '</a>';
			break;
		}
		case "embedded":
		{
			echo '<a href="#map_lightbox" data-fancybox>' . esc_html__( 'View Map', 'propertyhive' ) . '</a>';
	
			echo '<div id="map_lightbox" style="display:none; width:90%; max-width:800px;">';
	   	 		echo do_shortcode('[property_map]');
	    	echo '</div>';
			break;
		}
		case "iframe":
		{
			echo '<a 
			    href="#" 
			    data-fancybox 
			    data-type="iframe" 
			    data-src="https://maps.google.com/?output=embed&amp;f=q&amp;q=' . (float)$property->latitude . ',' . (float)$property->longitude . '&amp;ll=' . (float)$property->latitude . ',' . (float)$property->longitude . '&amp;layer=t&amp;hq=&amp;t=m&amp;z=15"
			>' . esc_html(__( 'View Map', 'propertyhive' )) . '</a>';
			break;
		}
	}

echo '</div>';
