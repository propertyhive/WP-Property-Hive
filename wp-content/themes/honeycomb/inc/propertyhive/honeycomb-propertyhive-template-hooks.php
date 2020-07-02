<?php
/**
 * Honeycomb Property Hive hooks
 *
 * @package honeycomb
 */

/**
 * Styles
 *
 * @see  honeycomb_propertyhive_scripts()
 */

/**
 * Layout
 */
remove_action( 'propertyhive_before_main_content', 'propertyhive_output_content_wrapper',     10 );
remove_action( 'propertyhive_after_main_content',  'propertyhive_output_content_wrapper_end', 10 );
add_action( 'propertyhive_before_main_content',    'honeycomb_before_content',              10 );
add_action( 'propertyhive_after_main_content',     'honeycomb_after_content',               10 );

/** 
 * Property Details
 */
remove_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_title',     		5 );
remove_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_floor_area',     	7 );
remove_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_price',     		10 );
add_action( 'propertyhive_before_single_property_summary', 'propertyhive_template_single_title',     	5 );
add_action( 'propertyhive_before_single_property_summary', 'propertyhive_template_single_floor_area',   7 );
add_action( 'propertyhive_before_single_property_summary', 'propertyhive_template_single_price',     	9 );
add_action( 'propertyhive_after_single_property_summary', 'honeycomb_propertyhive_template_single_map', 50 );

/**
 * Footer
 */

add_action( 'honeycomb_footer',                  	'honeycomb_handheld_footer_bar',         999 );
