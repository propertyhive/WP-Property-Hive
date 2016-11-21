<div class="wrap propertyhive">

	<h1>Matching Properties For <?php echo get_the_title($contact_id); ?> (<?php echo count($properties); ?>)</h1>

	<form method="post" id="mainform" action="" enctype="multipart/form-data">

		<div id="poststuff">
		
		<?php
			echo '<div style="background:#F3F3F3; border:1px solid #DDD; padding:20px;">
                
                <h3 style="padding-top:0; margin-top:0;">Applicant Requirements</h3>';
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
                        ' . strip_tags( $applicant_profile['notes'] ) . '
                    </div>';
            }

            echo '</div>';
		?>

		<?php
			if ( !empty($properties) )
			{
				foreach ( $properties as $property )
				{
					$previously_sent = array();
					if ( isset($applicant_profile_match_history[$property->id]) && is_array($applicant_profile_match_history[$property->id]) && !empty($applicant_profile_match_history[$property->id]) )
					{
						$previously_sent = $applicant_profile_match_history[$property->id];
					}

					echo '<div id="matching_applicant_' . $contact_id . '_property_' . $property->id . '" style="padding:20px 0; border-bottom:1px solid #CCC;">';
                    
                        echo '<div style="float:left; width:18%;"><a href="' . get_edit_post_link( $property->id ) . '" target="_blank"><img src="' . $property->get_main_photo_src() . '" style="max-width:100%; margin:0 auto; display:block;" alt="' . addslashes($property->get_formatted_summary_address()) . '"></a></div>';
                        
                        echo '<div style="float:right; width:80%;">';
                            
                            echo '<h3 style="margin:0; padding:0; margin-bottom:9px;"><a href="' . get_edit_post_link( $property->id ) . '" target="_blank">' . $property->get_formatted_summary_address() . '</a></h3>';

                            echo '<div style="margin-bottom:7px; font-size:15px;">
                                <strong>' . ( ($property->_department == 'residential-lettings') ? __('Rent', 'propertyhive') : __('Price', 'propertyhive') ) . ': ' . $property->price_qualifier . ' ' . $property->get_formatted_price() . '</strong>
                                | ';
                            if ($property->department != 'commercial')
                            {
                                echo $property->bedrooms . ' bed | ';
                            }
                            else
                            {
                                $floor_area = $property->get_formatted_floor_area();
                                if ( $floor_area != '' )
                                {
                                    echo $floor_area . ' | ';
                                }
                            }
                            $property_type = $property->get_property_type();
                            if ( $property_type != '' )
                            {
                                echo $property_type . ' | ';
                            }
                            echo ' ' . $property->get_availability() . '
                            </div>';

                            echo '<div style="margin-bottom:7px;">' . strip_tags(get_the_excerpt()) . '</div>';

                            echo '<div style="background:#F8F8F8; padding:12px 11px; line-height:1.7em; border:1px solid #DDD; font-weight:700">

                            	<label><input type="checkbox" name="email_property_id[]" value="' . $property->id . '" ';

                            $post_tip = '';
                            if ( strpos($email_address, '@') === FALSE )
                            {
                                echo ' disabled title="Invalid email address: ' . $email_address . '"';
                                $post_tip = 'Invalid email address: ' . $email_address;
                            }
                            elseif ( $do_not_email )
                            {
                                echo ' disabled title="Contact via email not permitted - Set under contact details"';
                                $post_tip = 'Contact via email not permitted - Set under contact details';
                            }
                            else
                            {
                                if ( !empty($previously_sent) )
                                {
                                    $post_tip = 'Sent previously on ' . date("jS F Y", strtotime($previously_sent[count($previously_sent) - 1]['date']));
                                }
                                else
                                {
                                    echo ' checked';
                                }
                            }

                            echo '> Email Property To Applicant<span style="font-weight:400;">' . ( ($post_tip != '') ? ' - ' . $post_tip : '' ) . '</span></label>

                            	<br>

                            	<label><input type="checkbox" name="not_interested_property_id[]" value="' . $property->id . '"> Property Not Suitable</label>

                            </div>';

                        echo '</div>';

                        echo '<div style="clear:both"></div>';

                    echo '</div>';
				}
			}
		?>

        <p class="submit">

        	<input name="save" class="button-primary" type="submit" value="<?php echo __( 'Continue', 'propertyhive' ); ?>" />

            <a href="<?php echo get_edit_post_link($_GET['contact_id']); ?>" class="button"><?php _e( 'Cancel', 'propertyhive' ); ?></a>

        	<input type="hidden" name="step" value="one" />
        	<?php wp_nonce_field( 'propertyhive-matching-properties' ); ?>

        </p>

        <p>
        	<?php echo __( "If you've opted to email any of the properties you'll have the ability to edit the contents of the email in the next step.", 'propertyhive' ); ?>
        </p>

        </div>

	</form>

</div>

<script>

	jQuery('body').on('change', 'input[name=\'not_interested_property_id[]\']', function()
    {
        var property_id = jQuery(this).val();

        jQuery('input[name=\'email_property_id[]\'][value=\'' + property_id + '\']').attr('checked', false);

        opacity = 0.4;
        if ( !jQuery(this).is(':checked') )
        {
        	opacity = 1;
        }
        jQuery('#matching_applicant_<?php echo $contact_id; ?>_property_' + property_id).animate({
            opacity: opacity
        },
        {
            duration: 250
        });

        return false;
    });

</script>