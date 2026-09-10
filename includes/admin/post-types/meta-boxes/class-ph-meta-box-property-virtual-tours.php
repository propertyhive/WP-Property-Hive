<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Property Virtual Tours
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Virtual_Tours
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Property_Virtual_Tours; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Property_Virtual_Tours {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
                
            echo '<div id="property_virtual_tours">';
            
                $num_property_virtual_tours = get_post_meta($post->ID, '_virtual_tours', TRUE);
                if ($num_property_virtual_tours == '') { $num_property_virtual_tours = 0; }
                
                for ($i = 0; $i < $num_property_virtual_tours; ++$i)
                {
                    $label = get_post_meta($post->ID, '_virtual_tour_label_' . $i, TRUE);

                    echo '
                    <div>

                        <p class="form-field virtual_tour_field ">
                            <label for="">' . esc_html(__( 'Virtual Tour URL', 'propertyhive' )) . '</label>
                            <input type="text" class="short" name="virtual_tour[]" id="" value="' . esc_attr(get_post_meta($post->ID, '_virtual_tour_' . $i, TRUE)) . '" placeholder="https://"> 
                            <a href="" class="button remove_virtual_tour"><span class="fa fa-trash"></span></a>
                        </p>

                        <p class="form-field virtual_tour_field ">
                            <label for="">' . esc_html(__( 'Virtual Tour Label', 'propertyhive' )) . '</label>
                            <input type="text" class="short" name="virtual_tour_label[]" id="" value="' . esc_attr(( $label != '' ? $label : __( 'Virtual Tour', 'propertyhive' ) )) . '" placeholder="' . esc_attr(__( 'e.g. Virtual Tour', 'propertyhive' )) . '"> 
                        </p>

                        <hr>

                    </div>';
                }
            
            echo '</div>';
        
            echo '<div id="property_virtual_tour_template" style="display:none">';

            echo '
            <div>

                <p class="form-field virtual_tour_field ">
                    <label for="">' . esc_html(__( 'Virtual Tour URL', 'propertyhive' )) . '</label>
                    <input type="text" class="short" name="virtual_tour[]" id="" value="" placeholder="https://"> 
                    <a href="" class="button remove_virtual_tour"><span class="fa fa-trash"></span></a>
                </p>

                <p class="form-field virtual_tour_field ">
                    <label for="">' . esc_html(__( 'Virtual Tour Label', 'propertyhive' )) . '</label>
                    <input type="text" class="short" name="virtual_tour_label[]" id="" value="' . esc_attr(__( 'Virtual Tour', 'propertyhive' )) . '" placeholder="' . esc_attr(__( 'e.g. Virtual Tour', 'propertyhive' )) . '"> 
                </p>

                <hr>

            </div>';
            
            echo '</div>';
        
            echo '            
            <p class="form-field">
                <label for="">&nbsp;</label>
                <a href="" class="button button-primary add_property_virtual_tour"><span class="fa fa-plus"></span> ' . esc_html(__( 'Add Virtual Tour', 'propertyhive' )) . '</a>
            </p>';
        
            do_action('propertyhive_property_virtual_tours_fields');
	   
        echo '</div>';
        
        echo '</div>';
        
        echo '<script>
            
            jQuery(document).ready(function()
            {
                jQuery(\'.add_property_virtual_tour\').click(function()
                {
                    var virtual_tour_template = jQuery(\'#property_virtual_tour_template\').html();
                    
                    jQuery(\'#property_virtual_tours\').append(virtual_tour_template);
                    
                    return false;
                });
                
                jQuery(\'#property_virtual_tours\').on(\'click\', \'.remove_virtual_tour\', function()
                {
                    jQuery(this).parent().parent().fadeOut(\'slow\', function()
                    {
                        jQuery(this).remove();
                    });
                    
                    return false;
                });
            });
            
        </script>';
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
        
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Shape-only check rejects nested values before the separately sanitized text or integer conversion below.
        if ( ! isset( $_POST['virtual_tour'], $_POST['virtual_tour_label'] ) || ! is_array( $_POST['virtual_tour'] ) || ! is_array( $_POST['virtual_tour_label'] ) || count( $_POST['virtual_tour'] ) !== count( $_POST['virtual_tour_label'] ) || count( array_filter( $_POST['virtual_tour'], 'is_string' ) ) !== count( $_POST['virtual_tour'] ) || count( array_filter( $_POST['virtual_tour_label'], 'is_string' ) ) !== count( $_POST['virtual_tour_label'] ) ) {
            return;
        }
        $virtual_tours = array_values( array_map( 'sanitize_url', wp_unslash( $_POST['virtual_tour'] ) ) );
        $virtual_tour_labels = array_values( ph_clean( wp_unslash( $_POST['virtual_tour_label'] ) ) );
        // Get existing number of virtual tours to see if we need to remove any
        $existing_num_property_virtual_tours = get_post_meta($post_id, '_virtual_tours', TRUE);
        if ($existing_num_property_virtual_tours == '') { $existing_num_property_virtual_tours = 0; }
        
        $new_num_property_virtual_tours = max( 0, count($virtual_tours) - 1 ); // Minus one because of the template virtual tour. Don't want to include this
        
        if ($new_num_property_virtual_tours < $existing_num_property_virtual_tours)
        {
            // There are less now than before
            // Delete the additional ones
            for ($i = ($new_num_property_virtual_tours - 1); $i < $existing_num_property_virtual_tours; ++$i)
            {
                delete_post_meta($post_id, '_virtual_tour_' . $i);
                delete_post_meta($post_id, '_virtual_tour_label_' . $i);
            }
        }
        
        update_post_meta($post_id, '_virtual_tours', $new_num_property_virtual_tours );
        
        for ($i = 0; $i < $new_num_property_virtual_tours; ++$i)
        {
            update_post_meta($post_id, '_virtual_tour_' . $i, wp_slash( $virtual_tours[$i] ));
            update_post_meta($post_id, '_virtual_tour_label_' . $i, wp_slash( $virtual_tour_labels[$i] ));
        }
    }
}
