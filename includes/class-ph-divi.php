<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class PH_Divi {

	public function __construct()
	{
		add_action('et_builder_ready', array( $this, 'register_widgets' ) );
	}

	public function register_widgets()
	{
		if ( class_exists('ET_Builder_Module') ) 
		{
			$widgets = array(
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
				'Property Map Link',
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
			);

			$widgets = apply_filters( 'propertyhive_divi_widgets', $widgets );

			foreach ( $widgets as $widget )
			{
				$widget_dir = 'divi-widgets';
				$widget_dir = apply_filters( 'propertyhive_divi_widget_directory', dirname(__FILE__) . "/" . $widget_dir, $widget );
				if ( file_exists( $widget_dir . "/" . sanitize_title($widget) . ".php" ) )
				{
					require_once( $widget_dir . "/" . sanitize_title($widget) . ".php" );
					$class_name = 'Divi_' . str_replace(" ", "_", $widget) . '_Widget';
					new $class_name();
				}
			}
		}
	}
}

new PH_Divi();