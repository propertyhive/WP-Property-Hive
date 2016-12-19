<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Serve properties using the REST API
 *
 * @class 		PH_Rest_Api
 * @version		1.0.0
 * @package		PropertyHive/Classes/
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Yoast_API {

	/** @var PH_Rest_Api The single instance of the class */
	protected static $_instance = null;

	/**
	 * Hook in methods
	 */
	public static function init() {
		add_action( 'wpseo_opengraph', array( __CLASS__, 'wpseo_opengraph_image' ) );
		add_filter( 'wpseo_sitemap_exclude_taxonomy', array( __CLASS__, 'sitemap_exclude_taxonomy' ), 10, 2 );
		add_filter( 'wpseo_metabox_prio', array( __CLASS__, 'yoast_meta_box_to_bottom') );
		add_filter( 'manage_edit-property_columns', array( __CLASS__, 'yoast_remove_columns') );
	}

	public static function wpseo_opengraph_image() {
		global $post;

		if ( is_singular('property') )
		{
			if ( !has_post_thumbnail($post->ID) )
			{
				// If no image set already, use the main photo for the property
				$property = new PH_Property($post->ID);

				$image_src = $property->get_main_photo_src( 'full' );
				if ( $image_src != '' )
				{
					$GLOBALS['wpseo_og']->image_output( $image_src );
				}
			}
		}
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

}

PH_Yoast_API::init();