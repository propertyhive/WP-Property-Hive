<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

extract(shortcode_atts(array(
	'height' => '400',
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

get_property_street_view($attributes);