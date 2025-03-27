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
        add_action( 'init', array( __CLASS__, 'register_post_statuses' ), 5 );

        add_action( 'wp_trash_post', array( __CLASS__, 'trash_property_children' ), 5 );
        add_action( 'wp_trash_post', array( __CLASS__, 'trash_property_enquiries' ), 5 );

        add_action( 'before_delete_post', array( __CLASS__, 'delete_property_media' ), 5 );
        add_action( 'before_delete_post', array( __CLASS__, 'delete_property_children' ), 5 );
        add_action( 'before_delete_post', array( __CLASS__, 'delete_property_enquiries' ), 5 );
        add_action( 'before_delete_post', array( __CLASS__, 'delete_contact_user' ), 5 );

        add_action( 'delete_user', array( $this, 'delete_contact_user_link' ) );

        add_action( 'save_post', array( __CLASS__, 'create_concatenated_indexable_meta' ), 99, 3 );

        add_action( 'save_post', array( __CLASS__, 'ensure_property_floor_area_to_set' ), 99, 3 );

        add_action( 'save_post', array( __CLASS__, 'update_property_indexed_owner_names' ), 99, 3 );

        add_action( 'save_post', array( __CLASS__, 'store_related_viewings' ), 99, 3 );
        add_action( 'updated_post_meta', array( __CLASS__, 'store_related_viewings_meta_change' ), 10, 4 );

        add_action( 'propertyhive_update_address_concatenated', array( __CLASS__, 'update_address_concatenated' ) );

        add_filter( 'get_terms', array( $this, 'put_terms_in_order' ), 10, 4 );

        add_action( 'propertyhive_after_register_post_types', array( __CLASS__, 'maybe_flush_rewrite_rules' ) );
	}

    public static function maybe_flush_rewrite_rules()
    {
        if ( 'yes' === get_option( 'propertyhive_queue_flush_rewrite_rules' ) ) {
            update_option( 'propertyhive_queue_flush_rewrite_rules', 'no' );
            flush_rewrite_rules();
        }
    }

    public static function register_post_statuses()
    {
        register_post_status('archive', array(
            'label'                     => _x('Archived', 'post'),
            'public'                    => false,
            'exclude_from_search'       => true,
            'show_in_admin_all_list'    => false,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>'),
        ));
    }

    public static function update_property_indexed_owner_names( $post_id, $post, $update )
    {
        // $post_id and $post are required
        if ( empty( $post_id ) || empty( $post ) ) {
            return;
        }

        // Dont' save meta boxes for revisions or autosaves
        if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
            return;
        }

        if ( $post->post_type !== 'property' && $post->post_type !== 'contact' ) {
            return;
        }

        if ( $post->post_type == 'property' )
        {
            // Get the owner contact IDs
            $contact_ids = get_post_meta($post_id, '_owner_contact_id', true);
            if ( !is_array($contact_ids) ) 
            {
                $contact_ids = array($contact_ids);
            }

            // Get the names of the contacts
            $owner_details = array();
            if ( !empty($contact_ids) )
            {
                foreach ( $contact_ids as $contact_id ) 
                {
                    $contact_post = get_post($contact_id);
                    if ( $contact_post && $contact_post->post_type == 'contact' ) 
                    {
                        $owner_details[] = $contact_post->post_title;
                        if ( get_post_meta( $contact_id, '_telephone_number', TRUE ) != '' )
                        {
                            $owner_details[] = get_post_meta( $contact_id, '_telephone_number', TRUE );
                        }
                        if ( get_post_meta( $contact_id, '_email_address', TRUE ) != '' )
                        {
                            $owner_details[] = get_post_meta( $contact_id, '_email_address', TRUE );
                        }
                    }
                }

                $owner_details = array_filter($owner_details);
                $owner_details = array_unique($owner_details);
            }

            // Update the owner names meta field
            update_post_meta($post_id, '_owner_details', implode(', ', $owner_details));
        }

        if ( $post->post_type == 'contact' )
        {
            // Find all properties linked to this contact
            $args = array(
                'post_type' => 'property',
                'meta_query' => array(
                    array(
                        'key' => '_owner_contact_id',
                        'value' => '"' . $post_id . '"',
                        'compare' => 'LIKE'
                    )
                ),
                'posts_per_page' => -1
            );

            $properties = get_posts($args);
            foreach ( $properties as $property ) 
            {
                // Get the owner contact IDs
                $contact_ids = get_post_meta($property->ID, '_owner_contact_id', true);
                if ( !is_array($contact_ids) ) 
                {
                    $contact_ids = array($contact_ids);
                }

                // Get the names of the contacts
                $owner_details = array();
                if ( !empty($contact_ids) )
                {
                    foreach ( $contact_ids as $contact_id ) 
                    {
                        $contact_post = get_post($contact_id);
                        if ( $contact_post && $contact_post->post_type == 'contact' ) 
                        {
                            $owner_details[] = $contact_post->post_title;
                            if ( get_post_meta( $contact_id, '_telephone_number', TRUE ) != '' )
                            {
                                $owner_details[] = get_post_meta( $contact_id, '_telephone_number', TRUE );
                            }
                            if ( get_post_meta( $contact_id, '_email_address', TRUE ) != '' )
                            {
                                $owner_details[] = get_post_meta( $contact_id, '_email_address', TRUE );
                            }
                        }
                    }

                    $owner_details = array_filter($owner_details);
                    $owner_details = array_unique($owner_details);
                }

                // Update the owner names meta field
                update_post_meta($property->ID, '_owner_details', implode(', ', $owner_details));
            }
        }
    }

    public static function put_terms_in_order( $terms, $taxonomies, $query_vars, $term_query ) {

        if ( empty($taxonomies) )
        {
            return $terms;
        }

        if ( !isset($query_vars['fields']) || ( isset($query_vars['fields']) && $query_vars['fields'] != 'all' ) )
        {
            return $terms;
        }

        if ( !is_array($taxonomies) ) { $taxonomies = array($taxonomies); }

        foreach ( $taxonomies as $taxonomy_name )
        {
            $taxonomy = get_taxonomy( $taxonomy_name );

            if ( is_array($taxonomy->object_type) && in_array('property', $taxonomy->object_type) )
            {
                
            }
            else
            {
                return $terms;
            }

            $order = get_option( 'propertyhive_taxonomy_terms_order_' . $taxonomy_name, '' );

            if ( empty($order) )
            {
                return $terms;
            }

            // Convert $order string to an array of IDs
            $order_array = explode('|', $order);

            // Initialize a new array to store the sorted objects
            $sorted_array = array();

            // Loop through each order ID and find matching WP_Term objects
            foreach ( $order_array as $order_id ) 
            {
                foreach ( $terms as $item ) 
                {
                    if ( $item->term_id == $order_id ) 
                    {
                        $sorted_array[] = $item;
                        break; // Stop the inner loop once a match is found
                    }
                }
            }

            // Append WP_Term objects not listed in $order at the end of $sorted_array
            foreach ( $terms as $item ) 
            {
                // Check if the item is not in $sorted_array
                $found = false;
                foreach ( $sorted_array as $sorted_item ) 
                {
                    if ( $sorted_item->term_id == $item->term_id ) 
                    {
                        $found = true;
                        break;
                    }
                }
                // If not found, append to $sorted_array
                if ( !$found ) 
                {
                    $sorted_array[] = $item;
                }
            }

            // Replace the original array with the sorted array
            $terms = $sorted_array;
        }

        return $terms;

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
            apply_filters( 'propertyhive_taxonomy_objects_availability', array( 'property' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_availability',
                array(
                    'label'                 => __( 'Availabilities', 'propertyhive' ),
                    'hierarchical'          => true,
                    'show_ui'               => false,
                    'show_in_nav_menus'     => false,
                    'query_var'             => is_admin(),
                    'rewrite'               => false,
                    'public'                => true,
                    'show_in_rest'          => true,
                )
            )
        );

		register_taxonomy( 'property_type',
	        apply_filters( 'propertyhive_taxonomy_objects_property_type', array( 'property', 'appraisal' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_property_type',
    	        array(
                    'label'                 => __( 'Property Types', 'propertyhive' ),
    	            'hierarchical' 			=> true,
    	            'show_ui' 				=> false,
    	            'show_in_nav_menus' 	=> false,
    	            'query_var' 			=> is_admin(),
    	            'rewrite'				=> false,
    	            'public'                => true,
                    'show_in_rest'          => true,
    	        )
            )
	    );

        register_taxonomy( 'commercial_property_type',
            apply_filters( 'propertyhive_taxonomy_objects_commercial_property_type', array( 'property', 'appraisal' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_commercial_property_type',
                array(
                    'label'                 => __( 'Commercial Property Types', 'propertyhive' ),
                    'hierarchical'          => true,
                    'show_ui'               => false,
                    'show_in_nav_menus'     => false,
                    'query_var'             => is_admin(),
                    'rewrite'               => false,
                    'public'                => true,
                    'show_in_rest'          => true,
                )
            )
        );
        
        register_taxonomy( 'location',
            apply_filters( 'propertyhive_taxonomy_objects_location', array( 'property' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_location',
                array(
                    'label'                 => __( 'Locations', 'propertyhive' ),
                    'hierarchical'          => true,
                    'show_ui'               => false,
                    'show_in_nav_menus'     => false,
                    'query_var'             => is_admin(),
                    'rewrite'               => false,
                    'public'                => true,
                    'show_in_rest'          => true,
                )
            )
        );
        
        register_taxonomy( 'parking',
            apply_filters( 'propertyhive_taxonomy_objects_parking', array( 'property', 'appraisal' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_parking',
                array(
                    'label'                 => __( 'Parking', 'propertyhive' ),
                    'hierarchical'          => true,
                    'show_ui'               => false,
                    'show_in_nav_menus'     => false,
                    'query_var'             => is_admin(),
                    'rewrite'               => false,
                    'public'                => true,
                    'show_in_rest'          => true,
                )
            )
        );
        
        register_taxonomy( 'outside_space',
            apply_filters( 'propertyhive_taxonomy_objects_outside_space', array( 'property', 'appraisal' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_outside_space',
                array(
                    'label'                 => __( 'Outside Spaces', 'propertyhive' ),
                    'hierarchical'          => true,
                    'show_ui'               => false,
                    'show_in_nav_menus'     => false,
                    'query_var'             => is_admin(),
                    'rewrite'               => false,
                    'public'                => true,
                    'show_in_rest'          => true,
                )
            )
        );
        
        register_taxonomy( 'price_qualifier',
            apply_filters( 'propertyhive_taxonomy_objects_price_qualifier', array( 'property' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_price_qualifier',
                array(
                    'label'                 => __( 'Price Qualifiers', 'propertyhive' ),
                    'hierarchical'          => true,
                    'show_ui'               => false,
                    'show_in_nav_menus'     => false,
                    'query_var'             => is_admin(),
                    'rewrite'               => false,
                    'public'                => true,
                    'show_in_rest'          => true,
                )
            )
        );
        
        register_taxonomy( 'tenure',
            apply_filters( 'propertyhive_taxonomy_objects_tenure', array( 'property' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_tenure',
                array(
                    'label'                 => __( 'Tenures', 'propertyhive' ),
                    'hierarchical'          => true,
                    'show_ui'               => false,
                    'show_in_nav_menus'     => false,
                    'query_var'             => is_admin(),
                    'rewrite'               => false,
                    'public'                => true,
                    'show_in_rest'          => true,
                )
            )
        );

        register_taxonomy( 'commercial_tenure',
            apply_filters( 'propertyhive_taxonomy_objects_commercial_tenure', array( 'property' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_commercial_tenure',
                array(
                    'label'                 => __( 'Commercial Tenures', 'propertyhive' ),
                    'hierarchical'          => true,
                    'show_ui'               => false,
                    'show_in_nav_menus'     => false,
                    'query_var'             => is_admin(),
                    'rewrite'               => false,
                    'public'                => true,
                    'show_in_rest'          => true,
                )
            )
        );
        
        register_taxonomy( 'sale_by',
            apply_filters( 'propertyhive_taxonomy_objects_sale_by', array( 'property' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_sale_by',
                array(
                    'label'                 => __( 'Sale By', 'propertyhive' ),
                    'hierarchical'          => true,
                    'show_ui'               => false,
                    'show_in_nav_menus'     => false,
                    'query_var'             => is_admin(),
                    'rewrite'               => false,
                    'public'                => true,
                    'show_in_rest'          => true,
                )
            )
        );
        
        register_taxonomy( 'furnished',
            apply_filters( 'propertyhive_taxonomy_objects_furnished', array( 'property', 'appraisal' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_furnished',
                array(
                    'label'                 => __( 'Furnished', 'propertyhive' ),
                    'hierarchical'          => true,
                    'show_ui'               => false,
                    'show_in_nav_menus'     => false,
                    'query_var'             => is_admin(),
                    'rewrite'               => false,
                    'public'                => true,
                    'show_in_rest'          => true,
                )
            )
        );

        register_taxonomy( 'marketing_flag',
            apply_filters( 'propertyhive_taxonomy_objects_marketing_flag', array( 'property' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_marketing_flag',
                array(
                    'label'                 => __( 'Marketing Flags', 'propertyhive' ),
                    'hierarchical'          => true,
                    'show_ui'               => false,
                    'show_in_nav_menus'     => false,
                    'query_var'             => is_admin(),
                    'rewrite'               => false,
                    'public'                => true,
                    'show_in_rest'          => true,
                )
            )
        );

        register_taxonomy( 'property_feature',
            apply_filters( 'propertyhive_taxonomy_objects_property_feature', array( 'property' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_property_feature',
                array(
                    'label'                 => __( 'Property Features', 'propertyhive' ),
                    'hierarchical'          => true,
                    'show_ui'               => false,
                    'show_in_nav_menus'     => false,
                    'query_var'             => is_admin(),
                    'rewrite'               => false,
                    'public'                => true,
                    'show_in_rest'          => true,
                )
            )
        );

		register_taxonomy( 'management_key_date_type',
			apply_filters( 'propertyhive_taxonomy_objects_management_key_date_type', array( 'key_date' ) ),
            apply_filters(
                'propertyhive_taxonomy_args_management_key_date_type',
    			array(
    				'label'                 => __( 'Management Dates', 'propertyhive' ),
    				'hierarchical'          => true,
    				'show_ui'               => false,
    				'show_in_nav_menus'     => false,
    				'query_var'             => is_admin(),
    				'rewrite'               => false,
    				'public'                => true
    			)
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
					'show_in_nav_menus' 	=> true,
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
                    'show_in_menu'          => false,
                    'show_in_rest'          => true,
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

        register_post_type( "tenancy",
            apply_filters( 'propertyhive_register_post_type_tenancy',
                array(
                    'labels' => array(
                        'name'                  => __( 'Tenancies', 'propertyhive' ),
                        'singular_name'         => __( 'Tenancy', 'propertyhive' ),
                        'menu_name'             => _x( 'Tenancies', 'Admin menu name', 'propertyhive' ),
                        'add_new'               => __( 'Add Tenancy', 'propertyhive' ),
                        'add_new_item'          => __( 'Add New Tenancy', 'propertyhive' ),
                        'edit'                  => __( 'Edit', 'propertyhive' ),
                        'edit_item'             => __( 'Edit Tenancy', 'propertyhive' ),
                        'new_item'              => __( 'New Tenancy', 'propertyhive' ),
                        'view'                  => __( 'View Tenancy', 'propertyhive' ),
                        'view_item'             => __( 'View Tenancy', 'propertyhive' ),
                        'search_items'          => __( 'Search Tenancies', 'propertyhive' ),
                        'not_found'             => __( 'No tenancies found', 'propertyhive' ),
                        'not_found_in_trash'    => __( 'No tenancies found in trash', 'propertyhive' ),
                        'parent'                => __( 'Parent Tenancy', 'propertyhive' )
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

		register_post_type( "key_date",
			apply_filters( 'propertyhive_register_post_type_key_date',
				array(
					'labels' => array(
						'name'                  => __( 'Key Dates', 'propertyhive' ),
						'singular_name'         => __( 'Key Date', 'propertyhive' ),
						'menu_name'             => _x( 'Management', 'Admin menu name', 'propertyhive' ),
						'add_new'               => __( 'Add Key Date', 'propertyhive' ),
						'add_new_item'          => __( 'Add New Key Date', 'propertyhive' ),
						'edit'                  => __( 'Edit', 'propertyhive' ),
						'edit_item'             => __( 'Edit Key Date', 'propertyhive' ),
						'new_item'              => __( 'New Key Date', 'propertyhive' ),
						'view'                  => __( 'View Key Date', 'propertyhive' ),
						'view_item'             => __( 'View Key Date', 'propertyhive' ),
						'search_items'          => __( 'Search Key Dates', 'propertyhive' ),
						'not_found'             => __( 'No key dates found', 'propertyhive' ),
						'not_found_in_trash'    => __( 'No key dates found in trash', 'propertyhive' ),
						'parent'                => __( 'Parent Key Date', 'propertyhive' )
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
					'show_in_menu'          => false,
					'capabilities' => array(
						'create_posts' => 'do_not_allow'
					),
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

    public static function trash_property_enquiries( $post_id )
    {
        if ( apply_filters( 'propertyhive_delete_enquiries_on_property_delete', false ) === true && get_post_type($post_id) == 'property' )
        {
            $args = array(
                'post_type' => 'enquiry',
                'nopaging' => true,
                'post_status' => array('publish', 'pending', 'private', 'draft', 'auto-draft', 'future', 'trash'),
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => '_property_id',
                        'value' => $post_id
                    ),
                    array(
                        'key' => 'property_id',
                        'value' => $post_id
                    )
                )
            );

            $enquiries_query = new WP_Query($args);

            if ( $enquiries_query->have_posts() )
            {
                while( $enquiries_query->have_posts() )
                {
                    $enquiries_query->the_post();

                    wp_trash_post( get_the_ID() );
                }
            }
            wp_reset_postdata();
        }
    }

    public static function delete_property_media( $post_id ) 
    {
        if ( get_post_type($post_id) == 'property' && apply_filters( 'propertyhive_remove_media_on_property_delete', TRUE ) === TRUE )
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

    public static function delete_property_enquiries( $post_id )
    {
        if ( apply_filters( 'propertyhive_delete_enquiries_on_property_delete', false ) === true && get_post_type($post_id) == 'property' )
        {
            $args = array(
                'post_type' => 'enquiry',
                'nopaging' => true,
                'post_status' => array('publish', 'pending', 'private', 'draft', 'auto-draft', 'future', 'trash'),
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => '_property_id',
                        'value' => $post_id
                    ),
                    array(
                        'key' => 'property_id',
                        'value' => $post_id
                    )
                )
            );

            $enquiries_query = new WP_Query($args);

            if ( $enquiries_query->have_posts() )
            {
                while( $enquiries_query->have_posts() )
                {
                    $enquiries_query->the_post();

                    wp_delete_post( get_the_ID(), true );
                }
            }
            wp_reset_postdata();
        }
    }

    public static function delete_contact_user( $post_id )
    {
        if ( get_post_type($post_id) == 'contact' )
        {
            // If the contact being deleted has a user_id meta_key, delete the user with that ID if they're a contact or agent
            $contact_user_id = get_post_meta ($post_id, '_user_id', TRUE );
            if ( !empty($contact_user_id) )
            {
                $user_meta = get_userdata($contact_user_id);
                $user_roles = $user_meta->roles;
                $user_role = array_shift($user_roles);

                if ( in_array($user_role, array( 'property_hive_contact' )) )
                {
                    // Include user admin functions to get access to wp_delete_user().
                    require_once ABSPATH . 'wp-admin/includes/user.php';
                    wp_delete_user( $contact_user_id );
                }
            }
        }
    }

    public static function delete_contact_user_link( $user_id )
    {
        global $post;

        $args = array(
            'post_type' => 'contact',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => '_user_id',
                    'value' => (int)$user_id,
                )
            )
        );
        $agent_query = new WP_Query( $args );
        if ( $agent_query->have_posts() )
        {
            while ( $agent_query->have_posts() )
            {
                $agent_query->the_post();

                delete_post_meta( $post->ID, '_user_id' );

                wp_reset_postdata();
            }
        }
    }

    public static function ensure_property_floor_area_to_set( $post_id, $post, $update )
    {
        // $post_id and $post are required
        if ( empty( $post_id ) || empty( $post ) ) {
            return;
        }

        // Dont' save meta boxes for revisions or autosaves
        if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
            return;
        }

        if ( $post->post_type !== 'property' ) {
            return;
        }

        $property = new PH_Property( $post_id );


        if ( $property->department == 'commercial' || ph_get_custom_department_based_on( $property->department ) == 'commercial' )
        {
            if ( ($property->floor_area_to_sqft == '' || $property->floor_area_to_sqft == '0') && ( $property->floor_area_from_sqft != '' && $property->floor_area_from_sqft != '0' ) )
            {
                update_post_meta( $post_id, '_floor_area_to', $property->floor_area_from );
                update_post_meta( $post_id, '_floor_area_to_sqft', $property->floor_area_from_sqft);
            }
        }
    }

    /**
	 * Check if we're saving, then trigger an action based on the post type
	 *
	 * @param  int $post_id
	 * @param  object $post
	 */
    public static function create_concatenated_indexable_meta( $post_id, $post, $update )
    {
        // $post_id and $post are required
        if ( empty( $post_id ) || empty( $post ) ) {
            return;
        }

        // Dont' save meta boxes for revisions or autosaves
        if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
            return;
        }

        if ( $post->post_type !== 'property' && $post->post_type !== 'contact' ) {
            return;
        }

        if ( $post->post_type == 'property' )
        {
            $property = new PH_Property( $post_id );

            // Set field of concatenated address
            self::remove_all_duplicated_post_meta( $post_id, '_address_concatenated' );

            update_post_meta( $post_id, '_address_concatenated', $property->get_formatted_full_address() );

            // Set field of concatenated features
            self::remove_all_duplicated_post_meta( $post_id, '_features_concatenated' );

            $features_concat_array = $property->get_features();

            $features_concat = implode('|', array_filter($features_concat_array));
            update_post_meta($post_id, '_features_concatenated', $features_concat);

            // Set field of concatenated descriptions information
            self::remove_all_duplicated_post_meta( $post_id, '_descriptions_concatenated' );

            $descs_concat = $property->get_formatted_description();
            update_post_meta($post_id, '_descriptions_concatenated', $descs_concat);
        }

        if ( $post->post_type == 'contact' )
        {
            $contact = new PH_Contact( $post_id );

            // Set field of concatenated address
            self::remove_all_duplicated_post_meta( $post_id, '_address_concatenated' );

            update_post_meta( $post_id, '_address_concatenated', $contact->get_formatted_full_address() );
        }
    }

    private static function remove_all_duplicated_post_meta( $post_id, $meta_key )
    {
        $current_meta_array = get_post_meta( $post_id, $meta_key );
        if ( is_array($current_meta_array) && count($current_meta_array) > 1 )
        {
            delete_post_meta( $post_id, $meta_key );
        }
    }

    public static function update_address_concatenated()
    {
        $args = array(
            'post_type' => 'property',
            'fields' => 'ids',
            'post_status' => array( 'publish', 'draft'),
            'meta_query' => array(
                array(
                    'key' => '_address_concatenated',
                    'compare' => 'NOT EXISTS'
                )
            ),
            'nopaging' => true,
            'orderby' => 'rand',
            'suppress_filters' => true,
        );
        $property_query =  new WP_Query($args);

        if ( $property_query->have_posts() )
        {
            while ( $property_query->have_posts() )
            {
                $property_query->the_post();

                $property = new PH_Property( get_the_ID() );

                // Set field of concatenated address
                update_post_meta( get_the_ID(), '_address_concatenated', $property->get_formatted_full_address() );
            }
        }

        wp_reset_postdata();

        $args = array(
            'post_type' => 'contact',
            'fields' => 'ids',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_address_concatenated',
                    'compare' => 'NOT EXISTS'
                )
            ),
            'nopaging' => true,
            'orderby' => 'rand',
            'suppress_filters' => true,
        );
        $contact_query =  new WP_Query($args);

        if ( $contact_query->have_posts() )
        {
            while ( $contact_query->have_posts() )
            {
                $contact_query->the_post();

                $contact = new PH_Contact( get_the_ID() );

                // Set field of concatenated address
                update_post_meta( get_the_ID(), '_address_concatenated', $contact->get_formatted_full_address() );
            }
        }

        wp_reset_postdata();
    }

    /**
     * @param  int $post_id
     * @param  object $post
     */
    public static function store_related_viewings( $post_id, $post, $update )
    {
        // $post_id and $post are required
        if ( empty( $post_id ) || empty( $post ) ) {
            return;
        }

        // Dont' save meta boxes for revisions or autosaves
        if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
            return;
        }

        if ( $post->post_type !== 'property' && $post->post_type !== 'contact' && $post->post_type !== 'viewing' ) {
            return;
        }

        if ( get_option('propertyhive_module_disabled_viewings', '') == 'yes' ) {
            return;
        }

        $viewing_ids = array();

        switch ( $post->post_type )
        {
            case "property":
            {
                // get all viewings for this property
                $meta_query = array(
                    array(
                        'key' => '_property_id',
                        'value' => $post_id,
                    )
                );

                $args = array(
                    'fields'   => 'ids',
                    'post_type' => 'viewing',
                    'nopaging' => true,
                    'post_status' => 'publish',
                    'meta_query' => $meta_query,
                    'orderby' => 'none'
                );

                $viewings_query = new WP_Query( $args );

                if ( $viewings_query->have_posts() )
                {
                    while ( $viewings_query->have_posts() )
                    {
                        $viewings_query->the_post();

                        $viewing_ids[] = get_the_ID();
                    }
                }
                wp_reset_postdata();

                break;
            }
            case "contact":
            {
                // get all viewings for this contact
                $meta_query = array(
                    array(
                        'key' => '_applicant_contact_id',
                        'value' => $post_id,
                    )
                );

                $args = array(
                    'fields'   => 'ids',
                    'post_type' => 'viewing',
                    'nopaging' => true,
                    'post_status' => 'publish',
                    'meta_query' => $meta_query,
                    'orderby' => 'none'
                );

                $viewings_query = new WP_Query( $args );

                if ( $viewings_query->have_posts() )
                {
                    while ( $viewings_query->have_posts() )
                    {
                        $viewings_query->the_post();

                        $viewing_ids[] = get_the_ID();
                    }
                }
                wp_reset_postdata();

                break;
            }
            case "viewing":
            {
                $viewing_ids[] = $post_id;

                break;
            }
        }

        if ( !empty($viewing_ids) )
        {
            foreach ( $viewing_ids as $viewing_id )
            {
                $viewing = new PH_Viewing( $viewing_id );

                $related_viewings = $viewing->get_related_viewings();

                update_post_meta( $post_id, '_related_viewings', $related_viewings );

                if ( !empty($related_viewings['all']) )
                {
                    foreach ( $related_viewings['all'] as $related_viewing_id )
                    {
                        $other_viewing = new PH_Viewing( $related_viewing_id );

                        $other_related_viewings = $other_viewing->get_related_viewings();

                        update_post_meta( $related_viewing_id, '_related_viewings', $other_related_viewings );
                    }
                }
            }
        }
    }

    public static function store_related_viewings_meta_change( $meta_id, $object_id, $meta_key, $meta_value )
    {
        if ( get_post_type($object_id) == 'viewing' && ( $meta_key == '_status' || $meta_key == '_start_date_time' ) )
        {
            $viewing = new PH_Viewing( $object_id );

            $related_viewings = $viewing->get_related_viewings();

            update_post_meta( $object_id, '_related_viewings', $related_viewings );

            if ( !empty($related_viewings['all']) )
            {
                foreach ( $related_viewings['all'] as $related_viewing_id )
                {
                    $other_viewing = new PH_Viewing( $related_viewing_id );

                    $other_related_viewings = $other_viewing->get_related_viewings();

                    update_post_meta( $related_viewing_id, '_related_viewings', $other_related_viewings );
                }
            }
        }
    }
}

new PH_Post_types();
