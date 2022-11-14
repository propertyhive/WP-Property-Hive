<?php
/**
 * Property Notes
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Pinned_Notes
 */
class PH_Meta_Box_Pinned_Notes {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        global $wpdb, $propertyhive, $post;

       // $section = get_post_type($post->ID);

        //echo '<div class="propertyhive-pinned-notes-container" id="propertyhive_pinned_notes_container">' . __( 'Loading', 'propertyhive' ) . '...</div>';
    }
}