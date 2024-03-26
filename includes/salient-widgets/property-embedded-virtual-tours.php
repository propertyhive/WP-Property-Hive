<?php
/**
 * Salient Property Embedded Virtual Tours Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Salient_Property_Embedded_Virtual_Tours_Widget {

	public function __construct() 
	{
		add_action('admin_head', array( $this, 'custom_wpbakery_element_icon' ), 999 );
		add_action('vc_before_init', array( $this, 'custom_wpbakery_element' ) );
	}

	public function custom_wpbakery_element_icon()
	{
		echo '<style type="text/css">
			.wpb_salient_property_embedded_virtual_tours .vc_element-icon:before,
			a[data-tag="salient_property_embedded_virtual_tours"] .vc_element-icon:before {
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
      		"name" => __( 'Embedded Virtual Tours', 'propertyhive' ),
      		"base" => "salient_property_embedded_virtual_tours",
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
	         	array(
	          		"type" => "dropdown",
	          		"class" => "",
	          		"heading" => __( 'Use oEmbed', 'propertyhive' ),
	          		"param_name" => "oembed",
	          		"value" => array(
	          			__( 'Yes', 'propertyhive' ) => 'yes',
						__( 'No', 'propertyhive' ) => 'no',
	          		),
	          		'std' => 'no',
	         	),
      		)
   		));
	}
	
}

new Salient_Property_Embedded_Virtual_Tours_Widget();