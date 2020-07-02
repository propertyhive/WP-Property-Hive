<?php
/**
 * PropertyHive Page Functions
 *
 * Functions related to pages and menus.
 *
 * @author 		PropertyHive
 * @category 	Core
 * @package 	PropertyHive/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

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

/**
 * Fix active class in nav for search results page.
 *
 * @param array $menu_items
 * @return array
 */
function ph_nav_menu_item_classes( $menu_items ) {
	if ( ! is_propertyhive() ) {
		return $menu_items;
	}
	$search_results_page = (int) ph_get_page_id( 'search_results' );
	$page_for_posts = (int) get_option( 'page_for_posts' );

	foreach ( (array) $menu_items as $key => $menu_item ) {
		$classes = (array) $menu_item->classes;
		// Unset active class for blog page
		if ( $page_for_posts == $menu_item->object_id ) {
			$menu_items[ $key ]->current = false;
			if ( in_array( 'current_page_parent', $classes ) ) {
				unset( $classes[ array_search( 'current_page_parent', $classes ) ] );
			}
			if ( in_array( 'current-menu-item', $classes ) ) {
				unset( $classes[ array_search( 'current-menu-item', $classes ) ] );
			}
		// Set active state if this is the search results page link
		} elseif ( is_search_results() && $search_results_page == $menu_item->object_id && 'page' === $menu_item->object ) {
			$menu_items[ $key ]->current = true;
			$classes[] = 'current-menu-item';
			$classes[] = 'current_page_item';
		// Set parent state if this is a property page
		} elseif ( is_singular( 'property' ) && $search_results_page == $menu_item->object_id ) {
			$classes[] = 'current_page_parent';
		}
		$menu_items[ $key ]->classes = array_unique( $classes );
	}
	return $menu_items;
}
add_filter( 'wp_nav_menu_objects', 'ph_nav_menu_item_classes', 2 );

/**
 * Fix active class in wp_list_pages for search results page.
 *
 * @param string $pages
 * @return string
 */
function ph_list_pages( $pages ) {
	if ( is_propertyhive() ) {
		// Remove current_page_parent class from any item.
		$pages = str_replace( 'current_page_parent', '', $pages );
		// Find search_results_page_id through Property Hive options.
		$search_results_page = 'page-item-' . ph_get_page_id( 'search_results' );
		if ( is_search_results() ) {
			// Add current_page_item class to search results page.
			$pages = str_replace( $search_results_page, $search_results_page . ' current_page_item', $pages );
		} else {
			// Add current_page_parent class to search results page.
			$pages = str_replace( $search_results_page, $search_results_page . ' current_page_parent', $pages );
		}
	}
	return $pages;
}
add_filter( 'wp_list_pages', 'ph_list_pages' );