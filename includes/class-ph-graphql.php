<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GraphQL Compatibility
 *
 * @class 		PH_GraphQL
 * @version		1.0.0
 * @package		PropertyHive/Classes/
 * @category	Class
 * @author 		PropertyHive
 */
class PH_GraphQL {

	/** @var PH_Rank_Math The single instance of the class */
	protected static $_instance = null;

	/**
	 * Hook in methods
	 */
	public static function init() 
	{
		add_filter( 'propertyhive_register_post_type_property', array( __CLASS__, 'property_post_type_add_graphql_support' ) );
		add_action( 'graphql_register_types', array( __CLASS__, 'property_post_type_add_graphql_types' ) );
	}

	public static function property_post_type_add_graphql_support( $params ) 
	{
		$params['show_in_graphql'] = true;
		$params['graphql_single_name'] = 'Property';
		$params['graphql_plural_name'] = 'Properties';

		return $params;
	}

	public static function property_post_type_add_graphql_types( $type_registry ) 
	{
		register_graphql_field( 'Property', 'department', [
		    'type' => 'String',
		    'resolve' => function( $property ) 
		    {
		    	return get_post_meta( $property->databaseId, '_department', true );
		    }
		] );

		register_graphql_field( 'Property', 'price_formatted', [
		    'type' => 'String',
		    'resolve' => function( $property ) 
		    {
		    	$property = new PH_Property($property->databaseId);
		    	return $property->get_formatted_price();
		    }
		] );

		register_graphql_field( 'Property', 'bedrooms', [
		    'type' => 'Integer',
		    'resolve' => function( $property ) 
		    {
		    	$bedrooms = get_post_meta( $property->databaseId, '_bedrooms', true );
      			return ! empty( $bedrooms ) ? $bedrooms : null;
		    }
		] );
	}

}

PH_GraphQL::init();