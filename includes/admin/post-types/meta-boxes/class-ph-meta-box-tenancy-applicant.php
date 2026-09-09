<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Tenancy Applicant Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Tenancy_Applicant
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Tenancy_Applicant; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Tenancy_Applicant {

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
                        'value' => '<a href="' . esc_url(esc_url( get_edit_post_link($applicant_contact_id, '') )) . '" data-tenancy-applicant-id="' . esc_attr($applicant_contact_id) . '" data-tenancy-applicant-name="' . esc_attr(get_the_title($applicant_contact_id)) . '">' . esc_html(get_the_title($applicant_contact_id)) . '</a>',
                    ),
                    'telephone_number' => array(
                        'label' => __('Telephone Number', 'propertyhive'),
                        'value' => esc_html($contact->telephone_number),
                    ),
                    'email_address' => array(
                        'label' => __('Email Address', 'propertyhive'),
                        'value' => '<a href="mailto:' . esc_attr($contact->email_address) . '">' . esc_html($contact->email_address) . '</a>',
                    ),
                );
                echo '<input type="hidden" name="existing_tenancy_applicant" value="' . esc_attr($applicant_contact_id) . '">';

                $fields = apply_filters( 'propertyhive_tenancy_applicant_fields', $fields, $post->ID, $applicant_contact_id );

                $div_style = $i > 0 ? 'style="border-top:1px solid #ddd"' : '';
                echo '<div id="existing-owner-details-' . esc_attr( $applicant_contact_id ) . '"' . ( $i > 0 ? ' style="border-top:1px solid #ddd">' : ' >' );
                foreach ( $fields as $key => $field )
                {
                    echo '<p class="form-field ' . esc_attr($key) . '" >

                        <label>' . esc_html($field['label']) . '</label>

                        ';
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core field values are escaped above before the trusted PHP propertyhive_tenancy_applicant_fields filter, which intentionally permits extension HTML.
                    echo $field['value'];
                    echo '

                    </p>';
                }

                if ( count($applicant_contact_ids) > 1 )
                {
                ?>
                    <p class="form-field">
                        <label></label>
                        <a href="" class="button" id="remove-tenancy-tenant-<?php echo esc_attr($applicant_contact_id); ?>"><?php echo esc_html(__('Remove Tenant', 'propertyhive')); ?></a>
                    </p>
                <?php
                }
                echo "</div>";
                ++$i;
            }
        }
        ?>
        <input type="hidden" name="_applicant_contact_ids" id="_applicant_contact_ids" value="<?php echo ( !empty($applicant_contact_ids) ? esc_attr(implode('|', $applicant_contact_ids )) : '' ); ?>">

        <div id="tenancy_applicant_search_existing">
            <p class="form-field">

                <label for="tenancy_applicant_search"><?php echo ( empty( $applicant_contact_ids ) ? esc_html__( 'Search Applicants', 'propertyhive' ) : esc_html__( 'Add Applicants', 'propertyhive' ) ); ?></label>

                <span style="position:relative;">

                    <input type="text" name="tenancy_applicant_search" id="tenancy_applicant_search" style="width:100%;" placeholder="<?php echo esc_attr(__( 'Search Existing Contacts', 'propertyhive' )); ?>..." autocomplete="false">

                    <div id="tenancy_search_applicant_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="tenancy_selected_applicants" style="display:none;"></div>

                </span>

            </p>

            <p class="form-field">

                <label for="">&nbsp;</label>

                <a href="" class="create-tenancy-applicant button"><?php echo esc_html__( 'Create New Applicant', 'propertyhive' ); ?></a>

            </p>
        </div>
            
        <div id="tenancy_applicant_create_new" style="display:none">
            <?php
                $args = array( 
                    'id' => '_applicant_name', 
                    'label' => __( 'Name', 'propertyhive' ), 
                    'desc_tip' => false, 
                    'type' => 'text'
                );
                propertyhive_wp_text_input( $args );

                $args = array( 
                    'id' => '_applicant_telephone_number', 
                    'label' => __( 'Telephone Number', 'propertyhive' ), 
                    'desc_tip' => false, 
                    'type' => 'text'
                );
                propertyhive_wp_text_input( $args );

                $args = array( 
                    'id' => '_applicant_email_address', 
                    'label' => __( 'Email Address', 'propertyhive' ), 
                    'desc_tip' => false,
                    'description' => 'Upon booking a new applicant record will be created with these details.',
                    'type' => 'email'
                );
                propertyhive_wp_text_input( $args );
            ?>
            <p class="form-field">
                <label for="">&nbsp;</label>
                <a href="" class="create-tenancy-applicant-cancel"><?php echo esc_html__( 'Cancel and Search Existing Applicants', 'propertyhive' ); ?></a>
            </p>
        </div>
        <input type="hidden" name="_tenancy_applicant_create_new" id="_tenancy_applicant_create_new" value="">

        <script>

        function tenancy_applicant_escape(value) {
            return String(value == null ? '' : value).replace(/[&<>"']/g, function(character) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character];
            });
        }

        var tenancy_selected_applicants = [];
        <?php
            if ( $requested_applicants !== '' )
            {
                foreach ($applicant_contact_ids as $applicant_contact_id)
                {
                    ?>
                    tenancy_selected_applicants.push({ id: <?php echo (int)$applicant_contact_id; ?>, post_title: '<?php echo esc_js(get_the_title((int)$applicant_contact_id)); ?>' });
                    <?php
                }
            }
        ?>

jQuery(document).ready(function($)
{
    tenancy_update_selected_applicants();

    $('a.create-tenancy-applicant').click(function(e)
    {
        e.preventDefault();

        $('#_tenancy_applicant_create_new').val('1');

        $('#tenancy_applicant_search_existing').hide();
        $('#tenancy_applicant_create_new').fadeIn();
    });

    $('a.create-tenancy-applicant-cancel').click(function(e)
    {
        e.preventDefault();

        $('#_tenancy_applicant_create_new').val('');

        $('#tenancy_applicant_create_new').hide();
        $('#tenancy_applicant_search_existing').fadeIn();
        
    });

    $('#tenancy_applicant_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#tenancy_applicant_search').keyup(function()
    {
        var keyword = $(this).val();

        if (keyword.length == 0)
        {
            $('#tenancy_search_applicant_results').html('');
            $('#tenancy_search_applicant_results').hide();
            return false;
        }

        if (keyword.length < 3)
        {
            $('#tenancy_search_applicant_results').html('<div style="padding:10px;"><?php echo esc_html__( 'Enter', 'propertyhive' ); ?> ' + (3 - keyword.length ) + ' <?php echo esc_html__( 'more characters', 'propertyhive' ); ?>...</div>');
            $('#tenancy_search_applicant_results').show();
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
                $('#tenancy_search_applicant_results').html('<div style="padding:10px;"><?php echo esc_html__( 'No results found for', 'propertyhive' ); ?> \'' + tenancy_applicant_escape(keyword) + '\'</div>');
            }
            else
            {
                $('#tenancy_search_applicant_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in response )
                {
                    $('#tenancy_search_applicant_results ul').append('<li style="margin:0; padding:0;"><a href="' + tenancy_applicant_escape(response[i].ID) + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;">' + tenancy_applicant_escape(response[i].post_title) + '</a></li>');
                }
            }
            $('#tenancy_search_applicant_results').show();
        });
    });

    $('body').on('click', '#tenancy_search_applicant_results ul li a', function(e)
    {
        e.preventDefault();

        tenancy_selected_applicants.push( { id: $(this).attr('href'), post_title: $(this).text() } );

        $('#tenancy_search_applicant_results').html('');
        $('#tenancy_search_applicant_results').hide();

        $('#tenancy_applicant_search').val('');

        tenancy_update_selected_applicants();
    });

    $('body').on('click', 'a.tenancy-remove-applicant', function(e)
    {
        e.preventDefault();

        var applicant_id = $(this).attr('href');

        for (var key in tenancy_selected_applicants) 
        {
            if (tenancy_selected_applicants[key].id == applicant_id ) 
            {
                tenancy_selected_applicants.splice(key, 1);
            }
        }

        tenancy_update_selected_applicants();
    });

    $('body').on('click', 'a[id^="remove-tenancy-tenant-"]', function()
    {
        var applicant_contact_id = jQuery(this).attr('id');
        applicant_contact_id = applicant_contact_id.replace('remove-tenancy-tenant-', '');

        // Remove this ID from hidden field
        var existing_tenant_ids = jQuery('#_applicant_contact_ids').val().split('|');
        var new_tenant_ids = new Array();
        if ( existing_tenant_ids.length > 0 )
        {
            for ( var i in existing_tenant_ids )
            {
                if ( existing_tenant_ids[i] != applicant_contact_id )
                {
                    new_tenant_ids.push(existing_tenant_ids[i]);
                }
            }
        }
        jQuery('#_applicant_contact_ids').val( new_tenant_ids.join('|') );

        jQuery('#existing-owner-details-' + applicant_contact_id).fadeOut('fast');
        return false;
    });
});

function tenancy_update_selected_applicants()
{
    var applicant_contact_ids = jQuery("input[name='existing_tenancy_applicant']").map(function(){
        return jQuery(this).val();
    }).get();

    if ( tenancy_selected_applicants.length > 0 )
    {
        jQuery('#tenancy_selected_applicants').html('<ul></ul>');

        for ( var i in tenancy_selected_applicants )
        {
            jQuery('#tenancy_selected_applicants ul').append('<li><a href="' + tenancy_applicant_escape(tenancy_selected_applicants[i].id) + '" class="tenancy-remove-applicant" data-tenancy-applicant-id="' + tenancy_applicant_escape(tenancy_selected_applicants[i].id) + '" data-tenancy-applicant-name="' + tenancy_applicant_escape(tenancy_selected_applicants[i].post_title) + '" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + tenancy_applicant_escape(tenancy_selected_applicants[i].post_title) + '</li>');

            applicant_contact_ids.push(tenancy_selected_applicants[i].id);
        }
        jQuery('#tenancy_selected_applicants').show();
    }
    else
    {
        jQuery('#tenancy_selected_applicants').html('');
        jQuery('#tenancy_selected_applicants').hide();
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

        foreach ( array( '_applicant_name', '_applicant_address', '_applicant_contact_ids', '_tenancy_applicant_create_new', '_property_id', '_applicant_telephone_number', '_applicant_email_address' ) as $input_key ) {
            if ( isset( $_POST[ $input_key ] ) && ! is_string( $_POST[ $input_key ] ) ) {
                return;
            }
        }
        if ( ! empty( $_POST['_tenancy_applicant_create_new'] ) && ! current_user_can( get_post_type_object( 'contact' )->cap->create_posts ) ) {
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

        $tenancy_notes_to_write = array();

        $existing_applicants = get_post_meta($post_id, '_applicant_contact_id');
        if ( !is_array($existing_applicants) )
        {
            $existing_applicants = array($existing_applicants);
        }

        $applicant_contact_ids = !empty($_POST['_applicant_contact_ids']) ? $validated_applicants : [];

        $applicants_to_remove = array_diff($existing_applicants, $applicant_contact_ids);
        foreach ( $applicants_to_remove as $applicant_contact_id )
        {
            delete_post_meta( $post_id, '_applicant_contact_id', ph_clean($applicant_contact_id) );

            $tenancy_notes_to_write[] = array(
                'comment_post_ID' => $post_id,
                'note_action' => 'removed_from_tenancy',
                'applicant_contact_id' => $applicant_contact_id,
            );

            $tenancy_notes_to_write[] = array(
                'comment_post_ID' => $applicant_contact_id,
                'note_action' => 'removed_from_tenancy',
            );
        }

        if ( isset($_POST['_tenancy_applicant_create_new']) && !empty($_POST['_tenancy_applicant_create_new']) )
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

                    $tenancy_notes_to_write[] = array(
                        'comment_post_ID' => $contact_post_id,
                        'note_action' => empty($existing_applicants) ? 'tenancy_booked' : 'added_to_tenancy',
                    );
                }
            }
        }
        else
        {
            if ( !empty($applicant_contact_ids) )
            {
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

                    $tenancy_notes_to_write[] = array(
                        'comment_post_ID' => $applicant_contact_id,
                        'note_action' => empty($existing_applicants) ? 'tenancy_booked' : 'added_to_tenancy',
                    );
                }
            }
        }

        if ( !empty($tenancy_notes_to_write) )
        {
            foreach ( $tenancy_notes_to_write as $tenancy_note )
            {
                // Add note/comment to contact
                $current_user = wp_get_current_user();

                $comment = array(
                    'note_type' => 'action',
                    'action' => $tenancy_note['note_action'],
                );
                if ( isset($_POST['_property_id']) && !empty($_POST['_property_id']) )
                {
                    $comment['property_id'] = (int)$_POST['_property_id'];
                }

                if ( isset($tenancy_note['applicant_contact_id']) )
                {
                    $comment['contact_id'] = $tenancy_note['applicant_contact_id'];
                }
                else
                {
                    $comment['tenancy_id'] = $post_id;
                }

                $data = array(
                    'comment_post_ID'      => $tenancy_note['comment_post_ID'],
                    'comment_author'       => $current_user->display_name,
                    'comment_author_email' => 'propertyhive@noreply.com',
                    'comment_author_url'   => '',
                    'comment_date'         => gmdate("Y-m-d H:i:s"),
                    'comment_content'      => serialize($comment),
                    'comment_approved'     => 1,
                    'comment_type'         => 'propertyhive_note',
                );
                $comment_id = wp_insert_comment( $data );
            }
        }
    }

}
