<?php
/**
 * Property Rooms
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Rooms
 */
class PH_Meta_Box_Property_Rooms {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        
        echo '<div class="propertyhive_meta_box">';
        
            echo '<div class="options_group">';
                
                echo '<div id="property_rooms">';
                
                $num_property_rooms = get_post_meta($post->ID, '_rooms', TRUE);
                if ($num_property_rooms == '') { $num_property_rooms = 0; }
                
                for ($i = 0; $i < $num_property_rooms; ++$i)
                {
                    $room_name = get_post_meta($post->ID, '_room_name_' . $i, TRUE);
                    
                    echo '<div class="room">';
                
                        echo '<h3>
                            <button type="button" class="remove_room button">' . __( 'Remove', 'propertyhive' ) . '</button>
                            <div class="handlediv" title="' . __( 'Click to toggle', 'propertyhive' ) . '"></div>
                            <strong>' . ( ($room_name != '') ? $room_name : '(' . __('untitled', 'propertyhive') . ')' ) . '</strong>
                        </h3>';
                        
                        echo '<div class="room-details">';
                        
                            // Room Name
                            propertyhive_wp_text_input( array( 
                                'id' => '',
                                'name' => '_room_name[]', 
                                'label' => __( 'Room Name', 'propertyhive' ), 
                                'desc_tip' => false,
                                'value' => $room_name,
                                'placeholder' => __( 'e.g. Bedroom One', 'propertyhive' ), 
                                'type' => 'text'
                            ) );
                            
                            // Room Dimensions
                            propertyhive_wp_text_input( array( 
                                'id' => '',
                                'name' => '_room_dimensions[]', 
                                'label' => __( 'Room Dimensions', 'propertyhive' ), 
                                'desc_tip' => false, 
                                'value' => get_post_meta($post->ID, '_room_dimensions_' . $i, TRUE),
                                'placeholder' => __( 'e.g. 12\' 2" x 5\' 4"', 'propertyhive' ), 
                                'type' => 'text'
                            ) );
                            
                            // Room Description
                            propertyhive_wp_textarea_input( array( 
                                'id' => '',
                                'name' => '_room_description[]', 
                                'label' => __( 'Room Description', 'propertyhive' ), 
                                'desc_tip' => false,
                                'class' => '',
                                'value' => get_post_meta($post->ID, '_room_description_' . $i, TRUE),
                                'custom_attributes' => array(
                                    'style' => 'width:100%;'
                                )
                            ) );
                        
                        echo '</div>';
                    
                    echo '</div>';
                } 
                
                echo '</div>';
                
                echo '<div id="property_room_template" style="display:none">';
                echo '<div class="room">';
                
                    echo '<h3>
                        <button type="button" class="remove_room button">' . __( 'Remove', 'propertyhive' ) . '</button>
                        <div class="handlediv" title="' . __( 'Click to toggle', 'propertyhive' ) . '"></div>
                        <strong>Room Name Here</strong>
                    </h3>';
                    
                    echo '<div class="room-details">';
                    
                        // Room Name
                        propertyhive_wp_text_input( array( 
                            'id' => '',
                            'name' => '_room_name[]', 
                            'label' => __( 'Room Name', 'propertyhive' ), 
                            'desc_tip' => false,
                            'value' => '',
                            'placeholder' => __( 'e.g. Bedroom One', 'propertyhive' ), 
                            'type' => 'text'
                        ) );
                        
                        // Room Dimensions
                        propertyhive_wp_text_input( array( 
                            'id' => '',
                            'name' => '_room_dimensions[]', 
                            'label' => __( 'Room Dimensions', 'propertyhive' ), 
                            'desc_tip' => false, 
                            'value' => '',
                            'placeholder' => __( 'e.g. 12\' 2" x 5\' 4"', 'propertyhive' ), 
                            'type' => 'text'
                        ) );
                        
                        // Room Description
                        propertyhive_wp_textarea_input( array( 
                            'id' => '',
                            'name' => '_room_description[]', 
                            'label' => __( 'Room Description', 'propertyhive' ), 
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
                    <a href="#" class="button button-primary add_property_room"><span class="fa fa-plus"></span> Add Room</a>
                </p>';
                
                do_action('propertyhive_property_rooms_fields');
    	   
            echo '</div>';
        
        echo '</div>';
        
        echo '<script>
            
            jQuery(document).ready(function()
            {
                jQuery(\'#property_rooms\').on(\'keyup\', \'input[name=\\\'_room_name[]\\\']\', function()
                {
                    var room_name = jQuery(this).val();
                    if (room_name == \'\')
                    {
                        room_name = \'(' . __('untitled', 'propertyhive') . ')\';
                    }
                    jQuery(this).parent().parent().parent().children(\'h3\').children(\'strong\').html(room_name);
                });
                
                jQuery(\'.add_property_room\').click(function()
                {
                    var room_template = jQuery(\'#property_room_template\').html();
                    
                    jQuery(\'#property_rooms\').append(room_template);
                    
                    return false;
                });
                
                jQuery(document).on(\'click\', \'.remove_room\', function()
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
        global $wpdb;
        
        // Get existing number of rooms to see if we need to remove any
        $existing_num_property_rooms = get_post_meta($post_id, '_rooms', TRUE);
        if ($existing_num_property_rooms == '') { $existing_num_property_rooms = 0; }
        
        $new_num_property_rooms = count($_POST['_room_name']) - 1; // Minus one because of the template room. Don't want to include this

        if ($new_num_property_rooms < $existing_num_property_rooms)
        {
            // There are less now than before
            // Delete the additional ones
            for ($i = ($new_num_property_rooms - 1); $i < $existing_num_property_rooms; ++$i)
            {
                delete_post_meta($post_id, '_room_name_' . $i);
                delete_post_meta($post_id, '_room_dimensions_' . $i);
                delete_post_meta($post_id, '_room_description_' . $i);
            }
        }
        
        update_post_meta($post_id, '_rooms', $new_num_property_rooms );
        
        for ($i = 0; $i < $new_num_property_rooms; ++$i)
        {
            update_post_meta($post_id, '_room_name_' . $i, $_POST['_room_name'][$i]);
            update_post_meta($post_id, '_room_dimensions_' . $i, $_POST['_room_dimensions'][$i]);
            update_post_meta($post_id, '_room_description_' . $i, $_POST['_room_description'][$i]);
        }
    }

}
