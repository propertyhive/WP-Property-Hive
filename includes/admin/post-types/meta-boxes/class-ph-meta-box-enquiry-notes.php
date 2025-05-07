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

        echo '<div class="propertyhive-notes-container" id="propertyhive_' . esc_attr($section) . '_notes_container">' . esc_html(__( 'Loading', 'propertyhive' )) . '...</div>';
    }
}