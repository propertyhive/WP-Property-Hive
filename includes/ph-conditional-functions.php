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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Legacy public global helper is_propertyhive; the established callable name is part of the plugin/extension API and must remain stable.
function is_propertyhive() {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Existing public Property Hive extension hook is_propertyhive; changing the established name would detach installed callbacks.
	return apply_filters( 'is_propertyhive', ( is_search_results() || is_property() ) ? true : false );
}

if ( ! function_exists( 'is_search_results' ) ) {

    /**
     * is_search_results - Returns true when viewing the property archive (search results).
     *
     * @access public
     * @return bool
     */
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Legacy public global helper is_search_results; the established callable name is part of the plugin/extension API and must remain stable.
    function is_search_results() {
        return ( is_post_type_archive( 'property' ) || is_page( ph_get_page_id( 'search_results' ) ) ) ? true : false;
    }
}

if ( ! function_exists( 'is_property' ) ) {

	/**
	 * is_product - Returns true when viewing a single product.
	 *
	 * @access public
	 * @return bool
	 */
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Legacy public global helper is_property; the established callable name is part of the plugin/extension API and must remain stable.
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
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Legacy public global helper is_ajax; the established callable name is part of the plugin/extension API and must remain stable.
	function is_ajax() {
		return defined( 'DOING_AJAX' );
	}
}