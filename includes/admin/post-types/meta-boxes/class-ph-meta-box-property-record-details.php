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
class PH_Meta_Box_Property_Record_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;

        $parent_post = false;
        if ( isset($_GET['post_parent']) && $_GET['post_parent'] != '' )
        {
            $parent_post = $_GET['post_parent'];
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
            $negotiator_id = get_current_user_id();
        }
        
        echo '<p class="form-field negotiator_field">
        
            <label for="_negotiator_id">' . __('Negotiator', 'propertyhive') . '</label>';
            
            $args = array(
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
        
        if ( $parent_post !== FALSE )
        {
            $office_id = get_post_meta( $parent_post, '_office_id', TRUE );
        }
        if ($office_id == '')
        {
            // TO DO: Get primary office
        }
        
        echo '<p class="form-field negotiator_field">
        
            <label for="_office_id">' . __('Office', 'propertyhive') . '</label>
            
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
                
                echo '<option value="' . $post->ID . '"';
                if ($post->ID == $office_id)
                {
                    echo ' selected';
                }
                echo '>' . get_the_title() . '</option>';
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
        global $wpdb;
        
        update_post_meta( $post_id, '_negotiator_id', $_POST['_negotiator_id'] );
        update_post_meta( $post_id, '_office_id', $_POST['_office_id'] );
    }

}
