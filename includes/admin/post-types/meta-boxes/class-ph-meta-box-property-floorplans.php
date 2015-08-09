<?php
/**
 * Property Floorplans
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Floorplans
 */
class PH_Meta_Box_Property_Floorplans {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        
        echo '<div class="propertyhive_meta_box">';
        
            echo '<div class="options_group">';
            
                echo '<div class="media_grid" id="property_floorplans_grid"><ul>';
                
                $floorplans = get_post_meta($post->ID, '_floorplans', TRUE);
                $input_value = '';
                if (is_array($floorplans) && !empty($floorplans))
                {
                    $input_value = implode(",", $floorplans);
                    
                    foreach ($floorplans as $floorplan_attachment_id)
                    {
                        echo '<li id="floorplan_' . $floorplan_attachment_id . '">';
                            echo '<div class="hover"><div class="attachment-delete"><a href=""></a></div><div class="attachment-edit"><a href=""></a></div></div>';
                            echo wp_get_attachment_image( $floorplan_attachment_id, 'thumbnail' );
                        echo '</li>';
                    }
                }
                else
                {
                    //echo '<p>' . __( 'No floorplans have been uploaded yet', 'propertyhive' ) . '</p>';
                }
                
                echo '</ul></div>';
                
                echo '<a href="" class="button button-primary ph_upload_floorplan_button">' . __('Add Floorplans', 'propertyhive') . '</a>';
    
                do_action('propertyhive_property_floorplans_fields');
    	       
                echo '<input type="hidden" name="floorplan_attachment_ids" id="floorplan_attachment_ids" value="' . $input_value . '">';
               
            echo '</div>';
        
        echo '</div>';
        
        echo '<script>
              // Uploading files
              var file_frame2;
             
              jQuery(document).ready(function()
              {
                  jQuery( \'#property_floorplans_grid ul\' ).sortable({
                      update : function (event, ui) {
                            var new_order = \'\';
                            jQuery(\'#property_floorplans_grid ul\').find(\'li\').each( function () {
                                if (new_order != \'\')
                                {
                                    new_order += \',\';
                                }
                                new_order = new_order + jQuery(this).attr(\'id\').replace(\'floorplan_\', \'\');
                            });
                            jQuery(\'#floorplan_attachment_ids\').val(new_order);
                      }
                  });
                  jQuery( \'#property_floorplans_grid ul\' ).disableSelection();
                
                  jQuery(\'body\').on(\'click\', \'#property_floorplans_grid .attachment-delete a\', function()
                  {
                      var container = jQuery(this).parent().parent().parent();
                      var floorplan_id = container.attr(\'id\');
                      floorplan_id = floorplan_id.replace(\'floorplan_\', \'\');
                      
                      var attachment_ids = jQuery(\'#floorplan_attachment_ids\').val();
                      // Check it\'s not already in the list
                      attachment_ids = attachment_ids.split(\',\');
                      
                      var new_attachment_ids = \'\';
                      for (var i in attachment_ids)
                      {
                          if (attachment_ids[i] != floorplan_id)
                          {
                              if (new_attachment_ids != \'\')
                              {
                                  new_attachment_ids += \',\';
                              }
                              new_attachment_ids += attachment_ids[i];
                          }
                      }
                      jQuery(\'#floorplan_attachment_ids\').val(new_attachment_ids);
                      
                      container.fadeOut(\'fast\', function()
                      {
                          container.remove();
                      });
                      
                      return false;
                  });
                  
                  jQuery(\'body\').on(\'click\', \'#property_floorplans_grid .attachment-edit a\', function()
                  {
                      
                  });
                  
                  jQuery(\'.ph_upload_floorplan_button\').live(\'click\', function( event ){
                 
                    event.preventDefault();
                 
                    // If the media frame already exists, reopen it.
                    if ( file_frame2 ) {
                      file_frame2.open();
                      return;
                    }
                 
                    // Create the media frame.
                    file_frame2 = wp.media.frames.file_frame2 = wp.media({
                      title: jQuery( this ).data( \'uploader_title\' ),
                      button: {
                        text: jQuery( this ).data( \'uploader_button_text\' ),
                      },
                      multiple: true  // Set to true to allow multiple files to be selected
                    });
                 
                    // When an image is selected, run a callback.
                    file_frame2.on( \'select\', function() {
                        var selection = file_frame2.state().get(\'selection\');
     
                        selection.map( function( attachment ) {
                     
                            attachment = attachment.toJSON();
                     
                            // Do something with attachment.id and/or attachment.url here
                            console.log(attachment.url);
                            
                            // Add selected images to grid
                            add_floorplan_attachment_to_grid(attachment);
                        });
                    });
                 
                    // Finally, open the modal
                    file_frame2.open();
                  });
              });
              
              function add_floorplan_attachment_to_grid(attachment)
              {
                  var attachment_ids = jQuery(\'#floorplan_attachment_ids\').val();
                  // Check it\'s not already in the list
                  attachment_ids = attachment_ids.split(\',\');
                  
                  var ok_to_add = true;
                  for (var i in attachment_ids)
                  {
                      if (attachment.id == attachment_ids[i])
                      {
                          ok_to_add = false;
                      }
                  }
                  
                  if (ok_to_add)
                  {
                      // Append to hidden field
                      var new_attachment_ids = attachment_ids;
                      if (new_attachment_ids != \'\')
                      {
                          new_attachment_ids += \',\';
                      }
                      new_attachment_ids += attachment.id;
                      jQuery(\'#floorplan_attachment_ids\').val(new_attachment_ids);
                      
                      // Add floorplan to media grid
                      var mediaHTML = \'\';
                      mediaHTML += \'<li id="floorplan_\' + attachment.id + \'">\';
                      mediaHTML += \'<div class="hover"><div class="attachment-delete"><a href=""></a></div><div class="attachment-edit"><a href=""></a></div></div>\';
                      mediaHTML += \'<img src="\' + attachment.url + \'" alt=""></li>\';
                      
                      jQuery(\'#property_floorplans_grid ul\').append(mediaHTML);
                  }
              }
        </script>';
        
    }
    
    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        $floorplans = array();
        if (trim($_POST['floorplan_attachment_ids'], ',') != '')
        {
            $floorplans = explode( ",", trim($_POST['floorplan_attachment_ids'], ',') );
        }
        update_post_meta( $post_id, '_floorplans', $floorplans );
    }
}
