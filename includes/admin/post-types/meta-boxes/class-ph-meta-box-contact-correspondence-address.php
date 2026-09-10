<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Contact Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Contact_Correspondence_Address
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Contact_Correspondence_Address; preserving the existing PH_* class name is required for plugin and extension compatibility.
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
            'id' => '_company_name', 
            'label' => __( 'Company Name', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        ) );

        propertyhive_wp_text_input( array( 
            'id' => '_address_name_number', 
            'label' => __( 'Building Name / Number', 'propertyhive' ), 
            'desc_tip' => false, 
            'placeholder' => __( 'e.g. Thistle Cottage, or Flat 10', 'propertyhive' ), 
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

        // Country dropdown
        $countries = get_option( 'propertyhive_countries', array( 'GB' ) );
        $contact_country = get_post_meta( $thepostid, '_address_country', TRUE );
        if ( $contact_country == '' )
        {
            $contact_country = get_option( 'propertyhive_default_country', 'GB' );
        }
        if ( empty($countries) || count($countries) < 2 )
        {
            propertyhive_wp_hidden_input( array( 
                'id' => '_address_country',
                'value' => $contact_country,
            ) );
        }
        else
        {
            $ph_countries = new PH_Countries();

            $country_options = array();
            foreach ( $countries as $country_code )
            {
                $country = $ph_countries->get_country( $country_code );
                if ( $country !== false )
                {
                    $country_options[$country_code] = $country['name'];
                }
            }
            propertyhive_wp_select( array( 
                'id' => '_address_country', 
                'label' => __( 'Country', 'propertyhive' ), 
                'desc_tip' => false,
                'options' => $country_options,
                'value' => $contact_country,
            ) );
        }

        do_action('propertyhive_contact_correspondence_address_fields');
	    
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
        
        if ( isset( $_POST['_company_name'] ) && is_string( $_POST['_company_name'] ) ) {
            update_post_meta( $post_id, '_company_name', wp_slash( sanitize_text_field( wp_unslash( $_POST['_company_name'] ) ) ) );
        }
        if ( isset( $_POST['_address_name_number'] ) && is_string( $_POST['_address_name_number'] ) ) {
            update_post_meta( $post_id, '_address_name_number', wp_slash( sanitize_text_field( wp_unslash( $_POST['_address_name_number'] ) ) ) );
        }
        if ( isset( $_POST['_address_street'] ) && is_string( $_POST['_address_street'] ) ) {
            update_post_meta( $post_id, '_address_street', wp_slash( sanitize_text_field( wp_unslash( $_POST['_address_street'] ) ) ) );
        }
        if ( isset( $_POST['_address_two'] ) && is_string( $_POST['_address_two'] ) ) {
            update_post_meta( $post_id, '_address_two', wp_slash( sanitize_text_field( wp_unslash( $_POST['_address_two'] ) ) ) );
        }
        if ( isset( $_POST['_address_three'] ) && is_string( $_POST['_address_three'] ) ) {
            update_post_meta( $post_id, '_address_three', wp_slash( sanitize_text_field( wp_unslash( $_POST['_address_three'] ) ) ) );
        }
        if ( isset( $_POST['_address_four'] ) && is_string( $_POST['_address_four'] ) ) {
            update_post_meta( $post_id, '_address_four', wp_slash( sanitize_text_field( wp_unslash( $_POST['_address_four'] ) ) ) );
        }
        if ( isset( $_POST['_address_postcode'] ) && is_string( $_POST['_address_postcode'] ) ) {
            update_post_meta( $post_id, '_address_postcode', wp_slash( sanitize_text_field( wp_unslash( $_POST['_address_postcode'] ) ) ) );
        }
        if ( isset( $_POST['_address_country'] ) && is_string( $_POST['_address_country'] ) ) {
            update_post_meta( $post_id, '_address_country', wp_slash( ph_clean( wp_unslash( $_POST['_address_country'] ) ) ) );
        }

        do_action( 'propertyhive_save_contact_correspondence_address', $post_id );
    }

}
