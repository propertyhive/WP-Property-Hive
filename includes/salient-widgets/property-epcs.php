<?php
/**
 * Salient Property EPCs Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Salient_Property_Epcs_Widget {

	public function __construct() 
	{
		add_action('admin_head', array( $this, 'custom_wpbakery_element_icon' ), 999 );
		add_action('vc_before_init', array( $this, 'custom_wpbakery_element' ) );
	}

	public function custom_wpbakery_element_icon()
	{
		echo '<style type="text/css">
			.wpb_salient_property_epcs .vc_element-icon:before,
			a[data-tag="salient_property_epcs"] .vc_element-icon:before {
			  	font-family: "Dashicons" !important;
			  	content: "\f179";
			}
		</style>';
	}

	public function custom_wpbakery_element()
	{
		$class_name = get_class($this);

		$widget_dir = dirname(__FILE__);

		$html_template = $widget_dir . '/' . str_replace("_", "-", str_replace(array( "salient_", "_widget" ), "", sanitize_title($class_name))) . '-html.php';

		vc_map( array(
      		"name" => __( 'EPCs', 'propertyhive' ),
      		"base" => "salient_property_epcs",
      		"class" => "",
      		"category" => __( "Property Hive", "propertyhive"),
      		"html_template" => $html_template,
      		"params" => array(
      			array(
	          		"type" => "dropdown",
	          		"class" => "",
	          		"heading" => __( 'Show Title', 'propertyhive' ),
	          		"param_name" => "show_title",
	          		"value" => array(
	          			__( 'Yes', 'propertyhive' ) => 'yes',
						__( 'No', 'propertyhive' ) => 'no',
	          		),
	          		'std' => 'yes',
	         	),
      		)
   		));
	}
	
}

new Salient_Property_Epcs_Widget();