<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Rank Math SEO Compatibility
 *
 * @class 		PH_Yoast_SEO
 * @version		1.0.0
 * @package		PropertyHive/Classes/
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Rank_Math {

	/** @var PH_Rest_Api The single instance of the class */
	protected static $_instance = null;

	/**
	 * Hook in methods
	 */
	public static function init() {
		add_filter( 'rank_math/excluded_taxonomies', array( __CLASS__, 'sitemap_exclude_taxonomy' ), 10, 2 );
		add_filter( 'manage_edit-property_columns', array( __CLASS__, 'remove_columns') );
		add_filter( 'rank_math/sitemap/posts_to_exclude', array( __CLASS__, 'sitemap_exclude_off_market') );
	}

	public static function sitemap_exclude_taxonomy( $taxonomies ) {
		$taxonomy_objects = get_object_taxonomies( array('property', 'key_date'), 'objects' );

		if ( !empty($taxonomy_objects) )
		{
			foreach ( $taxonomy_objects as $taxonomy_name => $object )
			{
				if ( isset($taxonomies[$taxonomy_name]) ) 
				{
					unset($taxonomies[$taxonomy_name]);
				}
			}
		}
		return $taxonomies;
	}

	public static function remove_columns( $columns ) {
		unset( $columns['rank_math_seo_details'] );
		unset( $columns['rank_math_title'] );
		unset( $columns['rank_math_description'] );
		return $columns;
	}

	public static function sitemap_exclude_off_market( $post_ids_to_exclude ) {
        $properties_to_exclude = get_posts(array(
			'fields'            => 'ids',
			'posts_per_page'    => -1,
			'post_type'         => 'property',
			'meta_query'        => array(
				array(
					'key'   => '_on_market',
					'value' => '',
				)
			)
		));
		return array_merge($post_ids_to_exclude, $properties_to_exclude);
	}

}

PH_Rank_Math::init();