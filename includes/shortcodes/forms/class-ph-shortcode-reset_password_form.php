<?php
require_once(__DIR__ ."/class-ph-shortcode.php");

class PH_Shortcode_Reset_Password_Form extends PH_Shortcode{
     public function __construct(){
        parent::__construct("propertyhive_reset_password_form", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){

    }
}