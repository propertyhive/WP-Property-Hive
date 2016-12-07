<?php
/**
 * Property Photos
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Photos
 */
class PH_Meta_Box_Property_Photos {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        
        echo '<div class="propertyhive_meta_box">';
        
            echo '<div class="options_group">';
            
                echo '<div class="media_grid" id="property_photos_grid"><ul>';
                
                $photos = get_post_meta($post->ID, '_photos', TRUE);
                $input_value = '';
                if (is_array($photos) && !empty($photos))
                {                    
                    $input_value = implode(",", $photos);
                    
                    foreach ($photos as $photo_attachment_id)
                    {
                        echo '<li id="photo_' . $photo_attachment_id . '">';
                            echo '<div class="hover"><div class="attachment-delete"><a href=""></a></div><div class="attachment-edit"><a href=""></a></div></div>';
                            echo wp_get_attachment_image( $photo_attachment_id, 'thumbnail' );
                        echo '</li>';
                    }
                }
                else
                {
                    //echo '<p>' . __( 'No photos have been uploaded yet', 'propertyhive' ) . '</p>';
                }
                
                echo '</ul></div>';
                
                echo '<a href="" class="button button-primary ph_upload_photo_button">' . __('Add Photos', 'propertyhive') . '</a>';
    
                do_action('propertyhive_property_photos_fields');
    	       
                echo '<input type="hidden" name="photo_attachment_ids" id="photo_attachment_ids" value="' . $input_value . '">';
               
            echo '</div>';
        
        echo '</div>';
        
        echo '<script>
              // Uploading files
              var file_frame;
              
              //var sortable_options = 
             
              jQuery(document).ready(function()
              {
                  jQuery( \'#property_photos_grid ul\' ).sortable({
                      update : function (event, ui) {
                            var new_order = \'\';
                            jQuery(\'#property_photos_grid ul\').find(\'li\').each( function () {
                                if (new_order != \'\')
                                {
                                    new_order += \',\';
                                }
                                new_order = new_order + jQuery(this).attr(\'id\').replace(\'photo_\', \'\');
                            });
                            jQuery(\'#photo_attachment_ids\').val(new_order);
                      }
                  });
                  jQuery( \'#property_photos_grid ul\' ).disableSelection();
                  
                  jQuery(\'body\').on(\'click\', \'#property_photos_grid .attachment-delete a\', function()
                  {
                      var container = jQuery(this).parent().parent().parent();
                      var photo_id = container.attr(\'id\');
                      photo_id = photo_id.replace(\'photo_\', \'\');
                      
                      var attachment_ids = jQuery(\'#photo_attachment_ids\').val();
                      attachment_ids = attachment_ids.split(\',\');
                      
                      var new_attachment_ids = \'\';
                      for (var i in attachment_ids)
                      {
                          if (attachment_ids[i] != photo_id)
                          {
                              if (new_attachment_ids != \'\')
                              {
                                  new_attachment_ids += \',\';
                              }
                              new_attachment_ids += attachment_ids[i];
                          }
                      }
                      jQuery(\'#photo_attachment_ids\').val(new_attachment_ids);
                      
                      container.fadeOut(\'fast\', function()
                      {
                          container.remove();
                      });
                      
                      return false;
                  });
                  
                  jQuery(\'body\').on(\'click\', \'#property_photos_grid .attachment-edit a\', function()
                  {
                      event.preventDefault();

                      var container = jQuery(this).parent().parent().parent();
                      var photo_id = container.attr(\'id\');
                      photo_id = photo_id.replace(\'photo_\', \'\');
                      
                      // Create the media frame.
                    file_frame = wp.media.frames.file_frame = wp.media({
                      title: jQuery( this ).data( \'uploader_title\' ),
                      button: {
                        text: \'Update Image\',
                      },
                      multiple: false
                    });

                    // open
                    file_frame.on(\'open\',function() {
                      
                      var selection = file_frame.state().get(\'selection\');
                      var ids = photo_id;
                      if (ids) {
                          idsArray = ids.split(',');
                          idsArray.forEach(function(id) {
                              attachment = wp.media.attachment(id);
                              attachment.fetch();
                              selection.add( attachment ? [ attachment ] : [] );
                          });
                      }
                          
                    });

                    // When an image is selected, run a callback.
                    file_frame.on( \'select\', function() {
                        var selection = file_frame.state().get(\'selection\');
     
                        selection.map( function( attachment ) {

                            attachment = attachment.toJSON();

                            if (attachment.id != photo_id)
                            {
                              // Replace photo
                              var attachment_ids = jQuery(\'#photo_attachment_ids\').val();
                              attachment_ids = attachment_ids.split(\',\');
                              
                              for (var i in attachment_ids)
                              {
                                  if (attachment_ids[i] == photo_id)
                                  {
                                      attachment_ids[i] = attachment.id;
                                  }
                              }
                              jQuery(\'#photo_attachment_ids\').val(attachment_ids);
                              
                              // Add photo to media grid
                              var mediaHTML = \'\';
                              mediaHTML += \'<li id="photo_\' + attachment.id + \'">\';
                              mediaHTML += \'<div class="hover"><div class="attachment-delete"><a href=""></a></div><div class="attachment-edit"><a href=""></a></div></div>\';
                              mediaHTML += \'<img src="\' + attachment.url + \'" alt=""></li>\';
                              
                              jQuery(\'#property_photos_grid ul li#photo_\' + photo_id).after(mediaHTML);
                              jQuery(\'#property_photos_grid ul li#photo_\' + photo_id).remove();

                            }
                        });
                    });

                    // Finally, open the modal
                    file_frame.open();
                  });
                  
                  jQuery(\'.ph_upload_photo_button\').live(\'click\', function( event ){
                 
                    event.preventDefault();
                 
                    // If the media frame already exists, reopen it.
                    if ( file_frame ) {
                      file_frame.open();
                      return;
                    }
                 
                    // Create the media frame.
                    file_frame = wp.media.frames.file_frame = wp.media({
                      title: jQuery( this ).data( \'uploader_title\' ),
                      button: {
                        text: jQuery( this ).data( \'uploader_button_text\' ),
                      },
                      multiple: true  // Set to true to allow multiple files to be selected
                    });
                 
                    // When an image is selected, run a callback.
                    file_frame.on( \'select\', function() {
                        var selection = file_frame.state().get(\'selection\');
     
                        selection.map( function( attachment ) {
                     
                            attachment = attachment.toJSON();
                     
                            // Add selected images to grid
                            add_photo_attachment_to_grid(attachment);
                        });
                    });
                 
                    // Finally, open the modal
                    file_frame.open();
                  });
              });
              
              function add_photo_attachment_to_grid(attachment)
              {
                  var attachment_ids = jQuery(\'#photo_attachment_ids\').val();
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
                      jQuery(\'#photo_attachment_ids\').val(new_attachment_ids);
                      
                      // Add photo to media grid
                      var mediaHTML = \'\';
                      mediaHTML += \'<li id="photo_\' + attachment.id + \'">\';
                      mediaHTML += \'<div class="hover"><div class="attachment-delete"><a href=""></a></div><div class="attachment-edit"><a href=""></a></div></div>\';
                      mediaHTML += \'<img src="\' + attachment.url + \'" alt=""></li>\';
                      
                      jQuery(\'#property_photos_grid ul\').append(mediaHTML);
                  }
              }
        </script>';
        
    }
                  
    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        $photos = array();
        if (trim($_POST['photo_attachment_ids'], ',') != '')
        {
            $photos = explode( ",", trim($_POST['photo_attachment_ids'], ',') );
        }
        update_post_meta( $post_id, '_photos', $photos );
    }

}
