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

if ( $property->bathrooms == '' || $property->bathrooms == '0' ) {
	return;
}

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ), $this->settings['base'], $atts );

$style = '';
if ( ! empty( $atts['font_container'] ) && isset($atts['font_container']) ) 
{
	$style = ph_extract_font_style_from_salient_font_container( $font_container );
}

echo '<div class="' . esc_attr( $css_class ) . '" ' . $style . '>';

	$label = __( 'Brochure', 'propertyhive' );

	if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
    {
    	$brochure_urls = $property->brochure_urls;
        if ( !is_array($brochure_urls) ) { $brochure_urls = array(); }

        if ( !empty($brochure_urls) )
		{
			foreach ( $brochure_urls as $brochure )
			{
				echo '<a href="' . esc_url($brochure['url']) . '" target="_blank" rel="nofollow">' . esc_html($label) . '</a>';
			}
		}
    }
    else
    {
		$brochure_attachment_ids = $property->get_brochure_attachment_ids();

		if ( !empty($brochure_attachment_ids) )
		{
			foreach ( $brochure_attachment_ids as $attachment_id )
			{
				echo '<a href="' . esc_url(wp_get_attachment_url($attachment_id)) . '" target="_blank" rel="nofollow">' . esc_html($label) . '</a>';
			}
		}
	}

echo '</div>';