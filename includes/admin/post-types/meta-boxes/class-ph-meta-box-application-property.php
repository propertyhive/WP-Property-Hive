<?php
/**
 * Application Property Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Application_Property
 */
class PH_Meta_Box_Application_Property {

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
            
                <label>' . ( ( $property->department == 'residential-lettings' ) ? __('Landlord', 'propertyhive') : __('Owner', 'propertyhive') ) . '</label>';

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
                    echo '<a href="' . get_edit_post_link($owner_contact_id, '') . '" data-application-owner-id="' . $owner_contact_id . '" data-application-owner-name="' . get_the_title($owner_contact_id, '') . '">' . get_the_title($owner_contact_id) . '</a><br>';
                    echo 'Telephone: ' . ( ( $owner->telephone_number != '' ) ? $owner->telephone_number : '-' ) . '<br>';
                    echo 'Email: ' . ( ( $owner->email_address != '' ) ? '<a href="mailto:' . $owner->email_address . '">' . $owner->email_address . '</a>' : '-' );
                    echo '<br><br>';
                }
            }
            else
            {
                echo 'No ' . ( ( $property->department == 'residential-lettings' ) ? __('landlord', 'propertyhive') : __('owner', 'propertyhive') ) . ' specified';
            }
                
            echo '</p>';
        }
        else
        {
echo '<p class="form-field">
            
                <label for="application_property_search">' . __('Search Properties', 'propertyhive') . '</label>
                
                <span style="position:relative;">

                    <input type="text" name="application_property_search" id="application_property_search" style="width:100%;" placeholder="' . __( 'Search Properties', 'propertyhive' ) . '..." autocomplete="false">

                    <div id="application_search_property_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="application_selected_properties" style="display:none;"></div>

                </span>
                
            </p>';

            echo '<input type="hidden" name="_property_id" id="_property_id" value="">';
?>
<script>

var application_selected_properties = [];
<?php if (isset($_GET['property_id']) && $_GET['property_id'] != '') { $property = new PH_Property((int)$_GET['property_id']); ?>
application_selected_properties.push({ id: <?php echo (int)$_GET['property_id']; ?>, post_title: '<?php echo $property->get_formatted_full_address(); ?>' });
<?php } ?>
var application_search_properties_timeout;

jQuery(document).ready(function($)
{
    application_update_selected_properties();
    
    $('#application_property_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#application_property_search').keyup(function()
    {
        clearTimeout(application_search_properties_timeout);
        application_search_properties_timeout = setTimeout(function() { application_perform_property_search(); }, 400);
    });

    $('body').on('click', '#application_search_property_results ul li a', function(e)
    {
        e.preventDefault();

        application_selected_properties = []; // reset to only allow one property for now
        application_selected_properties.push({ id: $(this).attr('href'), post_title: $(this).text(), owner_id: $(this).attr('data-application-owner-id'), owner_name: $(this).attr('data-application-owner-name') });

        $('#application_search_property_results').html('');
        $('#application_search_property_results').hide();

        $('#application_property_search').val('');

        application_update_selected_properties();
    });

    $('body').on('click', 'a.application-remove-property', function(e)
    {
        e.preventDefault();

        var property_id = $(this).attr('href');

        for (var key in application_selected_properties) 
        {
            if (application_selected_properties[key].id == property_id ) 
            {
                application_selected_properties.splice(key, 1);
            }
        }

        application_update_selected_properties();
    });
});

function application_perform_property_search()
{
    var keyword = jQuery('#application_property_search').val();

    if (keyword.length == 0)
    {
        jQuery('#application_search_property_results').html('');
        jQuery('#application_search_property_results').hide();
        return false;
    }

    if (keyword.length < 3)
    {
        jQuery('#application_search_property_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
        jQuery('#application_search_property_results').show();
        return false;
    }

    var data = {
        action:         'propertyhive_search_properties',
        keyword:        keyword,
        department:     'residential-lettings',
        security:       '<?php echo wp_create_nonce( 'search-properties' ); ?>',
    };
    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
    {
        if (response == '' || response.length == 0)
        {
            jQuery('#application_search_property_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
        }
        else
        {
            jQuery('#application_search_property_results').html('<ul style="margin:0; padding:0;"></ul>');
            for ( var i in response )
            {
                jQuery('#application_search_property_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;" data-application-owner-id="' + response[i].owner_id + '" data-application-owner-name="' + response[i].owner_name + '">' + response[i].post_title + '</a></li>');
            }
        }
        jQuery('#application_search_property_results').show();
    });
}

function application_update_selected_properties()
{
    jQuery('#_property_id').val('');

    if ( application_selected_properties.length > 0 )
    {
        jQuery('#application_selected_properties').html('<ul></ul>');
        for ( var i in application_selected_properties )
        {
            jQuery('#application_selected_properties ul').append('<li><a href="' + application_selected_properties[i].id + '" class="application-remove-property" style="color:inherit; text-decoration:none;" data-application-owner-id="' + application_selected_properties[i].owner_id + '" data-application-owner-name="' + application_selected_properties[i].owner_name + '"><span class="dashicons dashicons-no-alt"></span></a> ' + application_selected_properties[i].post_title + '</li>');

            jQuery('#_property_id').val(application_selected_properties[i].id);
        }
        jQuery('#application_selected_properties').show();
    }
    else
    {
        jQuery('#application_selected_properties').html('');
        jQuery('#application_selected_properties').hide();
    }

    jQuery('#_property_id').trigger('change');
}

</script>
<?php
        }

        do_action('propertyhive_application_property_fields');
	    
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
