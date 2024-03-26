<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

extract(shortcode_atts(array(
	'height' => '400',
	'zoom' => '14',
	'scrollwheel' => 'true',
), $atts));

global $property;

if ( !isset($property->id) ) {
	return;
}

$attributes = array();
if ( isset($atts['height']) && $atts['height'] != '' )
{
	$attributes['height'] = $atts['height'];
}
if ( isset($atts['zoom']) && isset($atts['zoom']['size']) && $atts['zoom']['size'] != '' )
{
	$attributes['zoom'] = $atts['zoom']['size'];
}
if ( isset($atts['scrollwheel']) && $atts['scrollwheel'] != '' )
{
	$attributes['scrollwheel'] = $atts['scrollwheel'];
}

get_property_map($attributes);