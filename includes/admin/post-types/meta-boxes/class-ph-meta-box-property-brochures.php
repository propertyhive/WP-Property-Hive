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
        if ( empty($thumbnail_width) ) { $thumbnail_width = 150; }
        $thumbnail_height = get_option( 'thumbnail_size_h', 150 );
        if ( empty($thumbnail_height) ) { $thumbnail_height = 150; }
        
        echo '<div class="propertyhive_meta_box">';
        
            echo '<div class="options_group">';

            if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
            {
                echo '<div id="property_brochure_urls">';
            
                    $brochure_urls = get_post_meta($post->ID, '_brochure_urls', TRUE);
                    if ( !is_array($brochure_urls) ) { $brochure_urls = array(); }
                    
                    $i = 0;
                    foreach ( $brochure_urls as $brochure )
                    {
                        echo '
                        <p class="form-field brochure_url_field ">
                            <label for="brochure_url_' . esc_attr($i) . '">Brochure URL</label>
                            <input type="text" class="short" name="brochure_url[]" id="" value="' . esc_attr($brochure['url']) . '" placeholder="https://"> 
                            <a href="" class="button remove_brochure_url"><span class="fa fa-trash"></span></a>
                        </p>';

                        ++$i;
                    }
                
                echo '</div>';
            
                echo '<div id="property_brochure_url_template" style="display:none">';

                echo '
                <p class="form-field brochure_url_field ">
                    <label for="brochure_url_1">Brochure URL</label>
                    <input type="text" class="short" name="brochure_url[]" id="" value="" placeholder="https://"> 
                    <a href="" class="button remove_brochure_url"><span class="fa fa-trash"></span></a>
                </p>';
                
                echo '</div>';
            
                echo '            
                <p class="form-field">
                    <label for="">&nbsp;</label>
                    <a href="" class="button button-primary add_property_brochure_url"><span class="fa fa-plus"></span> Add Brochure URL</a>
                </p>';

                echo '<script>
            
                    jQuery(document).ready(function()
                    {
                        jQuery(\'.add_property_brochure_url\').click(function()
                        {
                            var brochure_url_template = jQuery(\'#property_brochure_url_template\').html();
                            
                            jQuery(\'#property_brochure_urls\').append(brochure_url_template);
                            
                            return false;
                        });
                        
                        jQuery(\'#property_brochure_urls\').on(\'click\', \'.remove_brochure_url\', function()
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
                        
                        echo '<li id="brochure_' . esc_attr($brochures_attachment_id) . '">';
                            echo '<div class="hover"><div class="attachment-delete"><a href=""></a></div><div class="attachment-edit"><a href=""></a></div></div>';
                            echo '<a href="' . esc_url(wp_get_attachment_url( $brochures_attachment_id )) . '" target="_blank"><img src="' . esc_url(PH()->plugin_url() . '/assets/images/filetypes/' . $icon) . '" alt="" width="' . esc_attr($thumbnail_width) . '" height="' . esc_attr($thumbnail_height) . '"></a>';
                        echo '</li>';
                    }
                }
                else
                {
                    //echo '<p>' . __( 'No brochures have been uploaded yet', 'propertyhive' ) . '</p>';
                }
                
                echo '</ul></div>';
                
                echo '<a href="" class="button button-primary ph_upload_brochure_button">' . esc_html(__('Add Brochures', 'propertyhive')) . '</a>';
    
                do_action('propertyhive_property_brochures_fields');
    	        
                echo '<input type="hidden" name="previous_brochure_attachment_ids" id="previous_brochure_attachment_ids" value="' . esc_attr($input_value) . '">';
                echo '<input type="hidden" name="brochure_attachment_ids" id="brochure_attachment_ids" value="' . esc_attr($input_value) . '">';

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
                            event.preventDefault();

                            var container = jQuery(this).parent().parent().parent();
                            var brochure_id = container.attr(\'id\');
                            brochure_id = brochure_id.replace(\'brochure_\', \'\');

                            // Create the media frame.
                            file_frame = wp.media.frames.file_frame = wp.media({
                                title: jQuery( this ).data( \'uploader_title\' ),
                                button: {
                                text: \'Update Brochure\',
                                },
                                multiple: false
                            });

                            // open
                            file_frame.on(\'open\',function() {

                                var selection = file_frame.state().get(\'selection\');
                                var ids = brochure_id;
                                if (ids) {
                                    idsArray = ids.split(',');
                                    idsArray.forEach(function(id) {
                                        attachment = wp.media.attachment(id);
                                        attachment.fetch();
                                        selection.add( attachment ? [ attachment ] : [] );
                                    });
                                }
                            });

                            // When an brochure is selected, run a callback.
                            file_frame.on( \'select\', function() {
                                var selection = file_frame.state().get(\'selection\');

                                selection.map( function( attachment ) {

                                    attachment = attachment.toJSON();

                                    if (attachment.id != brochure_id)
                                    {
                                        // Replace brochure
                                        var attachment_ids = jQuery(\'#brochure_attachment_ids\').val();
                                        attachment_ids = attachment_ids.split(\',\');

                                        for (var i in attachment_ids)
                                        {
                                            if (attachment_ids[i] == brochure_id)
                                            {
                                                attachment_ids[i] = attachment.id;
                                            }
                                        }
                                        jQuery(\'#brochure_attachment_ids\').val(attachment_ids);

                                        // Add brochure to media grid
                                        var mediaHTML = \'\';
                                        mediaHTML += \'<li id="brochure_\' + attachment.id + \'">\';
                                        mediaHTML += \'<div class="hover"><div class="attachment-delete"><a href=""></a></div><div class="attachment-edit"><a href=""></a></div></div>\';
                                        mediaHTML += \'<img src="\' + attachment.url + \'" alt=""></li>\';

                                        jQuery(\'#property_brochures_grid ul li#brochure_\' + brochure_id).after(mediaHTML);
                                        jQuery(\'#property_brochures_grid ul li#brochure_\' + brochure_id).remove();
                                    }
                                });
                            });

                            // Finally, open the modal
                            file_frame.open();
                        });
                        
                        jQuery(\'body\').on(\'click\', \'.ph_upload_brochure_button\', function( event ){
                       
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
                            mediaHTML += \'<img src="' . esc_url(PH()->plugin_url()) . '/assets/images/filetypes/\' + icon + \'" alt="" width="' . (int)$thumbnail_width . '" height="' . (int)$thumbnail_height . '"></li>\';
                            
                            jQuery(\'#property_brochures_grid ul\').append(mediaHTML);
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
        
        if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
        {
           $brochure_urls = array();
           if ( isset($_POST['brochure_url']) && is_array($_POST['brochure_url']) && !empty($_POST['brochure_url']) )
           {
              foreach ( $_POST['brochure_url'] as $brochure_url )
              {
                  if ( ph_clean($brochure_url) == '' ) { continue; }
                  $brochure_urls[] = array('url' => ph_clean($brochure_url));
              }
           }
           update_post_meta( $post_id, '_brochure_urls', $brochure_urls );
        }
        else
        {
            $brochures = array();
            if (trim($_POST['brochure_attachment_ids'], ',') != '')
            {
                $brochures = explode( ",", trim(ph_clean($_POST['brochure_attachment_ids']), ',') );

                foreach ($brochures as $attachment_id)
                {
                    $attachment = array(
                        'ID' => $attachment_id,
                        'post_parent' => $post_id,
                    );

                    wp_update_post($attachment);

                    clean_attachment_cache($attachment_id);
                }
            }
            update_post_meta( $post_id, '_brochures', $brochures );

            // Remove post attachment for brochures no longer in list
            if (trim($_POST['previous_brochure_attachment_ids'], ',') != '')
            {
                $previous_brochures = explode( ",", trim(ph_clean($_POST['previous_brochure_attachment_ids']), ',') );

                foreach ( $previous_brochures as $attachment_id )
                {
                    if ( !in_array($attachment_id, $brochures) )
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
