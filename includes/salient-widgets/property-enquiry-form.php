<?php
/**
 * Salient Property Enquiry Form Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Salient_Property_Enquiry_Form_Widget {

	public function __construct() 
	{
		add_action('admin_head', array( $this, 'custom_wpbakery_element_icon' ), 999 );
		add_action('vc_before_init', array( $this, 'custom_wpbakery_element' ) );
	}

	public function custom_wpbakery_element_icon()
	{
		echo '<style type="text/css">
			.wpb_salient_property_enquiry_form .vc_element-icon:before,
			a[data-tag="salient_property_enquiry_form"] .vc_element-icon:before {
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
      		"name" => __( 'Enquiry Form', 'propertyhive' ),
      		"base" => "salient_property_enquiry_form",
      		"class" => "",
      		"category" => __( "Property Hive", "propertyhive"),
      		"html_template" => $html_template,
      		"params" => array(
         		array(
  					'type' => 'css_editor',
  					'heading' => __( 'CSS box', 'js_composer' ),
  					'param_name' => 'css',
  					'group' => __( 'Design Options', 'js_composer' ),
  				),
      		)
   		));
	}
	
}

new Salient_Property_Enquiry_Form_Widget();