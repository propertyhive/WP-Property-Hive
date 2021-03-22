<?php
/**
 * Viewing Event Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Viewing_Event
 */
class PH_Meta_Box_Viewing_Event {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid, $pagenow;
        
        include( PH()->plugin_path() . '/includes/admin/views/html-viewing-event-meta-box.php' );
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        add_post_meta( $post_id, '_status', 'pending', TRUE );
        add_post_meta( $post_id, '_feedback_status', '', TRUE );
        add_post_meta( $post_id, '_feedback', '', TRUE );
        add_post_meta( $post_id, '_feedback_passed_on', '', TRUE );

        if ( isset($_POST['_original_viewing_id']) && $_POST['_original_viewing_id'] != '' )
        {
            add_post_meta( $post_id, '_original_viewing_id', (int)$_POST['_original_viewing_id'], TRUE );
        }

        $hours = str_pad((int)$_POST['_start_time_hours'], 2, '0', STR_PAD_LEFT);
        $minutes = str_pad((int)$_POST['_start_time_minutes'], 2, '0', STR_PAD_LEFT);
        update_post_meta( $post_id, '_start_date_time', ph_clean($_POST['_start_date']) . ' ' . $hours . ':' . $minutes . ':00' );
        update_post_meta( $post_id, '_duration', (int)$_POST['_duration'] );

        $reset_negs = true;
        if ( 
            isset($_POST['_previous_negotiator_ids']) && 
            isset($_POST['_negotiator_ids']) && 
            !empty($_POST['_negotiator_ids']) &&
            is_array($_POST['_negotiator_ids']) &&
            implode(",", $_POST['_negotiator_ids']) == $_POST['_previous_negotiator_ids'] 
        )
        {
            $reset_negs = false;
        }

        if ( $reset_negs )
        {
            delete_post_meta($post_id, '_negotiator_id');
            if ( !empty($_POST['_negotiator_ids']) )
            {
                foreach ( $_POST['_negotiator_ids'] as $negotiator_id )
                {
                    add_post_meta( $post_id, '_negotiator_id', (int)$negotiator_id );
                }
            }
        }

        update_post_meta( $post_id, '_booking_notes', sanitize_textarea_field($_POST['_booking_notes']) );

        $all_confirmed = '';
        if ( isset($_POST['_confirmed']) )
        {
            update_post_meta( $post_id, '_confirmed', $_POST['_confirmed'] );

            if ( count($_POST['_confirmed']) == $_POST['_num_requiring_confirmation'] )
            {
                $all_confirmed = 'yes';
            }
        }
        else
        {
            update_post_meta( $post_id, '_confirmed', '' );
        }
        update_post_meta( $post_id, '_all_confirmed', $all_confirmed );
        
        do_action( 'propertyhive_save_viewing_event', $post_id );   
    }

}
