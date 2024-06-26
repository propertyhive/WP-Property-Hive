<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$readonly = isset($readonly) ? $readonly : false;

echo '<div class="propertyhive_meta_box">';
    
    echo '<div class="options_group">';

    wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
    
    $start_date_time = get_post_meta( $post->ID, '_start_date_time', true );
    if ( $start_date_time == '' )
    {
        $start_date_time = date("Y-m-d H:i:s");

        if ( isset($_GET['start']) && $_GET['start'] != '' )
        {
            // $_GET['start'] should be a unix timestamp
            $start_date_time = date("Y-m-d H:i:s", $_GET['start']);
        }
    }

    echo '<p class="form-field event_start_time_field">
    
        <label for="_start_date">' . esc_html(__('Viewing Date / Time', 'propertyhive')) . '</label>';
    
    if ( $readonly )
    {
        echo esc_html(date("H:i", strtotime($start_date_time)) . ' on ' . date("l jS F Y", strtotime($start_date_time)));
    }
    else
    {
        echo '<input type="date" class="small" name="_start_date" id="_start_date" value="' . esc_attr(date("Y-m-d", strtotime($start_date_time))) . '" placeholder="">
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
            $value = date( "i", strtotime( $start_date_time ) );
        }
        for ( $i = 0; $i < 60; $i+=5 )
        {
            $j = str_pad($i, 2, '0', STR_PAD_LEFT);
            echo '<option value="' . esc_attr($j) . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . esc_html($j) . '</option>';
        }
        
        echo '</select>';
    }
    echo '</p>';

    $durations = array(15, 30, 45, 60, 75, 90, 105, 120, 135, 150, 165, 180);
    $durations = apply_filters( 'propertyhive_viewing_durations', $durations );

    $value = get_post_meta( $post->ID, '_duration', true );
    if ($value == '')
    {
        $value = apply_filters( 'propertyhive_viewing_default_duration_minutes', 30 ) * 60; // Default is 30 minutes, unless modified by filter

        if ( isset($_GET['start']) && $_GET['start'] != '' && isset($_GET['end']) && $_GET['end'] != '' )
        {
            // $_GET['start'] and $_GET['end'] should be a unix timestamp
            $duration = ($_GET['end'] - $_GET['start']) / 60;

            if ( in_array($duration, $durations) )
            {
                $value = $duration * 60;
            }
        }
    }

    if ( !$readonly )
    {
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
    }

    echo '
    <p class="form-field"><label for="_negotiator_ids">' . esc_html(__( 'Attending Negotiator(s)', 'propertyhive' )) . '</label>';

    $negotiator_ids = get_post_meta( $post->ID, '_negotiator_id' );
    if ( $readonly )
    {
        if ( !empty($negotiator_ids) )
        {
            $names = array();
            foreach ( $negotiator_ids as $negotiator_id )
            {
                $user_info = get_userdata($negotiator_id);
                if ( $user_info !== FALSE )
                {
                    $names[] = $user_info->display_name;
                }
            }
            echo esc_html(implode(", ", $names));
        }
        else
        {
            echo esc_html(__( 'Unaccompanied', 'propertyhive' ));
        }
    }
    else
    {
        echo '
        <select id="_negotiator_ids" name="_negotiator_ids[]" multiple="multiple" data-placeholder="' . esc_attr(__( 'Unaccompanied', 'propertyhive' )) . '" class="multiselect attribute_values">';
        
        if ( isset($pagenow) && $pagenow == 'post-new.php' )
        {
            $negotiator_ids = array( get_current_user_id() );
        }

        $args = array(
            'number' => 9999,
            'orderby' => 'display_name',
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

        echo '</select>';
    }
    echo '</p>';

    if ( isset($pagenow) && $pagenow == 'post-new.php' )
    {

    }
    else
    {
        propertyhive_wp_hidden_input( array( 
            'id' => '_previous_negotiator_ids', 
            'value' => implode(",", $negotiator_ids),
        ) );
    }

    if ( $readonly )
    {
        echo '<p class="form-field">
    
            <label for="">' . esc_html(__('Booking Notes', 'propertyhive')) . '</label>

            ' . nl2br(esc_html($viewing->booking_notes)) . '

        </p>';
    }
    else
    {
        propertyhive_wp_textarea_input( array( 
            'id' => '_booking_notes', 
            'label' => __( 'Booking Notes', 'propertyhive' ), 
            'desc_tip' => false,
            'class' => ''
        ) );
    }

    if ( !$readonly && get_post_meta( $post->ID, '_status', true ) == 'pending' )
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

    do_action('propertyhive_viewing_event_fields');
    
    echo '</div>';
    
    echo '</div>';
    
    if ( isset($_GET['viewing_id']) )
    {
        echo '<input type="hidden" name="_original_viewing_id" value="' . esc_attr((int)$_GET['viewing_id']) . '">';
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

            jQuery(\'#_applicant_contact_ids\').change(function()
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

            // get applicant
            jQuery(\'a[data-viewing-applicant-id]\').each(function()
            {
                options.push( { id: \'applicant-\' + jQuery(this).attr(\'data-viewing-applicant-id\'), name: \'Applicant (\' + jQuery(this).attr(\'data-viewing-applicant-name\') + \')\' } );
            });

            // get owner
            jQuery(\'a[data-viewing-owner-id]\').each(function()
            {
                options.push( { id: \'owner-\' + jQuery(this).attr(\'data-viewing-owner-id\'), name: \'Owner (\' + jQuery(this).attr(\'data-viewing-owner-name\') + \')\' } );
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
                jQuery(\'.confirmations .ph-radios\').html(\'<li>Please select a negotiator, applicant or property</li>\');
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