<?php
/**
 * Property Features
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Features
 */
class PH_Meta_Box_Property_Features {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group ui-sortable">';
                
            echo '<div id="property_features">';
            
                $num_property_features = get_post_meta($post->ID, '_features', TRUE);
                if ($num_property_features == '') { $num_property_features = 0; }
                
                for ($i = 0; $i < $num_property_features; ++$i)
                {
                    echo '
                    <p class="form-field feature_field ">
                        <label for="features_1">Feature</label>
                        <input type="text" class="short" name="feature[]" id="" value="' . get_post_meta($post->ID, '_feature_' . $i, TRUE) . '" placeholder=""> 
                        <a href="" class="button remove_feature"><span class="fa fa-trash"></span></a>
                    </p>';
                }
            
            echo '</div>';
        
            echo '<div id="property_feature_template" style="display:none">';

            echo '
            <p class="form-field feature_field ">
                <label for="features_1">Feature</label>
                <input type="text" class="short" name="feature[]" id="" value="" placeholder="' . __( 'e.g. Close to main transport links', 'propertyhive' ) . '"> 
                <a href="" class="button remove_feature"><span class="fa fa-trash"></span></a>
            </p>';
            
            echo '</div>';
        
            echo '            
            <p class="form-field">
                <label for="">&nbsp;</label>
                <a href="" class="button button-primary add_property_feature"><span class="fa fa-plus"></span> Add Feature</a>
            </p>';
        
            do_action('propertyhive_property_features_fields');
	   
        echo '</div>';
        
        echo '</div>';
        
        echo '<script>
            
            jQuery(document).ready(function()
            {
                jQuery(\'.add_property_feature\').click(function()
                {
                    var feature_template = jQuery(\'#property_feature_template\').html();
                    
                    jQuery(\'#property_features\').append(feature_template);
                    
                    return false;
                });
                
                jQuery(\'.remove_feature\').click(function()
                {
                    jQuery(this).parent().fadeOut(\'slow\', function()
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
        global $wpdb;
        
        // Get existing number of features to see if we need to remove any
        $existing_num_property_features = get_post_meta($post_id, '_features', TRUE);
        if ($existing_num_property_features == '') { $existing_num_property_features = 0; }
        
        $new_num_property_features = count($_POST['feature']) - 1; // Minus one because of the template feature. Don't want to include this
        
        if ($new_num_property_features < $existing_num_property_features)
        {
            // There are less now than before
            // Delete the additional ones
            for ($i = ($new_num_property_features - 1); $i < $existing_num_property_features; ++$i)
            {
                delete_post_meta($post_id, '_feature_' . $i);
            }
        }
        
        update_post_meta($post_id, '_features', $new_num_property_features );
        
        for ($i = 0; $i < $new_num_property_features; ++$i)
        {
            update_post_meta($post_id, '_feature_' . $i, $_POST['feature'][$i]);
        }
    }
}
