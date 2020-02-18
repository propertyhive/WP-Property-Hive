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

            $fields = array(
                'name' => array(
                    'label' => __('Name', 'propertyhive'),
                    'value' => '<a href="' . get_edit_post_link($applicant_contact_id, '') . '" data-viewing-applicant-id="' . $applicant_contact_id . '" data-viewing-applicant-name="' . get_the_title($applicant_contact_id) . '">' . get_the_title($applicant_contact_id) . '</a>',
                ),
                'telephone_number' => array(
                    'label' => __('Telephone Number', 'propertyhive'),
                    'value' => $contact->telephone_number,
                ),
                'email_address' => array(
                    'label' => __('Email Address', 'propertyhive'),
                    'value' => '<a href="mailto:' . $contact->email_address . '">' .  $contact->email_address  . '</a>',
                ),
            );

            $fields = apply_filters( 'propertyhive_sale_applicant_fields', $fields, $post->ID, $applicant_contact_id );

            foreach ( $fields as $key => $field )
            {
                echo '<p class="form-field ' . esc_attr($key) . '">
            
                    <label>' . esc_html($field['label']) . '</label>
                    
                    ' . $field['value'] . '
                    
                </p>';
            }
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
