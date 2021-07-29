<div class="wrap propertyhive">

	<h1>Matching Properties For <?php echo get_the_title($contact_id); ?> (<?php echo count($properties); ?>)</h1>

	<form method="post" id="mainform" action="" enctype="multipart/form-data">

		<div id="poststuff">
		
		<?php
            $percentage_lower = get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' );
            $percentage_higher = get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' );

			echo '<div style="background:#F3F3F3; border:1px solid #DDD; padding:20px;">
                
                <h3 style="padding-top:0; margin-top:0;">Applicant Requirements</h3>';

            $requirements = array();

            if ( 
                isset($applicant_profile['department']) && ( $applicant_profile['department'] == 'residential-sales' || ph_get_custom_department_based_on($applicant_profile['department']) == 'residential-sales' ) &&
                isset($applicant_profile['max_price_actual']) && $applicant_profile['max_price_actual'] != '' && $applicant_profile['max_price_actual'] != 0
            )
            {
                $requirements[] = array(
                    'label' => __( 'Maximum Price', 'propertyhive' ),
                    'value' => '&pound;' . number_format($applicant_profile['max_price']),
                );
            }
            if ( 
                isset($applicant_profile['department']) && ( $applicant_profile['department'] == 'residential-sales' || ph_get_custom_department_based_on($applicant_profile['department']) == 'residential-sales' )
            )
            {
                if ( $percentage_lower != '' && $percentage_higher != '' )
                {
                    $match_price_range_lower = '';
                    if ( !isset($applicant_profile['match_price_range_lower_actual']) || ( isset($applicant_profile['match_price_range_lower_actual']) && $applicant_profile['match_price_range_lower_actual'] == '' ) )
                    {
                        if ( isset($applicant_profile['max_price_actual']) && $applicant_profile['max_price_actual'] != '' )
                        {
                            $match_price_range_lower = $applicant_profile['max_price_actual'] - ( $applicant_profile['max_price_actual'] * ( $percentage_lower / 100 ) );
                        }
                    }
                    else
                    {
                        $match_price_range_lower = $applicant_profile['match_price_range_lower_actual'];
                    }

                    $match_price_range_higher = '';
                    if ( !isset($applicant_profile['match_price_range_higher_actual']) || ( isset($applicant_profile['match_price_range_higher_actual']) && $applicant_profile['match_price_range_higher_actual'] == '' ) )
                    {
                        if ( isset($applicant_profile['max_price_actual']) && $applicant_profile['max_price_actual'] != '' )
                        {
                            $match_price_range_higher = $applicant_profile['max_price_actual'] + ( $applicant_profile['max_price_actual'] * ( $percentage_higher / 100 ) );
                        }
                    }
                    else
                    {
                        $match_price_range_higher = $applicant_profile['match_price_range_higher_actual'];
                    }

                    if ( 
                        $match_price_range_lower != '' && $match_price_range_higher != ''
                    )
                    {
                        $requirements[] = array(
                            'label' => __( 'Match Price Range', 'propertyhive' ),
                            'value' => '&pound;' . number_format($match_price_range_lower) . ' to &pound;' . number_format($match_price_range_higher),
                        );
                    }
                }
            }
            if ( 
                isset($applicant_profile['department']) && ( $applicant_profile['department'] == 'residential-lettings' || ph_get_custom_department_based_on($applicant_profile['department']) == 'residential-lettings' ) &&
                isset($applicant_profile['max_price_actual']) && $applicant_profile['max_price_actual'] != '' && $applicant_profile['max_price_actual'] != 0
            )
            {
                $requirements[] = array(
                    'label' => __( 'Maximum Rent', 'propertyhive' ),
                    'value' => '&pound;' . number_format($applicant_profile['max_rent']) . ' ' . $applicant_profile['rent_frequency'],
                );
            }
            if ( 
                isset($applicant_profile['department']) && 
                ( 
                    $applicant_profile['department'] == 'residential-sales' || 
                    $applicant_profile['department'] == 'residential-lettings' ||
                    ph_get_custom_department_based_on($applicant_profile['department']) == 'residential-sales' ||
                    ph_get_custom_department_based_on($applicant_profile['department']) == 'residential-lettings'
                )
            )
            {
                if ( isset($applicant_profile['min_beds']) && $applicant_profile['min_beds'] != '' && $applicant_profile['min_beds'] != 0 )
                {
                    $requirements[] = array(
                        'label' => __( 'Minimum Beds', 'propertyhive' ),
                        'value' => $applicant_profile['min_beds'],
                    );
                }
                if ( isset($applicant_profile['property_types']) && is_array($applicant_profile['property_types']) && !empty($applicant_profile['property_types']) )
                {
                    $terms = get_terms('property_type', array('hide_empty' => false, 'fields' => 'names', 'include' => $applicant_profile['property_types']));
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                    {
                        $sliced_terms = array_slice( $terms, 0, 2 );

                        $requirements[] = array(
                            'label' => __( 'Property Types', 'propertyhive' ),
                            'value' => implode(", ", $sliced_terms) . ( (count($terms) > 2) ? '<span title="' . addslashes( implode(", ", $terms) ) .'"> + ' . (count($terms) - 2) . ' more</span>' : '' ),
                        );
                    }
                }
            }
            if ( 
                isset($applicant_profile['department']) && 
                ( $applicant_profile['department'] == 'commercial' || ph_get_custom_department_based_on($applicant_profile['department']) == 'commercial' )
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

                    $requirements[] = array(
                        'label' => __( 'Available As', 'propertyhive' ),
                        'value' => implode(", ", $available_as),
                    );
                }

                if ( 
                    (isset($applicant_profile['min_floor_area_actual']) && $applicant_profile['min_floor_area_actual'] != '') 
                    || 
                    (isset($applicant_profile['max_floor_area_actual']) && $applicant_profile['max_floor_area_actual'] != '')
                )
                {
                    $sizes = array('min' => '', 'max' => '');
                    $value = '';
                    if ( isset($applicant_profile['min_floor_area_actual']) && $applicant_profile['min_floor_area_actual'] != '' )
                    {
                        $sizes['min'] = $applicant_profile['min_floor_area_actual'];
                    }
                    if ( isset($applicant_profile['max_floor_area_actual']) && $applicant_profile['max_floor_area_actual'] != '' )
                    {
                        $sizes['max'] = $applicant_profile['max_floor_area_actual'];
                    }
                    if ( $sizes['min'] != '' && $sizes['max'] != '' )
                    {
                        $value = number_format($sizes['min']) . ' - ' . number_format($sizes['max']) . ' Sq Ft';
                    }
                    if ( $sizes['min'] != '' && $sizes['max'] == '' )
                    {
                        $value = 'From ' . number_format($sizes['min']) . ' Sq Ft';
                    }
                    if ( $sizes['min'] == '' && $sizes['max'] != '' )
                    {
                        $value = 'Up To ' . number_format($sizes['max']) . ' Sq Ft';
                    }

                    if ( $value != '' )
                    {
                        $requirements[] = array(
                            'label' => __( 'Floor Area', 'propertyhive' ),
                            'value' => $value,
                        );
                    }
                }

                if ( isset($applicant_profile['commercial_property_types']) && is_array($applicant_profile['commercial_property_types']) && !empty($applicant_profile['commercial_property_types']) )
                {
                    $terms = get_terms('commercial_property_type', array('hide_empty' => false, 'fields' => 'names', 'include' => $applicant_profile['commercial_property_types']));
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                    {
                        $sliced_terms = array_slice( $terms, 0, 2 );

                        $requirements[] = array(
                            'label' => __( 'Property Types', 'propertyhive' ),
                            'value' => implode(", ", $sliced_terms) . ( (count($terms) > 2) ? '<span title="' . addslashes( implode(", ", $terms) ) .'"> + ' . (count($terms) - 2) . ' more</span>' : '' ),
                        );
                    }
                }
            }
            if ( get_option('propertyhive_applicant_locations_type') != 'text' )
            {
                if ( isset($applicant_profile['locations']) && is_array($applicant_profile['locations']) && !empty($applicant_profile['locations']) )
                {
                    $terms = get_terms('location', array('hide_empty' => false, 'fields' => 'names', 'include' => $applicant_profile['locations']));
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                    {
                        $sliced_terms = array_slice( $terms, 0, 2 );

                        $requirements[] = array(
                            'label' => __( 'Locations', 'propertyhive' ),
                            'value' => implode(", ", $sliced_terms) . ( (count($terms) > 2) ? ' <span title="' . addslashes( implode(", ", $terms) ) .'">+ ' . (count($terms) - 2) . ' more</span>' : '' ),
                        );
                    }
                }
            }
            else
            {
                if ( isset($applicant_profile['location_text']) && trim($applicant_profile['location_text']) != '' )
                {
                    $location_value = trim($applicant_profile['location_text']);

                    if ( isset($applicant_profile['location_radius']) && $applicant_profile['location_radius'] != '' )
                    {
                        $location_value .= ' (Within '. $applicant_profile['location_radius'] .' Miles)';
                    }

                    $requirements[] = array(
                        'label' => __( 'Location', 'propertyhive' ),
                        'value' => $location_value,
                    );
                }
            }

            $requirements = apply_filters( 'propertyhive_applicant_requirements_display', $requirements, $contact_id, $applicant_profile );

            if ( !empty($requirements) )
            {
                foreach ( $requirements as $requirement )
                {
                    echo '<div style="display:inline-block; width:23%; margin-right:2%; vertical-align:top">
                        <strong>' . $requirement['label'] . ':</strong><br>
                        ' . $requirement['value'] . '
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

            echo '</div>';
		?>

		<?php
			if ( !empty($properties) )
			{
                $select_all_actions = array(
                    'email' => __( 'Email', 'propertyhive' ),
                    'not_interested' => __( 'Not Suitable', 'propertyhive' )
                );

                $select_all_actions = apply_filters( 'propertyhive_matching_select_all_actions', $select_all_actions );
        ?>
        <div class="select-actions" style="padding-top:15px">
            <span style="display:inline-block; vertical-align:middle;"><?php echo __( 'Select', 'propertyhive' ); ?>:</span> <?php
                foreach ( $select_all_actions as $key => $value )
                {
                    echo '<a href="javascript:;" class="button" id="select_all_' . esc_attr(sanitize_title($key)) . '" style="display:inline-block; vertical-align:middle;">All - ' . $value . '</a> ';
                }
            ?>
            <a href="javascript:;" class="button" id="select_none" style="display:inline-block; vertical-align:middle;">None</a>
        </div>

        <?php
				foreach ( $properties as $property )
				{
					$previously_sent = array();
					if ( isset($applicant_profile_match_history[$property->id]) && is_array($applicant_profile_match_history[$property->id]) && !empty($applicant_profile_match_history[$property->id]) )
					{
						$previously_sent = $applicant_profile_match_history[$property->id];
					}

                    $on_market_change_date = $property->_on_market_change_date;
                    $price_change_date = $property->_price_change_date;

					echo '<div id="matching_applicant_' . $contact_id . '_property_' . $property->id . '" style="padding:20px 0; border-bottom:1px solid #CCC;">';
                    
                        echo '<div style="float:left; width:18%;">';

                        $image_url = $property->get_main_photo_src();
                        if ( $image_url !== FALSE )
                        {
                            echo '<a href="' . get_edit_post_link( $property->id ) . '" target="_blank"><img src="' . $image_url . '" style="max-width:100%; margin:0 auto; display:block;" alt="' . addslashes($property->get_formatted_summary_address()) . '"></a>';
                        }

                        echo '</div>';
                        
                        echo '<div style="float:right; width:80%;">';
                            
                            echo '<h3 style="margin:0; padding:0; margin-bottom:9px;"><a href="' . get_edit_post_link( $property->id ) . '" target="_blank">' . $property->get_formatted_summary_address() . '</a></h3>';

                            echo '<div style="margin-bottom:7px; font-size:15px;">
                                <strong>' . ( ($property->_department == 'residential-lettings') ? __('Rent', 'propertyhive') : __('Price', 'propertyhive') ) . ': ' . $property->price_qualifier . ' ' . $property->get_formatted_price() . '</strong>
                                | ';
                            if ($property->department != 'commercial' || ph_get_custom_department_based_on($property->department) == 'commercial')
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

                            echo '<div style="margin-bottom:7px;">' . strip_tags(get_the_excerpt($property->id)) . '</div>';

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

                            do_action( 'propertyhive_applicant_match_send_methods', $contact_id, $applicant_profile_id, $property->id );

                            echo '<label><input type="checkbox" name="not_interested_property_id[]" value="' . $property->id . '"> Property Not Suitable</label>

                            </div>';

                        echo '</div>';

                        echo '<div style="clear:both"></div>';

                    echo '</div>';
				}
			}
		?>

        <p class="submit">

        	<input name="save" class="button-primary" type="submit" value="<?php echo __( 'Continue', 'propertyhive' ); ?>" />

            <a href="<?php echo get_edit_post_link((int)$_GET['contact_id']); ?>" class="button"><?php _e( 'Cancel', 'propertyhive' ); ?></a>

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

    jQuery(document).ready(function()
    {
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

        jQuery('.select-actions a').click(function(e)
        {
            e.preventDefault();

            var id = jQuery(this).attr('id').replace("select_all_", "");

            jQuery('input[name$=\'_property_id[]\']').prop('checked', false);

            if ( id != 'select_none' )
            {
                jQuery('input[name=\'' + id + '_property_id[]\']').prop('checked', 'checked');
            }
        });
    })

</script>