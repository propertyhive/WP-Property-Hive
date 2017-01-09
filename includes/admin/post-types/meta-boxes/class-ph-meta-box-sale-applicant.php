<?php
/**
 * Sale Applicant Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Sale_Applicant
 */
class PH_Meta_Box_Sale_Applicant {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $applicant_contact_id = get_post_meta( $post->ID, '_applicant_contact_id', true );

        if ( !empty($applicant_contact_id) )
        {
            $contact = new PH_Contact($applicant_contact_id);

            echo '<p class="form-field">
            
                <label>' . __('Name', 'propertyhive') . '</label>
                
                <a href="' . get_edit_post_link($applicant_contact_id, '') . '">' . get_the_title($applicant_contact_id) . '</a>
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . __('Telephone Number', 'propertyhive') . '</label>
                
                ' . $contact->telephone_number . '
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . __('Email Address', 'propertyhive') . '</label>
                
                <a href="mailto:' . $contact->email_address . '">' .  $contact->email_address  . '</a>
                
            </p>';
        }
        else
        {
            echo 'No applicant found';
        }

        do_action('propertyhive_sale_applicant_fields');
	    
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
