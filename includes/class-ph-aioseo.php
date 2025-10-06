<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * AIOSEO Compatibility
 *
 * @class 		PH_AIOSEO
 * @version		1.0.0
 * @package		PropertyHive/Classes/
 * @category	Class
 * @author 		PropertyHive
 */
class PH_AIOSEO {

	/** @var PH_AIOSEO The single instance of the class */
	protected static $_instance = null;

	/**
	 * Hook in methods
	 */
	public static function init() 
	{
		add_filter( 'aioseo_post_metabox_priority', array( __CLASS__, 'meta_box_to_bottom') );
		add_filter( 'aioseo_sitemap_exclude_posts', array( __CLASS__, 'sitemap_exclude_off_market'), 10, 2 );

		add_filter( 'aioseo_schema_output', [ __CLASS__, 'add_real_estate_listing_schema' ] );
	}

	public static function meta_box_to_bottom( $priority ) 
	{
	   return 'low';
	}
	
	public static function sitemap_exclude_off_market( $ids, $type )
	{
		$ids = is_array( $ids ) ? $ids : [];

		$off_market = get_posts(array(
            'fields'            => 'ids',
            'posts_per_page'    => -1,
            'post_type'         => 'property',
            'meta_query'        => array(
                array(
                    'key'   => '_on_market',
                    'value' => '',
                )
            )
        ));

		if ( $off_market ) {
			$ids = array_merge( $ids, $off_market );
		}
		return array_values( array_unique( array_map( 'intval', $ids ) ) );
	}

	public static function add_real_estate_listing_schema( $graphs )
	{
		if ( ! is_singular( 'property' ) ) {
			return $graphs;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return $graphs;
		}

		$property = new PH_Property($post_id);

		// Common fields (adjust to your meta keys as needed).
		$title       = get_the_title( $post_id );
		$url         = get_permalink( $post_id );
		$description = wp_strip_all_tags( get_the_excerpt( $post_id ) ?: '', true );
		$datePosted  = date("Y-m-d\TH:i:s", strtotime($property->_on_market_change_date)) . "+00:00";

		$department  = $property->_department;

		$street        = $property->_address_street;
		$locality      = $property->_address_two;
		$region        = $property->_address_three;
		$postcode      = $property->_address_postcode;
		$country       = $property->_address_country;
		$lat           = $property->_latitude;
		$lng           = $property->_longitude;

		// Address bits (adjust to your meta keys).
		$address = array_filter( [
			'@type'           => 'PostalAddress',
			'streetAddress'   => $street,
			'addressLocality' => $locality,
			'addressRegion'   => $region,
			'postalCode'      => $postcode,
			'addressCountry'  => $country,
		] );

		$bedrooms      = (int)$property->_bedrooms;
		$bathrooms     = (int)$property->_bathrooms;

		$images = array();
        if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        {
            $photo_urls = $property->_photo_urls;
            if ( !is_array($photo_urls) ) { $photo_urls = array(); }

            if ( !empty($num_images) )
            {
                $photo_urls = array_slice($photo_urls, 0, $num_images);
            }

            foreach ( $photo_urls as $photo )
            {
                $images[] = isset($photo['url']) ? $photo['url'] : '';
            }
        }
        else
        {
            $gallery_attachments = $property->get_gallery_attachment_ids();

            if ( !empty($gallery_attachments) ) 
            {
                if ( !empty($num_images) )
                {
                    $gallery_attachments = array_slice($gallery_attachments, 0, $num_images);
                }
            
                foreach ($gallery_attachments as $gallery_attachment)
                {
                    $images[] = wp_get_attachment_url( $gallery_attachment );
                }
            }
        }
		$images = array_values( array_unique( array_filter( $images ) ) );

		$types = array( 'Residence' );
		if ( $department != 'commercial' && ph_get_custom_department_based_on($department) != 'commercial' )
		{
			$types[] = 'SingleFamilyResidence';
		}
		$item_offered = array_filter( [
			'@type'               => $types,
			'address'             => ! empty( $address ) ? $address : null,
		] );
		if ( $lat && $lng ) {
			$item_offered['geo'] = [
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float)$lat,
				'longitude' => (float)$lng,
			];
		}
		if ( $bedrooms && $department != 'commercial' && ph_get_custom_department_based_on($department) != 'commercial' )  { $item_offered['numberOfBedrooms']      = $bedrooms; }
		if ( $bathrooms && $department != 'commercial' && ph_get_custom_department_based_on($department) != 'commercial' ) { $item_offered['numberOfBathroomsTotal'] = $bathrooms; }

		// Attach an Offer (price, currency, availability) that points at the Residence
		if ( $department == 'residential-sales' || ph_get_custom_department_based_on($department) == 'residential-sales' ) 
		{
			$offer = [
				'@type'          => 'Offer',
				'price'          => $property->_poa != 'yes' ? (float)$property->_price : '',
				'priceCurrency'  => $property->_currency,
				// Use a schema.org ItemAvailability URL if you have one; default to InStock
				//'availability'   => $availability ?: 'https://schema.org/InStock',
				'businessFunction' => 'https://purl.org/goodrelations/v1#Sell',
				'itemOffered'    => $item_offered,
				'url' 			 => $url
			];
		}
		elseif ( $department == 'residential-lettings' || ph_get_custom_department_based_on($department) == 'residential-lettings' ) 
		{
			$offer = [
				'@type'          => 'Offer',
				'price'          => $property->_poa != 'yes' ? (float)$property->_rent : '',
				'priceCurrency'  => $property->_currency,
				'priceSpecification' => [
					'@type' => 'UnitPriceSpecification',
					'price' => $property->_poa != 'yes' ? (float)$property->_rent : '',
					'priceCurrency' => $property->_currency,
					'unitText' => $property->_rent_frequency,
				],
				'businessFunction' => 'https://purl.org/goodrelations/v1#LeaseOut',
				// Use a schema.org ItemAvailability URL if you have one; default to InStock
				//'availability'   => $availability ?: 'https://schema.org/InStock',
				'itemOffered'    => $item_offered,
				'url' 			 => $url
			];
		}

		$realEstateListing = array_filter( [
			'@type'            => 'RealEstateListing',
			'@id'              => trailingslashit( $url ) . '#realEstateListing',
			'url'              => $url,
			'name'             => $title,
			'description'      => $description,
			'datePosted'       => $datePosted,
			'image'            => ! empty( $images ) ? $images : null,
			'mainEntityOfPage' => $url,
			'offers'           => $offer,
		] );

		// Append (don’t replace) our graph.
		if ( ! is_array( $graphs ) ) {
			$graphs = [];
		}
		$graphs[] = $realEstateListing;

		return $graphs;
	}
}

PH_AIOSEO::init();