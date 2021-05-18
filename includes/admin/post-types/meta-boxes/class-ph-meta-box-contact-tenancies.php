<?php
/**
 * Contact Tenancies
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Contact_Tenancies
 */
class PH_Meta_Box_Contact_Tenancies {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        
        $post_id = $post->ID;

		echo '<div id="propertyhive_contact_tenancies_grid">Loading...</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
    }

}
