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
                                'id' => '_room_description_' . $i,
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
                            'id' => '_room_description_id',
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
            
            var custom_departments = ' . json_encode(ph_get_custom_departments()) . ';
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
                    var new_room_id = jQuery(\'textarea[name=\\\'_room_description[]\\\']\').length;

                    var room_template = jQuery(\'#property_room_template\').html();
                    room_template = room_template.replace("_id", "_" + new_room_id);
                    room_template = room_template.replace("_id", "_" + new_room_id);
                    room_template = room_template.replace("_id", "_" + new_room_id);
                    room_template = room_template.replace("_id", "_" + new_room_id);
                    room_template = room_template.replace("_id", "_" + new_room_id);

                    jQuery(\'#property_rooms\').append(room_template);
                    ';
                    if ( apply_filters('propertyhive_enable_description_editor', false) === true ) { echo 'ph_init_description_editors();'; }
                    echo '
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

        echo '
        <script>
            
            jQuery(document).ready(function()
            {
                //showHideRoomsMetaBox();
                
                jQuery(\'input[type=\\\'radio\\\'][name=\\\'_department\\\']\').change(function()
                {
                     //showHideRoomsMetaBox();
                });
            });
            
            function showHideRoomsMetaBox()
            {
                 var selectedDepartment = jQuery(\'input[type=\\\'radio\\\'][name=\\\'_department\\\']:checked\').val();
                 
                 if ( selectedDepartment == \'residential-sales\' || ( custom_departments[selectedDepartment] && custom_departments[selectedDepartment].based_on == \'residential-sales\' ) || selectedDepartment == \'residential-lettings\' || ( custom_departments[selectedDepartment] && custom_departments[selectedDepartment].based_on == \'residential-lettings\' ) )
                 {
                    jQuery(\'#propertyhive-property-rooms\').show();
                 }
                 else
                 {
                    jQuery(\'#propertyhive-property-rooms\').hide();
                 }
            }
            
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
            update_post_meta($post_id, '_room_name_' . $i, ph_clean($_POST['_room_name'][$i]));
            update_post_meta($post_id, '_room_dimensions_' . $i, ph_clean($_POST['_room_dimensions'][$i]));

            $allowed_html = array(
                'p' => array(),
                'br' => array(),
                'i' => array(),
                'em' => array(),
                'u' => array(),
                'strong' => array(),
                'b' => array(),
            );
            update_post_meta($post_id, '_room_description_' . $i, wp_kses($_POST['_room_description'][$i], $allowed_html));
        }
    }

}
