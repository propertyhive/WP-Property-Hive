<?php
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
        
            <label for="_management_fee">' . __('Management Fee', 'propertyhive') . '</label>';

		echo '<input type="text" class="" name="_management_fee" id="_management_fee" value="' . get_post_meta( $post->ID, '_management_fee', true ) . '" placeholder="" style="width:70px">
            
            <select id="_management_fee_units" name="_management_fee_units" class="select" style="width:auto">
                <option value=""' . ( ($management_fee_units == 'percentage' || $management_fee_units == '') ? ' selected' : '') . '>' . __('%', 'propertyhive') . '</option>
                <option value="percentage"' . ( ($management_fee_units == 'percentage' || $management_fee_units == '') ? ' selected' : '') . '>' . __('%', 'propertyhive') . '</option>
                <option value="fixed"' . ( $management_fee_units == 'fixed' ? ' selected' : '') . '>' . __('Fixed', 'propertyhive') . '</option>
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
        global $wpdb;

	    update_post_meta( $post_id, '_management_type', ph_clean($_POST['_management_type']) );
	    update_post_meta( $post_id, '_management_fee', ph_clean($_POST['_management_fee']) );
	    update_post_meta( $post_id, '_management_fee_units', ph_clean($_POST['_management_fee_units']) );

	    do_action( 'propertyhive_save_tenancy_management_details', $post_id );
    }

}
