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
		$this->label = __( 'License', 'propertyhive' );

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

		$license_type = PH()->license->get_license_type();

		// get old license information
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

		// get new license information
		$valid_pro_license = false;
		$pro_input_border_color = '';
		$pro_output = '';
		if (get_option('propertyhive_pro_license_key', '') != '') 
		{ 
			if (PH()->license->is_valid_pro_license_key())
			{
				$pro_input_border_color = '#090';
			}
			else
			{
				$pro_license = PH()->license->get_current_pro_license();
				$pro_input_border_color = '#900'; 
				$pro_output = $pro_license['error'];
			}
		}
		

		$settings = array(

			array( 'title' => __( 'License Key', 'propertyhive' ), 'type' => 'title', 'id' => 'license_options' ),

		);

		$settings[] = array(
			'title'       => __( 'License Type', 'propertyhive' ),
			'id'          => 'propertyhive_license_type',
			'type'        => 'radio',
			'default' 	  => $license_type,
			'options'     => array(
				'pro' => 'Pro',
				'old' => 'Old-Style (deprecated 1st September 2023)'
			)
		);

		$settings[] = array(
			'type'        => 'html',
			'id' 		  => 'pro_license_key_info',
			'html' 		  => __( '<p>With a Pro license key you\'ll unlock a wide array of Property Hive functionality. We offer multiple packages to suit your needs. Your Pro subscription details and license key can be found within the \'<a href="https://wp-property-hive.com/my-account/" target="_blank">My Account</a>\' section of our website.</p>
							' . ( (!$valid_pro_license) ? 
									'<br><p><a href="https://wp-property-hive.com/pricing/" class="button button-primary" target="_blank">Get PRO</a></p>' : 
									'<br><p><a href="https://wp-property-hive.com/my-account/" class="button button-primary" target="_blank">Manage Subscription and Get License Key</a></p>' 
								), 'propertyhive' ) . '
			<input type="hidden" name="pro_license_key_action" value="' . ( PH()->license->is_valid_pro_license_key() ? 'deactivate' : 'activate' ) . '">',
		);

		$settings[] = array(
			'title'       => __( 'License Key', 'propertyhive' ),
			'id'          => 'propertyhive_pro_license_key',
			'type'        => 'text',
			'css'         => 'min-width:350px; border:1px solid ' . $pro_input_border_color,
			'desc' 		  => $pro_output,
		);

		$settings[] = array(
			'type'        => 'html',
			'id' 		  => 'license_key_info',
			'html' 		  => __( '<p>If you purchased a license key prior to 1st September 2023 you can enter it here to benefit from updates to any existing add ons you\'ve purchased.</p>
				<p>We\'ve since moved to a new and improved <a href="https://wp-property-hive.com/pricing/" target="_blank">pro pricing model</a> that is more cost effective for you and gives access to more features.</p>
				' . ( $license_type == 'old' ? '<p>To switch your existing license key over to the new pricing model please get in touch at <a href="mailto:info@wp-property-hive.com.">info@wp-property-hive.com</a>.</p>' : '' ) . '
				<br><p><a href="https://wp-property-hive.com/pricing/" class="button button-primary" target="_blank">Get PRO</a></p>', 'propertyhive' ),
		);

		$settings[] = array(
			'title'       => __( 'License Key', 'propertyhive' ),
			'id'          => 'propertyhive_license_key',
			'type'        => 'text',
			'css'         => 'min-width:350px; border:1px solid ' . $input_border_color,
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

		$settings = $this->get_settings();

		PH_Admin_Settings::save_fields( $settings );

		update_option( 'missing_invalid_expired_license_key_notice_dismissed', '' );

		if ( $_POST['propertyhive_license_type'] == 'pro' && isset($_POST['propertyhive_pro_license_key']) && !empty(ph_clean($_POST['propertyhive_pro_license_key'])) && $_POST['pro_license_key_action'] == 'activate' )
		{
			$return = PH()->license->activate_pro_license_key();
			if ( $return['success'] === false )
			{
				PH_Admin_Settings::add_error($return['error']);
			}
			else
			{
				PH_Admin_Settings::add_message('License key activated successfully');
			}
		}

		if ( $_POST['propertyhive_license_type'] == 'pro' && isset($_POST['propertyhive_pro_license_key']) && !empty(ph_clean($_POST['propertyhive_pro_license_key'])) && $_POST['pro_license_key_action'] == 'deactivate' )
		{
			$return = PH()->license->deactivate_pro_license_key();
			if ( $return['success'] === false )
			{
				PH_Admin_Settings::add_error($return['error']);
			}
			else
			{
				PH_Admin_Settings::add_message('License key deactivated successfully');
			}
		}

		if ( $_POST['propertyhive_license_type'] == 'old' )
		{
			PH()->license->ph_check_licenses(true);
		}
	}
}

endif;

return new PH_Settings_Licenses();