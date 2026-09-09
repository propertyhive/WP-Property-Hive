<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

extract(shortcode_atts(array(
	'height' => '400',
), $atts));

global $property;

if ( !isset($property->id) ) {
	return;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
$attributes = array();
if ( isset($atts['height']) && $atts['height'] != '' )
{
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
	$attributes['height'] = $atts['height'];
}

get_property_street_view($attributes);