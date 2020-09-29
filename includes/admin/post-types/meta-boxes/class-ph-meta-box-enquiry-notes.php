<?php
/**
 * Enquiry Notes
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Enquiryy_Notes
 */
class PH_Meta_Box_Enquiry_Notes {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        global $propertyhive, $post;

        $section = 'enquiry';
        include( PH()->plugin_path() . '/includes/admin/views/html-display-notes.php' );
    }
}