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

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
$attributes = array();
if ( isset($atts['height']) && $atts['height'] != '' )
{
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
	$attributes['height'] = $atts['height'];
}
if ( isset($atts['zoom']) && isset($atts['zoom']['size']) && $atts['zoom']['size'] != '' )
{
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
	$attributes['zoom'] = $atts['zoom']['size'];
}
if ( isset($atts['scrollwheel']) && $atts['scrollwheel'] != '' )
{
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WPBakery html_template local variable; this file is a framework-rendered view receiving $atts/$this, and PrefixAllGlobals sees it outside the framework render scope.
	$attributes['scrollwheel'] = $atts['scrollwheel'];
}

get_property_map($attributes);