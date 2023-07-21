<?php
/**
 * PropertyHive License Settings
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Settings_Licenses' ) ) :

/**
 * PH_Settings_Licenses.
 */
class PH_Settings_Licenses extends PH_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'licensekey';
		$this->label = __( 'PRO / License Key', 'propertyhive' );

		add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$license = PH()->license->get_current_license();

		$output = '';
		$input_border_color = '';
		$renew_link = '<a href="https://wp-property-hive.com/license-key/" target="_blank">' . __( 'Renew License', 'propertyhive' ) . '</a>';
		$valid_license = false;

		if ( is_array($license) && empty($license) && get_option( 'propertyhive_license_key', '' ) != '' )
		{
			$output = '<span style="color:#900">' . __( 'Invalid license key entered. If you\'re seeing this message and the license key is correct please contact Property Hive support.', 'propertyhive' ) . '</span>';
			$input_border_color = '#900';

			if ( get_option( 'propertyhive_license_key_error', '' ) != '' )
			{
				$output .= ' <span style="color:#900">' . esc_html(get_option( 'propertyhive_license_key_error', '' )) . '</span>';
			}
		}
		elseif ( isset($license['active']) && $license['active'] != '1' )
		{
			$output = '<span style="color:#900">' . __( 'License inactive.', 'propertyhive' ) . '</span>';
			$input_border_color = '#900';
		}
		else
		{
			if ( isset($license['expires_at']) && $license['expires_at'] != '' )
			{
				if ( (strtotime($license['expires_at']) + 86400) <= time() )
				{
					// Expired
					$output = '<span style="color:#900">' . __( 'License expired on ' . date("jS F Y", strtotime($license['expires_at'])), 'propertyhive' ) . '. ' . $renew_link . '</span>';
					$input_border_color = '#900';
				}
				else
				{
					// Valid
					$output = '<span style="color:#090">' . __( 'License valid', 'propertyhive') . '.</span>';
					$input_border_color = '#090';
					$valid_license = true;
				}
			}
		}

		$settings = array(

			array( 'title' => __( 'License Key', 'propertyhive' ), 'type' => 'title', 'id' => 'license_options' ),

		);

		$default_license_key_type = 'pro';
		if ( get_option('propertyhive_license_key') != '' )
		{
			$default_license_key_type = 'old';
		}

		$settings[] = array(
			'type'        => 'html',
			'html' 		  => __( '<p>By having a license key you will benefit from priority one-to-one email support, setup assistance, troubleshooting, and updates to purchased add ons containing new functionality and fixes.</p>

				<p>Licenses are valid for 12 months from the date of purchase and exist on a per-site-basis. Should a license key not exist, your website will still function as it does now. However support will be limited and potentially slower than usual and you won’t receive updates to any purchased add ons.</p>

				' . ( (!$valid_license) ? '<br><p><a href="https://wp-property-hive.com/license-key/" class="button button-primary" target="_blank">Purchase License Key</a></p>' : '' ), 'propertyhive' ),
		);

		$settings[] = array(
			'title'       => __( 'License Key', 'propertyhive' ),
			'id'          => 'propertyhive_license_key',
			'type'        => 'text',
			'css'         => 'min-width:300px; border:1px solid ' . $input_border_color,
			'desc' 		  => $output,
		);

		$settings[] = array( 'type' => 'sectionend', 'id' => 'license_options' );

		return apply_filters( 'propertyhive_get_settings_' . $this->id, $settings );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section, $hide_save_button;

        $settings = $this->get_settings();

		PH_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {

		$settings = $this->get_license_key_settings();

		PH_Admin_Settings::save_fields( $settings );

		update_option( 'missing_invalid_expired_license_key_notice_dismissed', '' );

		PH()->license->ph_check_licenses(true);
	}
}

endif;

return new PH_Settings_Licenses();