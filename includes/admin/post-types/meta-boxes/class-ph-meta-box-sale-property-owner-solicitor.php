<?php
/**
 * Sale Property_Owner Solicitor Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Sale_Property_Owner_Solicitor
 */
class PH_Meta_Box_Sale_Property_Owner_Solicitor {

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

            $fields = array(
                'name' => array(
                    'label' => __('Name', 'propertyhive'),
                    'value' => '<a href="' . get_edit_post_link($property_owner_solicitor_contact_id, '') . '">' . get_the_title($property_owner_solicitor_contact_id) . ( $contact->company_name != '' && $contact->company_name != get_the_title($property_owner_solicitor_contact_id) ? ' (' . $contact->company_name . ')' : '' ) . '</a>',
                ),
                'telephone_number' => array(
                    'label' => __('Telephone Number', 'propertyhive'),
                    'value' => $contact->telephone_number,
                ),
                'email_address' => array(
                    'label' => __('Email Address', 'propertyhive'),
                    'value' => '<a href="mailto:' . $contact->email_address . '">' .  $contact->email_address  . '</a>',
                ),
            );

            $fields = apply_filters( 'propertyhive_sale_property_owner_solictor_fields', $fields, $post->ID, $property_owner_solicitor_contact_id );

            foreach ( $fields as $key => $field )
            {
                echo '<p class="form-field ' . esc_attr($key) . '">
            
                    <label>' . esc_html($field['label']) . '</label>
                    
                    ' . $field['value'] . '
                    
                </p>';
            }

            echo '<p class="form-field">
            
                <label></label>
                
                <a class="button" href="' . wp_nonce_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ), '1', 'remove_property_owner_solicitor' ) . '">' .  __( 'Remove Solicitor', 'propertyhive' ) . '</a>
                
            </p>';
        }
        else
        {
            echo '<p class="form-field">
            
                <label for="sale_property_owner_solicitor_search">' . __('Search Solicitors', 'propertyhive') . '</label>
                
                <span style="position:relative;">

                    <input type="text" name="sale_property_owner_solicitor_search" id="sale_property_owner_solicitor_search" style="width:100%;" placeholder="' . __( 'Search Existing Contacts', 'propertyhive' ) . '..." autocomplete="false">

                    <div id="sale_search_property_owner_solicitor_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="sale_selected_property_owner_solicitors" style="display:none;"></div>

                </span>
                
            </p>';

            echo '<input type="hidden" name="_property_owner_solicitor_contact_ids" id="_property_owner_solicitor_contact_ids" value="">';
?>
<script>

var sale_selected_property_owner_solicitors = [];

jQuery(document).ready(function($)
{
    sale_update_selected_property_owner_solicitors();

    $('#sale_property_owner_solicitor_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#sale_property_owner_solicitor_search').keyup(function()
    {
        var keyword = $(this).val();

        if (keyword.length == 0)
        {
            $('#sale_search_property_owner_solicitor_results').html('');
            $('#sale_search_property_owner_solicitor_results').hide();
            return false;
        }

        if (keyword.length < 3)
        {
            $('#sale_search_property_owner_solicitor_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
            $('#sale_search_property_owner_solicitor_results').show();
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
                $('#sale_search_property_owner_solicitor_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
            }
            else
            {
                $('#sale_search_property_owner_solicitor_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in response )
                {
                    $('#sale_search_property_owner_solicitor_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;" data-property-owner-solicitor-name="' + response[i].post_title + '"><strong>' + response[i].post_title + '</strong><small style="color:#999; padding-top:1px; display:block; line-height:1.5em">' + ( response[i].address_full_formatted != '' ? response[i].address_full_formatted + '<br>' : '' ) + ( response[i].telephone_number != '' ? response[i].telephone_number + '<br>' : '' ) + ( response[i].email_address != '' ? response[i].email_address : '' ) + '</small></a></li>');
                }
            }
            $('#sale_search_property_owner_solicitor_results').show();
        });
    });

    $('body').on('click', '#sale_search_property_owner_solicitor_results ul li a', function(e)
    {
        e.preventDefault();

        sale_selected_property_owner_solicitors = []; // reset to only allow one property_owner for now
        sale_selected_property_owner_solicitors[$(this).attr('href')] = ({ post_title: $(this).attr('data-property-owner-solicitor-name') });

        $('#sale_search_property_owner_solicitor_results').html('');
        $('#sale_search_property_owner_solicitor_results').hide();

        $('#sale_property_owner_solicitor_search').val('');

        sale_update_selected_property_owner_solicitors();
    });

    $('body').on('click', 'a.sale-remove-property_owner-solicitor', function(e)
    {
        e.preventDefault();

        var property_owner_solicitor_id = $(this).attr('href');

        delete(sale_selected_property_owner_solicitors[property_owner_solicitor_id]);

        sale_update_selected_property_owner_solicitors();
    });
});

function sale_update_selected_property_owner_solicitors()
{
    jQuery('#_property_owner_contact_solicitor_ids').val();
    if ( Object.keys(sale_selected_property_owner_solicitors).length > 0 )
    {
        jQuery('#sale_selected_property_owner_solicitors').html('<ul></ul>');
        for ( var i in sale_selected_property_owner_solicitors )
        {
            jQuery('#sale_selected_property_owner_solicitors ul').append('<li><a href="' + i + '" class="sale-remove-property_owner-solicitor" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + sale_selected_property_owner_solicitors[i].post_title + '</li>');

            jQuery('#_property_owner_solicitor_contact_ids').val(i);
        }
        jQuery('#sale_selected_property_owner_solicitors').show();
    }
    else
    {
        jQuery('#sale_selected_property_owner_solicitors').html('');
        jQuery('#sale_selected_property_owner_solicitors').hide();
    }
}

</script>
<?php
        }

        do_action('propertyhive_sale_property_owner_solicitor_fields');
	    
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
