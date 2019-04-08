<?php
/**
 * Contains the query functions for PropertyHive which alter the front-end post queries and loops.
 *
 * @class 		PH_Query
 * @version		1.0.0
 * @package		PropertyHive/Classes
 * @category	Class
 * @author 		PropertyHive
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Query' ) ) :

/**
 * PH_Query Class
 */
class PH_Query {

	/** @public array Query vars to add to wp */
	public $query_vars = array();

	/** @public array Unfiltered property ids (before layered nav etc) */
	public $unfiltered_property_ids 	= array();

	/** @public array Filtered property ids (after layered nav) */
	public $filtered_property_ids 	= array();

	/** @public array Filtered property ids (after layered nav, per taxonomy) */
	public $filtered_property_ids_for_taxonomy 	= array();

	/** @public array property IDs that match the layered nav + price filter */
	public $post__in 		= array();

	/** @public array The meta query for the page */
	public $meta_query 		= '';

	/** @public array Post IDs matching layered nav only */
	public $layered_nav_post__in 	= array();

	/** @public array Stores post IDs matching layered nav, so price filter can find max price in view */
	public $layered_nav_property_ids = array();

	/**
	 * Constructor for the query class. Hooks in methods.
	 *
	 * @access public
	 */
	public function __construct() {
	    
		//add_action( 'init', array( $this, 'add_endpoints' ) );
		add_action( 'init', array( $this, 'layered_nav_init' ) );
		add_action( 'init', array( $this, 'price_filter_init' ) );

		if ( ! is_admin() ) {
			add_action( 'init', array( $this, 'get_errors' ) );
			add_filter( 'query_vars', array( $this, 'add_query_vars'), 0 );
			add_action( 'parse_request', array( $this, 'parse_request'), 0 );
			add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
			add_filter( 'the_posts', array( $this, 'the_posts' ), 11, 2 );
			add_action( 'wp', array( $this, 'remove_property_query' ) );
			add_action( 'wp', array( $this, 'remove_ordering_args' ) );
		}

		$this->init_query_vars();
	}

	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {
		// Query vars to add to WP
		$this->query_vars = array(
		
        );
	}

	/**
	 * Get any errors from querystring
	 */
	public function get_errors() {
		if ( ! empty( $_GET['ph_error'] ) && ( $error = sanitize_text_field( $_GET['ph_error'] ) ) && ! ph_has_notice( $error, 'error' ) )
			ph_add_notice( $error, 'error' );
	}

	/**
	 * Add endpoints for query vars
	 */
	public function add_endpoints() {
		foreach ( $this->query_vars as $key => $var )
			add_rewrite_endpoint( $var, EP_PAGES );
	}

	/**
	 * add_query_vars function.
	 *
	 * @access public
	 * @param array $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->query_vars as $key => $var )
			$vars[] = $key;

		return $vars;
	}

	/**
	 * Get query vars
	 * @return array()
	 */
	public function get_query_vars() {
		return $this->query_vars;
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported
	 */
	public function parse_request() {
		global $wp;

		// Map query vars to their keys, or get them if endpoints are not supported
		foreach ( $this->query_vars as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $var ] ) );
			}

			elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
	}

	/**
	 * Hook into pre_get_posts to do the main property query
	 *
	 * @access public
	 * @param mixed $q query object
	 * @return void
	 */
	public function pre_get_posts( $q ) {
		// We only want to affect the main query
		if ( ! $q->is_main_query() )
			return;

		// Fix for verbose page rules
		if ( $GLOBALS['wp_rewrite']->use_verbose_page_rules && isset( $q->queried_object->ID ) && $q->queried_object->ID == ph_get_page_id( 'search_results' ) ) {
			$q->set( 'post_type', 'property' );
			$q->set( 'page', '' );
			$q->set( 'pagename', '' );

			// Fix conditional Functions
			$q->is_archive           = true;
			$q->is_post_type_archive = true;
			$q->is_singular          = false;
			$q->is_page              = false;
		}
        
		// When orderby is set, WordPress shows posts. Get around that here.
		if ( ($q->is_home() || $q->get( 'page_id' ) == get_option('page_on_front')) && 'page' == get_option('show_on_front') && get_option('page_on_front') == ph_get_page_id('search_results') ) 
		{
			$_query = wp_parse_args( $q->query );
			if ( empty( $_query ) || ! array_diff( array_keys( $_query ), array( 'preview', 'page', 'paged', 'cpage', 'orderby' ) ) ) 
			{
				
				$q->is_page = true;
				$q->is_home = false;
				$q->set( 'page_id', (int) get_option('page_on_front') );
				$q->set( 'post_type', 'property' );
			}
		}

		// Special check for sites with the property search results on front page
		if ( $q->is_page() && 'page' == get_option( 'show_on_front' ) && $q->get('page_id') == ph_get_page_id('search_results') ) {

			// This is a front-page property listings
			$q->set( 'post_type', 'property' );
			$q->set( 'page_id', '' );
			if ( isset( $q->query['paged'] ) )
				$q->set( 'paged', $q->query['paged'] );

			// Define a variable so we know this is the front page search results later on
			define( 'SEARCH_RESULTS_IS_ON_FRONT', true );

			// Get the actual WP page to avoid errors and let us use is_front_page()
			// This is hacky but works. Awaiting http://core.trac.wordpress.org/ticket/21096
			global $wp_post_types;

			$search_results_page = get_post( ph_get_page_id('search_results') );
			$q->is_page = true;

			$wp_post_types['property']->ID 			 = $search_results_page->ID;
			$wp_post_types['property']->post_title 	 = $search_results_page->post_title;
			$wp_post_types['property']->post_name 	 = $search_results_page->post_name;
			$wp_post_types['property']->post_type    = $search_results_page->post_type;
			$wp_post_types['property']->ancestors    = get_ancestors( $search_results_page->ID, $search_results_page->post_type );

			// Fix conditional Functions like is_front_page
			$q->is_singular = false;
			$q->is_post_type_archive = true;
			$q->is_archive = true;

			// Fix WP SEO
			if ( class_exists( 'WPSEO_Meta' ) ) {
				add_filter( 'wpseo_metadesc', array( $this, 'wpseo_metadesc' ) );
				add_filter( 'wpseo_metakey', array( $this, 'wpseo_metakey' ) );
			}

		} else {
            
			// Only apply to property categories, the property post archive, the search results page, and property attribute taxonomies
		    if 	( ! $q->is_post_type_archive( 'property' ) && ! $q->is_tax( get_object_taxonomies( 'property' ) ) )
		   		return;
            
		}
        
		$this->property_query( $q );

		if ( is_search() ) 
		{
		    add_filter( 'posts_where', array( $this, 'search_post_excerpt' ) );
		    add_filter( 'wp', array( $this, 'remove_posts_where' ) );
		}

		add_filter( 'posts_where', array( $this, 'exclude_protected_properties' ) );

		// We're on a property search page so queue the propertyhive_get_properties_in_view function
		//add_action( 'wp', array( $this, 'get_properties_in_view' ), 2);

		// And remove the pre_get_posts hook
		$this->remove_property_query();
	}

	/**
	 * search_post_excerpt function.
	 *
	 * @access public
	 * @param string $where (default: '')
	 * @return string (modified where clause)
	 */
	public function search_post_excerpt( $where = '' ) {
		/*global $wp_the_query;

		// If this is not a PH Query, do not modify the query
		if ( empty( $wp_the_query->query_vars['ph_query'] ) || empty( $wp_the_query->query_vars['s'] ) )
		    return $where;

		$where = preg_replace(
		    "/post_title\s+LIKE\s*(\'\%[^\%]+\%\')/",
		    "post_title LIKE $1) OR (post_excerpt LIKE $1", $where );*/

		return $where;
	}

	/**
	 * Prevent password protected properties appearing in the loops
	 *
	 * @param  string $where
	 * @return string
	 */
	public function exclude_protected_properties( $where ) {
		global $wpdb;
		$where .= " AND {$wpdb->posts}.post_password = ''";
    	return $where;
	}

	/**
	 * wpseo_metadesc function.
	 * Hooked into wpseo_ hook already, so no need for function_exist
	 *
	 * @access public
	 * @return string
	 */
	public function wpseo_metadesc() {
		return WPSEO_Meta::get_value( 'metadesc', ph_get_page_id('search_results') );
	}


	/**
	 * wpseo_metakey function.
	 * Hooked into wpseo_ hook already, so no need for function_exist
	 *
	 * @access public
	 * @return string
	 */
	public function wpseo_metakey() {
		return WPSEO_Meta::get_value( 'metakey', ph_get_page_id('search_results') );
	}


	/**
	 * Hook into the_posts to do the main property query if needed - relevanssi compatibility
	 *
	 * @access public
	 * @param array $posts
	 * @param WP_Query|bool $query (default: false)
	 * @return array
	 */
	public function the_posts( $posts, $query = false ) {
	    
		// Abort if there's no query
		if ( ! $query )
			return $posts;

		// Abort if we're not filtering posts
		if ( empty( $this->post__in ) )
			return $posts;

		// Abort if this query has already been done
		if ( ! empty( $query->ph_query ) )
			return $posts;

		// Abort if this isn't a search query
		if ( empty( $query->query_vars["s"] ) )
			return $posts;

		// Abort if we're not on a post type archive/property taxonomy
		if 	( ! $query->is_post_type_archive( 'property' ) && ! $query->is_tax( get_object_taxonomies( 'property' ) ) )
	   		return $posts;

		$filtered_posts = array();
		$queried_post_ids = array();

		foreach ( $posts as $post ) {
		    if ( in_array( $post->ID, $this->post__in ) ) {
			    $filtered_posts[] = $post;
			    $queried_post_ids[] = $post->ID;
		    }
		}

		$query->posts = $filtered_posts;
	    $query->post_count = count( $filtered_posts );

	    // Ensure filters are set
	    $this->unfiltered_property_ids = $queried_post_ids;
	    $this->filtered_property_ids = $queried_post_ids;

	    if ( sizeof( $this->layered_nav_post__in ) > 0 ) {
		    $this->layered_nav_property_ids = array_intersect( $this->unfiltered_property_ids, $this->layered_nav_post__in );
	    } else {
		    $this->layered_nav_property_ids = $this->unfiltered_property_ids;
	    }

		return $filtered_posts;
	}


	/**
	 * Query the properties, applying sorting/ordering etc. This applies to the main wordpress loop
	 *
	 * @access public
	 * @param mixed $q
	 * @return void
	 */
	public function property_query( $q ) {

		// Meta query
		$meta_query = $this->get_meta_query( $q );
        
        // Tax query
        $tax_query = $this->get_tax_query( $q->get( 'tax_query' ) );

        // Date query
		$date_query = $this->get_date_query();

		// Ordering
		$ordering   = $this->get_search_results_ordering_args();

		// Get a list of post id's which match the current filters set (in the layered nav and price filter)
		$post__in = array();
		//$post__in   = array_unique( apply_filters( 'loop_shop_post_in', array() ) );

		// Ordering query vars
		$q->set( 'orderby', $ordering['orderby'] );
		$q->set( 'order', $ordering['order'] );
		if ( isset( $ordering['meta_key'] ) )
			$q->set( 'meta_key', $ordering['meta_key'] );

		// Query vars that affect posts shown
		$q->set( 'meta_query', $meta_query );
        $q->set( 'tax_query', $tax_query );
        $q->set( 'date_query', $date_query );
		$q->set( 'post__in', $post__in );
		$q->set( 'posts_per_page', $q->get( 'posts_per_page' ) ? $q->get( 'posts_per_page' ) : apply_filters( 'loop_search_results_per_page', get_option( 'posts_per_page' ) ) );

		// Set a special variable
		$q->set( 'ph_query', true );

		// Store variables
		$this->post__in   = $post__in;
		$this->meta_query = $meta_query;
        $this->tax_query = $tax_query;
        
		do_action( 'propertyhive_property_query', $q, $this );
	}


	/**
	 * Remove the query
	 *
	 * @access public
	 * @return void
	 */
	public function remove_property_query() {
		remove_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}

	/**
	 * Remove ordering queries
	 */
	public function remove_ordering_args() {
	    
	}

	/**
	 * Remove the posts_where filter
	 *
	 * @access public
	 * @return void
	 */
	public function remove_posts_where() {
		remove_filter( 'posts_where', array( $this, 'search_post_excerpt' ) );
	}


	/**
	 * Get an unpaginated list all property ID's (both filtered and unfiltered). Makes use of transients.
	 *
	 * @access public
	 * @return void
	 */
	public function get_properties_in_view() {
		global $wp_the_query;

		$unfiltered_property_ids = array();

		// Get main query
		$current_wp_query = $wp_the_query->query;

		// Get WP Query for current page (without 'paged')
		unset( $current_wp_query['paged'] );

		// Generate a transient name based on current query
		$transient_name = 'ph_uf_pid_' . md5( http_build_query( $current_wp_query ) );
		$transient_name = ( is_search() ) ? $transient_name . '_s' : $transient_name;

		if ( false === ( $unfiltered_property_ids = get_transient( $transient_name ) ) ) {

		    // Get all visible posts, regardless of filters
		    $unfiltered_property_ids = get_posts(
				array_merge(
					$current_wp_query,
					array(
						'post_type' 	=> 'property',
						'numberposts' 	=> -1,
						'post_status' 	=> 'publish',
						'meta_query' 	=> $this->meta_query,
						'fields' 		=> 'ids',
						'no_found_rows' => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false
					)
				)
			);

			set_transient( $transient_name, $unfiltered_property_ids, YEAR_IN_SECONDS );
		}

		// Store the variable
		$this->unfiltered_property_ids = $unfiltered_property_ids;

		// Also store filtered posts ids...
		if ( sizeof( $this->post__in ) > 0 )
			$this->filtered_property_ids = array_intersect( $this->unfiltered_property_ids, $this->post__in );
		else
			$this->filtered_property_ids = $this->unfiltered_property_ids;

		// And filtered post ids which just take layered nav into consideration (to find max price in the price widget)
		if ( sizeof( $this->layered_nav_post__in ) > 0 )
			$this->layered_nav_property_ids = array_intersect( $this->unfiltered_property_ids, $this->layered_nav_post__in );
		else
			$this->layered_nav_property_ids = $this->unfiltered_property_ids;
	}


	/**
	 * Returns an array of arguments for ordering properties based on the selected values
	 *
	 * @access public
	 * @return array
	 */
	public function get_search_results_ordering_args( $orderby = '', $order = '' ) {
		// Get ordering from query string unless defined
		if ( ! $orderby ) {
			$orderby_value = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : apply_filters( 'propertyhive_default_search_results_orderby', get_option( 'propertyhive_default_search_results_orderby' ) );

			// Get order + orderby args from string
			$orderby_value = explode( '-', $orderby_value );
			$orderby       = esc_attr( $orderby_value[0] );
			$order         = ! empty( $orderby_value[1] ) ? $orderby_value[1] : $order;
		}

		$orderby = strtolower( $orderby );
		$order   = strtoupper( $order );

		$args = array();

		// default - menu_order
		if (
			( isset($_REQUEST['department']) && $_REQUEST['department'] != 'commercial' ) ||
			( !isset($_REQUEST['department']) && get_option( 'propertyhive_primary_department' ) != 'commercial' )
		)
		{
			$args['orderby']  = 'meta_value_num';
			$args['order']    = $order == 'ASC' ? 'ASC' : 'DESC';
			$args['meta_key'] = '_price_actual';
		}
		elseif (
			( isset($_REQUEST['department']) && $_REQUEST['department'] == 'commercial' ) ||
			( !isset($_REQUEST['department']) && get_option( 'propertyhive_primary_department' ) == 'commercial' )
		)
		{
			$args['orderby']  = 'meta_value_num';
			$args['order']    = $order == 'ASC' ? 'ASC' : 'DESC';
			$args['meta_key'] = '_floor_area_from_sqft';
		}

		switch ( $orderby ) {
			case 'rand' :
				$args['orderby']  = 'rand';
			break;
			case 'date' :
				$args['orderby']  = 'date';
				$args['order']    = $order == 'ASC' ? 'ASC' : 'DESC';
			break;
			case 'price' :
				$args['orderby']  = 'meta_value_num';
				$args['order']    = $order == 'ASC' ? 'ASC' : 'DESC';
				if (
					( isset($_REQUEST['department']) && $_REQUEST['department'] == 'commercial' ) ||
					( !isset($_REQUEST['department']) && get_option( 'propertyhive_primary_department' ) == 'commercial' )
				)
				{
					$args['meta_key'] = '_price_from_actual';
				}
				else
				{
					$args['meta_key'] = '_price_actual';
				}
			break;
			case 'floor_area' :
				$args['orderby']  = 'meta_value_num';
				$args['order']    = $order == 'ASC' ? 'ASC' : 'DESC';
				$args['meta_key'] = '_floor_area_from_sqft';
			break;
		}

		return apply_filters( 'propertyhive_get_search_results_ordering_args', $args );
	}

	/**
	 * Appends date queries to an array.
	 * @access public
	 * @param array $meta_query
	 * @return array
	 */
	public function get_date_query() {

		$date_query = array();

		if ( isset( $_REQUEST['added_from'] ) && $_REQUEST['added_from'] != '' )
        {
            $date_query = array(
                'column'  => 'post_date_gmt',
                'after'   => sanitize_text_field( $_REQUEST['added_from'] ) 
            );
        }

		if ( isset( $_REQUEST['added_from_hours'] ) && $_REQUEST['added_from_hours'] != '' )
        {
            $date_query = array(
                'column'  => 'post_date_gmt',
                'after'   => sanitize_text_field( $_REQUEST['added_from_hours'] ) . ' hours ago'
            );
        }

        return array_filter( $date_query );
	}

	/**
	 * Appends meta queries to an array.
	 * @access public
	 * @param array $meta_query
	 * @return array
	 */
	public function get_meta_query( $q = array() ) {
	    
        $meta_query = array();
        if ( !empty($q) )
        {
            $meta_query = $q->get( 'meta_query' );
        }
        
		if ( ! is_array( $meta_query ) )
			$meta_query = array();

		$meta_query[] = $this->on_market_meta_query();
        $meta_query[] = $this->department_meta_query($q);
        $meta_query[] = $this->address_keyword_meta_query();
        $meta_query[] = $this->country_meta_query();
        $meta_query[] = $this->minimum_price_meta_query();
        $meta_query[] = $this->maximum_price_meta_query();
        $meta_query[] = $this->price_range_meta_query();
        $meta_query[] = $this->minimum_rent_meta_query();
        $meta_query[] = $this->maximum_rent_meta_query();
        $meta_query[] = $this->rent_range_meta_query();
        $meta_query[] = $this->bedrooms_meta_query();
        $meta_query[] = $this->minimum_bedrooms_meta_query();
        $meta_query[] = $this->maximum_bedrooms_meta_query();
        $meta_query[] = $this->minimum_bathrooms_meta_query();
        $meta_query[] = $this->maximum_bathrooms_meta_query();
        $meta_query[] = $this->minimum_reception_rooms_meta_query();
        $meta_query[] = $this->maximum_reception_rooms_meta_query();
        $meta_query[] = $this->available_date_from_meta_query();
        $meta_query[] = $this->minimum_floor_area_meta_query();
        $meta_query[] = $this->maximum_floor_area_meta_query();
        $meta_query[] = $this->floor_area_range_meta_query();
        $meta_query[] = $this->commercial_for_sale_to_rent_meta_query();
        $meta_query[] = $this->commercial_for_sale_meta_query();
        $meta_query[] = $this->commercial_to_rent_meta_query();
        $meta_query[] = $this->negotiator_meta_query();
        $meta_query[] = $this->office_meta_query();
        
		return array_filter( apply_filters( 'propertyhive_property_query_meta_query', $meta_query, $this ) );
	}

	/**
	 * Returns a meta query to handle property on market status
	 *
	 * @access public
	 * @param string $compare (default: 'IN')
	 * @return array
	 */
	public function on_market_meta_query( ) {
        
        $meta_query = array();
        
        if ( !is_admin() )
        {
    		$meta_query = array(
    		    'key'     => '_on_market',
    		    'value'   => 'yes',
    		    'compare' => '='
    		);
		}

		return $meta_query;
	}

    /**
     * Returns a meta query to handle property department
     *
     * @access public
     * @return array
     */
    public function department_meta_query( $q ) {
        
        $meta_query = array();
        
        if ( isset( $_REQUEST['department'] ) && $_REQUEST['department'] != '' )
        {
            $meta_query = array(
                'key'     => '_department',
                'value'   => sanitize_text_field( $_REQUEST['department'] ),
                'compare' => '='
            );
        }
        else
        {
            // Need a department set if on search results page
            if (!empty($q) && $q->is_post_type_archive( 'property' ))
            {
            	if (get_option( 'propertyhive_primary_department' ) != '')
            	{
            		$department = get_option( 'propertyhive_primary_department' );
            	}
            	else
            	{
	                $departments = array();
	                if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
	                {
	                    $departments[] = 'residential-sales';
	                }
	                if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
	                {
	                    $departments[] = 'residential-lettings';
	                }
	                if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
	                {
	                    $departments[] = 'commercial';
	                }
	                
	                $department = $departments[0];
            	}
                $meta_query = array(
                    'key'     => '_department',
                    'value'   => $department,
                    'compare' => '='
                );
            }
        }
        
        return $meta_query;
    }

    /**
	 * Returns a meta query to handle searching for a keyword in the address
	 *
	 * @access public
	 * @param string $compare (default: 'IN')
	 * @return array
	 */
	public function address_keyword_meta_query( ) {
      	
      	$meta_query = array();
      	
      	if ( isset( $_REQUEST['address_keyword'] ) && $_REQUEST['address_keyword'] != '' )
        {
        	$_REQUEST['address_keyword'] = ph_clean( wp_unslash( $_REQUEST['address_keyword'] ) );

        	// Remove country code from end (i.e. ', UK')
        	$_REQUEST['address_keyword'] = preg_replace('/\,\s?[A-Z][A-Z]$/', '', $_REQUEST['address_keyword']);

        	$address_keywords = array( $_REQUEST['address_keyword'] );

        	if ( strpos( $_REQUEST['address_keyword'], ' ' ) !== FALSE )
        	{
        		$address_keywords[] = str_replace(" ", "-", ph_clean($_REQUEST['address_keyword']));
        	}
        	if ( strpos( $_REQUEST['address_keyword'], '-' ) !== FALSE )
        	{
        		$address_keywords[] = str_replace("-", " ", ph_clean($_REQUEST['address_keyword']));
        	}

	      	$meta_query = array('relation' => 'OR');

	      	$address_fields_to_query = array(
	      		'_reference_number',
	      		'_address_street',
	      		'_address_two',
	      		'_address_three',
	      		'_address_four',
	      		'_address_postcode'
	      	);

	      	$address_fields_to_query = apply_filters( 'propertyhive_address_fields_to_query', $address_fields_to_query );

	      	foreach ( $address_keywords as $address_keyword )
	      	{
	      		foreach ( $address_fields_to_query as $address_field )
	      		{
	      			if ( $address_field == '_address_postcode' ) { continue; } // ignore postcode as that is handled differently afterwards

	      			$meta_query[] = array(
					    'key'     => $address_field,
					    'value'   => $address_keyword,
					    'compare' => get_option( 'propertyhive_address_keyword_compare', '=' )
					);
	      		}
			}
			if ( in_array('_address_postcode', $address_fields_to_query) )
			{
		      	if ( strlen($_REQUEST['address_keyword']) <= 4 )
		      	{
		      		$meta_query[] = array(
					    'key'     => '_address_postcode',
					    'value'   => ph_clean( $_REQUEST['address_keyword'] ),
					    'compare' => '='
					);
		      		$meta_query[] = array(
					    'key'     => '_address_postcode',
					    'value'   => '^' . ph_clean( $_REQUEST['address_keyword'] ) . '[ ]',
					    'compare' => 'RLIKE'
					);
		      	}
		      	else
		      	{
		      		$meta_query[] = array(
					    'key'     => '_address_postcode',
					    'value'   => ph_clean( $_REQUEST['address_keyword'] ),
					    'compare' => 'LIKE'
					);
		      	}
		    }
      	}

		return $meta_query;
	}

	/**
     * Returns a meta query to handle country
     *
     * @access public
     * @return array
     */
    public function country_meta_query( ) {
        
        $meta_query = array();
        
        if ( isset( $_REQUEST['country'] ) && $_REQUEST['country'] != '' )
        {
            $meta_query = array(
                'key'     => '_address_country',
                'value'   => ph_clean( $_REQUEST['country'] )
            );
        }
        
        return $meta_query;
    }
    
    /**
     * Returns a meta query to handle minimum price
     *
     * @access public
     * @return array
     */
    public function minimum_price_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-sales' && 
            isset( $_REQUEST['minimum_price'] ) && $_REQUEST['minimum_price'] != '' 
        )
        {
        	$search_form_currency = get_option( 'propertyhive_search_form_currency', 'GBP' );

        	$minimum_price = $_REQUEST['minimum_price'];
        	if ( $search_form_currency != 'GBP' )
        	{
        		// Convert $_REQUEST['minimum_price'] to GBP
        		$ph_countries = new PH_Countries();

        		$minimum_price = $ph_countries->convert_price_to_gbp( $minimum_price, $search_form_currency );
        	}

            $meta_query = array(
                'key'     => '_price_actual',
                'value'   => ph_clean( floor( $minimum_price ) ),
                'compare' => '>=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }
    
    /**
     * Returns a meta query to handle maximum price
     *
     * @access public
     * @return array
     */
    public function maximum_price_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-sales' && 
            isset( $_REQUEST['maximum_price'] ) && $_REQUEST['maximum_price'] != '' 
        )
        {
        	$search_form_currency = get_option( 'propertyhive_search_form_currency', 'GBP' );

        	$maximum_price = $_REQUEST['maximum_price'];
        	if ( $search_form_currency != 'GBP' )
        	{
        		// Convert $_REQUEST['maximum_price'] to GBP
        		$ph_countries = new PH_Countries();

        		$maximum_price = $ph_countries->convert_price_to_gbp( $maximum_price, $search_form_currency );
        	}

            $meta_query = array(
                'key'     => '_price_actual',
                'value'   => ph_clean( ceil( $maximum_price ) ),
                'compare' => '<=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }

    /**
     * Returns a meta query to handle price range
     *
     * @access public
     * @return array
     */
    public function price_range_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-sales' && 
            isset( $_REQUEST['price_range'] ) && $_REQUEST['price_range'] != '' 
        )
        {
        	$explode_price_range = explode("-", ph_clean($_REQUEST['price_range']));

        	$search_form_currency = get_option( 'propertyhive_search_form_currency', 'GBP' );

        	if ( isset($explode_price_range[0]) && $explode_price_range[0] != '' )
        	{
        		$minimum_price = $explode_price_range[0];

	        	if ( $search_form_currency != 'GBP' )
	        	{
	        		// Convert $explode_price_range[0] to GBP
	        		$ph_countries = new PH_Countries();

	        		$minimum_price = $ph_countries->convert_price_to_gbp( $minimum_price, $search_form_currency );
	        	}

	            $meta_query[] = array(
	                'key'     => '_price_actual',
	                'value'   => sanitize_text_field( floor( $minimum_price ) ),
	                'compare' => '>=',
	                'type'    => 'NUMERIC' 
	            );
	        }
	        if ( isset($explode_price_range[1]) && $explode_price_range[1] != '' )
        	{
        		$maximum_price = $explode_price_range[1];
	        	if ( $search_form_currency != 'GBP' )
	        	{
	        		// Convert $explode_price_range[1] to GBP
	        		$ph_countries = new PH_Countries();

	        		$maximum_price = $ph_countries->convert_price_to_gbp( $maximum_price, $search_form_currency );
	        	}

	            $meta_query[] = array(
	                'key'     => '_price_actual',
	                'value'   => sanitize_text_field( ceil( $maximum_price ) ),
	                'compare' => '<=',
	                'type'    => 'NUMERIC' 
	            );
	        }
        }
        
        return $meta_query;
    }
    
    /**
     * Returns a meta query to handle minimum rent
     *
     * @access public
     * @return array
     */
    public function minimum_rent_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-lettings' && 
            isset( $_REQUEST['minimum_rent'] ) && $_REQUEST['minimum_rent'] != '' 
        )
        {
        	$search_form_currency = get_option( 'propertyhive_search_form_currency', 'GBP' );

        	$minimum_rent = $_REQUEST['minimum_rent'];
        	if ( $search_form_currency != 'GBP' )
        	{
        		// Convert $_REQUEST['minimum_rent'] to GBP
        		$ph_countries = new PH_Countries();

        		$minimum_rent = $ph_countries->convert_price_to_gbp( $minimum_rent, $search_form_currency );
        	}

            $meta_query = array(
                'key'     => '_price_actual',
                'value'   => ph_clean( floor( $minimum_rent ) ),
                'compare' => '>=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }
    
    /**
     * Returns a meta query to handle maximum rent
     *
     * @access public
     * @return array
     */
    public function maximum_rent_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-lettings' && 
            isset( $_REQUEST['maximum_rent'] ) && $_REQUEST['maximum_rent'] != '' 
        )
        {
        	$search_form_currency = get_option( 'propertyhive_search_form_currency', 'GBP' );

        	$maximum_rent = $_REQUEST['maximum_rent'];
        	if ( $search_form_currency != 'GBP' )
        	{
        		// Convert $_REQUEST['maximum_rent'] to GBP
        		$ph_countries = new PH_Countries();

        		$maximum_rent = $ph_countries->convert_price_to_gbp( $maximum_rent, $search_form_currency );
        	}

            $meta_query = array(
                'key'     => '_price_actual',
                'value'   => ph_clean( ceil( $maximum_rent ) ),
                'compare' => '<=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }

    /**
     * Returns a meta query to handle rent range
     *
     * @access public
     * @return array
     */
    public function rent_range_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-lettings' && 
            isset( $_REQUEST['rent_range'] ) && $_REQUEST['rent_range'] != '' 
        )
        {
        	$explode_rent_range = explode("-", ph_clean($_REQUEST['rent_range']));

        	$search_form_currency = get_option( 'propertyhive_search_form_currency', 'GBP' );

        	if ( isset($explode_rent_range[0]) && $explode_rent_range[0] != '' )
        	{
        		$minimum_rent = $explode_rent_range[0];
	        	if ( $search_form_currency != 'GBP' )
	        	{
	        		// Convert $explode_rent_range[0] to GBP
	        		$ph_countries = new PH_Countries();

	        		$minimum_rent = $ph_countries->convert_price_to_gbp( $minimum_rent, $search_form_currency );
	        	}

	            $meta_query[] = array(
	                'key'     => '_price_actual',
	                'value'   => sanitize_text_field( floor( $minimum_rent ) ),
	                'compare' => '>=',
	                'type'    => 'NUMERIC' 
	            );
	        }
	        if ( isset($explode_rent_range[1]) && $explode_rent_range[1] != '' )
        	{
        		$maximum_rent = $explode_rent_range[1];
	        	if ( $search_form_currency != 'GBP' )
	        	{
	        		// Convert $explode_rent_range[1] to GBP
	        		$ph_countries = new PH_Countries();

	        		$maximum_rent = $ph_countries->convert_price_to_gbp( $maximum_rent, $search_form_currency );
	        	}

	            $meta_query[] = array(
	                'key'     => '_price_actual',
	                'value'   => sanitize_text_field( ceil( $maximum_rent ) ),
	                'compare' => '<=',
	                'type'    => 'NUMERIC' 
	            );
	        }
        }
        
        return $meta_query;
    }

    /**
     * Returns a meta query to handle exact bedrooms
     *
     * @access public
     * @return array
     */
    public function bedrooms_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
        	(
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-sales') ||
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-lettings')
        	) &&
        	isset( $_REQUEST['bedrooms'] ) && $_REQUEST['bedrooms'] != '' 
        )
        {
            $meta_query = array(
                'key'     => '_bedrooms',
                'value'   => ph_clean( $_REQUEST['bedrooms'] ),
                'compare' => '=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }
    
    /**
     * Returns a meta query to handle minimum bedrooms
     *
     * @access public
     * @return array
     */
    public function minimum_bedrooms_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
        	(
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-sales') ||
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-lettings')
        	) &&
        	isset( $_REQUEST['minimum_bedrooms'] ) && $_REQUEST['minimum_bedrooms'] != '' 
        )
        {
            $meta_query = array(
                'key'     => '_bedrooms',
                'value'   => ph_clean( $_REQUEST['minimum_bedrooms'] ),
                'compare' => '>=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }

    /**
     * Returns a meta query to handle maximum bedrooms
     *
     * @access public
     * @return array
     */
    public function maximum_bedrooms_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
        	(
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-sales') ||
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-lettings')
        	) &&
        	isset( $_REQUEST['maximum_bedrooms'] ) && $_REQUEST['maximum_bedrooms'] != '' 
        )
        {
            $meta_query = array(
                'key'     => '_bedrooms',
                'value'   => ph_clean( $_REQUEST['maximum_bedrooms'] ),
                'compare' => '<=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }

	/**
     * Returns a meta query to handle minimum bathrooms
     *
     * @access public
     * @return array
     */
    public function minimum_bathrooms_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
        	(
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-sales') ||
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-lettings')
        	) &&
        	isset( $_REQUEST['minimum_bathrooms'] ) && $_REQUEST['minimum_bathrooms'] != '' 
        )
        {
            $meta_query = array(
                'key'     => '_bathrooms',
                'value'   => ph_clean( $_REQUEST['minimum_bathrooms'] ),
                'compare' => '>=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }

    /**
     * Returns a meta query to handle maximum bathrooms
     *
     * @access public
     * @return array
     */
    public function maximum_bathrooms_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
        	(
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-sales') ||
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-lettings')
        	) &&
        	isset( $_REQUEST['maximum_bathrooms'] ) && $_REQUEST['maximum_bathrooms'] != '' 
        )
        {
            $meta_query = array(
                'key'     => '_bathrooms',
                'value'   => ph_clean( $_REQUEST['maximum_bathrooms'] ),
                'compare' => '<=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }

	/**
     * Returns a meta query to handle minimum reception rooms
     *
     * @access public
     * @return array
     */
    public function minimum_reception_rooms_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
        	(
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-sales') ||
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-lettings')
        	) &&
        	isset( $_REQUEST['minimum_reception_rooms'] ) && $_REQUEST['minimum_reception_rooms'] != '' 
        )
        {
            $meta_query = array(
                'key'     => '_reception_rooms',
                'value'   => ph_clean( $_REQUEST['minimum_reception_rooms'] ),
                'compare' => '>=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }

    /**
     * Returns a meta query to handle maximum reception rooms
     *
     * @access public
     * @return array
     */
    public function maximum_reception_rooms_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
        	(
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-sales') ||
        		(isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-lettings')
        	) &&
        	isset( $_REQUEST['maximum_reception_rooms'] ) && $_REQUEST['maximum_reception_rooms'] != '' 
        )
        {
            $meta_query = array(
                'key'     => '_reception_rooms',
                'value'   => ph_clean( $_REQUEST['maximum_reception_rooms'] ),
                'compare' => '<=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }

    /**
     * Returns a meta query to handle available date from
     *
     * @access public
     * @return array
     */
    public function available_date_from_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
        	isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'residential-lettings' &&
        	isset( $_REQUEST['available_date_from'] ) && $_REQUEST['available_date_from'] != '' 
        )
        {
        	$available_date = ph_clean($_REQUEST['available_date_from']);
        	if ( strpos($available_date, '/') !== FALSE )
        	{
        		// it's been provided in the format dd/mm/yyyy
        		$explode_available_date = explode("/", $available_date);
        		if ( count($explode_available_date) ==  3 )
        		{
        			$available_date = $explode_available_date[0] . '-' . $explode_available_date[1] . '-' . $explode_available_date[2];
        		}
        	}
            $meta_query = array(
                'key'     => '_available_date',
                'value'   => ph_clean( $available_date ),
                'compare' => '<=', 
            );
        }
        
        return $meta_query;
    }

    /**
     * Returns a meta query to handle minimum floor area
     *
     * @access public
     * @return array
     */
    public function minimum_floor_area_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'commercial' && 
            isset( $_REQUEST['minimum_floor_area'] ) && $_REQUEST['minimum_floor_area'] != '' 
        )
        {
            $meta_query = array(
                'key'     => '_floor_area_from_sqft',
                'value'   => ph_clean( $_REQUEST['minimum_floor_area'] ),
                'compare' => '>=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }
    
    /**
     * Returns a meta query to handle maximum floor area
     *
     * @access public
     * @return array
     */
    public function maximum_floor_area_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'commercial' && 
            isset( $_REQUEST['maximum_floor_area'] ) && $_REQUEST['maximum_floor_area'] != '' 
        )
        {
            $meta_query = array(
                'key'     => '_floor_area_to_sqft',
                'value'   => ph_clean( $_REQUEST['maximum_floor_area'] ),
                'compare' => '<=',
                'type'    => 'NUMERIC' 
            );
        }
        
        return $meta_query;
    }

    /**
     * Returns a meta query to handle floor area range
     *
     * @access public
     * @return array
     */
    public function floor_area_range_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'commercial' && 
            isset( $_REQUEST['floor_area_range'] ) && $_REQUEST['floor_area_range'] != '' 
        )
        {
        	$explode_floor_area_range = explode("-", ph_clean($_REQUEST['floor_area_range']));

        	if ( isset($explode_floor_area_range[0]) && $explode_floor_area_range[0] != '' )
        	{
	            $meta_query = array(
	                'key'     => '_floor_area_from_sqft',
	                'value'   => ph_clean( $explode_floor_area_range[0] ),
	                'compare' => '>=',
	                'type'    => 'NUMERIC' 
	            );
	        }
	        if ( isset($explode_floor_area_range[1]) && $explode_floor_area_range[1] != '' )
        	{
	            $meta_query = array(
	                'key'     => '_floor_area_to_sqft',
	                'value'   => ph_clean( $explode_floor_area_range[1] ),
	                'compare' => '<=',
	                'type'    => 'NUMERIC' 
	            );
	        }
        }
        
        return $meta_query;
    }

    /**
     * Returns a meta query to handle commercial for sale or to rent
     *
     * @access public
     * @return array
     */
    public function commercial_for_sale_to_rent_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'commercial' && 
            isset( $_REQUEST['commercial_for_sale_to_rent'] ) && $_REQUEST['commercial_for_sale_to_rent'] == 'for_sale' 
        )
        {
            $meta_query = array(
                'key'     => '_for_sale',
                'value'   => 'yes',
                'compare' => '=',
            );
        }

        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'commercial' && 
            isset( $_REQUEST['commercial_for_sale_to_rent'] ) && $_REQUEST['commercial_for_sale_to_rent'] == 'to_rent' 
        )
        {
            $meta_query = array(
                'key'     => '_to_rent',
                'value'   => 'yes',
                'compare' => '=',
            );
        }
        
        return $meta_query;
    }

    /**
     * Returns a meta query to handle commercial for sale
     *
     * @access public
     * @return array
     */
    public function commercial_for_sale_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'commercial' && 
            isset( $_REQUEST['commercial_for_sale'] ) && $_REQUEST['commercial_for_sale'] == '1' 
        )
        {
            $meta_query = array(
                'key'     => '_for_sale',
                'value'   => 'yes',
                'compare' => '=',
            );
        }
        
        return $meta_query;
    }

    /**
     * Returns a meta query to handle commercial to rent
     *
     * @access public
     * @return array
     */
    public function commercial_to_rent_meta_query( ) {
        
        $meta_query = array();
        
        if ( 
            isset( $_REQUEST['department'] ) && $_REQUEST['department'] == 'commercial' && 
            isset( $_REQUEST['commercial_to_rent'] ) && $_REQUEST['commercial_to_rent'] == '1' 
        )
        {
            $meta_query = array(
                'key'     => '_to_rent',
                'value'   => 'yes',
                'compare' => '=',
            );
        }
        
        return $meta_query;
    }

    /**
	 * Returns a meta query to handle property negotiator
	 *
	 * @access public
	 * @return array
	 */
	public function negotiator_meta_query( ) {
        
        $meta_query = array();
        
        if ( isset( $_REQUEST['negotiator_id'] ) && $_REQUEST['negotiator_id'] != '' )
        {
    		$meta_query = array(
    		    'key'     => '_negotiator_id',
    		    'value'   => (int)$_REQUEST['negotiator_id'],
    		    'compare' => '='
    		);
		}

		return $meta_query;
	}

    /**
	 * Returns a meta query to handle property office
	 *
	 * @access public
	 * @param string $compare (default: 'IN')
	 * @return array
	 */
	public function office_meta_query( ) {
        
        $meta_query = array();
        
        if ( isset( $_REQUEST['officeID'] ) && $_REQUEST['officeID'] != '' )
        {
    		$meta_query = array(
    		    'key'     => '_office_id',
    		    'value'   => (int)$_REQUEST['officeID'],
    		    'compare' => '='
    		);
		}

		return $meta_query;
	}

    /**
     * Appends taxonomy queries to an array.
     * @access public
     * @param array $tax_query
     * @return array
     */
    public function get_tax_query( $tax_query = array() ) {
        if ( ! is_array( $tax_query ) )
            $tax_query = array();

        if ( isset($_REQUEST) && !empty($_REQUEST) )
        {
            foreach ( $_REQUEST as $key => $value )
            {
                if ( taxonomy_exists($key) && isset( $_REQUEST[$key] ) && !empty($_REQUEST[$key]) && $this->taxonomy_allowed_for_department( $key ) )
                {
                    $tax_query[] = array(
                        'taxonomy'  => $key,
                        'terms' => ph_clean( (is_array($value)) ? $value : array( $value ) )
                    );
                }
            }
        }

        return array_filter( apply_filters( 'propertyhive_property_query_tax_query', $tax_query, $this ) );
    }

    private function taxonomy_allowed_for_department( $taxonomy )
    {
    	if ( isset( $_REQUEST['department'] ) && $_REQUEST['department'] != '' )
        {
        	$department = ph_clean($_REQUEST['department']);
        }
        else
        {
            $department = get_option( 'propertyhive_primary_department', 'residential-sales' );
        }

        switch ( $department )
        {
        	case 'residential-sales':
        	{
	        	if ( in_array( $taxonomy, array('commercial_property_type', 'commercial_tenure', 'furnished') ) )
	        	{
	        		return false;
	        	}
	        	break;
	        }
	        case 'residential-lettings':
        	{
	        	if ( in_array( $taxonomy, array('commercial_property_type', 'commercial_tenure', 'tenure', 'sale_by', 'price_qualifier') ) )
	        	{
	        		return false;
	        	}
	        	break;
	        }
	        case 'commercial':
        	{
	        	if ( in_array( $taxonomy, array('property_type', 'tenure', 'parking', 'outside_space', 'furnished') ) )
	        	{
	        		return false;
	        	}
	        	break;
	        }
        }


        return true;
    }
    
	/**
	 * Layered Nav Init
	 */
	public function layered_nav_init( ) {

	}

	/**
	 * Layered Nav post filter
	 *
	 * @param array $filtered_posts
	 * @return array
	 */
	public function layered_nav_query( $filtered_posts ) {
		
	}

	/**
	 * Price filter Init
	 */
	public function price_filter_init() {
		
	}

	/**
	 * Price Filter post filter
	 *
	 * @param array $filtered_posts
	 * @return array
	 */
	public function price_filter( $filtered_posts ) {
	    
	}

}

endif;
