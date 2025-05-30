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
		if ( $license_type == '' )
		{
			$license_type = 'pro';
		}

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
		$license_key_to_display = '';
		if (get_option('propertyhive_pro_license_key', '') != '') 
		{ 
			$length = strlen(get_option('propertyhive_pro_license_key', '')); 
			$license_key_to_display = get_option('propertyhive_pro_license_key', '');
			if ( $length >= 6 )
			{
				$license_key_to_display = get_option('propertyhive_pro_license_key', '')[0] . 
				get_option('propertyhive_pro_license_key', '')[1] . 
				get_option('propertyhive_pro_license_key', '')[2] . 
				str_repeat('*', $length - 4) . 
				get_option('propertyhive_pro_license_key', '')[$length-3] . 
				get_option('propertyhive_pro_license_key', '')[$length-2] . 
				get_option('propertyhive_pro_license_key', '')[$length-1]; 
			}

			if ( PH()->license->is_valid_pro_license_key(true) )
			{
				$valid_pro_license = true;				
				// to be used for displaying subscription level in future
				//$product_id_and_package = PH()->license->get_pro_license_product_id_and_package();
				$pro_input_border_color = '#090';
			}
			else
			{
				$pro_license = PH()->license->get_current_pro_license();
				$pro_input_border_color = '#900'; 
				$pro_output = ( isset($pro_license['error']) ? $pro_license['error'] : '' );
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
				'old' => 'Old-Style (license keys purchased before October 2023)'
			)
		);

		$settings[] = array(
			'type'        => 'html',
			'id' 		  => 'pro_license_key_info',
			'html' 		  => __( '<p>With a Pro license subscription you\'ll unlock a wide array of Property Hive functionality. We offer multiple packages to suit your needs. Your Pro subscription details and license key can be found within the \'<a href="https://wp-property-hive.com/my-account/" target="_blank">My Account</a>\' section of our website.</p>
							' . ( (!$valid_pro_license) ? 
									'<br><p><a href="https://wp-property-hive.com/pricing/?src=plugin-license-settings" class="button button-primary" target="_blank">Get PRO</a></p>' : 
									'<br><p>
										<a href="' . admin_url('admin.php?page=ph-settings&tab=features') . '" class="button button-primary">Activate Features</a>
										<a href="https://wp-property-hive.com/my-account/subscriptions/?src=wordpress-license-tab" class="button button" target="_blank">Manage Subscription</a>
									</p>'
								), 'propertyhive' ) . '
			<input type="hidden" name="pro_license_key_action" value="' . ( $valid_pro_license ? 'deactivate' : 'activate' ) . '">',
		);

		if ( $valid_pro_license )
		{
			$settings[] = array(
				'type'        => 'html',
				'id' 		  => 'pro_license_key_display',
				'title'		  => __( 'License Key', 'propertyhive' ),
				'html' 		  => '<input type="text" disabled="disabled" value="' . $license_key_to_display . '" style="min-width:350px; border:1px solid ' . $pro_input_border_color . '"> ' . $pro_output
			);

			$settings[] = array(
				'title'       => __( 'License Key', 'propertyhive' ),
				'id'          => 'propertyhive_pro_license_key',
				'type'        => 'hidden'
			);
		}
		else
		{
			$settings[] = array(
				'title'       => __( 'License Key', 'propertyhive' ),
				'id'          => 'propertyhive_pro_license_key',
				'type'        => 'text',
				'css' 	  => 'min-width:350px; border:1px solid ' . $pro_input_border_color,
				'desc' 		  => $pro_output
			);
		}

		$settings[] = array(
		    'type'        => 'html',
		    'id'          => 'license_key_info',
		    'html'        => sprintf(
		        __( '<p>If you purchased a license key prior to 16th October 2023, you can enter it here to benefit from updates to any existing add-ons you\'ve purchased.</p>
		            <p>We\'ve since moved to a new and improved <a href="%1$s" target="_blank">pro pricing model</a> that is more cost effective for you and gives access to more features.</p>
		            %2$s
		            <br><p><a href="%1$s" class="button button-primary" target="_blank">Get PRO</a></p>', 'propertyhive' ),
		        esc_url('https://wp-property-hive.com/pricing/?src=plugin-license-settings'),
		        $license_type == 'old' ? '<p>' . sprintf( __( 'To switch your existing license key over to the new pricing model, please get in touch at <a href="mailto:%s">%s</a>.', 'propertyhive' ), 'info@wp-property-hive.com', 'info@wp-property-hive.com' ) . '</p>' : ''
		    ),
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