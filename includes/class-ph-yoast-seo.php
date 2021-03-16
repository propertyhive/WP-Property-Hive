<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Yoast SEO Compatibility
 *
 * @class 		PH_Yoast_SEO
 * @version		1.0.0
 * @package		PropertyHive/Classes/
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Yoast_SEO {

	/** @var PH_Rest_Api The single instance of the class */
	protected static $_instance = null;

	/**
	 * Hook in methods
	 */
	public static function init() {
		add_filter( 'wpseo_sitemap_exclude_taxonomy', array( __CLASS__, 'sitemap_exclude_taxonomy' ), 10, 2 );
		add_filter( 'wpseo_metabox_prio', array( __CLASS__, 'yoast_meta_box_to_bottom') );
		add_filter( 'manage_edit-property_columns', array( __CLASS__, 'yoast_remove_columns') );
		add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', array( __CLASS__, 'sitemap_exclude_off_market') );
	}

	public static function sitemap_exclude_taxonomy( $value, $taxonomy ) {
		$taxonomy_objects = get_object_taxonomies( 'property', 'objects' );
   		$taxonomies = array();

   		if ( !empty($taxonomy_objects) )
   		{
   			foreach ( $taxonomy_objects as $taxonomy_name => $object )
   			{
   				$taxonomies[] = $taxonomy_name;
   			}
   		}
		if ( 
			in_array($taxonomy, $taxonomies)
		) 
		{
			return true;
		}
	}

	public static function yoast_meta_box_to_bottom() {
		return 'low';
	}

	public static function yoast_remove_columns( $columns ) {
		unset( $columns['wpseo-score'] );
		unset( $columns['wpseo-score-readability'] );
		unset( $columns['wpseo-title'] );
		unset( $columns['wpseo-metadesc'] );
		unset( $columns['wpseo-focuskw'] );
		return $columns;
	}

	public static function sitemap_exclude_off_market() {
        return get_posts(array(
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
	}

}

PH_Yoast_SEO::init();