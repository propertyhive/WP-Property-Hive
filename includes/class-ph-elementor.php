<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class PH_Elementor {

	public function __construct()
	{
		add_action( 'plugins_loaded', array( $this, 'setup_propertyhive_elementor_functionality' ) );
	}

	public function setup_propertyhive_elementor_functionality()
	{
		add_filter( 'elementor_pro/utils/get_public_post_types', array( $this, 'register_public_post_type' ) );

		add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_category' ) );

		add_action( 'elementor/preview/enqueue_scripts', array( 'PH_Frontend_Scripts', 'load_scripts' ) );
		add_action( 'elementor/preview/enqueue_scripts', array( $this, 'load_elementor_scripts' ) );

		if ( did_action( 'elementor/loaded' ) ) 
		{
		    // Widgets
		    add_action( 'init', array( $this, 'register_widgets' ) );
		}
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
			'Property Price',
			'Property Images',
			'Property Image',
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
			'Property Floor Area',
			'Property Map',
			'Property Street View',
			'Property Floorplans',
			'Property EPCs',
			'Property Enquiry Form',
		);

		$widgets = apply_filters( 'propertyhive_elementor_widgets', $widgets );

		foreach ( $widgets as $widget )
		{
			if ( file_exists( dirname(__FILE__) . "/elementor-widgets/" . sanitize_title($widget) . ".php" ) )
			{
				require_once( dirname(__FILE__) . "/elementor-widgets/" . sanitize_title($widget) . ".php" );
				$class_name = '\Elementor_' . str_replace(" ", "_", $widget) . '_Widget';
				\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new $class_name() );
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

}

new PH_Elementor();