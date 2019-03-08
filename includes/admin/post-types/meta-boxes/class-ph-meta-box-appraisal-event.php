<?php
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
class PH_Meta_Box_Appraisal_Event {

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
        
            <label for="_start_date">' . __('Appraisal Date / Time', 'propertyhive') . '</label>
            
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
        <select id="_negotiator_ids" name="_negotiator_ids[]" multiple="multiple" data-placeholder="' . __( 'Please select a negotiator', 'propertyhive' ) . '" class="multiselect attribute_values">';
        
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
        
        if ( isset($_GET['appraisal_id']) )
        {
            echo '<input type="hidden" name="_original_appraisal_id" value="' . (int)$_GET['appraisal_id'] . '">';
        }

        echo '<input type="hidden" name="_num_requiring_confirmation" id="_num_requiring_confirmation" value="">';

        $previously_selected = array();
        $confirmed = get_post_meta( $post->ID, '_confirmed', true );
        if ( is_array($confirmed) && !empty($confirmed) )
        {
            $previously_selected = $confirmed;
        }

        echo '<script>

            var previously_selected = ' . json_encode($previously_selected) . ';

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
                        jQuery(\'.confirmations .ph-radios\').append(\'<li><label><input type="checkbox" class="checkbox" name="_confirmed[]" value="\' + options[i].id + \'"> \' + options[i].name + \'</label></li>\');
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
                    jQuery("input[name=\'_confirmed[]\'][value=\'" + previously_selected[i] + "\']").prop(\'checked\', true);
                }

                jQuery(\'#_num_requiring_confirmation\').val(num_requiring_confirmation);
            }

        </script>';
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

        if ( isset($_POST['_original_appraisal_id']) && $_POST['_original_appraisal_id'] != '' )
        {
            add_post_meta( $post_id, '_original_appraisal_id', (int)$_POST['_original_appraisal_id'], TRUE );
        }

        update_post_meta( $post_id, '_start_date_time', ph_clean($_POST['_start_date']) . ' ' . (int)$_POST['_start_time_hours'] . ':' . (int)$_POST['_start_time_minutes'] . ':00' );
        update_post_meta( $post_id, '_duration', (int)$_POST['_duration'] );

        delete_post_meta($post_id, '_negotiator_id');
        if ( !empty($_POST['_negotiator_ids']) )
        {
            foreach ( $_POST['_negotiator_ids'] as $negotiator_id )
            {
                add_post_meta( $post_id, '_negotiator_id', (int)$negotiator_id );
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
        
        do_action( 'propertyhive_save_appraisal_event', $post_id );   
    }

}