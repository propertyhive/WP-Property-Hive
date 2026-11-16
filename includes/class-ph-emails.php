<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.


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
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'propertyhive' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'propertyhive' ), '1.0.0' );
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

		// Send enquiry email for enquiries created via the REST API
		add_action( 'propertyhive_rest_api_enquiry_created', array( $this, 'send_rest_api_enquiry_alert' ), 10, 2 );

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
		if ( isset( $_GET['custom_email_log_cron'] ) )
        {
            if ( ! current_user_can( 'manage_propertyhive' ) ) {
                wp_die( esc_html__( 'Insufficient permissions', 'propertyhive' ), '', array( 'response' => 403 ) );
            }
            check_admin_referer( 'propertyhive-run-email-job' );
            $job = is_string( $_GET['custom_email_log_cron'] ) ? sanitize_key( wp_unslash( $_GET['custom_email_log_cron'] ) ) : '';
            if ( ! in_array( $job, array( 'propertyhive_process_email_log', 'propertyhive_auto_email_match' ), true ) ) {
                wp_die( esc_html__( 'Invalid email job.', 'propertyhive' ), '', array( 'response' => 400 ) );
            }
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Dynamic email cron dispatch is a bounded internal false positive: the value is sanitized, restricted to two propertyhive_* jobs, and reached only after capability and nonce checks.
            do_action( $job );
        }
	}

	public function send_applicant_registration_alert( $contact_post_id, $user_id )
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This internal action callback is invoked by the nonce-checked applicant registration AJAX handler; it only formats that request's notification email.
		$request_post = wp_unslash( $_POST );
		$office_id = isset( $request_post['office_id'] ) && is_scalar( $request_post['office_id'] ) ? absint( $request_post['office_id'] ) : 0;
		$department = isset( $request_post['department'] ) && is_string( $request_post['department'] ) ? sanitize_key( $request_post['department'] ) : '';

		if ( get_option( 'propertyhive_new_registration_alert', '' ) == 'yes' && $office_id > 0 && '' !== $department )
		{
			$to = get_post_meta( $office_id, '_office_email_address_' . str_replace( 'residential-', '', $department ), TRUE );

			if ( $to == '' )
			{
				$to = get_option( 'admin_email', '' );
			}

			$subject = sprintf( 
				/* translators: %s: person name */
				__( 'A new applicant, %s, has registered through your website', 'propertyhive' ), 
				get_the_title($contact_post_id) 
			);

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

					$raw_value = isset( $request_post[ $key ] ) && ( is_string( $request_post[ $key ] ) || is_array( $request_post[ $key ] ) ) ? $request_post[ $key ] : '';
				$value = ph_clean( $raw_value );
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

		$expired_lock = gmdate( 'Y-m-d H:i:s', strtotime( '24 hours ago' ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The email queue is a custom plugin table and these bounded maintenance updates have no WordPress API equivalent.
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}ph_email_log SET status = 'fail2', lock_id = '' WHERE status = 'fail1' AND lock_id <> '' AND locked_at <= %s",
            $expired_lock
        ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The email queue is a custom plugin table and these bounded maintenance updates have no WordPress API equivalent.
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}ph_email_log SET status = 'fail1', lock_id = '' WHERE status = '' AND lock_id <> '' AND locked_at <= %s",
            $expired_lock
        ) );

        // Reserve a bounded batch before sending, so concurrent workers cannot send it twice.
        $process_limit = max( 1, min( 1000, (int) apply_filters( 'propertyhive_email_process_limit', 25 ) ) );
        $now = gmdate( 'Y-m-d H:i:s' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The email queue is a custom plugin table and this bounded reservation update has no WordPress API equivalent.
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}ph_email_log SET lock_id = %s, locked_at = %s WHERE (status = '' OR status = 'fail1') AND lock_id = '' AND send_at <= %s LIMIT %d",
            $lock_id, $now, $now, $process_limit
        ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The email queue is a custom plugin table and this lock-scoped read has no WordPress API equivalent.
        $emails_to_send = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ph_email_log WHERE lock_id = %s",
            $lock_id
        ) );

		foreach ( $emails_to_send as $email_to_send ) 
		{
			$email_id = $email_to_send->email_id;

			$contact_id = (int)$email_to_send->contact_id;

			// Ensure client is still publised, hasn't unsubscribed from emails and that send matching properties is yes
			// To combat being added to the queue but then the queue only getting processed at a later date
			$forbidden_contact_methods = get_post_meta( $contact_id, '_forbidden_contact_methods', true );

			$email_forbidden = is_array( $forbidden_contact_methods ) && in_array( 'email', $forbidden_contact_methods, true );

			$applicant_profile = get_post_meta( $contact_id, '_applicant_profile_' . (int)$email_to_send->applicant_profile_id, true );

			$matching_enabled = is_array( $applicant_profile )
				&& isset( $applicant_profile['send_matching_properties'] )
				&& 'yes' === $applicant_profile['send_matching_properties'];

			if (
				'publish' !== get_post_status( $contact_id )
				|| $email_forbidden
				|| !$matching_enabled
			) {
				$wpdb->update(
					$wpdb->prefix . 'ph_email_log',
					array(
						'status'  => 'fail2',
						'lock_id' => '',
					),
					array( 'email_id' => (int)$email_id ),
					array( '%s', '%s' ),
					array( '%d' )
				);

				continue;
			}

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
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The email queue is a custom plugin table; wpdb is the required API for updating its status.
			$wpdb->update(
                $wpdb->prefix . 'ph_email_log',
                array( 'status' => $new_status, 'lock_id' => '' ),
                array( 'email_id' => (int) $email_id ),
                array( '%s', '%s' ),
                array( '%d' )
            );
		}

		// Delete old logs
		$keep_logs_days = (string)apply_filters( 'propertyhive_keep_email_logs_days', '3650' ); // 10 years

	    // Revert back to 3650 days if anything other than numbers has been passed
	    // This prevent SQL injection and errors
	    if ( !preg_match("/^\d+$/", $keep_logs_days) )
	    {
	        $keep_logs_days = '3650';
	    }

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The email queue is a custom plugin table and this retention cleanup has no WordPress API equivalent.
	    $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}ph_email_log WHERE send_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            (int) $keep_logs_days
        ) );
	}

	/*
	 * Automatically send new properties to registered applicants
	 */
	public function ph_auto_email_match()
	{
		global $post;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- dry_run only selects diagnostic output and never changes persisted data; the real email action is capability and nonce protected in run_custom_email_cron().
		$request_get = wp_unslash( $_GET );
		$dry_run = isset( $request_get['dry_run'] );

		if ( $dry_run === true ) { echo 'Running auto-match in dry run mode. Logging will be output and no emails will be sent.' . "<br>\n"; }

		// Auto emails enabled in settings
		// Auto emails not disabled in applicant record
		// Property added more recently that setting enabled in settings
		// Property not already previously sent
		// Valid email address
		// 'Do not email' not selected

		$auto_property_match_enabled = get_option( 'propertyhive_auto_property_match', '' );

		if ( $dry_run === true ) { echo 'Auto-match setting enabled: ' . esc_html( $auto_property_match_enabled ) . "<br>\n"; }

		if ( $auto_property_match_enabled == '' )
		{
			return false;
		}
		
		$auto_property_match_enabled_date = get_option( 'propertyhive_auto_property_match_enabled_date', '' );

		if ( $dry_run === true ) { echo 'Auto-match setting enabled date: ' . esc_html( $auto_property_match_enabled_date ) . "<br>\n"; }

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
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy Property Negotiator compatibility filter; existing role filters depend on this exact public hook name.
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
			'post_status' => 'publish',
			'nopaging' => true,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Matching mail must include all eligible applicant profiles; membership and opt-in eligibility are stored in contact metadata.
			'meta_query' => $meta_query,
			'fields' => 'ids'
		);

		if ( $dry_run === true ) { echo 'Running query to get contacts with args: ' . esc_html( wp_json_encode( $args, JSON_PRETTY_PRINT ) ) . "<br>\n"; }

		$contact_query = new WP_Query( $args );

		if ( $dry_run === true ) { echo 'Found ' . esc_html( $contact_query->found_posts ) . ' contacts' . "<br>\n"; }

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

            $default_body = wp_kses( $default_body, $allowed_tags );

			while ( $contact_query->have_posts() )
			{
				$contact_query->the_post();

				$contact_id = get_the_ID();

				if ( $dry_run === true ) { echo 'Doing contact: ' . esc_html( get_the_title() ) . "<br>\n"; }

				// invalid email address
				if ( strpos( get_post_meta( $contact_id, '_email_address', TRUE ), '@' ) === FALSE )
				{
					if ( $dry_run === true ) { echo esc_html('Invalid email address. Skipping') . "<br>\n"; }

					continue;
				}

				// email in the list of forbidden contact methods
				$forbidden_contact_methods = get_post_meta( $contact_id, '_forbidden_contact_methods', TRUE );
				if ( is_array($forbidden_contact_methods) && in_array('email', $forbidden_contact_methods) )
				{
					if ( $dry_run === true ) { echo esc_html('Email communication forbidden in contact preferences. Skipping') . "<br>\n"; }

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

					if ( $dry_run === true ) { if ( !empty($dismissed_properties) ) { echo 'Dismissed properties: ' . esc_html( wp_json_encode( $dismissed_properties, JSON_PRETTY_PRINT ) ) . "<br>\n"; } }

					for ( $i = 0; $i < $applicant_profiles; ++$i )
					{
						$applicant_profile = get_post_meta( $contact_id, '_applicant_profile_' . $i, TRUE );

						if ( $applicant_profile == '' || !is_array($applicant_profile) || !isset($applicant_profile['department']) )
						{
							if ( $dry_run === true ) { echo esc_html('Applicant relationship empty or no department set') . "<br>\n"; }
							continue;
						}

						if ( !isset($applicant_profile['send_matching_properties']) || ( isset($applicant_profile['send_matching_properties']) && $applicant_profile['send_matching_properties'] != 'yes' ) )
						{
							if ( $dry_run === true ) { echo esc_html('Send matching properties disabled') . "<br>\n"; }
							continue;
						}

						if ( isset($applicant_profile['auto_match_disabled']) && $applicant_profile['auto_match_disabled'] == 'yes' )
						{
							if ( $dry_run === true ) { echo esc_html('Auto match disabled') . "<br>\n"; }
							continue;
						}

						if ( $dry_run === true ) { echo 'Getting matching properties' . "<br>\n"; }

						$matching_properties = $ph_admin_matching_properties->get_matching_properties( $contact_id, $i, $auto_property_match_enabled_date );

						if ( $dry_run === true ) { echo esc_html('Found ' . count($matching_properties) . ' matching properties') . "<br>\n"; }

						if ( !empty($matching_properties) )
						{
							$already_sent_properties = get_post_meta( $contact_id, '_applicant_profile_' . $i . '_match_history', TRUE );

							// Remove from this array if on market changed or price changed
							if ( is_array($already_sent_properties) )
							{
								if ( $dry_run === true ) { echo 'Already sent properties before: ' . esc_html( wp_json_encode( $already_sent_properties, JSON_PRETTY_PRINT ) ) . "<br>\n"; }

								$matching_property_lookup = array_fill_keys($matching_properties, true);

								foreach ( $already_sent_properties as $already_sent_property_id => $sends )
								{
									if ( !isset($matching_property_lookup[$already_sent_property_id]) )
								    {
								    	if ( $dry_run === true ) { echo 'Already sent property no longer in matched list. Skipping checks for on market and price changes.' . "<br>\n"; }

								        continue;
								    }

									$highest_send = $sends[count($sends) - 1]['date'];

									if ( $highest_send != '' )
									{
										if ( $dry_run === true ) { echo 'Property: ' . esc_html( $already_sent_property_id ) . ' last sent: ' . esc_html( $highest_send ) . "<br>\n"; }

										$on_market_change_date = get_post_meta( $already_sent_property_id, '_on_market_change_date', TRUE );

										if ( $dry_run === true ) { echo 'Property: ' . esc_html( $already_sent_property_id ) . ' last on market change: ' . esc_html( $on_market_change_date ) . "<br>\n"; }

										$price_change_date = get_post_meta( $already_sent_property_id, '_price_change_date', TRUE );

										if ( $dry_run === true ) { echo 'Property: ' . esc_html( $already_sent_property_id ) . ' last price change: ' . esc_html( $price_change_date ) . "<br>\n"; }

										if ( $on_market_change_date > $highest_send )
										{
											if ( $dry_run === true ) { echo 'Property: ' . esc_html( $already_sent_property_id ) . ' has changed on market since last sent' . "<br>\n"; }

											// This property has changed since it was last sent. Remove from already sent list so it gets sent again
											unset($already_sent_properties[$already_sent_property_id]);
										}
										elseif ( $price_change_date > $highest_send )
										{
											if ( $dry_run === true ) { echo 'Property: ' . esc_html( $already_sent_property_id ) . ' has changed price since last sent' . "<br>\n"; }

											// This property has changed since it was last sent. Remove from already sent list so it gets sent again
											unset($already_sent_properties[$already_sent_property_id]);
										}
									}
								}

								if ( $dry_run === true ) { echo 'Already sent properties after: ' . esc_html( wp_json_encode( $already_sent_properties, JSON_PRETTY_PRINT ) ) . "<br>\n"; }
							}

							if ( $dry_run === true ) { echo 'Matching properties before removing already sent: ' . esc_html( wp_json_encode( $matching_properties, JSON_PRETTY_PRINT ) ) . "<br>\n"; }

							// Check properties haven't already been sent and not marked as 'not interested'
							$new_matching_properties = array();
							foreach ($matching_properties as $matching_property)
							{
								if ( !isset($already_sent_properties[$matching_property]) && !in_array($matching_property, $dismissed_properties) )
								{
									$new_matching_properties[] = $matching_property;
								}
							}

							if ( $dry_run === true ) { echo 'Matching properties after removing already sent: ' . esc_html( wp_json_encode( $new_matching_properties, JSON_PRETTY_PRINT ) ) . "<br>\n"; }

							$max_results = apply_filters( 'propertyhive_auto_match_maximum_results', FALSE);
							if ( $max_results !== FALSE )
							{
								$new_matching_properties = array_slice($new_matching_properties, 0, (int)$max_results);
							}

							if ( $dry_run === true ) { echo esc_html('Found ' . count($new_matching_properties) . ' matching properties after removing already sent and dismissed') . "<br>\n"; }

							if ( !empty($new_matching_properties) )
							{
								$subject = str_replace("[property_count]", count($new_matching_properties) . ' propert' . ( ( count($new_matching_properties) != 1 ) ? 'ies' : 'y' ), $default_subject);

						        $contact = new PH_Contact($contact_id);
						        $body = str_replace( '[contact_name]', esc_html( $contact->post_title ), $default_body );
						        $body = str_replace( '[contact_dear]', esc_html( $contact->dear() ), $body );
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

								$body = str_replace( '[office_name]', esc_html( $highest_office_name ), $body );
								$body = str_replace( '[office_email_address]', esc_html( $highest_office_email_address ), $body );

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

								$body = str_replace( '[negotiator_name]', esc_html( $highest_negotiator_name ), $body );
								$body = str_replace( '[negotiator_email_address]', esc_html( $highest_negotiator_email_address ), $body );

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
									echo esc_html('Would\'ve sent email. Not sending due to being ran in dry run mode') . "<br>\n";
								}
							}
						}
					}
				}
				else
				{
					if ( $dry_run === true ) { echo esc_html('No applicant profiles found. Skipping') . "<br>\n"; }
				}
			}
		}

		if ( $dry_run === true ) { echo esc_html('Finished auto-match process') . "<br>\n"; die(); }

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
			if ( ! class_exists( 'PropertyHive_Emogrifier' ) && class_exists( 'DOMDocument' ) ) {
				include_once( dirname( __FILE__ ) . '/libraries/class-emogrifier.php' );
			}
			
			// apply CSS styles inline for picky email clients
			try {
				$emogrifier = new PropertyHive_Emogrifier( $content, $css );
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
			$unsubscribe_link = PH()->get_contact_unsubscribe_url( $contact_id );
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

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Email bodies are deliberately HTML. The callers sanitize stored/request template content before this shared wrapper, and email header/footer plus propertyhive_mail_content are trusted extension points; escaping here would break supported markup.
		echo wpautop( wptexturize( $message ) );

		do_action( 'propertyhive_email_footer', $contact_id );

		// Get contents
		$message = ob_get_clean();

		return $message;
	}

	/**
	 * Send the enquiry email for an enquiry created via the REST API, mirroring the email sent
	 * when an enquiry form is submitted on the website.
	 *
	 * Off by default. Sent only when the enquiry was created with 'send_email' set to true, or when
	 * the 'propertyhive_rest_api_enquiry_send_email' filter returns true. Uses only data stored
	 * against the enquiry, so it can also be called later (e.g. from a queued job), in which case
	 * $request is null.
	 *
	 * As with website enquiry forms, 'propertyhive_property_enquiry_sent' fires once the email has
	 * been sent successfully.
	 *
	 * @param int $enquiry_post_id
	 * @param WP_REST_Request|null $request The original request, when available. Passed to filters.
	 * @return bool Whether the email was sent.
	 */
	public function send_rest_api_enquiry_alert( $enquiry_post_id, $request = null )
	{
		$send_email = ( get_post_meta( $enquiry_post_id, '_send_email', true ) == 'yes' );
		$send_email = (bool)apply_filters( 'propertyhive_rest_api_enquiry_send_email', $send_email, $enquiry_post_id, $request );

		if ( !$send_email )
		{
			return false;
		}

		$enquiry = new PH_Enquiry( $enquiry_post_id );

		$custom_fields = $enquiry->get_custom_fields();

		$property_ids = array_values( array_filter( array_map( 'intval', (array)get_post_meta( $enquiry_post_id, 'property_id', false ) ) ) );

		$name = (string)get_post_meta( $enquiry_post_id, 'name', true );
		$email_address = (string)get_post_meta( $enquiry_post_id, 'email_address', true );
		$telephone_number = (string)get_post_meta( $enquiry_post_id, 'telephone_number', true );
		$enquiry_message = (string)get_post_meta( $enquiry_post_id, 'message', true );

		$to = $this->get_enquiry_alert_recipient( $enquiry_post_id );

		// Subject
		if ( count($property_ids) == 1 )
		{
			$subject = __( 'New Property Enquiry', 'propertyhive' ) . ': ' . get_the_title( $property_ids[0] );
		}
		elseif ( count($property_ids) > 1 )
		{
			$subject = __( 'Multiple Property Enquiry', 'propertyhive' ) . ': ' . count($property_ids) . ' Properties';
		}
		else
		{
			$subject = __( 'New Enquiry', 'propertyhive' ) . ( $name != '' ? ' ' . __( 'from', 'propertyhive' ) . ' ' . $name : '' );
		}

		// Body
		$message = __( "You have received an enquiry. Please find details of the enquiry below", 'propertyhive' ) . "\n\n";

		if ( !empty($property_ids) )
		{
			$message = apply_filters( 'propertyhive_property_enquiry_pre_body', $message, $property_ids );

			$message .= ( count($property_ids) > 1 ? __( 'Properties', 'propertyhive' ) : __( 'Property', 'propertyhive' ) ) . ":\n";
			foreach ( $property_ids as $property_id )
			{
				$property = new PH_Property( $property_id );
				$message .= apply_filters( 'propertyhive_property_enquiry_property_output', $property->get_formatted_full_address() . "\n" . html_entity_decode(wp_strip_all_tags($property->get_formatted_price())) . "\n" . get_permalink( $property_id ), $property_id ) . "\n\n";
			}
		}

		$fields = array(
			__( 'Name', 'propertyhive' ) => $name,
			__( 'Email Address', 'propertyhive' ) => $email_address,
			__( 'Telephone Number', 'propertyhive' ) => $telephone_number,
			__( 'Message', 'propertyhive' ) => $enquiry_message,
		);

		// Custom fields to leave out of the email (by key). None by default.
		$excluded_custom_fields = (array)apply_filters( 'propertyhive_rest_api_enquiry_email_excluded_custom_fields', array(), $enquiry_post_id, $request );

		foreach ( $custom_fields as $key => $value )
		{
			if ( in_array( $key, $excluded_custom_fields, true ) ) { continue; }

			$fields[ ucwords( str_replace( array( '_', '-' ), ' ', $key ) ) ] = $value;
		}
		$fields = apply_filters( 'propertyhive_rest_api_enquiry_email_fields', $fields, $enquiry_post_id, $request );

		foreach ( $fields as $label => $value )
		{
			if ( (string)$value === '' ) { continue; }
			$message .= wp_strip_all_tags($label) . ": " . wp_strip_all_tags($value) . "\n";
		}

		if ( apply_filters('propertyhive_enquiry_email_show_manage_link', true) )
		{
			$message .= "\n" . __( "To manage this enquiry please visit the following URL", 'propertyhive' ) . ':' . "\n\n";
			$message .= admin_url( 'post.php?post=' . (int)$enquiry_post_id . '&action=edit' );
		}

		if ( !empty($property_ids) )
		{
			$message = apply_filters( 'propertyhive_property_enquiry_post_body', $message, $property_ids );
		}

		// Headers
		$from_email_address = get_option( 'propertyhive_email_from_address', '' );
		if ( $from_email_address == '' )
		{
			$from_email_address = get_option( 'admin_email' );
		}
		$from_email_address = sanitize_email( $from_email_address );

		$from_name = str_replace( array( "\r", "\n" ), '', $name );

		$headers = array();
		$headers[] = ( $from_name !== '' ) ? sprintf( 'From: %s <%s>', $from_name, $from_email_address ) : sprintf( 'From: <%s>', $from_email_address );
		if ( is_email($email_address) )
		{
			$headers[] = 'Reply-To: ' . $email_address;
		}

		// Apply the same filters as website enquiry form emails so existing customisations carry over
		if ( !empty($property_ids) )
		{
			$to = apply_filters( 'propertyhive_property_enquiry_to', $to, $property_ids );
			$subject = apply_filters( 'propertyhive_property_enquiry_subject', $subject, $property_ids );
			$headers = apply_filters( 'propertyhive_property_enquiry_headers', $headers, $property_ids );
			$message = apply_filters( 'propertyhive_property_enquiry_body', $message, $property_ids );
		}

		$to = apply_filters( 'propertyhive_rest_api_enquiry_email_to', $to, $enquiry_post_id, $request );
		$subject = apply_filters( 'propertyhive_rest_api_enquiry_email_subject', $subject, $enquiry_post_id, $request );
		$headers = apply_filters( 'propertyhive_rest_api_enquiry_email_headers', $headers, $enquiry_post_id, $request );
		$message = apply_filters( 'propertyhive_rest_api_enquiry_email_body', $message, $enquiry_post_id, $request );

		if ( empty($to) )
		{
			return false;
		}

		do_action( 'propertyhive_before_property_enquiry_sent' );

		$sent = wp_mail( $to, $subject, $message, $headers );

		do_action( 'propertyhive_after_property_enquiry_sent' );

		// Record the outcome against the enquiry (underscore prefixed so it isn't shown as an enquiry detail)
		update_post_meta( $enquiry_post_id, '_email_sent', $sent ? 'yes' : 'failed' );

		do_action( 'propertyhive_rest_api_enquiry_email_sent', $enquiry_post_id, $sent, $to );

		if ( $sent )
		{
			// Fire the same action as website enquiry forms, with the enquiry's details in the same
			// flat shape as a form submission (custom fields alongside the standard fields)
			$enquiry_data = array_filter( array(
				'name' => $name,
				'email_address' => $email_address,
				'telephone_number' => $telephone_number,
				'message' => $enquiry_message,
				'property_id' => implode( '|', $property_ids ),
			), 'strlen' );
			$enquiry_data = array_merge( $enquiry_data, $custom_fields );

			do_action( 'propertyhive_property_enquiry_sent', $enquiry_data, $to, $enquiry_post_id );
		}

		return (bool)$sent;
	}

	/**
	 * Get the email address an enquiry's email should be sent to, before filters are applied.
	 *
	 * Uses the office explicitly passed when the enquiry was created, else the (first) property's
	 * office, else the enquiry's office. Prefers the office's email address for the property's
	 * department, falling back through the other departments as website enquiry forms do, and
	 * finally to the site admin email address.
	 *
	 * @param int $enquiry_post_id
	 * @return string
	 */
	public function get_enquiry_alert_recipient( $enquiry_post_id )
	{
		$property_ids = array_values( array_filter( array_map( 'intval', (array)get_post_meta( $enquiry_post_id, 'property_id', false ) ) ) );

		$office_id = '';
		$department = '';
		if ( !empty($property_ids) )
		{
			$office_id = get_post_meta( $property_ids[0], '_office_id', true );
			$department = get_post_meta( $property_ids[0], '_department', true );
		}
		if ( get_post_meta( $enquiry_post_id, '_office_id_requested', true ) == 'yes' || $office_id == '' )
		{
			$office_id = get_post_meta( $enquiry_post_id, '_office_id', true );
		}

		if ( !empty($office_id) && get_post_type( (int)$office_id ) == 'office' )
		{
			switch ( $department )
			{
				case "residential-lettings": { $keys = array( 'lettings', 'sales', 'commercial' ); break; }
				case "commercial": { $keys = array( 'commercial', 'lettings', 'sales' ); break; }
				case "residential-sales":
				case "": { $keys = array( 'sales', 'lettings', 'commercial' ); break; }
				default: { $keys = array( str_replace( 'residential-', '', $department ), 'sales', 'lettings', 'commercial' ); break; }
			}

			foreach ( $keys as $key )
			{
				$to = get_post_meta( (int)$office_id, '_office_email_address_' . $key, true );
				if ( $to != '' )
				{
					return $to;
				}
			}
		}

		return get_option( 'admin_email' );
	}

	public function send_enquiry_auto_responder( $data = array() )
	{
		if ( ! is_array( $data ) ) {
			return;
		}

		$request_data = wp_unslash( $data );
		if ( isset( $request_data['property_id'] ) && is_string( $request_data['property_id'] ) && '' !== $request_data['property_id'] )
		{
			$property_ids = array_filter( array_map( 'absint', explode( '|', $request_data['property_id'] ) ) );
			if ( empty( $property_ids ) || count( $property_ids ) > 100 ) {
				return;
			}
			foreach ( $property_ids as $property_id ) {
				if ( 'property' !== get_post_type( $property_id ) || ! propertyhive_is_post_publicly_viewable( $property_id ) ) {
					return;
				}
			}

			$to = isset( $request_data['email_address'] ) && is_string( $request_data['email_address'] ) ? sanitize_email( $request_data['email_address'] ) : '';
			$subject = get_option( 'propertyhive_enquiry_auto_responder_email_subject', '' );
			$body = get_option( 'propertyhive_enquiry_auto_responder_email_body', '' );

			if ( $to != '' && $subject != '' && $body != '' )
			{
				$headers = array();
				$headers[] = 'From: ' . html_entity_decode(get_bloginfo('name')) . ' <' . get_option( 'propertyhive_email_from_address', get_option( 'admin_email' ) ) . '>';
				$headers[] = 'Content-Type: text/html; charset=UTF-8';

				$name = isset( $request_data['name'] ) && is_string( $request_data['name'] ) ? ph_clean( $request_data['name'] ) : '';
				$body = str_replace( '[name]', esc_html( $name ), $body );

				$property_address_hyperlinked = array();
				foreach ( $property_ids as $property_id )
				{
					$property_address_hyperlinked[] = '<a href="' . esc_url( get_permalink( $property_id ) ) . '">' . esc_html( get_the_title( $property_id ) ) . '</a>';
				}
				$body = str_replace( "[property_address_hyperlinked]", implode(' and ', array_filter(array_merge(array(join(', ', array_slice($property_address_hyperlinked, 0, -1))), array_slice($property_address_hyperlinked, -1)), 'strlen')), $body );

				if ( strpos( $body, '[similar_properties]' ) !== FALSE )
				{
					$similar_html = '';

					foreach ( $property_ids as $property_id )
					{
						$department = get_post_meta( $property_id, '_department', TRUE );					

						// Get three similar properties
						$args = array(
							'post_type' => 'property',
							'post_status' => 'publish',
							'posts_per_page' => 3,
							'orderby' => 'rand',
							// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Each email’s similar-property query returns three properties and excludes the current property. posts_per_page=3; post__not_in is one property ID; metadata/taxonomy match the recommendation rules.
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

						// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Each email’s similar-property query returns three properties and excludes the current property. posts_per_page=3; post__not_in is one property ID; metadata/taxonomy match the recommendation rules.
						$args['meta_query'] = $meta_query;

						$property_match_statuses = get_option( 'propertyhive_property_match_statuses', '' );
						if ( $property_match_statuses != '' && is_array($property_match_statuses) && !empty($property_match_statuses) )
						{
							// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Each email’s similar-property query returns three properties and excludes the current property. posts_per_page=3; post__not_in is one property ID; metadata/taxonomy match the recommendation rules.
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
							while ( $properties_query->have_posts() )
							{
								$properties_query->the_post();

								$property = new PH_Property( get_the_ID() );

								ob_start();

								ph_get_template( 'emails/enquiry-autoresponder-similar-property.php', array( 'property' => $property ) );

								$similar_html .= ob_get_clean();
							}
						}
					}
					if ( !empty($similar_html) )
					{
						$similar_html = '<br><hr><br><h3>Similar Properties You Might Like:</h3>' . $similar_html;
					}
					$body = str_replace( "[similar_properties]", $similar_html, $body );
				}

				$body = apply_filters( 'propertyhive_enquiry_auto_responder_body', $body, $property_ids[0] );
	        	$body = apply_filters( 'propertyhive_mail_content', $this->style_inline( $this->wrap_message( $body ) ) );

				wp_mail( $to, $subject, $body, $headers );
			}
		}
	}
}
