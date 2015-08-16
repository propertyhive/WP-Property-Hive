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
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );

		register_taxonomy( 'property_type',
	        'property',
	        array(
	            'hierarchical' 			=> true,
	            'show_ui' 				=> false,
	            'show_in_nav_menus' 	=> false,
	            'query_var' 			=> is_admin(),
	            'rewrite'				=> false,
	            'public'                => true
	        )
	    );
        
        register_taxonomy( 'location',
            'property',
            array(
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );
        
        register_taxonomy( 'parking',
            'property',
            array(
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );
        
        register_taxonomy( 'outside_space',
            'property',
            array(
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
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );
        
        register_taxonomy( 'furnished',
            'property',
            array(
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
					'show_in_menu'          => false
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
                    'publicly_queryable'    => true,
                    'exclude_from_search'   => false,
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
                    'public'                => true,
                    'show_ui'               => false,
                    'capability_type'       => 'post',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => true,
                    'exclude_from_search'   => false,
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
                    'show_ui'               => false,
                    'capability_type'       => 'post',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => false,
                    'exclude_from_search'   => false,
                    'hierarchical'          => false, // Hierarchical causes memory issues - WP loads all records!
                    'query_var'             => true,
                    'supports'              => array( 'title' ),
                    'show_in_nav_menus'     => false,
                    'show_in_menu'          => false
                )
            )
        );
        
        do_action( 'propertyhive_after_register_post_types' );
	}
}

new PH_Post_types();
