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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Offer_Property; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Offer_Property {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $property_id = get_post_meta( $post->ID, '_property_id', true );

        // Read-only property preselection; the relationship is saved only by the nonce-checked save method.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin prefill link only, without persistent changes.
        $prefill_id = isset( $_GET['property_id'] ) && is_string( $_GET['property_id'] ) && ctype_digit( sanitize_text_field( wp_unslash( $_GET['property_id'] ) ) ) ? absint( $_GET['property_id'] ) : 0;
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post->ID ) || get_post_type( $post->ID ) !== 'offer' || get_post_type( $prefill_id ) !== 'property' || ! current_user_can( 'read_post', $prefill_id ) ) {
            $prefill_id = 0;
        }


        if ( !empty($property_id) )
        {
            $property = new PH_Property((int)$property_id);

            echo '<p class="form-field">
            
                <label>' . esc_html(__('Address', 'propertyhive')) . '</label>
                
                <a href="' . esc_url(get_edit_post_link($property_id, '')) . '">' . esc_html($property->get_formatted_full_address()) . '</a>' . ( !in_array($property->post_status, array('trash', 'archive')) ? ' (<a href="' . esc_url(get_permalink($property_id)) . '" target="_blank">'. esc_html__('View On Website', 'propertyhive') . '</a>)' : '' ) . '
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . esc_html( ( $property->department == 'residential-lettings' || ph_get_custom_department_based_on($property->department) == 'residential-lettings' ) ? __('Landlord', 'propertyhive') : __('Owner', 'propertyhive') ) . '</label>';

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
                    echo '<a href="' . esc_url(get_edit_post_link($owner_contact_id, '')) . '">' . esc_html(get_the_title($owner_contact_id)) . '</a><br>';
                    echo esc_html(__('Telephone', 'propertyhive')) . ': ' . ( ( $owner->telephone_number != '' ) ? esc_html($owner->telephone_number) : '-' ) . '<br>';
                    echo esc_html(__('Email', 'propertyhive')) . ': ' . ( ( $owner->email_address != '' ) ? '<a href="mailto:' . esc_attr($owner->email_address) . '">' . esc_html($owner->email_address) . '</a>' : '-' );
                    echo '<br><br>';
                }
            }
            else
            {
                echo esc_html(__('No', 'propertyhive') . ' ' . ( ( $property->department == 'residential-lettings' || ph_get_custom_department_based_on($property->department) == 'residential-lettings' ) ? __('landlord', 'propertyhive') : __('owner', 'propertyhive') ) . ' ' . __('specified', 'propertyhive'));
            }
                
            echo '</p>';

            do_action('propertyhive_property_search_meta_box_details', $property_id);
            do_action('propertyhive_offer_property_search_meta_box_details', $post->ID, $property_id);
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
<?php if ( $prefill_id ) { $property = new PH_Property( $prefill_id ); ?>
offer_selected_properties[<?php echo (int) $prefill_id; ?>] = ({ post_title: <?php echo wp_json_encode( $property->get_formatted_full_address(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?> });
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
            $('#offer_search_property_results').html('<div style="padding:10px;"><?php echo esc_js( esc_html__( 'Enter', 'propertyhive' ) ); ?> ' + (3 - keyword.length ) + ' <?php echo esc_js( esc_html__( 'more characters', 'propertyhive' ) ); ?>...</div>');
            $('#offer_search_property_results').show();
            return false;
        }

        var data = {
            action:         'propertyhive_search_properties',
            keyword:        keyword,
            department:     'residential-sales|commercial~forsale',
            security:       '<?php echo esc_js(wp_create_nonce( 'search-properties' )); ?>',
        };
        $.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
        {
            if (response == '' || response.length == 0)
            {
                jQuery('#offer_search_property_results').empty().append(jQuery('<div style="padding:10px;"></div>').text(<?php echo wp_json_encode( __( 'No results found for', 'propertyhive' ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?> + " '" + keyword + "'"));
            }
            else
            {
                $('#offer_search_property_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in response )
                {
                    if (!/^[0-9]+$/.test(String(response[i].ID))) { continue; }
                    var link = jQuery('<a style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;"></a>').attr('href', String(response[i].ID)).text(response[i].post_title);
                    jQuery('#offer_search_property_results ul').append(jQuery('<li style="margin:0; padding:0;"></li>').append(link));
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
            $.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response)
            {
                if (response != '')
                {
                    var solicitor_data = jQuery.parseJSON( response );

                    jQuery('#offer_selected_property_owner_solicitors').html('<ul></ul>');

                    if (!/^[0-9]+$/.test(String(solicitor_data.id))) { return; }
                    var remove = jQuery('<a class="offer-remove-property_owner-solicitor" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a>').attr('href', String(solicitor_data.id));
                    jQuery('#offer_selected_property_owner_solicitors ul').append(jQuery('<li></li>').append(remove).append(document.createTextNode(' ' + solicitor_data.name)));

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
    jQuery('#_property_id').val('');

    if ( Object.keys(offer_selected_properties).length > 0 )
    {
        jQuery('#offer_selected_properties').html('<ul></ul>');
        for ( var i in offer_selected_properties )
        {
            var remove = jQuery('<a class="offer-remove-property" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a>').attr('href', String(i));
            jQuery('#offer_selected_properties ul').append(jQuery('<li></li>').append(remove).append(document.createTextNode(' ' + offer_selected_properties[i].post_title)));

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
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['post_ID'] ) || ! is_scalar( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== (int) $post_id ) {
            return;
        }

        global $wpdb;

        if ( get_post_type( $post_id ) !== 'offer' || ! isset( $_POST['_property_id'] ) || ! is_string( $_POST['_property_id'] ) || ! ctype_digit( sanitize_text_field( wp_unslash( $_POST['_property_id'] ) ) ) ) {
            return;
        }
        $property_id = absint( $_POST['_property_id'] );
        if ( $property_id && 'property' === get_post_type( $property_id ) && current_user_can( 'read_post', $property_id ) ) {
            update_post_meta( $post_id, '_property_id', $property_id );
        }
    }

}
