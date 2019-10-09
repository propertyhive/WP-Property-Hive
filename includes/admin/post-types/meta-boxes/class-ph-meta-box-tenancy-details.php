<?php
/**
 * Tenancy Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Tenancy_Details
 */
class PH_Meta_Box_Tenancy_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $args = array( 
            'id' => '_reference_number', 
            'label' => __( 'Reference Number', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        );
        propertyhive_wp_text_input( $args );

        do_action('propertyhive_tenancy_details_fields');
        
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        $status = get_post_meta( $post_id, '_status', TRUE );
        if ( $status == '' )
        {
            update_post_meta( $post_id, '_status', 'preparing' );
        }

        //$amount = preg_replace("/[^0-9]/", '', ph_clean($_POST['_amount']));
        //update_post_meta( $post_id, '_amount', $amount );

        do_action( 'propertyhive_save_tenancy_details', $post_id );
    }

}
