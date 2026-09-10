<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Appraisal_Property_Owner; preserving the existing PH_* class name is required for plugin and extension compatibility.
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
            
                <label>' . esc_html__('Name', 'propertyhive') . '</label>
                
                <a 
                    href="' . esc_url(get_edit_post_link($property_owner_contact_id, '')) . '" 
                    data-appraisal-property-owner-id="' . (int)$property_owner_contact_id . '" 
                    data-appraisal-property-owner-name="' . esc_attr( get_the_title($property_owner_contact_id) ) . '" 
                    data-appraisal-property-owner-address-name-number="' . esc_attr( $contact->address_name_number ) . '" 
                    data-appraisal-property-owner-address-street="' . esc_attr( $contact->address_street ) . '" 
                    data-appraisal-property-owner-address-two="' . esc_attr( $contact->address_two ) . '" 
                    data-appraisal-property-owner-address-three="' . esc_attr( $contact->address_three ) . '" 
                    data-appraisal-property-owner-address-four="' . esc_attr( $contact->address_four ) . '" 
                    data-appraisal-property-owner-address-postcode="' . esc_attr( $contact->address_postcode ) . '" 
                    data-appraisal-property-owner-address-country="' . esc_attr( $contact->address_country ) . '" 
                >' . esc_html( get_the_title($property_owner_contact_id) ) . '</a>
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . esc_html(__('Telephone Number', 'propertyhive')) . '</label>
                
                ' . esc_html($contact->telephone_number) . '
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . esc_html(__('Email Address', 'propertyhive')) . '</label>
                
                <a href="mailto:' . esc_attr($contact->email_address) . '">' . esc_html($contact->email_address) . '</a>
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . esc_html(__('Correspondence Address', 'propertyhive')) . '</label>
                
                ' . wp_kses( $contact->get_formatted_full_address('<br>'), array( 'br' => array() ) ) . '
                
            </p>';
        }
        else
        {
            echo '<div id="appraisal_property_owner_search_existing">';

                echo '<p class="form-field">
                
                    <label for="appraisal_property_owner_search">' . esc_html(__('Search Contacts', 'propertyhive')) . '</label>
                    
                    <span style="position:relative;">

                        <input type="text" name="appraisal_property_owner_search" id="appraisal_property_owner_search" style="width:100%;" placeholder="' . esc_attr(__( 'Search Existing Contacts', 'propertyhive' )) . '..." autocomplete="false">

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
<?php
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only contact prefill; submission is guarded separately.
$prefill_contact = isset( $_GET['property_owner_contact_id'] ) && is_string( $_GET['property_owner_contact_id'] ) ? absint( $_GET['property_owner_contact_id'] ) : 0;
if ( $prefill_contact > 0 && 'contact' === get_post_type( $prefill_contact ) && current_user_can( 'edit_post', $prefill_contact ) ) { ?>
appraisal_selected_property_owners.push(<?php echo wp_json_encode( array( 'id' => $prefill_contact, 'post_title' => get_the_title( $prefill_contact ) ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>);
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
            e.preventDefault();
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
            $('#appraisal_search_property_owner_results').html('<div style="padding:10px;"><?php echo esc_html__( 'Enter', 'propertyhive' ); ?> ' + (3 - keyword.length ) + ' <?php echo esc_html__( 'more characters', 'propertyhive' ); ?>...</div>');
            $('#appraisal_search_property_owner_results').show();
            return false;
        }

        var data = {
            action:         'propertyhive_search_contacts',
            keyword:        keyword,
            security:       '<?php echo esc_js(wp_create_nonce( 'search-contacts' )); ?>',
        };
        $.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
        {
            if (response == '' || response.length == 0)
            {
                $('#appraisal_search_property_owner_results').empty().append($('<div>').css('padding', '10px').text(<?php echo wp_json_encode( __( 'No results found for', 'propertyhive' ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?> + " '" + keyword + "'"));
            }
            else
            {
                $('#appraisal_search_property_owner_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in response )
                {
                    var owner_link = $('<a>').attr('href', response[i].ID).css({color:'#666', display:'block', padding:'7px 10px', background:'#FFF', borderBottom:'1px solid #DDD', textDecoration:'none'});
                    owner_link.attr('data-appraisal-property-owner-name', response[i].post_title);
                    ['name_number', 'street', 'two', 'three', 'four', 'postcode', 'country'].forEach(function(part) { owner_link.attr('data-appraisal-property-owner-address-' + part.replace('_', '-'), response[i]['address_' + part]); });
                    owner_link.append($('<strong>').text(response[i].post_title));
                    var owner_summary = $('<small>').css({color:'#999', paddingTop:'1px', display:'block', lineHeight:'1.5em'});
                    [response[i].address_full_formatted, response[i].telephone_number, response[i].email_address].forEach(function(value) { if (value) { if (owner_summary.contents().length) { owner_summary.append('<br>'); } owner_summary.append(document.createTextNode(value)); } });
                    owner_link.append(owner_summary);
                    $('#appraisal_search_property_owner_results ul').append($('<li>').css({margin:0,padding:0}).append(owner_link));
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
            var selected_owner = appraisal_selected_property_owners[i];
            var remove_owner = jQuery('<a>').attr({href:selected_owner.id, 'data-appraisal-property-owner-id':selected_owner.id, 'data-appraisal-property-owner-name':selected_owner.post_title}).addClass('appraisal-remove-property-owner').css({color:'inherit',textDecoration:'none'}).append(jQuery('<span>').addClass('dashicons dashicons-no-alt'));
            ['name_number', 'street', 'two', 'three', 'four', 'postcode', 'country'].forEach(function(part) { remove_owner.attr('data-appraisal-property-owner-address-' + part.replace('_', '-'), selected_owner['address_' + part]); });
            jQuery('#appraisal_selected_property_owners ul').append(jQuery('<li>').append(remove_owner).append(document.createTextNode(' ' + selected_owner.post_title)));

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
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['post_ID'] ) || ! is_scalar( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== (int) $post_id ) {
            return;
        }

        $request_post = wp_unslash( $_POST );
        $input = array();
        foreach ( array( '_appraisal_property_owner_create_new', '_property_owner_contact_ids', '_property_owner_name', '_property_owner_telephone_number', '_property_owner_email_address', '_property_owner_address_name_number', '_property_owner_address_street', '_property_owner_address_two', '_property_owner_address_three', '_property_owner_address_four', '_property_owner_address_postcode' ) as $key )
        {
            if ( isset( $request_post[ $key ] ) && ! is_string( $request_post[ $key ] ) ) { return; }
            $input[ $key ] = isset( $request_post[ $key ] ) ? sanitize_text_field( $request_post[ $key ] ) : '';
        }
        $selected_contact = absint( $input['_property_owner_contact_ids'] );
        if ( empty( $input['_appraisal_property_owner_create_new'] ) && ! empty( $input['_property_owner_contact_ids'] ) && ( ! ctype_digit( $input['_property_owner_contact_ids'] ) || 'contact' !== get_post_type( $selected_contact ) || ! current_user_can( 'edit_post', $selected_contact ) ) ) { return; }
        if ( ! empty( $input['_appraisal_property_owner_create_new'] ) )
        {
            $contact_type = get_post_type_object( 'contact' );
            if ( ! $contact_type || ! current_user_can( $contact_type->cap->create_posts ) ) { return; }
        }

        global $wpdb;

        if ( isset($input['_appraisal_property_owner_create_new']) && !empty($input['_appraisal_property_owner_create_new']) )
        {
            // we're created a new property owner on submission
            if (!empty($input['_property_owner_name']))
            {
                // Need to create contact
                $contact_post = array(
                    'post_title'    => wp_slash( $input['_property_owner_name'] ),
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

                    $ph_contact_telephone_value = ( isset( $input['_property_owner_telephone_number'] ) && is_string( $input['_property_owner_telephone_number'] ) ) ? sanitize_text_field( $input['_property_owner_telephone_number'] ) : '';
                    update_post_meta( $contact_post_id, '_telephone_number', wp_slash( $ph_contact_telephone_value ) );
                    update_post_meta( $contact_post_id, '_telephone_number_clean',  ph_clean(ph_clean_telephone_number($ph_contact_telephone_value)) );

                    $ph_contact_email_value = ( isset( $input['_property_owner_email_address'] ) && is_string( $input['_property_owner_email_address'] ) ) ? sanitize_text_field( $input['_property_owner_email_address'] ) : '';
                    update_post_meta( $contact_post_id, '_email_address', str_replace(" ", "", wp_slash( $ph_contact_email_value )) );

                    update_post_meta( $contact_post_id, '_address_name_number', wp_slash( isset( $input['_property_owner_address_name_number'] ) && is_string( $input['_property_owner_address_name_number'] ) ? sanitize_text_field( $input['_property_owner_address_name_number'] ) : '' ) );
                    update_post_meta( $contact_post_id, '_address_street', wp_slash( isset( $input['_property_owner_address_street'] ) && is_string( $input['_property_owner_address_street'] ) ? sanitize_text_field( $input['_property_owner_address_street'] ) : '' ) );
                    update_post_meta( $contact_post_id, '_address_two', wp_slash( isset( $input['_property_owner_address_two'] ) && is_string( $input['_property_owner_address_two'] ) ? sanitize_text_field( $input['_property_owner_address_two'] ) : '' ) );
                    update_post_meta( $contact_post_id, '_address_three', wp_slash( isset( $input['_property_owner_address_three'] ) && is_string( $input['_property_owner_address_three'] ) ? sanitize_text_field( $input['_property_owner_address_three'] ) : '' ) );
                    update_post_meta( $contact_post_id, '_address_four', wp_slash( isset( $input['_property_owner_address_four'] ) && is_string( $input['_property_owner_address_four'] ) ? sanitize_text_field( $input['_property_owner_address_four'] ) : '' ) );
                    update_post_meta( $contact_post_id, '_address_postcode', wp_slash( isset( $input['_property_owner_address_postcode'] ) && is_string( $input['_property_owner_address_postcode'] ) ? sanitize_text_field( $input['_property_owner_address_postcode'] ) : '' ) );

                    update_post_meta( $post_id, '_property_owner_contact_id', $contact_post_id );
                }
                
            }
        }
        else
        {
            if ( isset($input['_property_owner_contact_ids']) && !empty($input['_property_owner_contact_ids']) )
            {
                update_post_meta( $post_id, '_property_owner_contact_id', ph_clean($input['_property_owner_contact_ids']) );

                $existing_contact_types = get_post_meta( $input['_property_owner_contact_ids'], '_contact_types', TRUE );
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
                update_post_meta( $input['_property_owner_contact_ids'], '_contact_types', $existing_contact_types );
            }
        }
    }

}
