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
        
        if ( get_option('propertyhive_features_type') == 'checkbox' )
        {
            echo '<div class="propertyhive_meta_box">';

            echo '<div class="options_group">';

            $features = array();
            $args = array(
                'hide_empty' => false,
                'parent' => 0
            );
            $terms = get_terms( 'property_feature', $args );
            
            if ( !empty( $terms ) && !is_wp_error( $terms ) )
            {
                foreach ($terms as $term)
                { 
                    $features[$term->term_id] = $term->name;
                }
            }

            if ( !empty($features) )
            {
                $selected_values = array();
                $term_list = wp_get_post_terms($post->ID, 'property_feature', array("fields" => "ids"));
                if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
                {
                    foreach ( $term_list as $term_id )
                    {
                        $selected_values[] = $term_id;
                    }
                }

                echo '<table width="100%">';

                $i = 0;
                foreach ( $features as $term_id => $name )
                {
                    if ( $i == 3 )
                    {
                        echo '</tr>';
                        $i = 0;
                    }

                    if ( $i == 0 )
                    {
                        echo '</tr>';
                    }

                    echo '<td width="33%"><label style="margin:0; padding:0; float:none; width:100%;"><input type="checkbox" name="feature_ids[]" value="' . $term_id . '"';
                    if ( in_array($term_id, $selected_values) )
                    {
                        echo ' checked';
                    }
                    echo '> ' . $name . '</label></td>';

                    ++$i;
                }

                // Close any <td>s where necessary
                for ( $j = $i; $j < 3; $j++ )
                {
                    echo '<td>&nbsp;</td>';
                }

                echo '</table>';
            }
            else
            {
                // No features
                echo __( 'No features available to choose from. These can be edited in the <a href="' . admin_url('admin.php?page=ph-settings&tab=customfields&section=property-feature') . '" target="_blank">Settings</a> area', 'propertyhive' );
            }

            echo '</div>';
            
            echo '</div>';
        }
        else
        {
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
                
                var obtaining_features = true;
                var existing_features = new Array;
                jQuery(document).ready(function()
                {
                    jQuery(\'.add_property_feature\').click(function()
                    {
                        var feature_template = jQuery(\'#property_feature_template\').html();
                        
                        jQuery(\'#property_features\').append(feature_template);
                        
                        return false;
                    });
                    
                    jQuery(document).on(\'click\', \'.remove_feature\', function()
                    {
                        jQuery(this).parent().fadeOut(\'slow\', function()
                        {
                            jQuery(this).remove();
                        });
                        
                        return false;
                    });

                    jQuery(document).on(\'keypress\', \'.feature_field input\', function(e)
                    {
                        var keyCode = e.keyCode || e.which;
                        if (keyCode === 13) { 
                            e.preventDefault();
                            return false;
                        }

                        if (!obtaining_features && existing_features.length > 0)
                        {
                            var options = {
                                source: existing_features,
                                minLength: 2
                            };

                            jQuery(this).autocomplete(options);
                        }
                    });

                    // get list of previously used features
                    // Do AJAX request
                    var data = {
                        action:         \'propertyhive_load_existing_features\',
                        security:       \'' . wp_create_nonce("load-existing-features") . '\',
                    };
        
                    jQuery.post( \'' . admin_url('admin-ajax.php') . '\', data, function(response) {
                        
                        obtaining_features = false;
                        existing_features = response;
                      
                    });


                });
                
            </script>';
        }
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        if ( get_option('propertyhive_features_type') == 'checkbox' )
        {
            $features = array();
            if ( isset( $_POST['feature_ids'] ) && !empty( $_POST['feature_ids'] ) )
            {
                foreach ( $_POST['feature_ids'] as $feature_id )
                {
                    $features[] = (int)$feature_id;
                }
            }
            if ( !empty($features) )
            {
                wp_set_post_terms( $post_id, $features, 'property_feature' );
            }
            else
            {
                wp_delete_object_term_relationships( $post_id, 'property_feature' );
            }
        }
        else
        {
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
                update_post_meta($post_id, '_feature_' . $i, ph_clean($_POST['feature'][$i]));
            }
        }
    }
}
