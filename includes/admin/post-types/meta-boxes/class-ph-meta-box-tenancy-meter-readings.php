<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Tenancy Meter Readings
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Tenancy_Meter_Readings
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Tenancy_Meter_Readings; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Tenancy_Meter_Readings {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        echo '<div class="propertyhive_meta_box">';

        echo '<div class="options_group">';

        $meter_readings = get_post_meta( $thepostid, '_meter_readings', true );

        $meter_reading_types = apply_filters( 'propertyhive_tenancy_meter_reading_types' , array('gas', 'water', 'electricity'));

        $meter_labels = array( 'gas' => __( 'Gas', 'propertyhive' ), 'water' => __( 'Water', 'propertyhive' ), 'electricity' => __( 'Electricity', 'propertyhive' ) );
        $i = 1;
        $num_meter_reading_types = count($meter_reading_types);
        foreach ( $meter_reading_types as $meter_reading_type )
        {
            $args = array(
                'id' => $meter_reading_type,
                'label' => isset( $meter_labels[$meter_reading_type] ) ? $meter_labels[$meter_reading_type] : ucfirst( $meter_reading_type ),
                'desc_tip' => false,
                'type' => 'text',
                'value' => isset($meter_readings[$meter_reading_type]['reading']) ? $meter_readings[$meter_reading_type]['reading'] : '',
            );
            propertyhive_wp_text_input( $args );

            $args = array(
                'id' => $meter_reading_type . '_taken',
                'label' => __( 'Date Taken', 'propertyhive' ),
                'desc_tip' => false,
                'type' => 'date',
                'value' => isset($meter_readings[$meter_reading_type]['date_taken']) ? $meter_readings[$meter_reading_type]['date_taken'] : '',
            );
            propertyhive_wp_text_input( $args );

            if ($i < $num_meter_reading_types) {
                echo '<hr>';
            }
            $i++;
        }

        do_action('propertyhive_tenancy_meter_readings_fields');

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

        if ( 'tenancy' !== get_post_type( $post_id ) ) { return; }
        $meter_readings = array();
        $meter_reading_types = apply_filters( 'propertyhive_tenancy_meter_reading_types' , array('gas', 'water', 'electricity'));

        foreach ( $meter_reading_types as $meter_reading_type )
        {
            $taken_key = $meter_reading_type . '_taken';
            if ( ( isset( $_POST[$meter_reading_type] ) && ! is_string( $_POST[$meter_reading_type] ) ) || ( isset( $_POST[$taken_key] ) && ! is_string( $_POST[$taken_key] ) ) ) {
                return;
            }
            $meter_readings[$meter_reading_type] = array(
                'reading'    => isset( $_POST[$meter_reading_type] ) ? ph_clean( wp_unslash( $_POST[$meter_reading_type] ) ) : '',
                'date_taken' => isset( $_POST[$taken_key] ) ? ph_clean( wp_unslash( $_POST[$taken_key] ) ) : '',
            );
        }

        update_post_meta( $post_id, '_meter_readings', wp_slash( $meter_readings ) );

	    do_action( 'propertyhive_save_tenancy_meter_readings', $post_id );
    }
}
