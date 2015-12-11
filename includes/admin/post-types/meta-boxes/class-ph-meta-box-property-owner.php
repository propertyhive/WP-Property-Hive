<?php
/**
 * Property Owner / Landlord
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Owner
 */
class PH_Meta_Box_Property_Owner {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
	    
        global $post, $wpdb, $thepostid;
        
        if ( isset($_GET['owner_contact_id']) && ! empty( $_GET['owner_contact_id'] ) )
        {
            if ( get_post_type( $_GET['owner_contact_id'] ) == 'contact' )
            {
                $owner_contact_id = $_GET['owner_contact_id'];
            }   
        }
        else
        {
            $owner_contact_id = get_post_meta($post->ID, '_owner_contact_id', TRUE);
        }
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        echo '<input type="hidden" name="_owner_contact_id" id="_owner_contact_id" value="' . $owner_contact_id . '">';

            // No owner currently selected
            
            echo '<div id="search_propertyhive_contacts"' . ( ($owner_contact_id != '') ? ' style="display:none"' : '' ) . '>';

                echo '<p class="form-field search_propertyhive_contacts_keyword_field">
                    <label for="search_propertyhive_contacts_keyword">' . __('Search Existing Contacts', 'propertyhive') . '</label>
                    <input type="text" class="short" name="search_propertyhive_contacts_keyword" id="search_propertyhive_contacts_keyword" value="" placeholder="' . __( 'Start typing to search...' , 'propertyhive') . '">
                </p>';
                
                echo '<p class="form-field search_propertyhive_contacts_results">
                    <label for="search_propertyhive_contacts_results"></label>
                    <span id="search_propertyhive_contacts_results"></span>
                </p>';
                
                echo '<p class="form-field"><label>&nbsp;</label>';
                
                echo __('Or', 'propertyhive') . '<br><br>';
                echo '<a href="#" class="button add-new-property-owner-contact">' . __('Add New Contact', 'propertyhive') . '</a>';
    
                echo '</p>';
                
            echo '</div>';
            
            echo '<script>
            
                      function load_existing_owner_contact(contact_id)
                      {
                          // Do AJAX request
                          var data = {
                              action:         \'propertyhive_load_existing_owner_contact\',
                              contact_id:     contact_id,
                              security:       \'' . wp_create_nonce("load-existing-owner-contact") . '\',
                          };
                
                          jQuery.post( \'' . admin_url('admin-ajax.php') . '\', data, function(response) {
                              
                              jQuery(\'#existing-owner-details\').html( response );
                              
                          });
                          
                          jQuery(\'#_owner_contact_id\').val(contact_id);
                      }
            
                      jQuery(document).ready(function()
                      {
                          ';
                          
                          if ($owner_contact_id != '')
                          {
                              echo 'load_existing_owner_contact(' . $owner_contact_id . ');';
                          }
                          
                          echo '
                          jQuery(\'a.add-new-property-owner-contact\').click(function()
                          {
                              jQuery(\'#search_propertyhive_contacts\').fadeOut(\'fast\', function()
                              {
                                  jQuery(\'#add_new_property_owner_contact\').fadeIn();
                              });
                              
                              return false;
                          });
                          
                          jQuery(\'a.search-property-owner-contacts\').click(function()
                          {
                              jQuery(\'#add_new_property_owner_contact\').fadeOut(\'fast\', function()
                              {
                                  jQuery(\'#search_propertyhive_contacts\').fadeIn();
                                  jQuery(\'#search_propertyhive_contacts_keyword\').focus();
                              });
                              
                              return false;
                          });
                          
                          jQuery(\'body\').on(\'click\', \'a[id^=\\\'search-contact-result-\\\']\', function()
                          {
                              var contact_id = jQuery(this).attr(\'id\');
                              contact_id = contact_id.replace(\'search-contact-result-\', \'\');
                              
                              load_existing_owner_contact(contact_id);
                              
                              jQuery(\'#search_propertyhive_contacts\').fadeOut(\'fast\', function()
                              {
                                    jQuery(\'#existing-owner-details\').fadeIn();
                              });
                              return false;
                          });
                          
                          jQuery(\'body\').on(\'click\', \'a#remove-owner-contact\', function()
                          {
                              jQuery(\'#existing-owner-details\').fadeOut(\'fast\', function()
                              {
                                    jQuery(\'#search_propertyhive_contacts\').fadeIn();
                              });
                              
                              jQuery(\'#_owner_contact_id\').val(\'\');
                              
                              return false;
                          });
                          
                          // Existing contact search
                          jQuery(\'#search_propertyhive_contacts_keyword\').keyup(function()
                          {
                              var keyword = jQuery(\'#search_propertyhive_contacts_keyword\').val();
                              
                              if (keyword.length == 0)
                              {
                                  // Clear existing results
                                  jQuery(\'#search_propertyhive_contacts_results\').stop(true, true).fadeOut(\'fast\');
                              }
                              else
                              {
                                  jQuery(\'#search_propertyhive_contacts_results\').stop(true, true).fadeIn(\'fast\');
                                  
                                  if (keyword.length > 2)
                                  {
                                        // Do AJAX request
                                        var data = {
                                            action:         \'propertyhive_search_contacts\',
                                            keyword:        keyword,
                                            security:       \'' . wp_create_nonce("search-contacts") . '\',
                                        };
                                
                                        jQuery.post( \'' . admin_url('admin-ajax.php') . '\', data, function(response) {
                                            
                                            if (response.length > 0)
                                            {
                                                var new_html = \'\';
                                                for (var i in response)
                                                {
                                                    new_html += \'<a href="#" id="search-contact-result-\' + response[i].ID + \'">\' + response[i].post_title + \'</a><br>\';
                                                }
                                                jQuery(\'#search_propertyhive_contacts_results\').html(new_html);
                                            }
                                            else
                                            {
                                                jQuery(\'#search_propertyhive_contacts_results\').html(\'' . __( 'No contacts found', 'propertyhive' ) . '\');
                                            }
                                            
                                            
                                            //$(\'ul.record_notes\').prepend( response );
                                            //$(\'#propertyhive-property-notes\').unblock();
                                            //$(\'#add_property_note\').val(\'\');
                                        });
                                  }
                                  else
                                  {
                                      jQuery(\'#search_propertyhive_contacts_results\').html(\'' . __( 'Keep on typing...', 'propertyhive' ) . '\');
                                  }
                              }
                          });
                      });
                  </script>';
            
            echo '<div id="add_new_property_owner_contact" style="display:none;">';
            
            echo '<a href="#" class="button right search-property-owner-contacts">&lt; ' . __('Search Existing Contacts', 'propertyhive') . '</a>';
            
            echo '<h4>' . __('Name', 'propertyhive') . '</h4>';
            
            propertyhive_wp_text_input( array( 
                'id' => '_owner_name', 
                'label' => __( 'Full Name', 'propertyhive' ), 
                'desc_tip' => false, 
                'placeholder' => __( 'e.g. Mr & Mrs Jones, Ms Jane Smith', 'propertyhive' ), 
                //'description' => __( 'Stock quantity. If this is a variable product this value will be used to control stock for all variations, unless you define stock at variation level.', 'propertyhive' ), 
                'type' => 'text'
            ) );
            
            echo '<h4>' . __('Correspondence Address', 'propertyhive') . ' (<a href="" class="use-property-address">Use Property Address</a>)</h4>';
            
            propertyhive_wp_text_input( array( 
                'id' => '_owner_address_name_number', 
                'label' => __( 'Building Name / Number', 'propertyhive' ), 
                'desc_tip' => false, 
                'placeholder' => __( 'e.g. Thistle Cottage, or Flat 10', 'propertyhive' ), 
                //'description' => __( 'Stock quantity. If this is a variable product this value will be used to control stock for all variations, unless you define stock at variation level.', 'propertyhive' ), 
                'type' => 'text'
            ) );
            
            propertyhive_wp_text_input( array( 
                'id' => '_owner_address_street', 
                'label' => __( 'Street', 'propertyhive' ), 
                'desc_tip' => false, 
                'placeholder' => __( 'e.g. High Street', 'propertyhive' ), 
                'type' => 'text'
            ) );
            
            propertyhive_wp_text_input( array( 
                'id' => '_owner_address_two', 
                'label' => __( 'Address Line 2', 'propertyhive' ), 
                'desc_tip' => false, 
                'type' => 'text'
            ) );
            
            propertyhive_wp_text_input( array( 
                'id' => '_owner_address_three', 
                'label' => __( 'Town / City', 'propertyhive' ), 
                'desc_tip' => false, 
                'type' => 'text'
            ) );
            
            propertyhive_wp_text_input( array( 
                'id' => '_owner_address_four', 
                'label' => __( 'County / State', 'propertyhive' ), 
                'desc_tip' => false, 
                'type' => 'text'
            ) );
            
            propertyhive_wp_text_input( array( 
                'id' => '_owner_address_postcode', 
                'label' => __( 'Postcode / Zip Code', 'propertyhive' ), 
                'desc_tip' => false, 
                'type' => 'text'
            ) );
            
            // Country dropdown?
            
            echo '<h4>' . __('Contact Details', 'propertyhive') . '</h4>';
            
            propertyhive_wp_text_input( array( 
                'id' => '_owner_telephone_number', 
                'label' => __( 'Telephone Number', 'propertyhive' ), 
                'desc_tip' => false, 
                'type' => 'text'
            ) );
            
            propertyhive_wp_text_input( array( 
                'id' => '_owner_email_address', 
                'label' => __( 'Email Address', 'propertyhive' ), 
                'desc_tip' => true, 
                'description' => __( 'If the contact has multiple email addresses simply separate them using a comma', 'propertyhive' ),
                'type' => 'text'
            ) );
            
            echo '</div>';
    
            do_action('propertyhive_property_owner_fields');
        
            echo '<div id="existing-owner-details"' . ( ($owner_contact_id == '') ? ' style="display:none"' : '' ) . '>';
                
            echo '</div>';
	    
        echo '</div>';
        
        echo '</div>';

        echo '<script>

          jQuery(document).ready(function()
          {
              jQuery(\'a.use-property-address\').click(function()
              {
                  jQuery(\'#_owner_address_name_number\').val( jQuery(\'#_address_name_number\').val() );
                  jQuery(\'#_owner_address_street\').val( jQuery(\'#_address_street\').val() );
                  jQuery(\'#_owner_address_two\').val( jQuery(\'#_address_two\').val() );
                  jQuery(\'#_owner_address_three\').val( jQuery(\'#_address_three\').val() );
                  jQuery(\'#_owner_address_four\').val( jQuery(\'#_address_four\').val() );
                  jQuery(\'#_owner_address_postcode\').val( jQuery(\'#_address_postcode\').val() );

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
        
        $contact_post_id = $_POST['_owner_contact_id'];
        
        if ($contact_post_id == '')
        {
            // If no owner passed in, only add if new fields have been filled in
            if (
                $_POST['_owner_name'] != '' ||
                $_POST['_owner_address_name_number'] != '' ||
                $_POST['_owner_address_street'] != '' ||
                $_POST['_owner_address_two'] != '' ||
                $_POST['_owner_address_three'] != '' ||
                $_POST['_owner_address_four'] != '' ||
                $_POST['_owner_address_postcode'] != '' ||
                $_POST['_owner_telephone_number'] != '' ||
                $_POST['_owner_email_address'] != ''

            )
            {
                // Insert contact
                $owner_post = array(
                    'post_title'    => $_POST['_owner_name'],
                    'post_content'  => '',
                    'post_status'   => 'publish',
                    'post_type'  => 'contact',
                );
              
                // Insert the post into the database
                $contact_post_id = wp_insert_post( $owner_post );
              
                update_post_meta( $contact_post_id, '_address_name_number', $_POST['_owner_address_name_number'] );
                update_post_meta( $contact_post_id, '_address_street', $_POST['_owner_address_street'] );
                update_post_meta( $contact_post_id, '_address_two', $_POST['_owner_address_two'] );
                update_post_meta( $contact_post_id, '_address_three', $_POST['_owner_address_three'] );
                update_post_meta( $contact_post_id, '_address_four', $_POST['_owner_address_four'] );
                update_post_meta( $contact_post_id, '_address_postcode', $_POST['_owner_address_postcode'] );
              
                update_post_meta( $contact_post_id, '_telephone_number', $_POST['_owner_telephone_number'] );
                update_post_meta( $contact_post_id, '_email_address', $_POST['_owner_email_address'] );
            }
        }

        $existing_contact_types = get_post_meta( $contact_post_id, '_contact_types', TRUE );
        if ( $existing_contact_types == '' || !is_array($existing_contact_types) )
        {
            $existing_contact_types = array();
        }
        if ( !in_array( 'owner', $existing_contact_types ) )
        {
            $existing_contact_types[] = 'owner';
            update_post_meta( $contact_post_id, '_contact_types', $existing_contact_types );
        }
        
        update_post_meta( $post_id, '_owner_contact_id', $contact_post_id );
    }

}
