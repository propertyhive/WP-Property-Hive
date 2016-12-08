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
	}

	public function modify_rest_property_query($args, $request)
	{
		if ( !isset( $args['meta_query'] ) )
		{
			$args['meta_query'] = array();
		}

		$args['meta_query'][] = array(
			'key' => '_on_market',
			'value' => 'yes'
		);
		
		return $args;
	}

	public function register_rest_api_property_fields()
	{
		$field_array = array(
			'department',
			'latitude',
			'longitude',
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
			'featured',
			'availability',
			'marketing_flags',
			'features',
			'description',
			'office',
		);

		foreach ( $field_array as $field )
		{
			register_rest_field( 'property',
		        $field,
		        array(
		            'get_callback'  => function( $object, $field_name, $request )
		            {
		            	$property = new PH_Property($object[ 'id' ]);

		            	switch ($field_name)
		            	{
		            		case "price":
		            		{ 
		            			if ( $property->poa != 'yes' )
		            			{
		            				if ( $property->department == 'residential-lettings' ) { return $property->rent; }else{ return $property->price; } 
		            			}

		            			return '';
		            		}
		            		case "price_formatted": { return $property->get_formatted_price(); break; }
		            		case "features": { return $property->get_features(); break; }
		            		case "description": { return $property->get_formatted_description(); break; }
		            		case "office": 
		            		{ 
		            			return array(
		            				'name' => $property->office_name,
		            				'address' => $property->office_address,
		            				'telephone_number' => $property->office_telephone_number,
		            				'email_address' => $property->office_email_address,
		            			); 
		            			break; 
		            		}
		            		default:
		            		{
				            	return $property->{$field_name};
				            }
				        }
		            },
		            'update_callback' => null,
		            'schema' => null,
		        )
		    );
		}
	}

}

