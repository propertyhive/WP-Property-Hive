<?php
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

        $i = 1;
        $num_meter_reading_types = count($meter_reading_types);
        foreach ( $meter_reading_types as $meter_reading_type )
        {
            $args = array(
                'id' => $meter_reading_type,
                'label' => __( ucfirst( $meter_reading_type ), 'propertyhive' ),
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
        global $wpdb;

        $meter_readings = array();
        $meter_reading_types = apply_filters( 'propertyhive_tenancy_meter_reading_types' , array('gas', 'water', 'electricity'));

        foreach ( $meter_reading_types as $meter_reading_type )
        {
            $meter_readings[$meter_reading_type] = array(
                'reading'    => ph_clean($_POST[$meter_reading_type]),
                'date_taken' => ph_clean($_POST[$meter_reading_type . '_taken']),
            );
        }

        update_post_meta( $post_id, '_meter_readings', $meter_readings );

	    do_action( 'propertyhive_save_tenancy_meter_readings', $post_id );
    }
}
