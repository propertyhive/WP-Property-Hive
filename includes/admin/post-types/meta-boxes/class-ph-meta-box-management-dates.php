<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Management_Dates
 */
class PH_Meta_Box_Management_Dates {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {

		$property_id = $post->_property_id;
		$tenancy_id = $post->ID;

		$key_dates = get_posts(array (
			'post_type' => 'key_date',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => '_tenancy_id',
					'value' => $tenancy_id
				),
				array(
					'key' => '_property_id',
					'value' => $property_id
				),
			),
		));

		include PH()->plugin_path() . '/includes/admin/views/html-meta-box-table.php';
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

	    do_action( 'propertyhive_save_tenancy_key_dates', $post_id );
    }

}