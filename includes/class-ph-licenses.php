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

    /** @var array|null Feature catalogue for entitlement checks in this request. */
    private $entitlement_features = null;

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

	}

    /**
     * Explain entitlement to one add-on without exposing licence credentials.
     * Pre-Pro ownership is independent of the selected licence type or Pro status.
     *
     * @param string $slug Add-on directory name.
     * @param bool   $force Refresh the Pro licence status for deliberate activation.
     * @param bool   $include_message Include a translated explanation; disable for checks before init.
     * @return array Permission, reason code and administrator-facing explanation.
     */
    public function get_add_on_entitlement( $slug, $force = false, $include_message = true ) {
        $result = $this->get_standard_add_on_entitlement( $slug, $force, $include_message );
        if ( ! empty( $result['can_use'] ) || ! is_string( $slug ) || 1 !== preg_match( '/^propertyhive-[a-z0-9_-]+$/D', $slug ) ) {
            return $result;
        }

        $whitelist = $this->get_site_whitelist( $force );
        if ( true === $whitelist['allowed'] && ( ! array_key_exists( 'add_ons', $whitelist ) || in_array( $slug, $whitelist['add_ons'], true ) ) ) {
            return array(
                'can_use' => true,
                'reason' => 'whitelisted',
                'message' => $include_message ? __( 'This site has been manually approved to use this add-on.', 'propertyhive' ) : '',
            );
        }
        return $result;
    }

    /**
     * Fetch site-wide permissions once per cache period, shared by every add-on.
     * Domain and wildcard matching is performed by the whitelist endpoint.
     */
    private function get_site_whitelist( $force = false ) {
        $site_url = home_url();
        $cache_key = 'ph_site_whitelist_' . md5( $site_url );
        if ( ! $force ) {
            $cached = $this->validate_site_whitelist( get_transient( $cache_key ) );
            if ( null !== $cached ) {
                return $cached;
            }
        }

        $whitelist = array( 'allowed' => false );
        $response = wp_remote_post(
            'https://license.wp-property-hive.com/whitelist.php',
            array(
                'timeout' => 5,
                'redirection' => 0,
                'sslverify' => true,
                'limit_response_size' => 65536,
                'headers' => array( 'Accept' => 'application/json' ),
                'body' => array( 'url' => $site_url ),
            )
        );
        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $validated = $this->validate_site_whitelist( json_decode( wp_remote_retrieve_body( $response ), true ) );
            if ( null !== $validated ) {
                $whitelist = $validated;
            }
        }

        // Cache refusals and failures too, avoiding a request for each active add-on.
        set_transient( $cache_key, $whitelist, $whitelist['allowed'] ? 24 * HOUR_IN_SECONDS : 15 * MINUTE_IN_SECONDS );
        return $whitelist;
    }

    /** Validate remote and cached permissions. Omitted add_ons means all add-ons. */
    private function validate_site_whitelist( $data ) {
        if ( ! is_array( $data ) || ! isset( $data['allowed'] ) || ! is_bool( $data['allowed'] ) ) {
            return null;
        }
        $result = array( 'allowed' => $data['allowed'] );
        if ( array_key_exists( 'add_ons', $data ) ) {
            if ( ! is_array( $data['add_ons'] ) ) {
                return null;
            }
            foreach ( $data['add_ons'] as $slug ) {
                if ( ! is_string( $slug ) || 1 !== preg_match( '/^propertyhive-[a-z0-9_-]+$/D', $slug ) ) {
                    return null;
                }
            }
            $result['add_ons'] = array_values( array_unique( $data['add_ons'] ) );
        }
        return $result;
    }

    /** Standard pre-Pro, free-feature and Pro-license entitlement rules. */
    private function get_standard_add_on_entitlement( $slug, $force = false, $include_message = true ) {
        $result = array( 'can_use' => false, 'reason' => 'feature_unavailable', 'message' => ( $include_message ? __( 'Feature information is unavailable. Please retry or contact support.', 'propertyhive' ) : '' ) );
        if ( ! is_string( $slug ) || '' === $slug ) {
            return $result;
        }
        $pre_pro_add_ons = get_option( 'propertyhive_pre_pro_add_ons', array() );
        if ( is_array( $pre_pro_add_ons ) ) {
            foreach ( $pre_pro_add_ons as $add_on ) {
                if ( is_array( $add_on ) && isset( $add_on['slug'] ) && $slug === $add_on['slug'] ) {
                    return array( 'can_use' => true, 'reason' => 'pre_pro', 'message' => ( $include_message ? __( 'Included in this site\'s pre-Pro add-ons. No Pro subscription is required for continued use.', 'propertyhive' ) : '' ) );
                }
            }
        }

        // Load the public catalogue once per request, including unsuccessful reads.
        if ( null === $this->entitlement_features ) {
            $features = get_ph_pro_features();
            $this->entitlement_features = is_array( $features ) ? $features : array();
        }
        $feature = false;
        foreach ( $this->entitlement_features as $candidate ) {
            if ( is_array( $candidate ) && isset( $candidate['wordpress_plugin_file'] ) && is_string( $candidate['wordpress_plugin_file'] ) && explode( '/', $candidate['wordpress_plugin_file'] )[0] === $slug ) {
                $feature = $candidate;
                break;
            }
        }
        if ( ! is_array( $feature ) || ! isset( $feature['plans'] ) || ! is_array( $feature['plans'] ) || empty( $feature['plans'] ) ) {
            return $result;
        }
        if ( in_array( 'free', $feature['plans'], true ) ) {
            return array( 'can_use' => true, 'reason' => 'free', 'message' => ( $include_message ? __( 'Free feature. No license is required.', 'propertyhive' ) : '' ) );
        }
        if ( 'pro' !== $this->get_license_type() ) {
            return array( 'can_use' => false, 'reason' => 'legacy_review', 'message' => ( $include_message ? __( 'No pre-Pro entitlement is recorded for this add-on. If you purchased it separately, contact support to verify your purchase.', 'propertyhive' ) : '' ) );
        }
        if ( '' === (string) get_option( 'propertyhive_pro_license_key', '' ) ) {
            return array( 'can_use' => false, 'reason' => 'missing_key', 'message' => ( $include_message ? __( 'A Pro license covering this feature is required. No pre-Pro entitlement is recorded for this add-on.', 'propertyhive' ) : '' ) );
        }
        if ( ! $this->is_valid_pro_license_key( $force ) ) {
            return array( 'can_use' => false, 'reason' => 'invalid_license', 'message' => ( $include_message ? __( 'The Pro license is inactive or invalid on this site.', 'propertyhive' ) : '' ) );
        }
        $package = $this->get_pro_license_product_id_and_package();
        if ( ! is_array( $package ) || ! isset( $package['success'], $package['package'] ) || true !== $package['success'] || ! is_string( $package['package'] ) ) {
            return array( 'can_use' => false, 'reason' => 'package_unavailable', 'message' => ( $include_message ? __( 'The Pro package could not be confirmed. Please retry or contact support.', 'propertyhive' ) : '' ) );
        }
        if ( ! in_array( $package['package'], $feature['plans'], true ) ) {
            return array( 'can_use' => false, 'reason' => 'wrong_package', 'message' => ( $include_message ? __( 'This feature is not included in the current Pro package.', 'propertyhive' ) : '' ) );
        }
        return array( 'can_use' => true, 'reason' => 'pro', 'message' => ( $include_message ? __( 'Included in the current Pro package.', 'propertyhive' ) : '' ) );
    }

    public function ph_check_add_on_can_be_used( $can, $slug ) {
        // Preserve a restriction already imposed by another integration.
        if ( false === $can ) {
            return false;
        }
        // Add-ons check permission during plugins_loaded, before translations are ready.
        $entitlement = $this->get_add_on_entitlement( $slug, false, false );
        return $entitlement['can_use'];
    }

	public function ph_check_add_on_can_be_updated( $can, $slug )
	{
        $license_type = $this->get_license_type();

		if ( $license_type == 'old' )
		{
			if ( 'r34p1773mp' === trim((string)get_option( 'propertyhive_license_key', '' ))) 
			{
			    return false;
			}

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

		// Historical add-on entitlements are license-verification data, not optional usage telemetry.
		// Do not reconstruct this record from the site's currently installed plugins.
		$pre_pro_add_ons = get_option( 'propertyhive_pre_pro_add_ons', null );
		if ( is_array( $pre_pro_add_ons ) ) {
			$data['pre_pro_add_ons'] = $pre_pro_add_ons;
		}

		$data['ph_version'] = PH_VERSION;

		if ( !function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

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

		$share_usage_data = get_option( 'propertyhive_data_sharing' ) != 'no';

		$plugins        = get_plugins(); // Fetch all plugins with details
		$active_plugins = get_option('active_plugins', array());

		$active_plugins_data = [];
		$inactive_plugins_data = [];

		// Loop through plugins to separate active and inactive along with version
		foreach ($plugins as $plugin_path => $plugin_data) 
		{
		    $plugin_slug = explode( '/', wp_normalize_path( $plugin_path ) )[0];
		    if ( ! $share_usage_data && 'propertyhive' !== $plugin_slug && 0 !== strpos( $plugin_slug, 'propertyhive-' ) ) {
		        continue;
		    }

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

		if ( $share_usage_data )
		{
			// Not opted-out

			// Retrieve current theme info
			$theme_data = wp_get_theme();
			$theme      = $theme_data->Name . ' ' . $theme_data->Version;

			$data['php_version'] = phpversion();
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

			$data['inactive_plugins'] = $inactive_plugins_data;

			$data['locale']           = get_locale();

			// Import info
			$property_import = get_option( 'propertyhive_property_import', array() );
			if ( !is_array($property_import) )
			{
				$property_import = array();
			}
			$formats = array();
			$reapit_customer_codes = array();
            foreach ( $property_import as $import_id => $options )
            {
            	if ( is_array($options) && isset($options['running']) && $options['running'] == '1' )
            	{
            		if ( isset($options['format']) && $options['format'] == 'xml' )
            		{
            			$options['format'] .= ' (' . esc_url( isset($options['xml_url']) && is_string($options['xml_url']) ? $options['xml_url'] : '' ) . ')';
            		}
            		elseif ( isset($options['format']) && $options['format'] == 'csv' )
            		{
            			$options['format'] .= ' (' . esc_url( isset($options['csv_url']) && is_string($options['csv_url']) ? $options['csv_url'] : '' ) . ')';
            		}
            		elseif ( isset($options['format']) && $options['format'] == 'json_reapit_foundations' )
					{
					    if ( isset( $options['customer_id'] ) && is_string( $options['customer_id'] ) )
					    {
					        $customer_code = trim( $options['customer_id'] );

					        if ( $customer_code !== '' )
					        {
					            $reapit_customer_codes[] = $customer_code;
					        }
					    }
					}
            		elseif ( 
            			isset($options['format']) && 
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
			
			$data['reapit_customer_codes'] = array_values(
			    array_unique( $reapit_customer_codes )
			);

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

		$request = wp_remote_post( 'https://license.wp-property-hive.com/check-license.php', array(
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

            // Keep credentials and installation details out of URL access logs.
            $url = 'https://wp-property-hive.com/?wc-api=wc-am-api';
            $data = array(
                'wc_am_action' => 'activate',
                'instance'     => $instance_id,
                'object'       => wp_parse_url( get_site_url(), PHP_URL_HOST ),
                'product_id'   => $license['product_id'],
                'api_key'      => $license_key,
            );

	    	$response = wp_remote_post( $url, array(
                'redirection' => 0,
                'body'        => $data,
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

            // Keep credentials and installation details out of URL access logs.
            $url = 'https://wp-property-hive.com/?wc-api=wc-am-api';
            $data = array(
                'wc_am_action' => 'deactivate',
                'instance'     => $instance_id,
                'object'       => wp_parse_url( get_site_url(), PHP_URL_HOST ),
                'product_id'   => $license['product_id'],
                'api_key'      => $license_key,
            );

	    	$response = wp_remote_post( $url, array(
                'redirection' => 0,
                'body'        => $data,
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
					$this->clear_pro_license_caches();

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

        // Preserve verification/usage data while moving API parameters into the body.
        $url = 'https://wp-property-hive.com/?wc-api=wc-am-api';
        $data = array_merge( $data, array(
                'wc_am_action' => 'product_list',
                'instance'     => $instance_id,
                'api_key'      => $license_key,
        ) );

    	$response = wp_remote_post( $url, array(
                'redirection' => 0,
                'body'        => $data,
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

        // Preserve verification/usage data while moving API parameters into the body.
        $url = 'https://wp-property-hive.com/?wc-api=wc-am-api';
        $data = array_merge( $data, array(
                'wc_am_action' => 'status',
                'product_id'   => $product_id_and_package['product_id'],
                'instance'     => $instance_id,
                'api_key'      => $license_key,
        ) );

    	$response = wp_remote_post( $url, array(
                'redirection' => 0,
                'body'        => $data,
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


	private function clear_pro_license_caches()
	{
		delete_transient( 'ph_pro_license_product_id_and_package' );
		delete_option( 'ph_pro_last_known_license_product_id_and_package' );
		delete_transient( 'ph_pro_license_status' );
		delete_option( 'propertyhive_pro_license_key_status' );
		delete_option( 'propertyhive_pro_license_key_last_checked' );
	}
}
