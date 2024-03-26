<?php
/**
 * Salient Property Image Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Salient_Property_Image_Widget {

	public function __construct() 
	{
		add_action('admin_head', array( $this, 'custom_wpbakery_element_icon' ) );
		add_action('vc_before_init', array( $this, 'custom_wpbakery_element' ) );
	}

	public function custom_wpbakery_element_icon()
	{
		echo '<style type="text/css">
			.wpb_salient_property_image .vc_element-icon:before,
			a[data-tag="salient_property_image"] .vc_element-icon:before {
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
      		"name" => __( 'Property Image', 'propertyhive' ),
      		"base" => "salient_property_image",
      		"class" => "",
      		"category" => __( "Property Hive", "propertyhive"),
      		"html_template" => $html_template,
      		"params" => array(
         		array(
	          		"type" => "textfield",
	          		"class" => "",
	          		"heading" => __( 'Image #', 'propertyhive' ),
	          		"param_name" => "image_number",
	          		"value" => 1,
	         	),
	         	array(
	          		"type" => "dropdown",
	          		"class" => "",
	          		"heading" => __( 'Image Size', 'propertyhive' ),
	          		"param_name" => "image_size",
	          		"value" => array(
	          			__( 'Thumbnail', 'propertyhive' ) => 'thumbnail',
						__( 'Medium', 'propertyhive' ) => 'medium',
						__( 'Large', 'propertyhive' ) => 'large',
						__( 'Full', 'propertyhive' ) => 'full',
	          		),
	          		'std' => 'large',
	         	),
	         	array(
	          		"type" => "dropdown",
	          		"class" => "",
	          		"heading" => __( 'Image Ratio', 'propertyhive' ),
	          		"param_name" => "output_ratio",
	          		"value" => array(
	          			__( 'Uploaded Ratio', 'propertyhive' ) => '',
						__( '3:2', 'propertyhive' ) => '3:2',
						__( '4:3', 'propertyhive' ) => '4:3',
						__( '16:9', 'propertyhive' ) => '16:9',
						__( 'Square', 'propertyhive' ) => '1:1',
	          		),
	          		'std' => '',
	         	),
      		)
   		));
	}


}

new Salient_Property_Image_Widget();