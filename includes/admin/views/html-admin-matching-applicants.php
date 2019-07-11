<div class="wrap propertyhive">

	<h1>Matching Applicants For <?php echo $property->get_formatted_full_address(); ?> (<?php echo count($applicants); ?>)</h1>

	<form method="post" id="mainform" action="" enctype="multipart/form-data">

		<div id="poststuff">
	
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