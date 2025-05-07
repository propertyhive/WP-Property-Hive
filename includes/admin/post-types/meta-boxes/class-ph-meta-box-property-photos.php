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

            if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
            {
                echo '<div id="property_photo_urls">';
            
                    $photo_urls = get_post_meta($post->ID, '_photo_urls', TRUE);
                    if ( !is_array($photo_urls) ) { $photo_urls = array(); }
                    
                    $i = 0;
                    foreach ( $photo_urls as $photo )
                    {
                        echo '
                        <p class="form-field photo_url_field ">
                            <label for="photo_url_' . esc_attr($i) . '">Photo URL</label>
                            <input type="text" class="short" name="photo_url[]" id="" value="' . esc_attr($photo['url']) . '" placeholder="https://"> 
                            <a href="" class="button remove_photo_url"><span class="fa fa-trash"></span></a>
                        </p>';

                        ++$i;
                    }
                
                echo '</div>';
            
                echo '<div id="property_photo_url_template" style="display:none">';

                echo '
                <p class="form-field photo_url_field ">
                    <label for="photo_url_1">Photo URL</label>
                    <input type="text" class="short" name="photo_url[]" id="" value="" placeholder="https://"> 
                    <a href="" class="button remove_photo_url"><span class="fa fa-trash"></span></a>
                </p>';
                
                echo '</div>';
            
                echo '            
                <p class="form-field">
                    <label for="">&nbsp;</label>
                    <a href="" class="button button-primary add_property_photo_url"><span class="fa fa-plus"></span> Add Photo URL</a>
                </p>';

                echo '<script>
            
                    jQuery(document).ready(function()
                    {
                        jQuery(\'.add_property_photo_url\').click(function()
                        {
                            var photo_url_template = jQuery(\'#property_photo_url_template\').html();
                            
                            jQuery(\'#property_photo_urls\').append(photo_url_template);
                            
                            return false;
                        });
                        
                        jQuery(\'.remove_photo_url\').click(function()
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
            else
            {           
                echo '<div class="media_grid" id="property_photos_grid"><ul>';
                
                $photos = get_post_meta($post->ID, '_photos', TRUE);
                $input_value = '';
                if (is_array($photos) && !empty($photos))
                {                    
                    $input_value = implode(",", $photos);
                    
                    foreach ($photos as $photo_attachment_id)
                    {
                        echo '<li id="photo_' . (int)$photo_attachment_id . '">';
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
                
                echo '<a href="" class="button button-primary ph_upload_photo_button">' . esc_html(__('Add Photos', 'propertyhive')) . '</a>';
    
                do_action('propertyhive_property_photos_fields');
    	          
                echo '<input type="hidden" name="previous_photo_attachment_ids" id="previous_photo_attachment_ids" value="' . esc_attr($input_value) . '">';
                echo '<input type="hidden" name="photo_attachment_ids" id="photo_attachment_ids" value="' . esc_attr($input_value) . '">';

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
                        
                        jQuery(\'body\').on(\'click\', \'#property_photos_grid .attachment-delete a\', function(event)
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
                        
                        jQuery(\'body\').on(\'click\', \'#property_photos_grid .attachment-edit a\', function(event)
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
                        
                        jQuery(\'body\').on(\'click\', \'.ph_upload_photo_button\', function( event ){
                       
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
               
            echo '</div>';
        
        echo '</div>';
    }
                  
    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        {
           $photo_urls = array();
           if ( isset($_POST['photo_url']) && is_array($_POST['photo_url']) && !empty($_POST['photo_url']) )
           {
              foreach ( $_POST['photo_url'] as $photo_url )
              {
                  if ( ph_clean($photo_url) == '' ) { continue; }
                  $photo_urls[] = array('url' => ph_clean($photo_url));
              }
           }
           update_post_meta( $post_id, '_photo_urls', $photo_urls );
        }
        else
        {
            $photos = array();
            if (trim($_POST['photo_attachment_ids'], ',') != '')
            {
                $photos = explode( ",", trim(ph_clean($_POST['photo_attachment_ids']), ',') );

                foreach ($photos as $attachment_id)
                {
                    $attachment = array(
                        'ID' => $attachment_id,
                        'post_parent' => $post_id,
                    );

                    wp_update_post($attachment);

                    clean_attachment_cache($attachment_id);
                }
            }
            update_post_meta( $post_id, '_photos', $photos );

            // Remove post attachment for photos no longer in list
            if (trim($_POST['previous_photo_attachment_ids'], ',') != '')
            {
                $previous_photos = explode( ",", trim(ph_clean($_POST['previous_photo_attachment_ids']), ',') );

                foreach ( $previous_photos as $attachment_id )
                {
                    if ( !in_array($attachment_id, $photos) )
                    {
                        // No longer in list, let's unattach it
                        $attachment = array(
                            'ID' => $attachment_id,
                            'post_parent' => 0,
                        );

                        wp_update_post($attachment);

                        clean_attachment_cache($attachment_id);
                    }
                }
            }
        }
    }

}
