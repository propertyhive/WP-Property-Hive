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
		add_action('admin_head', array( $this, 'custom_wpbakery_element_icon' ), 999 );
		add_action('vc_before_init', array( $this, 'custom_wpbakery_element' ) );
	}

	public function custom_wpbakery_element_icon()
	{
		echo '<style type="text/css">
			.wpb_salient_property_search_form .vc_element-icon:before,
			a[data-tag="salient_property_search_form"] .vc_element-icon:before {
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

		$description = '';
		if ( class_exists('PH_Template_Assistant') )
		{
			$description = sprintf( __( 'Search forms can be managed from within \'<a href="%s" target="_blank">Property Hive > Settings > Template Assistant > Search Forms</a>\'', 'propertyhive' ), admin_url('/admin.php?page=ph-settings&tab=template-assistant&section=search-forms') );
		}

		$departments = ph_get_departments();

        $department_options = array();

        foreach ( $departments as $key => $value )
        {
            if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
            {
                $department_options[$value] = $key;
            }
        }

		vc_map( array(
      		"name" => __( 'Search Form', 'propertyhive' ),
      		"base" => "salient_property_search_form",
      		"class" => "",
      		"category" => __( "Property Hive", "propertyhive"),
      		"html_template" => $html_template,
      		"params" => array(
         		array(
	          		"type" => "textfield",
	          		"class" => "",
	          		"heading" => __( 'Form ID', 'propertyhive' ),
	          		"param_name" => "form_id",
	          		"value" => 'default',
	          		"description" => $description 
	         	),
	         	array(
	          		"type" => "dropdown",
	          		"class" => "",
	          		"heading" => __( 'Default Department', 'propertyhive' ),
	          		"param_name" => "default_department",
	          		"value" => $department_options
	         	),
      		)
   		));
	}


}

new Salient_Property_Search_Form_Widget();
