<?php
/**
 * Property Record Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Record_Details
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Property_Record_Details; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Property_Record_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;

        $parent_post = false;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only parent defaults; the parent must be an editable property and saving uses the metabox nonce.
        $requested_parent = isset( $_GET['post_parent'] ) && is_scalar( $_GET['post_parent'] ) ? absint( $_GET['post_parent'] ) : 0;
        if ( $requested_parent && get_post_type( $requested_parent ) === 'property' && current_user_can( 'manage_propertyhive' ) && current_user_can( 'edit_post', $requested_parent ) )
        {
            $parent_post = $requested_parent;
        }
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        // Negotiator
        $negotiator_id = get_post_meta($post->ID, '_negotiator_id', TRUE);
        
        if ( $parent_post !== FALSE )
        {
            $negotiator_id = get_post_meta( $parent_post, '_negotiator_id', TRUE );
        }
        if ($negotiator_id == '')
        {
            // if neg isn't set then default to current user
            $negotiator_id = apply_filters('propertyhive_default_property_negotiator_id', get_current_user_id());
        }
        
        echo '<p class="form-field negotiator_field">
        
            <label for="_negotiator_id">' . esc_html(__('Negotiator', 'propertyhive')) . '</label>';
            
            $args = array(
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
        
        if ( $parent_post !== FALSE )
        {
            $office_id = get_post_meta( $parent_post, '_office_id', TRUE );
        }
        if ($office_id == '')
        {
            // Get office set on neg/user
            $office_id = get_user_meta($negotiator_id, 'office_id', TRUE);
        }
        if ($office_id == '')
        {
            // TO DO: Get primary office
        }
        
        echo '<p class="form-field negotiator_field">
        
            <label for="_office_id">' . esc_html(__('Office', 'propertyhive')) . '</label>
            
            <select id="_office_id" name="_office_id" class="select short">';
        
        $original_post = $post;

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
        
        $office_query->reset_postdata();
        
        $post = $original_post;
        
        echo '</select>
            
        </p>';

        do_action('propertyhive_property_record_details_fields');
	    
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
        
        if ( isset( $_POST['_negotiator_id'] ) && is_scalar( $_POST['_negotiator_id'] ) ) {
            update_post_meta( $post_id, '_negotiator_id', (int) $_POST['_negotiator_id'] );
        }
        if ( isset( $_POST['_office_id'] ) && is_scalar( $_POST['_office_id'] ) ) {
            update_post_meta( $post_id, '_office_id', (int) $_POST['_office_id'] );
        }

        do_action('propertyhive_save_property_record_details', $post_id);
    }

}
