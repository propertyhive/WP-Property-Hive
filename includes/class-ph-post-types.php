<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Post types
 *
 * Registers post types
 *
 * @class 		PH_Post_types
 * @version		1.0.0
 * @package		PropertyHive/Classes
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Post_types {

	/**
	 * Constructor
	 */
	public function __construct() {
	    add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );

        add_action( 'wp_trash_post', array( __CLASS__, 'trash_property_children' ), 5 );

        add_action( 'before_delete_post', array( __CLASS__, 'delete_property_media' ), 5 );
        add_action( 'before_delete_post', array( __CLASS__, 'delete_property_children' ), 5 );

        add_action( 'save_post', array( $this, 'create_concatenated_indexable_meta' ), 10, 2 );
	}

	/**
	 * Register PropertyHive taxonomies.
	 */
	public static function register_taxonomies() {
	        
	    if ( taxonomy_exists( 'property_type' ) )
			return;

		do_action( 'propertyhive_register_taxonomy' );

		//$permalinks = get_option( 'propertyhive_permalinks' );

        register_taxonomy( 'availability',
            'property',
            array(
                'label'                 => __( 'Availabilities', 'propertyhive' ),
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );

		register_taxonomy( 'property_type',
	        array( 'property', 'appraisal' ),
	        array(
                'label'                 => __( 'Property Types', 'propertyhive' ),
	            'hierarchical' 			=> true,
	            'show_ui' 				=> false,
	            'show_in_nav_menus' 	=> false,
	            'query_var' 			=> is_admin(),
	            'rewrite'				=> false,
	            'public'                => true
	        )
	    );

        register_taxonomy( 'commercial_property_type',
            array( 'property', 'appraisal' ),
            array(
                'label'                 => __( 'Commercial Property Types', 'propertyhive' ),
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );
        
        register_taxonomy( 'location',
            'property',
            array(
                'label'                 => __( 'Locations', 'propertyhive' ),
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );
        
        register_taxonomy( 'parking',
            array( 'property', 'appraisal' ),
            array(
                'label'                 => __( 'Parking', 'propertyhive' ),
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );
        
        register_taxonomy( 'outside_space',
            array( 'property', 'appraisal' ),
            array(
                'label'                 => __( 'Outside Spaces', 'propertyhive' ),
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );
        
        register_taxonomy( 'price_qualifier',
            'property',
            array(
                'label'                 => __( 'Price Qualifiers', 'propertyhive' ),
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );
        
        register_taxonomy( 'tenure',
            'property',
            array(
                'label'                 => __( 'Tenures', 'propertyhive' ),
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );

        register_taxonomy( 'commercial_tenure',
            'property',
            array(
                'label'                 => __( 'Commercial Tenures', 'propertyhive' ),
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );
        
        register_taxonomy( 'sale_by',
            'property',
            array(
                'label'                 => __( 'Sale By', 'propertyhive' ),
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );
        
        register_taxonomy( 'furnished',
            array( 'property', 'appraisal' ),
            array(
                'label'                 => __( 'Furnished', 'propertyhive' ),
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );

        register_taxonomy( 'marketing_flag',
            'property',
            array(
                'label'                 => __( 'Marketing Flags', 'propertyhive' ),
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );

        register_taxonomy( 'property_feature',
            'property',
            array(
                'label'                 => __( 'Property Features', 'propertyhive' ),
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );
			
		do_action( 'do_action_after_register_taxonomies' );
	}

	/**
	 * Register core post types
	 */
	public static function register_post_types() {
	    
		if ( post_type_exists('property') )
			return;

		do_action( 'propertyhive_register_post_type' );

		//$permalinks        = get_option( 'property_permalinks' );
		//$product_permalink = empty( $permalinks['property_base'] ) ? _x( 'property', 'slug', 'propertyhive' ) : $permalinks['property_base'];

		register_post_type( "property",
			apply_filters( 'propertyhive_register_post_type_property',
				array(
					'labels' => array(
							'name' 					=> __( 'Properties', 'propertyhive' ),
							'singular_name' 		=> __( 'Property', 'propertyhive' ),
							'menu_name'				=> _x( 'Properties', 'Admin menu name', 'propertyhive' ),
							'add_new' 				=> __( 'Add Property', 'propertyhive' ),
							'add_new_item' 			=> __( 'Add New Property', 'propertyhive' ),
							'edit' 					=> __( 'Edit', 'propertyhive' ),
							'edit_item' 			=> __( 'Edit Property', 'propertyhive' ),
							'new_item' 				=> __( 'New Property', 'propertyhive' ),
							'view' 					=> __( 'View Property', 'propertyhive' ),
							'view_item' 			=> __( 'View Property', 'propertyhive' ),
							'search_items' 			=> __( 'Search Properties', 'propertyhive' ),
							'not_found' 			=> __( 'No properties found', 'propertyhive' ),
							'not_found_in_trash' 	=> __( 'No properties found in trash', 'propertyhive' ),
							'parent' 				=> __( 'Parent Property', 'propertyhive' )
						),
					'description' 			=> __( 'This is where you can add new properties to your site.', 'propertyhive' ),
					'public' 				=> true,
					'show_ui' 				=> true,
					'capability_type' 		=> 'post',
					'map_meta_cap'			=> true,
					'publicly_queryable' 	=> true,
					'exclude_from_search' 	=> false,
					'hierarchical' 			=> false, // Hierarchical causes memory issues - WP loads all records!
					//'rewrite' 				=> $product_permalink ? array( 'slug' => untrailingslashit( $product_permalink ), 'with_front' => false, 'feeds' => true ) : false,
					'query_var' 			=> true,
					'supports' 				=> array( 'title', 'excerpt' ),
					'has_archive' 			=> ( $search_results_page_id = ph_get_page_id( 'search_results' ) ) && get_page( $search_results_page_id ) ? get_page_uri( $search_results_page_id ) : 'search_results',
					'show_in_nav_menus' 	=> false,
					'show_in_menu'          => false,
					'show_in_admin_bar'     => true,
                    'show_in_rest'          => true,
				)
			)
		);
        
        register_post_type( "contact",
            apply_filters( 'propertyhive_register_post_type_contact',
                array(
                    'labels' => array(
                            'name'                  => __( 'Contacts', 'propertyhive' ),
                            'singular_name'         => __( 'Contact', 'propertyhive' ),
                            'menu_name'             => _x( 'Contacts', 'Admin menu name', 'propertyhive' ),
                            'add_new'               => __( 'Add Contact', 'propertyhive' ),
                            'add_new_item'          => __( 'Add New Contact', 'propertyhive' ),
                            'edit'                  => __( 'Edit', 'propertyhive' ),
                            'edit_item'             => __( 'Edit Contact', 'propertyhive' ),
                            'new_item'              => __( 'New Contact', 'propertyhive' ),
                            'view'                  => __( 'View Contact', 'propertyhive' ),
                            'view_item'             => __( 'View Contact', 'propertyhive' ),
                            'search_items'          => __( 'Search Contacts', 'propertyhive' ),
                            'not_found'             => __( 'No contacts found', 'propertyhive' ),
                            'not_found_in_trash'    => __( 'No contacts found in trash', 'propertyhive' ),
                            'parent'                => __( 'Parent Contact', 'propertyhive' )
                        ),
                    'description'           => __( 'This is where you can add new contacts to your site.', 'propertyhive' ),
                    'public'                => false,
                    'show_ui'               => true,
                    'capability_type'       => 'post',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => false,
                    'exclude_from_search'   => true,
                    'hierarchical'          => false, // Hierarchical causes memory issues - WP loads all records!
                    'query_var'             => true,
                    'supports'              => array( 'title' ),
                    'show_in_nav_menus'     => false,
                    'show_in_menu'          => false
                )
            )
        );
        
        register_post_type( "office",
            apply_filters( 'propertyhive_register_post_type_office',
                array(
                    'labels' => array(
                            'name'                  => __( 'Offices', 'propertyhive' ),
                            'singular_name'         => __( 'Office', 'propertyhive' ),
                            'menu_name'             => _x( 'Offices', 'Admin menu name', 'propertyhive' ),
                            'add_new'               => __( 'Add Office', 'propertyhive' ),
                            'add_new_item'          => __( 'Add New Office', 'propertyhive' ),
                            'edit'                  => __( 'Edit', 'propertyhive' ),
                            'edit_item'             => __( 'Edit Office', 'propertyhive' ),
                            'new_item'              => __( 'New Office', 'propertyhive' ),
                            'view'                  => __( 'View Office', 'propertyhive' ),
                            'view_item'             => __( 'View Office', 'propertyhive' ),
                            'search_items'          => __( 'Search Offices', 'propertyhive' ),
                            'not_found'             => __( 'No offices found', 'propertyhive' ),
                            'not_found_in_trash'    => __( 'No offices found in trash', 'propertyhive' ),
                            'parent'                => __( 'Parent Office', 'propertyhive' )
                        ),
                    'public'                => true,
                    'show_ui'               => false,
                    'capability_type'       => 'post',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => false,
                    'exclude_from_search'   => true,
                    'hierarchical'          => false, // Hierarchical causes memory issues - WP loads all records!
                    'query_var'             => true,
                    'supports'              => array( 'title' ),
                    'show_in_nav_menus'     => false,
                    'show_in_menu'          => false
                )
            )
        );
        
        register_post_type( "enquiry",
            apply_filters( 'propertyhive_register_post_type_enquiry',
                array(
                    'labels' => array(
                            'name'                  => __( 'Enquiries', 'propertyhive' ),
                            'singular_name'         => __( 'Enquiry', 'propertyhive' ),
                            'menu_name'             => _x( 'Enquiries', 'Admin menu name', 'propertyhive' ),
                            'add_new'               => __( 'Add Enquiry', 'propertyhive' ),
                            'add_new_item'          => __( 'Add New Enquiry', 'propertyhive' ),
                            'edit'                  => __( 'Edit', 'propertyhive' ),
                            'edit_item'             => __( 'Edit Enquiry', 'propertyhive' ),
                            'new_item'              => __( 'New Enquiry', 'propertyhive' ),
                            'view'                  => __( 'View Enquiry', 'propertyhive' ),
                            'view_item'             => __( 'View Enquiry', 'propertyhive' ),
                            'search_items'          => __( 'Search Enquiries', 'propertyhive' ),
                            'not_found'             => __( 'No enquiries found', 'propertyhive' ),
                            'not_found_in_trash'    => __( 'No enquiries found in trash', 'propertyhive' ),
                            'parent'                => __( 'Parent Enquiry', 'propertyhive' )
                        ),
                    'public'                => false,
                    'show_ui'               => true,
                    'capability_type'       => 'post',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => false,
                    'exclude_from_search'   => true,
                    'hierarchical'          => false, // Hierarchical causes memory issues - WP loads all records!
                    'query_var'             => true,
                    'supports'              => array( 'title' ),
                    'show_in_nav_menus'     => false,
                    'show_in_menu'          => false
                )
            )
        );

        register_post_type( "appraisal",
            apply_filters( 'propertyhive_register_post_type_appraisal',
                array(
                    'labels' => array(
                            'name'                  => __( 'Appraisals', 'propertyhive' ),
                            'singular_name'         => __( 'Appraisal', 'propertyhive' ),
                            'menu_name'             => _x( 'Appraisals', 'Admin menu name', 'propertyhive' ),
                            'add_new'               => __( 'Add Appraisal', 'propertyhive' ),
                            'add_new_item'          => __( 'Add New Appraisal', 'propertyhive' ),
                            'edit'                  => __( 'Edit', 'propertyhive' ),
                            'edit_item'             => __( 'Edit Appraisal', 'propertyhive' ),
                            'new_item'              => __( 'New Appraisal', 'propertyhive' ),
                            'view'                  => __( 'View Appraisal', 'propertyhive' ),
                            'view_item'             => __( 'View Appraisal', 'propertyhive' ),
                            'search_items'          => __( 'Search Appraisals', 'propertyhive' ),
                            'not_found'             => __( 'No appraisals found', 'propertyhive' ),
                            'not_found_in_trash'    => __( 'No appraisals found in trash', 'propertyhive' ),
                            'parent'                => __( 'Parent Appraisal', 'propertyhive' )
                        ),
                    'public'                => false,
                    'show_ui'               => true,
                    'capability_type'       => 'post',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => false,
                    'exclude_from_search'   => true,
                    'hierarchical'          => false, // Hierarchical causes memory issues - WP loads all records!
                    'query_var'             => true,
                    'supports'              => false,
                    'show_in_nav_menus'     => false,
                    'show_in_menu'          => false
                )
            )
        );

        register_post_type( "viewing",
            apply_filters( 'propertyhive_register_post_type_viewing',
                array(
                    'labels' => array(
                            'name'                  => __( 'Viewings', 'propertyhive' ),
                            'singular_name'         => __( 'Viewing', 'propertyhive' ),
                            'menu_name'             => _x( 'Viewings', 'Admin menu name', 'propertyhive' ),
                            'add_new'               => __( 'Add Viewing', 'propertyhive' ),
                            'add_new_item'          => __( 'Add New Viewing', 'propertyhive' ),
                            'edit'                  => __( 'Edit', 'propertyhive' ),
                            'edit_item'             => __( 'Edit Viewing', 'propertyhive' ),
                            'new_item'              => __( 'New Viewing', 'propertyhive' ),
                            'view'                  => __( 'View Viewing', 'propertyhive' ),
                            'view_item'             => __( 'View Viewing', 'propertyhive' ),
                            'search_items'          => __( 'Search Viewings', 'propertyhive' ),
                            'not_found'             => __( 'No viewings found', 'propertyhive' ),
                            'not_found_in_trash'    => __( 'No viewings found in trash', 'propertyhive' ),
                            'parent'                => __( 'Parent Viewing', 'propertyhive' )
                        ),
                    'public'                => false,
                    'show_ui'               => true,
                    'capability_type'       => 'post',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => false,
                    'exclude_from_search'   => true,
                    'hierarchical'          => false, // Hierarchical causes memory issues - WP loads all records!
                    'query_var'             => true,
                    'supports'              => false,
                    'show_in_nav_menus'     => false,
                    'show_in_menu'          => false
                )
            )
        );

        register_post_type( "offer",
            apply_filters( 'propertyhive_register_post_type_offer',
                array(
                    'labels' => array(
                            'name'                  => __( 'Offers', 'propertyhive' ),
                            'singular_name'         => __( 'Offer', 'propertyhive' ),
                            'menu_name'             => _x( 'Offers', 'Admin menu name', 'propertyhive' ),
                            'add_new'               => __( 'Add Offer', 'propertyhive' ),
                            'add_new_item'          => __( 'Add New Offer', 'propertyhive' ),
                            'edit'                  => __( 'Edit', 'propertyhive' ),
                            'edit_item'             => __( 'Edit Offer', 'propertyhive' ),
                            'new_item'              => __( 'New Offer', 'propertyhive' ),
                            'view'                  => __( 'View Offer', 'propertyhive' ),
                            'view_item'             => __( 'View Offer', 'propertyhive' ),
                            'search_items'          => __( 'Search Offers', 'propertyhive' ),
                            'not_found'             => __( 'No offers found', 'propertyhive' ),
                            'not_found_in_trash'    => __( 'No offers found in trash', 'propertyhive' ),
                            'parent'                => __( 'Parent Offer', 'propertyhive' )
                        ),
                    'public'                => false,
                    'show_ui'               => true,
                    'capability_type'       => 'post',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => false,
                    'exclude_from_search'   => true,
                    'hierarchical'          => false, // Hierarchical causes memory issues - WP loads all records!
                    'query_var'             => true,
                    'supports'              => false,
                    'show_in_nav_menus'     => false,
                    'show_in_menu'          => false
                )
            )
        );

        register_post_type( "sale",
            apply_filters( 'propertyhive_register_post_type_sale',
                array(
                    'labels' => array(
                            'name'                  => __( 'Sales', 'propertyhive' ),
                            'singular_name'         => __( 'Sale', 'propertyhive' ),
                            'menu_name'             => _x( 'Sales', 'Admin menu name', 'propertyhive' ),
                            'add_new'               => __( 'Add Sale', 'propertyhive' ),
                            'add_new_item'          => __( 'Add New Sale', 'propertyhive' ),
                            'edit'                  => __( 'Edit', 'propertyhive' ),
                            'edit_item'             => __( 'Edit Sale', 'propertyhive' ),
                            'new_item'              => __( 'New Sale', 'propertyhive' ),
                            'view'                  => __( 'View Sale', 'propertyhive' ),
                            'view_item'             => __( 'View Sale', 'propertyhive' ),
                            'search_items'          => __( 'Search Sales', 'propertyhive' ),
                            'not_found'             => __( 'No sales found', 'propertyhive' ),
                            'not_found_in_trash'    => __( 'No sales found in trash', 'propertyhive' ),
                            'parent'                => __( 'Parent Sale', 'propertyhive' )
                        ),
                    'public'                => false,
                    'show_ui'               => true,
                    'capability_type'       => 'post',
                    'capabilities' => array(
                        'create_posts' => false
                    ),
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => false,
                    'exclude_from_search'   => true,
                    'hierarchical'          => false, // Hierarchical causes memory issues - WP loads all records!
                    'query_var'             => true,
                    'supports'              => false,
                    'show_in_nav_menus'     => false,
                    'show_in_menu'          => false
                )
            )
        );
        
        do_action( 'propertyhive_after_register_post_types' );
	}

    public static function trash_property_children( $post_id )
    {
        if ( get_post_type($post_id) == 'property' )
        {
            // get all children
            $args = array(
                'post_type' => 'property',
                'nopaging' => true,
                'post_parent' => (int)$post_id,
                'suppress_filters' => TRUE,
                'post_status' => array('publish', 'pending', 'private', 'draft', 'auto-draft', 'future'),
            );

            $properties_query = new WP_Query($args);

            if ( $properties_query->have_posts() )
            {
                while( $properties_query->have_posts() )
                {
                    $properties_query->the_post();

                    wp_trash_post( get_the_ID() );
                }
            }
            wp_reset_postdata();
        }
    }

    public static function delete_property_media( $post_id ) 
    {
        if ( get_post_type($post_id) == 'property' )
        {
            $property = new PH_Property( (int)$post_id );

            $media_ids = $property->get_gallery_attachment_ids();
            self::do_delete_media_attachments( $post_id, $media_ids );

            $media_ids = $property->get_floorplan_attachment_ids();
            self::do_delete_media_attachments( $post_id, $media_ids );

            $media_ids = $property->get_brochure_attachment_ids();
            self::do_delete_media_attachments( $post_id, $media_ids );

            $media_ids = $property->get_epc_attachment_ids();
            self::do_delete_media_attachments( $post_id, $media_ids );
        }
    }

    private static function do_delete_media_attachments( $post_id, $media_ids = array() )
    {
        if ( is_array($media_ids) && !empty($media_ids) )
        {
            foreach ( $media_ids as $media_id )
            {
                // Make sure this media isn't used anywhere else
                $args = array(
                    'post_type' => 'property',
                    'fields' => 'ids',
                    'suppress_filters' => TRUE,
                    'posts_per_page' => 1,
                    'post__not_in' => array( $post_id ),
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => '_photos',
                            'value' => ':' . $media_id . ';',
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => '_photos',
                            'value' => ':"' . $media_id . '";',
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => '_floorplans',
                            'value' => ':' . $media_id . ';',
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => '_floorplans',
                            'value' => ':"' . $media_id . '";',
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => '_epcs',
                            'value' => ':' . $media_id . ';',
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => '_epcs',
                            'value' => ':"' . $media_id . '";',
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => '_brochures',
                            'value' => ':' . $media_id . ';',
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => '_brochures',
                            'value' => ':"' . $media_id . '";',
                            'compare' => 'LIKE'
                        ),
                    )
                );

                $property_query = new WP_Query( $args );

                if ( !$property_query->have_posts() )
                {
                    // We're ok to delete
                    wp_delete_attachment( $media_id, true );
                }
            }
        }
    }

    public static function delete_property_children( $post_id )
    {
        if ( get_post_type($post_id) == 'property' )
        {
            // get all children
            $args = array(
                'post_type' => 'property',
                'nopaging' => true,
                'suppress_filters' => TRUE,
                'post_parent' => (int)$post_id,
                'post_status' => array('publish', 'pending', 'private', 'draft', 'auto-draft', 'future', 'trash'),
            );

            $properties_query = new WP_Query($args);

            if ( $properties_query->have_posts() )
            {
                while( $properties_query->have_posts() )
                {
                    $properties_query->the_post();

                    wp_delete_post( get_the_ID(), true );
                }
            }
            wp_reset_postdata();
        }
    }

    /**
	 * Check if we're saving, then trigger an action based on the post type
	 *
	 * @param  int $post_id
	 * @param  object $post
	 */
    public static function create_concatenated_indexable_meta( $post_id, $post )
    {
        // $post_id and $post are required
        if ( empty( $post_id ) || empty( $post ) ) {
            return;
        }

        // Dont' save meta boxes for revisions or autosaves
        if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
            return;
        }

        // Check the nonce
        if ( empty( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( $_POST['propertyhive_meta_nonce'], 'propertyhive_save_data' ) ) {
            return;
        }

        // Check the post being saved == the $post_id to prevent triggering this call for other save_post events
        if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
            return;
        }

        // Check user has permission to edit
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check the post type
        if ( $post->post_type !== 'property') {
            return;
        }

        // Set field of concatenated features
        $features_concat_array = array();
        if ( get_option('propertyhive_features_type') == 'checkbox' )
        {
            $term_list = wp_get_post_terms($post_id, 'property_feature', array("fields" => "names"));
            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
            {
                foreach ( $term_list as $term_name )
                {
                    $features_concat_array[] = trim($term_name);
                }
            }
        }
        else
        {
            $num_property_features = (int)get_post_meta($post_id, '_features', TRUE);

            for ( $i = 0; $i < $num_property_features; ++$i )
            {
                $features_concat_array[] = get_post_meta($post_id, '_feature_' . $i, TRUE);
            }
        }

        $features_concat = implode('|', array_filter($features_concat_array));
        if ( $features_concat == '' )
        {
            delete_post_meta($post_id, '_features_concatenated');
        }
        else
        {
            $existing_concat = get_post_meta($post_id, '_features_concatenated', TRUE);
            if( !$existing_concat || $existing_concat !== $features_concat )
            {
                update_post_meta($post_id, '_features_concatenated', $features_concat);
            }
        }

        // Set field of concatenated descriptions information
        $desc_phrasing = get_post_meta($post_id, '_department', TRUE) == 'commercial' ? 'description' : 'room';
        $num_property_descs = (int)get_post_meta($post_id, '_'. $desc_phrasing .'s', TRUE);

        $descs_concat_array = array();
        for ( $i = 0; $i < $num_property_descs; ++$i )
        {
            $descs_concat_array[] = get_post_meta($post_id, '_'. $desc_phrasing .'_name_' . $i, TRUE);

            $desc_field_name = $desc_phrasing == 'room' ? '_room_description_' : '_description_';
            $descs_concat_array[] = get_post_meta($post_id, $desc_field_name . $i, TRUE);
        }
        $descs_concat = implode('|', array_filter($descs_concat_array));

        if ( $descs_concat == '' )
        {
            delete_post_meta($post_id, '_'. $desc_phrasing . 's_concatenated');
        }
        else
        {
            $existing_concat = get_post_meta($post_id, '_'. $desc_phrasing . 's_concatenated', TRUE);
            if( !$existing_concat || $existing_concat !== $descs_concat )
            {
                update_post_meta($post_id, '_'. $desc_phrasing . 's_concatenated', $descs_concat);
            }
        }
    }
}

new PH_Post_types();
