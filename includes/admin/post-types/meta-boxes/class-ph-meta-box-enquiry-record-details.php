<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Enquiry_Record_Details; preserving the existing PH_* class name is required for plugin and extension compatibility.
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
        
            <label for="_negotiator_id">' . esc_html(__('Negotiator', 'propertyhive')) . '</label>';
            
            $args = array(
                'show_option_none' => '-- ' . __( 'Unassigned', 'propertyhive' ) . ' --',
                'name' => '_negotiator_id', 
                'id' => '_negotiator_id', 
                'class' => 'select short',
                'selected' => $negotiator_id,
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy Property Negotiator compatibility filter; existing role filters depend on this exact public hook name.
                'role__not_in' => apply_filters( 'property_negotiator_exclude_roles', array('property_hive_contact', 'subscriber') )
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
        
            <label for="office_id">' . esc_html(__('Office', 'propertyhive')) . '</label>
            
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
                
                echo '<option value="' . esc_attr($post->ID) . '"';
                if ($post->ID == $office_id)
                {
                    echo ' selected';
                }
                echo '>' . esc_html(get_the_title()) . '</option>';
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
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['post_ID'] ) || ! is_scalar( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== (int) $post_id ) {
            return;
        }

        global $wpdb;

        if ( isset( $_POST['_status'] ) && is_string( $_POST['_status'] ) ) {
            update_post_meta( $post_id, '_status', ph_clean( wp_unslash( $_POST['_status'] ) ) );
        }
        if ( isset( $_POST['_negotiator_id'] ) && is_scalar( $_POST['_negotiator_id'] ) ) {
            update_post_meta( $post_id, '_negotiator_id', ( ( $_POST['_negotiator_id'] != -1 ) ? (int) $_POST['_negotiator_id'] : '' ) );
        }
        if ( isset( $_POST['_office_id'] ) && is_scalar( $_POST['_office_id'] ) ) {
            update_post_meta( $post_id, '_office_id', (int) $_POST['_office_id'] );
        }
        if ( isset( $_POST['_source'] ) && is_string( $_POST['_source'] ) ) {
            update_post_meta( $post_id, '_source', ph_clean( wp_unslash( $_POST['_source'] ) ) );
        }

        do_action( 'propertyhive_save_enquiry_record_details', $post_id );
    }

}
