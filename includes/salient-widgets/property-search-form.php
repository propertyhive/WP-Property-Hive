<?php
/**
 * Salient Property Search Form Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Salient_Property_Search_Form_Widget {

	public function __construct() 
	{
		add_action('vc_before_init', array( $this, 'custom_wpbakery_element' ) );
		
	}

	public function custom_wpbakery_element()
	{
		/*$class_name = get_class($this);

		$widget_dir = dirname(__FILE__);

		$html_template = $widget_dir . '/' . str_replace("_", "-", str_replace(array( "salient_", "_widget" ), "", sanitize_title($class_name))) . '-html.php';

		$html_template = $widget_dir . '/' . str_replace("-", "_", basename(__FILE__) );*/

		vc_map( array(
      		"name" => __( 'Search Form', 'propertyhive' ),
      		"base" => "salient_property_search_form",
      		"class" => "",
      		"category" => __( "Content", "propertyhive"),
      		//"html_template" => $html_template,
      		"params" => array(
         		array(
	          		"type" => "textfield",
	          		"class" => "",
	          		"heading" => __( "Text", "propertyhive" ),
	          		"param_name" => "text_content",
	          		"value" => __( "Default param value", "propertyhive" ),
	          		"description" => __( "Description for foo param.", "propertyhive" )
	         	),
      		)
   		));
	}


}

new Salient_Property_Search_Form_Widget();