<?php
/**
 * Tenancy Inspections
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Tenancy_Inspections
 */
class PH_Meta_Box_Tenancy_Inspections {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid, $post;
        
        $original_post = $post;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $args = array(
            'post_type' => 'inspection',
            'nopaging'    => true,
            'orderby'   => 'meta_value',
            'order'       => 'DESC',
            'meta_key'  => '_start_date_time',
            'post_status'   => 'publish',
            'meta_query'  => array(
                array(
                    'key' => '_tenancy_id',
                    'value' => (int)$thepostid
                )
            ),
        );

        $inspections_query = new WP_Query( $args );

        if ( $inspections_query->have_posts() )
        {
            echo '<table style="width:100%">
                <thead>
                    <tr>
                        <th style="text-align:left;">' . __( 'Date', 'propertyhive' ) . ' / ' . __( 'Time', 'propertyhive' ) . '</th>
                        <th style="text-align:left;">' . __( 'Type', 'propertyhive' ) . '</th>
                        <th style="text-align:left;">' . __( 'Status', 'propertyhive' ) . '</th>
                        <th style="text-align:left;">' . __( 'Carried Out By', 'propertyhive' ) . '</th>
                    </tr>
                </thead>
                <tbody>';

            while ( $inspections_query->have_posts() )
            {
                $inspections_query->the_post();

                echo '<tr>';
                    echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_the_ID(), '' ) . '">' . date("H:i jS F Y", strtotime(get_post_meta(get_the_ID(), '_start_date_time', TRUE))) . '</a></td>';
                    echo '<td style="text-align:left;">' . ucfirst( get_post_meta(get_the_ID(), '_type', TRUE) ) . '</td>';
                    echo '<td style="text-align:left;">' . ucfirst( get_post_meta(get_the_ID(), '_status', TRUE) ) . '</td>';
                    echo '<td style="text-align:left;">' . ucfirst( get_post_meta(get_the_ID(), '_carried_out_by', TRUE) ) . '</td>';
                echo '</tr>';
            }

            echo '
                </tbody>
            </table>
            <br>';
        }
        else
        {
            echo '<p>' . __( 'No inspections exist for this tenancy', 'propertyhive') . '</p>';
        }
        wp_reset_postdata();

        echo '</div>';
        
        echo '</div>';

        $post = $original_post;
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;


    }

}
