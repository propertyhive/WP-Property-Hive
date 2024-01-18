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
		add_action( "rest_after_insert_property", array( $this, 'ensure_key_fields_set' ), 10, 3 );
	}

	public function ensure_key_fields_set( $post, $request, $creating )
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
			'price_formatted',
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

