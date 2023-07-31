<?php
/**
 * Offer Property Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Offer_Property
 */
class PH_Meta_Box_Offer_Property {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $property_id = get_post_meta( $post->ID, '_property_id', true );

        if ( !empty($property_id) )
        {
            $property = new PH_Property((int)$property_id);

            echo '<p class="form-field">
            
                <label>' . __('Address', 'propertyhive') . '</label>
                
                <a href="' . get_edit_post_link($property_id, '') . '">' . $property->get_formatted_full_address() . '</a> (<a href="' . get_permalink($property_id) . '" target="_blank">View On Website</a>)
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . ( ( $property->department == 'residential-lettings' || ph_get_custom_department_based_on($property->department) == 'residential-lettings' ) ? __('Landlord', 'propertyhive') : __('Owner', 'propertyhive') ) . '</label>';

            $owner_contact_ids = $property->_owner_contact_id;
            if ( 
                ( !is_array($owner_contact_ids) && $owner_contact_ids != '' && $owner_contact_ids != 0 ) 
                ||
                ( is_array($owner_contact_ids) && !empty($owner_contact_ids) )
            )
            {
                if ( !is_array($owner_contact_ids) )
                {
                    $owner_contact_ids = array($owner_contact_ids);
                }

                foreach ( $owner_contact_ids as $owner_contact_id )
                {
                    $owner = new PH_Contact((int)$owner_contact_id);
                    echo '<a href="' . get_edit_post_link($owner_contact_id, '') . '">' . get_the_title($owner_contact_id) . '</a><br>';
                    echo 'Telephone: ' . ( ( $owner->telephone_number != '' ) ? $owner->telephone_number : '-' ) . '<br>';
                    echo 'Email: ' . ( ( $owner->email_address != '' ) ? '<a href="mailto:' . $owner->email_address . '">' . $owner->email_address . '</a>' : '-' );
                    echo '<br><br>';
                }
            }
            else
            {
                echo 'No ' . ( ( $property->department == 'residential-lettings' || ph_get_custom_department_based_on($property->department) == 'residential-lettings' ) ? __('landlord', 'propertyhive') : __('owner', 'propertyhive') ) . ' specified';
            }
                
            echo '</p>';
        }
        else
        {
echo '<p class="form-field">
            
                <label for="offer_property_search">' . esc_html(__('Search Properties', 'propertyhive')) . '</label>
                
                <span style="position:relative;">

                    <input type="text" name="offer_property_search" id="offer_property_search" style="width:100%;" placeholder="' . esc_attr(__( 'Search Properties', 'propertyhive' )) . '..." autocomplete="false">

                    <div id="offer_search_property_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="offer_selected_properties" style="display:none;"></div>

                </span>
                
            </p>';

            echo '<input type="hidden" name="_property_id" id="_property_id" value="">';
?>
<script>

var offer_selected_properties = [];
<?php if (isset($_GET['property_id']) && $_GET['property_id'] != '') { $property = new PH_Property((int)$_GET['property_id']); ?>
offer_selected_properties[<?php echo (int)$_GET['property_id']; ?>] = ({ post_title: '<?php echo $property->get_formatted_full_address(); ?>' });
<?php } ?>

jQuery(document).ready(function($)
{
    offer_update_selected_properties();
    
    $('#offer_property_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#offer_property_search').keyup(function()
    {
        var keyword = $(this).val();

        if (keyword.length == 0)
        {
            $('#offer_search_property_results').html('');
            $('#offer_search_property_results').hide();
            return false;
        }

        if (keyword.length < 3)
        {
            $('#offer_search_property_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
            $('#offer_search_property_results').show();
            return false;
        }

        var data = {
            action:         'propertyhive_search_properties',
            keyword:        keyword,
            department:     'residential-sales|commercial~forsale',
            security:       '<?php echo wp_create_nonce( 'search-properties' ); ?>',
        };
        $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
            if (response == '' || response.length == 0)
            {
                $('#offer_search_property_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
            }
            else
            {
                $('#offer_search_property_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in response )
                {
                    $('#offer_search_property_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;">' + response[i].post_title + '</a></li>');
                }
            }
            $('#offer_search_property_results').show();
        });
    });

    $('body').on('click', '#offer_search_property_results ul li a', function(e)
    {
        e.preventDefault();

        offer_selected_properties = []; // reset to only allow one property for now
        offer_selected_properties[$(this).attr('href')] = ({ post_title: $(this).text() });

        $('#offer_search_property_results').html('');
        $('#offer_search_property_results').hide();

        $('#offer_property_search').val('');

        offer_update_selected_properties();

        // If the Owner Solicitor select meta box exists on the page and no solicitor has been selected yet
        if (typeof jQuery('#offer_selected_property_owner_solicitors').html() !== 'undefined' && jQuery('#offer_selected_property_owner_solicitors').html() == '')
        {
            // Find the first solicitor assigned to any of the property owners and assign them as Owner Solicitor if found
            var data = {
                action: 'propertyhive_get_contact_solicitor',
                post_id: $(this).attr('href'),
            };
            $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response)
            {
                if (response != '')
                {
                    var solicitor_data = jQuery.parseJSON( response );

                    jQuery('#offer_selected_property_owner_solicitors').html('<ul></ul>');

                    jQuery('#offer_selected_property_owner_solicitors ul').append('<li><a href="' + solicitor_data['id'] + '" class="offer-remove-property_owner-solicitor" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + solicitor_data['name'] + '</li>');

                    jQuery('#_property_owner_solicitor_contact_ids').val(solicitor_data['id']);

                    jQuery('#offer_selected_property_owner_solicitors').show();
                }
            });
        }
    });

    $('body').on('click', 'a.offer-remove-property', function(e)
    {
        e.preventDefault();

        var property_id = $(this).attr('href');

        delete(offer_selected_properties[property_id]);

        offer_update_selected_properties();
    });
});

function offer_update_selected_properties()
{
    jQuery('#_property_id').val();

    if ( Object.keys(offer_selected_properties).length > 0 )
    {
        jQuery('#offer_selected_properties').html('<ul></ul>');
        for ( var i in offer_selected_properties )
        {
            jQuery('#offer_selected_properties ul').append('<li><a href="' + i + '" class="offer-remove-property" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + offer_selected_properties[i].post_title + '</li>');

            jQuery('#_property_id').val(i);
        }
        jQuery('#offer_selected_properties').show();
    }
    else
    {
        jQuery('#offer_selected_properties').html('');
        jQuery('#offer_selected_properties').hide();
    }
}

</script>
<?php
        }

        do_action('propertyhive_offer_property_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        if ( isset($_POST['_property_id']) && !empty($_POST['_property_id']) )
        {
            update_post_meta( $post_id, '_property_id', (int)$_POST['_property_id'] );
        }
    }

}
