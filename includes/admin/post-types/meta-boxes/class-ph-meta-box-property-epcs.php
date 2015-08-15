<?php
/**
 * Property EPCs
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Epcs
 */
class PH_Meta_Box_Property_Epcs {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        
        $thumbnail_width = get_option( 'thumbnail_size_w', 150 );
        $thumbnail_height = get_option( 'thumbnail_size_h', 150 );
        
        echo '<div class="propertyhive_meta_box">';
        
            echo '<div class="options_group">';
            
                echo '<div class="media_grid" id="property_epcs_grid"><ul>';
                
                $epcs = get_post_meta($post->ID, '_epcs', TRUE);
                $input_value = '';
                if (is_array($epcs) && !empty($epcs))
                {                    
                    $input_value = implode(",", $epcs);
                    
                    foreach ($epcs as $epcs_attachment_id)
                    {
                        if (wp_attachment_is_image( $epcs_attachment_id ))
                        {
                            $image = wp_get_attachment_thumb_url($epcs_attachment_id);
                        }
                        else
                        {
                            $type = get_post_mime_type($epcs_attachment_id);
                            $icon = 'text.png';
                            
                            switch ($type)
                            {
                                case "application/pdf":
                                case "application/x-pdf":
                                {
                                    $icon = 'pdf.png';
                                    break;
                                }
                                case "application/msword":
                                case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                                {
                                    $icon = 'word.png';
                                    break;
                                }
                                case "text/csv":
                                case "application/vnd.ms-excel":
                                case "text/csv":
                                {
                                    $icon = 'excel.png';
                                    break;
                                }
                            }
                            
                            $image = PH()->plugin_url() . '/assets/images/filetypes/' . $icon;
                        }
                        
                        
                        echo '<li id="epc_' . $epcs_attachment_id . '">';
                            echo '<div class="hover"><div class="attachment-delete"><a href=""></a></div><div class="attachment-edit"><a href=""></a></div></div>';
                            echo '<img src="' . $image . '" alt="" width="' . $thumbnail_width . '" height="' . $thumbnail_height . '">';
                        echo '</li>';
                    }
                }
                else
                {
                    //echo '<p>' . __( 'No epcs have been uploaded yet', 'propertyhive' ) . '</p>';
                }
                
                echo '</ul></div>';
                
                echo '<a href="" class="button button-primary ph_upload_epc_button">' . __('Add EPCs', 'propertyhive') . '</a>';
    
                do_action('propertyhive_property_epcs_fields');
    	       
                echo '<input type="hidden" name="epc_attachment_ids" id="epc_attachment_ids" value="' . $input_value . '">';
               
            echo '</div>';
        
        echo '</div>';
        
        echo '<script>
              // Uploading files
              var file_frame4;
              
              //var sortable_options = 
             
              jQuery(document).ready(function()
              {
                  jQuery( \'#property_epcs_grid ul\' ).sortable({
                      update : function (event, ui) {
                            var new_order = \'\';
                            jQuery(\'#property_epcs_grid ul\').find(\'li\').each( function () {
                                if (new_order != \'\')
                                {
                                    new_order += \',\';
                                }
                                new_order = new_order + jQuery(this).attr(\'id\').replace(\'epc_\', \'\');
                            });
                            jQuery(\'#epc_attachment_ids\').val(new_order);
                      }
                  });
                  jQuery( \'#property_epcs_grid ul\' ).disableSelection();
                  
                  jQuery(\'body\').on(\'click\', \'#property_epcs_grid .attachment-delete a\', function()
                  {
                      var container = jQuery(this).parent().parent().parent();
                      var epc_id = container.attr(\'id\');
                      epc_id = epc_id.replace(\'epc_\', \'\');
                      
                      var attachment_ids = jQuery(\'#epc_attachment_ids\').val();
                      // Check it\'s not already in the list
                      attachment_ids = attachment_ids.split(\',\');
                      
                      var new_attachment_ids = \'\';
                      for (var i in attachment_ids)
                      {
                          if (attachment_ids[i] != epc_id)
                          {
                              if (new_attachment_ids != \'\')
                              {
                                  new_attachment_ids += \',\';
                              }
                              new_attachment_ids += attachment_ids[i];
                          }
                      }
                      jQuery(\'#epc_attachment_ids\').val(new_attachment_ids);
                      
                      container.fadeOut(\'fast\', function()
                      {
                          container.remove();
                      });
                      
                      return false;
                  });
                  
                  jQuery(\'body\').on(\'click\', \'#property_epcs_grid .attachment-edit a\', function()
                  {
                      
                  });
                  
                  jQuery(\'.ph_upload_epc_button\').live(\'click\', function( event ){
                 
                    event.preventDefault();
                 
                    // If the media frame already exists, reopen it.
                    if ( file_frame4 ) {
                      file_frame4.open();
                      return;
                    }
                 
                    // Create the media frame.
                    file_frame4 = wp.media.frames.file_frame4 = wp.media({
                      title: jQuery( this ).data( \'uploader_title\' ),
                      button: {
                        text: jQuery( this ).data( \'uploader_button_text\' ),
                      },
                      multiple: true  // Set to true to allow multiple files to be selected
                    });
                 
                    // When an image is selected, run a callback.
                    file_frame4.on( \'select\', function() {
                        var selection = file_frame4.state().get(\'selection\');
     
                        selection.map( function( attachment ) {
                     
                            attachment = attachment.toJSON();
                     
                            // Do something with attachment.id and/or attachment.url here
                            
                            // Add selected images to grid
                            add_epc_attachment_to_grid(attachment);
                        });
                    });
                 
                    // Finally, open the modal
                    file_frame4.open();
                  });
              });
              
              function add_epc_attachment_to_grid(attachment)
              {
                  var attachment_ids = jQuery(\'#epc_attachment_ids\').val();
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
                      jQuery(\'#epc_attachment_ids\').val(new_attachment_ids);
                      
                      // Add epc to media grid
                      var mediaHTML = \'\';
                      
                      // get extension and icon
                      var attachment_url = attachment.url;
                      attachment_url = attachment_url.split(\'.\');
                      var extension = attachment_url[attachment_url.length-1].toLowerCase();
                      
                      if (extension == \'jpeg\' || extension == \'jpg\' || extension == \'png\' || extension == \'gif\' || extension == \'bmp\')
                      {
                          var image = attachment.url;
                      }
                      else
                      {
                          var icon = \'text.png\';
                          switch (extension)
                          {
                              case \'pdf\':
                              {
                                  icon = \'pdf.png\';
                                  break;
                              }
                              case \'doc\':
                              case \'docx\':
                              {
                                  icon = \'word.png\';
                                  break;
                              }
                              case \'csv\':
                              case \'xls\':
                              case \'xlsx\':
                              {
                                  icon = \'excel.png\';
                                  break;
                              }
                          }
                          
                          var image = \'' . PH()->plugin_url() . '/assets/images/filetypes/\' + icon;
                      }
                      
                      mediaHTML += \'<li id="epc_\' + attachment.id + \'">\';
                      mediaHTML += \'<div class="hover"><div class="attachment-delete"><a href=""></a></div><div class="attachment-edit"><a href=""></a></div></div>\';
                      mediaHTML += \'<img src="\' + image + \'" alt="" width="' . $thumbnail_width . '" height="' . $thumbnail_height . '"></li>\';
                      
                      jQuery(\'#property_epcs_grid ul\').append(mediaHTML);
                  }
              }
        </script>';
        
    }
                  
    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        $epcs = array();
        if (trim($_POST['epc_attachment_ids'], ',') != '')
        {
            $epcs = explode( ",", trim($_POST['epc_attachment_ids'], ',') );
        }
        update_post_meta( $post_id, '_epcs', $epcs );
    }

}
