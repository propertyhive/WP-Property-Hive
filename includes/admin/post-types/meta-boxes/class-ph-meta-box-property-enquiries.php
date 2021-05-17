<?php
/**
 * Property Enquiries
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Enquiries
 */
class PH_Meta_Box_Property_Enquiries {

    /**
     * Output the metabox
     */
    public static function output( $post ) {

        echo '<div id="propertyhive_property_enquiries_meta_box">Loading...</div>';

    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        

    }

}
