<?php
/**
 * PropertyHive Template Hooks
 *
 * Action/filter hooks used for PropertyHive functions/templates
 *
 * @author 		PropertyHive
 * @category 	Core
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter( 'body_class', 'ph_body_class' );
add_filter( 'post_class', 'ph_property_post_class', 20, 3 );

/** 
 * PH Header
 *
 * @see  ph_products_rss_feed()
 * @see  ph_generator_tag()
 */
add_action( 'wp_head', 'ph_properties_rss_feed' );
add_action( 'get_the_generator_html', 'ph_generator_tag', 10, 2 );
add_action( 'get_the_generator_xhtml', 'ph_generator_tag', 10, 2 );

/**
 * Content Wrappers
 *
 * @see propertyhive_output_content_wrapper()
 * @see propertyhive_output_content_wrapper_end()
 */
add_action( 'propertyhive_before_main_content', 'propertyhive_output_content_wrapper', 10 );
add_action( 'propertyhive_after_main_content', 'propertyhive_output_content_wrapper_end', 10 );

/**
 * Properties Loop
 *
 * @see propertyhive_result_count()
 * @see propertyhive_catalog_ordering()
 */
add_action( 'propertyhive_before_search_results_loop', 'propertyhive_search_form', 10 );
add_action( 'propertyhive_before_search_results_loop', 'propertyhive_result_count', 20 );
add_action( 'propertyhive_before_search_results_loop', 'propertyhive_catalog_ordering', 30 );


/**
 * Pagination after search loops
 *
 * @see propertyhive_pagination()
 */
add_action( 'propertyhive_after_search_results_loop', 'propertyhive_pagination', 10 );

/**
 * Property Loop Items
 *
 * @see propertyhive_template_loop_property_thumbnail()
 * @see propertyhive_template_loop_floor_area()
 * @see propertyhive_template_loop_price()
 * @see propertyhive_template_loop_summary()
 * @see propertyhive_template_loop_actions()
 */
add_action( 'propertyhive_before_search_results_loop_item_title', 'propertyhive_template_loop_property_thumbnail', 10 );

add_action( 'propertyhive_after_search_results_loop_item_title', 'propertyhive_template_loop_floor_area', 5 ); // commercial only
add_action( 'propertyhive_after_search_results_loop_item_title', 'propertyhive_template_loop_price', 10 );
add_action( 'propertyhive_after_search_results_loop_item_title', 'propertyhive_template_loop_summary', 20 );
add_action( 'propertyhive_after_search_results_loop_item_title', 'propertyhive_template_loop_actions', 30 );

/**
 * Featured Property Loop Items
 *
 * @see propertyhive_template_loop_property_thumbnail()
 * @see propertyhive_template_loop_price()
 * @see propertyhive_template_loop_actions()
 */
add_action( 'propertyhive_before_featured_loop_item_title', 'propertyhive_template_loop_property_thumbnail', 10 );

add_action( 'propertyhive_after_featured_loop_item_title', 'propertyhive_template_loop_price', 10 );
add_action( 'propertyhive_after_featured_loop_item_title', 'propertyhive_template_loop_actions', 30 );

/**
 * Recent Property Loop Items
 *
 * @see propertyhive_template_loop_property_thumbnail()
 * @see propertyhive_template_loop_price()
 * @see propertyhive_template_loop_actions()
 */
add_action( 'propertyhive_before_recent_loop_item_title', 'propertyhive_template_loop_property_thumbnail', 10, 2 );

add_action( 'propertyhive_after_recent_loop_item_title', 'propertyhive_template_loop_price', 10 );
add_action( 'propertyhive_after_recent_loop_item_title', 'propertyhive_template_loop_actions', 20 );

/**
 * Before Single Property Summary Div
 *
 * @see propertyhive_template_not_on_market()
 * @see propertyhive_show_property_images()
 * @see propertyhive_show_property_thumbnails()
 */
add_action( 'propertyhive_before_single_property_summary', 'propertyhive_template_not_on_market', 5 );
add_action( 'propertyhive_before_single_property_summary', 'propertyhive_show_property_images', 10 );
add_action( 'propertyhive_product_thumbnails', 'propertyhive_show_property_thumbnails', 20 );

/**
 * Property Summary Box
 *
 * @see propertyhive_template_single_title()
 * @see propertyhive_template_single_floor_area()
 * @see propertyhive_template_single_price()
 * @see propertyhive_template_single_meta()
 * @see propertyhive_template_single_sharing()
 */
add_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_title', 5 );
add_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_floor_area', 7 ); // commercial only
add_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_price', 10 );
add_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_meta', 20 );
add_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_sharing', 30 );

/**
 * After Single Property Summary Div
 *
 * @see propertyhive_template_single_actions()
 * @see propertyhive_template_single_features()
 * @see propertyhive_template_single_summary()
 * @see propertyhive_template_single_description()
 */
add_action( 'propertyhive_after_single_property_summary', 'propertyhive_template_single_actions', 10 );
add_action( 'propertyhive_after_single_property_summary', 'propertyhive_template_single_features', 20 );
add_action( 'propertyhive_after_single_property_summary', 'propertyhive_template_single_summary', 30 );
add_action( 'propertyhive_after_single_property_summary', 'propertyhive_template_single_description', 40 );

/**
 * Property Actions Box
 *
 * @see propertyhive_make_enquiry_button()
 */
add_action( 'propertyhive_property_actions_list_start', 'propertyhive_make_enquiry_button', 10 );

/**
 * My Account
 *
 * @see propertyhive_my_account_navigation()
 * @see propertyhive_my_account_sections()
 */
add_action( 'propertyhive_my_account_content', 'propertyhive_my_account_navigation', 10 );
add_action( 'propertyhive_my_account_content', 'propertyhive_my_account_sections', 20 );

add_action( 'propertyhive_my_account_section_dashboard', 'propertyhive_my_account_dashboard', 10 );
add_action( 'propertyhive_my_account_section_details', 'propertyhive_my_account_details', 10 );
add_action( 'propertyhive_my_account_section_requirements', 'propertyhive_my_account_requirements', 10 );
add_action( 'propertyhive_my_account_section_applicant_viewings', 'propertyhive_my_account_applicant_viewings', 10 );
add_action( 'propertyhive_my_account_section_owner_properties', 'propertyhive_my_account_owner_properties', 10 );
add_action( 'propertyhive_my_account_section_owner_viewings', 'propertyhive_my_account_owner_viewings', 10 );
add_action( 'propertyhive_my_account_section_delete', 'propertyhive_my_account_delete', 10 );

/**
 * Footer
 *
 * @see  ph_print_js()
 */
//add_action( 'wp_footer', 'ph_print_js', 25 );
