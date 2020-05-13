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
		if ( did_action( 'elementor/loaded' ) ) 
		{
			add_filter( 'elementor_pro/utils/get_public_post_types', array( $this, 'register_public_post_type' ) );

			add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_category' ) );

		    // Widgets
		    add_action( 'init', array( $this, 'register_widgets' ) );
		}
	}

	public function add_elementor_widget_category( $elements_manager )
	{
		$elements_manager->add_category(
			'property-hive',
			[
				'title' => __( 'Property Hive', 'propertyhive' ),
				'icon' => 'fa fa-plug',
			]
		);
	}

	public function register_widgets()
	{
		$widgets = array(
			'Price Formatted',
			'Images',
			'Features',
			'Summary Description',
			'Full Description',
			'Actions',
			'Property Meta',
			'Availability',
			'Property Type',
			'Bedrooms',
			'Bathrooms',
			'Reception Rooms',
			'Map',
			'Street View',
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