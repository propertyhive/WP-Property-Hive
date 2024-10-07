<?php
require_once(__DIR__ ."/class-ph-shortcode.php");

class PH_Shortcode_My_Account extends PH_Shortcode{
     public function __construct(){
        parent::__construct("propertyhive_my_account", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){

    }
}