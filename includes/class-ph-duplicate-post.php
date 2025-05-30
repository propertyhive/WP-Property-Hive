<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Duplicate Post Plugin Compatibility
 *
 * @class 		PH_Duplicate_Post
 * @version		1.0.0
 * @package		PropertyHive/Classes/
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Duplicate_Post {

	/** @var PH_Duplicate_Post The single instance of the class */
	protected static $_instance = null;

	/**
	 * Hook in methods
	 */
	public static function init() 
	{
		add_action( 'duplicate_post_post_copy', array( __CLASS__, 'sort_out_concat_fields' ), 10 );
	}

	public static function sort_out_concat_fields( $new_post_id ) 
	{
		delete_post_meta( $new_post_id, '_address_concatenated' );
		delete_post_meta( $new_post_id, '_features_concatenated' );
		delete_post_meta( $new_post_id, '_descriptions_concatenated' );
		delete_post_meta( $new_post_id, '_owner_details' );

		$post = get_post( $new_post_id );
		do_action( "save_post", $new_post_id, $post, false );
	}
}

PH_Duplicate_Post::init();