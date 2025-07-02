<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Transactional Emails Controller
 *
 * Property Hive Emails Class which handles the sending on transactional emails and email templates. This class loads in available emails.
 *
 * @class 		PH_Emails
 * @version		1.0.0
 * @package		PropertyHive/Classes/Emails
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Emails {
	
	/** @var PH_Emails The single instance of the class */
	protected static $_instance = null;

	/**
	 * Main PH_Emails Instance.
	 *
	 * Ensures only one instance of PH_Emails is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return PH_Emails Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'propertyhive' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'propertyhive' ), '1.0.0' );
	}

	/**
	 * Constructor for the email class hooks in all emails that can be sent.
	 *
	 */
	public function __construct() {
		$this->init();

		// Email Header, Footer
		add_action( 'propertyhive_email_header', array( $this, 'email_header' ), 10, 1 );
		add_action( 'propertyhive_email_footer', array( $this, 'email_footer' ), 10, 1 );

		add_action( 'propertyhive_process_email_log', array( $this, 'ph_process_email_log' ) );
		add_action( 'propertyhive_auto_email_match', array( $this, 'ph_auto_email_match' ) );

		// Send applicant registration email 
		add_action( 'propertyhive_applicant_registered', array( $this, 'send_applicant_registration_alert' ), 10, 2 );

		add_action( 'admin_init', array( $this, 'run_custom_email_cron' ), 10, 1 );

		add_action( 'init', array( $this, 'check_email_queue_is_scheduled'), 99 );
	}

	public function check_email_queue_is_scheduled()
	{
		$schedule = wp_get_schedule( 'propertyhive_process_email_log' );

        if ( $schedule === FALSE )
        {
            // Hmm... cron job not found. Let's set it up
            $timestamp = wp_next_scheduled( 'propertyhive_process_email_log' );
            wp_unschedule_event($timestamp, 'propertyhive_process_email_log' );
            wp_clear_scheduled_hook('propertyhive_process_email_log');
            
            wp_schedule_event( time(), 'every_fifteen_minutes', 'propertyhive_process_email_log' );
        }
	}

	public function run_custom_email_cron()
	{
		if (isset($_GET['custom_email_log_cron']) && in_array($_GET['custom_email_log_cron'], array('propertyhive_process_email_log', 'propertyhive_auto_email_match')) )
        {
            do_action($_GET['custom_email_log_cron']);
        }
	}

	public function send_applicant_registration_alert( $contact_post_id, $user_id )
	{
		if ( 
			get_option( 'propertyhive_new_registration_alert', '' ) == 'yes' && 
			isset($_POST['office_id']) && // in the future we should have office stored against contact and use that
			$_POST['office_id'] != '' &&
			isset($_POST['department']) && // Should really take department from contacts requirements
			$_POST['department'] != ''
		)
		{
			$to = get_post_meta( (int)$_POST['office_id'], '_office_email_address_' . str_replace("residential-", "", ph_clean($_POST['department'])), TRUE );

			if ( $to == '' )
			{
				$to = get_option( 'admin_email', '' );
			}

			$subject = sprintf( __( 'A new applicant, %s, has registered through your website', 'propertyhive' ), get_the_title($contact_post_id) );

			$message = __( "A new applicant has registered through your website. Please find details of the applicant below:", 'propertyhive' ) . "\n\n";
            
            $message = apply_filters( 'propertyhive_applicant_registration_email_pre_body', $message, $contact_post_id );

            $form_controls = ph_get_user_details_form_fields();
    
	    	$form_controls = apply_filters( 'propertyhive_user_details_form_fields', $form_controls );

	    	$form_controls_2 = ph_get_applicant_requirements_form_fields();
	    
	    	$form_controls_2 = apply_filters( 'propertyhive_applicant_requirements_form_fields', $form_controls_2, get_post_meta( $contact_post_id, '_applicant_profile_0', true ) );

	    	$form_controls = array_merge( $form_controls, $form_controls_2 );

	    	$form_controls = apply_filters( 'propertyhive_applicant_registration_form_fields', $form_controls );

            unset($form_controls['office_id']);
            
            foreach ($form_controls as $key => $control)
            {
            	if ( isset($control['type']) && $control['type'] == 'password' )
            	{
            		continue;
            	}

            	$value = ph_clean($_POST[$key]);
            	$values = array();
            	if ( !empty($value) && taxonomy_exists($key) )
            	{
            		if ( !is_array($value) ) { $value = array($value); }

            		foreach ( $value as $value_term )
            		{
	            		$term = get_term( ph_clean($value_term), $key );

	            		if ( isset($term->name) )
	            		{
	            			$values[] = $term->name;
	            		}
	            	}

	            	$value = implode(", ", $values);
            	}

            	if ( ph_clean($value) == '' )
            	{
            		continue;
            	}

                $label = ( isset($control['label']) ) ? $control['label'] : $key;
                $message .= $label . ": " . $value . "\n";
            }

			wp_mail($to, $subject, $message);
		}
	}

	/**
	 * Process ph_email_log table. Handle failed, hung and pending emails
	 */
	public function ph_process_email_log()
	{
		global $wpdb;

		$lock_id = uniqid( "", true );

		$wpdb->query("
		    UPDATE " . $wpdb->prefix . "ph_email_log
		    SET 
				status = 'fail2',
				lock_id = ''
		    WHERE 
		    	status = 'fail1'
		    AND
		    	lock_id <> '' 
		    AND
		    	locked_at <= '" . date("Y-m-d H:i:s", strtotime('24 hours ago')) . "'
		");

		$wpdb->query("
		    UPDATE " . $wpdb->prefix . "ph_email_log
		    SET 
				status = 'fail1',
				lock_id = ''
		    WHERE 
		    	status = ''
		    AND
		    	lock_id <> '' 
		    AND
		    	locked_at <= '" . date("Y-m-d H:i:s", strtotime('24 hours ago')) . "'
		");
		
		// Lock/reserve all emails in log that are status blank or 'fail1' and lock_id blank and send_at in the past
		// Only grab 25 at a time to prevent hanging/being seen as spamming
		$wpdb->query("
		    UPDATE " . $wpdb->prefix . "ph_email_log
		    SET 
				lock_id = '" . $lock_id . "',
				locked_at = '" . date("Y-m-d H:i:s") . "'
		    WHERE 
		    	(status = '' OR status = 'fail1')
		    AND
		    	lock_id = ''
		    AND
		    	send_at <= '" . date("Y-m-d H:i:s") . "'
		    LIMIT " . apply_filters( 'propertyhive_email_process_limit', 25 ) . "
		");

		// We now have up to 25 emails locked. Get this 25 and attempt to send
		$emails_to_send = $wpdb->get_results("
			SELECT *
			FROM " . $wpdb->prefix . "ph_email_log
			WHERE 
				lock_id = '" . $lock_id . "'
		");

		foreach ( $emails_to_send as $email_to_send ) 
		{
			$email_id = $email_to_send->email_id;

			$headers = array();
			$headers[] = 'From: ' . html_entity_decode($email_to_send->from_name) . ' <' . $email_to_send->from_email_address . '>';
			if ( $email_to_send->cc_email_address != '' )
			{
				$headers[] = 'Cc: ' . $email_to_send->cc_email_address;
			}
			if ( $email_to_send->bcc_email_address != '' )
			{
				$headers[] = 'Bcc: ' . $email_to_send->bcc_email_address;
			}
			$headers[] = 'Content-Type: text/html; charset=UTF-8';

			$body = $email_to_send->body;

            if ( extension_loaded('zlib') && @gzuncompress($body) !== false ) 
            {
                $body = gzuncompress($body);
            }

        	$body = apply_filters( 'propertyhive_mail_content', $this->style_inline( $this->wrap_message( $body, $email_to_send->contact_id ) ) );
			
			$sent = wp_mail( 
				$email_to_send->to_email_address, 
				$email_to_send->subject, 
				$body, 
				$headers/*,
				string|array $attachments = array() */
			);

			$new_status = '';
			if ( $sent )
			{
				// Sent successfully
				$new_status = 'sent';
			}
			else
			{
				// Failed to send
				if ($email_to_send->status == '')
				{
					$new_status = 'fail1';
				}
				else
				{
					$new_status = 'fail2';
				}
			}
			$wpdb->query("
			    UPDATE " . $wpdb->prefix . "ph_email_log
			    SET 
					status = '" . $new_status . "',
					lock_id = ''
			    WHERE 
			    	email_id = '" . $email_id . "'
			");
		}

		// Delete old logs
		$keep_logs_days = (string)apply_filters( 'propertyhive_keep_email_logs_days', '3650' ); // 10 years

	    // Revert back to 3650 days if anything other than numbers has been passed
	    // This prevent SQL injection and errors
	    if ( !preg_match("/^\d+$/", $keep_logs_days) )
	    {
	        $keep_logs_days = '3650';
	    }

	    $wpdb->query( "DELETE FROM " . $wpdb->prefix . "ph_email_log WHERE send_at < DATE_SUB(NOW(), INTERVAL " . $keep_logs_days . " DAY)" );
	}

	/*
	 * Automatically send new properties to registered applicants
	 */
	public function ph_auto_email_match()
	{
		global $post;

		$dry_run = isset($_GET['dry_run']) ? true : false;

		if ( $dry_run === true ) { echo 'Running auto-match in dry run mode. Logging will be output and no emails will be sent.' . "<br>\n"; }

		// Auto emails enabled in settings
		// Auto emails not disabled in applicant record
		// Property added more recently that setting enabled in settings
		// Property not already previously sent
		// Valid email address
		// 'Do not email' not selected

		$auto_property_match_enabled = get_option( 'propertyhive_auto_property_match', '' );

		if ( $dry_run === true ) { echo 'Auto-match setting enabled: ' . $auto_property_match_enabled . "<br>\n"; }

		if ( $auto_property_match_enabled == '' )
		{
			return false;
		}
		
		$auto_property_match_enabled_date = get_option( 'propertyhive_auto_property_match_enabled_date', '' );

		if ( $dry_run === true ) { echo 'Auto-match setting enabled date: ' . $auto_property_match_enabled_date . "<br>\n"; }

		if ( $auto_property_match_enabled_date == '' )
		{
			return false;
		}

		// Get all of the offices and store them in an array to save having to query them for every email
		$args = array(
			'post_type' => 'office',
			'nopaging' => true
		);

		$office_names = array();
		$office_email_addresses = array();
		$office_query = new WP_Query( $args );

		if ( $office_query->have_posts() )
		{
			while ( $office_query->have_posts() )
			{
				$office_query->the_post();

				$office_names[get_the_ID()] = get_the_title( get_the_ID() );

				$office_email_addresses['residential-sales'][get_the_ID()] = get_post_meta( get_the_ID(), '_office_email_address_sales', TRUE );
				$office_email_addresses['residential-lettings'][get_the_ID()] = get_post_meta( get_the_ID(), '_office_email_address_lettings', TRUE );
				$office_email_addresses['commercial'][get_the_ID()] = get_post_meta( get_the_ID(), '_office_email_address_commercial', TRUE );
			}
		}

		wp_reset_postdata();

		// Get all of the negotiators and store them in an array to save having to query them for every email
		$negotiator_names = array();
		$negotiator_email_addresses = array();

        $args = array(
            'number' => 9999,
            'role__not_in' => apply_filters( 'property_negotiator_exclude_roles', array('property_hive_contact', 'subscriber') ),
            'fields' => array( 'ID', 'display_name', 'user_email' )
        );

        $args = apply_filters( 'propertyhive_negotiators_query', $args );

        $user_query = new WP_User_Query( $args );

        $negotiators = array();

        if ( ! empty( $user_query->results ) ) 
        {
            foreach ( $user_query->results as $user ) 
            {
                $negotiator_names[$user->ID] = $user->display_name;

				$negotiator_email_addresses[$user->ID] = $user->user_email;
            }
        }

		include_once( dirname(__FILE__) . '/admin/class-ph-admin-matching-properties.php' );
		$ph_admin_matching_properties = new PH_Admin_Matching_Properties();

		$meta_query = array(
			array(
				'key' => '_contact_types',
				'value' => 'applicant',
				'compare' => 'LIKE'
			)
		);

		if ( version_compare( get_bloginfo( 'version' ), '5.1', '>=' ) )
		{
			// If WP version contains compare_key, check contact has at least one applicant profile with Send Matching Properties checked
			$meta_query[] = array(
				'key' => '_applicant_profile_',
				'compare_key' => 'LIKE',
				'value' => 'send_matching_properties";s:3:"yes"',
				'compare' => 'LIKE',
			);
		}

		// Get all contacts that have a type of applicant
		$args = array(
			'post_type' => 'contact',
			'nopaging' => true,
			'meta_query' => $meta_query,
			'fields' => 'ids'
		);

		if ( $dry_run === true ) { echo 'Running query to get contacts with args: ' . print_r($args, true) . "<br>\n"; }

		$contact_query = new WP_Query( $args );

		if ( $dry_run === true ) { echo 'Found ' . $contact_query->found_posts . ' contacts' . "<br>\n"; }

		if ( $contact_query->have_posts() )
		{
			$default_subject = get_option( 'propertyhive_property_match_default_email_subject', '' );
            $default_body = get_option( 'propertyhive_property_match_default_email_body', '' );

            $allowed_tags = array(
                'strong' => array(),
                'span'   => array(),
                'em'     => array(),
                'h1'     => array(),
                'h2'     => array(),
                'h3'     => array(),
                'h4'     => array(),
                'h5'     => array(),
                'h6'     => array(),
                'i'      => array(),
                'u'      => array(),
                'b'      => array(),
                'a'      => array(
                    'href' => array(),
                    'target' => array(),
                ),
            );
            $allowed_tags = apply_filters( 'propertyhive_match_email_allowed_tags', $allowed_tags );

            $default_body = wp_kses($default_body, $allowedposttags);

			while ( $contact_query->have_posts() )
			{
				$contact_query->the_post();

				$contact_id = get_the_ID();

				if ( $dry_run === true ) { echo 'Doing contact: ' . get_the_title() . "<br>\n"; }

				// invalid email address
				if ( strpos( get_post_meta( $contact_id, '_email_address', TRUE ), '@' ) === FALSE )
				{
					if ( $dry_run === true ) { echo 'Invalid email address. Skipping' . "<br>\n"; }

					continue;
				}

				// email in the list of forbidden contact methods
				$forbidden_contact_methods = get_post_meta( $contact_id, '_forbidden_contact_methods', TRUE );
				if ( is_array($forbidden_contact_methods) && in_array('email', $forbidden_contact_methods) )
				{
					if ( $dry_run === true ) { echo 'Email communication forbidden in contact preferences. Skipping' . "<br>\n"; }

					continue;
				}

				$applicant_profiles = get_post_meta( $contact_id, '_applicant_profiles', TRUE );

				if ( $applicant_profiles != '' && $applicant_profiles > 0 )
				{
					$dismissed_properties = get_post_meta( $contact_id, '_dismissed_properties', TRUE );
					if ( $dismissed_properties == '' )
					{
						$dismissed_properties = array();
					}

					if ( $dry_run === true ) { if ( !empty($dismissed_properties) ) { echo 'Dismissed properties: ' . print_r($dismissed_properties, true) . "<br>\n"; } }

					for ( $i = 0; $i < $applicant_profiles; ++$i )
					{
						$applicant_profile = get_post_meta( $contact_id, '_applicant_profile_' . $i, TRUE );

						if ( $applicant_profile == '' || !is_array($applicant_profile) || !isset($applicant_profile['department']) )
						{
							if ( $dry_run === true ) { echo 'Applicant relationship empty or no department set' . "<br>\n"; }
							continue;
						}

						if ( !isset($applicant_profile['send_matching_properties']) || ( isset($applicant_profile['send_matching_properties']) && $applicant_profile['send_matching_properties'] != 'yes' ) )
						{
							if ( $dry_run === true ) { echo 'Send matching properties disabled' . "<br>\n"; }
							continue;
						}

						if ( isset($applicant_profile['auto_match_disabled']) && $applicant_profile['auto_match_disabled'] == 'yes' )
						{
							if ( $dry_run === true ) { echo 'Auto match disabled' . "<br>\n"; }
							continue;
						}

						if ( $dry_run === true ) { echo 'Getting matching properties' . "<br>\n"; }

						$matching_properties = $ph_admin_matching_properties->get_matching_properties( $contact_id, $i, $auto_property_match_enabled_date );

						if ( $dry_run === true ) { echo 'Found ' . count($matching_properties) . ' matching properties' . "<br>\n"; }

						if ( !empty($matching_properties) )
						{
							$already_sent_properties = get_post_meta( $contact_id, '_applicant_profile_' . $i . '_match_history', TRUE );

							// Remove from this array if on market changed or price changed
							if ( is_array($already_sent_properties) )
							{
								foreach ( $already_sent_properties as $already_sent_property_id => $sends )
								{
									$highest_send = $sends[count($sends) - 1]['date'];

									if ( $highest_send != '' )
									{
										$on_market_change_date = get_post_meta( $already_sent_property_id, '_on_market_change_date', TRUE );
										$price_change_date = get_post_meta( $already_sent_property_id, '_price_change_date', TRUE );

										if ( $on_market_change_date > $highest_send || $price_change_date > $highest_send )
										{
											// This property has changed since it was last sent. Remove from already sent list so it gets sent again
											unset($already_sent_properties[$already_sent_property_id]);
										}
									}
								}
							}

							// Check properties haven't already been sent and not marked as 'not interested'
							$new_matching_properties = array();
							foreach ($matching_properties as $matching_property)
							{
								if ( !isset($already_sent_properties[$matching_property->id]) && !in_array($matching_property->id, $dismissed_properties) )
								{
									$new_matching_properties[] = $matching_property->id;
								}
							}

							$max_results = apply_filters( 'propertyhive_auto_match_maximum_results', FALSE);
							if ( $max_results !== FALSE )
							{
								$new_matching_properties = array_slice($new_matching_properties, 0, (int)$max_results);
							}

							if ( $dry_run === true ) { echo 'Found ' . count($new_matching_properties) . ' matching properties after removing already sent and dismissed' . "<br>\n"; }

							if ( !empty($new_matching_properties) )
							{
								$subject = str_replace("[property_count]", count($new_matching_properties) . ' propert' . ( ( count($new_matching_properties) != 1 ) ? 'ies' : 'y' ), $default_subject);

						        $contact = new PH_Contact($contact_id);
						        $body = str_replace("[contact_name]", $contact->post_title, $default_body);
						        $body = str_replace("[contact_dear]", $contact->dear(), $body);
						        $body = str_replace("[property_count]", count($new_matching_properties) . ' propert' . ( ( count($new_matching_properties) != 1 ) ? 'ies' : 'y' ), $body);

						        $office_counts = array();
						        $negotiator_counts = array();

						        if ( strpos($body, '[properties]') !== FALSE )
						        {
						            ob_start();

						            if ( !empty($new_matching_properties) )
						            {
						                foreach ( $new_matching_properties as $email_property_id )
						                {
						                    $property = new PH_Property((int)$email_property_id);

						                    if ( $property->office_id != '' && $property->office_id != 0 )
						                    {
						                    	if ( !isset($office_counts[$property->office_id]) ) { $office_counts[$property->office_id] = 0; }
						                    	++$office_counts[$property->office_id];
						                    }

						                    if ( $property->negotiator_id != '' && $property->negotiator_id != 0 )
						                    {
						                    	if ( !isset($negotiator_counts[$property->negotiator_id]) ) { $negotiator_counts[$property->negotiator_id] = 0; }
						                    	++$negotiator_counts[$property->negotiator_id];
						                    }

						                    ph_get_template( 'emails/applicant-match-property.php', array( 'property' => $property ) );
						                }
						            }
						            $body = str_replace("[properties]", ob_get_clean(), $body);
						        }

						        // Get email address of office with most properties
						        $highest_office_name = '';
						        $highest_office_email_address = '';
						        if ( !empty($office_counts) )
						        {
							        arsort($office_counts);
							        reset($office_counts);
									$highest_office_id = key($office_counts);
									$highest_office_name = ( isset($office_names[$highest_office_id]) ? $office_names[$highest_office_id] : '' );
									$highest_office_email_address = ( isset($office_email_addresses[$applicant_profile['department']][$highest_office_id]) ? $office_email_addresses[$applicant_profile['department']][$highest_office_id] : '' );
								}
								if ( $highest_office_email_address == '' )
								{
									// fallback to admin email address
									$highest_office_email_address = get_option('admin_email');
								}

								$body = str_replace("[office_name]", $highest_office_name, $body);
								$body = str_replace("[office_email_address]", $highest_office_email_address, $body);

								$highest_negotiator_name = '';
								$highest_negotiator_email_address = '';
						        if ( !empty($negotiator_counts) )
						        {
							        arsort($negotiator_counts);
							        reset($negotiator_counts);
									$highest_negotiator_id = key($negotiator_counts);
									$highest_negotiator_name = ( isset($negotiator_names[$highest_negotiator_id]) ? $negotiator_names[$highest_negotiator_id] : '' );
									$highest_negotiator_email_address = ( isset($negotiator_email_addresses[$highest_negotiator_id]) ? $negotiator_email_addresses[$highest_negotiator_id] : '' );
								}

								$body = str_replace("[negotiator_name]", $highest_negotiator_name, $body);
								$body = str_replace("[negotiator_email_address]", $highest_negotiator_email_address, $body);

								$highest_office_email_address = apply_filters( 'propertyhive_auto_match_from_email_address', $highest_office_email_address );

								if ( !$dry_run )
								{
									$ph_admin_matching_properties->send_emails(
										$contact_id,
										$i,
										$new_matching_properties,
										get_bloginfo('name'),
										$highest_office_email_address,
										$subject,
										$body
									);
								}
								else
								{
									echo 'Would\'ve sent email. Not sending due to being ran in dry run mode' . "<br>\n";
								}
							}
						}
					}
				}
				else
				{
					if ( $dry_run === true ) { echo 'No applicant profiles found. Skipping' . "<br>\n"; }
				}
			}
		}

		if ( $dry_run === true ) { echo 'Finished auto-match process' . "<br>\n"; die(); }

		wp_reset_postdata();
	}

	/**
	 * Init email classes.
	 */
	public function init() {
		
	}

	/**
	 * Apply inline styles to dynamic content.
	 *
	 * @param string|null $content
	 * @return string
	 */
	public function style_inline( $content ) {
		// make sure we only inline CSS for html emails
		if ( class_exists( 'DOMDocument' ) ) {
			ob_start();
			ph_get_template( 'emails/email-styles.php' );
			$css = apply_filters( 'propertyhive_email_styles', ob_get_clean() );

			// include css inliner
			if ( ! class_exists( 'Emogrifier' ) && class_exists( 'DOMDocument' ) ) {
				include_once( dirname( __FILE__ ) . '/libraries/class-emogrifier.php' );
			}
			
			// apply CSS styles inline for picky email clients
			try {
				$emogrifier = new Emogrifier( $content, $css );
				$content    = $emogrifier->emogrify();
			} catch ( Exception $e ) {
				die(esc_html("Error converting CSS styles to be inline. Error as follows: " . $e->getMessage()));
			}
		}
		return $content;
	}
	
	/**
	 * Get the email header.
	 */
	public function email_header( $contact_id = '' ) {
		ph_get_template( 'emails/email-header.php' );
	}

	/**
	 * Get the email footer.
	 */
	public function email_footer( $contact_id = '' ) {
		$unsubscribe_link = '';
		if ($contact_id != '')
		{
			$unsubscribe_link = site_url() .'?ph_unsubscribe=' . base64_encode($contact_id . '|' . md5( get_post_meta( $contact_id, '_email_address', TRUE ) ) );
		}

		ph_get_template( 'emails/email-footer.php', array( 'unsubscribe_link' => $unsubscribe_link ) );
	}

	/**
	 * Wraps a message in the Property Hive mail template.
	 *
	 * @param mixed $email_heading
	 * @param string $message
	 * @return string
	 */
	public function wrap_message( $message, $contact_id = '', $plain_text = false ) {
		// Buffer
		ob_start();

		do_action( 'propertyhive_email_header', $contact_id );

		echo wpautop( wptexturize( $message ) );

		do_action( 'propertyhive_email_footer', $contact_id );

		// Get contents
		$message = ob_get_clean();

		return $message;
	}

	public function send_enquiry_auto_responder( $data = array() )
	{
		if ( isset($data['property_id']) && sanitize_text_field($data['property_id']) != '' )
		{
			$property_id = $data['property_id'];
			$to = sanitize_email( $_POST['email_address'] );
			$subject = get_option( 'propertyhive_enquiry_auto_responder_email_subject', '' );
			$body = get_option( 'propertyhive_enquiry_auto_responder_email_body', '' );

			if ( $to != '' && $subject != '' && $body != '' )
			{
				$headers = array();
				$headers[] = 'From: ' . html_entity_decode(get_bloginfo('name')) . ' <' . get_option( 'propertyhive_email_from_address', get_option( 'admin_email' ) ) . '>';
				$headers[] = 'Content-Type: text/html; charset=UTF-8';

				$body = str_replace( "[name]", ( isset($_POST['name']) ? ph_clean($_POST['name']) : '' ), $body );

				$body = str_replace( "[property_address_hyperlinked]", '<a href="' . get_permalink($property_id) . '">' . get_the_title($property_id) . '</a>', $body );

				if ( strpos( $body, '[similar_properties]' ) !== FALSE )
				{
					$similar_html = '';

					$department = get_post_meta( $property_id, '_department', TRUE );					

					// Get three similar properties
					$args = array(
						'post_type' => 'property',
						'post_status' => 'publish',
						'posts_per_page' => 3,
						'orderby' => 'rand',
						'post__not_in' => array($property_id),
					);

					$meta_query = array();

					$meta_query[] = array(
						'key' 		=> '_department',
						'value' 	=> $department,
					);

					$meta_query[] = array(
						'key' 		=> '_on_market',
						'value' 	=> 'yes',
					);

					if ( $department != 'commercial' && ph_get_custom_department_based_on( $department ) != 'commercial' )
					{
						$bedrooms = get_post_meta( $property_id, '_bedrooms', TRUE );

						$meta_query[] = array(
							'key' 		=> '_bedrooms',
							'value' 	=> $bedrooms,
							'type'      => 'NUMERIC'
						);


						$price = get_post_meta( $property_id, '_price_actual', true );
						$lower_price = $price - ($price / 10);
						$higher_price = $price + ($price / 10);

						$meta_query[] = array(
							'key' 		=> '_price_actual',
							'value' 	=> $lower_price,
							'compare'   => '>=',
							'type'      => 'NUMERIC'
						);

						$meta_query[] = array(
							'key' 		=> '_price_actual',
							'value' 	=> $higher_price,
							'compare'   => '<=',
							'type'      => 'NUMERIC'
						);
					}
					else
					{
						$for_sale = get_post_meta( $property_id, '_for_sale', true );
						$to_rent = get_post_meta( $property_id, '_to_rent', true );

						if ( $for_sale == 'yes' )
						{
							$meta_query[] = array(
								'key' 		=> '_for_sale',
								'value' 	=> 'yes',
							);
						}
						elseif ( $to_rent == 'yes' )
						{
							$meta_query[] = array(
								'key' 		=> '_to_rent',
								'value' 	=> 'yes',
							);
						}
					}

					$args['meta_query'] = $meta_query;

					$property_match_statuses = get_option( 'propertyhive_property_match_statuses', '' );
					if ( $property_match_statuses != '' && is_array($property_match_statuses) && !empty($property_match_statuses) )
					{
						$args['tax_query'] = array(
							array(
								'taxonomy' => 'availability',
								'field'    => 'term_id',
								'terms'    => $property_match_statuses,
								'operator' => 'IN',
							)
						);
					}

					$properties_query = new WP_Query( apply_filters( 'propertyhive_auto_responder_similar_properties_query', $args, $property_id ) );

					if ( $properties_query->have_posts() )
					{
						$similar_html = '<br><hr><br><h3>Similar Properties You Might Like:</h3>';

						while ( $properties_query->have_posts() )
						{
							$properties_query->the_post();

							$property = new PH_Property( get_the_ID() );

							ob_start();

							ph_get_template( 'emails/enquiry-autoresponder-similar-property.php', array( 'property' => $property ) );

							$similar_html .= ob_get_clean();
						}
					}
					$body = str_replace( "[similar_properties]", $similar_html, $body );
				}

				$body = apply_filters( 'propertyhive_enquiry_auto_responder_body', $body, $property_id );
	        	$body = apply_filters( 'propertyhive_mail_content', $this->style_inline( $this->wrap_message( $body ) ) );

				wp_mail( $to, $subject, $body, $headers );
			}
		}
	}
}