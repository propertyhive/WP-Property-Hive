<?php
/**
 * Property Description
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Description
 */
class PH_Meta_Box_Property_Description {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        
        echo '<div class="propertyhive_meta_box">';
        
            echo '<div class="options_group">';
                
                echo '<div id="property_descriptions">';
                
                $num_property_descriptions = get_post_meta($post->ID, '_descriptions', TRUE);
                if ($num_property_descriptions == '') { $num_property_descriptions = 0; }
                
                for ($i = 0; $i < $num_property_descriptions; ++$i)
                {
                    $description_name = get_post_meta($post->ID, '_description_name_' . $i, TRUE);
                    
                    echo '<div class="room">';
                
                        echo '<h3>
                            <button type="button" class="remove_description button">' . esc_html(__( 'Remove', 'propertyhive' )) . '</button>
                            <div class="handlediv" title="' . esc_attr(__( 'Click to toggle', 'propertyhive' )) . '"></div>
                            <strong>' . esc_html( ($description_name != '') ? $description_name : '(' . __('untitled', 'propertyhive') . ')' ) . '</strong>
                        </h3>';
                        
                        echo '<div class="room-details">';
                        
                            // Description Title
                            propertyhive_wp_text_input( array( 
                                'id' => '',
                                'name' => '_description_name[]', 
                                'label' => __( 'Description Title', 'propertyhive' ), 
                                'desc_tip' => false,
                                'value' => $description_name,
                                'placeholder' => __( 'e.g. Location, Business Rates, Parking', 'propertyhive' ), 
                                'type' => 'text'
                            ) );

                            // Description
                            propertyhive_wp_textarea_input( array( 
                                'id' => '_description_' . $i,
                                'name' => '_description[]', 
                                'label' => __( 'Description', 'propertyhive' ), 
                                'desc_tip' => false,
                                'class' => '',
                                'value' => html_entity_decode(get_post_meta($post->ID, '_description_' . $i, TRUE)),
                                'custom_attributes' => array(
                                    'style' => 'width:100%;'
                                )
                            ) );
                        
                        echo '</div>';
                    
                    echo '</div>';
                } 
                
                echo '</div>';
                
                echo '<div id="property_description_template" style="display:none">';
                echo '<div class="room">';
                
                    echo '<h3>
                        <button type="button" class="remove_description button">' . esc_html(__( 'Remove', 'propertyhive' )) . '</button>
                        <div class="handlediv" title="' . esc_attr(__( 'Click to toggle', 'propertyhive' )) . '"></div>
                        <strong>Description Title Here</strong>
                    </h3>';
                    
                    echo '<div class="room-details">';
                    
                        // Description Title
                        propertyhive_wp_text_input( array( 
                            'id' => '',
                            'name' => '_description_name[]', 
                            'label' => __( 'Description Title', 'propertyhive' ), 
                            'desc_tip' => false,
                            'value' => '',
                            'placeholder' => __( 'e.g. Location, Business Rates, Parking', 'propertyhive' ), 
                            'type' => 'text'
                        ) );
                        
                        // Description
                        propertyhive_wp_textarea_input( array( 
                            'id' => '_description_id',
                            'name' => '_description[]', 
                            'label' => __( 'Description', 'propertyhive' ), 
                            'desc_tip' => false,
                            'class' => '',
                            'value' => '',
                            'custom_attributes' => array(
                                'style' => 'width:100%;'
                            )
                        ) );
                    
                    echo '</div>';
                
                echo '</div>';
                echo '</div>';
                
                echo '<p class="form-field">
                    <label for="">&nbsp;</label>
                    <a href="#" class="button button-primary add_property_description"><span class="fa fa-plus"></span> Add Description</a>
                </p>';
                
                do_action('propertyhive_property_description_fields');
    	   
            echo '</div>';
        
        echo '</div>';
        
        echo '<script>
            
            var custom_departments = ' . json_encode(ph_get_custom_departments()) . ';
            jQuery(document).ready(function()
            {
                jQuery(\'#property_descriptions\').on(\'keyup\', \'input[name=\\\'_description_name[]\\\']\', function()
                {
                    var description_name = jQuery(this).val();
                    if (description_name == \'\')
                    {
                        description_name = \'(' . esc_js(__('untitled', 'propertyhive')) . ')\';
                    }
                    jQuery(this).parent().parent().parent().children(\'h3\').children(\'strong\').html(description_name);
                });
                
                jQuery(\'.add_property_description\').click(function()
                {
                    var new_description_id = jQuery(\'textarea[name=\\\'_description[]\\\']\').length;

                    var description_template = jQuery(\'#property_description_template\').html();
                    description_template = description_template.replace("_id", "_" + new_description_id);
                    description_template = description_template.replace("_id", "_" + new_description_id);
                    description_template = description_template.replace("_id", "_" + new_description_id);
                    description_template = description_template.replace("_id", "_" + new_description_id);
                    description_template = description_template.replace("_id", "_" + new_description_id);
                    
                    jQuery(\'#property_descriptions\').append(description_template);
                    ';
                    if ( apply_filters('propertyhive_enable_description_editor', false) === true ) { echo 'ph_init_description_editors();'; }
                    echo '
                    return false;
                });
                
                jQuery(document).on(\'click\', \'.remove_description\', function()
                {
                    jQuery(this).parent().parent().fadeOut(\'slow\', function()
                    {
                        jQuery(this).remove();
                    });
                    
                    return false;
                });
            });
            
        </script>';

        echo '
        <script>
            
            jQuery(document).ready(function()
            {
                //showHideDescriptionsMetaBox();
                
                jQuery(\'input[type=\\\'radio\\\'][name=\\\'_department\\\']\').change(function()
                {
                     //showHideDescriptionsMetaBox();
                });
            });
            
            function showHideDescriptionsMetaBox()
            {
                 var selectedDepartment = jQuery(\'input[type=\\\'radio\\\'][name=\\\'_department\\\']:checked\').val();
                 
                 if ( selectedDepartment == \'commercial\' || ( custom_departments[selectedDepartment] && custom_departments[selectedDepartment].based_on == \'commercial\' ) )
                 {
                    jQuery(\'#propertyhive-property-description\').show();
                 }
                 else
                 {
                    jQuery(\'#propertyhive-property-description\').hide();
                 }
            }
            
        </script>';
           
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        // Get existing number of descriptions to see if we need to remove any
        $existing_num_property_descriptions = get_post_meta($post_id, '_descriptions', TRUE);
        if ($existing_num_property_descriptions == '') { $existing_num_property_descriptions = 0; }
        
        $new_num_property_descriptions = count($_POST['_description_name']) - 1; // Minus one because of the template description. Don't want to include this

        if ($new_num_property_descriptions < $existing_num_property_descriptions)
        {
            // There are less now than before
            // Delete the additional ones
            for ($i = ($new_num_property_descriptions - 1); $i < $existing_num_property_descriptions; ++$i)
            {
                delete_post_meta($post_id, '_description_name_' . $i);
                delete_post_meta($post_id, '_description_' . $i);
            }
        }
        
        update_post_meta($post_id, '_descriptions', $new_num_property_descriptions );
        
        for ($i = 0; $i < $new_num_property_descriptions; ++$i)
        {
            update_post_meta($post_id, '_description_name_' . $i, ph_clean($_POST['_description_name'][$i]));

            $allowed_html = array(
                'p' => array(),
                'br' => array(),
                'i' => array(),
                'em' => array(),
                'u' => array(),
                'strong' => array(),
                'b' => array(),
                'a' => array(
                    'href' => array(),
                    'target' => array(),
                    'title' => array()
                ),
            );
            update_post_meta($post_id, '_description_' . $i, wp_kses($_POST['_description'][$i], $allowed_html));
        }
    }

}
