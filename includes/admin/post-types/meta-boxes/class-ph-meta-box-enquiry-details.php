<?php
/**
 * Enquiry Details
 *
 * @author 		BIOSTALL
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Enquiry_Details
 */
class PH_Meta_Box_Enquiry_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        $ignore_keys = array(
            '_status',
            '_source',
            '_negotiator_id',
            '_office_id',
            '_action'
        );
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $enquiry_meta = get_metadata( 'post', $post->ID );
        
        foreach ($enquiry_meta as $key => $value)
        {
            if ( ! in_array( $key, $ignore_keys ) && substr( $key, 0, 1 ) != '_' )
            {
                if ( $key == '_property_id' )
                {
                    $value = '<a href="' . get_edit_post_link( $value[0] ) . '">' . get_the_title( $value[0] ) . '</a>';
                }
                else
                {
                    $value = ( ( isset( $value[0] ) && ! empty( $value[0] )) ? $value[0] : '-' );
                }
                
                echo '<p class="form-field enquiry_details_field">
        
                        <label for="source">' . $key . '</label>
                      
                        ' . nl2br( $value ) . '
                      
                      </p>';
            }
        }

        do_action('propertyhive_enquiry_details_fields');
	    
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
