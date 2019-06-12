<div class="wrap propertyhive">

	<h1>Matching Applicants For <?php echo $property->get_formatted_full_address(); ?> (<?php echo count($applicants); ?>)</h1>

	<form method="post" id="mainform" action="" enctype="multipart/form-data">

		<div id="poststuff">
		
		<?php
			/*echo '<div style="background:#F3F3F3; border:1px solid #DDD; padding:20px;">
                
                <h3 style="padding-top:0; margin-top:0;">Property Details</h3>';
            if ( 
                isset($applicant_profile['department']) && $applicant_profile['department'] == 'residential-sales' &&
                isset($applicant_profile['max_price_actual']) && $applicant_profile['max_price_actual'] != '' && $applicant_profile['max_price_actual'] != 0
            )
            {
                echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                    <strong>Maximum Price:</strong><br>
                    &pound;' . number_format($applicant_profile['max_price']) . '
                </div>';
            }
            if ( 
                isset($applicant_profile['department']) && $applicant_profile['department'] == 'residential-lettings' &&
                isset($applicant_profile['max_price_actual']) && $applicant_profile['max_price_actual'] != '' && $applicant_profile['max_price_actual'] != 0
            )
            {
                echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                    <strong>Maximum Rent:</strong><br>
                    &pound;' . number_format($applicant_profile['max_rent']) . ' ' . $applicant_profile['rent_frequency'] . '
                </div>';
            }
            if ( 
                isset($applicant_profile['department']) && 
                ( $applicant_profile['department'] == 'residential-sales' || $applicant_profile['department'] == 'residential-lettings' )
            )
            {
                if ( isset($applicant_profile['min_beds']) && $applicant_profile['min_beds'] != '' && $applicant_profile['min_beds'] != 0 )
                {
                    echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                        <strong>Minimum Beds:</strong><br>
                        ' . $applicant_profile['min_beds'] . '
                    </div>';
                }
                if ( isset($applicant_profile['property_types']) && is_array($applicant_profile['property_types']) && !empty($applicant_profile['property_types']) )
                {
                    $terms = get_terms('property_type', array('hide_empty' => false, 'fields' => 'names', 'include' => $applicant_profile['property_types']));
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                    {
                        $sliced_terms = array_slice( $terms, 0, 2 );
                        echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                            <strong>Property Types:</strong><br>
                            ' . implode(", ", $sliced_terms) . ( (count($terms) > 2) ? '<span title="' . addslashes( implode(", ", $terms) ) .'"> + ' . (count($terms) - 2) . ' more</span>' : '' ) . '
                        </div>';
                    }
                }
            }
            if ( 
                isset($applicant_profile['department']) && 
                ( $applicant_profile['department'] == 'commercial' )
            )
            {
                if ( isset($applicant_profile['available_as']) && is_array($applicant_profile['available_as']) && !empty($applicant_profile['available_as']) )
                {
                    $available_as = array();
                    if ( in_array('sale', $applicant_profile['available_as']) )
                    {
                        $available_as[] = 'For Sale';
                    }
                    if ( in_array('rent', $applicant_profile['available_as']) )
                    {
                        $available_as[] = 'To Rent';
                    }

                    echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                        <strong>Available As:</strong><br>
                        ' . implode(", ", $available_as) .'
                    </div>';
                }

                if ( isset($applicant_profile['commercial_property_types']) && is_array($applicant_profile['commercial_property_types']) && !empty($applicant_profile['commercial_property_types']) )
                {
                    $terms = get_terms('commercial_property_type', array('hide_empty' => false, 'fields' => 'names', 'include' => $applicant_profile['commercial_property_types']));
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                    {
                        $sliced_terms = array_slice( $terms, 0, 2 );
                        echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                            <strong>Property Types:</strong><br>
                            ' . implode(", ", $sliced_terms) . ( (count($terms) > 2) ? '<span title="' . addslashes( implode(", ", $terms) ) .'"> + ' . (count($terms) - 2) . ' more</span>' : '' ) . '
                        </div>';
                    }
                }
            }
            if ( isset($applicant_profile['locations']) && is_array($applicant_profile['locations']) && !empty($applicant_profile['locations']) )
            {
                $terms = get_terms('location', array('hide_empty' => false, 'fields' => 'names', 'include' => $applicant_profile['locations']));
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                {
                    $sliced_terms = array_slice( $terms, 0, 2 );
                    echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                        <strong>Locations:</strong><br>
                        ' . implode(", ", $sliced_terms) . ( (count($terms) > 2) ? ' <span title="' . addslashes( implode(", ", $terms) ) .'">+ ' . (count($terms) - 2) . ' more</span>' : '' ) . '
                    </div>';
                }
            }

            if ( isset($applicant_profile['notes']) && $applicant_profile['notes'] != '' )
            {
                echo '<div style="display:inline-block; width:100%; vertical-align:top; margin-top:15px;">
                        <strong>Additional Requirement Notes:</strong><br>
                        ' . nl2br( strip_tags( $applicant_profile['notes'] ) ) . '
                    </div>';
            }

            echo '</div><br><br>';*/
		?>

		<?php
			if ( !empty($applicants) )
			{
               // var_dump($applicants);
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

				foreach ( $applicants as $applicant )
				{
					$previously_sent = array();
                    
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
                    if ( isset($applicant['applicant_profile']['max_price']) && $applicant['applicant_profile']['max_price'] != '' )
                    {
                        $requirements[] = 'Max Price: &pound;' . number_format($applicant['applicant_profile']['max_price']);
                    }
                    if ( isset($applicant['applicant_profile']['min_beds']) && $applicant['applicant_profile']['min_beds'] != '' )
                    {
                        $requirements[] = 'Min Beds: ' . $applicant['applicant_profile']['min_beds'];
                    }
                    if ( isset($applicant['applicant_profile']['property_types']) && is_array($applicant['applicant_profile']['property_types']) && !empty($applicant['applicant_profile']['property_types']) )
                    {
                        //$requirements[] = 'Max Price: ' . $applicant['applicant_profile']['max_price'];
                    }
                    if ( isset($applicant['applicant_profile']['locations']) && is_array($applicant['applicant_profile']['locations']) && !empty($applicant['applicant_profile']['locations']) )
                    {
                        //$requirements[] = 'Max Price: ' . $applicant['applicant_profile']['max_price'];
                    }
                    if ( isset($applicant['applicant_profile']['notes']) && $applicant['applicant_profile']['notes'] != '' )
                    {
                        $requirements[] = 'Notes: ' . nl2br($applicant['applicant_profile']['notes']);
                    }
                    $requirements = implode("<br>", $requirements);
                    if ( $requirements == '' )
                    {
                        $requirements = '-';
                    }

                    $columns = array(
                        'name' => '<a href="' . get_edit_post_link($applicant['contact_id']) . '" target="_blank">' . get_the_title($applicant['contact_id']) . '</a>',
                        'contact_details' => 'T: ' . get_post_meta( $applicant['contact_id'], '_telephone_number', TRUE ) . '<br>E: ' . $email_address,
                        'requirements' => $requirements,
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

</script>