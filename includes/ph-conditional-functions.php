<?php
/**
 * PropertyHive Conditional Functions
 *
 * Functions for determining the current query/page.
 *
 * @author 		PropertyHive
 * @category 	Core
 * @package 	PropertyHive/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * is_propertyhive - Returns true if on a page which uses PropertyHive templates
 *
 * @access public
 * @return bool
 */
function is_propertyhive() {
	return apply_filters( 'is_propertyhive', ( is_search_results() || is_property() ) ? true : false );
}

if ( ! function_exists( 'is_search_results' ) ) {

    /**
     * is_search_results - Returns true when viewing the property archive (search results).
     *
     * @access public
     * @return bool
     */
    function is_search_results() {
        return ( is_post_type_archive( 'property' ) || is_page( ph_get_page_id( 'property' ) ) ) ? true : false;
    }
}

if ( ! function_exists( 'is_property' ) ) {

	/**
	 * is_product - Returns true when viewing a single product.
	 *
	 * @access public
	 * @return bool
	 */
	function is_property() {
		return is_singular( array( 'property' ) );
	}
}

if ( ! function_exists( 'is_ajax' ) ) {

	/**
	 * is_ajax - Returns true when the page is loaded via ajax.
	 *
	 * @access public
	 * @return bool
	 */
	function is_ajax() {
		return defined( 'DOING_AJAX' );
	}
}