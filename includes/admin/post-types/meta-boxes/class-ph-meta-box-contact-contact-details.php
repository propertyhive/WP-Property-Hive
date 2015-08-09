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
 * PH_Meta_Box_Contact_Contact_Details
 */
class PH_Meta_Box_Contact_Contact_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        propertyhive_wp_text_input( array( 
            'id' => '_telephone_number', 
            'label' => __( 'Telephone Number', 'propertyhive' ), 
            'desc_tip' => false,
            'type' => 'text'
        ) );
        
        propertyhive_wp_text_input( array( 
            'id' => '_email_address', 
            'label' => __( 'Email Address', 'propertyhive' ), 
            'desc_tip' => true,
            'description' => __( 'If the contact has multiple email addresses simply separate them using a comma', 'propertyhive' ), 
            'type' => 'text'
        ) );
        
        propertyhive_wp_textarea_input( array( 
            'id' => '_contact_notes', 
            'label' => __( 'Contact Notes', 'propertyhive' ), 
            'desc_tip' => false,
            'placeholder' => __( 'e.g. Works nights so do not call between 11am and 2pm', 'propertyhive' ), 
        ) );
        

        do_action('propertyhive_contact_contact_details_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        update_post_meta( $post_id, '_telephone_number', $_POST['_telephone_number'] );
        update_post_meta( $post_id, '_email_address', $_POST['_email_address'] );
        update_post_meta( $post_id, '_contact_notes', $_POST['_contact_notes'] );
    }

}
