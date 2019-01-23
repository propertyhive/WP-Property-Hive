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
		);

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

			array( 'title' => __( 'Enquiry Auto Responder Settings', 'propertyhive' ), 'type' => 'title', 'id' => 'enquiry_auto_responder_email_options' ),

            array(
                'title'   => __( 'Auto Responder Enabled', 'propertyhive' ),
                'id'      => 'propertyhive_enquiry_auto_responder',
                'type'    => 'checkbox',
                'default' => '',
            ),

            array(
                'title'   => __( 'Auto Responder Email Subject', 'propertyhive' ),
                'id'      => 'propertyhive_enquiry_auto_responder_email_subject',
                'type'    => 'text',
                'css'         => 'min-width:300px;',
            ),

            array(
                'title'   => __( 'Auto Responder Email Body', 'propertyhive' ),
                'id'      => 'propertyhive_enquiry_auto_responder_email_body',
                'type'    => 'textarea',
                'css'         => 'min-width:300px; height:110px;',
            ),

            array( 'type' => 'sectionend', 'id' => 'enquiry_auto_responder_email_options' )

        );

		if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
	    {
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
	            'title'   => __( 'Automatically Send Matching Properties To Applicants', 'propertyhive' ),
	            'desc'    => __( 'Enabling this setting will mean applicants will automatically get sent emailed properties as they\'re added.<br><br>
	            	- This will only apply to properties added from the moment this option is activated.<br>
	            	- When enabled, this can disabled on a per-applicant basis by going into their record<br>
	            	- When sending out lots of emails we recommend using <a href="https://en-gb.wordpress.org/plugins/tags/smtp" target="_blank">a plugin</a> to send them out using SMTP. Your web developer or hosting company should be able to advise on this.', 'propertyhive' ),
	            'id'      => 'propertyhive_auto_property_match',
	            'type'    => 'checkbox',
	            'default' => '',
	        );

			$settings[] = array( 'type' => 'sectionend', 'id' => 'applicant_match_email_options' );
		}

		if ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
	    {
	    	$settings[] = array( 'title' => __( 'Viewing Booking Confirmations', 'propertyhive' ), 'type' => 'title', 'id' => 'viewing_booking_confirmation_email_options' );

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

	        $settings[] = array( 'type' => 'sectionend', 'id' => 'viewing_booking_confirmation_email_options' );
	    }

		$settings = apply_filters( 'propertyhive_email_settings', $settings );

		return apply_filters( 'propertyhive_get_settings_' . $this->id, $settings );
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
        	// Default to 7 days
        	$additional_query .= " AND send_at >= '" . date("Y-m-d", strtotime('-7 days')) . " 00:00:00' ";
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
								method = 'email'
						");
	            	?>
					<li class="all"><a href="<?php echo admin_url('admin.php?page=ph-settings&tab=email&section=log' . $additional_query_string); ?>"<?php if ( !isset($_GET['status']) || (isset($_GET['status']) && $_GET['status'] == '') ) { echo ' class="current"'; } ?>>All <span class="count">(<?php echo number_format($emails); ?>)</span></a> |</li>
					<?php
	            		$emails = $wpdb->get_var("
							SELECT COUNT(*)
							FROM " . $wpdb->prefix . "ph_email_log
							WHERE 
								method = 'email'
							AND
								status = ''
						");
	            	?>
					<li class="queued"><a href="<?php echo admin_url('admin.php?page=ph-settings&tab=email&section=log&status=queued' . $additional_query_string); ?>"<?php if ( isset($_GET['status']) && $_GET['status'] == 'queued' ) { echo ' class="current"'; } ?>>Queued <span class="count">(<?php echo number_format($emails); ?>)</span></a> |</li>
					<?php
            		$emails = $wpdb->get_var("
						SELECT COUNT(*)
						FROM " . $wpdb->prefix . "ph_email_log
						WHERE 
							method = 'email'
						AND
							status IN ('fail1', 'fail2')
					");
            	?>
					<li class="failed"><a href="<?php echo admin_url('admin.php?page=ph-settings&tab=email&section=log&status=failed' . $additional_query_string); ?>"<?php if ( isset($_GET['status']) && $_GET['status'] == 'failed' ) { echo ' class="current"'; } ?>>Failed <span class="count">(<?php echo number_format($emails); ?>)</span></a> |</li>
					<?php
            		$emails = $wpdb->get_var("
						SELECT COUNT(*)
						FROM " . $wpdb->prefix . "ph_email_log
						WHERE 
							method = 'email'
						AND
							status = 'sent'
					");
            	?>
					<li class="sent"><a href="<?php echo admin_url('admin.php?page=ph-settings&tab=email&section=log&status=sent' . $additional_query_string); ?>"<?php if ( isset($_GET['status']) && $_GET['status'] == 'sent' ) { echo ' class="current"'; } ?>>Sent <span class="count">(<?php echo number_format($emails); ?>)</span></a></li>
				</ul>

				<div class="tablenav top">
					<div class="alignleft actions bulkactions">
						<div class="alignleft actions">
							<input type="hidden" name="page" value="ph-settings">
							<input type="hidden" name="tab" value="email">
							<input type="hidden" name="section" value="log">
							<input type="hidden" name="status" value="<?php echo ( ( isset($_GET['status']) ) ? $_GET['status'] : '' ); ?>">
							<select name="date_from" id="dropdown_date_from">
								<option value="<?php echo date("Y-m-d", strtotime("-7 days")); ?>"<?php if ( isset($_GET['date_from']) && $_GET['date_from'] == date("Y-m-d", strtotime("-7 days")) ) { echo ' selected'; } ?>>Last 7 Days</option>
								<option value="<?php echo date("Y-m-d", strtotime("-14 days")); ?>"<?php if ( isset($_GET['date_from']) && $_GET['date_from'] == date("Y-m-d", strtotime("-14 days")) ) { echo ' selected'; } ?>>Last 14 Days</option>
								<option value="<?php echo date("Y-m-d", strtotime("-30 days")); ?>"<?php if ( isset($_GET['date_from']) && $_GET['date_from'] == date("Y-m-d", strtotime("-30 days")) ) { echo ' selected'; } ?>>Last 30 Days</option>
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
                        	<th class="date-time"><?php _e( 'Date/Time', 'propertyhive' ); ?></th>
                        	<th class="recipient"><?php _e( 'Recipient', 'propertyhive' ); ?></th>
                            <th class="subject"><?php _e( 'Subject', 'propertyhive' ); ?></th>
                            <th class="status"><?php _e( 'Status', 'propertyhive' ); ?></th>
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
								method = 'email' ";
						if ( isset($_GET['status']) )
						{
							switch ( $_GET['status'] )
							{
								case "queued": { $query .= " AND status = '' "; break; }
								case "failed": { $query .= " AND status IN ('fail1', 'fail2') "; break; }
								case "sent": { $query .= " AND status = 'sent' "; break; }
							}
						}
						$query .= $additional_query;

						$query .= " ORDER BY send_at DESC
							LIMIT 250
						";
                   		$emails = $wpdb->get_results( $query );

						foreach ( $emails as $email ) 
						{
					?>
					<tr>
                    	<td class="date-time"><?php echo date("jS M Y H:i", strtotime($email->send_at)); ?></td>
                    	<td class="recipient"><?php echo '<a href="' . get_edit_post_link($email->contact_id) . '">' . get_the_title($email->contact_id) . '</a><br>' . $email->to_email_address; ?></td>
                        <td class="subject"><?php echo $email->subject; ?></td>
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
                        </td>
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
	public function save() {
		PH_Admin_Settings::save_fields( $this->get_settings() );

		if ( isset($_POST['propertyhive_auto_property_match']) && $_POST['propertyhive_auto_property_match'] == '1' )
		{
			update_option( 'propertyhive_auto_property_match_enabled_date', date("Y-m-d H:i:s"), FALSE);

			wp_schedule_event( time(), 'hourly', 'propertyhive_auto_email_match' ); //  Skew it by 30 minutes to reduce conflict with email log processing
		}
		else
		{
			wp_clear_scheduled_hook( 'propertyhive_auto_email_match' );
		}
	}
}

endif;

return new PH_Settings_Emails();