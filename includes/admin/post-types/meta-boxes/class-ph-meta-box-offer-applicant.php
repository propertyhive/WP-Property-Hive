<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Offer Applicant Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Offer_Applicant
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Offer_Applicant; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Offer_Applicant {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $applicant_contact_ids = array();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preselection in the authorized editor; selected IDs are checked as contacts below.
        $requested_applicants = isset( $_GET['applicant_contact_id'] ) && is_string( $_GET['applicant_contact_id'] ) ? sanitize_text_field( wp_unslash( $_GET['applicant_contact_id'] ) ) : '';
        if ( $requested_applicants !== '' )
        {
            $explode_applicant_contact_ids = explode('|', $requested_applicants);
            foreach ($explode_applicant_contact_ids as $explode_applicant_contact_id)
            {
                if ( get_post_type( (int)$explode_applicant_contact_id ) == 'contact' )
                {
                    $applicant_contact_ids[] = (int)$explode_applicant_contact_id;
                }
            }
        }
        else
        {
            $applicant_contact_ids = get_post_meta($post->ID, '_applicant_contact_id');
        }

        if ( $applicant_contact_ids == '' )
        {
            $applicant_contact_ids = array();
        }
        if ( !is_array($applicant_contact_ids) && $applicant_contact_ids != '' && $applicant_contact_ids != 0 )
        {
            $applicant_contact_ids = array($applicant_contact_ids);
        }

        if ( !empty($applicant_contact_ids) )
        {
            $i = 0;
            foreach ( $applicant_contact_ids as $applicant_contact_id )
            {
                $contact = new PH_Contact($applicant_contact_id);

                $fields = array(
                    'name' => array(
                        'label' => __('Name', 'propertyhive'),
                        'value' => '<a href="' . esc_url( get_edit_post_link($applicant_contact_id, '') ) . '" data-offer-applicant-id="' . esc_attr($applicant_contact_id) . '" data-offer-applicant-name="' . esc_attr(get_the_title($applicant_contact_id)) . '">' . esc_html(get_the_title($applicant_contact_id)) . '</a>',
                    ),
                    'telephone_number' => array(
                        'label' => __('Telephone Number', 'propertyhive'),
                        'value' => esc_html($contact->telephone_number),
                    ),
                    'email_address' => array(
                        'label' => __('Email Address', 'propertyhive'),
                        'value' => '<a href="mailto:' . esc_attr($contact->email_address) . '">' .  esc_html($contact->email_address)  . '</a>',
                    ),
                );
                echo '<input type="hidden" name="existing_offer_applicant" value="' . esc_attr($applicant_contact_id) . '">';

                $fields = apply_filters( 'propertyhive_offer_applicant_fields', $fields, $post->ID, $applicant_contact_id );

                $div_style = $i > 0 ? 'style="border-top:1px solid #ddd"' : '';
                echo $i > 0 ? '<div style="border-top:1px solid #ddd">' : '<div >';
                foreach ( $fields as $key => $field )
                {
                    echo '<p class="form-field ' . esc_attr($key) . '" >

                        <label>' . esc_html($field['label']) . '</label>

                        ';
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core field values are escaped above before the trusted PHP propertyhive_offer_applicant_fields filter, which intentionally permits extension HTML.
                    echo $field['value'];
                    echo '

                    </p>';
                }
                echo "</div>";
                ++$i;
            }
        }
        ?>
        <input type="hidden" name="_applicant_contact_ids" id="_applicant_contact_ids" value="<?php echo ( !empty($applicant_contact_ids) ? esc_attr(implode('|', $applicant_contact_ids )) : '' ); ?>">

        <div id="offer_applicant_search_existing">

            <p class="form-field">

                <label for="offer_applicant_search"><?php echo esc_html(__('Search Applicants', 'propertyhive')); ?></label>

                <span style="position:relative;">

                    <input type="text" name="offer_applicant_search" id="offer_applicant_search" style="width:100%;" placeholder="<?php echo esc_html(__( 'Search Existing Contacts', 'propertyhive' )); ?>..." autocomplete="false">

                    <div id="offer_search_applicant_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="offer_selected_applicants" style="display:none;"></div>

                </span>

            </p>

            <p class="form-field">

                <label for="">&nbsp;</label>

                <a href="" class="create-offer-applicant button"><?php echo esc_html__( 'Create New Applicant', 'propertyhive' ); ?></a>

            </p>

        </div>

        <div id="offer_applicant_create_new" style="display:none">
            <?php

            $args = array(
                'id' => '_applicant_name',
                'label' => __( 'Name', 'propertyhive' ),
                'desc_tip' => false,
                'description' => 'Upon booking a new applicant record will be created with these details.',
                'type' => 'text',
            );
            propertyhive_wp_text_input( $args );

            $args = array(
                'id' => '_applicant_telephone_number',
                'label' => __( 'Telephone Number', 'propertyhive' ),
                'desc_tip' => false,
                'type' => 'text',
            );
            propertyhive_wp_text_input( $args );

            $args = array(
                'id' => '_applicant_email_address',
                'label' => __( 'Email Address', 'propertyhive' ),
                'desc_tip' => false,
                'type' => 'email',
            );
            propertyhive_wp_text_input( $args );

            $args = array(
                'id' => '_applicant_address',
                'label' => __( 'Address', 'propertyhive' ),
                'desc_tip' => false,
            );
            propertyhive_wp_textarea_input( $args );
            ?>

            <p class="form-field">

                <label for="">&nbsp;</label>

                <a href="" class="create-offer-applicant-cancel"><?php echo esc_html__( 'Cancel and Search Existing Applicants', 'propertyhive' ); ?></a>

            </p>

        </div>

        <input type="hidden" name="_offer_applicant_create_new" id="_offer_applicant_create_new" value="">

        <script>

        function offer_applicant_escape(value) {
            return String(value == null ? '' : value).replace(/[&<>"']/g, function(character) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character];
            });
        }

        var offer_selected_applicants = [];
        <?php
            if ( $requested_applicants !== '' )
            {
                foreach ($applicant_contact_ids as $applicant_contact_id)
                {
                    ?>
                    offer_selected_applicants.push({ id: <?php echo (int)$applicant_contact_id; ?>, post_title: '<?php echo esc_js(get_the_title((int)$applicant_contact_id)); ?>' });
                    <?php
                }
            }
        ?>

        jQuery(document).ready(function($)
        {
            offer_update_selected_applicants();

            $('a.create-offer-applicant').click(function(e)
            {
                e.preventDefault();

                $('#_offer_applicant_create_new').val('1');

                $('#offer_applicant_search_existing').hide();
                $('#offer_applicant_create_new').fadeIn();
            });

            $('a.create-offer-applicant-cancel').click(function(e)
            {
                e.preventDefault();

                $('#_offer_applicant_create_new').val('');

                $('#offer_applicant_create_new').hide();
                $('#offer_applicant_search_existing').fadeIn();
            });

            $('#offer_applicant_search').on('keyup keypress', function(e)
            {
                var keyCode = e.charCode || e.keyCode || e.which;
                if (keyCode == 13)
                {
                    event.preventDefault();
                    return false;
                }
            });

            $('#offer_applicant_search').keyup(function()
            {
                var keyword = $(this).val();

                if (keyword.length == 0)
                {
                    $('#offer_search_applicant_results').html('');
                    $('#offer_search_applicant_results').hide();
                    return false;
                }

                if (keyword.length < 3)
                {
                    $('#offer_search_applicant_results').html('<div style="padding:10px;"><?php echo esc_html__( 'Enter', 'propertyhive' ); ?> ' + (3 - keyword.length ) + ' <?php echo esc_html__( 'more characters', 'propertyhive' ); ?>...</div>');
                    $('#offer_search_applicant_results').show();
                    return false;
                }

                var data = {
                    action:         'propertyhive_search_contacts',
                    keyword:        keyword,
                    security:       '<?php echo esc_js(wp_create_nonce( 'search-contacts' )); ?>',
                    exclude_ids:    jQuery('#_applicant_contact_ids').val(),
                };
                $.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
                {
                    if (response == '' || response.length == 0)
                    {
                        $('#offer_search_applicant_results').html('<div style="padding:10px;"><?php echo esc_html__( 'No results found for', 'propertyhive' ); ?> \'' + offer_applicant_escape(keyword) + '\'</div>');
                    }
                    else
                    {
                        $('#offer_search_applicant_results').html('<ul style="margin:0; padding:0;"></ul>');
                        for ( var i in response )
                        {
                            $('#offer_search_applicant_results ul').append('<li style="margin:0; padding:0;"><a href="' + offer_applicant_escape(response[i].ID) + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;" data-applicant-name="' + offer_applicant_escape(response[i].post_title) + '"><strong>' + offer_applicant_escape(response[i].post_title) + '</strong><br><small style="color:#999; padding-top:1px; display:block; line-height:1.5em">' + ( response[i].address_full_formatted != '' ? offer_applicant_escape(response[i].address_full_formatted) + '<br>' : '' ) + ( response[i].telephone_number != '' ? offer_applicant_escape(response[i].telephone_number) + '<br>' : '' ) + ( response[i].email_address != '' ? offer_applicant_escape(response[i].email_address) : '' ) + '</small></a></li>');
                        }
                    }
                    $('#offer_search_applicant_results').show();
                });
            });

            $('body').on('click', '#offer_search_applicant_results ul li a', function(e)
            {
                e.preventDefault();

                offer_selected_applicants.push( { id: $(this).attr('href'), post_title: $(this).attr('data-applicant-name') } );

                $('#offer_search_applicant_results').html('');
                $('#offer_search_applicant_results').hide();

                $('#offer_applicant_search').val('');

                offer_update_selected_applicants();

                // If the Applicant Solicitor select meta box exists on the page and no solicitor has been selected yet
                if (typeof jQuery('#offer_selected_applicant_solicitors').html() !== 'undefined' && jQuery('#offer_selected_applicant_solicitors').html() == '')
                {
                    // Check if the selected applicant has a solicitor assigned to them and select them as applicant solicitor if so
                    var data = {
                        action: 'propertyhive_get_contact_solicitor',
                        post_id: $(this).attr('href'),
                    };
                    $.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response)
                    {
                        if (response != '')
                        {
                            var solicitor_data = jQuery.parseJSON( response );

                            jQuery('#offer_selected_applicant_solicitors').html('<ul></ul>');

                            jQuery('#offer_selected_applicant_solicitors ul').append('<li><a href="' + offer_applicant_escape(solicitor_data['id']) + '" class="offer-remove-applicant-solicitor" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + offer_applicant_escape(solicitor_data['name']) + '</li>');

                            jQuery('#_applicant_solicitor_contact_ids').val(solicitor_data['id']);

                            jQuery('#offer_selected_applicant_solicitors').show();
                        }
                    });
                }
            });

            $('body').on('click', 'a.offer-remove-applicant', function(e)
            {
                e.preventDefault();

                var applicant_id = $(this).attr('href');

                for (var key in offer_selected_applicants)
                {
                    if (offer_selected_applicants[key].id == applicant_id )
                    {
                        offer_selected_applicants.splice(key, 1);
                    }
                }

                offer_update_selected_applicants();
            });

        });

        function offer_update_selected_applicants()
        {
            var applicant_contact_ids = jQuery("input[name='existing_offer_applicant']").map(function(){
                return jQuery(this).val();
            }).get();

            if ( offer_selected_applicants.length > 0 )
            {
                jQuery('#offer_selected_applicants').html('<ul></ul>');

                for ( var i in offer_selected_applicants )
                {
                    jQuery('#offer_selected_applicants ul').append('<li><a href="' + offer_applicant_escape(offer_selected_applicants[i].id) + '" class="offer-remove-applicant" data-offer-applicant-id="' + offer_applicant_escape(offer_selected_applicants[i].id) + '" data-offer-applicant-name="' + offer_applicant_escape(offer_selected_applicants[i].post_title) + '" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + offer_applicant_escape(offer_selected_applicants[i].post_title) + '</li>');

                    applicant_contact_ids.push(offer_selected_applicants[i].id);
                }
                jQuery('#offer_selected_applicants').show();
            }
            else
            {
                jQuery('#offer_selected_applicants').html('');
                jQuery('#offer_selected_applicants').hide();
            }

            jQuery('#_applicant_contact_ids').val(applicant_contact_ids.join('|'));
            jQuery('#_applicant_contact_ids').trigger('change');
        }

        </script>
        <?php
	    
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

        foreach ( array( '_applicant_name', '_applicant_address', '_applicant_contact_ids', '_offer_applicant_create_new', '_property_id', '_applicant_telephone_number', '_applicant_email_address' ) as $input_key ) {
            if ( isset( $_POST[ $input_key ] ) && ! is_string( $_POST[ $input_key ] ) ) {
                return;
            }
        }
        if ( ! empty( $_POST['_offer_applicant_create_new'] ) && ! current_user_can( get_post_type_object( 'contact' )->cap->create_posts ) ) {
            return;
        }
        $submitted_property = isset( $_POST['_property_id'] ) ? sanitize_text_field( wp_unslash( $_POST['_property_id'] ) ) : '';
        if ( $submitted_property !== '' && $submitted_property !== '0' && ( ! ctype_digit( $submitted_property ) || get_post_type( (int) $submitted_property ) !== 'property' ) ) {
            return;
        }
        $submitted_applicants = isset( $_POST['_applicant_contact_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['_applicant_contact_ids'] ) ) : '';
        $validated_applicants = array();
        if ( $submitted_applicants !== '' ) {
            foreach ( array_unique( explode( '|', $submitted_applicants ) ) as $contact_id ) {
                if ( ! ctype_digit( $contact_id ) || get_post_type( (int) $contact_id ) !== 'contact' || ! current_user_can( 'edit_post', (int) $contact_id ) ) {
                    return;
                }
                $validated_applicants[] = (int) $contact_id;
            }
        }

        $existing_applicants = get_post_meta($post->ID, '_applicant_contact_id');

        if ( isset($_POST['_offer_applicant_create_new']) && !empty($_POST['_offer_applicant_create_new']) )
        {
            // we're created a new applicant on submission
            if (!empty($_POST['_applicant_name']))
            {
                // Need to create contact/applicant
                $contact_post = array(
                    'post_title'    => wp_slash( sanitize_text_field( wp_unslash( $_POST['_applicant_name'] ) ) ),
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
                    update_post_meta( $contact_post_id, '_contact_types', array('applicant') );

                    $ph_contact_telephone_value = ( isset( $_POST['_applicant_telephone_number'] ) && is_string( $_POST['_applicant_telephone_number'] ) ) ? sanitize_text_field( wp_unslash( $_POST['_applicant_telephone_number'] ) ) : '';
                    update_post_meta( $contact_post_id, '_telephone_number', wp_slash( $ph_contact_telephone_value ) );
                    update_post_meta( $contact_post_id, '_telephone_number_clean',  ph_clean(ph_clean_telephone_number($ph_contact_telephone_value)) );

                    $ph_contact_email_value = ( isset( $_POST['_applicant_email_address'] ) && is_string( $_POST['_applicant_email_address'] ) ) ? sanitize_text_field( wp_unslash( $_POST['_applicant_email_address'] ) ) : '';
                    update_post_meta( $contact_post_id, '_email_address', str_replace(" ", "", wp_slash( $ph_contact_email_value )) );

                    if ( isset($_POST['_applicant_address']) && !empty(sanitize_textarea_field( wp_unslash( $_POST['_applicant_address'] ) )) )
                    {
                        $address = ph_split_address_into_fields( sanitize_textarea_field( wp_unslash( $_POST['_applicant_address'] ) ) );

                        update_post_meta( $contact_post_id, '_address_name_number', wp_slash( $address['address_name_number'] ) );
                        update_post_meta( $contact_post_id, '_address_street', wp_slash( $address['address_street'] ) );
                        update_post_meta( $contact_post_id, '_address_two', wp_slash( $address['address_two'] ) );
                        update_post_meta( $contact_post_id, '_address_three', wp_slash( $address['address_three'] ) );
                        update_post_meta( $contact_post_id, '_address_four', wp_slash( $address['address_four'] ) );
                        update_post_meta( $contact_post_id, '_address_postcode', wp_slash( $address['address_postcode'] ) );
                        update_post_meta( $contact_post_id, '_address_country', get_option( 'propertyhive_default_country', 'GB' ) );
                    }

                    update_post_meta( $contact_post_id, '_applicant_profiles', 1 );

                    // get department of selected property
                    $department = 'residential-sales'; // should be primary department. TODO
                    if ( isset($_POST['_property_id']) && $_POST['_property_id'] != '' )
                    {
                        $property = new PH_Property( (int)$_POST['_property_id'] );
                        $department = $property->department;
                    }
                    update_post_meta( $contact_post_id, '_applicant_profile_0', array( 'department' => $department, 'send_matching_properties' => '' ) );

                    add_post_meta( $post_id, '_applicant_contact_id', $contact_post_id );
                }
            }
        }
        else
        {
            if ( isset($_POST['_applicant_contact_ids']) && !empty($_POST['_applicant_contact_ids']) )
            {
                $applicant_contact_ids = $validated_applicants;

                if ( !is_array($existing_applicants) )
                {
                    $existing_applicants = array($existing_applicants);
                }

                $applicants_to_add = array_diff($applicant_contact_ids, $existing_applicants);

                // make the contact an applicant if not already
                foreach ( $applicants_to_add as $applicant_contact_id )
                {
                    add_post_meta( $post_id, '_applicant_contact_id', ph_clean($applicant_contact_id) );

                    $existing_contact_types = get_post_meta( $applicant_contact_id, '_contact_types', TRUE );
                    if ( $existing_contact_types == '' || !is_array($existing_contact_types) )
                    {
                        $existing_contact_types = array();
                    }
                    if ( !in_array( 'applicant', $existing_contact_types ) )
                    {
                        $existing_contact_types[] = 'applicant';
                        update_post_meta( $applicant_contact_id, '_contact_types', $existing_contact_types );
                    }
                }
            }
        }
    }

}
