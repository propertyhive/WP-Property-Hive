<?php


class PH_Shortcode_Property_Map extends PH_Shortcode{
     public function __construct(){
        parent::__construct("property_map", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){

		global $property;

		$atts = shortcode_atts( array(
			'id'        	=> '',
			'height'        => '400',
			'zoom'          => '14',
			'scrollwheel'   => 'true',
			'init_on_load'  => 'true'
		), $atts, 'property_map' );

		ob_start();

		echo get_property_map( $atts );

		return ob_get_clean();
    }
}

new PH_Shortcode_Property_Map();