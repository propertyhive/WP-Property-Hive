<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class PH_Elementor {

	public function __construct()
	{
		add_action( 'plugins_loaded', array( $this, 'setup_propertyhive_elementor_functionality' ) );
		add_action( 'elementor/query/onmarketpropertyquery', array( $this, 'elementor_query_on_market_only' ) );
		add_filter( 'elementor/query/query_args', array( $this, 'elementor_query_args' ), 10, 2 );
	}

	public function elementor_query_args( $query_vars, $widget )
	{
		// Check to see if this is the right Widget.
		$settings = $widget->get_data( 'settings' );

		if ( isset($settings['post_query_query_id']) && $settings['post_query_query_id'] == 'onmarketpropertyquery' )
		{
			$ordering = PH()->query->get_search_results_ordering_args();

			$query_vars['orderby'] = $ordering['orderby'] . ' post_title';
			$query_vars['order'] = $ordering['order'];
			if ( isset( $ordering['meta_key'] ) )
			{
				$query_vars['meta_key'] = $ordering['meta_key'];
			}
		}

		return $query_vars;
	}

	public function setup_propertyhive_elementor_functionality()
	{
		add_filter( 'elementor_pro/utils/get_public_post_types', array( $this, 'register_public_post_type' ) );

		add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_category' ) );

		add_filter( 'elementor/image_size/get_attachment_image_html', array( $this, 'portfolio_use_property_image_url'), 10, 4 );

		add_action( 'elementor/preview/enqueue_scripts', array( 'PH_Frontend_Scripts', 'load_scripts' ) );
		add_action( 'elementor/preview/enqueue_scripts', array( $this, 'load_elementor_scripts' ) );

		if ( did_action( 'elementor/loaded' ) ) 
		{
		    // Widgets
		    add_action( 'init', array( $this, 'register_widgets' ) );
		}
	}

	public function elementor_query_on_market_only( $query )
	{
		PH()->query->property_query( $query );

		// Set the custom post type 
		$query->set( 'post_type', [ 'property' ] );

		// ensure a department is set as not set by default from property_query
		$meta_query = $query->get('meta_query');

		$new_meta_query = $meta_query;
		$department_found = false;
		foreach ( $meta_query as $key => $value )
		{
			if ( isset($value['key']) && $value['key'] == '_department' )
			{
				$department_found = true;
				break;
			}
		}

		if ( !$department_found )
		{
			$new_meta_query[] = array(
				'key' => '_department',
				'value' => get_option( 'propertyhive_primary_department', 'residential-sales' )
			);
		}

		$query->set( 'meta_query', $new_meta_query );
	}

	public function load_elementor_scripts()
	{
		$suffix               = '';
		$assets_path          = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';

		wp_enqueue_script( 'propertyhive_fancybox' );
		wp_enqueue_style( 'propertyhive_fancybox_css' );

	    wp_enqueue_script( 'flexslider', $assets_path . 'js/flexslider/jquery.flexslider' . $suffix . '.js', array( 'jquery' ), '2.2.2', true );
        wp_enqueue_script( 'flexslider-init', $assets_path . 'js/flexslider/jquery.flexslider.init' . $suffix . '.js', array( 'jquery','flexslider' ), PH_VERSION, true );
        wp_enqueue_style( 'flexslider_css', $assets_path . 'css/flexslider.css' );

        $api_key = get_option('propertyhive_google_maps_api_key');
	    wp_register_script('googlemaps', '//maps.googleapis.com/maps/api/js?' . ( ( $api_key != '' && $api_key !== FALSE ) ? 'key=' . $api_key : '' ), false, '3');
	    wp_enqueue_script('googlemaps');

		wp_enqueue_script( 'propertyhive_elementor', $assets_path . 'js/elementor/elementor.js', array( 'jquery','flexslider' ), PH_VERSION, true );
	}

	public function add_elementor_widget_category( $elements_manager )
	{
		$elements_manager->add_category(
			'property-hive',
			[
				'title' => __( 'Property Hive', 'propertyhive' ),
				'icon' => 'fa fa-home',
			]
		);
	}

	public function register_widgets()
	{
		$widgets = array(
			'Property Search Form',
			'Property Tabbed Details',
			'Property Price',
			'Property Images',
			'Property Image',
			'Property Gallery',
			'Property Features',
			'Property Summary Description',
			'Property Full Description',
			'Property Actions',
			'Property Meta',
			'Property Availability',
			'Property Type',
			'Property Bedrooms',
			'Property Bathrooms',
			'Property Reception Rooms',
			'Property Reference Number',
			'Property Floor Area',
			'Property Tenure',
			'Property Council Tax Band',
			'Property Let Available Date',
			'Property Map',
			'Property Street View',
			'Property Floorplans',
			'Property Floorplans Link',
			'Property EPCs',
			'Property EPCs Link',
			'Property Enquiry Form',
			'Property Enquiry Form Link',
			'Property Brochures Link',
			'Property Embedded Virtual Tours',
			'Property Virtual Tours Link',
			'Property Office Name',
			'Property Office Telephone Number',
			'Property Office Email Address',
			'Property Office Address',
			'Property Negotiator Name',
			'Property Negotiator Telephone Number',
			'Property Negotiator Email Address',
			'Property Negotiator Photo',
			'Back To Search',
			'Property Search Result Count',
			'Property Search Order',
		);

		$widgets = apply_filters( 'propertyhive_elementor_widgets', $widgets );

		foreach ( $widgets as $widget )
		{
			$widget_dir = 'elementor-widgets';
			$widget_dir = apply_filters( 'propertyhive_elementor_widget_directory', dirname(__FILE__) . "/" . $widget_dir, $widget );
			if ( file_exists( $widget_dir . "/" . sanitize_title($widget) . ".php" ) )
			{
				require_once( $widget_dir . "/" . sanitize_title($widget) . ".php" );
				$class_name = '\Elementor_' . str_replace(" ", "_", $widget) . '_Widget';
				\Elementor\Plugin::instance()->widgets_manager->register( new $class_name() );
			}
		}
	}

	public function register_public_post_type( $post_types ) {
		
		if ( isset($post_types['property']) )
		{
			return $post_types;
		}

		$post_types['property'] = __( 'Property', 'propertyhive' );

		return $post_types;
	}

	public function portfolio_use_property_image_url( $html, $settings, $image_size_key, $image_key )
	{
		global $post;

		if ( !isset($settings['posts_post_type']) || ( isset($settings['posts_post_type']) && $settings['posts_post_type'] != 'property' ) )
		{
			return $html;
		}

		// We're viewing a property

		if ( get_option('propertyhive_images_stored_as', '') != 'urls' )
		{
			return $html;
		}

		// Images are stored as URLs

		$images = get_post_meta( $post->ID, '_photo_urls', TRUE );

		if ( empty($images) )
		{
			return $html;
		}

		$image_src = $images[0]['url'];

		$image_class = ! empty( $settings['hover_animation'] ) ? 'elementor-animation-' . $settings['hover_animation'] : '';
		$image_class_html = ! empty( $image_class ) ? ' class="' . $image_class . '"' : '';

		$html = sprintf( '<img src="%s" title="" alt=""%s />', esc_attr( $image_src ), $image_class_html );
		return $html;
	}
}

new PH_Elementor();