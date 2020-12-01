<?php
/**
 * Tenancy Deposit Scheme
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Tenancy_Deposit_Scheme
 */
class PH_Meta_Box_Tenancy_Deposit_Scheme {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        $args = array(
            'id' => '_deposit_scheme', 
            'label' => __( 'Deposit Scheme', 'propertyhive' ), 
            'desc_tip' => false, 
            'options' => array(
            	// TODO: User managed list
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
//die('<pre>' . print_r($_POST, true));
        update_post_meta( $post_id, '_deposit_scheme', ph_clean($_POST['_deposit_scheme']) );
        update_post_meta( $post_id, '_deposit_registration_date', ph_clean($_POST['_deposit_registration_date']) );
        update_post_meta( $post_id, '_deposit_reference', ph_clean($_POST['_deposit_reference']) );

	    do_action( 'propertyhive_save_tenancy_deposit_scheme', $post_id );
    }

}
