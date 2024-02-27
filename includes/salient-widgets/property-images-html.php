<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( is_admin() )
{
	return;
}

extract(shortcode_atts(array(
	"hide_thumbnails" => "", 
), $atts));

if ( isset($atts['hide_thumbnails']) && $atts['hide_thumbnails'] == 'yes' )
{
	remove_action( 'propertyhive_product_thumbnails', 'propertyhive_show_property_thumbnails', 20 );
}

propertyhive_show_property_images();