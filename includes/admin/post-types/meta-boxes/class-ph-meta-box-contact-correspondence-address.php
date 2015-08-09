<?php
/**
 * Contact Details
 *
 * @author 		BIOSTALL
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Contact_Correspondence_Address
 */
class PH_Meta_Box_Contact_Correspondence_Address {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        propertyhive_wp_text_input( array( 
            'id' => '_address_name_number', 
            'label' => __( 'Building Name / Number', 'propertyhive' ), 
            'desc_tip' => false, 
            'placeholder' => __( 'e.g. Thistle Cottage, or Flat 10', 'propertyhive' ), 
            //'description' => __( 'Stock quantity. If this is a variable product this value will be used to control stock for all variations, unless you define stock at variation level.', 'propertyhive' ), 
            'type' => 'text'
        ) );
        
        propertyhive_wp_text_input( array( 
            'id' => '_address_street', 
            'label' => __( 'Street', 'propertyhive' ), 
            'desc_tip' => false, 
            'placeholder' => __( 'e.g. High Street', 'propertyhive' ), 
            'type' => 'text'
        ) );
        
        propertyhive_wp_text_input( array( 
            'id' => '_address_two', 
            'label' => __( 'Address Line 2', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        ) );
        
        propertyhive_wp_text_input( array( 
            'id' => '_address_three', 
            'label' => __( 'Town / City', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        ) );
        
        propertyhive_wp_text_input( array( 
            'id' => '_address_four', 
            'label' => __( 'County / State', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        ) );
        
        propertyhive_wp_text_input( array( 
            'id' => '_address_postcode', 
            'label' => __( 'Postcode / Zip Code', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        ) );

        // Country dropdown?

        do_action('propertyhive_contact_correspondence_address_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        update_post_meta( $post_id, '_address_name_number', $_POST['_address_name_number'] );
        update_post_meta( $post_id, '_address_street', $_POST['_address_street'] );
        update_post_meta( $post_id, '_address_two', $_POST['_address_two'] );
        update_post_meta( $post_id, '_address_three', $_POST['_address_three'] );
        update_post_meta( $post_id, '_address_four', $_POST['_address_four'] );
        update_post_meta( $post_id, '_address_postcode', $_POST['_address_postcode'] );
    }

}
