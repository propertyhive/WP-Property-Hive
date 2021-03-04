<?php
/**
 * Viewing Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Viewing_Details
 */
class PH_Meta_Box_Viewing_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        $viewing = new PH_Viewing($post->ID);
        
        echo '<div id="propertyhive_viewing_details_meta_box_container">Loading...</div>'; 
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
    
        if ( isset($_POST['_cancelled_reason']) ) { update_post_meta( $post_id, '_cancelled_reason', sanitize_textarea_field($_POST['_cancelled_reason']) ); }
        if ( isset($_POST['_feedback']) ) { update_post_meta( $post_id, '_feedback', sanitize_textarea_field($_POST['_feedback']) ); }

        do_action( 'propertyhive_save_viewing_details', $post_id );
    }

}
