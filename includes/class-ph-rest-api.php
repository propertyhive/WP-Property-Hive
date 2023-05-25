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
		add_filter( 'rest_property_query', array( $this, 'modify_rest_property_query' ), 10, 2 );
		add_action( 'rest_api_init', array( $this, 'register_rest_api_property_fields' ), 99 );
		add_action( 'rest_api_init', array( $this, 'register_rest_api_office_fields' ), 99 );
		add_filter( 'rest_property_collection_params', array( $this, 'modify_rest_order_by' ), 10, 1 );
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
			'price_formatted',
			'currency',
			'price_qualifier',
			'sale_by',
			'tenure',
			'deposit',
			'furnished',
			'available_date',
			'bedrooms',
			'bathrooms',
			'reception_rooms',
			'property_type',
			'parking',
			'outside_space',
			'on_market',
			'featured',
			'location',
			'availability',
			'marketing_flags',
			'features',
			'description',
			'office',
			'images',
			'floorplans',
			'brochures',
			'epcs',
			'virtual_tours',
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
		            				if ( $property->_department == 'residential-lettings' ) { $return = $property->_rent; }else{ $return = $property->_price; } 
		            			}
		            			else
		            			{
		            				$return = '';
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
							update_post_meta( $object->ID, '_' . $field_name, $value );
						}

						do_action( 'propertyhive_rest_api_property_field_update_callback', $value, $object, $field_name );
					},
		            'schema' => null,
		        )
		    );
		}
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
			'latitude',
			'longitude',
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

