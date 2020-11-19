<?php
/**
 * PropertyHive Admin Matching Applicants Class.
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Admin_Matching_Applicants' ) ) :

/**
 * PH_Admin_Matching_Applicants
 */
class PH_Admin_Matching_Applicants {

	public function output()
	{
        if ( !isset($_GET['property_id']) || (isset($_GET['property_id']) && get_post_type((int)$_GET['property_id']) != 'property') )
        {
            die('Invalid property_id passed');
        }

        $property_id = (int)$_GET['property_id'];

        $property = new PH_Property($property_id);

		if ( isset($_POST['step']) )
		{
			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'propertyhive-matching-applicants' ) )
	    		die( __( 'Action failed. Please refresh the page and retry.', 'propertyhive' ) );

			switch ( $_POST['step'] )
			{
				case "one":
				{
					// Properties have been selected to email or dismiss

					// Handle dismissed properties
					$this->dismiss_properties();

                    $nothing_to_send = true;

					// Handle properties to email
					if ( isset($_POST['email_contact_applicant_profile_id']) && !empty($_POST['email_contact_applicant_profile_id']) )
					{
                        $nothing_to_send = false;

                        $subject = get_option( 'propertyhive_property_match_default_email_subject', '' );
                        $body = get_option( 'propertyhive_property_match_default_email_body', '' );
					}

                    $nothing_to_send = apply_filters( 'propertyhive_applicant_match_nothing_to_send', $nothing_to_send );

                    if ( $nothing_to_send != true )
                    {
?>
<div class="wrap propertyhive">

    <div id="poststuff">

        <form method="post" id="mainform" action="" enctype="multipart/form-data">
<?php
            if ( isset($_POST['email_contact_applicant_profile_id']) && !empty($_POST['email_contact_applicant_profile_id']) )
            {
                // We've got emails to send
                include 'views/html-admin-matching-applicants-email.php';
            }

            do_action( 'propertyhive_applicant_match_step_two', $property_id );
?>
            <p class="submit">

                <input name="save" class="button-primary" type="submit" value="<?php echo __( 'Send Matches', 'propertyhive' ); ?>" />
                <?php if ( isset($_POST['email_contact_applicant_profile_id']) && !empty($_POST['email_contact_applicant_profile_id']) ) { ?>
                <input name="preview" id="preview_email" class="button" type="button" value="<?php echo __( 'Preview Email', 'propertyhive' ); ?>" />
                <?php } ?>

                <input type="hidden" name="step" value="two" />
                <input type="hidden" name="email_contact_applicant_profile_id" value="<?php echo ( isset($_POST['email_contact_applicant_profile_id']) && is_array($_POST['email_contact_applicant_profile_id']) && !empty($_POST['email_contact_applicant_profile_id']) ) ? implode(",", ph_clean($_POST['email_contact_applicant_profile_id'])) : ''; ?>" />
                <?php do_action( 'propertyhive_applicant_match_step_two_hidden_fields' ); ?>
                <?php wp_nonce_field( 'propertyhive-matching-applicants' ); ?>

            </p>

            <p>
            <?php echo __( 'When sending out lots of emails we recommend using <a href="https://en-gb.wordpress.org/plugins/tags/smtp" target="_blank">a plugin</a> to send them out using SMTP. Your web developer or hosting company should be able to advise on this.', 'propertyhive' );
            ?>
            </p>

        </form>

    </div>

</div>

<script>

    jQuery(document).ready(function()
    {
        jQuery('#preview_email').click(function(e)
        {
            e.preventDefault();

            showPreview();
        });
    });

    function showPreview()
    {
        jQuery('#mainform').attr('target', '_blank');
        jQuery('#mainform').attr('action', '<?php echo admin_url( '?preview_propertyhive_email=true&property_id=' . (int)$_GET['property_id']); ?>');

        jQuery('#mainform').submit();
        jQuery('#mainform').attr('target', '_self');
        jQuery('#mainform').attr('action', '');
    }

</script>
<?php
                    }

					if ( $nothing_to_send == true )
                    {
                        echo '<script>window.location.href = "' . get_edit_post_link( $property_id, 'url' ) . '&ph_message=2";</script>';

						//header("Location: " . get_edit_post_link( $contact_id, 'url' ) . '&ph_message=2' ); // properties marked as not interested
                        //die();
					}

					break;
				}
				case "two":
				{
                    if ( isset($_POST['email_contact_applicant_profile_id']) && !empty($_POST['email_contact_applicant_profile_id']) )
                    {
                        $email_contact_applicant_profile_id = explode(",", $_POST['email_contact_applicant_profile_id']);

                        foreach ( $email_contact_applicant_profile_id as $contact_applicant_profile_id )
                        {
                            $explode_contact_applicant_profile_id = explode("|", $contact_applicant_profile_id);
                            $contact_id = $explode_contact_applicant_profile_id[0];
                            $applicant_profile_id = $explode_contact_applicant_profile_id[1];

                            $email_address = get_post_meta( (int)$contact_id, '_email_address', TRUE );
                            
                            $to_email_addresses = explode(",", $email_address);
                            $new_to_email_addresses = array();
                            foreach ( $to_email_addresses as $to_email_address)
                            {
                                $new_to_email_addresses[] = sanitize_email($to_email_address);
                            }

                            $cc_email_addresses = explode(",", $_POST['cc_email_address']);
                            $new_cc_email_addresses = array();
                            foreach ( $cc_email_addresses as $cc_email_address )
                            {
                                $new_cc_email_addresses[] = sanitize_email($cc_email_address);
                            }

                            $bcc_email_addresses = explode(",", $_POST['bcc_email_address']);
                            $new_bcc_email_addresses = array();
                            foreach ( $bcc_email_addresses as $bcc_email_address )
                            {
                                $new_bcc_email_addresses[] = sanitize_email($bcc_email_address);
                            }

        					// Email info entered. Time to send emails
                            $this->send_emails(
                                (int)$contact_id, 
                                (int)$applicant_profile_id, 
                                array($property_id),
                                ph_clean($_POST['from_name']),
                                sanitize_email($_POST['from_email_address']),
                                ph_clean($_POST['subject']),
                                sanitize_textarea_field($_POST['body']),
                                implode(",", $new_to_email_addresses),
                                implode(",", $new_cc_email_addresses),
                                implode(",", $new_bcc_email_addresses)
                            );
                        }

                        //header("Location: " . get_edit_post_link( $contact_id, 'url' ) . '&ph_message=1' ); // email sent
                        //die();
                    }

                    do_action( 'propertyhive_applicant_match_step_send', $property_id );

                    echo '<script>window.location.href = "' . get_edit_post_link( $property_id, 'url' ) . '&ph_message=1";</script>';
				}
			}
		}
		else
		{
			$applicants = $this->get_matching_applicants( (int)$_GET['property_id'] );

            $on_market_change_date = $property->_on_market_change_date;
            $price_change_date = $property->_price_change_date;

			include 'views/html-admin-matching-applicants.php';
		}
	}

	private function dismiss_properties()
	{
		$property_id = (int)$_GET['property_id'];

		if ( isset($_POST['not_interested_contact_applicant_profile_id']) && !empty($_POST['not_interested_contact_applicant_profile_id']) )
		{
			foreach ( $_POST['not_interested_contact_applicant_profile_id'] as $contact_applicant_profile_id )
			{
                $explode_contact_applicant_profile_id = explode("|", $contact_applicant_profile_id);

                $contact_id = $explode_contact_applicant_profile_id[0];
                $applicant_profile_id = $explode_contact_applicant_profile_id[1];

                // Get currently dismissed properties for this contact to decide if we need to add or remove it
                $dismissed_properties = get_post_meta( $contact_id, '_dismissed_properties', TRUE );

                if ( !is_array($dismissed_properties) )
                {
                    $dismissed_properties = array();
                }

				if ( in_array((int)$property_id, $dismissed_properties) )
		        {
		            // Already dismissed. Need to remove from array
		            if( ($key = array_search((int)$property_id, $dismissed_properties)) !== false ) 
		            {
		                unset($dismissed_properties[$key]);
		            }
		        }
		        else
		        {
		            // Not dismissed. Add to array
		            $dismissed_properties[] = (int)$property_id;
		        }

                $dismissed_properties = array_unique($dismissed_properties);

                update_post_meta( $contact_id, '_dismissed_properties', $dismissed_properties );
			}
		}
	}

	public function get_matching_applicants( $property_id )
	{
		global $post;

        $hot_applicants = array();
		$applicants = array();

        $property = new PH_Property((int)$property_id);

        if ( $property !== FALSE )
        {
            $property_types = array();
            $prefix = $property->department == 'commercial' ? 'commercial_' : '';
            $term_list = wp_get_post_terms($property_id, $prefix . 'property_type', array("fields" => "all"));
            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
            {
                foreach ( $term_list as $term )
                {
                    $property_types[] = $term->term_id;

                    if ( $term->parent != 0 )
                    {
                        $parent = get_term_by( 'id', $term->parent , $prefix . 'property_type' );
                        $property_types[] = $parent->term_id;

                        if ( $parent->parent != 0 )
                        {
                            $parent = get_term_by( 'id', $parent->parent , $prefix . 'property_type' );
                            $property_types[] = $parent->term_id;
                        }
                    }
                }
            }

            if ( get_option('propertyhive_applicant_locations_type') != 'text' )
            {
                $locations = array();
                $term_list = wp_get_post_terms($property_id, 'location', array("fields" => "all"));
                if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
                {
                    foreach ( $term_list as $term )
                    {
                        $locations[] = $term->term_id;

                        if ( $term->parent != 0 )
                        {
                            $parent = get_term_by( 'id', $term->parent , 'location' );
                            $locations[] = $parent->term_id;

                            if ( $parent->parent != 0 )
                            {
                                $parent = get_term_by( 'id', $parent->parent , 'location' );
                                $locations[] = $parent->term_id;
                            }
                        }
                    }
                }
            }

            $floor_area_from = $property->floor_area_from_sqft;
            if ( $floor_area_from === '' )
            {
                $floor_area_from = 0;
            }
            $floor_area_to = $property->floor_area_to_sqft;
            if ( $floor_area_to === '' )
            {
                $floor_area_to = 99999999999;
            }

            $percentage_lower = get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' );
            $percentage_higher = get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' );

            $args = array(
                'post_type' => 'contact',
                'nopaging' => true,
            );

            // Meta query
            $meta_query = array();
            $meta_query[] = array(
                'key' => '_contact_types',
                'value' => 'applicant',
                'compare' => 'LIKE'
            );

            $args['meta_query'] = $meta_query;

            $contacts_query = new WP_Query( $args );

            if ( $contacts_query->have_posts() )
            {
                while ( $contacts_query->have_posts() )
                {
                    $contacts_query->the_post();

                    $contact = new PH_Contact(get_the_ID());

                    $dismissed_properties = get_post_meta( get_the_ID(), '_dismissed_properties', TRUE );

                    if ( is_array($dismissed_properties) && in_array($property_id, $dismissed_properties) )
                    {
                        // This property is dismissed
                    }
                    else
                    {
                        $num_applicant_profiles = get_post_meta( get_the_ID(), '_applicant_profiles', TRUE );

                        for ( $i = 0; $i < $num_applicant_profiles; ++$i )
                        {
                            $applicant_profile = get_post_meta( get_the_ID(), '_applicant_profile_' . $i, TRUE );

                            if ( isset($applicant_profile['send_matching_properties']) && $applicant_profile['send_matching_properties'] == 'yes' )
                            {
                                $elements_checked = 0;
                                $matching_elements = 0;

                                ++$elements_checked;
                                if ( $applicant_profile['department'] != $property->department )
                                {
                                    
                                }
                                else
                                {
                                    ++$matching_elements;

                                    if ( $property->department != 'commercial' )
                                    {
                                        if ( $property->department == 'residential-sales' )
                                        {
                                            if ( $percentage_lower != '' && $percentage_higher != '' )
                                            {
                                                $match_price_range_lower = '';
                                                if ( !isset($applicant_profile['match_price_range_lower_actual']) || ( isset($applicant_profile['match_price_range_lower_actual']) && $applicant_profile['match_price_range_lower_actual'] == '' ) )
                                                {
                                                    if ( isset($applicant_profile['max_price_actual']) && $applicant_profile['max_price_actual'] != '' )
                                                    {
                                                        if ( $percentage_lower != '' )
                                                        {
                                                            $match_price_range_lower = $applicant_profile['max_price_actual'] - ( $applicant_profile['max_price_actual'] * ( $percentage_lower / 100 ) );
                                                        }
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
                                                        if ( $percentage_higher != '' )
                                                        {
                                                            $match_price_range_higher = $applicant_profile['max_price_actual'] + ( $applicant_profile['max_price_actual'] * ( $percentage_higher / 100 ) );
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    $match_price_range_higher = $applicant_profile['match_price_range_higher_actual'];
                                                }

                                                if ( 
                                                    ( $match_price_range_lower == '' && $match_price_range_higher == '' ) ||
                                                    (
                                                        $property->_price_actual >= $match_price_range_lower &&
                                                        $property->_price_actual <= $match_price_range_higher
                                                    )
                                                )
                                                {
                                                    ++$matching_elements;
                                                }
                                                ++$elements_checked;
                                            }
                                            else
                                            {
                                                if ( 
                                                    $applicant_profile['max_price_actual'] == '' ||
                                                    $property->_price_actual <= $applicant_profile['max_price_actual']
                                                )
                                                {
                                                    ++$matching_elements;
                                                }
                                                ++$elements_checked;
                                            }
                                        }
                                        else
                                        {
                                            if ( 
                                                $applicant_profile['max_price_actual'] == '' ||
                                                $property->_price_actual <= $applicant_profile['max_price_actual']
                                            )
                                            {
                                                ++$matching_elements;
                                            }
                                            ++$elements_checked;
                                        }

                                        if ( 
                                            $applicant_profile['min_beds'] == '' ||
                                            $property->_bedrooms >= $applicant_profile['min_beds']
                                        )
                                        {
                                            ++$matching_elements;
                                        }
                                        ++$elements_checked;
                                    }
                                    else
                                    {
                                        if ( $applicant_profile['min_floor_area'] === '' )
                                        {
                                            $applicant_profile['min_floor_area'] = 0;
                                        }
                                        if ( $applicant_profile['max_floor_area'] === '' )
                                        {
                                            $applicant_profile['max_floor_area'] = 99999999999;
                                        }
                                        
                                        if ( 
                                            $applicant_profile['min_floor_area'] <= $floor_area_to &&
                                            $applicant_profile['max_floor_area'] >= $floor_area_from
                                        )
                                        {
                                            ++$matching_elements;
                                        }
                                        ++$elements_checked;
                                    }

                                    if ( 
                                        !isset($applicant_profile[$prefix . 'property_types']) ||
                                        ( isset($applicant_profile[$prefix . 'property_types']) && empty($applicant_profile[$prefix . 'property_types']) )
                                    )
                                    {
                                        ++$matching_elements;
                                    }
                                    elseif ( isset($applicant_profile[$prefix . 'property_types']) && !empty($applicant_profile[$prefix . 'property_types']) )
                                    {
                                        foreach ( $applicant_profile[$prefix . 'property_types'] as $applicant_property_type )
                                        {
                                            if ( in_array($applicant_property_type, $property_types) )
                                            {
                                                ++$matching_elements;
                                                break;
                                            }
                                        }
                                    }
                                    ++$elements_checked;

                                    if ( get_option('propertyhive_applicant_locations_type') != 'text' )
                                    {
                                        if (
                                            !isset($applicant_profile['locations']) ||
                                            ( isset($applicant_profile['locations']) && empty($applicant_profile['locations']) )
                                        )
                                        {
                                            ++$matching_elements;
                                        }
                                        elseif ( isset($applicant_profile['locations']) && !empty($applicant_profile['locations']) )
                                        {
                                            foreach ( $applicant_profile['locations'] as $applicant_location )
                                            {
                                                if ( in_array($applicant_location, $locations) )
                                                {
                                                    ++$matching_elements;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        if ( !isset($applicant_profile['location_text']) || trim($applicant_profile['location_text']) == '' )
                                        {
                                            ++$matching_elements;
                                        }
                                        else
                                        {
                                            $location_matched = false;
                                            $address_keywords = array( $applicant_profile['location_text'] );
                                            if ( strpos( $applicant_profile['location_text'], ' ' ) !== FALSE )
                                            {
                                                $address_keywords[] = str_replace(" ", "-", ph_clean($applicant_profile['location_text']));
                                            }
                                            if ( strpos( $applicant_profile['location_text'], '-' ) !== FALSE )
                                            {
                                                $address_keywords[] = str_replace("-", " ", ph_clean($applicant_profile['location_text']));
                                            }

                                            if ( strpos( $applicant_profile['location_text'], '.' ) !== FALSE )
                                            {
                                                $address_keywords[] = str_replace(".", "", ph_clean($applicant_profile['location_text']));
                                            }
                                            if ( stripos( $applicant_profile['location_text'], 'st ' ) !== FALSE )
                                            {
                                                $address_keywords[] = str_ireplace("st ", "st. ", ph_clean($applicant_profile['location_text']));
                                            }

                                            $location_address_fields = array(
                                                '_address_street',
                                                '_address_two',
                                                '_address_three',
                                                '_address_four',
                                                '_address_postcode'
                                            );
                                            $location_address_fields = apply_filters( 'propertyhive_address_fields_to_query', $location_address_fields );

                                            foreach ( $address_keywords as $address_keyword )
                                            {
                                                foreach ( $location_address_fields as $address_field )
                                                {
                                                    if ( $address_field == '_address_postcode' ) { continue; } // ignore postcode as that is handled differently afterwards

                                                    if (
                                                        ( get_option( 'propertyhive_address_keyword_compare', '=' ) == '=' && strcasecmp($address_keyword, $property->{$address_field}) == 0 )
                                                        ||
                                                        ( get_option( 'propertyhive_address_keyword_compare', '=' ) == 'LIKE' && stripos($property->{$address_field}, $address_keyword) !== false )
                                                    )
                                                    {
                                                        ++$matching_elements;
                                                        $location_matched = true;
                                                        break 2;
                                                    }
                                                }
                                            }
                                            if ( !$location_matched && in_array('_address_postcode', $location_address_fields) )
                                            {
                                                if ( strlen($applicant_profile['location_text']) <= 4 )
                                                {
                                                    // if location is just the first part of postcode, check if it is present at the start of the postcode
                                                    if ( strcasecmp($address_keyword, substr($property->_address_postcode, 0, strlen($address_keyword))) == 0 )
                                                    {
                                                        ++$matching_elements;
                                                    }
                                                }
                                                else
                                                {
                                                    $postcode = ph_clean( $applicant_profile['location_text'] );
                                                    $postcodes = [strtolower($postcode)];

                                                    if ( preg_match('#^(GIR ?0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]([0-9ABEHMNPRV-Y])?)|[0-9][A-HJKPS-UW])[0-9][ABD-HJLNP-UW-Z]{2})$#i', $postcode) )
                                                    {
                                                        // UK postcode found with no space

                                                        if ( strlen($postcode) == 5 )
                                                        {
                                                            $first_part = substr($postcode, 0, 2);
                                                            $last_part = substr($postcode, 2, 3);

                                                            $postcodes[] = strtolower($first_part . ' ' . $last_part);
                                                        }
                                                        elseif ( strlen($postcode) == 6 )
                                                        {
                                                            $first_part = substr($postcode, 0, 3);
                                                            $last_part = substr($postcode, 3, 3);

                                                            $postcodes[] = strtolower($first_part . ' ' . $last_part);
                                                        }
                                                        elseif ( strlen($postcode) == 7 )
                                                        {
                                                            $first_part = substr($postcode, 0, 4);
                                                            $last_part = substr($postcode, 4, 3);

                                                            $postcodes[] = strtolower($first_part . ' ' . $last_part);
                                                        }
                                                    }

                                                    if ( in_array(strtolower($property->_address_postcode), $postcodes) )
                                                    {
                                                        ++$matching_elements;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    ++$elements_checked;
                                }

                                $additional_checks = apply_filters( 'propertyhive_matching_applicants_check', true, $property, get_the_ID(), $applicant_profile );

                                if ( $additional_checks === true && $matching_elements == $elements_checked )
                                {  
                                    $applicant_profile['applicant_profile_id'] = $i;

                                    // Matched all criteria
                                    if ( isset($applicant_profile['grading']) && $applicant_profile['grading'] == 'hot' )
                                    {
                                        $hot_applicants[] = array(
                                            'contact_id' => get_the_ID(),
                                            'applicant_profile' => $applicant_profile,
                                        );
                                    }
                                    else
                                    {
                                        $applicants[] = array(
                                            'contact_id' => get_the_ID(),
                                            'applicant_profile' => $applicant_profile,
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }

            wp_reset_postdata();
        }

        return array_merge($hot_applicants, $applicants);
	}

    public function send_emails( $contact_id, $applicant_profile, $email_property_ids, $from_name, $from_email_address, $subject, $body, $to_email_address = '', $cc_email_address = '', $bcc_email_address = '' )
    {
        global $wpdb;

        $current_user = wp_get_current_user();

        if ( $to_email_address == '' )
        {
            $to_email_address = get_post_meta( $contact_id, '_email_address', TRUE );
        }

        $subject = str_replace("[property_count]", count($email_property_ids) . ' propert' . ( ( count($email_property_ids) != 1 ) ? 'ies' : 'y' ), $subject);

        $body = str_replace("[contact_name]", get_the_title($contact_id), $body);
        $body = str_replace("[property_count]", count($email_property_ids) . ' propert' . ( ( count($email_property_ids) != 1 ) ? 'ies' : 'y' ), $body);

        if ( strpos($body, '[properties]') !== FALSE )
        {
            ob_start();
            if ( !empty($email_property_ids) )
            {
                foreach ( $email_property_ids as $email_property_id )
                {

                    $property = new PH_Property((int)$email_property_id);
                    ph_get_template( 'emails/applicant-match-property.php', array( 'property' => $property ) );
                }
            }
            $body = str_replace("[properties]", ob_get_clean(), $body);
        }

        // Insert into email log
        $insert = $wpdb->insert( 
            $wpdb->prefix . 'ph_email_log', 
            array( 
                'contact_id' => $contact_id,
                'property_ids' => serialize($email_property_ids),
                'applicant_profile_id' => $applicant_profile,
                'to_email_address' => $to_email_address,
                'cc_email_address' => $cc_email_address,
                'bcc_email_address' => $bcc_email_address,
                'from_name' => $from_name,
                'from_email_address' => $from_email_address,
                'subject' => stripslashes($subject),
                'body' => stripslashes($body),
                'status' => '',
                'send_at' => date("Y-m-d H:i:s"),
                'sent_by' => $current_user->ID,
            ), 
            array( 
                '%d',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
            ) 
        );

        if ( $insert !== FALSE )
        {
            $email_log_id = $wpdb->insert_id;

            // Insert properties into applicant match history
            $applicant_profile_match_history = get_post_meta( $contact_id, '_applicant_profile_' . $applicant_profile . '_match_history', TRUE );
            if ( !is_array($applicant_profile_match_history) )
            {
                $applicant_profile_match_history = array();
            }

            if ( is_array($email_property_ids) && !empty($email_property_ids) )
            {
                foreach ( $email_property_ids as $email_property_id )
                {
                    if ( !isset($applicant_profile_match_history[$email_property_id]) )
                    {
                        $applicant_profile_match_history[$email_property_id] = array();
                    }

                    $applicant_profile_match_history[$email_property_id][] = array(
                        'date' => date("Y-m-d H:i:s"),
                        'method' => 'email',
                        'email_log_id' => $email_log_id,
                    );

                    // Add note/comment to property
                    $comment = array(
                        'note_type' => 'mailout',
                        'method' => 'email',
                        'email_log_id' => $email_log_id
                    );

                    $data = array(
                        'comment_post_ID'      => $email_property_id,
                        'comment_author'       => $current_user->display_name,
                        'comment_author_email' => 'propertyhive@noreply.com',
                        'comment_author_url'   => '',
                        'comment_date'         => date("Y-m-d H:i:s"),
                        'comment_content'      => serialize($comment),
                        'comment_approved'     => 1,
                        'comment_type'         => 'propertyhive_note',
                    );
                    wp_insert_comment( $data );

                }

                update_post_meta( $contact_id, '_applicant_profile_' . $applicant_profile . '_match_history', $applicant_profile_match_history );

                // Add note/comment to contact
                $comment = array(
                    'note_type' => 'mailout',
                    'method' => 'email',
                    'email_log_id' => $email_log_id
                );

                $data = array(
                    'comment_post_ID'      => $contact_id,
                    'comment_author'       => $current_user->display_name,
                    'comment_author_email' => 'propertyhive@noreply.com',
                    'comment_author_url'   => '',
                    'comment_date'         => date("Y-m-d H:i:s"),
                    'comment_content'      => serialize($comment),
                    'comment_approved'     => 1,
                    'comment_type'         => 'propertyhive_note',
                );
                wp_insert_comment( $data );
            }
        }
    }

}

endif;