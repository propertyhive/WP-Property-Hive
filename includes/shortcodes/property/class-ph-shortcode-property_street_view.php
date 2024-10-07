<?php


class PH_Shortcode_Property_Street_View extends PH_Shortcode{
     public function __construct(){
        parent::__construct("property_street_view", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){

		global $property;

		$atts = shortcode_atts( array(
			'height'        => '400',
		), $atts, 'property_street_view' );

		ob_start();

		echo get_property_street_view( $atts );

		return ob_get_clean();
    }
}

new PH_Shortcode_Property_Street_View();