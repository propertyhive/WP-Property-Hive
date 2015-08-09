<?php
/**
 * Property Brochures
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Brochures
 */
class PH_Meta_Box_Property_Brochures {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        
        $thumbnail_width = get_option( 'thumbnail_size_w', 150 );
        $thumbnail_height = get_option( 'thumbnail_size_h', 150 );
        
        echo '<div class="propertyhive_meta_box">';
        
            echo '<div class="options_group">';
            
                echo '<div class="media_grid" id="property_brochures_grid"><ul>';
                
                $brochures = get_post_meta($post->ID, '_brochures', TRUE);
                $input_value = '';
                if (is_array($brochures) && !empty($brochures))
                {                    
                    $input_value = implode(",", $brochures);
                    
                    foreach ($brochures as $brochures_attachment_id)
                    {
                        $type = get_post_mime_type($brochures_attachment_id);
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
                        
                        echo '<li id="brochures_' . $brochures_attachment_id . '">';
                            echo '<div class="hover"><div class="attachment-delete"><a href=""></a></div><div class="attachment-edit"><a href=""></a></div></div>';
                            echo '<a href="' . wp_get_attachment_url( $brochures_attachment_id ) . '" target="_blank"><img src="' . PH()->plugin_url() . '/assets/images/filetypes/' . $icon . '" alt="" width="' . $thumbnail_width . '" height="' . $thumbnail_height . '"></a>';
                        echo '</li>';
                    }
                }
                else
                {
                    //echo '<p>' . __( 'No brochures have been uploaded yet', 'propertyhive' ) . '</p>';
                }
                
                echo '</ul></div>';
                
                echo '<a href="" class="button button-primary ph_upload_brochure_button">' . __('Add Brochures', 'propertyhive') . '</a>';
    
                do_action('propertyhive_property_brochures_fields');
    	       
                echo '<input type="hidden" name="brochure_attachment_ids" id="brochure_attachment_ids" value="' . $input_value . '">';
               
            echo '</div>';
        
        echo '</div>';
        
        echo '<script>
              // Uploading files
              var file_frame3;
              
              //var sortable_options = 
             
              jQuery(document).ready(function()
              {
                  jQuery( \'#property_brochures_grid ul\' ).sortable({
                      update : function (event, ui) {
                            var new_order = \'\';
                            jQuery(\'#property_brochures_grid ul\').find(\'li\').each( function () {
                                if (new_order != \'\')
                                {
                                    new_order += \',\';
                                }
                                new_order = new_order + jQuery(this).attr(\'id\').replace(\'brochure_\', \'\');
                            });
                            jQuery(\'#brochure_attachment_ids\').val(new_order);
                      }
                  });
                  jQuery( \'#property_brochures_grid ul\' ).disableSelection();
                  
                  jQuery(\'body\').on(\'click\', \'#property_brochures_grid .attachment-delete a\', function()
                  {
                      var container = jQuery(this).parent().parent().parent();
                      var brochure_id = container.attr(\'id\');
                      brochure_id = brochure_id.replace(\'brochure_\', \'\');
                      
                      var attachment_ids = jQuery(\'#brochure_attachment_ids\').val();
                      // Check it\'s not already in the list
                      attachment_ids = attachment_ids.split(\',\');
                      
                      var new_attachment_ids = \'\';
                      for (var i in attachment_ids)
                      {
                          if (attachment_ids[i] != brochure_id)
                          {
                              if (new_attachment_ids != \'\')
                              {
                                  new_attachment_ids += \',\';
                              }
                              new_attachment_ids += attachment_ids[i];
                          }
                      }
                      jQuery(\'#brochure_attachment_ids\').val(new_attachment_ids);
                      
                      container.fadeOut(\'fast\', function()
                      {
                          container.remove();
                      });
                      
                      return false;
                  });
                  
                  jQuery(\'body\').on(\'click\', \'#property_brochures_grid .attachment-edit a\', function()
                  {
                      
                  });
                  
                  jQuery(\'.ph_upload_brochure_button\').live(\'click\', function( event ){
                 
                    event.preventDefault();
                 
                    // If the media frame already exists, reopen it.
                    if ( file_frame3 ) {
                      file_frame3.open();
                      return;
                    }
                 
                    // Create the media frame.
                    file_frame3 = wp.media.frames.file_frame3 = wp.media({
                      title: jQuery( this ).data( \'uploader_title\' ),
                      button: {
                        text: jQuery( this ).data( \'uploader_button_text\' ),
                      },
                      multiple: true  // Set to true to allow multiple files to be selected
                    });
                 
                    // When an image is selected, run a callback.
                    file_frame3.on( \'select\', function() {
                        var selection = file_frame3.state().get(\'selection\');
     
                        selection.map( function( attachment ) {
                     
                            attachment = attachment.toJSON();
                     
                            // Do something with attachment.id and/or attachment.url here
                            console.log(attachment.url);
                            
                            // Add selected images to grid
                            add_brochure_attachment_to_grid(attachment);
                        });
                    });
                 
                    // Finally, open the modal
                    file_frame3.open();
                  });
              });
              
              function add_brochure_attachment_to_grid(attachment)
              {
                  var attachment_ids = jQuery(\'#brochure_attachment_ids\').val();
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
                      jQuery(\'#brochure_attachment_ids\').val(new_attachment_ids);
                      
                      // Add brochure to media grid
                      var mediaHTML = \'\';
                      
                      // get extension and icon
                      var icon = \'text.png\';
                      var attachment_url = attachment.url;
                      attachment_url = attachment_url.split(\'.\');
                      var extension = attachment_url[attachment_url.length-1].toLowerCase();
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
                      
                      mediaHTML += \'<li id="brochure_\' + attachment.id + \'">\';
                      mediaHTML += \'<div class="hover"><div class="attachment-delete"><a href=""></a></div><div class="attachment-edit"><a href=""></a></div></div>\';
                      mediaHTML += \'<img src="' . PH()->plugin_url() . '/assets/images/filetypes/\' + icon + \'" alt="" width="' . $thumbnail_width . '" height="' . $thumbnail_height . '"></li>\';
                      
                      jQuery(\'#property_brochures_grid ul\').append(mediaHTML);
                  }
              }
        </script>';
        
    }
                  
    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        $brochures = array();
        if (trim($_POST['brochure_attachment_ids'], ',') != '')
        {
            $brochures = explode( ",", trim($_POST['brochure_attachment_ids'], ',') );
        }
        update_post_meta( $post_id, '_brochures', $brochures );
    }

}
