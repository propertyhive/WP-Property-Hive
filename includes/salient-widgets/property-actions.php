<?php
/**
 * Salient Property Actions Widget.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Salient_Property_Actions_Widget {

	public function __construct() 
	{
		add_action('admin_head', array( $this, 'custom_wpbakery_element_icon' ), 999 );
		add_action('vc_before_init', array( $this, 'custom_wpbakery_element' ) );
	}

	public function custom_wpbakery_element_icon()
	{
		echo '<style type="text/css">
			.wpb_salient_property_actions .vc_element-icon:before,
			a[data-tag="salient_property_actions"] .vc_element-icon:before {
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
      		"name" => __( 'Actions', 'propertyhive' ),
      		"base" => "salient_property_actions",
      		"class" => "",
      		"category" => __( "Property Hive", "propertyhive"),
      		"html_template" => $html_template,
      		"params" => array(
      			array(
	          		"type" => "dropdown",
	          		"class" => "",
	          		"heading" => __( 'Display As', 'propertyhive' ),
	          		"param_name" => "display",
	          		"value" => array(
	          			__( 'List', 'propertyhive' ) => 'list',
						__( 'Buttons', 'propertyhive' ) => 'buttons',
	          		),
	          		'std' => 'list',
	         	),
      			array(
					'type' => 'font_container',
					'param_name' => 'font_container',
					'value' => 'text_align:left',
					'settings' => array(
						'fields' => array(
							'text_align',
							'font_size',
							'line_height',
							'color',
							'tag_description' => esc_html__( 'Select element tag.', 'js_composer' ),
							'text_align_description' => esc_html__( 'Select text alignment.', 'js_composer' ),
							'font_size_description' => esc_html__( 'Enter font size.', 'js_composer' ),
							'line_height_description' => esc_html__( 'Enter line height.', 'js_composer' ),
							'color_description' => esc_html__( 'Select heading color.', 'js_composer' ),
						),
					),
				),
				array(
	          		"type" => "colorpicker",
	          		"class" => "",
	          		"heading" => __( 'Button Background Colour', 'propertyhive' ),
	          		"param_name" => "button_background_color",
	          		'dependency' => array(
				        "element" => "display",
				        "value" => "buttons"
				    ),
	          		"value" => '#000'
	         	),
	         	array(
	          		"type" => "colorpicker",
	          		"class" => "",
	          		"heading" => __( 'Button Text Colour', 'propertyhive' ),
	          		"param_name" => "button_text_color",
	          		'dependency' => array(
				        "element" => "display",
				        "value" => "buttons"
				    ),
	          		"value" => '#FFF'
	         	),
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

new Salient_Property_Actions_Widget();