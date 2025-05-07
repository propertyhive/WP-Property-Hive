<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

extract(shortcode_atts(array(
	"image_number" => "", 
	"image_size" => "", 
	"output_ratio" => "", 
), $atts));

global $property;

if ( !isset($property->id) ) {
	return;
}

$image_number = 1;
if ( isset($atts['image_number']) && $atts['image_number'] != '' && is_numeric($atts['image_number']) )
{
	$image_number = (int)$atts['image_number'];
}

if ( $output_ratio != '' )
{
	// output div with image as background
	$url = '';
	if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    {
    	$photos = $property->_photo_urls;
    	if ( isset($photos[$image_number-1]) )
    	{
    		$url = $photos[$image_number-1]['url'];
    	}
    }
    else
    {
		$gallery_attachment_ids = $property->get_gallery_attachment_ids();

		if ( isset($gallery_attachment_ids[$image_number-1]) )
		{
			$url = wp_get_attachment_image_src( $gallery_attachment_ids[$image_number-1], $atts['image_size'] );
			$url = $url[0];
		}
	}

	// convert ratio to percentage
    $numbers = explode(':', $output_ratio);
    $percent = ( ( (int)$numbers[1] / (int)$numbers[0] ) * 100 ) . '%';

    echo '<div style="background:url(' . esc_url($url) . ') no-repeat center center; background-size:cover; padding-bottom:' . esc_attr($percent) . '">';
}
else
{
	// output <img>
	if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
    {
    	$photos = $property->_photo_urls;
    	if ( isset($photos[$image_number-1]) )
    	{
    		echo '<img src="' . esc_url($photos[$image_number-1]['url']) . '" alt="">';
    	}
    }
    else
    {
		$gallery_attachment_ids = $property->get_gallery_attachment_ids();

		if ( isset($gallery_attachment_ids[$image_number-1]) )
		{
			echo wp_get_attachment_image( $gallery_attachment_ids[$image_number-1], $atts['image_size'] );
		}
	}
}

do_action( 'propertyhive_salient_widget_property_image_render_after', $atts, $property );