<?php
require_once(__DIR__ ."/class-ph-shortcode.php");

class PH_Shortcode_Login_Form extends PH_Shortcode{
     public function __construct(){
        parent::__construct("propertyhive_login_form", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){

    }
}