<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Yoast SEO Compatibility
 *
 * @class 		PH_Yoast_SEO
 * @version		1.0.0
 * @package		PropertyHive/Classes/
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Yoast_SEO {

	/** @var PH_Yoast_SEO The single instance of the class */
	protected static $_instance = null;

	/**
	 * Hook in methods
	 */
	public static function init() {
		add_filter( 'wpseo_sitemap_exclude_taxonomy', array( __CLASS__, 'sitemap_exclude_taxonomy' ), 10, 2 );
		add_filter( 'wpseo_metabox_prio', array( __CLASS__, 'yoast_meta_box_to_bottom') );
		add_filter( 'manage_edit-property_columns', array( __CLASS__, 'yoast_remove_columns') );
		add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', array( __CLASS__, 'sitemap_exclude_off_market') );
		
		add_filter( 'wpseo_schema_webpage_type', array( __CLASS__, 'yoast_schema_webpage_type') );
		add_filter( 'wpseo_schema_webpage', array( __CLASS__, 'yoast_schema_webpage') );
		add_filter( 'wpseo_schema_graph', array( __CLASS__, 'yoast_schema_graph'), 20 );
		
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	public static function enqueue_scripts() 
	{
		$screen = get_current_screen();

		if ( in_array( $screen->id, array( 'property' ) ) )
        {
        	wp_enqueue_script( 'propertyhive-yoast', PH()->plugin_url() . '/assets/js/admin/yoast.js', array(), PH_VERSION );
        }
    }

	public static function yoast_schema_webpage_type( $type )
	{
		global $post;

		if ( isset($post->post_type) && $post->post_type == 'property' )
		{
			if ( !is_array($type) )
			{
				$type = array($type);
			}
			$type[] = 'RealEstateListing';
			$type = array_filter( array_values( array_unique( $type ) ) );
		}

		return $type;
	}

	public static function yoast_schema_webpage( $data ) 
	{
		// Enrich the WebPage piece with RealEstateListing props (datePosted, offers, mainEntity)

		if ( ! is_singular( 'property' ) ) {
			return $data;
		}

		$post_id = get_the_ID();

		$property = new PH_Property($post_id);

		$url     = get_permalink( $post_id );

		// IDs so everything links together nicely in the graph
		$residence_id = $url . '#/residence/' . $post_id;
		$offer_id     = $url . '#/offer/' . $post_id;

		// Core listing bits
		$department   = $property->_department;

		$data['datePosted'] = date("Y-m-d\TH:i:s", strtotime(get_post_meta( $post_id, '_on_market_change_date', true ))) . "+00:00";

		// Attach an Offer (price, currency, availability) that points at the Residence
		if ( $department == 'residential-sales' || ph_get_custom_department_based_on($department) == 'residential-sales' ) 
		{
			$data['offers'] = [
				'@type'          => 'Offer',
				'@id'            => $offer_id,
				'price'          => $property->_poa != 'yes' ? (float)$property->_price : '',
				'priceCurrency'  => $property->_currency,
				// Use a schema.org ItemAvailability URL if you have one; default to InStock
				//'availability'   => $availability ?: 'https://schema.org/InStock',
				'businessFunction' => 'https://purl.org/goodrelations/v1#Sell',
				'itemOffered'    => [ '@id' => $residence_id ],
				'url' 			 => $url
			];
		}
		elseif ( $department == 'residential-lettings' || ph_get_custom_department_based_on($department) == 'residential-lettings' ) 
		{
			$data['offers'] = [
				'@type'          => 'Offer',
				'@id'            => $offer_id,
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
				'itemOffered'    => [ '@id' => $residence_id ],
				'url' 			 => $url
			];
		}

		// Point the WebPage at the main entity (the actual property)
		$data['mainEntity'] = [ '@id' => $residence_id ];

		return $data;
	}

	public static function yoast_schema_graph( $graph ) 
	{
		// Append a Residence/House node with all the property specifics

		if ( ! is_singular( 'property' ) ) {
			return $graph;
		}

		$post_id = get_the_ID();

		$property = new PH_Property($post_id);

		$url     = get_permalink( $post_id );

		$residence_id = $url . '#/residence/' . $post_id;

		// Collect property meta
		$bedrooms      = (int)$property->_bedrooms;
		$bathrooms     = (int)$property->_bathrooms;
		$street        = $property->_address_street;
		$locality      = $property->_address_two;
		$region        = $property->_address_three;
		$postcode      = $property->_address_postcode;
		$country       = $property->_address_country;
		$lat           = $property->_latitude;
		$lng           = $property->_longitude;

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

		$residence = [
			'@type' => [ 'Residence' ], // Swap 'House' for 'Apartment', 'SingleFamilyResidence', etc.
			'@id'   => $residence_id,
			'url'   => $url,
			'name'  => get_the_title( $post_id ),
		];

		// Address
		if ( $street || $locality || $region || $postcode || $country ) {
			$residence['address'] = [
				'@type'           => 'PostalAddress',
				'streetAddress'   => $street,
				'addressLocality' => $locality,
				'addressRegion'   => $region,
				'postalCode'      => $postcode,
				'addressCountry'  => $country,
			];
		}

		// Geo
		if ( $lat && $lng ) {
			$residence['geo'] = [
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float)$lat,
				'longitude' => (float)$lng,
			];
		}

		// Rooms & size
		if ( $bedrooms )  { $residence['numberOfBedrooms']      = $bedrooms; }
		if ( $bathrooms ) { $residence['numberOfBathroomsTotal'] = $bathrooms; }

		// Photos / primary image
		if ( $images ) {
			$residence['image'] = $images;
		}

		$graph[] = $residence;
		return $graph;
	}

	public static function sitemap_exclude_taxonomy( $value, $taxonomy ) {
		$taxonomy_objects = get_object_taxonomies( 'property', 'objects' );
   		$taxonomies = array();

   		if ( !empty($taxonomy_objects) )
   		{
   			foreach ( $taxonomy_objects as $taxonomy_name => $object )
   			{
   				$taxonomies[] = $taxonomy_name;
   			}
   		}
		if ( 
			in_array($taxonomy, $taxonomies)
		) 
		{
			return true;
		}
	}

	public static function yoast_meta_box_to_bottom() {
		return 'low';
	}

	public static function yoast_remove_columns( $columns ) {
		unset( $columns['wpseo-score'] );
		unset( $columns['wpseo-score-readability'] );
		unset( $columns['wpseo-title'] );
		unset( $columns['wpseo-metadesc'] );
		unset( $columns['wpseo-focuskw'] );
		return $columns;
	}

	public static function sitemap_exclude_off_market() {
        return get_posts(array(
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
	}

}

PH_Yoast_SEO::init();