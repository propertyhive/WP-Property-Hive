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

		$post_id = $post->ID;

		echo '<div id="propertyhive_management_dates_container">';
			include PH()->plugin_path() . '/includes/admin/views/html-meta-box-table.php';
		echo '</div>';
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

	    do_action( 'propertyhive_save_tenancy_key_dates', $post_id );
    }

}