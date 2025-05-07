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
        
        $applicant_contact_ids = get_post_meta($post->ID, '_applicant_contact_id');

        if ( $applicant_contact_ids == '' )
        {
            $applicant_contact_ids = array();
        }
        if ( !is_array($applicant_contact_ids) && $applicant_contact_ids != '' && $applicant_contact_ids != 0 )
        {
            $applicant_contact_ids = array($applicant_contact_ids);
        }

        if ( !empty($applicant_contact_ids) )
        {
            $i = 0;
            foreach ( $applicant_contact_ids as $applicant_contact_id )
            {
                $contact = new PH_Contact($applicant_contact_id);

                $fields = array(
                    'name' => array(
                        'label' => __('Name', 'propertyhive'),
                        'value' => '<a href="' . esc_url(get_edit_post_link($applicant_contact_id, '')) . '" data-sale-applicant-id="' . esc_attr($applicant_contact_id) . '" data-sale-applicant-name="' . esc_attr(get_the_title($applicant_contact_id)) . '">' . esc_html(get_the_title($applicant_contact_id)) . '</a>',
                    ),
                    'telephone_number' => array(
                        'label' => __('Telephone Number', 'propertyhive'),
                        'value' => esc_html($contact->telephone_number),
                    ),
                    'email_address' => array(
                        'label' => __('Email Address', 'propertyhive'),
                        'value' => '<a href="mailto:' . esc_attr($contact->email_address) . '">' . esc_html($contact->email_address) . '</a>',
                    ),
                );

                $fields = apply_filters( 'propertyhive_sale_applicant_fields', $fields, $post->ID, $applicant_contact_id );

                $div_style = $i > 0 ? 'style="border-top:1px solid #ddd"' : '';
                echo "<div " . $div_style . ">";
                foreach ( $fields as $key => $field )
                {
                    echo '<p class="form-field ' . esc_attr($key) . '" >

                        <label>' . esc_html($field['label']) . '</label>

                        ' . $field['value'] . '

                    </p>';
                }
                echo "</div>";
                ++$i;
            }
        }
        else
        {
            echo 'No applicant found';
        }
	    
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
