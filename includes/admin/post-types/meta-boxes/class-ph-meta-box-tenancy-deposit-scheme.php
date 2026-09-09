<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Tenancy_Deposit_Scheme; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Tenancy_Deposit_Scheme {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        $deposit_schemes = apply_filters(
            'propertyhive_tenancy_deposit_schemes',
            array(
                'dps'          => 'Deposit Protection Service',
                'mydeposits'   => 'MyDeposits',
                'mydepositsscotland'   => 'MyDeposits Scotland',
                'tds'          => 'Tenancy Deposit Scheme',
                'lps'          => 'Letting Protection Service (Scotland / NI)',
                'safedeposits' => 'Safe Deposits (Scotland)',
            )
        );

        $deposit_schemes['none'] = 'No Scheme Required';

        $args = array(
            'id' => '_deposit_scheme', 
            'label' => __( 'Deposit Scheme', 'propertyhive' ), 
            'desc_tip' => false, 
            'options' => $deposit_schemes,
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

        do_action('propertyhive_tenancy_deposit_scheme_fields');
        
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

        if ( isset( $_POST['_deposit_scheme'] ) && is_string( $_POST['_deposit_scheme'] ) ) {
            update_post_meta( $post_id, '_deposit_scheme', wp_slash( ph_clean( wp_unslash( $_POST['_deposit_scheme'] ) ) ) );
        }
        if ( isset( $_POST['_deposit_registration_date'] ) && is_string( $_POST['_deposit_registration_date'] ) ) {
            update_post_meta( $post_id, '_deposit_registration_date', wp_slash( ph_clean( wp_unslash( $_POST['_deposit_registration_date'] ) ) ) );
        }
        if ( isset( $_POST['_deposit_reference'] ) && is_string( $_POST['_deposit_reference'] ) ) {
            update_post_meta( $post_id, '_deposit_reference', wp_slash( sanitize_text_field( wp_unslash( $_POST['_deposit_reference'] ) ) ) );
        }

	    do_action( 'propertyhive_save_tenancy_deposit_scheme', $post_id );
    }

}
