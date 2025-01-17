<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap propertyhive">

	<h1>Matching Applicants For <?php echo $property->get_formatted_full_address(); ?> (<?php echo count($applicants); ?>)</h1>

	<form method="post" id="mainform" action="" enctype="multipart/form-data">

		<div id="poststuff">
	
		<?php
			if ( !empty($applicants) )
			{
                $select_all_actions = array(
                    'email' => __( 'Email', 'propertyhive' ),
                    'not_interested' => __( 'Not Suitable', 'propertyhive' )
                );

                $select_all_actions = apply_filters( 'propertyhive_matching_select_all_actions', $select_all_actions );
        ?>
        <div class="select-actions" style="padding-bottom:15px">
            <span style="display:inline-block; vertical-align:middle;"><?php echo __( 'Select', 'propertyhive' ); ?>:</span> <?php
                foreach ( $select_all_actions as $key => $value )
                {
                    echo '<a href="javascript:;" class="button" id="select_all_' . esc_attr(sanitize_title($key)) . '" style="display:inline-block; vertical-align:middle;">All - ' . $value . '</a> ';
                }
            ?>
            <a href="javascript:;" class="button" id="select_none" style="display:inline-block; vertical-align:middle;">None</a>
        </div>
        <?php
                echo '<table width="100%">';

                $columns = array(
                    'name' => 'Name',
                    'contact_details' => 'Contact Details',
                    'requirements' => 'Requirements'
                );

                $columns = apply_filters( 'propertyhive_matching_applicants_row_headings', $columns );

                echo '<thead>
                    <tr>';
                foreach ( $columns as $key => $column )
                {
                    echo '<th style="text-align:left">' . $column . '</th>';
                }
                echo '
                        <th style="text-align:left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                ';

                $percentage_lower = get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' );
                $percentage_higher = get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' );

				foreach ( $applicants as $applicant )
				{
					$previously_sent = array();

                    $currency = '&pound;';
                    if ( isset($applicant['applicant_profile']['currency']) && !empty($applicant['applicant_profile']['currency']) )
                    {
                        $PH_Countries = new PH_Countries();
                        $selected_currency = $PH_Countries->get_currency($applicant['applicant_profile']['currency']);
                        if ( $selected_currency !== false )
                        {
                            $currency = $selected_currency['currency_symbol'];
                        }
                    }
                    
                    $applicant_profile_match_history = get_post_meta( $applicant['contact_id'], '_applicant_profile_' . $applicant['applicant_profile']['applicant_profile_id'] . '_match_history', TRUE );
					if ( isset($applicant_profile_match_history[$property->id]) && is_array($applicant_profile_match_history[$property->id]) && !empty($applicant_profile_match_history[$property->id]) )
					{
						$previously_sent = $applicant_profile_match_history[$property->id];
					}

                    $email_address = get_post_meta( $applicant['contact_id'], '_email_address', TRUE );
                    $forbidden_contact_methods = get_post_meta( $applicant['contact_id'], '_forbidden_contact_methods', TRUE );
                    $do_not_email = false;
                    if ( is_array($forbidden_contact_methods) && in_array('email', $forbidden_contact_methods) )
                    {
                        $do_not_email = true;
                    }

                    $requirements = array();
                    if ( 
                        isset($applicant['applicant_profile']['department']) && 
                        ( $applicant['applicant_profile']['department'] == 'residential-sales' || ph_get_custom_department_based_on($applicant['applicant_profile']['department']) == 'residential-sales' 
                    )
                    )
                    {
                        if ( isset($applicant['applicant_profile']['max_price']) && $applicant['applicant_profile']['max_price'] != '' )
                        {
                            $requirements[] = array(
                                'label' => __( 'Maximum Price', 'propertyhive' ),
                                'value' => $currency . ph_display_price_field($applicant['applicant_profile']['max_price']),
                            );
                        }
                        if ( $percentage_lower != '' && $percentage_higher != '' )
                        {
                            $match_price_range_lower = '';
                            if ( 
                                !isset($applicant['applicant_profile']['match_price_range_lower_actual']) || 
                                ( isset($applicant['applicant_profile']['match_price_range_lower_actual']) && $applicant['applicant_profile']['match_price_range_lower_actual'] == '' ) 
                            )
                            {
                                if ( isset($applicant['applicant_profile']['max_price']) && $applicant['applicant_profile']['max_price'] != '' )
                                {
                                    $match_price_range_lower = $applicant['applicant_profile']['max_price'] - ( $applicant['applicant_profile']['max_price'] * ( $percentage_lower / 100 ) );
                                }
                            }
                            else
                            {
                                $match_price_range_lower = $applicant['applicant_profile']['match_price_range_lower'];
                            }

                            $match_price_range_higher = '';
                            if ( 
                                !isset($applicant['applicant_profile']['match_price_range_higher_actual']) || 
                                ( isset($applicant['applicant_profile']['match_price_range_higher_actual']) && $applicant['applicant_profile']['match_price_range_higher_actual'] == '' ) 
                            )
                            {
                                if ( isset($applicant['applicant_profile']['max_price']) && $applicant['applicant_profile']['max_price'] != '' )
                                {
                                    $match_price_range_higher = $applicant['applicant_profile']['max_price'] + ( $applicant['applicant_profile']['max_price'] * ( $percentage_higher / 100 ) );
                                }
                            }
                            else
                            {
                                $match_price_range_higher = $applicant['applicant_profile']['match_price_range_higher'];
                            }

                            if ( 
                                $match_price_range_lower != '' && $match_price_range_higher != ''
                            )
                            {
                                $requirements[] = array(
                                    'label' => __( 'Maximum Price Range', 'propertyhive' ),
                                    'value' => $currency . ph_display_price_field($match_price_range_lower) . ' to ' . $currency . ph_display_price_field($match_price_range_higher),
                                );
                            }
                        }
                    }

                    if ( 
                        isset($applicant['applicant_profile']['department']) && 
                        ( $applicant['applicant_profile']['department'] == 'residential-lettings' || ph_get_custom_department_based_on($applicant['applicant_profile']['department']) == 'residential-lettings' 
                    )
                    )
                    {
                        if ( isset($applicant['applicant_profile']['max_rent']) && $applicant['applicant_profile']['max_rent'] != '' )
                        {
                            $requirements[] = array(
                                'label' => __( 'Maximum Rent', 'propertyhive' ),
                                'value' => $currency . ph_display_price_field($applicant['applicant_profile']['max_rent']) . ' ' . $applicant['applicant_profile']['rent_frequency']
                            );
                        }
                    }

                    if ( isset($applicant['applicant_profile']['min_beds']) && $applicant['applicant_profile']['min_beds'] != '' )
                    {
                        $requirements[] = array(
                            'label' => __( 'Minimum Beds', 'propertyhive' ),
                            'value' => $applicant['applicant_profile']['min_beds'],
                        );
                    }

                    if ( isset($applicant['applicant_profile']['property_types']) && is_array($applicant['applicant_profile']['property_types']) && !empty($applicant['applicant_profile']['property_types']) )
                    {
                        //$requirements[] = 'Max Price: ' . $applicant['applicant_profile']['max_price'];
                    }

                    if ( get_option('propertyhive_applicant_locations_type') == 'text' && isset($applicant['applicant_profile']['location_text']) && $applicant['applicant_profile']['location_text'] != '')
                    {
                        $location_value = $applicant['applicant_profile']['location_text'];

                        if ( isset($applicant['applicant_profile']['location_radius']) && $applicant['applicant_profile']['location_radius'] != '' )
                        {
                            $location_value .= ' (Within '. $applicant['applicant_profile']['location_radius'] .' Miles)';
                        }

                        $requirements[] = array(
                            'label' => __( 'Location', 'propertyhive' ),
                            'value' => $location_value,
                        );
                    }

                    $requirements = apply_filters( 'propertyhive_applicant_requirements_display', $requirements, $applicant['contact_id'], $applicant['applicant_profile'] );
                    
                    if ( isset($applicant['applicant_profile']['notes']) && $applicant['applicant_profile']['notes'] != '' )
                    {
                        $requirements[] = array(
                            'label' => __( 'Notes', 'propertyhive' ),
                            'value' => nl2br( strip_tags($applicant['applicant_profile']['notes']) ),
                        );
                    }

                    $requirements_output = '';
                    if ( empty($requirements) )
                    {
                        $requirements_output = '-';
                    }
                    else
                    {
                        foreach ( $requirements as $requirement )
                        {
                            $requirements_output .= $requirement['label'] . ': ' . $requirement['value'] . '<br>';
                        }
                    }

                    $columns = array(
                        'name' => '<strong><a href="' . get_edit_post_link($applicant['contact_id']) . '" target="_blank">' . get_the_title($applicant['contact_id']) . '</a>' . ( ( isset($applicant['applicant_profile']['grading']) && $applicant['applicant_profile']['grading'] == 'hot' ) ? '<br><span style="color:#C00;">('. __( 'Hot Applicant', 'propertyhive' ) . ')</span>' : '' ) . '</strong>',
                        'contact_details' => 'T: ' . get_post_meta( $applicant['contact_id'], '_telephone_number', TRUE ) . '<br>E: ' . $email_address,
                        'requirements' => $requirements_output,
                    );

                    $columns = apply_filters( 'propertyhive_matching_applicants_row_data', $columns, $applicant, $property->id );

                    echo '<tr id="matching_contact_' . $applicant['contact_id'] . '_applicant_profile_' . $applicant['applicant_profile']['applicant_profile_id'] . '">';
                    foreach ( $columns as $key => $column )
                    {
                        echo '<td style="border-bottom:1px solid #CCC; padding:5px 0;">' . $column . '</td>';
                    }
                    echo '<td style="border-bottom:1px solid #CCC; padding:5px 0;">';

                        echo '<label><input type="checkbox" name="email_contact_applicant_profile_id[]" value="' . $applicant['contact_id'] . '|' . $applicant['applicant_profile']['applicant_profile_id'] . '" ';

                        $post_tip = '';
                        if ( strpos($email_address, '@') === FALSE )
                        {
                            echo ' disabled title="Invalid email address: ' . $email_address . '"';
                            //$post_tip = 'Invalid email address: ' . $email_address;
                        }
                        elseif ( $do_not_email )
                        {
                            echo ' disabled title="Contact via email not permitted - Set under contact details"';
                            //$post_tip = 'Contact via email not permitted - Set under contact details';
                        }
                        else
                        {
                            if ( !empty($previously_sent) )
                            {
                                $post_tip = 'Sent previously via ' . $previously_sent[count($previously_sent) - 1]['method'] . ' on ' . date("jS F Y", strtotime($previously_sent[count($previously_sent) - 1]['date']));

                                if ( 
                                    $on_market_change_date > $previously_sent[count($previously_sent) - 1]['date'] ||
                                    $price_change_date > $previously_sent[count($previously_sent) - 1]['date']
                                )
                                {
                                    if ( $price_change_date > $previously_sent[count($previously_sent) - 1]['date'] )
                                    {
                                        $post_tip .= ', however a price change occurred on ' . date("jS F Y", strtotime($price_change_date));
                                    }
                                    elseif ( $on_market_change_date > $previously_sent[count($previously_sent) - 1]['date'] )
                                    {
                                        $post_tip .= ', however a change to the on market status occurred on ' . date("jS F Y", strtotime($on_market_change_date));
                                    }
                                    echo ' checked';
                                }
                            }
                            else
                            {
                                echo ' checked';
                            }
                        }

                        echo '> Email Property To Applicant<span style="font-weight:400;">' . ( ($post_tip != '') ? ' - ' . $post_tip : '' ) . '</span></label>

                            <br>';

                        do_action( 'propertyhive_property_match_send_methods', $applicant['contact_id'], $applicant['applicant_profile']['applicant_profile_id'], $property->id );

                        echo '<label><input type="checkbox" name="not_interested_contact_applicant_profile_id[]" value="' . $applicant['contact_id'] . '|' . $applicant['applicant_profile']['applicant_profile_id'] . '"> Property Not Suitable</label>';

                    echo '</td>';
                    echo '</tr>';
				}

                echo '
                    </tbody>
                </table>
                ';
			}
            else
            {
                echo __( 'No matching applicants found', 'propertyhive' );
            }
		?>

        <p class="submit">

        	<input name="save" class="button-primary" type="submit" value="<?php echo __( 'Continue', 'propertyhive' ); ?>" />

            <a href="<?php echo get_edit_post_link((int)$_GET['property_id']); ?>" class="button"><?php _e( 'Cancel', 'propertyhive' ); ?></a>

        	<input type="hidden" name="step" value="one" />
        	<?php wp_nonce_field( 'propertyhive-matching-applicants' ); ?>

        </p>

        <p>
        	<?php echo __( "If you've opted to email any of the applicants you'll have the ability to edit the contents of the email in the next step.", 'propertyhive' ); ?>
        </p>

        </div>

	</form>

</div>

<script>

    jQuery(document).ready(function()
    {
    	jQuery('body').on('change', 'input[name=\'not_interested_contact_applicant_profile_id[]\']', function()
        {
            var applicant_contact_profile_id = jQuery(this).val();
            var applicant_contact_profile_id_split = applicant_contact_profile_id.split('|');

            jQuery('input[name=\'email_contact_applicant_profile_id[]\'][value=\'' + applicant_contact_profile_id + '\']').attr('checked', false);

            opacity = 0.4;
            if ( !jQuery(this).is(':checked') )
            {
            	opacity = 1;
            }
            jQuery('#matching_contact_' + applicant_contact_profile_id_split[0] + '_applicant_profile_' + applicant_contact_profile_id_split[1]).animate({
                opacity: opacity
            },
            {
                duration: 250
            });

            return false;
        });

        jQuery('.select-actions a').click(function(e)
        {
            e.preventDefault();

            var id = jQuery(this).attr('id').replace("select_all_", "");

            jQuery('input[name$=\'_contact_applicant_profile_id[]\']').prop('checked', false);

            if ( id != 'select_none' )
            {
                jQuery('input[name=\'' + id + '_contact_applicant_profile_id[]\']').prop('checked', 'checked');
            }
        });
    });

</script>