<?php
/**
 * Viewing Actions
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Viewing_Actions
 */
class PH_Meta_Box_Viewing_Actions {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        $status = get_post_meta( $post->ID, '_status', TRUE );

        echo '<div id="propertyhive_viewing_actions_meta_box_container">

        	Loading...';

        echo '</div>';
    }
}