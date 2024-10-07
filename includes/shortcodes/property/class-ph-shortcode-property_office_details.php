<?php
require_once(__DIR__ ."/class-ph-shortcode.php");

class PH_Shortcode_Property_Office_Details extends PH_Shortcode{
     public function __construct(){
        parent::__construct("property_office_details", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){

    }
}