<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Contact_New_Relationship; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Contact_New_Relationship {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only selection default; saving verifies its own nonce and permissions.
        $contact_type = isset( $_GET['contact_type'] ) && is_string( $_GET['contact_type'] ) ? sanitize_key( wp_unslash( $_GET['contact_type'] ) ) : '';

        propertyhive_wp_radio( array( 
            'id' => '_contact_type_new', 
            'label' => __( 'Contact Type', 'propertyhive' ), 
            'desc_tip' => false,
            'options' => array(
                'applicant' => 'Applicant',
                'thirdparty' => 'Third Party'
            ),
            'value' => in_array( $contact_type, array( 'applicant', 'thirdparty' ), true ) ? $contact_type : 'applicant'
        ) );
        

        do_action('propertyhive_contact_new_relationship_fields');
	    
        echo '</div>';
        
        echo '</div>';
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['post_ID'] ) || ! is_scalar( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== (int) $post_id ) {
            return;
        }

        global $wpdb;
        
        $contact_type = isset( $_POST['_contact_type_new'] ) && is_string( $_POST['_contact_type_new'] ) ? sanitize_key( wp_unslash( $_POST['_contact_type_new'] ) ) : '';
        if ( ! in_array( $contact_type, array( 'applicant', 'thirdparty' ), true ) ) {
            return;
        }

        update_post_meta( $post_id, '_contact_types', array( $contact_type ) );

        if ( $contact_type == 'applicant' )
        {
            update_post_meta( $post_id, '_applicant_profiles', 1 );
            update_post_meta( $post_id, '_applicant_profile_0', array('department' => get_option('propertyhive_primary_department', '')) );
        }
        if ( $contact_type == 'thirdparty' )
        {
            update_post_meta( $post_id, '_third_party_categories', array('') );
        }

        do_action( 'propertyhive_save_contact_new_relationship', $post_id );
    }

}
