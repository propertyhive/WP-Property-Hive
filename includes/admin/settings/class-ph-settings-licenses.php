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
		$this->label = __( 'License Key', 'propertyhive' );

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
			'' => __( 'License Key', 'propertyhive' ),
		);
		return apply_filters( 'propertyhive_get_sections_' . $this->id, $sections );
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
		$renew_link = '<a href=https://wp-property-hive.com/product/12-month-license-key/"" target="_blank">' . __( 'Renew License', 'propertyhive' ) . '</a>';
		$valid_license = false;

		if ( is_array($license) && empty($license) && get_option( 'propertyhive_license_key', '' ) != '' )
		{
			$output = '<span style="color:#900">' . __( 'Invalid license key entered. If you\'re seeing this message and the license key is correct please contact Property Hive support.', 'propertyhive' ) . '</span>';
			$input_border_color = '#900';
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
				if ( strtotime($license['expires_at']) <= time() )
				{
					// Expired
					$output = '<span style="color:#900">' . __( 'License expired on ' . date("jS F Y", strtotime($license['expires_at'])), 'propertyhive' ) . '. ' . $renew_link . '</span>';
					$input_border_color = '#900';
				}
				elseif ( 
					strtotime($license['expires_at']) > time() &&
					strtotime($license['expires_at']) < (time() + 30 * 24 * 60 * 60)
				)
				{
					// Expires in less than 30 days
					$output = '<span style="color:#F90">' . __( 'License expires on ' . date("jS F Y", strtotime($license['expires_at'])), 'propertyhive' ) . '. ' . $renew_link . '</span>';
					$input_border_color = '#F90';
				}
				elseif (strtotime($license['expires_at']) > time())
				{
					// Valid
					$output = '<span style="color:#090">' . __( 'License valid. Expires on ' . date("jS F Y", strtotime($license['expires_at'])), 'propertyhive' ) . '.</span>';
					$input_border_color = '#090';
					$valid_license = true;
				}
			}
		}

		$settings = apply_filters( 'propertyhive_licenses_settings', array(

			array( 'title' => __( 'License Key', 'propertyhive' ), 'type' => 'title', 'id' => 'license_options' ),

			array(
				'type'        => 'html',
				'html' 		  => __( '<p>By having a license key you will benefit from priority one-to-one email support, setup assistance, troubleshooting, and updates to purchased add ons containing new functionality and fixes.</p>

					<p>Licenses are valid for 12 months from the date of purchase and exist on a per-site-basis. Should a license key not exist, your website will still function as it does now. However support will be limited and potentially slower than usual and you won’t receive updates to any purchased add ons.</p>

					' . ( (!$valid_license) ? '<br><p><a href="https://wp-property-hive.com/product/12-month-license-key/" class="button button-primary" target="_blank">Purchase License Key</a></p>' : '' ), 'propertyhive' ),
			),

			array(
				'title'       => __( 'License Key', 'propertyhive' ),
				'id'          => 'propertyhive_license_key',
				'type'        => 'text',
				'css'         => 'min-width:300px; border:1px solid ' . $input_border_color,
				'desc' 		  => $output,
			),

			array( 'type' => 'sectionend', 'id' => 'license_options' ),

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

		PH()->license->ph_check_licenses(true);
	}
}

endif;

return new PH_Settings_Licenses();