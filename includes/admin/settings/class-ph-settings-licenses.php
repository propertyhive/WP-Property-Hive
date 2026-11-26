<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Settings_Licenses; preserving the existing PH_* class name is required for plugin and extension compatibility.
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

		$stored_pro_key = (string) get_option( 'propertyhive_pro_license_key', '' );
		$stored_legacy_key = (string) get_option( 'propertyhive_license_key', '' );

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
					/* translators: %s: License expiry date. */
					$output = '<span style="color:#900">' . sprintf( __( 'License expired on %s', 'propertyhive' ), gmdate( 'jS F Y', strtotime( $license['expires_at'] ) ) ) . '. ' . $renew_link . '</span>';
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

		$legacy_error = get_option( 'propertyhive_license_key_error', '' );
		if ( 'old' === $license_type && '' !== $legacy_error ) {
			$output = '<span style="color:#900">' . esc_html( $legacy_error ) . '</span>';
			$input_border_color = '#900';
			$valid_license = false;
		}

		// get new license information
		$valid_pro_license = false;
		$pro_input_border_color = '';
		$pro_output = '';
		if ( 'pro' === $license_type && get_option('propertyhive_pro_license_key', '') != '' )
		{ 

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
			'html'       => '<p>' . wp_kses_post( __( 'With a Pro license subscription you unlock a wide array of Property Hive functionality. We offer multiple packages to suit your needs. Your Pro subscription details and license key can be found in the <a href="https://wp-property-hive.com/my-account/" target="_blank">My Account</a> section of our website.', 'propertyhive' ) ) . '</p><br><p>' .
                ( ! $valid_pro_license ?
                    '<a href="https://wp-property-hive.com/pricing/?src=plugin-license-settings" class="button button-primary" target="_blank">' . esc_html__( 'Get PRO', 'propertyhive' ) . '</a>' :
                    '<a href="' . esc_url( admin_url( 'admin.php?page=ph-settings&tab=features' ) ) . '" class="button button-primary">' . esc_html__( 'Activate Features', 'propertyhive' ) . '</a> <a href="https://wp-property-hive.com/my-account/subscriptions/?src=wordpress-license-tab" class="button button" target="_blank">' . esc_html__( 'Manage Subscription', 'propertyhive' ) . '</a>'
                ) . '</p><input type="hidden" name="pro_license_key_action" value="' . ( $valid_pro_license ? 'deactivate' : 'activate' ) . '">',
		);

        // Never include a saved key in an editable or hidden input, even if expired.
        $pro_output = $this->redact_license_message( $pro_output );
        $output = $this->redact_license_message( $output );
        if ( '' !== $stored_pro_key ) {
            $settings[] = array(
                'type' => 'html',
                'id' => 'pro_license_key_display',
                'title' => __( 'License Key', 'propertyhive' ),
                'html' => '<input type="text" disabled="disabled" value="' . esc_attr( $this->masked_license_key( $stored_pro_key ) ) . '" style="min-width:350px; border:1px solid ' . esc_attr( $pro_input_border_color ) . '"> <span>' . esc_html( $pro_output ) . '</span>',
            );
        }
        if ( ! $valid_pro_license ) {
            // The generic password renderer would refill this from get_option().
            $settings[] = array(
                'type' => 'html',
                'id' => 'propertyhive_pro_license_key',
                'title' => __( 'License Key', 'propertyhive' ),
                'html' => '<input type="password" name="propertyhive_pro_license_key" id="propertyhive_pro_license_key" value="" autocomplete="new-password" spellcheck="false" style="min-width:350px;">' .
                    ( '' !== $stored_pro_key ? '<p class="description">' . esc_html__( 'Enter a license key, or leave blank to retry activation of the saved key.', 'propertyhive' ) . '</p>' : '' ),
            );
        }

		$legacy_license_message = '';

		if ( 'old' === $license_type ) 
		{
			$legacy_license_message = '<p>' . sprintf(
				/* translators: %s: URL to the support page */
				__( 'To switch your existing license key over to the new pricing model, please <a href="%s">get in touch</a>.', 'propertyhive' ),
				esc_url( 'https://wp-property-hive.com/support' )
			) . '</p>';
		}

		$settings[] = array(
			'type' => 'html',
			'id'   => 'license_key_info',
			'html' => sprintf(
				/* translators: 1: URL to the pricing page, 2: optional message shown to legacy license holders */
				__(
					'<p>If you purchased a license key prior to 16th October 2023, you can enter it here to benefit from updates to any existing add-ons you\'ve purchased.</p>
					<p>We\'ve since moved to a new and improved <a href="%1$s" target="_blank">pro pricing model</a> that is more cost effective for you and gives access to more features.</p>
					%2$s
					<br><p><a href="%1$s" class="button button-primary" target="_blank">Get PRO</a></p>',
					'propertyhive'
				),
				esc_url( 'https://wp-property-hive.com/pricing/?src=plugin-license-settings' ),
				$legacy_license_message
			),
		);

        $settings[] = array(
            'title' => __( 'License Key', 'propertyhive' ),
            'id' => 'propertyhive_license_key',
            'type' => 'html',
            'html' => ( '' !== $stored_legacy_key
                ? '<input type="text" disabled="disabled" value="' . esc_attr( $this->masked_license_key( $stored_legacy_key ) ) . '" style="min-width:350px; border:1px solid ' . esc_attr( $input_border_color ) . '">'
                : '<input type="password" name="propertyhive_license_key" id="propertyhive_license_key" value="" autocomplete="new-password" spellcheck="false" style="min-width:350px;">'
            ) . '<input type="hidden" name="legacy_license_key_action" value="' . ( '' !== $stored_legacy_key ? 'remove' : 'activate' ) . '"><div>' . wp_kses_post( $output ) . '</div>',
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

        $type = PH()->license->get_license_type();
        $GLOBALS['save_button_text'] = 'old' === $type
            ? ( '' !== (string) get_option( 'propertyhive_license_key', '' ) ? __( 'Remove key', 'propertyhive' ) : __( 'Activate key', 'propertyhive' ) )
            : ( PH()->license->is_valid_pro_license_key() ? __( 'Deactivate key', 'propertyhive' ) : __( 'Activate key', 'propertyhive' ) );
		PH_Admin_Settings::output_fields( $settings );
	}

    /** Save licence operations without round-tripping stored credentials. */
    public function save() {
        if ( ! current_user_can( 'manage_options' ) || ! isset( $_REQUEST['_wpnonce'] ) || ! is_string( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'propertyhive-settings' ) ) {
            return;
        }

        $license_type = isset( $_POST['propertyhive_license_type'] ) && is_string( $_POST['propertyhive_license_type'] ) ? sanitize_text_field( wp_unslash( $_POST['propertyhive_license_type'] ) ) : '';
        if ( ! in_array( $license_type, array( 'pro', 'old' ), true ) ) {
            PH_Admin_Settings::add_error( __( 'Invalid license type.', 'propertyhive' ) );
            return;
        }
        foreach ( array( 'propertyhive_pro_license_key', 'propertyhive_license_key', 'pro_license_key_action', 'legacy_license_key_action' ) as $field ) {
            if ( isset( $_POST[$field] ) && ! is_string( $_POST[$field] ) ) {
                PH_Admin_Settings::add_error( __( 'Invalid license settings.', 'propertyhive' ) );
                return;
            }
        }
        $license_key = isset( $_POST['propertyhive_pro_license_key'] ) ? ph_clean( wp_unslash( $_POST['propertyhive_pro_license_key'] ) ) : '';
        $legacy_key = isset( $_POST['propertyhive_license_key'] ) ? ph_clean( wp_unslash( $_POST['propertyhive_license_key'] ) ) : '';
        $saved_pro_key = (string) get_option( 'propertyhive_pro_license_key', '' );
        $saved_legacy_key = (string) get_option( 'propertyhive_license_key', '' );
        $action_field = 'pro' === $license_type ? 'pro_license_key_action' : 'legacy_license_key_action';
        $action = isset( $_POST[$action_field] ) ? sanitize_text_field( wp_unslash( $_POST[$action_field] ) ) : '';
        $allowed_actions = 'pro' === $license_type ? array( '', 'activate', 'deactivate' ) : array( '', 'activate', 'remove' );
        if ( ! in_array( $action, $allowed_actions, true ) ) {
            PH_Admin_Settings::add_error( __( 'Invalid license operation.', 'propertyhive' ) );
            return;
        }

        if ( 'pro' === $license_type && 'deactivate' === $action ) {
            if ( '' === $saved_pro_key ) {
                PH_Admin_Settings::add_error( __( 'No saved license key to deactivate.', 'propertyhive' ) );
                return;
            }
            // The licensing client reads the existing key and instance on the server.
            $result = PH()->license->deactivate_pro_license_key();
            if ( empty( $result['success'] ) ) {
                PH_Admin_Settings::add_error( $this->redact_license_message( $result['error'], array( $license_key ) ) );
                return;
            }
            // Do not discard the credential unless the server confirmed deactivation.
            delete_option( 'propertyhive_pro_license_key' );
            if ( '' !== (string) get_option( 'propertyhive_pro_license_key', '' ) ) {
                PH_Admin_Settings::add_error( __( 'The license was deactivated but the saved key could not be removed. Please retry or contact support.', 'propertyhive' ) );
                return;
            }
            $this->clear_pro_display_caches();
            PH_Admin_Settings::add_message( __( 'License key deactivated and removed from this site.', 'propertyhive' ) );
        }

        if ( 'old' === $license_type && 'remove' === $action ) {
            delete_option( 'propertyhive_license_key' );
            if ( '' !== (string) get_option( 'propertyhive_license_key', '' ) ) {
                PH_Admin_Settings::add_error( __( 'The saved license key could not be removed.', 'propertyhive' ) );
                return;
            }
            delete_option( 'propertyhive_license_key_details' );
            delete_option( 'propertyhive_license_key_error' );
            delete_option( 'propertyhive_last_license_check' );
            PH_Admin_Settings::add_message( __( 'License key removed from this site.', 'propertyhive' ) );
        }

        if ( 'activate' === $action && ( 'pro' === $license_type ? '' === $license_key && '' === $saved_pro_key : '' === $legacy_key && '' === $saved_legacy_key ) ) {
            PH_Admin_Settings::add_error( __( 'Please enter a license key.', 'propertyhive' ) );
            return;
        }

        $settings = $this->get_settings();
        // The generic saver must never clear or repopulate either credential.
        foreach ( $settings as $index => $setting ) {
            if ( isset( $setting['id'] ) && in_array( $setting['id'], array( 'propertyhive_pro_license_key', 'propertyhive_license_key' ), true ) ) {
                unset( $settings[$index] );
            }
        }
        PH_Admin_Settings::save_fields( $settings );
        update_option( 'missing_invalid_expired_license_key_notice_dismissed', '' );

        if ( 'pro' === $license_type && 'activate' === $action ) {
            // Blank entry retries the saved inactive key; it never erases it.
            if ( '' !== $license_key ) {
                update_option( 'propertyhive_pro_license_key', $license_key );
                if ( get_option( 'propertyhive_pro_license_key', '' ) !== $license_key ) {
                    PH_Admin_Settings::add_error( __( 'The license key could not be saved. No activation was requested.', 'propertyhive' ) );
                    return;
                }
                $this->clear_pro_display_caches();
            }
            $result = PH()->license->activate_pro_license_key();
            if ( empty( $result['success'] ) ) {
                // Keep the attempted credential for retry if the remote outcome is uncertain.
                PH_Admin_Settings::add_error( $this->redact_license_message( $result['error'], array( $license_key ) ) );
            } else {
                $this->clear_pro_display_caches();
                PH_Admin_Settings::add_message( __( 'License key activated successfully.', 'propertyhive' ) );
            }
        }
        if ( 'old' === $license_type && 'remove' !== $action ) {
            if ( 'activate' === $action && '' !== $legacy_key ) {
                update_option( 'propertyhive_license_key', $legacy_key );
                if ( get_option( 'propertyhive_license_key', '' ) !== $legacy_key ) {
                    PH_Admin_Settings::add_error( __( 'The license key could not be saved.', 'propertyhive' ) );
                    return;
                }
            }
            PH()->license->ph_check_licenses( true );
        }
    }

    /** Clear cached Pro entitlement data without changing instance/domain identity. */
    private function clear_pro_display_caches() {
        delete_transient( 'ph_pro_license_product_id_and_package' );
        delete_transient( 'ph_pro_license_status' );
        delete_option( 'ph_pro_last_known_license_product_id_and_package' );
        delete_option( 'propertyhive_pro_license_key_status' );
        delete_option( 'propertyhive_pro_license_key_last_checked' );
    }

    /** Never echo a stored credential back through a service error message. */
    private function redact_license_message( $message, $extra_keys = array() ) {
        $message = is_scalar( $message ) ? (string) $message : __( 'The license operation failed. Please try again or contact support.', 'propertyhive' );
        $keys = array_merge( $extra_keys, array( get_option( 'propertyhive_pro_license_key', '' ), get_option( 'propertyhive_license_key', '' ) ) );
        foreach ( $keys as $key ) {
            if ( is_string( $key ) && '' !== $key ) {
                $message = str_replace( array( $key, esc_html( $key ), rawurlencode( $key ), urlencode( $key ) ), '[redacted]', $message );
            }
        }
        return $message;
    }

    /** A short identifier only; short/invalid keys must not be revealed in full. */
    private function masked_license_key( $key ) {
        return strlen( $key ) > 8 ? str_repeat( '*', strlen( $key ) - 4 ) . substr( $key, -4 ) : '********';
    }

}

endif;

return new PH_Settings_Licenses();
