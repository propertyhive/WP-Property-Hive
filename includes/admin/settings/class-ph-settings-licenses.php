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
		add_action( 'propertyhive_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'propertyhive_admin_field_pro_features', array( $this, 'pro_features_setting' ) );
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			'' => __( 'Activated Features', 'propertyhive' ),
			'licensekey' => __( 'License Key', 'propertyhive' ),
		);
		return apply_filters( 'propertyhive_get_sections_' . $this->id, $sections );
	}

	public function get_pro_settings() {

        $current_settings = get_option( 'propertyhive_pro', array() );

        $settings = array(

            array( 'title' => __( 'Active Features', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'pro_active_features_settings' )

        );

        $settings[] = array(
            'type' => 'pro_features',
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'pro_active_features_settings');

        return $settings;
    }

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_license_key_settings() {

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

		$settings = apply_filters( 'propertyhive_licenses_settings', array(

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

	public function pro_features_setting()
    {
        $features = get_ph_pro_features();

        echo '<div class="pro-feature-settings">';

        // filters

        // list of features
        echo '<ul>';
        foreach ( $features as $feature )
        {
            $feature_status = false;

            if ( is_dir( WP_PLUGIN_DIR . '/' . $feature['slug'] ) )
            {
                $feature_status = 'installed';
            }
            if ( is_plugin_active( $feature['plugin'] ) ) 
            {
                $feature_status = 'active';
            }

            echo '<li>
                <div class="inner">
                    <h3>' . ( ( isset($feature['icon']) && !empty($feature['icon']) ) ? '<span class="dashicons ' . $feature['icon'] . '"></span> ' : '' ) . $feature['name'] . '</h3>' . 
                    ( ( isset($feature['pro']) && $feature['pro'] === true ) ? '<span class="pro">PRO</span>' : '' ) . 
                    ( ( isset($feature['description']) && !empty($feature['description']) ) ? '<p>' . $feature['description'] . '</p>' : '' ) . '
                    <p>
                    ' . ( ( isset($feature['url']) && !empty($feature['url']) ) ? '<a href="' . $feature['url'] . '" target="_blank">' . __( 'Read More', 'propertyhive' ) . '</a>' : '' ) . 
                    ( ( isset($feature['docs_url']) && !empty($feature['docs_url']) ) ? '&nbsp;&nbsp;|&nbsp;&nbsp;<a href="' . $feature['docs_url'] . '" target="_blank">' . __( 'Docs', 'propertyhive' ) . '</a>' : '' ) . 
                    '</p>';

                
                if ( $feature_status == 'active' )
                {
                    $transient = get_site_transient( 'update_plugins' );
                    if ( isset($transient->response) && is_array($transient->response) && isset($transient->response[$feature['plugin']]) && isset($transient->response[$feature['plugin']]->new_version) )
                    {
                        $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $feature['plugin'] );

                        if ( version_compare($plugin_data['Version'], $transient->response[$feature['plugin']]->new_version, '<') )
                        {
                            echo '<div style="float:right"><a href="' . admin_url('update-core.php') . '" style="text-decoration:none"><span class="dashicons dashicons-update"></span> Update available</a></div>';
                        }
                    }
                }

                echo '<label class="switch">
                  <input type="checkbox" name="active_plugins[]" value="' . $feature['slug'] . '"' . ( $feature_status == 'active' ? ' checked' : '' ) . '>
                  <span class="slider round"></span>
                </label>';

                echo '<div class="loading"><img src="' . PH()->plugin_url() . '/assets/images/admin/loading.gif" alt=""></div>';

                echo '</div>';
            echo '</li>';
        }
        echo '</ul><div style="clear:both"></div>';

        echo '</div>';
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
            	case "licensekey": { $settings = $this->get_license_key_settings(); break; }
            	default: { die("Unknown setting section"); }
            }
        }
        else
        {
        	$hide_save_button = true;
        	$settings = $this->get_pro_settings(); 
        }

		PH_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {

		if ( $current_section != '' ) 
        {
        	switch ($current_section)
        	{
        		case 'licensekey':
				{
					$settings = $this->get_license_key_settings();

					PH_Admin_Settings::save_fields( $settings );

					update_option( 'missing_invalid_expired_license_key_notice_dismissed', '' );

					PH()->license->ph_check_licenses(true);
					break;
				}
				default: { die("Unknown setting section"); }
			}
		}
		else
		{
			//$settings = $this->get_settings();

			//PH_Admin_Settings::save_fields( $settings );
		}
		
	}
}

endif;

return new PH_Settings_Licenses();