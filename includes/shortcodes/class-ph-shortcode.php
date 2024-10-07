<?php 

class PH_Shortcode {

    public function __construct($shortcode, $function) {
        add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function);
    }
}