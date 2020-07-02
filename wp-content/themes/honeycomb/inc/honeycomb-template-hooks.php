<?php
/**
 * Honeycomb hooks
 *
 * @package honeycomb
 */

/**
 * General
 *
 * @see  honeycomb_above_header_widget_region()
 * @see  honeycomb_below_header_widget_region()
 * @see  honeycomb_get_sidebar()
 */
add_action( 'honeycomb_before_header', 	'honeycomb_above_header_widget_region', 10 );
add_action( 'honeycomb_before_content', 'honeycomb_below_header_widget_region', 10 );
add_action( 'honeycomb_before_content',	'honeycomb_page_banner', 				20 );
add_action( 'honeycomb_sidebar',        'honeycomb_get_sidebar',          		10 );

/**
 * Header
 *
 * @see  honeycomb_skip_links()
 * @see  honeycomb_site_branding()
 * @see  honeycomb_primary_navigation()
 */
add_action( 'honeycomb_header', 'honeycomb_skip_links',                       0 );
add_action( 'honeycomb_header', 'honeycomb_site_branding',                    20 );
add_action( 'honeycomb_header', 'honeycomb_primary_navigation',               30 );

/**
 * Footer
 *
 * @see  honeycomb_footer_widgets()
 * @see  honeycomb_credit()
 */
add_action( 'honeycomb_footer', 'honeycomb_footer_widgets', 10 );
add_action( 'honeycomb_footer', 'honeycomb_credit',         20 );

/**
 * Homepage
 *
 * @see  honeycomb_property_search_form()
 * @see  honeycomb_homepage_content()
 * @see  honeycomb_featured_properties()
 */
add_action( 'homepage', 'honeycomb_homepage_content',      	10 );
add_action( 'homepage', 'honeycomb_property_search_form',  	20 );
add_action( 'homepage', 'honeycomb_featured_properties',   	30 );

/**
 * Posts
 *
 * @see  honeycomb_post_header()
 * @see  honeycomb_post_meta()
 * @see  honeycomb_post_content()
 * @see  honeycomb_init_structured_data()
 * @see  honeycomb_paging_nav()
 * @see  honeycomb_single_post_header()
 * @see  honeycomb_post_nav()
 * @see  honeycomb_display_comments()
 */
add_action( 'honeycomb_loop_post',           'honeycomb_post_header',          10 );
add_action( 'honeycomb_loop_post',           'honeycomb_post_meta',            20 );
add_action( 'honeycomb_loop_post',           'honeycomb_post_content',         30 );
add_action( 'honeycomb_loop_post',           'honeycomb_init_structured_data', 40 );
add_action( 'honeycomb_loop_after',          'honeycomb_paging_nav',           10 );
add_action( 'honeycomb_single_post',         'honeycomb_post_header',          10 );
add_action( 'honeycomb_single_post',         'honeycomb_post_meta',            20 );
add_action( 'honeycomb_single_post',         'honeycomb_post_content',         30 );
add_action( 'honeycomb_single_post',         'honeycomb_init_structured_data', 40 );
add_action( 'honeycomb_single_post_bottom',  'honeycomb_post_nav',             10 );
add_action( 'honeycomb_single_post_bottom',  'honeycomb_display_comments',     20 );
add_action( 'honeycomb_post_content_before', 'honeycomb_post_thumbnail',       10 );

/**
 * Pages
 *
 * @see  honeycomb_page_header()
 * @see  honeycomb_page_content()
 * @see  honeycomb_init_structured_data()
 * @see  honeycomb_display_comments()
 */
add_action( 'honeycomb_page',       'honeycomb_page_header',          10 );
add_action( 'honeycomb_page',       'honeycomb_page_content',         20 );
add_action( 'honeycomb_page',       'honeycomb_init_structured_data', 30 );
add_action( 'honeycomb_page_after', 'honeycomb_display_comments',     10 );
