<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ), $this->settings['base'], $atts );

$style = '';
if ( ! empty( $atts['font_container'] ) && isset($atts['font_container']) ) 
{
	$style = ph_extract_font_style_from_salient_font_container( $font_container );
}

echo '<div class="' . esc_attr( $css_class ) . '" ' . $style . '>';

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
			echo '<a href="#map_lightbox" data-fancybox>' . __( 'View Map', 'propertyhive' ) . '</a>';
	
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