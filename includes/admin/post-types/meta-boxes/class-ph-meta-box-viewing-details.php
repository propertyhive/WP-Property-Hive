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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Viewing_Details; preserving the existing PH_* class name is required for plugin and extension compatibility.
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
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['post_ID'] ) || ! is_scalar( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== (int) $post_id ) {
            return;
        }

        global $wpdb;
        if ( 'viewing' !== get_post_type( $post_id ) ) { return; }

        if ( isset($_POST['_cancelled_reason']) && is_string( $_POST['_cancelled_reason'] ) ) { update_post_meta( $post_id, '_cancelled_reason', wp_slash( sanitize_textarea_field( wp_unslash( $_POST['_cancelled_reason'] ) ) ) ); }
        if ( isset($_POST['_cancelled_reason_public']) && $_POST['_cancelled_reason_public'] == 'yes' ) { update_post_meta( $post_id, '_cancelled_reason_public', 'yes' ); }else{ update_post_meta( $post_id, '_cancelled_reason_public', 'no' ); }
        if ( isset($_POST['_feedback']) && is_string( $_POST['_feedback'] ) ) { update_post_meta( $post_id, '_feedback', wp_slash( sanitize_textarea_field( wp_unslash( $_POST['_feedback'] ) ) ) ); }

        do_action( 'propertyhive_save_viewing_details', $post_id );
    }

}
