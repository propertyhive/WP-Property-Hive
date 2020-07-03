<?php
/**
 * Tenancy Safety Checks
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Tenancy_Safety_Checks
 */
class PH_Meta_Box_Tenancy_Safety_Checks {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        $management_type = get_post_meta( $thepostid, '_management_type', TRUE );

        if ( $management_type != 'fully_managed' )
        {
            echo '<p>' . __( 'Management type of tenancy is ' . ucwords(str_replace("_", " ", $management_type)), 'propertyhive' ) . '</p>';
        }
        else
        {
            $safety_checks = get_post_meta( $thepostid, '_safety_checks', TRUE );
            
            $safety_check_types = array(
                'Gas',
                'Electricity',
                'Smoke Alarm',
                'Carbon Monoxide',
                'Fire',
                'Buildings Insurance',
                'Contents Insurance',
                'EPC',
                'HMO',
                'PAT',
            );

            $safety_check_types = apply_filters( 'propertyhive_tenancy_safety_check_types', $safety_check_types );

            foreach ( $safety_check_types as $i => $safety_check_type )
            {
                $description = '';
                if ( 
                    isset($safety_checks[$safety_check_type]) &&
                    $safety_checks[$safety_check_type] != ''
                )
                {
                    if ( strtotime($safety_checks[$safety_check_type]) <= time() )
                    {
                        $description = ' <span style="color:#C00; font-weight:700">Expired</span>';
                    }
                    elseif ( strtotime($safety_checks[$safety_check_type]) <= time() + (30 * 24 * 60 * 60) )
                    {
                        $description = ' <span style="color:#ffbf00; font-weight:700">Expiring Soon</span>';
                    }
                }

                $args = array( 
                    'id' => '_safety_check_' . $i, 
                    'name' => '_safety_check[' . $safety_check_type . ']', 
                    'label' => __( $safety_check_type . ' Expiry', 'propertyhive' ), 
                    'value' => isset($safety_checks[$safety_check_type]) ? $safety_checks[$safety_check_type] : '',
                    'desc_tip' => false, 
                    'description' => $description,
                    'class' => 'small',
                    'type' => 'date',
                );
                propertyhive_wp_text_input( $args );
            }
        }

        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        $lowest_date = '';
        $safety_checks = array();

        if ( 
            isset($_POST['_management_type']) && $_POST['_management_type'] == 'fully_managed' && 
            isset($_POST['_safety_check']) && is_array($_POST['_safety_check']) && !empty($_POST['_safety_check']) 
        )
        {
            foreach ( $_POST['_safety_check'] as $safety_check_type => $date )
            {
                if ( ph_clean($date) != '' )
                {
                    $safety_checks[$safety_check_type] = ph_clean($date);

                    if ( $lowest_date == '' || ( $lowest_date != '' && strtotime($lowest_date) > strtotime(ph_clean($date)) ) )
                    {
                        $lowest_date = ph_clean($date);
                    }
                }
            }

            update_post_meta( $post_id, '_nearest_safety_check_expiry', $lowest_date );
            update_post_meta( $post_id, '_safety_checks', $safety_checks );
        }
    }

}
