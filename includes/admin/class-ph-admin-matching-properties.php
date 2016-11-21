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
        if ( !isset($_GET['contact_id']) || (isset($_GET['contact_id']) && get_post_type($_GET['contact_id']) != 'contact') )
        {
            die('Invalid contact_id passed');
        }
        if ( !isset($_GET['applicant_profile']) )
        {
            die('Invalid applicant_profile passed');
        }

        $contact_id = $_GET['contact_id'];

        $email_address = get_post_meta( $contact_id, '_email_address', TRUE );

		$applicant_profile_id = $_GET['applicant_profile'];

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

					// Handle properties to email
					if ( isset($_POST['email_property_id']) && !empty($_POST['email_property_id']) )
					{
                        $subject = get_option( 'propertyhive_property_match_default_email_subject', '' );
                        $body = get_option( 'propertyhive_property_match_default_email_body', '' );

						// We've got emails to send
						include 'views/html-admin-matching-properties-email.php';
					}
					else
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
    					// Email info entered. Time to send emails
                        $this->send_emails(
                            $_GET['contact_id'], 
                            $_GET['applicant_profile'], 
                            explode(",", $_POST['email_property_id']),
                            $_POST['from_name'],
                            $_POST['from_email_address'],
                            $_POST['subject'],
                            $_POST['body'],
                            $_POST['to_email_address']
                        );

                        echo '<script>window.location.href = "' . get_edit_post_link( $contact_id, 'url' ) . '&ph_message=1";</script>';

                        //header("Location: " . get_edit_post_link( $contact_id, 'url' ) . '&ph_message=1' ); // email sent
                        //die();
                    }
				}
			}
		}
		else
		{
			$applicant_profile_match_history = get_post_meta( $contact_id, '_applicant_profile_' . $applicant_profile_id . '_match_history', TRUE );

			$properties = $this->get_matching_properties( $_GET['contact_id'], $_GET['applicant_profile'] );

            $do_not_email = false;
            $forbidden_contact_methods = get_post_meta( $_GET['contact_id'], '_forbidden_contact_methods', TRUE );
            if ( is_array($forbidden_contact_methods) && in_array('email', $forbidden_contact_methods) )
            {
                $do_not_email = true;
            }

			include 'views/html-admin-matching-properties.php';
		}
	}

	private function dismiss_properties()
	{
		$contact_id = $_GET['contact_id'];
		$applicant_profile_id = $_GET['applicant_profile'];

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
				if ( in_array($property_id, $dismissed_properties) )
		        {
		            // Already dismissed. Need to remove from array
		            if( ($key = array_search($property_id, $dismissed_properties)) !== false ) 
		            {
		                unset($dismissed_properties[$key]);
		            }
		        }
		        else
		        {
		            // Not dismissed. Add to array
		            $dismissed_properties[] = $property_id;
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
            if ( isset($applicant_profile['locations']) && is_array($applicant_profile['locations']) && !empty($applicant_profile['locations']) )
            {
                $tax_query[] = array(
                    'taxonomy' => 'location',
                    'field'    => 'term_id',
                    'terms'    => $applicant_profile['locations'],
                    'operator' => 'IN',
                );
            }
            $args['tax_query'] = $tax_query;

            $properties_query = new WP_Query( $args );

            if ( $properties_query->have_posts() )
            {
                //echo '<h2>' . $properties_query->found_posts . ' matching propert' . ( ( $properties_query->found_posts != 1 ) ? 'ies' : 'y') . ' found</h2>';

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

    public function send_emails( $contact_id, $applicant_profile, $email_property_ids, $from_name, $from_email_address, $subject, $body, $to_email_address = '' )
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