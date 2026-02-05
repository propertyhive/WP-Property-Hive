<?php
/**
 * Viewing Property Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Viewing_Property
 */
class PH_Meta_Box_Viewing_Property {

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
                    echo '<a href="' . esc_url(get_edit_post_link($owner_contact_id, '')) . '" data-viewing-owner-id="' . (int)$owner_contact_id . '" data-viewing-owner-name="' . esc_attr(get_the_title($owner_contact_id, '')) . '">' . esc_html(get_the_title($owner_contact_id)) . '</a><br>';
                    echo esc_html(__('Telephone', 'propertyhive')) . ': ' . ( ( $owner->telephone_number != '' ) ? esc_html($owner->telephone_number) : '-' ) . '<br>';
                    echo esc_html(__('Email', 'propertyhive')) . ': ' . ( ( $owner->email_address != '' ) ? '<a href="mailto:' . esc_attr($owner->email_address) . '">' . esc_html($owner->email_address) . '</a>' : '-' );
                    echo '<br><br>';
                }
            }
            else
            {
                echo esc_html(__('No', 'propertyhive') . ' ' . ( ( $property->department == 'residential-lettings' ) ? __('landlord', 'propertyhive') : __('owner', 'propertyhive') ) . ' ' . __('specified', 'propertyhive'));
            }
                
            echo '</p>';

            do_action('propertyhive_property_search_meta_box_details', $property_id);
            do_action('propertyhive_viewing_property_search_meta_box_details', $post->ID, $property_id);
        }
        else
        {
echo '<p class="form-field">
            
                <label for="viewing_property_search">' . esc_html(__('Search Properties', 'propertyhive')) . '</label>
                
                <span style="position:relative;">

                    <input type="text" name="viewing_property_search" id="viewing_property_search" style="width:100%;" placeholder="' . esc_attr(__( 'Search Properties', 'propertyhive' )) . '..." autocomplete="false">

                    <div id="viewing_search_property_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="viewing_selected_properties" style="display:none;"></div>

                </span>
                
            </p>';

            echo '<input type="hidden" name="_property_id" id="_property_id" value="">';
?>
<script>

var viewing_selected_properties = [];
<?php if (isset($_GET['property_id']) && $_GET['property_id'] != '') { $property = new PH_Property((int)$_GET['property_id']); ?>
viewing_selected_properties.push({ id: <?php echo (int)$_GET['property_id']; ?>, post_title: '<?php echo esc_js($property->get_formatted_full_address()); ?>' });
<?php } ?>
var viewing_search_properties_timeout;
var viewing_search_properties_xhr = jQuery.ajax({});

jQuery(document).ready(function($)
{
    viewing_update_selected_properties();
    
    $('#viewing_property_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#viewing_property_search').keydown(function()
    {
        clearTimeout(viewing_search_properties_timeout);
    });

    $('#viewing_property_search').keyup(function()
    {
        viewing_search_properties_timeout = setTimeout(function() { viewing_perform_property_search(); }, 400);
    });

    $('body').on('click', '#viewing_search_property_results ul li a', function(e)
    {
        e.preventDefault();

        viewing_selected_properties = []; // reset to only allow one property for now
        viewing_selected_properties.push({ id: $(this).attr('href'), post_title: $(this).text(), owner_id: $(this).attr('data-viewing-owner-id'), owner_name: $(this).attr('data-viewing-owner-name') });

        $('#viewing_search_property_results').html('');
        $('#viewing_search_property_results').hide();

        $('#viewing_property_search').val('');

        viewing_update_selected_properties();
    });

    $('body').on('click', 'a.viewing-remove-property', function(e)
    {
        e.preventDefault();

        var property_id = $(this).attr('href');

        for (var key in viewing_selected_properties) 
        {
            if (viewing_selected_properties[key].id == property_id ) 
            {
                viewing_selected_properties.splice(key, 1);
            }
        }

        viewing_update_selected_properties();
    });
});

function viewing_perform_property_search()
{
    var keyword = jQuery('#viewing_property_search').val();

    if (keyword.length == 0)
    {
        jQuery('#viewing_search_property_results').html('');
        jQuery('#viewing_search_property_results').hide();
        return false;
    }

    if (keyword.length < 3)
    {
        jQuery('#viewing_search_property_results').html('<div style="padding:10px;"><?php echo esc_html__( 'Enter', 'propertyhive' ); ?> ' + (3 - keyword.length ) + ' <?php echo esc_html__( 'more characters', 'propertyhive' ); ?>...</div>');
        jQuery('#viewing_search_property_results').show();
        return false;
    }

    jQuery('#viewing_search_property_results').html('<div style="padding:10px;"><?php echo esc_html__( 'Loading...', 'propertyhive' ); ?></div>');
    jQuery('#viewing_search_property_results').show();

    var data = {
        action:         'propertyhive_search_properties',
        keyword:        keyword,
        security:       '<?php echo esc_js(wp_create_nonce( 'search-properties' )); ?>',
    };
    viewing_search_properties_xhr.abort(); // cancel previous request
    viewing_search_properties_xhr = jQuery.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
    {
        if (response == '' || response.length == 0)
        {
            jQuery('#viewing_search_property_results').html('<div style="padding:10px;"><?php echo esc_html__( 'No results found for', 'propertyhive' ); ?> \'' + keyword + '\'</div>');
        }
        else
        {
            jQuery('#viewing_search_property_results').html('<ul style="margin:0; padding:0;"></ul>');
            for ( var i in response )
            {
                jQuery('#viewing_search_property_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;" data-viewing-owner-id="' + response[i].owner_id + '" data-viewing-owner-name="' + response[i].owner_name + '">' + response[i].post_title + '</a></li>');
            }
        }
        jQuery('#viewing_search_property_results').show();
    });
}

function viewing_update_selected_properties()
{
    jQuery('#_property_id').val('');

    if ( viewing_selected_properties.length > 0 )
    {
        jQuery('#viewing_selected_properties').html('<ul></ul>');
        for ( var i in viewing_selected_properties )
        {
            jQuery('#viewing_selected_properties ul').append('<li><a href="' + viewing_selected_properties[i].id + '" class="viewing-remove-property" style="color:inherit; text-decoration:none;" data-viewing-owner-id="' + viewing_selected_properties[i].owner_id + '" data-viewing-owner-name="' + viewing_selected_properties[i].owner_name + '"><span class="dashicons dashicons-no-alt"></span></a> ' + viewing_selected_properties[i].post_title + '</li>');

            jQuery('#_property_id').val(viewing_selected_properties[i].id);
        }
        jQuery('#viewing_selected_properties').show();
    }
    else
    {
        jQuery('#viewing_selected_properties').html('');
        jQuery('#viewing_selected_properties').hide();
    }

    jQuery('#_property_id').trigger('change');
}

</script>
<?php
        }

        do_action('propertyhive_viewing_property_fields');
	    
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
