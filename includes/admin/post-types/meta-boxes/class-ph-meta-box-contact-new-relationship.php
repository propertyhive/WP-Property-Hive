<?php
/**
 * New Relationship
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Contact_New_Relationship
 */
class PH_Meta_Box_Contact_New_Relationship {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        propertyhive_wp_radio( array( 
            'id' => '_contact_type_new', 
            'label' => __( 'Contact Type', 'propertyhive' ), 
            'desc_tip' => false,
            'options' => array(
                'applicant' => 'Applicant',
                'thirdparty' => 'Third Party'
            ),
            'value' => isset($_GET['contact_type']) && in_array($_GET['contact_type'], array('applicant', 'thirdparty')) ? $_GET['contact_type'] : 'applicant'
        ) );
        

        do_action('propertyhive_contact_new_relationship_fields');
	    
        echo '</div>';
        
        echo '</div>';
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        update_post_meta( $post_id, '_contact_types', array(ph_clean($_POST['_contact_type_new'])) );

        if ( $_POST['_contact_type_new'] == 'applicant' )
        {
            update_post_meta( $post_id, '_applicant_profiles', 1 );
            update_post_meta( $post_id, '_applicant_profile_0', array('department' => get_option('propertyhive_primary_department', '')) );
        }
        if ( $_POST['_contact_type_new'] == 'thirdparty' )
        {
            update_post_meta( $post_id, '_third_party_categories', array('') );
        }

        do_action( 'propertyhive_save_contact_new_relationship', $post_id );
    }

}
