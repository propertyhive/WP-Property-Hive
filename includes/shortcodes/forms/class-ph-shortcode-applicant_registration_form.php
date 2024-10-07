<?php
require_once(__DIR__ ."/class-ph-shortcode.php");

class PH_Shortcode_Applicant_Registration_Form extends PH_Shortcode{
     public function __construct(){
        parent::__construct("applicant_registration_form", __CLASS__ . '::shortcode');
    }

    public static function shortcode($atts){

    }
}