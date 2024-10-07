<?php


class PH_Shortcode_Property_Static_Map extends PH_Shortcode{
     public function __construct(){
        parent::__construct("property_static_map", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){

		global $property;

		$atts = shortcode_atts( array(
			'id'        	=> '',
			'height'        => '400',
			'zoom'          => '14',
			'link'        	=> 'true',
		), $atts, 'property_static_map' );

		ob_start();

		echo get_property_static_map( $atts );

		return ob_get_clean();
    }
}

new PH_Shortcode_Property_Static_Map();