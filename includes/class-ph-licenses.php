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
	public function ph_check_licenses( $force_check = false )
	{
		// Do a maximum of once per week
		$last_send = get_option( 'propertyhive_last_license_check', '' );
		if( !$force_check && is_numeric( $last_send ) && $last_send > strtotime( '-1 week' ) ) {
			return false;
		}

		update_option( 'propertyhive_last_license_check', time() );

		// Start by removing what we already know about the license
		update_option( 'propertyhive_license_key_details', '', 'no' );

		$data = array();

		$data['license_key'] = get_option( 'propertyhive_license_key', '' );
		$data['url']         = home_url();

		if ( get_option( 'propertyhive_data_sharing' ) != 'no' )
		{
			// Not opted-out

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

			$property_import = get_option( 'propertyhive_property_import', array() );
			if ( !is_array($property_import) )
			{
				$property_import = array();
			}
			$formats = array();
            foreach ( $property_import as $import_id => $options )
            {
            	if ( $options['running'] == '1' )
            	{
            		$formats[] = $options['format'];
            	}
			}
			$data['property_import_formats'] = $formats;

			// get counts
			$post_types = array('office', 'property', 'contact', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy', 'key_date');
			foreach ( $post_types as $post_type )
			{
				$args = array(
					'post_type' => $post_type,
					'fields' => 'ids',
					'nopaging' => TRUE,
					'post_status' => 'publish'
				);
				if ( $post_type == 'property' )
				{
					$args['meta_query'] = array(
						array(
							'key' => '_on_market',
							'value' => 'yes'
						)
					);
				}

				$post_query = new WP_Query( $args );

				$data['post_type_count_' . $post_type] = $post_query->found_posts;

				wp_reset_postdata();
			}

		}

		$request = wp_remote_post( 'http://license.wp-property-hive.com/check-license.php', array(
			'method'      => 'POST',
			'timeout'     => 20,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'body'        => $data,
			'user-agent'  => 'PH/' . PH_VERSION . '; ' . get_bloginfo( 'url' )
		) );

		if ( is_wp_error( $request ) || ( !is_wp_error( $request ) && isset($request['body']) && $request['body'] == '' ) )
		{
			update_option( 'propertyhive_license_key_details', array(), 'no' );
			return false;
		}

		$body = unserialize($request['body']);
		if ( $body !== FALSE && is_array($body) && !empty($body) )
		{
			update_option( 'propertyhive_license_key_details', $body, 'no' );
		}
	}

	public function get_current_license()
	{
		$license = get_option( 'propertyhive_license_key_details', array() );

		if ( !is_array( $license ) ) { $license = array(); }

		return $license;
	}
}