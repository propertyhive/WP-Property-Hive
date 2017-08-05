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
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        $start_date_time = get_post_meta( $post->ID, '_start_date_time', true );
        if ( $start_date_time == '' )
        {
            $start_date_time = date("Y-m-d H:i:s");
        }

        echo '<p class="form-field event_start_time_field">
        
            <label for="_start_date">' . __('Viewing Date / Time', 'propertyhive') . '</label>
            
            <input type="text" id="_start_date" name="_start_date" class="date-picker short" placeholder="yyyy-mm-dd" style="width:120px;" value="' . date("Y-m-d", strtotime($start_date_time)) . '">
            <select id="_start_time_hours" name="_start_time_hours" class="select short" style="width:55px">';
        
        if ( $start_date_time == '' )
        {
            $value = date("H");
        }
        else
        {
            $value = date( "H", strtotime( $start_date_time ) );
        }
        for ( $i = 0; $i < 23; ++$i )
        {
            $j = str_pad($i, 2, '0', STR_PAD_LEFT);
            echo '<option value="' . $j . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . $j . '</option>';
        }
        
        echo '</select>
        :
        <select id="_start_time_minutes" name="_start_time_minutes" class="select short" style="width:55px">';
        
        if ( $start_date_time == '' )
        {
            $value = '';
        }
        else
        {
            $value = date( "i", strtotime( $start_date_time ) );
        }
        for ( $i = 0; $i < 60; $i+=5 )
        {
            $j = str_pad($i, 2, '0', STR_PAD_LEFT);
            echo '<option value="' . $j . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . $j . '</option>';
        }
        
        echo '</select>
            
        </p>';

        $value = get_post_meta( $post->ID, '_duration', true );
        if ($value == '')
        {
            $value = 30 * 60; // Default is 30 minutes
        }
        echo '<p class="form-field">
        
            <label for="_duration">' . __('Duration', 'propertyhive') . '</label>
            
            <select id="_duration" name="_duration" class="select short">
                <option value="' . (15 * 60) . '"' . ( $value == (15 * 60) ? 'selected' : '' ) . '>15 minutes</option>
                <option value="' . (30 * 60) . '"' . ( $value == (30 * 60) ? 'selected' : '' ) . '>30 minutes</option>
                <option value="' . (45 * 60) . '"' . ( $value == (45 * 60) ? 'selected' : '' ) . '>45 minutes</option>
                <option value="' . (60 * 60) . '"' . ( $value == (60 * 60) ? 'selected' : '' ) . '>1 hour</option>
                <option value="' . (90 * 60) . '"' . ( $value == (90 * 60) ? 'selected' : '' ) . '>1 hour 30 minutes</option>
                <option value="' . (120 * 60) . '"' . ( $value == (120 * 60) ? 'selected' : '' ) . '>2 hours</option>
                <option value="' . (150 * 60) . '"' . ( $value == (150 * 60) ? 'selected' : '' ) . '>2 hour 30 minutes</option>
                <option value="' . (180 * 60) . '"' . ( $value == (180 * 60) ? 'selected' : '' ) . '>3 hours</option>
            </select>
            
        </p>';

        echo '
        <p class="form-field"><label for="_negotiator_ids">' . __( 'Attending Negotiator(s)', 'propertyhive' ) . '</label>
        <select id="_negotiator_ids" name="_negotiator_ids[]" multiple="multiple" data-placeholder="' . __( 'Unattended', 'propertyhive' ) . '" class="multiselect attribute_values">';
        
        $negotiator_ids = get_post_meta( $post->ID, '_negotiator_id' );
        if ( empty($negotiator_ids) )
        {
            $negotiator_ids = array( get_current_user_id() );
        }

        $args = array(
            'number' => 9999,
            'orderby' => 'display_name',
            'role__not_in' => array('property_hive_contact') 
        );
        $user_query = new WP_User_Query( $args );

        if ( ! empty( $user_query->results ) ) 
        {
            foreach ( $user_query->results as $user ) 
            {
                echo '<option value="' . $user->ID . '"';
                if ( in_array($user->ID, $negotiator_ids) )
                {
                    echo ' selected';
                }
                echo '>' . $user->display_name . '</option>';
            }
        }

        echo '</select>
        </p>';

        propertyhive_wp_textarea_input( array( 
            'id' => '_booking_notes', 
            'label' => __( 'Booking Notes', 'propertyhive' ), 
            'desc_tip' => false,
            'class' => ''
        ) );

        do_action('propertyhive_viewing_event_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
        if ( isset($_GET['viewing_id']) )
        {
            echo '<input type="hidden" name="_original_viewing_id" value="' . $_GET['viewing_id'] . '">';
        }
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
            add_post_meta( $post_id, '_original_viewing_id', $_POST['_original_viewing_id'], TRUE );
        }

        update_post_meta( $post_id, '_start_date_time', $_POST['_start_date'] . ' ' . $_POST['_start_time_hours'] . ':' . $_POST['_start_time_minutes'] . ':00' );
        update_post_meta( $post_id, '_duration', $_POST['_duration'] );

        delete_post_meta($post_id, '_negotiator_id');
        if ( !empty($_POST['_negotiator_ids']) )
        {
            foreach ( $_POST['_negotiator_ids'] as $negotiator_id )
            {
                add_post_meta( $post_id, '_negotiator_id', $negotiator_id );
            }
        }

        update_post_meta( $post_id, '_booking_notes', $_POST['_booking_notes'] );
        
        do_action( 'propertyhive_save_viewing_event', $post_id );   
    }

}
