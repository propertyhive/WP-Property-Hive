<?php
require_once(__DIR__ ."/class-ph-shortcode.php");

class PH_Shortcode_Property_Map extends PH_Shortcode{
     public function __construct(){
        parent::__construct("property_map", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){

    }
}