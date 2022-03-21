<?php
/**
 * Application Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Application_Details
 */
class PH_Meta_Box_Application_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        $thepostid = $post->ID;

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

		$start_date = get_post_meta( $thepostid, '_start_date', TRUE );
		$end_date = get_post_meta( $thepostid, '_end_date', TRUE );
		if ( $start_date || $end_date )
		{
			echo '<p class="form-field">
        
            <label for="">' . __( 'Status', 'propertyhive' ) . '</label>';

			// if ( $start_date && strtotime( $start_date ) > time() )
			// {
			// 	echo __( 'Pending', 'propertyhive' );
			// }
			// elseif ( 
            //     $start_date && strtotime( $start_date ) <= time() && 
            //     ( time() <= strtotime( $end_date ) || $end_date == '' )
            // )
			// {
			// 	echo __( 'Current', 'propertyhive' );
			// }
			// elseif ( $end_date && strtotime( $end_date ) < time() )
			// {
			// 	echo __( 'Finished', 'propertyhive' );
			// }

			echo '</p>';
		}

        do_action('propertyhive_application_details_fields');
        
        echo '</div>';
        
        echo '</div>';
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        $status = get_post_meta( $post_id, '_status', TRUE );
        if ( $status == '' )
        {
            update_post_meta( $post_id, '_status', 'application' );
        }

        do_action( 'propertyhive_save_application_details', $post_id );
    }

}
