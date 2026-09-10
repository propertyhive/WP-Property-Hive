<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Appraisal Event Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Appraisal_Event
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Appraisal_Event; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Appraisal_Event {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid, $pagenow;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );

        // This screen only uses GET values to prefill a new, read-only form. The
        // save path below has its own nonce and capability checks.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Optional GET values only prefill the appraisal form; they do not mutate state.
        $request_get = wp_unslash( $_GET );
        $start_timestamp = ( isset( $request_get['start'] ) && is_scalar( $request_get['start'] ) ) ? absint( $request_get['start'] ) : null;
        $end_timestamp = ( isset( $request_get['end'] ) && is_scalar( $request_get['end'] ) ) ? absint( $request_get['end'] ) : null;
        
        $start_date_time = get_post_meta( $post->ID, '_start_date_time', true );
        if ( $start_date_time == '' )
        {
            $start_date_time = gmdate("Y-m-d H:i:s");

            if ( null !== $start_timestamp && '' !== (string) $start_timestamp )
            {
                // $_GET['start'] should be a unix timestamp
                $start_date_time = gmdate( "Y-m-d H:i:s", $start_timestamp );
            }
        }

        echo '<p class="form-field event_start_time_field">
        
            <label for="_start_date">' . esc_html(__('Appraisal Date / Time', 'propertyhive')) . '</label>

            <input type="date" class="small" name="_start_date" id="_start_date" value="' . esc_attr(gmdate("Y-m-d", strtotime($start_date_time))) . '" placeholder="">
            <select id="_start_time_hours" name="_start_time_hours" class="select short" style="width:55px">';
        
        if ( $start_date_time == '' )
        {
            $value = gmdate("H");
        }
        else
        {
            $value = gmdate( "H", strtotime( $start_date_time ) );
        }
        for ( $i = 0; $i < 23; ++$i )
        {
            $j = str_pad($i, 2, '0', STR_PAD_LEFT);
            echo '<option value="' . esc_attr($j) . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . esc_html($j) . '</option>';
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
            $value = gmdate( "i", strtotime( $start_date_time ) );
        }
        for ( $i = 0; $i < 60; $i+=5 )
        {
            $j = str_pad($i, 2, '0', STR_PAD_LEFT);
            echo '<option value="' . esc_attr($j) . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . esc_html($j) . '</option>';
        }
        
        echo '</select>
            
        </p>';

        $durations = array(15, 30, 45, 60, 75, 90, 105, 120, 135, 150, 165, 180);
        $durations = apply_filters( 'propertyhive_appraisal_durations', $durations );

        $value = get_post_meta( $post->ID, '_duration', true );
        if ($value == '')
        {
            $value = apply_filters( 'propertyhive_appraisal_default_duration_minutes', 30 ) * 60; // Default is 30 minutes, unless modified by filter

            if ( null !== $start_timestamp && null !== $end_timestamp )
            {
                // $_GET['start'] and $_GET['end'] should be a unix timestamp
                $duration = ( $end_timestamp - $start_timestamp ) / 60;

                if ( in_array($duration, $durations) )
                {
                    $value = $duration * 60;
                }
            }
        }

        echo '<p class="form-field">
        
            <label for="_duration">' . esc_html(__('Duration', 'propertyhive')) . '</label>
            
            <select id="_duration" name="_duration" class="select short">';

            foreach ( $durations as $duration )
            {
                // convert duration to reable format (i.e. 1 hour 15 minutes)
                $hours = floor($duration / 60);
                $minutes = $duration % 60;
                echo '<option value="' . esc_attr(($duration * 60)) . '"' . ( $value == ($duration * 60) ? 'selected' : '' ) . '>' . esc_html(( $hours > 0 ? $hours . ' hour' . ( $hours != 1 ? 's' : '' ) : '' ) . ( $minutes != '' ? ' '. $minutes . ' minutes' : '' )) . '</option>';
            }
            if ( !in_array( $value / 60, $durations))
            {
                $hours = floor(($value / 60) / 60);
                $minutes = ($value / 60) % 60;
                echo '<option value="' . esc_attr($value) . '" selected>' . esc_html(( $hours > 0 ? $hours . ' hour' . ( $hours != 1 ? 's' : '' ) : '' ) . ( $minutes != '' ? ' ' . $minutes . ' minutes' : '' )) . '</option>';
            }

        echo '</select>
            
        </p>';

        echo '
        <p class="form-field"><label for="_negotiator_ids">' . esc_html(__( 'Attending Negotiator(s)', 'propertyhive' )) . '</label>
        <select id="_negotiator_ids" name="_negotiator_ids[]" multiple="multiple" data-placeholder="' . esc_attr(__( 'Please select a negotiator', 'propertyhive' )) . '" class="multiselect attribute_values">';
        
        $negotiator_ids = get_post_meta( $post->ID, '_negotiator_id' );
        if ( $pagenow == 'post-new.php' )
        {
            $negotiator_ids = array( get_current_user_id() );
        }

        $args = array(
            'number' => 9999,
            'orderby' => 'display_name',
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy Property Negotiator compatibility filter; existing role filters depend on this exact public hook name.
            'role__not_in' => apply_filters( 'property_negotiator_exclude_roles', array('property_hive_contact', 'subscriber') )
        );

        $args = apply_filters( 'propertyhive_negotiators_query', $args );
        
        $user_query = new WP_User_Query( $args );

        if ( ! empty( $user_query->results ) ) 
        {
            foreach ( $user_query->results as $user ) 
            {
                echo '<option value="' . esc_attr($user->ID) . '"';
                if ( in_array($user->ID, $negotiator_ids) )
                {
                    echo ' selected';
                }
                echo '>' . esc_html($user->display_name) . '</option>';
            }
        }

        echo '</select>
        </p>';

        if ( $pagenow != 'post-new.php' )
        {
            propertyhive_wp_hidden_input( array( 
                'id' => '_previous_negotiator_ids', 
                'value' => implode(",", $negotiator_ids),
            ) );
        }

        propertyhive_wp_textarea_input( array( 
            'id' => '_booking_notes', 
            'label' => __( 'Booking Notes', 'propertyhive' ), 
            'desc_tip' => false,
            'class' => ''
        ) );

        if ( get_post_meta( $post->ID, '_status', true ) == 'pending' )
        {
            propertyhive_wp_checkboxes( array(
                'id' => '_confirmed', 
                'wrapper_class' => 'confirmations',
                'label' => __( 'Confirmed', 'propertyhive' ), 
                'options' => array(
                    
                ),
                'desc_tip' => false,
            ) );
        }

        do_action('propertyhive_appraisal_event_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
        $original_appraisal_id = ( isset( $request_get['appraisal_id'] ) && is_scalar( $request_get['appraisal_id'] ) ) ? absint( $request_get['appraisal_id'] ) : 0;
        if ( $original_appraisal_id > 0 )
        {
            echo '<input type="hidden" name="_original_appraisal_id" value="' . esc_attr( $original_appraisal_id ) . '">';
        }

        echo '<input type="hidden" name="_num_requiring_confirmation" id="_num_requiring_confirmation" value="">';

        $previously_selected = array();
        $confirmed = get_post_meta( $post->ID, '_confirmed', true );
        if ( is_array($confirmed) && !empty($confirmed) )
        {
            $previously_selected = $confirmed;
        }

        echo '<script>

            var previously_selected = ' . wp_json_encode( $previously_selected, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';

            jQuery(document).ready(function()
            {
                generate_confirmation_options(true);

                jQuery(\'#_negotiator_ids\').change(function()
                {
                    generate_confirmation_options(false);
                });

                jQuery(\'#_property_id\').change(function()
                {
                    generate_confirmation_options(false);
                });
            });

            function generate_confirmation_options(first_load)
            {
                var num_requiring_confirmation = 0;

                // get previously selected options so we can re-select them after updating options
                if (!first_load)
                {
                    previously_selected = [];
                    if ( jQuery("input[name=\'_confirmed[]\']:checked").length > 0 )
                    {
                        jQuery("input[name=\'_confirmed[]\']:checked").each(function()
                        {
                            previously_selected.push(jQuery(this).val());
                        })
                    }
                }

                var options = [];

                // get owner
                jQuery(\'a[data-appraisal-property-owner-id]\').each(function()
                {
                    options.push( { id: \'owner-\' + jQuery(this).attr(\'data-appraisal-property-owner-id\'), name: \'Owner (\' + jQuery(this).attr(\'data-appraisal-property-owner-name\') + \')\' } );
                });

                // get negs
                jQuery(\'#_negotiator_ids option:selected\').each(function(){ options.push( { id: \'negotiator-\' + jQuery(this).val(), name: \'Negotiator (\' + jQuery(this).text() + \')\' } ); });
                
                // set options
                jQuery(\'.confirmations .ph-radios\').html(\'\');
                if ( options.length > 0 )
                {
                    for ( var i in options )
                    {
                        var confirmation_label = jQuery(\'<label>\');
                        confirmation_label.append(jQuery(\'<input>\', { type: \'checkbox\', class: \'checkbox\', name: \'_confirmed[]\' }).val(options[i].id));
                        confirmation_label.append(document.createTextNode(\' \' + options[i].name));
                        jQuery(\'.confirmations .ph-radios\').append(jQuery(\'<li>\').append(confirmation_label));
                        num_requiring_confirmation = num_requiring_confirmation + 1;
                    }
                }
                else
                {
                    jQuery(\'.confirmations .ph-radios\').html(\'<li>Please select a negotiator or enter property owner details</li>\');
                }

                // reselect previously selected
                
                for ( var i in previously_selected )
                {
                    //console.log(previously_selected[i]);
                    jQuery("input[name=\'_confirmed[]\']").filter(function() { return this.value == previously_selected[i]; }).prop(\'checked\', true);
                }

                jQuery(\'#_num_requiring_confirmation\').val(num_requiring_confirmation);
            }

        </script>';
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

        $original_appraisal_id = ( isset( $request_post['_original_appraisal_id'] ) && is_scalar( $request_post['_original_appraisal_id'] ) ) ? absint( $request_post['_original_appraisal_id'] ) : 0;
        if ( $original_appraisal_id > 0 )
        {
            add_post_meta( $post_id, '_original_appraisal_id', $original_appraisal_id, TRUE );
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
        
        do_action( 'propertyhive_save_appraisal_event', $post_id );   
    }

}
