<?php
/**
 * Tenancy Key Dates
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Key_Dates
 */
class PH_Meta_Box_Key_Dates {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        echo '<div class="propertyhive_meta_box">';

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
