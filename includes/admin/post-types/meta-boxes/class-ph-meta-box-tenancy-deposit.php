<?php
/**
 * Tenancy Deposit
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Tenancy_Deposit
 */
class PH_Meta_Box_Tenancy_Deposit {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $args = array( 
            'id' => '_deposit', 
            'label' => __( 'Deposit Amount', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'number'
        );
        propertyhive_wp_text_input( $args );

        $args = array( 
            'id' => '_deposit_taken_by', 
            'label' => __( 'Deposit Taken By', 'propertyhive' ), 
            'desc_tip' => false,
            'options' => array(
                'landlord' => 'Landlord',
                'agency' => 'Our Agency',
            ),
        );
        propertyhive_wp_select( $args );

        $args = array( 
            'id' => '_deposit_held_by', 
            'label' => __( 'Deposit Held By', 'propertyhive' ), 
            'desc_tip' => false, 
            'options' => array(
                'landlord' => 'Landlord',
                'agency' => 'Our Agency',
                'tdp' => 'Tenancy Deposit Protection (TDP) Scheme',
            ),
        );
        propertyhive_wp_select( $args );

        $args = array( 
            'id' => '_deposit_scheme', 
            'label' => __( 'Deposit Scheme', 'propertyhive' ), 
            'desc_tip' => false, 
            'options' => array(
                'dps' => 'Deposit Protection Service',
                'mydeposits' => 'MyDeposits',
                'tds' => 'Tenancy Deposit Scheme',
                'lps' => 'Letting Protection Service (Scotland / NI)',
                'safedeposits' => 'Safe Deposits (Scotland)',
                'none' => 'No Scheme Required',
            ),
        );
        propertyhive_wp_select( $args );

        $args = array( 
            'id' => '_deposit_received_date', 
            'label' => __( 'Date Received', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'date',
        );
        propertyhive_wp_text_input( $args );

        $args = array( 
            'id' => '_deposit_registration_date', 
            'label' => __( 'Date Registered', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'date',
        );
        propertyhive_wp_text_input( $args );

        $args = array( 
            'id' => '_deposit_reference', 
            'label' => __( 'Deposit Reference', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text',
        );
        propertyhive_wp_text_input( $args );

        do_action('propertyhive_tenancy_deposit_fields');
        
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        $amount = preg_replace("/[^0-9]/", '', ph_clean($_POST['_deposit']));
        update_post_meta( $post_id, '_deposit', $amount );

        update_post_meta( $post_id, '_deposit_taken_by', ph_clean($_POST['_deposit_taken_by']) );
        update_post_meta( $post_id, '_deposit_held_by', ph_clean($_POST['_deposit_held_by']) );
        update_post_meta( $post_id, '_deposit_scheme', ph_clean($_POST['_deposit_scheme']) );
        update_post_meta( $post_id, '_deposit_received_date', ph_clean($_POST['_deposit_received_date']) );
        update_post_meta( $post_id, '_deposit_registration_date', ph_clean($_POST['_deposit_registration_date']) );
        update_post_meta( $post_id, '_deposit_reference', ph_clean($_POST['_deposit_reference']) );

        do_action( 'propertyhive_save_tenancy_deposit', $post_id );
    }

}
