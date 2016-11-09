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
		add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );
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
		return apply_filters( 'propertyhive_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters( 'propertyhive_email_settings', array(

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

			array( 'title' => __( 'Property Match Email Settings', 'propertyhive' ), 'type' => 'title', 'id' => 'applicant_match_email_options' ),

            array(
                'title'   => __( 'Default Email Subject', 'propertyhive' ),
                'id'      => 'propertyhive_property_match_default_email_subject',
                'type'    => 'text',
                'css'         => 'min-width:300px;',
            ),

            array(
                'title'   => __( 'Default Email Body', 'propertyhive' ),
                'id'      => 'propertyhive_property_match_default_email_body',
                'type'    => 'textarea',
                'css'         => 'min-width:300px; height:110px;',
            ),

            array(
                'title'   => __( 'Automatically Send Matching Properties To Applicants', 'propertyhive' ),
                'desc'    => __( 'Enabling this setting will mean applicants will automatically get sent emailed properties as they\'re added.<br><br>
                	- This will only apply to properties added from the moment this option is activated.<br>
                	- When enabled, this can disabled on a per-applicant basis by going into their record<br>
                	- When sending out lots of emails we recommend using <a href="https://en-gb.wordpress.org/plugins/tags/smtp" target="_blank">a plugin</a> to send them out using SMTP. Your web developer or hosting company should be able to advise on this.', 'propertyhive' ),
                'id'      => 'propertyhive_auto_property_match',
                'type'    => 'checkbox',
                'default' => '',
            ),

			array( 'type' => 'sectionend', 'id' => 'applicant_match_email_options' ),

		) );

		return apply_filters( 'propertyhive_get_settings_' . $this->id, $settings );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings();
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