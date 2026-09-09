<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Viewing_Event; preserving the existing PH_* class name is required for plugin and extension compatibility.
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
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['post_ID'] ) || ! is_scalar( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== (int) $post_id ) {
            return;
        }

        $request_post = wp_unslash( $_POST );

        global $wpdb;

        add_post_meta( $post_id, '_status', 'pending', TRUE );
        add_post_meta( $post_id, '_feedback_status', '', TRUE );
        add_post_meta( $post_id, '_feedback', '', TRUE );
        add_post_meta( $post_id, '_feedback_passed_on', '', TRUE );

        $original_viewing_id = ( isset( $request_post['_original_viewing_id'] ) && is_scalar( $request_post['_original_viewing_id'] ) ) ? absint( $request_post['_original_viewing_id'] ) : 0;
        if ( $original_viewing_id > 0 )
        {
            add_post_meta( $post_id, '_original_viewing_id', $original_viewing_id, TRUE );
        }

        $hours_valid = isset( $request_post['_start_time_hours'] ) && is_scalar( $request_post['_start_time_hours'] );
        $minutes_valid = isset( $request_post['_start_time_minutes'] ) && is_scalar( $request_post['_start_time_minutes'] );
        $start_date_valid = isset( $request_post['_start_date'] ) && is_string( $request_post['_start_date'] );
        if ( $hours_valid && $minutes_valid && $start_date_valid )
        {
            $hours = min( 23, absint( $request_post['_start_time_hours'] ) );
            $minutes = min( 59, absint( $request_post['_start_time_minutes'] ) );
            $start_date = sanitize_text_field( $request_post['_start_date'] );
            update_post_meta( $post_id, '_start_date_time', wp_slash( $start_date . ' ' . str_pad( $hours, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $minutes, 2, '0', STR_PAD_LEFT ) . ':00' ) );
        }

        if ( isset( $request_post['_duration'] ) && is_scalar( $request_post['_duration'] ) )
        {
            update_post_meta( $post_id, '_duration', absint( $request_post['_duration'] ) );
        }

        $previous_negotiator_ids = ( isset( $request_post['_previous_negotiator_ids'] ) && is_scalar( $request_post['_previous_negotiator_ids'] ) ) ? sanitize_text_field( $request_post['_previous_negotiator_ids'] ) : '';
        $negotiator_ids = array();
        $negotiator_ids_valid = true;
        if ( array_key_exists( '_negotiator_ids', $request_post ) && ! is_array( $request_post['_negotiator_ids'] ) )
        {
            $negotiator_ids_valid = false;
        }
        elseif ( array_key_exists( '_negotiator_ids', $request_post ) )
        {
            foreach ( $request_post['_negotiator_ids'] as $negotiator_id )
            {
                if ( ! is_scalar( $negotiator_id ) )
                {
                    $negotiator_ids_valid = false;
                    break;
                }
                if ( absint( $negotiator_id ) > 0 )
                {
                    $negotiator_ids[] = absint( $negotiator_id );
                }
            }
            $negotiator_ids = array_values( array_unique( $negotiator_ids ) );
        }
        $negotiator_ids_csv = implode( ',', $negotiator_ids );
        $reset_negs = true;
        if ( ! $negotiator_ids_valid )
        {
            // Ignore malformed submissions and preserve existing negotiator relationships.
            $reset_negs = false;
        }
        elseif ( ! empty( $negotiator_ids ) && '' !== $previous_negotiator_ids && $negotiator_ids_csv === $previous_negotiator_ids )
        {
            $reset_negs = false;
        }

        if ( $reset_negs )
        {
            delete_post_meta($post_id, '_negotiator_id');
            if ( ! empty( $negotiator_ids ) )
            {
                foreach ( $negotiator_ids as $negotiator_id )
                {
                    add_post_meta( $post_id, '_negotiator_id', $negotiator_id );
                }
            }
        }

        if ( ! array_key_exists( '_booking_notes', $request_post ) || is_string( $request_post['_booking_notes'] ) )
        {
            $booking_notes = isset( $request_post['_booking_notes'] ) ? sanitize_textarea_field( $request_post['_booking_notes'] ) : '';
            update_post_meta( $post_id, '_booking_notes', wp_slash( $booking_notes ) );
        }

        $all_confirmed = '';
        $confirmed = array();
        $confirmed_valid = true;
        if ( array_key_exists( '_confirmed', $request_post ) && ! is_array( $request_post['_confirmed'] ) )
        {
            $confirmed_valid = false;
        }
        elseif ( array_key_exists( '_confirmed', $request_post ) )
        {
            foreach ( $request_post['_confirmed'] as $confirmed_value )
            {
                if ( ! is_scalar( $confirmed_value ) )
                {
                    $confirmed_valid = false;
                    break;
                }
                $confirmed[] = sanitize_text_field( (string) $confirmed_value );
            }
        }

        if ( $confirmed_valid && array_key_exists( '_confirmed', $request_post ) )
        {
            update_post_meta( $post_id, '_confirmed', $confirmed );

            if ( isset( $request_post['_num_requiring_confirmation'] ) && is_scalar( $request_post['_num_requiring_confirmation'] ) && count( $confirmed ) === absint( $request_post['_num_requiring_confirmation'] ) )
            {
                $all_confirmed = 'yes';
            }
            update_post_meta( $post_id, '_all_confirmed', $all_confirmed );
        }
        elseif ( ! array_key_exists( '_confirmed', $request_post ) )
        {
            update_post_meta( $post_id, '_confirmed', '' );
            update_post_meta( $post_id, '_all_confirmed', $all_confirmed );
        }
        
        do_action( 'propertyhive_save_viewing_event', $post_id );   
    }

}
