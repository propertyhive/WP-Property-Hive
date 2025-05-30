<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class PH_Avada {

	private $widgets = array(
		'Property Actions',
		'Property Availability',
		'Property Bathrooms',
		'Property Bedrooms',
		'Property Brochures Link',
		'Property Council Tax Band',
		'Property Deposit',
		'Property Embedded Virtual Tours',
		'Property Enquiry Form',
		'Property Enquiry Form Link',
		'Property EPCs',
		'Property Features',
		'Property Floorplans',
		'Property Full Description',
		'Property Image',
		'Property Images',
		'Property Let Available Date',
		'Property Map',
		'Property Meta',
		'Property Price',
		'Property Reception Rooms',
		'Property Reference Number',
		'Property Street View',
		'Property Summary Description',
		'Property Tenure',
		'Property Type',
	);

	public function __construct()
	{
		add_action('fusion_builder_before_init', array( $this, 'register_widgets' ) );

		$widgets = apply_filters( 'propertyhive_avada_widgets', $this->widgets );

		foreach ( $widgets as $widget )
		{
			$widget_dir = 'avada-widgets';
			$widget_dir = apply_filters( 'propertyhive_avada_widget_directory', dirname(__FILE__) . "/" . $widget_dir, $widget );
			if ( file_exists( $widget_dir . "/" . sanitize_title($widget) . "-shortcode.php" ) )
			{
				require_once( $widget_dir . "/" . sanitize_title($widget) . "-shortcode.php" );
			}
		}
	}

	public function register_widgets()
	{
		$widgets = apply_filters( 'propertyhive_avada_widgets', $this->widgets );

		foreach ( $widgets as $widget )
		{
			$widget_dir = 'avada-widgets';
			$widget_dir = apply_filters( 'propertyhive_avada_widget_directory', dirname(__FILE__) . "/" . $widget_dir, $widget );
			if ( file_exists( $widget_dir . "/" . sanitize_title($widget) . ".php" ) )
			{
				require_once( $widget_dir . "/" . sanitize_title($widget) . ".php" );
			}
		}	
		
	}
}

new PH_Avada();