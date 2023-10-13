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

	private $pro_license_product_id_and_package = array();

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
		add_filter( 'properyhive_add_on_can_be_used', array( $this, 'ph_check_add_on_can_be_used' ), 10, 2 );
		add_filter( 'properyhive_add_on_can_be_updated', array( $this, 'ph_check_add_on_can_be_updated' ), 10, 2 );
	}

	public function ph_check_add_on_can_be_used( $can, $slug )
	{
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

                // Check this plugin is valid for the purchased type of plan (i.e. map search can't be used with an 'import only' plan)
            }
        }
        else
        {
            // Not a pro user. Check it's in the list of installed add ons
            $was_installed_pre_pro = false;

            $pre_installed_addons = get_option( 'propertyhive_pre_pro_add_ons', '' );
            if ( !is_array($pre_installed_addons) ) { $pre_installed_addons = array(); }

            if ( !empty($pre_installed_addons) )
            {
                foreach ( $pre_installed_addons as $pre_installed_addon )
                {
                    if ( isset( $pre_installed_addon['slug'] ) && $pre_installed_addon['slug'] == $slug )
                    {
                        $was_installed_pre_pro = true;
                    }
                }
            }

            if ( !$was_installed_pre_pro )
            {
                // show warning
                return false;
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
					return true;
				}

				// Check this plugin is valid for the purchased type of plan (i.e. map search can't be used with an 'import only' plan)
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

	private function get_data_for_license_check()
	{
		$license_type = $this->get_license_type();

		$data = array();
		$data['license_key_type'] = $license_type;
		$data['license_key'] = ( $license_type == 'old' ? get_option( 'propertyhive_license_key', '' ) : get_option( 'propertyhive_pro_license_key', '' ) );
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

		// Start by removing what we already know about the license
		update_option( 'propertyhive_license_key_details', '', 'no' );
		update_option( 'propertyhive_license_key_error', '', 'no' );

		$data = $this->get_data_for_license_check();

		$request = wp_remote_post( 'http://license.wp-property-hive.com/check-license.php', array(
			'method'      => 'POST',
			'timeout'     => 20,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'body'        => $data,
			'user-agent'  => 'PH/' . PH_VERSION . '; ' . get_bloginfo( 'url' )
		) );

		if ( is_wp_error( $request ) )
		{
			update_option( 'propertyhive_license_key_details', array(), 'no' );
			update_option( 'propertyhive_license_key_error', $request->get_error_message(), 'no' );
			return false;
		}

		if ( isset($request['body']) && $request['body'] == '' )
		{
			update_option( 'propertyhive_license_key_details', array(), 'no' );
			update_option( 'propertyhive_license_key_error', 'No response received when checking license', 'no' );
			return false;
		}

		$body = unserialize($request['body']);
		if ( $body !== FALSE && is_array($body) && !empty($body) )
		{
			update_option( 'propertyhive_license_key_details', $body, 'no' );
		}
		else
		{
			update_option( 'propertyhive_license_key_details', array(), 'no' );
			update_option( 'propertyhive_license_key_error', 'Failed to process response data: ' . print_r($request['body'], true), 'no' );
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
		$license = $this->get_pro_license_product_id_and_package($force);

		if ( isset($license['success']) && $license['success'] === true )
		{
			$license_key = get_option( 'propertyhive_pro_license_key', '' );

			$instance_id = get_option( 'propertyhive_pro_instance_id', '' );

	    	if ( empty($instance_id) )
	    	{
	    		$instance_id = wp_generate_password( 12, false ); // disable specialchars
	    		update_option('propertyhive_pro_instance_id', $instance_id );
	    	}

			// found API key. It's product ID will be stored in product_id
			$url = 'https://dev2022.wp-property-hive.com/?';
	    	$url .= 'wc-api=wc-am-api&';
	    	$url .= 'wc_am_action=activate&';
	    	$url .= 'instance=' . $instance_id . '&';
	    	$url .= 'object=' . parse_url( get_site_url(), PHP_URL_HOST ) . '&';
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
	        		'error' => __( 'Received response code ' . wp_remote_retrieve_response_code( $response ) . ' when activating license key status', 'propertyhive' )
	        	);
	        	return $return;
			}

			$result = $response['body'];

			$body = json_decode($result, true);

			if ( json_last_error() !== JSON_ERROR_NONE ) 
			{
				$return = array(
	        		'success' => false,
	        		'error' => __( 'Failed to decode response when activating license key status. Please try again', 'propertyhive' ) . ': ' . print_r( $result, true )
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
	        		'error' => __( 'Something went wrong when trying to activate license key', 'propertyhive' ) . ': ' . print_r($body, true)
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
		$license = $this->get_pro_license_product_id_and_package($force);

		if ( isset($license['success']) && $license['success'] === true )
		{
			$license_key = get_option( 'propertyhive_pro_license_key', '' );

			$instance_id = get_option( 'propertyhive_pro_instance_id', '' );

	    	if ( empty($instance_id) )
	    	{
	    		$instance_id = wp_generate_password( 12, false ); // disable specialchars
	    		update_option('propertyhive_pro_instance_id', $instance_id );
	    	}

			// found API key. It's product ID will be stored in product_id
			$url = 'https://dev2022.wp-property-hive.com/?';
	    	$url .= 'wc-api=wc-am-api&';
	    	$url .= 'wc_am_action=deactivate&';
	    	$url .= 'instance=' . $instance_id . '&';
	    	$url .= 'object=' . parse_url( get_site_url(), PHP_URL_HOST ) . '&';
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
	        		'error' => __( 'Received response code ' . wp_remote_retrieve_response_code( $response ) . ' when deactivating license key status', 'propertyhive' )
	        	);
	        	return $return;
			}

			$result = $response['body'];

			$body = json_decode($result, true);

			if ( json_last_error() !== JSON_ERROR_NONE ) 
			{
				$return = array(
	        		'success' => false,
	        		'error' => __( 'Failed to decode response when deactivating license key status. Please try again', 'propertyhive' ) . ': ' . print_r( $result, true )
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
	        		'error' => __( 'Something went wrong when trying to deactivate license key', 'propertyhive' ) . ': ' . print_r($body, true)
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
		if ( $force != true && !empty($this->pro_license_product_id_and_package) )
    	{
    		return $this->pro_license_product_id_and_package;
    	}

    	$license_key = get_option( 'propertyhive_pro_license_key', '' );

        if ( empty($license_key) )
        {
        	$return = array(
        		'success' => false,
        		'error' => __( 'No license key entered', 'propertyhive' )
        	);
        	$this->pro_license_product_id_and_package = $return;
        	return $return;
        }

        $instance_id = get_option( 'propertyhive_pro_instance_id', '' );

        if ( empty($instance_id) )
    	{
    		$instance_id = wp_generate_password( 12, false ); // disable specialchars
    		update_option('propertyhive_pro_instance_id', $instance_id );
    	}

        $data = $this->get_data_for_license_check();

        $url = 'https://dev2022.wp-property-hive.com/?';
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
        	$this->pro_license_product_id_and_package = $return;
        	return $return;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) )
		{
        	$return = array(
        		'success' => false,
        		'error' => __( 'Received response code ' . wp_remote_retrieve_response_code( $response ) . ' when requesting license key product list', 'propertyhive' )
        	);
        	$this->pro_license_product_id_and_package = $return;
        	return $return;
		}

		$result = $response['body'];

		$body = json_decode($result, true);

		if ( json_last_error() !== JSON_ERROR_NONE ) 
		{
        	$return = array(
        		'success' => false,
        		'error' => __( 'Failed to decode response when requesting license key product list. Please try again', 'propertyhive' ) . ': ' . print_r( $result, true )
        	);
        	$this->pro_license_product_id_and_package = $return;
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

					foreach ( $body['data']['product_list']['wc_subs_resources'] as $resource )
					{
						if ( isset($resource['product_id']) )
						{
							if ( in_array($resource['product_id'], array(13794, 13796, 13801)) )
							{
								$package = 'import';
							}
							elseif ( in_array($resource['product_id'], array(13797, 137981, 13802)) )
							{
								$package = 'complete';
							}
							$product_id = $resource['product_id'];
						}
					}

					if ( $package !== FALSE )
					{
						$return = array(
			        		'success' => true,
			        		'package' => $package,
			        		'product_id' => $product_id
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
		        		'error' => __( 'API key doesn\'t appear to belong to any orders', 'propertyhive' ) . ': ' . print_r($body, true)
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

			$this->pro_license_product_id_and_package = $return;
			return $return;
		}
		else
		{
			$return = array(
        		'success' => false,
        		'error' => __( 'Something went wrong when requesting license key product list', 'propertyhive' ) . ': ' . print_r($body, true)
        	);
        	$this->pro_license_product_id_and_package = $return;
			return $return;
		}
	}

	public function get_current_pro_license($force = false)
	{
		if ( $force != true && !empty($this->pro_license_status) )
    	{
    		return $this->pro_license_status;
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
        	$this->pro_license_status = $return;
        	return $return;
        }

        $instance_id = get_option( 'propertyhive_pro_instance_id', '' );

        if ( empty($instance_id) )
    	{
    		$instance_id = wp_generate_password( 12, false ); // disable specialchars
    		update_option('propertyhive_pro_instance_id', $instance_id );
    	}

        $data = $this->get_data_for_license_check();

        $url = 'https://dev2022.wp-property-hive.com/?';
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
    			return $previous_license_key_status;
    		}

        	$return = array(
        		'success' => false,
        		'error' => __( 'Failed to request license status', 'propertyhive' ) . ': ' . $response->get_error_message()
        	);
        	$this->pro_license_status = $return;
        	return $return;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) )
		{
			// error for some reason. Return last know status
    		$previous_license_key_status = get_option( 'propertyhive_pro_license_key_status', '' );
    		if ( !empty($previous_license_key_status) )
    		{
    			return $previous_license_key_status;
    		}

        	$return = array(
        		'success' => false,
        		'error' => __( 'Received response code ' . wp_remote_retrieve_response_code( $response ) . ' when requesting license key status', 'propertyhive' )
        	);
        	$this->pro_license_status = $return;
        	return $return;
		}

		$result = $response['body'];

		$body = json_decode($result, true);

		if ( json_last_error() !== JSON_ERROR_NONE ) 
		{
        	$return = array(
        		'success' => false,
        		'error' => __( 'Failed to decode response when requesting license key status. Please try again', 'propertyhive' ) . ': ' . print_r( $result, true )
        	);
        	$this->pro_license_status = $return;
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
		        		'error' => __( 'License key inactive', 'houzezpropertyfeed' )
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

			$this->pro_license_status = $return;
			return $return;
		}
		else
		{
			$return = array(
        		'success' => false,
        		'error' => __( 'Something went wrong when requesting license key status', 'propertyhive' ) . ': ' . print_r($body, true)
        	);
        	$this->pro_license_status = $return;
			return $return;
		}
	}
}