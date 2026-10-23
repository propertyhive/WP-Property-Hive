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

	private $pro_license_status = array();

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
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'propertyhive' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'propertyhive' ), '1.0.0' );
	}

	/**
	 * Constructor for the licenses class
	 *
	 */
	public function __construct() {
		add_action( 'propertyhive_check_licenses', array( $this, 'ph_check_licenses' ) );
		add_filter( 'propertyhive_add_on_can_be_used', array( $this, 'ph_check_add_on_can_be_used' ), 10, 2 );
		add_filter( 'propertyhive_add_on_can_be_updated', array( $this, 'ph_check_add_on_can_be_updated' ), 10, 2 );

		add_action( 'admin_notices', array( $this, 'pro_license_domain_notice' ) );
		add_action( 'admin_post_ph_move_pro_license', array( $this, 'handle_move_pro_license' ) );
	}

	public function ph_check_add_on_can_be_used( $can, $slug )
	{
		// Check add on wasn't active before
		$pre_pro_add_ons = get_option( 'propertyhive_pre_pro_add_ons', array() );

		if ( !empty($pre_pro_add_ons) )
		{
			foreach ( $pre_pro_add_ons as $pre_pro_add_on )
			{
				if ( isset($pre_pro_add_on['slug']) && $pre_pro_add_on['slug'] == $slug )
				{
					// Yes! This add on was being used pre pro so should still be able to be used without license checks
					return true;
				}
			}
		}
		
        // Check we have the right kind of license key to operate this add on

        $license_type = $this->get_license_type();

        if ( $license_type == 'pro' )
        {
            if ( get_option('propertyhive_pro_license_key', '') != '' ) 
            { 
                if ( !$this->is_valid_pro_license_key() )
                {
                    // show warning
                    return false;
                }

                // Valid license. Check this plugin is valid for the purchased type of plan (i.e. map search can't be used with an 'import only' plan)
                $feature = get_ph_pro_feature( $slug );
                $product_id_and_package = PH()->license->get_pro_license_product_id_and_package();

                if ( isset($product_id_and_package['success']) && $product_id_and_package['success'] === true )
                {
                    if ( 
                        isset($feature['plans']) && 
                        isset($product_id_and_package['package']) &&
                        in_array($product_id_and_package['package'], $feature['plans'])
                    )
                    {
                        
                    }
                    else
                    {
                    	// show warning
                    	return false;
                    }
                }
            }
        }

        return $can;
	}

	public function ph_check_add_on_can_be_updated( $can, $slug )
	{
        $license_type = $this->get_license_type();

		if ( $license_type == 'old' )
		{
			$license = get_option( 'propertyhive_license_key_details', array() );

			if ( !is_array( $license ) ) { $license = array(); }

		    if ( isset($license['active']) && $license['active'] == '1' && isset($license['expires_at']) && $license['expires_at'] != '' && strtotime($license['expires_at']) > time() )
		    {
		    	return true;
		    }
		}
		elseif ( $license_type == 'pro' )
		{
			// get new license information
			if ( get_option('propertyhive_pro_license_key', '') != '' ) 
			{ 
				if ( $this->is_valid_pro_license_key() )
				{
					// Valid license. Check this plugin is valid for the purchased type of plan (i.e. map search can't be used with an 'import only' plan)
	                $feature = get_ph_pro_feature( $slug );
	                $product_id_and_package = PH()->license->get_pro_license_product_id_and_package();

	                if ( isset($product_id_and_package['success']) && $product_id_and_package['success'] === true )
	                {
	                	if ( ( $product_id_and_package['is_trial'] ?? null ) !== false ) 
	                	{
						    return false;
						}

	                    if ( 
	                        isset($feature['plans']) && 
	                        isset($product_id_and_package['package']) &&
	                        in_array($product_id_and_package['package'], $feature['plans'])
	                    )
	                    {
	                        return true;
	                    }
	                }
				}
			}
		}

        return $can;
	}

	public function get_license_type()
	{
		$license_type = get_option( 'propertyhive_license_type', '' );

		if ( $license_type == '' )
		{
			// We don't know. Let's work it out by seeing if they have a license key
			$existing_license_key = get_option( 'propertyhive_license_key', '' );

			if ( $existing_license_key != '' )
			{
				$license = PH()->license->get_current_license();

				if ( isset($license['active']) && $license['active'] == '1' )
				{
					return 'old';
				}
			}
		}

		return $license_type; // pro / old
	}

	/**
	 * Get SHA-256 checksums for selected PHP files belonging to active
	 * Property Hive plugins.
	 *
	 * Includes:
	 * - PHP files directly within the plugin root.
	 * - PHP files directly within the plugin's /includes directory.
	 *
	 * Does not scan subdirectories within /includes.
	 *
	 * @return array
	 */
	private function get_active_propertyhive_plugin_checksums()
	{
		if (
			! class_exists( 'FilesystemIterator' ) ||
			! function_exists( 'hash_file' )
		) {
			return array(
				'_environment' => array(
					'status' => 'unavailable',
					'error'  => 'checksum_requirements_unavailable',
				),
			);
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins_raw = get_plugins();
		$installed_plugins     = array();

		/*
		 * Normalise paths for consistency across Windows and Unix-like servers.
		 */
		foreach ( $installed_plugins_raw as $plugin_file => $plugin_data ) {
			$installed_plugins[ wp_normalize_path( $plugin_file ) ] = $plugin_data;
		}

		$active_plugins = (array) get_option( 'active_plugins', array() );

		/*
		 * Include network-activated plugins on multisite.
		 */
		if ( is_multisite() ) {
			$network_active_plugins = (array) get_site_option(
				'active_sitewide_plugins',
				array()
			);

			$active_plugins = array_merge(
				$active_plugins,
				array_keys( $network_active_plugins )
			);
		}

		/*
		 * Normalise and deduplicate active plugin paths.
		 */
		$normalised_active_plugins = array();

		foreach ( $active_plugins as $plugin_file ) {
			if ( ! is_string( $plugin_file ) || '' === $plugin_file ) {
				continue;
			}

			$normalised_active_plugins[] = wp_normalize_path(
				plugin_basename( $plugin_file )
			);
		}

		$active_plugins = array_values(
			array_unique( $normalised_active_plugins )
		);

		$plugin_checksums = array();

		foreach ( $active_plugins as $plugin_file ) {
			if ( ! isset( $installed_plugins[ $plugin_file ] ) ) {
				continue;
			}

			$plugin_path_parts = explode( '/', $plugin_file );
			$plugin_slug       = isset( $plugin_path_parts[0] )
				? $plugin_path_parts[0]
				: '';

			if ( '' === $plugin_slug ) {
				continue;
			}

			/*
			 * Match Property Hive core and add-ons whose directory starts
			 * with "propertyhive-".
			 */
			if (
				'propertyhive' !== $plugin_slug &&
				0 !== strpos( $plugin_slug, 'propertyhive-' )
			) {
				continue;
			}

			$plugin_root = wp_normalize_path(
				trailingslashit( WP_PLUGIN_DIR ) . $plugin_slug
			);

			if ( ! is_dir( $plugin_root ) ) {
				continue;
			}

			$directories_to_scan = array(
				'plugin_root' => $plugin_root,
			);

			$includes_directory = wp_normalize_path(
				trailingslashit( $plugin_root ) . 'includes'
			);

			if ( is_dir( $includes_directory ) ) {
				$directories_to_scan['includes'] = $includes_directory;
			}

			$files_to_hash = array();
			$scan_errors   = array();
			$hash_errors   = array();

			/*
			 * Scan only the immediate contents of each directory.
			 * FilesystemIterator does not recurse into subdirectories.
			 */
			foreach ( $directories_to_scan as $directory_type => $directory ) {
				try {
					$iterator = new FilesystemIterator(
						$directory,
						FilesystemIterator::SKIP_DOTS |
						FilesystemIterator::CURRENT_AS_FILEINFO |
						FilesystemIterator::KEY_AS_PATHNAME
					);

					foreach ( $iterator as $file_info ) {
						if (
							! $file_info instanceof SplFileInfo ||
							! $file_info->isFile() ||
							$file_info->isLink() ||
							'php' !== strtolower( $file_info->getExtension() )
						) {
							continue;
						}

						/*
						 * For the /includes directory, only include PHP files
						 * relating to licensing, installation or AJAX.
						 *
						 * All root-level PHP files are still included.
						 */
						if (
							'includes' === $directory_type &&
							1 !== preg_match(
								'/(?:licen[cs]e|install|ajax)/i',
								$file_info->getFilename()
							)
						) {
							continue;
						}

						$files_to_hash[] = wp_normalize_path(
							$file_info->getPathname()
						);
					}
				} catch ( Throwable $exception ) {
					$scan_errors[] = $directory_type . '_scan_failed';
				}
			}

			$files_to_hash = array_values(
				array_unique( $files_to_hash )
			);

			sort( $files_to_hash, SORT_STRING );

			$plugin_root_prefix = trailingslashit( $plugin_root );
			$checksums          = array();

			foreach ( $files_to_hash as $php_file ) {
				if ( 0 !== strpos( $php_file, $plugin_root_prefix ) ) {
					$scan_errors[] = 'file_outside_plugin_root';
					continue;
				}

				$relative_path = substr(
					$php_file,
					strlen( $plugin_root_prefix )
				);

				$relative_path = wp_normalize_path( $relative_path );

				if ( '' === $relative_path ) {
					continue;
				}

				if (
					! is_file( $php_file ) ||
					! is_readable( $php_file )
				) {
					$hash_errors[] = $relative_path;
					continue;
				}

				$checksum = hash_file( 'sha256', $php_file );

				if ( false === $checksum ) {
					$hash_errors[] = $relative_path;
					continue;
				}

				$checksums[ $relative_path ] = $checksum;
			}

			ksort( $checksums, SORT_STRING );

			$scan_errors = array_values(
				array_unique( $scan_errors )
			);

			$hash_errors = array_values(
				array_unique( $hash_errors )
			);

			sort( $scan_errors, SORT_STRING );
			sort( $hash_errors, SORT_STRING );

			$plugin_data = $installed_plugins[ $plugin_file ];

			$plugin_checksums[ $plugin_slug ] = array(
				'name'        => isset( $plugin_data['Name'] )
					? sanitize_text_field( $plugin_data['Name'] )
					: '',
				'version'     => isset( $plugin_data['Version'] )
					? sanitize_text_field( $plugin_data['Version'] )
					: '',
				'plugin_file' => $plugin_file,
				'file_count'  => count( $checksums ),
				'files'       => $checksums,
				'hash_errors' => $hash_errors,
				'scan_errors' => $scan_errors,
			);
		}

		ksort( $plugin_checksums, SORT_STRING );

		return $plugin_checksums;
	}

	private function get_data_for_license_check()
	{
		global $wpdb;

		$license_type = $this->get_license_type();

		$data = array();
		$data['license_key_type'] = $license_type;
		$data['license_key'] = ( $license_type == 'old' ? get_option( 'propertyhive_license_key', '' ) : get_option( 'propertyhive_pro_license_key', '' ) );
		$data['license_key_instance_id'] = ( $license_type == 'pro' ? get_option( 'propertyhive_pro_instance_id', '' ) : '' );
		$data['url']         = home_url();

		/*
		 * Integrity information is part of license validation and is therefore
		 * collected independently of the optional usage/data-sharing setting.
		 */
		$plugin_integrity = array(
			'schema_version' => 1,
			'algorithm'      => 'sha256',
			'plugins'        => $this->get_active_propertyhive_plugin_checksums(),
		);

		$plugin_integrity_json = wp_json_encode( $plugin_integrity );

		if ( false !== $plugin_integrity_json ) 
		{
			$data['propertyhive_plugin_integrity'] = $plugin_integrity_json;
		}

		if ( get_option( 'propertyhive_data_sharing' ) != 'no' )
		{
			// Not opted-out

			// Retrieve current theme info
			$theme_data = wp_get_theme();
			$theme      = $theme_data->Name . ' ' . $theme_data->Version;

			$data['php_version'] = phpversion();
			$data['ph_version'] = PH_VERSION;
			$data['wp_version']  = get_bloginfo( 'version' );
			$data['server']      = isset( $_SERVER['SERVER_SOFTWARE'] ) && is_string( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

			$data['install_date'] = get_option('propertyhive_install_timestamp', '');
			if ( $data['install_date'] != '' && $data['install_date'] != 0 )
			{
				$data['install_date'] = gmdate("jS F Y", $data['install_date']);
			}

			$data['multisite']   = is_multisite();
			$data['theme']       = $theme;
			$data['email']       = get_bloginfo( 'admin_email' );

			// Retrieve current plugin information
			if ( !function_exists( 'get_plugins' ) ) {
				include ABSPATH . '/wp-admin/includes/plugin.php';
			}

			$plugins        = get_plugins(); // Fetch all plugins with details
			$active_plugins = get_option('active_plugins', array());

			$active_plugins_data = [];
			$inactive_plugins_data = [];

			// Loop through plugins to separate active and inactive along with version
			foreach ($plugins as $plugin_path => $plugin_data) 
			{
			    $plugin_info = [
			        'name'    => isset($plugin_data['Name']) ? $plugin_data['Name'] : '',
			        'version' => isset($plugin_data['Version']) ? $plugin_data['Version'] : '',
			        'path'    => $plugin_path,
			    ];

			    if (in_array($plugin_path, $active_plugins)) 
			    {
			        $active_plugins_data[] = $plugin_info;
			    }
			    else
			    {
			        $inactive_plugins_data[] = $plugin_info;
			    }
			}

			$data['active_plugins']   = $active_plugins_data;
			$data['inactive_plugins'] = $inactive_plugins_data;

			$data['locale']           = get_locale();

			// Import info
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
            		if ( $options['format'] == 'xml' )
            		{
            			$options['format'] .= ' (' . esc_url($options['xml_url']) . ')';
            		}
            		elseif ( $options['format'] == 'csv' )
            		{
            			$options['format'] .= ' (' . esc_url($options['csv_url']) . ')';
            		}
            		elseif ( 
            			$options['format'] == 'rtdf' &&
            			get_option( 'propertyhive_rtdf_crm', '' )  != ''
            		)
            		{
            			$options['format'] .= ' (' . get_option( 'propertyhive_rtdf_crm', '' ) . ')';
            		}
            		$formats[] = $options['format'];
            	}
			}
			$data['property_import_formats'] = $formats;

			// Exports
			$formats = array();

			$exports = array(
			    'propertyhive_realtimefeed' => array(
			        'label' => 'RTDF',
			        'field' => 'send_property_api_url',
			    ),
			    'propertyhive_zooplarealtimefeed' => array(
			        'label' => 'Zoopla',
			        'field' => 'send_property_api_url',
			    ),
			    'propertyhive_blmexport' => array(
			        'label' => 'BLM',
			        'field' => 'ftp_host',
			    ),
			);

			foreach ( $exports as $option_name => $config )
			{
			    $property_export = get_option( $option_name, array() );

			    if ( ! is_array( $property_export ) ) {
			    	continue;
			    }

		        if ( ! isset( $property_export['portals'] ) ) {
		            continue;
		        }

		        if ( ! is_array( $property_export['portals'] ) ) {
		            continue;
		        }

		        $portals = isset( $property_export['portals'] ) && is_array( $property_export['portals'] )
		            ? $property_export['portals']
		            : array();

		        foreach ( $portals as $portal_id => $portal )
		        {
		            if ( ! is_array( $portal ) ) {
		                continue;
		            }

		            if ( ! is_array( $portal ) || ( $portal['mode'] ?? '' ) !== 'live' ) {
		                continue;
		            }

		            $destination = $portal[ $config['field'] ] ?? '';

		            $formats[] = sprintf(
		                '%s (%s)',
		                $config['label'],
		                $destination
		            );
		        }
			}

			$data['property_export_formats'] = $formats;

			// Locrating
			if ( is_plugin_active( 'propertyhive-locrating/propertyhive-locrating.php' ) )
			{
				$locrating_settings = get_option( 'propertyhive_locrating', array() );
				if ( !empty($locrating_settings) && isset($locrating_settings['enabled']) )
				{
					$data['locrating_enabled'] = ( $locrating_settings['enabled'] == 1 ? $locrating_settings['enabled'] : 0 );
				}
			}

			// Visi homes
			if ( is_plugin_active( 'propertyhive-visihomes/propertyhive-visihomes.php' ) )
			{
				$visihomes_settings = get_option( 'propertyhive_visihomes', array() );
				if ( 
					!empty($visihomes_settings) && 
					isset( $visihomes_settings['embed_code'] ) &&
	            	!empty($visihomes_settings['embed_code']) 
				)
				{
					$data['visihomes_enabled'] = 1;
				}
			}

			// Search analytics

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Aggregate current custom-table analytics for the license report; new search events and retention change these counts independently.
			$counts = $wpdb->get_row(
				"SELECT
					COALESCE(
						SUM(searched_at >= UTC_TIMESTAMP() - INTERVAL 7 DAY),
						0
					) AS last_7_days,
					COALESCE(
						SUM(searched_at >= UTC_TIMESTAMP() - INTERVAL 30 DAY),
						0
					) AS last_30_days,
					COUNT(*) AS last_90_days
				FROM {$wpdb->prefix}ph_search_log
				WHERE searched_at >= UTC_TIMESTAMP() - INTERVAL 90 DAY",
				ARRAY_A
			);

			if ( is_array($counts) )
			{
				$data['searches_last_7_days'] = (int) ( $counts['last_7_days'] ?? 0 );
				$data['searches_last_30_days'] = (int) ( $counts['last_30_days'] ?? 0 );
				$data['searches_last_90_days'] = (int) ( $counts['last_90_days'] ?? 0 );
			}

			// get counts
			$post_types = array('office', 'property', 'contact', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy', 'key_date');
			foreach ( $post_types as $post_type )
			{
				$args = array(
					'post_type' => $post_type,
					'fields' => 'ids',
					'posts_per_page' => 1,
					'post_status' => 'publish'
				);
				if ( $post_type == 'property' )
				{
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- On-market state is stored in property metadata; retrieve one ID and use found_posts for the aggregate count.
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

			$departments = array_keys( ph_get_departments() );
			foreach ( $departments as $department )
			{
				$department_option = 'propertyhive_active_departments_' . str_replace( 'residential-', '', $department );
				if ( get_option( $department_option ) != 'yes' )
				{
					continue;
				}

				$args = array(
					'post_type' => 'property',
					'fields' => 'ids',
					'posts_per_page' => 1,
					'post_status' => 'publish',
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Department and on-market state use property metadata; retrieve one ID and use found_posts for the opted-in aggregate count.
					'meta_query' => array(
						array(
							'key' => '_on_market',
							'value' => 'yes'
						),
						array(
							'key' => '_department',
							'value' => $department
						)
					)
				);

				$post_query = new WP_Query( $args );

				$data['property_count_by_department_' . $department] = $post_query->found_posts;

				wp_reset_postdata();
			}

			// Onboarding wizard step
			$onboarding_state = get_option( 'propertyhive_onboarding', false );

			if ( is_array( $onboarding_state ) && ! empty( $onboarding_state ) )
			{
				$onboarding_status = isset( $onboarding_state['status'] )
					? sanitize_key( $onboarding_state['status'] )
					: 'not_started';

				$onboarding_step = isset( $onboarding_state['last_step'] )
					? sanitize_key( $onboarding_state['last_step'] )
					: '';

				// When skipped, last_step is the exact step on which Skip was clicked.
				// Otherwise, use the most recently viewed step so abandonment is captured.
				if (
					$onboarding_status !== 'skipped' &&
					! empty( $onboarding_state['events'] ) &&
					is_array( $onboarding_state['events'] )
				)
				{
					foreach ( array_reverse( $onboarding_state['events'] ) as $event )
					{
						if (
							isset( $event['event'], $event['data']['step'] ) &&
							$event['event'] === 'step_viewed'
						)
						{
							$onboarding_step = sanitize_key( $event['data']['step'] );
							break;
						}
					}
				}

				$data['onboarding_status'] = $onboarding_status;
				$data['onboarding_step']   = $onboarding_step;
			}

			$data = apply_filters( 'propertyhive_license_check_data', $data );
		}

		return $data;
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

		// Retain last-known license details during outages; replace them only with a valid HTTPS response.
		update_option( 'propertyhive_license_key_error', '', 'no' );

		$data = $this->get_data_for_license_check();

		$request = wp_remote_post( 'http://license.wp-property-hive.com/check-license.php', array(
			'method'      => 'POST',
			'timeout'     => 20,
			'redirection' => 1,
			'httpversion' => '1.1',
			'blocking'    => true,
			'body'        => $data,
			'user-agent'  => 'PH/' . PH_VERSION . '; ' . get_bloginfo( 'url' )
		) );

		if ( is_wp_error( $request ) )
		{
			update_option( 'propertyhive_license_key_error', __( 'The license service could not be reached securely. Your saved license details have been retained.', 'propertyhive' ) . ' ' . $request->get_error_message(), 'no' );
			return false;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $request ) || '' === wp_remote_retrieve_body( $request ) )
		{
			update_option( 'propertyhive_license_key_error', __( 'The license service returned an invalid response.', 'propertyhive' ) . ' ' . wp_remote_retrieve_response_code( $request ), 'no' );
			return false;
		}

		$body = @unserialize($request['body'], ['allowed_classes' => false]);
		if ( $body !== FALSE && is_array($body) )
		{
			update_option( 'propertyhive_license_key_details', $body, 'no' );
		}
		else
		{
			update_option( 'propertyhive_license_key_error', __( 'The license service returned invalid license data.', 'propertyhive' ) . ' ' . print_r($body, true), 'no' );
		}
	}

	public function get_current_license()
	{
		$license = get_option( 'propertyhive_license_key_details', array() );

		if ( !is_array( $license ) ) { $license = array(); }

		return $license;
	}

	/**
	 * Keep a copied installation from using the original site's activation.
	 *
	 * Returns true when unchanged, or WP_Error when the site has changed.
	 */
	public function check_pro_license_site_identity()
	{
	    $host = wp_parse_url( get_site_url(), PHP_URL_HOST );

	    if ( ! is_string( $host ) || $host === '' ) {
	        return new WP_Error(
	            'ph_license_site_unknown',
	            __( 'Unable to determine this site\'s domain.', 'propertyhive' )
	        );
	    }

	    $host = strtolower( rtrim( $host, '.' ) );

	    // Store a fingerprint so migration tools doing domain search/replace
	    // do not normally rewrite the original identity to the new domain.
	    $current_identity = hash( 'sha256', $host );

	    $saved_identity = get_option(
	        'propertyhive_pro_site_identity',
	        ''
	    );

	    // Backward-compatible migration: preserve existing activations.
	    // Establish the baseline on the first run of this updated code.
	    if ( $saved_identity === '' ) {
	        add_option(
	            'propertyhive_pro_site_identity',
	            $current_identity,
	            '',
	            false
	        );

	        $saved_identity = get_option(
	            'propertyhive_pro_site_identity',
	            ''
	        );
	    }

	    if ( $saved_identity === $current_identity ) {
	        update_option( 'propertyhive_pro_site_host', base64_encode( $host ), false );
	        return true;
	    }

	    // Invalidate copied license status, but preserve the original
		// key, instance ID and site identity for an explicit transfer.
		delete_option( 'propertyhive_pro_license_key_status' );
		delete_option( 'propertyhive_pro_license_key_last_checked' );
		delete_transient( 'ph_pro_license_status' );

		$message = __( 'This installation\'s domain has changed. Move the existing license here.', 'propertyhive' );
		$product_id = get_transient( $this->pro_domain_plan_cache_key() );
		if ( $this->pro_product_allows_additional_sites( $product_id ) ) {
			$message = __( 'This installation\'s domain has changed. Move the existing license here, or activate this as an additional site.', 'propertyhive' );
		}
		return new WP_Error( 'ph_license_site_changed', $message );
	}

	public function is_valid_pro_license_key($force = false)
	{
		$license = $this->get_current_pro_license($force);
		if ( isset($license['success']) && $license['success'] === true )
		{
			return true;
		}

		return false;
	}

	public function activate_pro_license_key()
	{
		$site_check = $this->check_pro_license_site_identity();

		if ( is_wp_error( $site_check ) ) 
		{
		    return array(
		        'success' => false,
		        'error'   => $site_check->get_error_message(),
		    );
		}

		$license = $this->get_pro_license_product_id_and_package(true);

		if ( isset($license['success']) && $license['success'] === true )
		{
			$license_key = get_option( 'propertyhive_pro_license_key', '' );

			$instance_id = get_option( 'propertyhive_pro_instance_id', '' );

	    	if ( empty($instance_id) )
	    	{
	    		$instance_id = wp_generate_password( 12, false ); // disable specialchars
	    		update_option('propertyhive_pro_instance_id', $instance_id );
	    	}

	    	$data = array();

			// found API key. It's product ID will be stored in product_id
			$url = 'https://wp-property-hive.com/?';
	    	$url .= 'wc-api=wc-am-api&';
	    	$url .= 'wc_am_action=activate&';
	    	$url .= 'instance=' . $instance_id . '&';
            $url .= 'object=' . wp_parse_url( get_site_url(), PHP_URL_HOST ) . '&';
	    	$url .= 'product_id=' . $license['product_id'] . '&';
	    	$url .= 'api_key=' . $license_key;

	    	$response = wp_remote_post( $url, array(
	    		'body' => $data,
	    	) );

	    	if ( is_wp_error($response) )
	    	{
	  			$return = array(
	        		'success' => false,
	        		'error' => __( 'Failed to activate license status', 'propertyhive' ) . ': ' . $response->get_error_message()
	        	);
	        	return $return;
			}

			if ( 200 !== wp_remote_retrieve_response_code( $response ) )
			{
				$return = array(
	        		'success' => false,
	        		'error' => sprintf( 
	        			/* translators: %s: HTTP header response code */
	        			__( 'Received response code %s when activating license key status', 'propertyhive' ), 
	        			wp_remote_retrieve_response_code( $response ) 
	        		)
	        	);
	        	return $return;
			}

			$result = $response['body'];

			$body = json_decode($result, true);

			if ( json_last_error() !== JSON_ERROR_NONE ) 
			{
				$return = array(
	        		'success' => false,
                    'error' => __( 'Failed to decode response when activating license key status. Please try again', 'propertyhive' ) . ': ' . wp_json_encode( $result )
	        	);
	        	return $return;
			}

			if ( isset($body['success']) )
			{
				if ( $body['success'] === true )
				{
					$return = array(
		        		'success' => true,
		        	);
					return $return;
				}
				else
				{
					$return = array(
		        		'success' => false,
		        		'error' => __( 'Error when activating license key', 'propertyhive' ) . ': ' . $body['error']
		        	);
					return $return;
				}
			}
			else
			{
				$return = array(
	        		'success' => false,
                    'error' => __( 'Something went wrong when trying to activate license key', 'propertyhive' ) . ': ' . wp_json_encode( $body )
	        	);
				return $return;
			}
		}
		else
		{
			return $license;
		}
	}

	public function deactivate_pro_license_key()
	{
		$site_check = $this->check_pro_license_site_identity();

		if ( is_wp_error( $site_check ) ) 
		{
		    return array(
		        'success' => false,
		        'error'   => $site_check->get_error_message(),
		    );
		}

		$license = $this->get_pro_license_product_id_and_package(true);

		if ( isset($license['success']) && $license['success'] === true )
		{
			$license_key = get_option( 'propertyhive_pro_license_key', '' );

			$instance_id = get_option( 'propertyhive_pro_instance_id', '' );

	    	if ( empty($instance_id) )
	    	{
	    		$instance_id = wp_generate_password( 12, false ); // disable specialchars
	    		update_option('propertyhive_pro_instance_id', $instance_id );
	    	}

	    	$data = array();

			// found API key. It's product ID will be stored in product_id
			$url = 'https://wp-property-hive.com/?';
	    	$url .= 'wc-api=wc-am-api&';
	    	$url .= 'wc_am_action=deactivate&';
	    	$url .= 'instance=' . $instance_id . '&';
            $url .= 'object=' . wp_parse_url( get_site_url(), PHP_URL_HOST ) . '&';
	    	$url .= 'product_id=' . $license['product_id'] . '&';
	    	$url .= 'api_key=' . $license_key;

	    	$response = wp_remote_post( $url, array(
	    		'body' => $data,
	    	) );

	    	if ( is_wp_error($response) )
	    	{
	  			$return = array(
	        		'success' => false,
	        		'error' => __( 'Failed to deactivate license status', 'propertyhive' ) . ': ' . $response->get_error_message()
	        	);
	        	return $return;
			}

			if ( 200 !== wp_remote_retrieve_response_code( $response ) )
			{
				$return = array(
	        		'success' => false,
	        		'error' => sprintf( 
	        			/* translators: %s: HTTP header response code */
	        			__( 'Received response code %s when deactivating license key status', 'propertyhive' ), 
	        			wp_remote_retrieve_response_code( $response ) 
	        		)
	        	);
	        	return $return;
			}

			$result = $response['body'];

			$body = json_decode($result, true);

			if ( json_last_error() !== JSON_ERROR_NONE ) 
			{
				$return = array(
	        		'success' => false,
                    'error' => __( 'Failed to decode response when deactivating license key status. Please try again', 'propertyhive' ) . ': ' . wp_json_encode( $result )
	        	);
	        	return $return;
			}

			if ( isset($body['success']) )
			{
				if ( $body['success'] === true )
				{
					$return = array(
		        		'success' => true,
		        	);
					return $return;
				}
				else
				{
					$return = array(
		        		'success' => false,
		        		'error' => __( 'Error when deactivating license key', 'propertyhive' ) . ': ' . $body['error']
		        	);
					return $return;
				}
			}
			else
			{
				$return = array(
	        		'success' => false,
                    'error' => __( 'Something went wrong when trying to deactivate license key', 'propertyhive' ) . ': ' . wp_json_encode( $body )
	        	);
				return $return;
			}
		}
		else
		{
			return $license;
		}
	}

	public function get_pro_license_product_id_and_package($force = false)
	{
		$site_check = $this->check_pro_license_site_identity();
		
		if ( is_wp_error( $site_check ) ) 
		{
		    return array(
		        'success' => false,
		        'error'   => $site_check->get_error_message(),
		    );
		}

		if ( $force !== true )
    	{
    		// Not forcing. Get from transient if possible
    		$pro_license_product_id_and_package = get_transient( 'ph_pro_license_product_id_and_package' );

    		if ( $pro_license_product_id_and_package !== false ) 
    		{
    			// return transient value
				return $pro_license_product_id_and_package;
			}
    	}

    	$license_key = get_option( 'propertyhive_pro_license_key', '' );

        if ( empty($license_key) )
        {
        	$return = array(
        		'success' => false,
        		'error' => __( 'No license key entered', 'propertyhive' )
        	);
        	set_transient( 'ph_pro_license_product_id_and_package', $return, HOUR_IN_SECONDS );
        	return $return;
        }

        $instance_id = get_option( 'propertyhive_pro_instance_id', '' );

        if ( empty($instance_id) )
    	{
    		$instance_id = wp_generate_password( 12, false ); // disable specialchars
    		update_option('propertyhive_pro_instance_id', $instance_id );
    	}

        $data = $this->get_data_for_license_check();

        $url = 'https://wp-property-hive.com/?';
    	$url .= 'wc-api=wc-am-api&';
    	$url .= 'wc_am_action=product_list&';
    	$url .= 'instance=' . $instance_id . '&';
    	$url .= 'api_key=' . $license_key;

    	$response = wp_remote_post( $url, array(
    		'body' => $data,
    	) );
    	
    	if ( is_wp_error($response) )
    	{
        	$return = array(
        		'success' => false,
        		'error' => __( 'Failed to request license product list', 'propertyhive' ) . ': ' . $response->get_error_message()
        	);

        	$last_known = get_option('ph_pro_last_known_license_product_id_and_package', array());
        	if ( !empty($last_known) )
        	{
        		set_transient( 'ph_pro_license_product_id_and_package', $last_known, HOUR_IN_SECONDS );
        		return $last_known;
        	}

        	return $return;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) )
		{
        	$return = array(
        		'success' => false,
        		'error' => sprintf( 
        			/* translators: %s: HTTP header response code */
        			__( 'Received response code %s when requesting license key product list', 'propertyhive' ), 
        			wp_remote_retrieve_response_code( $response ) 
        		)
        	);
        	
        	$last_known = get_option('ph_pro_last_known_license_product_id_and_package', array());
        	if ( !empty($last_known) )
        	{
        		set_transient( 'ph_pro_license_product_id_and_package', $last_known, HOUR_IN_SECONDS );
        		return $last_known;
        	}

        	return $return;
		}

		$result = $response['body'];

		$body = json_decode($result, true);

		if ( json_last_error() !== JSON_ERROR_NONE ) 
		{
        	$return = array(
        		'success' => false,
                'error' => __( 'Failed to decode response when requesting license key product list. Please try again', 'propertyhive' ) . ': ' . wp_json_encode( $result )
        	);
        	
        	$last_known = get_option('ph_pro_last_known_license_product_id_and_package', array());
        	if ( !empty($last_known) )
        	{
        		set_transient( 'ph_pro_license_product_id_and_package', $last_known, HOUR_IN_SECONDS );
        		return $last_known;
        	}

        	return $return;
		}

		if ( isset($body['success']) )
		{
			if ( $body['success'] === true )
			{
				if ( isset($body['data']['product_list']['wc_subs_resources']) && !empty($body['data']['product_list']['wc_subs_resources']) )
				{
					$package = false;
					$product_id = '';
					$is_trial = null;

					foreach ( $body['data']['product_list']['wc_subs_resources'] as $resource )
					{
						if ( isset($resource['product_id']) )
						{
							if ( in_array($resource['product_id'], array(14492, 14493, 14494, 23794)) )
							{
								$package = 'import';
							}
							elseif ( in_array($resource['product_id'], array(14495, 14496, 14497, 23792)) )
							{
								$package = 'complete';
							}
							$product_id = $resource['product_id'];
							$is_trial = isset( $resource['is_trial'] )
							    && is_bool( $resource['is_trial'] )
							        ? $resource['is_trial']
							        : null;
						}
					}

					if ( $package !== FALSE )
					{
						$return = array(
			        		'success' => true,
			        		'package' => $package,
			        		'product_id' => $product_id,
			        		'is_trial'   => $is_trial,
			        	);
					}
					else
					{
						$return = array(
			        		'success' => false,
			        		'error' => __( 'API key exists but unable to establish the plan', 'propertyhive' )
			        	);
					}
				}
				else
				{
					$return = array(
		        		'success' => false,
                        'error' => __( 'API key doesn\'t appear to belong to any orders', 'propertyhive' ) . ': ' . wp_json_encode( $body )
		        	);
				}
			}
			else
			{
				$return = array(
	        		'success' => false,
	        		'error' => __( 'Error when requesting license key product list', 'propertyhive' ) . ': ' . $body['error']
	        	);
			}

			set_transient( 'ph_pro_license_product_id_and_package', $return, HOUR_IN_SECONDS * 3 );
			update_option( 'ph_pro_last_known_license_product_id_and_package', $return );
			return $return;
		}
		else
		{
			$return = array(
        		'success' => false,
                'error' => __( 'Something went wrong when requesting license key product list', 'propertyhive' ) . ': ' . wp_json_encode( $body )
        	);
        	
			$last_known = get_option('ph_pro_last_known_license_product_id_and_package', array());
        	if ( !empty($last_known) )
        	{
        		set_transient( 'ph_pro_license_product_id_and_package', $last_known, HOUR_IN_SECONDS );
        		return $last_known;
        	}

			return $return;
		}
	}

	public function get_current_pro_license($force = false)
	{
		$site_check = $this->check_pro_license_site_identity();
		
		if ( is_wp_error( $site_check ) ) 
		{
		    return array(
		        'success' => false,
		        'error'   => $site_check->get_error_message(),
		    );
		}
		
		if ( $force !== true )
    	{
    		// Not forcing. Get from transient if possible
    		$pro_license_status = get_transient( 'ph_pro_license_status' );

    		if ( $pro_license_status !== false ) 
    		{
    			// return transient value
				return $pro_license_status;
			}
    	}

    	$product_id_and_package = $this->get_pro_license_product_id_and_package($force);

    	if ( !isset($product_id_and_package['success']) || ( isset($product_id_and_package['success']) && $product_id_and_package['success'] === false ) )
    	{
    		return $product_id_and_package;
    	}
    	
        $license_key = get_option( 'propertyhive_pro_license_key', '' );

        if ( empty($license_key) )
        {
        	$return = array(
        		'success' => false,
        		'error' => __( 'No license key entered', 'propertyhive' )
        	);
        	set_transient( 'ph_pro_license_status', $return, HOUR_IN_SECONDS );
        	return $return;
        }

        $instance_id = get_option( 'propertyhive_pro_instance_id', '' );

        if ( empty($instance_id) )
    	{
    		$instance_id = wp_generate_password( 12, false ); // disable specialchars
    		update_option('propertyhive_pro_instance_id', $instance_id );
    	}

        $data = $this->get_data_for_license_check();

        $url = 'https://wp-property-hive.com/?';
    	$url .= 'wc-api=wc-am-api&';
    	$url .= 'wc_am_action=status&';
    	$url .= 'product_id=' . $product_id_and_package['product_id'] . '&';
    	$url .= 'instance=' . $instance_id . '&';
    	$url .= 'api_key=' . $license_key;

    	$response = wp_remote_post( $url, array(
    		'body' => $data,
    	) );
    	
    	if ( is_wp_error($response) )
    	{
    		// error for some reason. Return last known status
    		$previous_license_key_status = get_option( 'propertyhive_pro_license_key_status', '' );
    		if ( !empty($previous_license_key_status) )
    		{
    			set_transient( 'ph_pro_license_status', $previous_license_key_status, HOUR_IN_SECONDS );
    			return $previous_license_key_status;
    		}

        	$return = array(
        		'success' => false,
        		'error' => __( 'Failed to request license status', 'propertyhive' ) . ': ' . $response->get_error_message()
        	);
        	return $return;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) )
		{
			// error for some reason. Return last known status
    		$previous_license_key_status = get_option( 'propertyhive_pro_license_key_status', '' );
    		if ( !empty($previous_license_key_status) )
    		{
    			set_transient( 'ph_pro_license_status', $previous_license_key_status, HOUR_IN_SECONDS );
    			return $previous_license_key_status;
    		}

        	$return = array(
        		'success' => false,
        		'error' => sprintf( 
        			/* translators: %s: HTTP header response code */
        			__( 'Received response code %s when requesting license key status', 'propertyhive' ), 
        			wp_remote_retrieve_response_code( $response ) 
        		)
        	);

        	return $return;
		}

		$result = $response['body'];

		$body = json_decode($result, true);

		if ( json_last_error() !== JSON_ERROR_NONE ) 
		{
        	$return = array(
        		'success' => false,
                'error' => __( 'Failed to decode response when requesting license key status. Please try again', 'propertyhive' ) . ': ' . wp_json_encode( $result )
        	);
        	
        	// error for some reason. Return last known status
    		$previous_license_key_status = get_option( 'propertyhive_pro_license_key_status', '' );
    		if ( !empty($previous_license_key_status) )
    		{
    			set_transient( 'ph_pro_license_status', $previous_license_key_status, HOUR_IN_SECONDS );
    			return $previous_license_key_status;
    		}

        	return $return;
		}

		if ( isset($body['success']) )
		{
			if ( $body['success'] === true )
			{
				if ( isset($body['status_check']) && $body['status_check'] === 'active' )
				{
					$return = array(
		        		'success' => true
		        	);
				}
				else
				{
					$return = array(
		        		'success' => false,
		        		'error' => __( 'License key inactive', 'propertyhive' )
		        	);
				}
			}
			else
			{
				$return = array(
	        		'success' => false,
	        		'error' => __( 'Error when requesting license key status', 'propertyhive' ) . ': ' . $body['error']
	        	);
			}

			update_option( 'propertyhive_pro_license_key_last_checked', time() );
			update_option( 'propertyhive_pro_license_key_status', $return );

			set_transient( 'ph_pro_license_status', $return, HOUR_IN_SECONDS * 3 );
			return $return;
		}
		else
		{
			$return = array(
        		'success' => false,
                'error' => __( 'Something went wrong when requesting license key status', 'propertyhive' ) . ': ' . wp_json_encode( $body )
        	);
        	
			// error for some reason. Return last known status
    		$previous_license_key_status = get_option( 'propertyhive_pro_license_key_status', '' );
    		if ( !empty($previous_license_key_status) )
    		{
    			set_transient( 'ph_pro_license_status', $previous_license_key_status, HOUR_IN_SECONDS );
    			return $previous_license_key_status;
    		}

			return $return;
		}
	}

	private function pro_product_allows_additional_sites( $product_id )
	{
		return in_array( (int) $product_id, array( 14493, 14494, 23794, 14496, 14497, 23792 ), true );
	}

	private function pro_domain_plan_cache_key()
	{
		return 'ph_domain_plan_' . hash( 'sha256', wp_json_encode( array(
			get_option( 'propertyhive_pro_license_key', '' ),
			get_option( 'propertyhive_pro_instance_id', '' ),
		) ) );
	}

	/** Resolve a single Pro product without the domain guard or stale fallbacks. */
	private function get_pro_domain_action_product_id( $license_key, $instance )
	{
		$products = $this->request_pro_license_transfer_api( 'product_list', array(
			'api_key' => $license_key,
			'instance' => $instance,
		) );
		$resources = isset( $products['data']['product_list']['wc_subs_resources'] )
			? $products['data']['product_list']['wc_subs_resources'] : array();
		if ( ! is_array( $resources ) ) {
			throw new RuntimeException( 'Invalid licensing product information.' );
		}
		$matches = array();
		foreach ( $resources as $resource ) {
			if ( ! is_array( $resource ) || ! isset( $resource['product_id'] ) ) {
				continue;
			}
			$id = absint( $resource['product_id'] );
			if ( in_array( $id, array( 14492, 14493, 14494, 23794, 14495, 14496, 14497, 23792 ), true ) ) {
				$matches[$id] = $id;
			}
		}
		if ( 1 !== count( $matches ) ) {
			throw new RuntimeException( 'A single Property Hive Pro product could not be identified. Please contact support.' );
		}
		return (int) reset( $matches );
	}

	public function pro_license_domain_notice()
	{
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$check = $this->check_pro_license_site_identity();
		$pending = get_option( 'propertyhive_pro_pending_license_transfer', array() );
		$mismatch = is_wp_error( $check ) && 'ph_license_site_changed' === $check->get_error_code();
		if ( ! $mismatch && empty( $pending ) ) {
			return;
		}
		$key = get_option( 'propertyhive_pro_license_key', '' );
		$instance = get_option( 'propertyhive_pro_instance_id', '' );
		if ( '' === $key || '' === $instance ) {
			return;
		}
		$host = strtolower( rtrim( (string) wp_parse_url( get_site_url(), PHP_URL_HOST ), '.' ) );
		$old_host = base64_decode( (string) get_option( 'propertyhive_pro_site_host', '' ), true );
		if ( ! is_string( $old_host ) || '' === $old_host || hash( 'sha256', $old_host ) !== get_option( 'propertyhive_pro_site_identity', '' ) ) {
			$old_host = __( 'the previous domain', 'propertyhive' );
		}
		$has_pending = ! empty( $pending );
		$pending_mode = is_array( $pending ) && isset( $pending['mode'] ) ? $pending['mode'] : 'move';
		$product_id = get_transient( $this->pro_domain_plan_cache_key() );
		if ( false === $product_id ) {
			try {
				$product_id = $this->get_pro_domain_action_product_id( $key, $instance );
			} catch ( Exception $exception ) {
				$product_id = 0;
			}
			set_transient( $this->pro_domain_plan_cache_key(), $product_id, $product_id ? 5 * MINUTE_IN_SECONDS : MINUTE_IN_SECONDS );
		}
		$can_add = $this->pro_product_allows_additional_sites( $product_id );
		?>
		<div class="notice notice-warning">
			<p><strong><?php esc_html_e( 'Property Hive: site domain changed', 'propertyhive' ); ?></strong></p>
			<p><?php printf(
				esc_html__( 'This installation was associated with %1$s and is now running on %2$s.', 'propertyhive' ),
				esc_html( $old_host ), esc_html( $host )
			); ?></p>
			<?php if ( $has_pending ) : ?>
				<p><?php esc_html_e( 'An operation is unfinished. Resume the same operation to confirm its result. Contact support if you need to change your choice.', 'propertyhive' ); ?></p>
			<?php else : ?>
				<p><?php esc_html_e( 'Pro is inactive on this domain. Move the license here to release the previous installation\'s activation.', 'propertyhive' ); ?></p>
				<?php if ( $can_add ) : ?>
					<p><?php esc_html_e( 'Alternatively, activate this as an additional site to keep the previous activation and use another available allowance.', 'propertyhive' ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ( ! $product_id ) : ?>
				<p><?php esc_html_e( 'We could not confirm your license plan. Please try again shortly or contact support.', 'propertyhive' ); ?></p>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ph_move_pro_license">
				<?php wp_nonce_field( 'ph_move_pro_license' ); ?>
				<p>
				<?php if ( ! $has_pending || 'move' === $pending_mode ) : ?>
					<button type="submit" name="ph_license_domain_mode" value="move" class="button button-primary"><?php echo esc_html( $has_pending ? __( 'Resume license move', 'propertyhive' ) : __( 'Move my license to this domain', 'propertyhive' ) ); ?></button>
				<?php endif; ?>
				<?php if ( $can_add && ( ! $has_pending || 'additional' === $pending_mode ) ) : ?>
					<button type="submit" name="ph_license_domain_mode" value="additional" class="button"><?php echo esc_html( $has_pending ? __( 'Resume additional-site activation', 'propertyhive' ) : __( 'Activate as an additional site', 'propertyhive' ) ); ?></button>
				<?php endif; ?>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Make a direct licensing request without local cached fallbacks.
	 *
	 * @throws RuntimeException When the request cannot be confirmed.
	 */
	private function request_pro_license_transfer_api( $action, $parameters )
	{
	    $allowed_actions = array(
	        'product_list',
	        'status',
	        'deactivate',
	        'activate',
	    );

	    if ( ! in_array( $action, $allowed_actions, true ) ) {
	        throw new RuntimeException( 'Invalid license action.' );
	    }

	    // Matches the request format used by the existing licensing methods.
	    $url = add_query_arg(
	        array_merge(
	            $parameters,
	            array(
	                'wc-api'       => 'wc-am-api',
	                'wc_am_action' => $action,
	            )
	        ),
	        'https://wp-property-hive.com/'
	    );

	    $response = wp_remote_post(
	        $url,
	        array(
	            'timeout'     => 20,
	            'redirection' => 0,
	        )
	    );

	    if ( is_wp_error( $response ) ) {
	        throw new RuntimeException(
	            'The licensing server could not be reached. '
	            . 'The transfer has not been confirmed. Please retry.'
	        );
	    }

	    if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
	        throw new RuntimeException(
	            'The licensing server returned an unexpected HTTP response. '
	            . 'Please retry.'
	        );
	    }

	    $body = json_decode(
	        wp_remote_retrieve_body( $response ),
	        true
	    );

	    if (
	        JSON_ERROR_NONE !== json_last_error() ||
	        ! is_array( $body )
	    ) {
	        throw new RuntimeException(
	            'The licensing server returned an invalid response. Please retry.'
	        );
	    }

	    if (
	        ! isset( $body['success'] ) ||
	        true !== $body['success']
	    ) {
	        // Do not display the full response, which could contain credentials.
	        throw new RuntimeException(
	            sprintf(
	                'The licensing server did not confirm the "%s" request. '
	                . 'Please retry or contact Property Hive support.',
	                $action
	            )
	        );
	    }

	    if (
	        'status' === $action &&
	        (
	            ! isset( $body['status_check'] ) ||
	            ! in_array(
	                $body['status_check'],
	                array( 'active', 'inactive' ),
	                true
	            )
	        )
	    ) {
	        throw new RuntimeException(
	            'The licensing server did not return a recognised activation status.'
	        );
	    }

	    return $body;
	}

	public function handle_move_pro_license()
	{
	    if ( ! current_user_can( 'manage_options' ) ) {
	        wp_die(
	            'You do not have permission to move this license.',
	            'Property Hive',
	            array( 'response' => 403 )
	        );
	    }

	    if (
	        ! isset( $_SERVER['REQUEST_METHOD'] ) ||
	        'POST' !== $_SERVER['REQUEST_METHOD']
	    ) {
	        wp_die(
	            'This action requires a POST request.',
	            'Property Hive',
	            array( 'response' => 405 )
	        );
	    }

	    check_admin_referer( 'ph_move_pro_license' );
	    $mode = isset( $_POST['ph_license_domain_mode'] ) && is_string( $_POST['ph_license_domain_mode'] )
	        ? sanitize_key( wp_unslash( $_POST['ph_license_domain_mode'] ) ) : 'move';
	    if ( ! in_array( $mode, array( 'move', 'additional' ), true ) ) {
	        wp_die( 'Invalid license action.', 'Property Hive', array( 'response' => 400 ) );
	    }

	    $lock_option = 'propertyhive_pro_license_transfer_lock';
	    $pending_option = 'propertyhive_pro_pending_license_transfer';

	    // add_option() prevents two requests acquiring the same lock.
	    if ( ! add_option( $lock_option, time(), '', false ) ) {
	        wp_die(
	            'A license transfer is already running. Please wait before retrying.',
	            'Property Hive',
	            array(
	                'response'  => 409,
	                'back_link' => true,
	            )
	        );
	    }

	    $error = '';

	    try {
	        $host = wp_parse_url( get_site_url(), PHP_URL_HOST );

	        if ( ! is_string( $host ) || '' === $host ) {
	            throw new RuntimeException(
	                'Unable to determine this site’s domain.'
	            );
	        }

	        $host = strtolower( rtrim( $host, '.' ) );
	        $destination_hash = hash( 'sha256', $host );

	        $saved_identity = get_option(
	            'propertyhive_pro_site_identity',
	            ''
	        );

	        $license_key = get_option(
	            'propertyhive_pro_license_key',
	            ''
	        );

	        $local_instance = get_option(
	            'propertyhive_pro_instance_id',
	            ''
	        );

	        $pending = get_option( $pending_option, array() );

	        if ( ! is_array( $pending ) ) {
	            throw new RuntimeException(
	                'The saved transfer information is invalid. Please contact support.'
	            );
	        }

	        if ( empty( $pending ) ) {
	            if (
	                '' === $saved_identity ||
	                $saved_identity === $destination_hash
	            ) {
	                throw new RuntimeException(
	                    'There is no domain change requiring a license transfer.'
	                );
	            }

	            if ( '' === $license_key || '' === $local_instance ) {
	                throw new RuntimeException(
	                    'The original license key or instance ID is missing. '
	                    . 'Please manage the previous activation through your account '
	                    . 'or contact support.'
	                );
	            }

	            $product_id = $this->get_pro_domain_action_product_id( $license_key, $local_instance );
	            if ( 'additional' === $mode && ! $this->pro_product_allows_additional_sites( $product_id ) ) {
	                throw new RuntimeException( 'This product does not support activation as an additional site.' );
	            }

	            $pending = array(
	                'api_key'          => $license_key,
	                'product_id'       => $product_id,
	                'mode'             => $mode,
	                'old_instance'     => $local_instance,
	                'new_instance'     => wp_generate_password( 12, false ),
	                'source_hash'      => $saved_identity,
	                'destination_host'=> $host,
	                'destination_hash'=> $destination_hash,
	            );

	            update_option( $pending_option, $pending, false );

	            // Do not mutate the remote activation unless retry information
	            // has actually been saved.
	            if ( get_option( $pending_option ) !== $pending ) {
	                throw new RuntimeException(
	                    'Unable to save the license transfer. No remote changes were made.'
	                );
	            }
	        }

	        $required_fields = array(
	            'api_key',
	            'product_id',
	            'old_instance',
	            'new_instance',
	            'source_hash',
	            'destination_host',
	            'destination_hash',
	        );

	        foreach ( $required_fields as $field ) {
	            if (
	                ! isset( $pending[ $field ] ) ||
	                ! is_scalar( $pending[ $field ] ) ||
	                '' === (string) $pending[ $field ]
	            ) {
	                throw new RuntimeException(
	                    'The saved transfer information is incomplete. Please contact support.'
	                );
	            }
	        }

	        // Prevent a pending transfer being reused after another domain move
	        // or after someone has replaced the local license credentials.
	        if (
	            $pending['destination_hash'] !== $destination_hash ||
	            $pending['destination_host'] !== $host ||
	            $pending['api_key'] !== $license_key ||
	            ! in_array(
	                $local_instance,
	                array(
	                    $pending['old_instance'],
	                    $pending['new_instance'],
	                ),
	                true
	            ) ||
	            ! in_array(
	                $saved_identity,
	                array(
	                    $pending['source_hash'],
	                    $pending['destination_hash'],
	                ),
	                true
	            )
	        ) {
	            throw new RuntimeException(
	                'The site or license details changed during this transfer. '
	                . 'Please contact support before continuing.'
	            );
	        }

	        $pending_mode = isset( $pending['mode'] ) ? $pending['mode'] : 'move';
	        if ( ! in_array( $pending_mode, array( 'move', 'additional' ), true ) || $pending_mode !== $mode ) {
	            throw new RuntimeException( 'A different license operation is pending. Resume that operation or contact support.' );
	        }
	        $verified_product_id = $this->get_pro_domain_action_product_id( $pending['api_key'], $pending['old_instance'] );
	        if ( $verified_product_id !== (int) $pending['product_id'] ) {
	            throw new RuntimeException( 'The license product changed during this operation. Please contact support.' );
	        }
	        if ( 'additional' === $mode && ! $this->pro_product_allows_additional_sites( $verified_product_id ) ) {
	            throw new RuntimeException( 'This product does not support activation as an additional site.' );
	        }

	        // Invalidate all local active-status fallbacks before remote changes.
	        delete_transient( 'ph_pro_license_status' );
	        delete_option( 'propertyhive_pro_license_key_status' );
	        delete_option( 'propertyhive_pro_license_key_last_checked' );

	        $old_parameters = array(
	            'api_key'    => $pending['api_key'],
	            'product_id' => $pending['product_id'],
	            'instance'   => $pending['old_instance'],
	        );

	        $new_parameters = array(
	            'api_key'    => $pending['api_key'],
	            'product_id' => $pending['product_id'],
	            'instance'   => $pending['new_instance'],
	            'object'     => $host,
	        );

	        // A previous activation request may have succeeded even if its
	        // response was lost. Check before attempting activation again.
	        $new_status = $this->request_pro_license_transfer_api(
	            'status',
	            $new_parameters
	        );

	        if ( 'move' === $mode ) {
		        $old_status = $this->request_pro_license_transfer_api(
		            'status',
		            $old_parameters
		        );

		        if ( 'active' === $old_status['status_check'] ) {
		            $this->request_pro_license_transfer_api(
		                'deactivate',
		                $old_parameters
		            );

		            $old_status = $this->request_pro_license_transfer_api(
		                'status',
		                $old_parameters
		            );

		            if ( 'inactive' !== $old_status['status_check'] ) {
		                throw new RuntimeException(
		                    'The previous activation has not been confirmed inactive. '
		                    . 'Please retry.'
		                );
		            }
		        }

	        }

	        if ( 'active' !== $new_status['status_check'] ) {
	            $this->request_pro_license_transfer_api(
	                'activate',
	                $new_parameters
	            );
	        }

	        // Always confirm the destination before accepting it locally.
	        $new_status = $this->request_pro_license_transfer_api(
	            'status',
	            $new_parameters
	        );

	        if ( 'active' !== $new_status['status_check'] ) {
	            throw new RuntimeException(
	                'Activation on this domain has not been confirmed. '
	                . ( 'move' === $mode ? 'The previous activation may already have been released. ' : 'The previous activation is unchanged. ' )
	                . 'Please retry to finish the transfer.'
	            );
	        }

	        update_option(
	            'propertyhive_pro_instance_id',
	            $pending['new_instance']
	        );

	        if (
	            get_option( 'propertyhive_pro_instance_id' ) !==
	            $pending['new_instance']
	        ) {
	            throw new RuntimeException(
	                'The new activation exists, but its instance ID could not be saved. '
	                . 'Please retry.'
	            );
	        }

	        delete_transient( 'ph_pro_license_product_id_and_package' );
	        delete_option( 'ph_pro_last_known_license_product_id_and_package' );
	        delete_transient( 'ph_pro_license_status' );
	        delete_option( 'propertyhive_pro_license_key_status' );

	        update_option( 'propertyhive_pro_site_host', base64_encode( $host ), false );

	        // Commit the new domain last, after remote activation is confirmed.
	        update_option(
	            'propertyhive_pro_site_identity',
	            $destination_hash
	        );

	        if (
	            get_option( 'propertyhive_pro_site_identity' ) !==
	            $destination_hash
	        ) {
	            throw new RuntimeException(
	                'The new activation exists, but its domain could not be saved. '
	                . 'Please retry.'
	            );
	        }

	        delete_option( $pending_option );

	    } catch ( Exception $exception ) {
	        $error = $exception->getMessage();

	    } finally {
	        delete_option( $lock_option );
	    }

	    if ( '' === $error ) {
	        wp_safe_redirect( admin_url( 'admin.php?page=ph-settings&tab=licensekey' ) );
	        exit;
	    }

	    $message = '' === $error
	        ? ( 'additional' === $mode
	            ? 'This domain is activated as an additional site. The previous activation is unchanged.'
	            : 'Your Property Hive license has been moved to this domain.' )
	        : $error;

	    // Use a confirmation page so this method needs no additional
	    // redirect-message or admin-notice implementation.
	    wp_die(
	        '<p>' . esc_html( $message ) . '</p>'
	        . '<p><a href="' . esc_url( admin_url() ) . '">'
	        . esc_html__( 'Return to WordPress admin', 'propertyhive' )
	        . '</a></p>',
	        'Property Hive license transfer',
	        array(
	            'response' => '' === $error ? 200 : 400,
	        )
	    );
	}
}
