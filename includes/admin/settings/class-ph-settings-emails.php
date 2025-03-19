<?php
/**
 * PropertyHive Email Settings
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Settings_Emails' ) ) :

/**
 * PH_Settings_Emails.
 */
class PH_Settings_Emails extends PH_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'email';
		$this->label = __( 'Emails', 'propertyhive' );

		add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'propertyhive_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

		add_action( 'propertyhive_admin_field_email_queue', array( $this, 'email_queue_setting' ) );
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			'' => __( 'Email Options', 'propertyhive' ),
			'enquiry-auto-responder' => __( 'Enquiry Auto-Responder', 'propertyhive' ),
		);

		if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
	    {
	    	$sections['match'] = __( 'Property Match', 'propertyhive' );
	    }

	    if ( 
	    	get_option('propertyhive_module_disabled_viewings', '') != 'yes' ||
	    	get_option('propertyhive_module_disabled_appraisals', '') != 'yes'
	    )
	    {
	    	$sections['booking-confirmation'] = __( 'Booking Confirmation', 'propertyhive' );
	    }

	    if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
	    {
	    	$sections['log'] = __( 'Email Queue', 'propertyhive' );
	    }

		return apply_filters( 'propertyhive_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = array(

			array( 'title' => __( 'Email Sender Settings', 'propertyhive' ), 'type' => 'title', 'id' => 'email_sender_options' ),

			array(
				'title'       => __( '"From" Email Address', 'propertyhive' ),
				'id'          => 'propertyhive_email_from_address',
				'type'        => 'text',
				'css'         => 'min-width:300px;',
				'default'     => get_option('admin_email'),
				'autoload'    => false,
				'desc_tip'    => false,
			),

			array( 'type' => 'sectionend', 'id' => 'email_sender_options' ),

			array( 'title' => __( 'Email Template Settings', 'propertyhive' ), 'type' => 'title', 'id' => 'email_template_options' ),

			array(
				'title'       => __( 'Header Image URL', 'propertyhive' ),
				'desc'        => __( 'URL to an image you want to show in the email header. Upload images using the media uploader (Admin > Media).', 'propertyhive' ),
				'id'          => 'propertyhive_email_header_image',
				'type'        => 'text',
				'css'         => 'min-width:300px;',
				'placeholder' => 'http://',
				'default'     => '',
				'autoload'    => false,
				'desc_tip'    => true,
			),

			array(
				'title'    => __( 'Email Background Colour', 'propertyhive' ),
				'desc'     => __( 'The background colour for Property Hive email templates.', 'propertyhive' ),
				'id'       => 'propertyhive_email_background_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#f7f7f7',
				'autoload' => false,
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Body Background Colour', 'propertyhive' ),
				'desc'     => __( 'The main body background colour.', 'propertyhive' ),
				'id'       => 'propertyhive_email_body_background_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#ffffff',
				'autoload' => false,
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Body Text Colour', 'propertyhive' ),
				'desc'     => __( 'The main body text colour.', 'propertyhive' ),
				'id'       => 'propertyhive_email_text_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#333333',
				'autoload' => false,
				'desc_tip' => true,
			),

			array( 'type' => 'sectionend', 'id' => 'email_template_options' ),

        );

		$settings = apply_filters( 'propertyhive_email_settings', $settings );

		return apply_filters( 'propertyhive_get_settings_' . $this->id, $settings );
	}

	/**
	 * Get enquiry auto-responder settings array
	 *
	 * @return array
	 */
	public function get_enquiry_autoresponder_settings() {
		    
		return apply_filters( 'propertyhive_enquiry_autoresponder_settings', array(

			array( 'title' => __( 'Enquiry Auto-Responder Settings', 'propertyhive' ), 'type' => 'title', 'id' => 'enquiry_auto_responder_email_options' ),

            array(
                'title'   => __( 'Auto-Responder Enabled', 'propertyhive' ),
                'id'      => 'propertyhive_enquiry_auto_responder',
                'type'    => 'checkbox',
                'default' => '',
            ),

            array(
                'title'   => __( 'Auto-Responder Email Subject', 'propertyhive' ),
                'id'      => 'propertyhive_enquiry_auto_responder_email_subject',
                'type'    => 'text',
                'css'         => 'min-width:300px;',
            ),

            array(
                'title'   => __( 'Auto-Responder Email Body', 'propertyhive' ),
                'id'      => 'propertyhive_enquiry_auto_responder_email_body',
                'type'    => 'textarea',
                'css'         => 'min-width:300px; height:110px;',
            ),

            array( 'type' => 'sectionend', 'id' => 'enquiry_auto_responder_email_options' )

		) ); // End settings
	}

	/**
	 * Get property match settings settings array
	 *
	 * @return array
	 */
	public function get_property_match_settings() {

		$settings = array();

		$settings[] = array( 'title' => __( 'Property Match Email Settings', 'propertyhive' ), 'type' => 'title', 'id' => 'applicant_match_email_options' );

        $settings[] = array(
            'title'   => __( 'Default Email Subject', 'propertyhive' ),
            'id'      => 'propertyhive_property_match_default_email_subject',
            'type'    => 'text',
            'css'         => 'min-width:300px;',
        );

        $settings[] = array(
            'title'   => __( 'Default Email Body', 'propertyhive' ),
            'id'      => 'propertyhive_property_match_default_email_body',
            'type'    => 'textarea',
            'css'         => 'min-width:300px; height:110px;',
        );

		$settings[] = array(
			'title'   => __( 'Default From Email Address', 'propertyhive' ),
			'id'      => 'propertyhive_property_match_default_from',
			'type'    => 'select',
			'default' => '',
			'css'     => 'min-width:300px;',
			'options' => array(
				'' => __( 'User Email Address', 'propertyhive' ),
				'default_from_email' => __( 'Default "From" Email Address', 'propertyhive' ),
			),
			'desc'    => '<p>' . __( 'This sets the email address that manual matches will be sent from by default. This can still be edited when you go to send the match.<br>Automatic matches, if enabled, will still be sent from the email address of the office that most of the properties in the match belong to.', 'propertyhive' ) . '</p>',
		);

        $options = array();
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'availability', $args );
        
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            { 
            	$options[$term->term_id] = $term->name;
            }
        }
        $settings[] = array(
            'title'   => __( 'Only Include Properties With Statuses', 'propertyhive' ),
            'id'      => 'propertyhive_property_match_statuses',
            'type'    => 'multiselect',
            'css'     => 'min-width:300px; height:110px;',
            'options' => $options,
            'desc'	=> '<p>' . __( 'By default, all on market properties will come back in matches when sending properties to applicants. If you wish to only send properties with a certain status you can choose this here. For example, maybe you don\'t want Sold STC properties to be sent. Hold ctrl/cmd whilst clicking to select multiple.<br>This will also affect the Similar Properties included in the Auto Responder above, if applicable.', 'propertyhive' ) . '</p>',
        );

        $time_offset = (int) get_option('gmt_offset') * 60 * 60;

        $settings[] = array(
            'title'   => __( 'Automatically Send Matching Properties To Applicants', 'propertyhive' ),
            'desc'    => __( 'Enabling this setting will mean applicants will automatically get sent properties.<br><br>
            	- This will only apply to properties added from the moment this option is activated.<br>
            	- When enabled, this can be disabled on a per-applicant basis by going into their record.<br>
            	- When sending out lots of emails we recommend using <a href="https://en-gb.wordpress.org/plugins/tags/smtp" target="_blank">a plugin</a> to send them out using SMTP. Your web developer or hosting company should be able to advise on this.', 'propertyhive' ) . ( ( get_option( 'propertyhive_auto_property_match', '' ) == 'yes' && get_option( 'propertyhive_auto_property_match_enabled_date', '' ) != '' ) ? '<br><br>Enabled on ' . date("jS F Y H:i", strtotime(get_option( 'propertyhive_auto_property_match_enabled_date', '' )) + $time_offset) : '' ),
            'id'      => 'propertyhive_auto_property_match',
            'type'    => 'checkbox',
            'default' => '',
        );

		$settings[] = array( 'type' => 'sectionend', 'id' => 'applicant_match_email_options' );
		    
		return apply_filters( 'propertyhive_property_match_settings', $settings ); // End settings
	}

	/**
	 * Get booking confirmation email settings array
	 *
	 * @return array
	 */
	public function get_booking_confirmation_settings() {
		    
		$settings = array();

		if ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
	    {
	    	//Applicant
	    	$settings[] = array( 'title' => __( 'Applicant Viewing Booking Confirmations', 'propertyhive' ), 'type' => 'title', 'id' => 'applicant_viewing_booking_confirmation_email_options' );

	        $settings[] = array(
	            'title'   => __( 'Default Email Subject', 'propertyhive' ),
	            'id'      => 'propertyhive_viewing_applicant_booking_confirmation_email_subject',
	            'type'    => 'text',
	            'css'         => 'min-width:300px;',
	        );

	        $settings[] = array(
	            'title'   => __( 'Default Email Body', 'propertyhive' ),
	            'id'      => 'propertyhive_viewing_applicant_booking_confirmation_email_body',
	            'type'    => 'textarea',
	            'css'         => 'min-width:300px; height:110px;',
	        );

	        $settings[] = array( 'type' => 'sectionend', 'id' => 'applicant_viewing_booking_confirmation_email_options' );

	        // Owner
	        $settings[] = array( 'title' => __( 'Owner/Landlord Viewing Booking Confirmations', 'propertyhive' ), 'type' => 'title', 'id' => 'owner_viewing_booking_confirmation_email_options' );

	        $settings[] = array(
	            'title'   => __( 'Default Email Subject', 'propertyhive' ),
	            'id'      => 'propertyhive_viewing_owner_booking_confirmation_email_subject',
	            'type'    => 'text',
	            'css'         => 'min-width:300px;',
	        );

	        $settings[] = array(
	            'title'   => __( 'Default Email Body', 'propertyhive' ),
	            'id'      => 'propertyhive_viewing_owner_booking_confirmation_email_body',
	            'type'    => 'textarea',
	            'css'         => 'min-width:300px; height:110px;',
	        );

	        $settings[] = array( 'type' => 'sectionend', 'id' => 'owner_viewing_booking_confirmation_email_options' );

	        // Attending Negotiator
	        $settings[] = array( 'title' => __( 'Attending Negotiator Viewing Booking Confirmations', 'propertyhive' ), 'type' => 'title', 'id' => 'attending_negotiator_viewing_booking_confirmation_email_options' );

	        $settings[] = array(
	            'title'   => __( 'Default Email Subject', 'propertyhive' ),
	            'id'      => 'propertyhive_viewing_attending_negotiator_booking_confirmation_email_subject',
	            'type'    => 'text',
	            'css'         => 'min-width:300px;',
	        );

	        $settings[] = array(
	            'title'   => __( 'Default Email Body', 'propertyhive' ),
	            'id'      => 'propertyhive_viewing_attending_negotiator_booking_confirmation_email_body',
	            'type'    => 'textarea',
	            'css'         => 'min-width:300px; height:110px;',
	        );

	        $settings[] = array( 'type' => 'sectionend', 'id' => 'attending_negotiator_viewing_booking_confirmation_email_options' );
	    }

	    if ( get_option('propertyhive_module_disabled_appraisals', '') != 'yes' )
	    {
	        // Owner
	        $settings[] = array( 'title' => __( 'Owner/Landlord Appraisal Booking Confirmations', 'propertyhive' ), 'type' => 'title', 'id' => 'owner_appraisal_booking_confirmation_email_options' );

	        $settings[] = array(
	            'title'   => __( 'Default Email Subject', 'propertyhive' ),
	            'id'      => 'propertyhive_appraisal_owner_booking_confirmation_email_subject',
	            'type'    => 'text',
	            'css'         => 'min-width:300px;',
	        );

	        $settings[] = array(
	            'title'   => __( 'Default Email Body', 'propertyhive' ),
	            'id'      => 'propertyhive_appraisal_owner_booking_confirmation_email_body',
	            'type'    => 'textarea',
	            'css'         => 'min-width:300px; height:110px;',
	        );

	        $settings[] = array( 'type' => 'sectionend', 'id' => 'owner_appraisal_booking_confirmation_email_options' );
	    }

	    if ( get_option('propertyhive_module_disabled_appraisals', '') != 'yes' || get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
	    {
	    	// Owner
	        $settings[] = array( 'title' => __( 'Booking Confirmations', 'propertyhive' ), 'type' => 'title', 'id' => 'booking_confirmation_email_options' );

	        $settings[] = array(
				'title'   => __( 'Sent From Email Address', 'propertyhive' ),
				'id'      => 'propertyhive_confirmations_default_from',
				'type'    => 'select',
				'default' => '',
				'css'     => 'min-width:300px;',
				'options' => array(
					'' => __( 'Default "From" Email Address', 'propertyhive' ),
					'office' => __( 'Office Email Address', 'propertyhive' ),
					'user' => __( 'User Email Address', 'propertyhive' ),
				),
				'desc'    => '<p>' . __( 'This sets the email address that booking confirmations come from and that will receive the response should someone reply.', 'propertyhive' ) . '</p>',
			);

	        $settings[] = array(
	            'title'   => __( 'Customise Confirmation Emails Before Sending', 'propertyhive' ),
	            'id'      => 'propertyhive_customise_confirmation_emails',
	            'type'    => 'checkbox',
	            'desc' 	  => 'With this ticked you\'ll be able to customise the email subject and body of any notifications before they get sent. This can be useful for adding any appraisal/viewing-specific details to the confirmation. If left unticked, the default subject and body set above will be used.'
	        );

	        $settings[] = array( 'type' => 'sectionend', 'id' => 'booking_confirmation_email_options' );
	    }

		return apply_filters( 'propertyhive_booking_confirmation_settings', $settings  ); // End general map settings
	}

	/**
	 * Get email queue settings array
	 *
	 * @return array
	 */
	public function get_email_queue_settings() {
		    
		return apply_filters( 'propertyhive_email_queue_settings', array(

			array( 'title' => __( 'Email Queue', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'email_queue_options' ),

            array(
                'type'    => 'email_queue'
            ),

			array( 'type' => 'sectionend', 'id' => 'email_queue_options'),

		) ); // End general map settings
	}

	/**
     * Output email queue
     *
     * @access public
     * @return void
     */
    public function email_queue_setting() 
    {
        global $wpdb, $post;

        $additional_query = '';
        $additional_query_string = '';
        if ( isset($_GET['date_from']) && sanitize_text_field($_GET['date_from']) != '' )
        {
        	$additional_query_string .= '&date_from=' . sanitize_text_field($_GET['date_from']);
        	if ( sanitize_text_field($_GET['date_from']) != 'all' )
        	{
	        	$additional_query .= " AND send_at >= '" . sanitize_text_field($_GET['date_from']) . " 00:00:00' ";
	        }
        }
        else
        {
        	// Default to 30 days
        	$additional_query .= " AND send_at >= '" . date("Y-m-d", strtotime('-30 days')) . " 00:00:00' ";
        }
        ?>
        <tr valign="top">
            <td style="padding:0">

            	<p>Some processes, such as property matching, can result in a lot of emails being sent at once. To cater for this we queue emails and send them out in batches at regular intervals.</p>

            	<p>Using this screen you can see which emails have been sent, which have failed, and how many are queued to be sent.</p>

            	<br>

            	<p><strong><?php
            		$next_due = wp_next_scheduled( 'propertyhive_process_email_log' );

                    if ( $next_due == FALSE )
                    {
                        echo 'Whoops. WordPress doesn\'t have the emails automated task scheduled. A quick fix for this is to deactivate, then re-activate the plugin.';
                    }
                    else
                    {
                    	echo __( 'Next scheduled to run at', 'propertyhive' ) . ' ' . date("H:i jS F Y", $next_due);
                    }
            	?></strong> <a href="<?php echo admin_url('admin.php?page=ph-settings&tab=email&section=log&custom_email_log_cron=propertyhive_process_email_log' ); ?>" class="button">Run Now</a></p>

            	<br>

            	<ul class="subsubsub">
            		<?php
	            		$emails = $wpdb->get_var("
							SELECT COUNT(*)
							FROM " . $wpdb->prefix . "ph_email_log
							WHERE
								status <> 'clear'
						");
	            	?>
					<li class="all"><a href="<?php echo esc_url(admin_url('admin.php?page=ph-settings&tab=email&section=log' . $additional_query_string)); ?>"<?php if ( !isset($_GET['status']) || (isset($_GET['status']) && $_GET['status'] == '') ) { echo ' class="current"'; } ?>>All <span class="count">(<?php echo number_format($emails); ?>)</span></a> |</li>
					<?php
	            		$emails = $wpdb->get_var("
							SELECT COUNT(*)
							FROM " . $wpdb->prefix . "ph_email_log
							WHERE 
								status = ''
						");
	            	?>
					<li class="queued"><a href="<?php echo esc_url(admin_url('admin.php?page=ph-settings&tab=email&section=log&status=queued' . $additional_query_string)); ?>"<?php if ( isset($_GET['status']) && $_GET['status'] == 'queued' ) { echo ' class="current"'; } ?>>Queued <span class="count">(<?php echo number_format($emails); ?>)</span></a> |</li>
					<?php
            		$emails = $wpdb->get_var("
						SELECT COUNT(*)
						FROM " . $wpdb->prefix . "ph_email_log
						WHERE 
							status IN ('fail1', 'fail2')
					");
            	?>
					<li class="failed"><a href="<?php echo esc_url(admin_url('admin.php?page=ph-settings&tab=email&section=log&status=failed' . $additional_query_string)); ?>"<?php if ( isset($_GET['status']) && $_GET['status'] == 'failed' ) { echo ' class="current"'; } ?>>Failed <span class="count">(<?php echo number_format($emails); ?>)</span></a> |</li>
					<?php
            		$emails = $wpdb->get_var("
						SELECT COUNT(*)
						FROM " . $wpdb->prefix . "ph_email_log
						WHERE 
							status = 'sent'
					");
            	?>
					<li class="sent"><a href="<?php echo esc_url(admin_url('admin.php?page=ph-settings&tab=email&section=log&status=sent' . $additional_query_string)); ?>"<?php if ( isset($_GET['status']) && $_GET['status'] == 'sent' ) { echo ' class="current"'; } ?>>Sent <span class="count">(<?php echo number_format($emails); ?>)</span></a></li>
				</ul>

				<div class="tablenav top">
					<div class="alignleft actions bulkactions">
						<div class="alignleft actions">
							<input type="hidden" name="page" value="ph-settings">
							<input type="hidden" name="tab" value="email">
							<input type="hidden" name="section" value="log">
							<input type="hidden" name="status" value="<?php echo ( ( isset($_GET['status']) ) ? esc_attr(ph_clean($_GET['status'])) : '' ); ?>">
							<select name="date_from" id="dropdown_date_from">
								<option value="<?php echo esc_attr(date("Y-m-d", strtotime("-7 days"))); ?>"<?php if ( isset($_GET['date_from']) && $_GET['date_from'] == date("Y-m-d", strtotime("-7 days")) ) { echo ' selected'; } ?>>Last 7 Days</option>
								<option value="<?php echo esc_attr(date("Y-m-d", strtotime("-14 days"))); ?>"<?php if ( isset($_GET['date_from']) && $_GET['date_from'] == date("Y-m-d", strtotime("-14 days")) ) { echo ' selected'; } ?>>Last 14 Days</option>
								<option value="<?php echo esc_attr(date("Y-m-d", strtotime("-30 days"))); ?>"<?php if ( !isset($_GET['date_from']) || ( isset($_GET['date_from']) && $_GET['date_from'] == date("Y-m-d", strtotime("-30 days")) ) ) { echo ' selected'; } ?>>Last 30 Days</option>
								<option value="all"<?php if ( isset($_GET['date_from']) && $_GET['date_from'] == 'all' ) { echo ' selected'; } ?>>All Time</option>
							</select>
							<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter">

							<script>
								jQuery(document).ready(function()
								{
									jQuery('#_wpnonce').remove();
									jQuery('input[name=\'_wp_http_referer\']').remove();
									jQuery('#mainform').attr('method', 'get');
									jQuery('#mainform').attr('action', '<?php echo admin_url('admin.php'); ?>');
								});
							</script>
						</div>
					</div>
				</div>

                <table class="ph_email_queue widefat" cellspacing="0">
                    <thead>
                        <tr>
                        	<th class="date-time"><?php echo esc_html(__( 'Date/Time', 'propertyhive' )); ?></th>
                        	<th class="recipient"><?php echo esc_html(__( 'Recipient', 'propertyhive' )); ?></th>
                            <th class="subject"><?php echo esc_html(__( 'Subject', 'propertyhive' )); ?></th>
                            <th class="status"><?php echo esc_html(__( 'Status', 'propertyhive' )); ?></th>
                            <th class="actions">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                   	<?php
                   		$query = "
							SELECT
								email_id,
								contact_id,
								to_email_address,
								subject,
								status,
								send_at
							FROM " . $wpdb->prefix . "ph_email_log
							WHERE 
								1=1 ";
						if ( isset($_GET['status']) )
						{
							switch ( ph_clean($_GET['status']) )
							{
								case "queued": { $query .= " AND status = '' "; break; }
								case "failed": { $query .= " AND status IN ('fail1', 'fail2') "; break; }
								case "sent": { $query .= " AND status = 'sent' "; break; }
								default: { $query .= " AND status <> 'clear' "; }
							}
						}
						else
						{
							$query .= " AND status <> 'clear' ";
						}
						$query .= $additional_query;

						$query .= " ORDER BY send_at DESC
							LIMIT 250
						";
                   		$emails = $wpdb->get_results( $query );

                   		if ( is_array($emails) && !empty($emails) )
                   		{
							foreach ( $emails as $email ) 
							{
						?>
						<tr>
	                    	<td class="date-time"><?php echo esc_html(date("jS M Y H:i", strtotime($email->send_at))); ?></td>
	                    	<td class="recipient"><?php echo '<a href="' . get_edit_post_link($email->contact_id) . '">' . esc_html(get_the_title($email->contact_id)) . '</a><br>' . esc_html($email->to_email_address); ?></td>
	                        <td class="subject"><?php echo esc_html($email->subject); ?></td>
	                        <td class="status"><?php
	                        	switch ($email->status)
	                        	{
	                        		case "fail1": { echo 'First attempt failed. Will retry'; break; }
	                        		case "fail2": { echo 'Failed after 2 attempts'; break; }
	                        		case "sent": { echo 'Sent'; break; }
	                        		default: { echo 'Queued'; }
	                        	}
	                        ?></td>
	                        <td class="actions">
	                        	<a href="<?php echo wp_nonce_url( admin_url('?view_propertyhive_email=' . $email->email_id . '&email_id=' . $email->email_id ), 'view-email' ) ?>" target="_blank" class="button">View Email</a>
	                        	<?php if ( $email->status == '' ) { ?><a onclick='var confirmBox = confirm("Are you sure you wish to remove this email from the queue?\n\nPlease note it will still show on the applicant record that the match has been performed and that any properties included in this email have been sent to them."); return confirmBox;' href="<?php echo wp_nonce_url( admin_url('admin.php?page=ph-settings&tab=email&section=log&delete_propertyhive_queued_email=' . $email->email_id . '&email_id=' . $email->email_id . ( isset($_GET['status']) && in_array($_GET['status'], array('queued', 'failed', 'sent')) ? '&status=' . sanitize_text_field($_GET['status']) : '' ) ), 'delete-email' ) ?>" class="button">Remove From Queue</a><?php } ?>
	                        </td>
	                    </tr>
						<?php
							}
						}
						else
						{
					?>
					<tr>
						<td colspan="5">No emails found</td>
					</tr>
					<?php
						}
                   	?>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php
    }

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section, $hide_save_button;

		if ( $current_section ) 
        {
        	switch ($current_section)
            {
            	case "enquiry-auto-responder": { $settings = $this->get_enquiry_autoresponder_settings(); break; }
            	case "match": { $settings = $this->get_property_match_settings();  break; }
            	case "booking-confirmation": { $settings = $this->get_booking_confirmation_settings(); break; }
            	case "log": { $settings = $this->get_email_queue_settings(); $hide_save_button = true; break; }
                default: { die("Unknown setting section"); }
            }
        }
        else
        {
        	$settings = $this->get_settings(); 
        }

		PH_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() 
	{
		global $current_section;

		if ( $current_section != '' ) 
        {
        	switch ($current_section)
        	{
        		case 'enquiry-auto-responder':
				{
					$settings = $this->get_enquiry_autoresponder_settings();

					PH_Admin_Settings::save_fields( $settings );
					break;
				}
				case 'match':
				{
					$settings = $this->get_property_match_settings();

					$previous_auto_property_match = get_option( 'propertyhive_auto_property_match', '' );

					PH_Admin_Settings::save_fields( $settings );

					if ( isset($_POST['propertyhive_auto_property_match']) && $_POST['propertyhive_auto_property_match'] == '1' )
					{
						if ( $previous_auto_property_match != 'yes' )
						{
							// it's been activated
							update_option( 'propertyhive_auto_property_match_enabled_date', date("Y-m-d H:i:s"), FALSE);
						}

						$timestamp = wp_next_scheduled( 'propertyhive_auto_email_match' );
			            wp_unschedule_event($timestamp, 'propertyhive_auto_email_match' );
			            wp_clear_scheduled_hook('propertyhive_auto_email_match');

						$recurrence = apply_filters( 'propertyhive_auto_email_match_cron_recurrence', 'daily' );
						if ( $recurrence != 'hourly' )
						{
							$timestamp = strtotime( 'tomorrow +2hours' ) - ( (int)get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
						}
						else
						{
							$timestamp = strtotime( '+1 hours' );
						}
						wp_schedule_event( 
							apply_filters( 'propertyhive_auto_email_match_cron_timestamp', $timestamp ), 
							$recurrence, 
							'propertyhive_auto_email_match' 
						);
					}
					else
					{
						wp_clear_scheduled_hook( 'propertyhive_auto_email_match' );
					}
					break;
				}
				case 'booking-confirmation':
				{
					$settings = $this->get_booking_confirmation_settings();

					PH_Admin_Settings::save_fields( $settings );
					break;
				}
				default: { die("Unknown setting section"); }
			}
		}
		else
		{
			PH_Admin_Settings::save_fields( $this->get_settings() );
		}
	}
}

endif;

return new PH_Settings_Emails();