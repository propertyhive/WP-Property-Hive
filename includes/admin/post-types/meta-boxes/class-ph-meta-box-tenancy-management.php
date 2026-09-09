<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Tenancy Management
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Tenancy_Safety_Checks
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Tenancy_Management; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Tenancy_Management {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

		$args = array(
			'id' => '_management_type',
			'name' => '_management_type',
			'label' => __( 'Management Type', 'propertyhive' ),
			'desc_tip' => false,
			'options' => apply_filters( 'propertyhive_tenancy_management_types', array(
				'let_only' => 'Let Only',
				'fully_managed' => 'Fully Managed'
			) ),
		);
		propertyhive_wp_select( $args );

		$management_fee_units = get_post_meta( $post->ID, '_management_fee_units', true );

		echo '<p class="form-field management-fee-details"' . ( get_post_meta( $post->ID, '_management_type', true ) != 'fully_managed' ? ' style="display:none;"' : '' ) . '>
        
            <label for="_management_fee">' . esc_html(__('Management Fee', 'propertyhive')) . '</label>';

		echo '<input type="text" class="" name="_management_fee" id="_management_fee" value="' . esc_attr(get_post_meta( $post->ID, '_management_fee', true )) . '" placeholder="" style="width:70px">
            
            <select id="_management_fee_units" name="_management_fee_units" class="select" style="width:auto">
                <option value=""' . ( ($management_fee_units == 'percentage' || $management_fee_units == '') ? ' selected' : '') . '>' . esc_html(__('%', 'propertyhive')) . '</option>
                <option value="percentage"' . ( ($management_fee_units == 'percentage' || $management_fee_units == '') ? ' selected' : '') . '>' . esc_html(__('%', 'propertyhive')) . '</option>
                <option value="fixed"' . ( $management_fee_units == 'fixed' ? ' selected' : '') . '>' . esc_html(__('Fixed', 'propertyhive')) . '</option>
            </select>
            
        </p>';

        do_action('propertyhive_tenancy_management_details_fields');

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

	    if ( isset( $_POST['_management_type'] ) && is_string( $_POST['_management_type'] ) ) {
	        update_post_meta( $post_id, '_management_type', wp_slash( ph_clean( wp_unslash( $_POST['_management_type'] ) ) ) );
	    }
	    if ( isset( $_POST['_management_fee'] ) && is_string( $_POST['_management_fee'] ) ) {
	        update_post_meta( $post_id, '_management_fee', wp_slash( ph_clean( wp_unslash( $_POST['_management_fee'] ) ) ) );
	    }
	    if ( isset( $_POST['_management_fee_units'] ) && is_string( $_POST['_management_fee_units'] ) ) {
	        update_post_meta( $post_id, '_management_fee_units', wp_slash( ph_clean( wp_unslash( $_POST['_management_fee_units'] ) ) ) );
	    }

	    do_action( 'propertyhive_save_tenancy_management_details', $post_id );
    }

}
