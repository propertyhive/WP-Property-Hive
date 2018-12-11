<?php
/**
 * Offer Property_Owner Solicitor Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Offer_Property_Owner_Solicitor
 */
class PH_Meta_Box_Offer_Property_Owner_Solicitor {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $property_owner_solicitor_contact_id = get_post_meta( $post->ID, '_property_owner_solicitor_contact_id', true );

        if ( !empty($property_owner_solicitor_contact_id) )
        {
            $contact = new PH_Contact($property_owner_solicitor_contact_id);

            echo '<p class="form-field">
            
                <label>' . __('Name', 'propertyhive') . '</label>
                
                <a href="' . get_edit_post_link($property_owner_solicitor_contact_id, '') . '">' . get_the_title($property_owner_solicitor_contact_id) . '</a>
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . __('Telephone Number', 'propertyhive') . '</label>
                
                ' . $contact->telephone_number . '
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . __('Email Address', 'propertyhive') . '</label>
                
                <a href="mailto:' . $contact->email_address . '">' .  $contact->email_address  . '</a>
                
            </p>';

            echo '<p class="form-field">
            
                <label></label>
                
                <a class="button" href="' . wp_nonce_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ), '1', 'remove_property_owner_solicitor' ) . '">' .  __( 'Remove Solicitor', 'propertyhive' ) . '</a>
                
            </p>';
        }
        else
        {
            echo '<p class="form-field">
            
                <label for="offer_property_owner_solicitor_search">' . __('Search Solicitors', 'propertyhive') . '</label>
                
                <span style="position:relative;">

                    <input type="text" name="offer_property_owner_solicitor_search" id="offer_property_owner_solicitor_search" style="width:100%;" placeholder="' . __( 'Search Existing Contacts', 'propertyhive' ) . '..." autocomplete="false">

                    <div id="offer_search_property_owner_solicitor_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="offer_selected_property_owner_solicitors" style="display:none;"></div>

                </span>
                
            </p>';

            echo '<input type="hidden" name="_property_owner_solicitor_contact_ids" id="_property_owner_solicitor_contact_ids" value="">';
?>
<script>

var offer_selected_property_owner_solicitors = [];

jQuery(document).ready(function($)
{
    offer_update_selected_property_owner_solicitors();

    $('#offer_property_owner_solicitor_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#offer_property_owner_solicitor_search').keyup(function()
    {
        var keyword = $(this).val();

        if (keyword.length == 0)
        {
            $('#offer_search_property_owner_solicitor_results').html('');
            $('#offer_search_property_owner_solicitor_results').hide();
            return false;
        }

        if (keyword.length < 3)
        {
            $('#offer_search_property_owner_solicitor_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
            $('#offer_search_property_owner_solicitor_results').show();
            return false;
        }

        var data = {
            action:         'propertyhive_search_contacts',
            keyword:        keyword,
            contact_type:   'thirdparty',
            security:       '<?php echo wp_create_nonce( 'search-contacts' ); ?>',
        };
        $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
            if (response == '' || response.length == 0)
            {
                $('#offer_search_property_owner_solicitor_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
            }
            else
            {
                $('#offer_search_property_owner_solicitor_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in response )
                {
                    $('#offer_search_property_owner_solicitor_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;">' + response[i].post_title + '</a></li>');
                }
            }
            $('#offer_search_property_owner_solicitor_results').show();
        });
    });

    $('body').on('click', '#offer_search_property_owner_solicitor_results ul li a', function(e)
    {
        e.preventDefault();

        offer_selected_property_owner_solicitors = []; // reset to only allow one property_owner for now
        offer_selected_property_owner_solicitors[$(this).attr('href')] = ({ post_title: $(this).text() });

        $('#offer_search_property_owner_solicitor_results').html('');
        $('#offer_search_property_owner_solicitor_results').hide();

        $('#offer_property_owner_solicitor_search').val('');

        offer_update_selected_property_owner_solicitors();
    });

    $('body').on('click', 'a.offer-remove-property_owner-solicitor', function(e)
    {
        e.preventDefault();

        var property_owner_solicitor_id = $(this).attr('href');

        delete(offer_selected_property_owner_solicitors[property_owner_solicitor_id]);

        offer_update_selected_property_owner_solicitors();
    });
});

function offer_update_selected_property_owner_solicitors()
{
    jQuery('#_property_owner_contact_solicitor_ids').val();
    if ( Object.keys(offer_selected_property_owner_solicitors).length > 0 )
    {
        jQuery('#offer_selected_property_owner_solicitors').html('<ul></ul>');
        for ( var i in offer_selected_property_owner_solicitors )
        {
            jQuery('#offer_selected_property_owner_solicitors ul').append('<li><a href="' + i + '" class="offer-remove-property_owner-solicitor" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + offer_selected_property_owner_solicitors[i].post_title + '</li>');

            jQuery('#_property_owner_solicitor_contact_ids').val(i);
        }
        jQuery('#offer_selected_property_owner_solicitors').show();
    }
    else
    {
        jQuery('#offer_selected_property_owner_solicitors').html('');
        jQuery('#offer_selected_property_owner_solicitors').hide();
    }
}

</script>
<?php
        }

        do_action('propertyhive_offer_property_owner_solicitor_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        if ( isset($_POST['_property_owner_solicitor_contact_ids']) && $_POST['_property_owner_solicitor_contact_ids'] != '' )
        {
            update_post_meta( $post_id, '_property_owner_solicitor_contact_id', (int)$_POST['_property_owner_solicitor_contact_ids'] );
        }
    }

}
