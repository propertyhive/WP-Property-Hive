<?php
/**
 * PropertyHive Admin Matching Properties Class.
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Admin_Matching_Properties' ) ) :

/**
 * PH_Admin_Matching_Properties
 */
class PH_Admin_Matching_Properties {

	public function output()
	{
        if ( !isset($_GET['contact_id']) || (isset($_GET['contact_id']) && get_post_type((int)$_GET['contact_id']) != 'contact') )
        {
            die('Invalid contact_id passed');
        }
        if ( !isset($_GET['applicant_profile']) )
        {
            die('Invalid applicant_profile passed');
        }

        $contact_id = (int)$_GET['contact_id'];

        $email_address = get_post_meta( $contact_id, '_email_address', TRUE );

		$applicant_profile_id = (int)$_GET['applicant_profile'];

		$applicant_profile = get_post_meta( $contact_id, '_applicant_profile_' . $applicant_profile_id, TRUE );

		if ( isset($_POST['step']) )
		{
			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'propertyhive-matching-properties' ) )
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
					if ( isset($_POST['email_property_id']) && !empty($_POST['email_property_id']) )
					{
                        $nothing_to_send = false;

                        $subject = get_option( 'propertyhive_property_match_default_email_subject', '' );
                        $body = get_option( 'propertyhive_property_match_default_email_body', '' );

                        $from_email_option = get_option( 'propertyhive_property_match_default_from', '' );
                        if( $from_email_option == 'default_from_email' )
                        {
                            $from_email_address = get_option('propertyhive_email_from_address', '');
                        }
                        else
                        {
                            $current_user = wp_get_current_user();
                            $from_email_address = $current_user->user_email;
                        }
					}

                    $nothing_to_send = apply_filters( 'propertyhive_property_match_nothing_to_send', $nothing_to_send );

                    if ( $nothing_to_send != true )
                    {
?>
<div class="wrap propertyhive">

    <div id="poststuff">

        <form method="post" id="mainform" action="" enctype="multipart/form-data">
<?php
            if ( isset($_POST['email_property_id']) && !empty($_POST['email_property_id']) )
            {
                // We've got emails to send
                include 'views/html-admin-matching-properties-email.php';
            }

            do_action( 'propertyhive_property_match_step_two', $contact_id, $applicant_profile_id );
?>
            <p class="submit">

                <input name="save" class="button-primary" type="submit" value="<?php echo __( 'Send Matches', 'propertyhive' ); ?>" />
                <?php if ( isset($_POST['email_property_id']) && !empty($_POST['email_property_id']) ) { ?>
                <input name="preview" id="preview_email" class="button" type="button" value="<?php echo __( 'Preview Email', 'propertyhive' ); ?>" />
                <?php } ?>

                <input type="hidden" name="step" value="two" />
                <input type="hidden" name="email_property_id" value="<?php echo ( isset($_POST['email_property_id']) && is_array($_POST['email_property_id']) && !empty($_POST['email_property_id']) ) ? implode(",", ph_clean($_POST['email_property_id'])) : ''; ?>" />
                <?php do_action( 'propertyhive_property_match_step_two_hidden_fields' ); ?>
                <?php wp_nonce_field( 'propertyhive-matching-properties' ); ?>

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
        jQuery('#mainform').attr('action', '<?php echo admin_url( '?preview_propertyhive_email=true&contact_id=' . (int)$_GET['contact_id'] . '&applicant_profile=' . (int)$_GET['applicant_profile'] ); ?>');

        jQuery('#mainform').submit();
        jQuery('#mainform').attr('target', '_self');
        jQuery('#mainform').attr('action', '');
    }

</script>
<?php
                    }

					if ( $nothing_to_send == true )
                    {
                        echo '<script>window.location.href = "' . get_edit_post_link( $contact_id, 'url' ) . '&ph_message=2";</script>';

						//header("Location: " . get_edit_post_link( $contact_id, 'url' ) . '&ph_message=2' ); // properties marked as not interested
                        //die();
					}

					break;
				}
				case "two":
				{
                    if ( isset($_POST['email_property_id']) && !empty($_POST['email_property_id']) )
                    {
                        $to_email_addresses = explode(",", $_POST['to_email_address']);
                        $new_to_email_addresses = array();
                        foreach ( $to_email_addresses as $to_email_address )
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
                            (int)$_GET['contact_id'], 
                            (int)$_GET['applicant_profile'], 
                            explode(",", ph_clean($_POST['email_property_id'])),
                            ph_clean($_POST['from_name']),
                            sanitize_email($_POST['from_email_address']),
                            ph_clean($_POST['subject']),
                            sanitize_textarea_field($_POST['body']),
                            implode(",", $new_to_email_addresses),
                            implode(",", $new_cc_email_addresses),
                            implode(",", $new_bcc_email_addresses)
                        );

                        //header("Location: " . get_edit_post_link( $contact_id, 'url' ) . '&ph_message=1' ); // email sent
                        //die();
                    }

                    do_action( 'propertyhive_property_match_step_send', $contact_id, $applicant_profile_id );

                    echo '<script>window.location.href = "' . get_edit_post_link( $contact_id, 'url' ) . '&ph_message=1";</script>';
				}
			}
		}
		else
		{
			$applicant_profile_match_history = get_post_meta( $contact_id, '_applicant_profile_' . $applicant_profile_id . '_match_history', TRUE );

			$properties = $this->get_matching_properties( (int)$_GET['contact_id'], (int)$_GET['applicant_profile'] );

            $do_not_email = false;
            $forbidden_contact_methods = get_post_meta( (int)$_GET['contact_id'], '_forbidden_contact_methods', TRUE );
            if ( is_array($forbidden_contact_methods) && in_array('email', $forbidden_contact_methods) )
            {
                $do_not_email = true;
            }

			include 'views/html-admin-matching-properties.php';
		}
	}

	private function dismiss_properties()
	{
		$contact_id = (int)$_GET['contact_id'];
		$applicant_profile_id = (int)$_GET['applicant_profile'];

		// Get currently dismissed properties for this contact to decide if we need to add or remove it
        $dismissed_properties = get_post_meta( $contact_id, '_dismissed_properties', TRUE );

        if ( !is_array($dismissed_properties) )
        {
            $dismissed_properties = array();
        }

		if ( isset($_POST['not_interested_property_id']) && !empty($_POST['not_interested_property_id']) )
		{
			foreach ( $_POST['not_interested_property_id'] as $property_id )
			{
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
			}

            $dismissed_properties = array_unique($dismissed_properties);

            update_post_meta( $contact_id, '_dismissed_properties', $dismissed_properties );
		}
	}

	public function get_matching_properties( $contact_id, $applicant_profile_id, $date_added_from = '' )
	{
		global $post;

		$properties = array();

        $contact = get_post($contact_id);

        if ( !is_null( $contact ) )
        {
            $percentage_lower = get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' );
            $percentage_higher = get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' );

            $dismissed_properties = get_post_meta( $contact_id, '_dismissed_properties', TRUE );
            if ( !is_array($dismissed_properties) )
            {
                $dismissed_properties = array();
            }

            $applicant_profile = get_post_meta( $contact_id, '_applicant_profile_' . $applicant_profile_id, TRUE );

            $args = array(
                'post_type' => 'property',
                'nopaging' => true,
                'post__not_in' => $dismissed_properties
            );

            if ( $date_added_from != '' )
            {
                $args['date_query'] = array(
                    array(
                        'after' => $date_added_from,
                        'inclusive' => true,
                    )
                );
            }

            // Meta query
            $meta_query = array('relation' => 'AND');
            $meta_query[] = array(
                'key' => '_on_market',
                'value' => 'yes'
            );
            if ( isset($applicant_profile['department']) && $applicant_profile['department'] == 'residential-sales' )
            {
                $meta_query[] = array(
                    'key' => '_department',
                    'value' => $applicant_profile['department']
                );
                
                if (
                    get_option( 'propertyhive_applicant_match_price_range_percentage_lower', '' ) != '' &&
                    get_option( 'propertyhive_applicant_match_price_range_percentage_higher', '' ) != ''
                )
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

                    if ( $match_price_range_lower != '' && $match_price_range_higher != '' )
                    {
                        $meta_query[] = array(
                            'key' => '_price_actual',
                            'value' => array($match_price_range_lower, $match_price_range_higher),
                            'compare' => 'BETWEEN',
                            'type' => 'NUMERIC'
                        );
                    }
                }
                else
                {
                    $meta_query[] = array(
                        'key' => '_price_actual',
                        'value' => $applicant_profile['max_price_actual'],
                        'compare' => '<=',
                        'type' => 'NUMERIC'
                    );
                }
            }
            elseif ( isset($applicant_profile['department']) && $applicant_profile['department'] == 'residential-lettings' )
            {
                $meta_query[] = array(
                    'key' => '_department',
                    'value' => $applicant_profile['department']
                );
                if ( isset($applicant_profile['max_price_actual']) && $applicant_profile['max_price_actual'] != '' && $applicant_profile['max_price_actual'] != 0 )
                {
                    $meta_query[] = array(
                        'key' => '_price_actual',
                        'value' => $applicant_profile['max_price_actual'],
                        'compare' => '<=',
                        'type' => 'NUMERIC'
                    );
                }
            }

            if ( isset($applicant_profile['department']) && ( $applicant_profile['department'] == 'residential-sales' || $applicant_profile['department'] == 'residential-lettings' ) )
            {
                if ( isset($applicant_profile['min_beds']) && $applicant_profile['min_beds'] != '' && $applicant_profile['min_beds'] != 0 )
                {
                    $meta_query[] = array(
                        'key' => '_bedrooms',
                        'value' => $applicant_profile['min_beds'],
                        'compare' => '>=',
                        'type' => 'NUMERIC'
                    );
                }
            }
            if ( isset($applicant_profile['department']) && $applicant_profile['department'] == 'commercial' )
            {
                if ( isset($applicant_profile['available_as']) && is_array($applicant_profile['available_as']) && !empty($applicant_profile['available_as']) )
                {
                    if ( in_array('sale', $applicant_profile['available_as']) && !in_array('rent', $applicant_profile['available_as']) )
                    {
                        $meta_query[] = array(
                            'key' => '_for_sale',
                            'value' => 'yes',
                        );
                    }
                    if ( !in_array('sale', $applicant_profile['available_as']) && in_array('rent', $applicant_profile['available_as']) )
                    {
                        $meta_query[] = array(
                            'key' => '_to_rent',
                            'value' => 'yes',
                        );
                    }
                    if ( in_array('sale', $applicant_profile['available_as']) && in_array('rent', $applicant_profile['available_as']) )
                    {
                        // Do nothing as both are ticked
                    }
                }

                if (
                    !isset($applicant_profile['min_floor_area_actual']) || 
                    ( isset($applicant_profile['min_floor_area_actual']) && $applicant_profile['min_floor_area_actual'] === '') 
                )
                {
                    $applicant_profile['min_floor_area_actual'] = 0;
                }
                if (
                    !isset($applicant_profile['max_floor_area_actual']) || 
                    ( isset($applicant_profile['max_floor_area_actual']) && $applicant_profile['max_floor_area_actual'] === '') 
                )
                {
                    $applicant_profile['max_floor_area_actual'] = 99999999999;
                }
                $meta_query[] = array(
                    'key' => '_floor_area_from_sqft',
                    'value' => $applicant_profile['max_floor_area_actual'],
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                );
                $meta_query[] = array(
                    'key' => '_floor_area_to_sqft',
                    'value' => $applicant_profile['min_floor_area_actual'],
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }

            if ( get_option('propertyhive_applicant_locations_type') == 'text' )
            {
                if ( isset($applicant_profile['location_text']) && $applicant_profile['location_text'] != '' )
                {
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

                    $location_query = array('relation' => 'OR');

                    $address_fields_to_query = array(
                        '_address_street',
                        '_address_two',
                        '_address_three',
                        '_address_four',
                        '_address_postcode'
                    );

                    $address_fields_to_query = apply_filters( 'propertyhive_address_fields_to_query', $address_fields_to_query );

                    foreach ( $address_keywords as $address_keyword )
                    {
                        foreach ( $address_fields_to_query as $address_field )
                        {
                            if ( $address_field == '_address_postcode' ) { continue; } // ignore postcode as that is handled differently afterwards

                            $location_query[] = array(
                                'key'     => $address_field,
                                'value'   => $address_keyword,
                                'compare' => get_option( 'propertyhive_address_keyword_compare', '=' )
                            );
                        }
                    }
                    if ( in_array('_address_postcode', $address_fields_to_query) )
                    {
                        if ( strlen($applicant_profile['location_text']) <= 4 )
                        {
                            $location_query[] = array(
                                'key'     => '_address_postcode',
                                'value'   => ph_clean( $applicant_profile['location_text'] ),
                                'compare' => '='
                            );
                            $location_query[] = array(
                                'key'     => '_address_postcode',
                                'value'   => '^' . ph_clean( $applicant_profile['location_text'] ) . '[ ]',
                                'compare' => 'RLIKE'
                            );
                        }
                        else
                        {
                            $postcode = ph_clean( $applicant_profile['location_text'] );

                            if ( preg_match('#^(GIR ?0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]([0-9ABEHMNPRV-Y])?)|[0-9][A-HJKPS-UW])[0-9][ABD-HJLNP-UW-Z]{2})$#i', $postcode) )
                            {
                                // UK postcode found with no space

                                if ( strlen($postcode) == 5 )
                                {
                                    $first_part = substr($postcode, 0, 2);
                                    $last_part = substr($postcode, 2, 3);

                                    $postcode = $first_part . ' ' . $last_part;
                                }
                                elseif ( strlen($postcode) == 6 )
                                {
                                    $first_part = substr($postcode, 0, 3);
                                    $last_part = substr($postcode, 3, 3);

                                    $postcode = $first_part . ' ' . $last_part;
                                }
                                elseif ( strlen($postcode) == 7 )
                                {
                                    $first_part = substr($postcode, 0, 4);
                                    $last_part = substr($postcode, 4, 3);

                                    $postcode = $first_part . ' ' . $last_part;
                                }
                            }

                            $location_query[] = array(
                                'key'     => '_address_postcode',
                                'value'   => ph_clean( $postcode ),
                                'compare' => 'LIKE'
                            );
                        }
                    }
                    $meta_query[] = $location_query;
                }
            }
            $args['meta_query'] = $meta_query;

            // Term query
            $tax_query = array('relation' => 'AND');
            if ( isset($applicant_profile['department']) && ( $applicant_profile['department'] == 'residential-sales' || $applicant_profile['department'] == 'residential-lettings' ) )
            {
                if ( isset($applicant_profile['property_types']) && is_array($applicant_profile['property_types']) && !empty($applicant_profile['property_types']) )
                {
                    $tax_query[] = array(
                        'taxonomy' => 'property_type',
                        'field'    => 'term_id',
                        'terms'    => $applicant_profile['property_types'],
                        'operator' => 'IN',
                    );
                }
            }
            if ( isset($applicant_profile['department']) && $applicant_profile['department'] == 'commercial' )
            {
                if ( isset($applicant_profile['commercial_property_types']) && is_array($applicant_profile['commercial_property_types']) && !empty($applicant_profile['commercial_property_types']) )
                {
                    $tax_query[] = array(
                        'taxonomy' => 'commercial_property_type',
                        'field'    => 'term_id',
                        'terms'    => $applicant_profile['commercial_property_types'],
                        'operator' => 'IN',
                    );
                }
            }
            if ( get_option('propertyhive_applicant_locations_type') != 'text' )
            {
                if ( isset($applicant_profile['locations']) && is_array($applicant_profile['locations']) && !empty($applicant_profile['locations']) )
                {
                    $tax_query[] = array(
                        'taxonomy' => 'location',
                        'field'    => 'term_id',
                        'terms'    => $applicant_profile['locations'],
                        'operator' => 'IN',
                    );
                }
            }
            $property_match_statuses = get_option( 'propertyhive_property_match_statuses', '' );
            if ( $property_match_statuses != '' && is_array($property_match_statuses) && !empty($property_match_statuses) )
            {
                $tax_query[] = array(
                    'taxonomy' => 'availability',
                    'field'    => 'term_id',
                    'terms'    => $property_match_statuses,
                    'operator' => 'IN',
                );
            }
            $args['tax_query'] = $tax_query;

            $args = apply_filters( 'propertyhive_matching_properties_args', $args, $contact_id, $applicant_profile );

            $properties_query = new WP_Query( $args );

            if ( $properties_query->have_posts() )
            {
                while ( $properties_query->have_posts() )
                {
                    $properties_query->the_post();

                    $property = new PH_Property($post->ID);

                    $properties[] = $property;
                }
            }
            wp_reset_postdata();
        }

        return $properties;
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