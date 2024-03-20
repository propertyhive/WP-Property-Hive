<?php
/**
 * Salient Property Address Name Number Widget.
 *
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Salient_Property_Address_Name_Number_Widget {

    public function __construct() {
        add_action('admin_head', array($this, 'custom_wpbakery_element_icon'), 999);
        add_action('vc_before_init', array($this, 'custom_wpbakery_element'));
    }

    public function custom_wpbakery_element_icon() {
        echo '<style type="text/css">
            .wpb_salient_property_address_name_number_icon {
                // Custom icon CSS here
            }
        </style>';
    }

    public function custom_wpbakery_element() {
        // Registration of the WPBakery element with specific parameters for "Address Name Number"
        // You would adjust this section based on your specific implementation needs
    }
}

// Initialize the widget
new Salient_Property_Address_Name_Number_Widget();
