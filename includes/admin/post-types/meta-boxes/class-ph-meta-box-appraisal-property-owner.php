<?php
/**
 * Appraisal Property Owner Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Appraisal_Property_Owner
 */
class PH_Meta_Box_Appraisal_Property_Owner {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $property_owner_contact_id = get_post_meta( $post->ID, '_property_owner_contact_id', true );

        if ( !empty($property_owner_contact_id) )
        {
            $contact = new PH_Contact($property_owner_contact_id);

            echo '<p class="form-field">
            
                <label>' . __('Name', 'propertyhive') . '</label>
                
                <a 
                    href="' . get_edit_post_link($property_owner_contact_id, '') . '" 
                    data-appraisal-property-owner-id="' . $property_owner_contact_id . '" 
                    data-appraisal-property-owner-name="' . esc_attr( get_the_title($property_owner_contact_id) ) . '" 
                    data-appraisal-property-owner-address-name-number="' . esc_attr( $contact->address_name_number ) . '" 
                    data-appraisal-property-owner-address-street="' . esc_attr( $contact->address_street ) . '" 
                    data-appraisal-property-owner-address-two="' . esc_attr( $contact->address_two ) . '" 
                    data-appraisal-property-owner-address-three="' . esc_attr( $contact->address_three ) . '" 
                    data-appraisal-property-owner-address-four="' . esc_attr( $contact->address_four ) . '" 
                    data-appraisal-property-owner-address-postcode="' . esc_attr( $contact->address_postcode ) . '" 
                    data-appraisal-property-owner-address-country="' . esc_attr( $contact->address_country ) . '" 
                >' . get_the_title($property_owner_contact_id) . '</a>
                
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
            
                <label>' . __('Correspondence Address', 'propertyhive') . '</label>
                
                ' . $contact->get_formatted_full_address('<br>') . '
                
            </p>';
        }
        else
        {
            echo '<div id="appraisal_property_owner_search_existing">';

                echo '<p class="form-field">
                
                    <label for="appraisal_property_owner_search">' . __('Search Contacts', 'propertyhive') . '</label>
                    
                    <span style="position:relative;">

                        <input type="text" name="appraisal_property_owner_search" id="appraisal_property_owner_search" style="width:100%;" placeholder="' . __( 'Search Existing Contacts', 'propertyhive' ) . '..." autocomplete="false">

                        <div id="appraisal_search_property_owner_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                        <div id="appraisal_selected_property_owners" style="display:none;"></div>

                    </span>
                    
                </p>

                <p class="form-field">
                
                    <label for="">&nbsp;</label>
                    
                    <a href="" class="create-appraisal-property-owner button">Create New Contact</a>
                    
                </p>';

                echo '<input type="hidden" name="_property_owner_contact_ids" id="_property_owner_contact_ids" value="">';

            echo '</div>';

            echo '<div id="appraisal_property_owner_create_new" style="display:none">';

                $args = array( 
                    'id' => '_property_owner_name', 
                    'label' => __( 'Name', 'propertyhive' ), 
                    'desc_tip' => false, 
                    'type' => 'text'
                );
                propertyhive_wp_text_input( $args );

                $args = array( 
                    'id' => '_property_owner_telephone_number', 
                    'label' => __( 'Telephone Number', 'propertyhive' ), 
                    'desc_tip' => false, 
                    'type' => 'text'
                );
                propertyhive_wp_text_input( $args );

                $args = array( 
                    'id' => '_property_owner_email_address', 
                    'label' => __( 'Email Address', 'propertyhive' ), 
                    'desc_tip' => false,
                    
                    'type' => 'email'
                );
                propertyhive_wp_text_input( $args );

                $args = array( 
                    'id' => '_property_owner_address_name_number', 
                    'label' => __( 'Building Name / Number', 'propertyhive' ), 
                    'desc_tip' => false, 
                    'placeholder' => __( 'e.g. Thistle Cottage, or Flat 10', 'propertyhive' ), 
                    'type' => 'text'
                );
                propertyhive_wp_text_input( $args );
                
                $args = array( 
                    'id' => '_property_owner_address_street', 
                    'label' => __( 'Street', 'propertyhive' ), 
                    'desc_tip' => false, 
                    'placeholder' => __( 'e.g. High Street', 'propertyhive' ), 
                    'type' => 'text',
                );
                propertyhive_wp_text_input( $args );
                
                $args = array( 
                    'id' => '_property_owner_address_two', 
                    'label' => __( 'Address Line 2', 'propertyhive' ), 
                    'desc_tip' => false, 
                    'type' => 'text'
                );
                propertyhive_wp_text_input( $args );
                
                $args = array( 
                    'id' => '_property_owner_address_three', 
                    'label' => __( 'Town / City', 'propertyhive' ), 
                    'desc_tip' => false, 
                    'type' => 'text'
                );
                propertyhive_wp_text_input( $args );
                
                $args = array( 
                    'id' => '_property_owner_address_four', 
                    'label' => __( 'County / State', 'propertyhive' ), 
                    'desc_tip' => false, 
                    'type' => 'text'
                );
                propertyhive_wp_text_input( $args );
                
                $args = array( 
                    'id' => '_property_owner_address_postcode', 
                    'label' => __( 'Postcode / Zip Code', 'propertyhive' ), 
                    'desc_tip' => false, 
                    'description' => 'Upon saving the appraisal a new contact record will be created with these details.',
                    'type' => 'text'
                );
                propertyhive_wp_text_input( $args );

                echo '<p class="form-field">
                
                    <label for="">&nbsp;</label>
                    
                    <a href="" class="create-appraisal-property-owner-cancel">Cancel and Search Existing Contacts</a>
                    
                </p>';

            echo '</div>';

            echo '<input type="hidden" name="_appraisal_property_owner_create_new" id="_appraisal_property_owner_create_new" value="">';
?>
<script>

var appraisal_selected_property_owners = [];
<?php if (isset($_GET['property_owner_contact_id']) && $_GET['property_owner_contact_id'] != '') { ?>
appraisal_selected_property_owners.push({ id: <?php echo (int)$_GET['property_owner_contact_id']; ?>, post_title: '<?php echo get_the_title((int)$_GET['property_owner_contact_id']); ?>' });
<?php } ?>

jQuery(document).ready(function($)
{
    appraisal_update_selected_property_owners();

    $('a.create-appraisal-property-owner').click(function(e)
    {
        e.preventDefault();

        $('#_appraisal_property_owner_create_new').val('1');

        $('#appraisal_property_owner_search_existing').hide();
        $('#appraisal_property_owner_create_new').fadeIn();
    });

    $('a.create-appraisal-property-owner-cancel').click(function(e)
    {
        e.preventDefault();

        $('#_appraisal_property_owner_create_new').val('');

        $('#appraisal_property_owner_create_new').hide();
        $('#appraisal_property_owner_search_existing').fadeIn();
        
        $('#appraisal_property_owner_search').focus();
    });

    $('#appraisal_property_owner_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#appraisal_property_owner_search').keyup(function()
    {
        var keyword = $(this).val();

        if (keyword.length == 0)
        {
            $('#appraisal_search_property_owner_results').html('');
            $('#appraisal_search_property_owner_results').hide();
            return false;
        }

        if (keyword.length < 3)
        {
            $('#appraisal_search_property_owner_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
            $('#appraisal_search_property_owner_results').show();
            return false;
        }

        var data = {
            action:         'propertyhive_search_contacts',
            keyword:        keyword,
            security:       '<?php echo wp_create_nonce( 'search-contacts' ); ?>',
        };
        $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
            if (response == '' || response.length == 0)
            {
                $('#appraisal_search_property_owner_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
            }
            else
            {
                $('#appraisal_search_property_owner_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in response )
                {
                    $('#appraisal_search_property_owner_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;" data-appraisal-property-owner-name="' + response[i].post_title + '" data-appraisal-property-owner-address-name-number="' + response[i].address_name_number + '" data-appraisal-property-owner-address-street="' + response[i].address_street + '" data-appraisal-property-owner-address-two="' + response[i].address_two + '" data-appraisal-property-owner-address-three="' + response[i].address_three + '" data-appraisal-property-owner-address-four="' + response[i].address_four + '" data-appraisal-property-owner-address-postcode="' + response[i].address_postcode + '" data-appraisal-property-owner-address-country="' + response[i].address_country + '"><strong>' + response[i].post_title + '</strong><small style="color:#999; padding-top:1px; display:block; line-height:1.5em">' + ( response[i].address_full_formatted != '' ? response[i].address_full_formatted + '<br>' : '' ) + ( response[i].telephone_number != '' ? response[i].telephone_number + '<br>' : '' ) + ( response[i].email_address != '' ? response[i].email_address : '' ) + '</small></a></li>');
                }
            }
            $('#appraisal_search_property_owner_results').show();
        });
    });

    $('body').on('click', '#appraisal_search_property_owner_results ul li a', function(e)
    {
        e.preventDefault();

        appraisal_selected_property_owners = []; // reset to only allow one owner for now
        appraisal_selected_property_owners.push( { 
            id: $(this).attr('href'), 
            post_title: $(this).attr('data-appraisal-property-owner-name'), 
            address_name_number: $(this).attr('data-appraisal-property-owner-address-name-number'), 
            address_street: $(this).attr('data-appraisal-property-owner-address-street'), 
            address_two: $(this).attr('data-appraisal-property-owner-address-two'), 
            address_three: $(this).attr('data-appraisal-property-owner-address-three'), 
            address_four: $(this).attr('data-appraisal-property-owner-address-four'), 
            address_postcode: $(this).attr('data-appraisal-property-owner-address-postcode'), 
            address_country: $(this).attr('data-appraisal-property-owner-address-country'), 
        } );
        console.log(appraisal_selected_property_owners);
        $('#appraisal_search_property_owner_results').html('');
        $('#appraisal_search_property_owner_results').hide();

        $('#appraisal_property_owner_search').val('');

        appraisal_update_selected_property_owners();
    });

    $('body').on('click', 'a.appraisal-remove-property-owner', function(e)
    {
        e.preventDefault();

        var property_owner_id = $(this).attr('href');

        for (var key in appraisal_selected_property_owners) 
        {
            if (appraisal_selected_property_owners[key].id == property_owner_id ) 
            {
                appraisal_selected_property_owners.splice(key, 1);
            }
        }

        appraisal_update_selected_property_owners();
    });
});

function appraisal_update_selected_property_owners()
{
    jQuery('#_property_owner_contact_ids').val('');

    if ( appraisal_selected_property_owners.length > 0 )
    {
        jQuery('#appraisal_selected_property_owners').html('<ul></ul>');
        for ( var i in appraisal_selected_property_owners )
        {
            jQuery('#appraisal_selected_property_owners ul').append('<li><a href="' + appraisal_selected_property_owners[i].id + '" class="appraisal-remove-property-owner" data-appraisal-property-owner-id="' + appraisal_selected_property_owners[i].id + '" data-appraisal-property-owner-name="' + appraisal_selected_property_owners[i].post_title + '" data-appraisal-property-owner-address-name-number="' + appraisal_selected_property_owners[i].address_name_number + '" data-appraisal-property-owner-address-street="' + appraisal_selected_property_owners[i].address_street + '" data-appraisal-property-owner-address-two="' + appraisal_selected_property_owners[i].address_two + '" data-appraisal-property-owner-address-three="' + appraisal_selected_property_owners[i].address_three + '" data-appraisal-property-owner-address-four="' + appraisal_selected_property_owners[i].address_four + '" data-appraisal-property-owner-address-postcode="' + appraisal_selected_property_owners[i].address_postcode + '" data-appraisal-property-owner-address-country="' + appraisal_selected_property_owners[i].address_country + '" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + appraisal_selected_property_owners[i].post_title + '</li>');

            jQuery('#_property_owner_contact_ids').val(appraisal_selected_property_owners[i].id);
        }
        jQuery('#appraisal_selected_property_owners').show();
    }
    else
    {
        jQuery('#appraisal_selected_property_owners').html('');
        jQuery('#appraisal_selected_property_owners').hide();
    }

    jQuery('#_property_owner_contact_ids').trigger('change');
}

</script>
<?php
        }

        do_action('propertyhive_appraisal_property_owner_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        if ( isset($_POST['_appraisal_property_owner_create_new']) && !empty($_POST['_appraisal_property_owner_create_new']) )
        {
            // we're created a new property owner on submission
            if (!empty($_POST['_property_owner_name']))
            {
                // Need to create contact
                $contact_post = array(
                    'post_title'    => ph_clean($_POST['_property_owner_name']),
                    'post_content'  => '',
                    'post_type'     => 'contact',
                    'post_status'   => 'publish',
                    'comment_status'    => 'closed',
                    'ping_status'    => 'closed',
                );
                        
                // Insert the post into the database
                $contact_post_id = wp_insert_post( $contact_post );

                if ( is_wp_error($contact_post_id) || $contact_post_id == 0 )
                {
                    // Failed. Don't really know at the moment how to handle this

                    $return = array('error' => 'Failed to create contact post. Please try again');
                    //echo json_encode( $return );
                    //die();
                }
                else
                {
                    // Successfully added contact post
                    update_post_meta( $contact_post_id, '_contact_types', array('potentialowner') );

                    update_post_meta( $contact_post_id, '_telephone_number', ph_clean($_POST['_property_owner_telephone_number']) );
                    update_post_meta( $contact_post_id, '_telephone_number_clean',  ph_clean($_POST['_property_owner_telephone_number'], true) );

                    update_post_meta( $contact_post_id, '_email_address', str_replace(" ", "", ph_clean($_POST['_property_owner_email_address'])) );

                    update_post_meta( $contact_post_id, '_address_name_number', ph_clean($_POST['_property_owner_address_name_number']) );
                    update_post_meta( $contact_post_id, '_address_street', ph_clean($_POST['_property_owner_address_street']) );
                    update_post_meta( $contact_post_id, '_address_two', ph_clean($_POST['_property_owner_address_two']) );
                    update_post_meta( $contact_post_id, '_address_three', ph_clean($_POST['_property_owner_address_three']) );
                    update_post_meta( $contact_post_id, '_address_four', ph_clean($_POST['_property_owner_address_four']) );
                    update_post_meta( $contact_post_id, '_address_postcode', ph_clean($_POST['_property_owner_address_postcode']) );

                    update_post_meta( $post_id, '_property_owner_contact_id', $contact_post_id );
                }
                
            }
        }
        else
        {
            if ( isset($_POST['_property_owner_contact_ids']) && !empty($_POST['_property_owner_contact_ids']) )
            {
                update_post_meta( $post_id, '_property_owner_contact_id', ph_clean($_POST['_property_owner_contact_ids']) );

                $existing_contact_types = get_post_meta( $_POST['_property_owner_contact_ids'], '_contact_types', TRUE );
                if ( !is_array($existing_contact_types) && ($existing_contact_types == '' || $existing_contact_types === FALSE) )
                {
                    $existing_contact_types = array();
                }
                elseif ( !is_array($existing_contact_types) && $existing_contact_types != '' )
                {
                    $existing_contact_types = array($existing_contact_types);
                }
                if ( !in_array('potentialowner', $existing_contact_types) )
                {
                    $existing_contact_types[] = 'potentialowner';
                }
                update_post_meta( $_POST['_property_owner_contact_ids'], '_contact_types', $existing_contact_types );
            }
        }
    }

}
