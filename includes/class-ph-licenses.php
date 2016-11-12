<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Licenses Controller
 *
 * Property Hive Licenses Class which handles the storing and checking of licenses
 *
 * @class 		PH_Licenses
 * @version		1.0.0
 * @package		PropertyHive/Classes/Licenses
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Licenses {
	
	/** @var PH_Licenses The single instance of the class */
	protected static $_instance = null;

	/**
	 * Main PH_Licenses Instance.
	 *
	 * Ensures only one instance of PH_Licenses is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return PH_Licenses Main instance
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
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'propertyhive' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'propertyhive' ), '1.0.0' );
	}

	/**
	 * Constructor for the licenses class
	 *
	 */
	public function __construct() {
		add_action( 'propertyhive_check_licenses', array( $this, 'ph_check_licenses' ) );
	}

	/**
	 * Automated check for licenses
	 *
	 */
	public function ph_check_licenses()
	{
		// Do a maximum of once per week
		$last_send = get_option( 'propertyhive_last_license_check' );
		if( is_numeric( $last_send ) && $last_send > strtotime( '-1 week' ) ) {
			return false;
		}

		$data = array();

		// Retrieve current theme info
		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;

		$data['php_version'] = phpversion();
		$data['ph_version'] = PH_VERSION;
		$data['wp_version']  = get_bloginfo( 'version' );
		$data['server']      = isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : '';

		$data['install_date'] = get_option('propertyhive_install_timestamp', '');
		if ( $data['install_date'] != '' && $data['install_date'] != 0 )
		{
			$data['install_date'] = date("jS F Y", $data['install_date']);
		}

		$data['multisite']   = is_multisite();
		$data['url']         = home_url();
		$data['theme']       = $theme;
		$data['email']       = get_bloginfo( 'admin_email' );

		// Retrieve current plugin information
		if( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins        = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins ) ) {
				// Remove active plugins from list so we can show active and inactive separately
				unset( $plugins[ $key ] );
			}
		}

		$data['active_plugins']   = $active_plugins;
		$data['inactive_plugins'] = $plugins;
		$data['locale']           = get_locale();

		$request = wp_remote_post( 'https://wp-property-hive.com/check-licenses.php', array(
			'method'      => 'POST',
			'timeout'     => 20,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'body'        => $data,
			'user-agent'  => 'PH/' . PH_VERSION . '; ' . get_bloginfo( 'url' )
		) );

		if( is_wp_error( $request ) ) {
			return $request;
		}

		update_option( 'propertyhive_last_license_check', time() );
	}
}