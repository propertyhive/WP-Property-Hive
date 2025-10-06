<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Serve properties using the REST API
 *
 * @class 		PH_Rest_Api
 * @version		1.0.0
 * @package		PropertyHive/Classes/
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Rest_Api {

	/** @var PH_Rest_Api The single instance of the class */
	protected static $_instance = null;

	/**
	 * Main PH_Rest_Api Instance.
	 *
	 * Ensures only one instance of PH_Rest_Api is loaded or can be loaded.
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
		// Property
		add_filter( 'rest_property_query', array( $this, 'modify_rest_property_query' ), 10, 2 );
		add_filter( 'rest_prepare_property', array( $this, 'rest_prepare_property' ), 10, 3 );
		add_action( 'rest_api_init', array( $this, 'register_rest_api_property_fields' ), 99 );
		add_filter( 'rest_property_collection_params', array( $this, 'modify_rest_order_by' ), 10, 1 );
		add_action( "rest_after_insert_property", array( $this, 'ensure_key_property_fields_set' ), 10, 3 );

		// Enquiry - Create
		add_action( 'rest_api_init', array( $this, 'register_enquiry_rest_route' ), 11 );
		add_action( "rest_after_insert_enquiry", array( $this, 'ensure_key_enquiry_fields_set' ), 10, 3 );

		// Enquiry - Read
		add_filter( 'pre_get_posts', array( $this, 'restrict_enquiry_rest_listing' ));
		add_filter( 'rest_pre_dispatch', array( $this, 'block_enquiry_rest_listing' ), 10, 3);
		add_action( 'rest_api_init', array( $this, 'register_rest_api_enquiry_fields' ), 99 );

		// Office
		add_action( 'rest_api_init', array( $this, 'register_rest_api_office_fields' ), 99 );
	}

	// Restrict listing of enquiries via REST API
	public function restrict_enquiry_rest_listing(WP_Query $query) 
	{
	    if ( !is_admin() && defined('REST_REQUEST') && REST_REQUEST ) 
	    {
	        $post_type = $query->get('post_type');

	        // Check if it's the 'enquiry' CPT
	        if ( $post_type === 'enquiry' ) 
	        {
	            if ( !current_user_can('manage_propertyhive') ) 
	        	{
	                // Force an empty result
	                $query->set('post__in', [0]);
	            }
	        }
	    }
	}

	public function block_enquiry_rest_listing($response, $server, $request) 
	{
	    if ( $request->get_route() === '/wp/v2/enquiry' ) 
	    {
	    	$current_user = wp_get_current_user();

	        if ( !current_user_can('manage_propertyhive') ) 
	        {
	            return new WP_Error(
	                'rest_forbidden',
	                __('You are not allowed to list enquiries.', 'propertyhive'),
	                ['status' => 403]
	            );
	        }
	    }
	    return $response;
	}

	public function register_rest_api_enquiry_fields()
	{
		$field_array = array(
			'office',
			'negotiator',
			'status',
			'source',
			'properties',
			'details',
		);

		$field_array = apply_filters( 'propertyhive_rest_api_enquiry_fields', $field_array );

		foreach ( $field_array as $field )
		{
			register_rest_field( 'enquiry',
		        $field,
		        array(
		            'get_callback'  => function( $object, $field_name, $request )
		            {
		            	$enquiry = new PH_Enquiry($object['id']);

		            	$return = '';

		            	switch ($field_name)
		            	{
		            		case "office": 
		            		{ 
		            			$return = array(
		            				'name' => get_the_title($enquiry->_office_id),
		            			); 
		            			break; 
							}
							case "negotiator": 
		            		{ 
		            			$user = get_userdata( $enquiry->_negotiator_id );

		            			$return = array(
		            				'name' => ( $user !== false ) ? $user->display_name : '',
		            			); 
		            			break; 
							}
							case "properties": 
		            		{ 
		            			$return = array();

		            			$property_ids = $enquiry->get_properties();

		            			if ( !empty($property_ids) )
		            			{
		            				foreach ( $property_ids as $property_id )
		            				{
		            					$property = new PH_Property((int)$property_id);

		            					$return[] = array(
		            						'id' => (int)$property_id,
		            						'address' => $property->get_formatted_full_address()
		            					);
		            				}
		            			}
		            			break; 
							}
							case "details": 
		            		{ 
		            			$ignore_keys = array(
					                '_status',
					                '_source',
					                '_negotiator_id',
					                '_office_id',
					                '_action',
					                '_contact_id',
					                '_property_id',
					                'property_id',
					            );

		            			$enquiry_meta = get_metadata( 'post', $object['id'] );

		            			$return = array();

		            			foreach ($enquiry_meta as $key => $value)
					            {
					                if ( !in_array( $key, $ignore_keys ) && substr( $key, 0, 1 ) != '_' && strpos($key, 'captcha') === FALSE )
					                {
					                	$return[trim($key, "_")] = $value;
					                }
					            }
		            			break; 
							}
		            		default:
		            		{
		            			$return = $enquiry->{$field_name};			            	
				            }
				        }

				        $return = apply_filters( 'propertyhive_rest_api_enquiry_field_callback', $return, $field_name, $enquiry );
				        return $return;
		            },
		            'schema' => null,
		        )
		    );
		}
	}

	public function register_enquiry_rest_route()
	{
	    register_rest_route('wp/v2', '/enquiry', array(
	        array(
	            'methods'   => WP_REST_Server::CREATABLE,
	            'callback'  => array( $this, 'handle_enquiry_post' ),
	            'permission_callback' => array( $this, 'enquiry_permission_check' )
	        ),
	    ));
	}

	public function enquiry_permission_check() 
	{
	    // Check if the user is authenticated
	    if (is_user_logged_in() || apply_filters('rest_authentication_errors', null) === null) {
	        // Check if the user has the capability to create enquiries (e.g., 'edit_posts')
	        if (current_user_can('edit_posts')) {
	            return true;
	        } else {
	            return new WP_Error('rest_forbidden', 'You do not have permissions to create enquiries.', array('status' => 403));
	        }
	    } else {
	        return new WP_Error('rest_forbidden', 'You are not authenticated.', array('status' => 403));
	    }
	}

	public function handle_enquiry_post(WP_REST_Request $request)
	{
	    // Handle the creation of the enquiry post
	    $params = $request->get_params();

	    // validate office ID
	    if ( isset($params['office_id']) && !empty($params['office_id']) ) 
	    {
	    	if ( get_post_type((int)$params['office_id']) != 'office' )
	    	{
		    	return new WP_Error('rest_enquiry_error', __( 'Invalid office ID passed', 'propertyhive' ) . ' ('. (int)$params['office_id'] . ')', array('status' => 400));
		    }
	    }

	    // validate negotiator ID
	    if ( isset($params['negotiator_id']) && !empty($params['negotiator_id']) ) 
	    {
	    	if ( get_userdata((int)$params['negotiator_id']) === false )
	    	{
		    	return new WP_Error('rest_enquiry_error', __( 'Invalid negotiator ID passed', 'propertyhive' ) . ' ('. (int)$params['negotiator_id'] . ')', array('status' => 400));
		    }
	    }

	    // validate source
	    $valid_sources = array(
            'office' => __( 'Office', 'propertyhive' ),
            'website' => __( 'Website', 'propertyhive' )
        );

        $valid_sources = apply_filters( 'propertyhive_enquiry_sources', $valid_sources );

	    if ( isset($params['source']) && !empty($params['source']) ) 
	    {
	    	if ( !array_key_exists($params['source'], $valid_sources) )
	    	{
			    return new WP_Error('rest_enquiry_error', __( 'Invalid source passed. Should be one of', 'propertyhive' ) . ': ' . implode(", ", array_keys($valid_sources)), array('status' => 400));
			}
	    }

	    if ( isset($params['property_id']) && !empty($params['property_id']) ) 
	    {
	    	if ( !is_array($params['property_id']) ) 
	    	{ 
	    		$property_ids = array($params['property_id']); 
	    	}
	    	else
	    	{
	    		$property_ids = $params['property_id'];
	    	}

	    	foreach ( $property_ids as $property_id )
	    	{
	    		if ( get_post_type((int)$property_id) != 'property' )
	    		{
	    			return new WP_Error('rest_enquiry_error', __( 'Invalid property ID passed', 'propertyhive' ) . ' ('. (int)$property_id . ')', array('status' => 400));
	    		}
	    	}
	    }

	    $title = ( isset($params['title']) && !empty($params['title']) ? $params['title'] : '' );
	    if (empty($title))
	    {
	    	$title = 'Enquiry';
	    	if ( isset($params['name']) && !empty($params['name']) )
	    	{
	    		$title .= ' from ' . $params['name'];
	    	}
	    	if ( isset($params['property_id']) && !empty($params['property_id']) )
	    	{
	    		if ( is_array($params['property_id']) )
	    		{
	    			$title .= ' about ' . count($params['property_id']) . ' properties';
		    	}
		    	else
		    	{
		    		$title .= ' about ' . get_the_title($params['property_id']);
		    	}
	    	}
	    }
	    $data = array(
	        'post_type'    => 'enquiry',
	        'post_status'  => 'publish',
	        'post_title'   => sanitize_text_field($title),
	        'post_content'   => '',
	        'comment_status' => 'closed',
            'ping_status'    => 'closed',
	    );

	    $post_id = wp_insert_post($data, true);

	    if ( is_wp_error($post_id) ) 
	    {
	        return new WP_Error('rest_enquiry_error', $post_id->get_error_message(), array('status' => 500));
	    }

	    // Status
	    if ( isset($params['status']) && !empty($params['status']) && in_array($params['status'], array('open', 'closed')) )
	    {
	    	update_post_meta( $post_id, '_status', sanitize_text_field($params['status']) );
	    }
	    else
	    {
			update_post_meta( $post_id, '_status', 'open' );
	    }

		if ( isset($params['office_id']) && !empty($params['office_id']) && is_numeric($params['office_id']) )
	    {
	    	// Should be a valid office ID as this has already been validated by this point
	    	update_post_meta( $post_id, '_office_id', (int)$params['office_id'] );
	    }
	    else
	    {
	        $primary_office_id = '';
	        $args = array(
	            'post_type' => 'office',
	            'nopaging' => true
	        );
	        $office_query = new WP_Query($args);

	        if ($office_query->have_posts())
	        {
	            while ($office_query->have_posts())
	            {
	                $office_query->the_post();

	                if (get_post_meta(get_the_ID(), 'primary', TRUE) == '1')
	                {
	                    $primary_office_id = get_the_ID();
	                }
	            }
	        }
	        $office_query->reset_postdata();

	        update_post_meta( $post_id, '_office_id', $primary_office_id );
	    }

	    if ( isset($params['negotiator_id']) && !empty($params['negotiator_id']) && is_numeric($params['negotiator_id']) )
	    {
	    	// Should be a valid neg ID as this has already been validated by this point
	    	update_post_meta( $post_id, '_negotiator_id', (int)$params['negotiator_id'] );
	    }
	    
		// Source
		if ( isset($params['source']) && !empty($params['source']) && array_key_exists($params['source'], $valid_sources) )
	    {
	    	update_post_meta( $post_id, '_source', sanitize_text_field($params['source']) );
	    }
	    else
	    {
			update_post_meta( $post_id, '_source', 'website' );
	    }

	    if ( isset($params['name']) && !empty($params['name']) )
	    {
	    	update_post_meta( $post_id, 'name', sanitize_text_field($params['name']) );
	    }

	    if ( isset($params['email_address']) && !empty($params['email_address']) )
	    {
	    	update_post_meta( $post_id, 'email_address', sanitize_email($params['email_address']) );
	    }

	    if ( isset($params['telephone_number']) && !empty($params['telephone_number']) )
	    {
	    	update_post_meta( $post_id, 'telephone_number', sanitize_text_field($params['telephone_number']) );
	    }

	    if ( isset($params['message']) && !empty($params['message']) )
	    {	
	    	update_post_meta( $post_id, 'message', sanitize_textarea_field($params['message']) );
	    }

	    if ( isset($params['property_id']) && !empty($params['property_id']) && ( is_numeric($params['property_id']) || is_array($params['property_id']) ) )
	    {
	    	// Should be a valid property  ID as this has already been validated by this point
	    	$property_ids = $params['property_id'];
	    	if ( !is_array($property_ids) ) { $property_ids = array($property_ids); }

	    	foreach ( $property_ids as $property_id )
	    	{
		    	add_post_meta( $post_id, 'property_id', (int)$property_id );
		    }
	    }

	    return new WP_REST_Response(array('ID' => $post_id), 201);
	}

	public function ensure_key_property_fields_set( $post, $request, $creating )
	{
		// Country / price actual
		$country = get_post_meta( $post->ID, '_address_country', true );
		if ( $country == '' )
		{
			$default_country = get_option( 'propertyhive_default_country', 'GB' );
			update_post_meta( $post->ID, '_address_country', $default_country );
		}

		$ph_countries = new PH_Countries();
		$ph_countries->update_property_price_actual( $post->ID );

		// Department
		$department = get_post_meta( $post->ID, '_department', true );
		if ( $department == '' )
		{
			$primary_department = get_option( 'propertyhive_primary_department', 'residential-sales' );
			update_post_meta( $post->ID, '_department', $primary_department );
		}

		// On Market
		$on_market = get_post_meta( $post->ID, '_on_market', true );
		if ( $on_market != 'yes' )
		{
			update_post_meta( $post->ID, '_on_market', '' );
		}

		// Featured
		$featured = get_post_meta( $post->ID, '_featured', true );
		if ( $featured != 'yes' )
		{
			update_post_meta( $post->ID, '_featured', '' );
		}

		// Office
		$office_id = get_post_meta( $post->ID, '_office_id', true );
		if ( $office_id == '' )
		{
			$primary_office_id = '';
			$args = array(
	            'post_type' => 'office',
	            'nopaging' => true
	        );
	        $office_query = new WP_Query($args);
	        
	        if ($office_query->have_posts())
	        {
	            while ($office_query->have_posts())
	            {
	                $office_query->the_post();

	                if (get_post_meta(get_the_ID(), 'primary', TRUE) == '1')
	                {
	                	$primary_office_id = get_the_ID();
	                }
	            }
	        }
	        $office_query->reset_postdata();
	        
			update_post_meta( $post->ID, '_office_id', $primary_office_id );
		}
	}

	public function modify_rest_order_by($params)
	{
		$fields = ["price-asc", "price-desc"];

		foreach ( $fields as $key => $value ) 
		{
			$params['orderby']['enum'][] = $value;
		}

		return $params;
	}

	public function modify_rest_property_query($args, $request)
	{
		/*if ( !isset( $args['meta_query'] ) )
		{
			$args['meta_query'] = array();
		}*/

		$PH_Query = new PH_Query();

		// Meta query
		$args['meta_query'] = $PH_Query->get_meta_query();
        
        // Tax query
        $args['tax_query'] = $PH_Query->get_tax_query();

        // Date query
		$args['date_query'] = $PH_Query->get_date_query();

		$ordering   = $PH_Query->get_search_results_ordering_args();
		$args['orderby'] = $ordering['orderby'] . ' post_title';
		$args['order'] = $ordering['order'];
		if ( isset( $ordering['meta_key'] ) )
			$args['meta_key'] = $ordering['meta_key'];

		$args = apply_filters( 'propertyhive_rest_api_query_args', $args );
		
		return $args;
	}

	public function register_rest_api_property_fields()
	{
		$field_array = array(
			'department',
			'reference_number',
			'address_street',
			'address_two',
			'address_three',
			'address_four',
			'address_postcode',
			'address_country',
			'latitude',
			'longitude',
			'price_actual',
			'price',
			'price_from',
			'price_to',
			'price_units',
			'rent_from',
			'rent_to',
			'rent_units',
			'price_formatted',
			'rent_frequency',
			'currency',
			'price_qualifier',
			'sale_by',
			'tenure',
			'council_tax_band',
			'deposit',
			'furnished',
			'available_date',
			'bedrooms',
			'bathrooms',
			'reception_rooms',
			'property_type',
			'parking',
			'outside_space',
			'for_sale',
			'to_rent',
			'floor_area_from',
			'floor_area_to',
			'floor_area_units',
			'site_area_from',
			'site_area_to',
			'site_area_units',
			'on_market',
			'featured',
			'location',
			'availability',
			'marketing_flag',
			'features',
			'description',
			'office',
			'negotiator',
			'images',
			'floorplans',
			'brochures',
			'epcs',
			'virtual_tours',
			'views_total',
			'views_last_7_days',
			'views_last_14_days',
			'views_last_30_days',
		);

		$field_array = apply_filters( 'propertyhive_rest_api_property_fields', $field_array );

		foreach ( $field_array as $field )
		{
			register_rest_field( 'property',
		        $field,
		        array(
		            'get_callback'  => function( $object, $field_name, $request )
		            {
		            	$property = new PH_Property($object[ 'id' ]);

		            	$return = '';

		            	switch ($field_name)
		            	{
		            		case "price":
		            		{
		            			if ( $property->_poa != 'yes' )
		            			{
		            				$department = $property->_department;
								    if ( ph_get_custom_department_based_on( $department ) !== false )
							        {
							        	$department = ph_get_custom_department_based_on( $department );
							        }
		            				if ( $department == 'residential-lettings' ) { $return = $property->_rent; }else{ $return = $property->_price; } 
		            			}
		            			break;
		            		}
		            		case "price_from":
		            		case "price_to":
		            		{
		            			if ( $property->_price_poa != 'yes' )
		            			{
		            				$return = $property->{'_' . $field_name};
		            			}
		            			break;
		            		}
		            		case "rent_from":
		            		case "rent_to":
		            		{
		            			if ( $property->_rent_poa != 'yes' )
		            			{
		            				$return = $property->{'_' . $field_name};
		            			}
		            			break;
		            		}
		            		case "price_formatted": { $return = $property->get_formatted_price(); break; }
		            		case "features": { $return = $property->get_features(); break; }
		            		case "description": { $return = $property->get_formatted_description(); break; }
		            		case "office": 
		            		{ 
		            			$return = array(
		            				'name' => $property->office_name,
		            				'address' => $property->office_address,
		            				'telephone_number' => $property->office_telephone_number,
		            				'email_address' => $property->office_email_address,
		            			); 
		            			break; 
							}
							case "negotiator": 
		            		{ 
		            			$return = array(
		            				'name' => $property->negotiator_name,
		            				'telephone_number' => $property->negotiator_telephone_number,
		            				'email_address' => $property->negotiator_email_address,
		            			); 
		            			break; 
							}
							case "images":
							{
								$images_array = array();
								if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
								{
									$image_urls = $property->_photo_urls;
									if ( !is_array($image_urls) ) { $image_urls = array(); }

									foreach ( $image_urls as $image_url )
									{
										if ( isset($image_url['url']) )
										{
											$images_array[] = array(
												'url' => $image_url['url'],
												'large' => $image_url['url'],
												'medium' => $image_url['url'],
												'thumbnail' => $image_url['url'],
											);
										}
									}
								}
								else
								{
									$image_ids = $property->get_gallery_attachment_ids();
									foreach ( $image_ids as $image_id )
									{
										$image_url = wp_get_attachment_url($image_id);
										if ($image_url !== false)
										{
											$large_image = wp_get_attachment_image_src( $image_id, 'large' );
											$medium_image = wp_get_attachment_image_src( $image_id, 'medium' );
											$thumbnail_image = wp_get_attachment_image_src( $image_id, 'thumbnail' );

											$images_array[] = array(
												'url' => $image_url,
												'large' => ( $large_image !== FALSE ? $large_image[0] : $image_url ),
												'medium' => ( $medium_image !== FALSE ? $medium_image[0] : $image_url ),
												'thumbnail' => ( $thumbnail_image !== FALSE ? $thumbnail_image[0] : $image_url ),
											);
										}
									}
								}
								$return = $images_array;
								break;
							}
							case "floorplans":
							{
								$floorplans_array = array();
								if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
								{
									$floorplan_urls = $property->_floorplan_urls;
									if ( !is_array($floorplan_urls) ) { $floorplan_urls = array(); }

									foreach ( $floorplan_urls as $floorplan_url )
									{
										if ( isset($floorplan_url['url']) )
										{
											$floorplans_array[] = array(
												'url' => $floorplan_url['url'],
												'large' => $floorplan_url['url'],
												'medium' => $floorplan_url['url'],
												'thumbnail' => $floorplan_url['url'],
											);
										}
									}
								}
								else
								{
									$floorplan_ids = $property->get_floorplan_attachment_ids();
									foreach ( $floorplan_ids as $floorplan_id )
									{
										$floorplan_url = wp_get_attachment_url($floorplan_id);
										if ($floorplan_url !== false)
										{
											$large_image = wp_get_attachment_image_src( $floorplan_id, 'large' );
											$medium_image = wp_get_attachment_image_src( $floorplan_id, 'medium' );
											$thumbnail_image = wp_get_attachment_image_src( $floorplan_id, 'thumbnail' );

											$floorplans_array[] = array(
												'url' => $floorplan_url,
												'large' => ( $large_image !== FALSE ? $large_image[0] : $floorplan_url ),
												'medium' => ( $medium_image !== FALSE ? $medium_image[0] : $floorplan_url ),
												'thumbnail' => ( $thumbnail_image !== FALSE ? $thumbnail_image[0] : $floorplan_url ),
											);
										}
									}
								}
								$return = $floorplans_array;
								break;
							}
							case "brochures":
							{
								$brochures_array = array();
								if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
								{
									$brochure_urls = $property->_brochure_urls;
									if ( !is_array($brochure_urls) ) { $brochure_urls = array(); }

									foreach ( $brochure_urls as $brochure_url )
									{
										if ( isset($brochure_url['url']) )
										{
											$brochures_array[] = array(
												'url' => $brochure_url['url'],
											);
										}
									}
								}
								else
								{
									$brochure_ids = $property->get_brochure_attachment_ids();
									foreach ( $brochure_ids as $brochure_id )
									{
										$brochure_url = wp_get_attachment_url($brochure_id);
										if ($brochure_url !== false)
										{
											$brochures_array[] = array(
												'url' => $brochure_url,
											);
										}
									}
								}
								$return = $brochures_array;
								break;
							}
							case "epcs":
							{
								$epcs_array = array();
								if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
								{
									$epc_urls = $property->_epc_urls;
									if ( !is_array($epc_urls) ) { $epc_urls = array(); }

									foreach ( $epc_urls as $epc_url )
									{
										if ( isset($epc_url['url']) )
										{
											$epcs_array[] = array(
												'url' => $epc_url['url'],
											);
										}
									}
								}
								else
								{
									$epc_ids = $property->get_epc_attachment_ids();
									foreach ( $epc_ids as $epc_id )
									{
										$epc_url = wp_get_attachment_url($epc_id);
										if ($epc_url !== false)
										{
											$epcs_array[] = array(
												'url' => $epc_url,
											);
										}
									}
								}
								$return = $epcs_array;
								break;
							}
		            		case "virtual_tours":
		            		{
		            			$return = $property->get_virtual_tours();
		            			break;
		            		}
		            		case "views_last_7_days":
		            		case "views_last_14_days":
		            		case "views_last_30_days":
		            		case "views_total":
		            		{
		            			$view_statistics = $property->_view_statistics;
					            if ( !is_array($view_statistics) )
					            {
					                $view_statistics = array();
					            }

					            $views = 0;

					            $date_from = '2001-01-01';
					            switch ($field_name)
					            {
					            	case "views_last_7_days": { $date_from = date("Y-m-d", strtotime('7 days ago')); break; }
				            		case "views_last_14_days": { $date_from = date("Y-m-d", strtotime('14 days ago')); break; }
				            		case "views_last_30_days": { $date_from = date("Y-m-d", strtotime('30 days ago')); break; }
						        }
					            $date_from = strtotime($date_from);

					            $date_to = date("Y-m-d");
					            $date_to = strtotime($date_to);

					            for ($i = $date_from; $i <= $date_to; $i += 86400) 
            					{ 
					                if ( isset($view_statistics[date("Y-m-d", $i)]) )
					                {
					                    $views += $view_statistics[date("Y-m-d", $i)];
					                }
            					}

		            			$return = $views;
		            			break;
		            		}
		            		default:
		            		{
		            			$return = $property->{$field_name};			            	
				            }
				        }

				        $return = apply_filters( 'propertyhive_rest_api_property_field_callback', $return, $field_name, $property );
				        return $return;
		            },
		            'update_callback' => function ( $value, $object, $field_name ) 
		            {
		            	if ( taxonomy_exists($field_name) )
		            	{
		            		wp_set_post_terms( $object->ID, $value, $field_name );
		            	}
		            	else
		            	{
		            		switch ( $field_name )
		            		{
		            			case "price":
		            			{
		            				$property = new PH_Property($object->ID);

		            				$price = '';
		            				if ( $value != '' )
		            				{
		            					$price = preg_replace("/[^0-9.]/", '', $value);
		            				}

		            				if ( isset($property->_department) && $property->_department == 'residential-lettings' )
		            				{
		            					update_post_meta( $object->ID, '_rent', $price );
		            				}
		            				else
		            				{
		            					update_post_meta( $object->ID, '_price', $price );
		            				}

		            				if ( $price != '' )
		            				{
			            				// Store price in common currency (GBP) used for ordering
							            $ph_countries = new PH_Countries();
							            $ph_countries->update_property_price_actual( $object->ID );
							        }

		            				break;
		            			}
		            			case "features":
		            			{
		            				if ( is_array($value) && !empty($value) )
		            				{
		            					update_post_meta( $object->ID, '_features', count($value) );

		            					$i = 0;
		            					foreach ( $value as $feature )
		            					{
		            						update_post_meta( $object->ID, '_feature_' . $i, $feature );
		            						
		            						++$i;
		            					}
		            				}
		            				break;
		            			}
		            			case "images":
		            			case "floorplans":
		            			case "brochures":
		            			case "epcs":
		            			{
		            				if ( !function_exists('media_handle_upload') ) {
										require_once(ABSPATH . "wp-admin" . '/includes/image.php');
										require_once(ABSPATH . "wp-admin" . '/includes/file.php');
										require_once(ABSPATH . "wp-admin" . '/includes/media.php');
									}

		            				$hive_field_name = trim($field_name, 's');
		            				if ( $hive_field_name == 'image' ) { $hive_field_name = 'photo'; }

		            				if ( is_array($value) && !empty($value) )
		            				{
		            					if ( get_option('propertyhive_' . $field_name . '_stored_as', '') == 'urls' )
										{
											$media_items = array();
											foreach ( $value as $media_item )
											{
												if ( 
													isset($media_item['url']) &&
													(
														substr( strtolower($media_item['url']), 0, 2 ) == '//' || 
														substr( strtolower($media_item['url']), 0, 4 ) == 'http'
													)
												)
												{
													$media_items[] = array(
														'url' => $media_item['url'],
													);
												}
											}
											update_post_meta( $object->ID, '_' . $hive_field_name . '_urls', $media_items );
										}
										else
										{
											$media_ids = array();
											$previous_media_ids = get_post_meta( $object->ID, '_' . $hive_field_name . 's', TRUE );
											foreach ( $value as $media_item )
											{
												if ( 
													isset($media_item['url']) &&
													(
														substr( strtolower($media_item['url']), 0, 2 ) == '//' || 
														substr( strtolower($media_item['url']), 0, 4 ) == 'http'
													)
												)
												{
													// This is a URL
													$url = $media_item['url'];
													$description = '';

													$filename = basename( $url );

													// Check, based on the URL, whether we have previously imported this media
													$imported_previously = false;
													$imported_previously_id = '';
													if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
													{
														foreach ( $previous_media_ids as $previous_media_id )
														{
															if ( 
																get_post_meta( $previous_media_id, '_imported_url', TRUE ) == $url
															)
															{
																$imported_previously = true;
																$imported_previously_id = $previous_media_id;
																break;
															}
														}
													}
													
													if ($imported_previously)
													{
														$media_ids[] = $imported_previously_id;
													}
													else
													{
													    $tmp = download_url( $url );
													    $file_array = array(
													        'name' => basename( $url ),
													        'tmp_name' => $tmp
													    );

													    // Check for download errors
													    if ( is_wp_error( $tmp ) ) 
													    {
													        // ERROR: $tmp->get_error_message();
													    }
													    else
													    {
														    $id = media_handle_sideload( $file_array, $object->ID, $description, array('post_title' => $filename) );

														    // Check for handle sideload errors.
														    if ( is_wp_error( $id ) ) 
														    {
														        @unlink( $file_array['tmp_name'] );
														        
														        // ERROR: $id->get_error_message();
														    }
														    else
														    {
														    	$media_ids[] = $id;

														    	update_post_meta( $id, '_imported_url', $url);
														    }
														}
													}
												}
											}
											update_post_meta( $object->ID, '_' . $hive_field_name . 's', $media_ids );

											// Loop through $previous_media_ids, check each one exists in $media_ids, and if it doesn't then delete
											if ( is_array($previous_media_ids) && !empty($previous_media_ids) )
											{
												foreach ( $previous_media_ids as $previous_media_id )
												{
													if ( !in_array($previous_media_id, $media_ids) )
													{
														if ( wp_delete_attachment( $previous_media_id, TRUE ) !== FALSE )
														{

														}
													}
												}
											}
										}
		            				}

		            				break;
		            			}
		            			case "virtual_tours":
		            			{
		            				if ( is_array($value) && !empty($value) )
		            				{
		            					update_post_meta( $object->ID, '_virtual_tours', count($value) );

		            					$i = 0;
		            					foreach ( $value as $virtual_tour )
		            					{
		            						update_post_meta( $object->ID, '_virtual_tour_' . $i, ( isset($virtual_tour['url']) ? $virtual_tour['url'] : '' ) );
		            						update_post_meta( $object->ID, '_virtual_tour_label_' . $i, ( isset($virtual_tour['label']) ? $virtual_tour['label'] : '' ) );

		            						++$i;
		            					}
		            				}
		            				break;
		            			}
		            			default:
		            			{
		            				update_post_meta( $object->ID, '_' . $field_name, $value );
		            			}
		            		}
						}

						do_action( 'propertyhive_rest_api_property_field_update_callback', $value, $object, $field_name );
					},
		            'schema' => null,
		        )
		    );
		}
	}

	public function rest_prepare_property($response, $post, $request) 
	{
		// Hide/show fields dynamically per item before it's returned

	    $data = $response->get_data();

	    // Get the department
	    $department = $data['department'] ?? get_post_meta($post->ID, '_department', true);
	    if ( ph_get_custom_department_based_on( $department ) !== false )
        {
        	$department = ph_get_custom_department_based_on( $department );
        }

	    // Define the "not applicable" fields per department
	    $remove_for = [
	        'residential-sales' => [
	        	// specify non-sales fields
	            'available_date', 'deposit', 'furnished', 'rent_frequency', 
	            'commercial_tenure', 'commercial_property_type', 'for_sale', 'to_rent', 'price_from', 'price_to', 'price_units', 'rent_from', 'rent_to', 'rent_units', 'floor_area_from', 'floor_area_to', 'floor_area_units', 'site_area_from', 'site_area_to', 'site_area_units'
	        ],
	        'residential-lettings' => [
	            // specify non-lettings fields
	            'sale_by', 'tenure',
	            'commercial_tenure', 'commercial_property_type', 'for_sale', 'to_rent', 'price_from', 'price_to', 'price_units', 'rent_from', 'rent_to', 'rent_units', 'floor_area_from', 'floor_area_to', 'floor_area_units', 'site_area_from', 'site_area_to', 'site_area_units',
	        ],
	        'commercial' => [
	        	// specify non-commercial fields
	        	'price', 'price_actual', 'bedrooms', 'bathrooms', 'reception_rooms', 'outside_space', 'parking',
	            'available_date', 'deposit', 'furnished', 'rent_frequency',
	        ],
	    ];

	    if ( !empty($remove_for[$department]) ) 
	    {
	        foreach ( $remove_for[$department] as $k ) 
	        {
	            unset($data[$k]);
	        }
	    }

	    $response->set_data($data);

	    return $response;
	}

	public function register_rest_api_office_fields()
	{
		$field_array = array(
			'primary',
			'office_address_1',
			'office_address_2',
			'office_address_3',
			'office_address_4',
			'office_address_postcode',
			'office_latitude',
			'office_longitude',
		);

		$departments = ph_get_departments();

        foreach ( $departments as $key => $value )
        {
        	$field_array[] = 'office_telephone_number_' . str_replace("residential-", "", $key);
        	$field_array[] = 'office_email_address_' . str_replace("residential-", "", $key);
        }

		$field_array = apply_filters( 'propertyhive_rest_api_office_fields', $field_array );

		foreach ( $field_array as $field )
		{
			register_rest_field( 'office',
		        $field,
		        array(
		            'get_callback'  => function( $object, $field_name, $request )
		            {
		            	$return = '';

		            	switch ($field_name)
		            	{
		            		default:
		            		{
		            			$return = get_post_meta( $object[ 'id' ], '_' . $field_name, TRUE );
				            }
				        }

				        $return = apply_filters( 'propertyhive_rest_api_office_field_callback', $return, $field_name, $object[ 'id' ] );
				        return $return;
		            },
		            'update_callback' => null,
		            'schema' => null,
		        )
		    );
		}
	}

}

