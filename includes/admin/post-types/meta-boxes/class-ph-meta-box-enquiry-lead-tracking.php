<?php
/**
 * Enquiry Lead Tracking Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Enquiry_Lead_Tracking
 */
class PH_Meta_Box_Enquiry_Lead_Tracking {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid, $post, $current_screen;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group" style="position:relative;">';

        $utm_keys = array(
            'utm_source',
            'utm_medium',
            'utm_term',
            'utm_content',
            'utm_campaign',
            'gclid', 
            'fbclid'
        );
        
        $enquiry_post_id = $post->ID;
        $enquiry_meta = get_metadata( 'post', $post->ID );

        $output_lead_tracking_details = false;

        foreach ($enquiry_meta as $key => $value)
        {
            if ( !in_array( $key, $utm_keys ) )
            {
                continue;
            }


            $value = ( ( isset( $value[0] ) && ! empty( $value[0] )) ? $value[0] : '' );

            if ( empty($value) )
            {
                continue;
            }

            echo '<p class="form-field enquiry_lead_tracking_field">

                    <label>' . esc_html($key). '</label>

                    ' . esc_html($value) . '

                  </p>';

            $output_lead_tracking_details = true;
        }

        if ( !$output_lead_tracking_details )
        {
            // Add documentation link in future
            echo '<p>

                No lead tracking information received.

            </p>';
        }

        do_action('propertyhive_enquiry_lead_tracking_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        

    }

}
