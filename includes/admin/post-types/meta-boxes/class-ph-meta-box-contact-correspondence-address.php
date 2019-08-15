<?php
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
        global $wpdb;
        
        update_post_meta( $post_id, '_company_name', ph_clean($_POST['_company_name']) );
        update_post_meta( $post_id, '_address_name_number', ph_clean($_POST['_address_name_number']) );
        update_post_meta( $post_id, '_address_street', ph_clean($_POST['_address_street']) );
        update_post_meta( $post_id, '_address_two', ph_clean($_POST['_address_two']) );
        update_post_meta( $post_id, '_address_three', ph_clean($_POST['_address_three']) );
        update_post_meta( $post_id, '_address_four', ph_clean($_POST['_address_four']) );
        update_post_meta( $post_id, '_address_postcode', ph_clean($_POST['_address_postcode']) );
        update_post_meta( $post_id, '_address_country', ph_clean($_POST['_address_country']) );

        do_action( 'propertyhive_save_contact_correspondence_address', $post_id );
    }

}
