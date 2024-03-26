<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

extract(shortcode_atts(array(
	"form_id" => "default", 
	"default_department" => "", 
), $atts));

echo do_shortcode('[property_search_form id="' . ( ( isset($atts['form_id']) && !empty($atts['form_id']) ) ? $atts['form_id'] : 'default' ) . '"' . ( ( isset($atts['default_department']) && !empty($atts['default_department']) ) ? ' default_department="' . $atts['default_department'] . '"' : '' ) . ']');