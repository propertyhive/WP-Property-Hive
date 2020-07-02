<?php
/**
 * Enquiry Record Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Enquiry_Record_Details
 */
class PH_Meta_Box_Enquiry_Record_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;

        $enquiry_post = $post;

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $args = array( 
            'id' => '_status', 
            'label' => __( 'Status', 'propertyhive' ), 
            'desc_tip' => false,
            'options' => array(
                'open' => __( 'Open', 'propertyhive' ),
                'closed' => __( 'Closed', 'propertyhive' )
            )
        );
        propertyhive_wp_select( $args );
        
        // Negotiator
        $negotiator_id = get_post_meta($post->ID, '_negotiator_id', TRUE);
        
        echo '<p class="form-field negotiator_id_field">
        
            <label for="_negotiator_id">' . __('Negotiator', 'propertyhive') . '</label>';
            
            $args = array(
                'show_option_none' => '-- ' . __( 'Unassigned', 'propertyhive' ) . ' --',
                'name' => '_negotiator_id', 
                'id' => '_negotiator_id', 
                'class' => 'select short',
                'selected' => $negotiator_id,
                'role__not_in' => array('property_hive_contact') 
            );
            wp_dropdown_users($args);
            
        echo '
        </p>';
        
        $office_id = get_post_meta($post->ID, '_office_id', TRUE);
        
        if ($office_id == '')
        {
            // if neg isn't set then default to current users offices
            //$negotiator_id = get_current_user_id();
        }
        
        echo '<p class="form-field office_id_field">
        
            <label for="office_id">' . __('Office', 'propertyhive') . '</label>
            
            <select id="_office_id" name="_office_id" class="select short">';
        
        $args = array(
            'post_type' => 'office',
            'nopaging' => true,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        $office_query = new WP_Query($args);
        
        if ($office_query->have_posts())
        {
            while ($office_query->have_posts())
            {
                $office_query->the_post();
                
                echo '<option value="' . $post->ID . '"';
                if ($post->ID == $office_id)
                {
                    echo ' selected';
                }
                echo '>' . get_the_title() . '</option>';
            }
        }
        
        wp_reset_postdata();

        $post = $enquiry_post;
        
        echo '</select>
            
        </p>';

        $sources = array(
            'office' => __( 'Office', 'propertyhive' ),
            'website' => __( 'Website', 'propertyhive' )
        );

        $sources = apply_filters( 'propertyhive_enquiry_sources', $sources );
        
        asort($sources);

        $args = array( 
            'id' => '_source', 
            'label' => __( 'Source', 'propertyhive' ), 
            'desc_tip' => false,
            'options' => $sources
        );
        propertyhive_wp_select( $args );

        do_action('propertyhive_enquiry_record_details_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        update_post_meta( $post_id, '_status', ph_clean($_POST['_status']) );
        update_post_meta( $post_id, '_negotiator_id', ( ( $_POST['_negotiator_id'] != -1 ) ? (int)$_POST['_negotiator_id'] : '' ) );
        update_post_meta( $post_id, '_office_id', (int)$_POST['_office_id'] );
        update_post_meta( $post_id, '_source', ph_clean($_POST['_source']) );

        do_action( 'propertyhive_save_enquiry_record_details', $post_id );
    }

}
