<?php
/**
 * PropertyHive Page Functions
 *
 * Functions related to pages and menus.
 *
 * @author 		BIOSTALL
 * @category 	Core
 * @package 	PropertyHive/Functions
 * @version     1.0.0
 */

/**
 * Retrieve page ids - used for search results, property. returns -1 if no page is found
 *
 * @param string $page
 * @return int
 */
function ph_get_page_id( $page ) {

	$page = apply_filters( 'propertyhive_get_' . $page . '_page_id', get_option('propertyhive_' . $page . '_page_id' ) );

	return $page ? $page : -1;
}
