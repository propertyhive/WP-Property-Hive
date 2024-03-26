<?php
/**
 * Salient Property Images Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Salient_Property_Images_Widget {

	public function __construct() 
	{
		add_action('admin_head', array( $this, 'custom_wpbakery_element_icon' ) );
		add_action('vc_before_init', array( $this, 'custom_wpbakery_element' ) );
	}

	public function custom_wpbakery_element_icon()
	{
		echo '<style type="text/css">
			.wpb_salient_property_images .vc_element-icon:before,
			a[data-tag="salient_property_images"] .vc_element-icon:before {
			  	font-family: "Dashicons" !important;
			  	content: "\f161";
			}
		</style>';
	}

	public function custom_wpbakery_element()
	{
		$class_name = get_class($this);

		$widget_dir = dirname(__FILE__);

		$html_template = $widget_dir . '/' . str_replace("_", "-", str_replace(array( "salient_", "_widget" ), "", sanitize_title($class_name))) . '-html.php';

		vc_map( array(
      		"name" => __( 'Property Images', 'propertyhive' ),
      		"base" => "salient_property_images",
      		"class" => "",
      		"category" => __( "Property Hive", "propertyhive"),
      		"html_template" => $html_template,
      		"params" => array(
	         	array(
	          		"type" => "dropdown",
	          		"class" => "",
	          		"heading" => __( 'Hide Thumbnails', 'propertyhive' ),
	          		"param_name" => "hide_thumbnails",
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

new Salient_Property_Images_Widget();