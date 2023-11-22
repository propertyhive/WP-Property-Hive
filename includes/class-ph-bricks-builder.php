<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class PH_Bricks_Builder {

	public function __construct()
	{
		add_action( 'init', array( $this, 'register_widgets' ), 11 );
	}

	public function register_widgets()
	{
		if ( !defined('BRICKS_DB_TEMPLATE_SLUG') )
		{
			return;
		}

		$widgets = array(
			'Property Search Form',
			'Property Images',
			'Property Image',
			'Property Gallery',
			'Property Address Name Number',
			'Property Address Street',
			'Property Address Line 2',
			'Property Address Town City',
			'Property Address County',
			'Property Address Postcode',
			'Property Price',
			'Property Price Qualifier',
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
			'Property Deposit',
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
			/*'Property Office Name',
			'Property Office Telephone Number',
			'Property Office Email Address',
			'Property Office Address',
			'Property Negotiator Name',
			'Property Negotiator Telephone Number',
			'Property Negotiator Email Address',
			'Property Negotiator Photo',
			'Back To Search',
			'Property Search Result Count',
			'Property Search Order',*/
		);

		$widgets = apply_filters( 'propertyhive_bricks_builder_widgets', $widgets );

		foreach ( $widgets as $widget )
		{
			$widget_dir = 'bricks-builder-widgets';
			$widget_dir = apply_filters( 'propertyhive_bricks_builder_widget_directory', dirname(__FILE__) . "/" . $widget_dir, $widget );
			if ( file_exists( $widget_dir . "/" . sanitize_title($widget) . ".php" ) )
			{
				\Bricks\Elements::register_element( $widget_dir . "/" . sanitize_title($widget) . ".php" );
			}
		}
	}
}

new PH_Bricks_Builder();