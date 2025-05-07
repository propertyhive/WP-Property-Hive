<?php
/**
 * Tenancy Property Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Tenancy_Property
 */
class PH_Meta_Box_Tenancy_Property {

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
            
                <label>' . esc_html(__('Address', 'propertyhive')) . '</label>
                
                <a href="' . esc_url(get_edit_post_link($property_id, '')) . '">' . esc_html($property->get_formatted_full_address()) . '</a>' . ( !in_array($property->post_status, array('trash', 'archive')) ? ' (<a href="' . esc_url(get_permalink($property_id)) . '" target="_blank">View On Website</a>)' : '' ) . '
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . esc_html( ( $property->department == 'residential-lettings' ) ? __('Landlord', 'propertyhive') : __('Owner', 'propertyhive') ) . '</label>';

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
                    echo '<a href="' . esc_url(get_edit_post_link($owner_contact_id, '')) . '" data-tenancy-owner-id="' . esc_attr($owner_contact_id) . '" data-tenancy-owner-name="' . esc_attr(get_the_title($owner_contact_id, '')) . '">' . esc_html(get_the_title($owner_contact_id)) . '</a><br>';
                    echo 'Telephone: ' . ( ( $owner->telephone_number != '' ) ? esc_html($owner->telephone_number) : '-' ) . '<br>';
                    echo 'Email: ' . ( ( $owner->email_address != '' ) ? '<a href="mailto:' . esc_attr($owner->email_address) . '">' . esc_html($owner->email_address) . '</a>' : '-' );
                    echo '<br><br>';
                }
            }
            else
            {
                echo esc_html('No ' . ( ( $property->department == 'residential-lettings' ) ? __('landlord', 'propertyhive') : __('owner', 'propertyhive') ) . ' specified');
            }
                
            echo '</p>';
        }
        else
        {
echo '<p class="form-field">
            
                <label for="tenancy_property_search">' . esc_html(__('Search Properties', 'propertyhive')) . '</label>
                
                <span style="position:relative;">

                    <input type="text" name="tenancy_property_search" id="tenancy_property_search" style="width:100%;" placeholder="' . esc_attr(__( 'Search Properties', 'propertyhive' )) . '..." autocomplete="false">

                    <div id="tenancy_search_property_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="tenancy_selected_properties" style="display:none;"></div>

                </span>
                
            </p>';

            echo '<input type="hidden" name="_property_id" id="_property_id" value="">';
?>
<script>

var tenancy_selected_properties = [];
<?php if (isset($_GET['property_id']) && $_GET['property_id'] != '') { $property = new PH_Property((int)$_GET['property_id']); ?>
tenancy_selected_properties.push({ id: <?php echo (int)$_GET['property_id']; ?>, post_title: '<?php echo esc_js($property->get_formatted_full_address()); ?>' });
<?php } ?>
var tenancy_search_properties_timeout;

jQuery(document).ready(function($)
{
    tenancy_update_selected_properties();
    
    $('#tenancy_property_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#tenancy_property_search').keyup(function()
    {
        clearTimeout(tenancy_search_properties_timeout);
        tenancy_search_properties_timeout = setTimeout(function() { tenancy_perform_property_search(); }, 400);
    });

    $('body').on('click', '#tenancy_search_property_results ul li a', function(e)
    {
        e.preventDefault();

        tenancy_selected_properties = []; // reset to only allow one property for now
        tenancy_selected_properties.push({ id: $(this).attr('href'), post_title: $(this).text(), owner_id: $(this).attr('data-tenancy-owner-id'), owner_name: $(this).attr('data-tenancy-owner-name') });

        $('#tenancy_search_property_results').html('');
        $('#tenancy_search_property_results').hide();

        $('#tenancy_property_search').val('');

        tenancy_update_selected_properties();
    });

    $('body').on('click', 'a.tenancy-remove-property', function(e)
    {
        e.preventDefault();

        var property_id = $(this).attr('href');

        for (var key in tenancy_selected_properties) 
        {
            if (tenancy_selected_properties[key].id == property_id ) 
            {
                tenancy_selected_properties.splice(key, 1);
            }
        }

        tenancy_update_selected_properties();
    });
});

function tenancy_perform_property_search()
{
    var keyword = jQuery('#tenancy_property_search').val();

    if (keyword.length == 0)
    {
        jQuery('#tenancy_search_property_results').html('');
        jQuery('#tenancy_search_property_results').hide();
        return false;
    }

    if (keyword.length < 3)
    {
        jQuery('#tenancy_search_property_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
        jQuery('#tenancy_search_property_results').show();
        return false;
    }

    var data = {
        action:         'propertyhive_search_properties',
        keyword:        keyword,
        department:     'residential-lettings',
        security:       '<?php echo esc_js(wp_create_nonce( 'search-properties' )); ?>',
    };
    jQuery.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
    {
        if (response == '' || response.length == 0)
        {
            jQuery('#tenancy_search_property_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
        }
        else
        {
            jQuery('#tenancy_search_property_results').html('<ul style="margin:0; padding:0;"></ul>');
            for ( var i in response )
            {
                jQuery('#tenancy_search_property_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;" data-tenancy-owner-id="' + response[i].owner_id + '" data-tenancy-owner-name="' + response[i].owner_name + '">' + response[i].post_title + '</a></li>');
            }
        }
        jQuery('#tenancy_search_property_results').show();
    });
}

function tenancy_update_selected_properties()
{
    jQuery('#_property_id').val('');

    if ( tenancy_selected_properties.length > 0 )
    {
        jQuery('#tenancy_selected_properties').html('<ul></ul>');
        for ( var i in tenancy_selected_properties )
        {
            jQuery('#tenancy_selected_properties ul').append('<li><a href="' + tenancy_selected_properties[i].id + '" class="tenancy-remove-property" style="color:inherit; text-decoration:none;" data-tenancy-owner-id="' + tenancy_selected_properties[i].owner_id + '" data-tenancy-owner-name="' + tenancy_selected_properties[i].owner_name + '"><span class="dashicons dashicons-no-alt"></span></a> ' + tenancy_selected_properties[i].post_title + '</li>');

            jQuery('#_property_id').val(tenancy_selected_properties[i].id);
        }
        jQuery('#tenancy_selected_properties').show();
    }
    else
    {
        jQuery('#tenancy_selected_properties').html('');
        jQuery('#tenancy_selected_properties').hide();
    }

    jQuery('#_property_id').trigger('change');
}

</script>
<?php
        }

        do_action('propertyhive_tenancy_property_fields');
	    
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
