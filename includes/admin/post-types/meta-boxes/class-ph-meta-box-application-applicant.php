<?php
/**
 * Application Applicant Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Application_Applicant
 */
class PH_Meta_Box_Application_Applicant {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $applicant_contact_ids = array();
        if ( isset($_GET['applicant_contact_id']) && ! empty( $_GET['applicant_contact_id'] ) )
        {
            $explode_applicant_contact_ids = explode('|', $_GET['applicant_contact_id']);
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
                        'value' => '<a href="' . get_edit_post_link($applicant_contact_id, '') . '" data-application-applicant-id="' . $applicant_contact_id . '" data-application-applicant-name="' . get_the_title($applicant_contact_id) . '">' . get_the_title($applicant_contact_id) . '</a>',
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
                echo '<input type="hidden" name="existing_application_applicant" value="' . $applicant_contact_id . '">';

                $fields = apply_filters( 'propertyhive_application_applicant_fields', $fields, $post->ID, $applicant_contact_id );

                $div_style = $i > 0 ? 'style="border-top:1px solid #ddd"' : '';
                echo "<div id=\"existing-owner-details-" . $applicant_contact_id . "\" " . $div_style . ">";
                foreach ( $fields as $key => $field )
                {
                    echo '<p class="form-field ' . esc_attr($key) . '" >

                        <label>' . esc_html($field['label']) . '</label>

                        ' . $field['value'] . '

                    </p>';
                }

                if ( count($applicant_contact_ids) > 1 )
                {
                ?>
                    <p class="form-field">
                        <label></label>
                        <a href="" class="button" id="remove-application-tenant-<?php echo $applicant_contact_id; ?>"><?php echo __('Remove Tenant', 'propertyhive'); ?></a>
                    </p>
                <?php
                }
                echo "</div>";
                ++$i;
            }
        }
        ?>
        <input type="hidden" name="_applicant_contact_ids" id="_applicant_contact_ids" value="<?php echo ( !empty($applicant_contact_ids) ? implode('|', $applicant_contact_ids ) : '' ); ?>">

        <div id="application_applicant_search_existing">
            <p class="form-field">

                <label for="application_applicant_search"><?php echo __(( empty($applicant_contact_ids) ? 'Search Applicants' : 'Add Applicants' ), 'propertyhive'); ?></label>

                <span style="position:relative;">

                    <input type="text" name="application_applicant_search" id="application_applicant_search" style="width:100%;" placeholder="<?php echo __( 'Search Existing Contacts', 'propertyhive' ); ?>..." autocomplete="false">

                    <div id="application_search_applicant_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="application_selected_applicants" style="display:none;"></div>

                </span>

            </p>

            <p class="form-field">

                <label for="">&nbsp;</label>

                <a href="" class="create-application-applicant button">Create New Applicant</a>

            </p>
        </div>
            
        <div id="application_applicant_create_new" style="display:none">
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
                <a href="" class="create-application-applicant-cancel">Cancel and Search Existing Applicants</a>
            </p>
        </div>
        <input type="hidden" name="_application_applicant_create_new" id="_application_applicant_create_new" value="">

        <script>

        var application_selected_applicants = [];
        <?php
            if (isset($_GET['applicant_contact_id']) && $_GET['applicant_contact_id'] != '')
            {
                $applicant_contact_ids = explode('|', $_GET['applicant_contact_id']);
                foreach ($applicant_contact_ids as $applicant_contact_id)
                {
                    ?>
                    application_selected_applicants.push({ id: <?php echo (int)$_GET['applicant_contact_id']; ?>, post_title: '<?php echo get_the_title((int)$_GET['applicant_contact_id']); ?>' });
                    <?php
                }
            }
        ?>

jQuery(document).ready(function($)
{
    application_update_selected_applicants();

    $('a.create-application-applicant').click(function(e)
    {
        e.preventDefault();

        $('#_application_applicant_create_new').val('1');

        $('#application_applicant_search_existing').hide();
        $('#application_applicant_create_new').fadeIn();
    });

    $('a.create-application-applicant-cancel').click(function(e)
    {
        e.preventDefault();

        $('#_application_applicant_create_new').val('');

        $('#application_applicant_create_new').hide();
        $('#application_applicant_search_existing').fadeIn();
        
    });

    $('#application_applicant_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#application_applicant_search').keyup(function()
    {
        var keyword = $(this).val();

        if (keyword.length == 0)
        {
            $('#application_search_applicant_results').html('');
            $('#application_search_applicant_results').hide();
            return false;
        }

        if (keyword.length < 3)
        {
            $('#application_search_applicant_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
            $('#application_search_applicant_results').show();
            return false;
        }

        var data = {
            action:         'propertyhive_search_contacts',
            keyword:        keyword,
            security:       '<?php echo wp_create_nonce( 'search-contacts' ); ?>',
            exclude_ids:    jQuery('#_applicant_contact_ids').val(),
        };
        $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
            if (response == '' || response.length == 0)
            {
                $('#application_search_applicant_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
            }
            else
            {
                $('#application_search_applicant_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in response )
                {
                    $('#application_search_applicant_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;">' + response[i].post_title + '</a></li>');
                }
            }
            $('#application_search_applicant_results').show();
        });
    });

    $('body').on('click', '#application_search_applicant_results ul li a', function(e)
    {
        e.preventDefault();

        application_selected_applicants.push( { id: $(this).attr('href'), post_title: $(this).text() } );

        $('#application_search_applicant_results').html('');
        $('#application_search_applicant_results').hide();

        $('#application_applicant_search').val('');

        application_update_selected_applicants();
    });

    $('body').on('click', 'a.application-remove-applicant', function(e)
    {
        e.preventDefault();

        var applicant_id = $(this).attr('href');

        for (var key in application_selected_applicants) 
        {
            if (application_selected_applicants[key].id == applicant_id ) 
            {
                application_selected_applicants.splice(key, 1);
            }
        }

        application_update_selected_applicants();
    });

    $('body').on('click', 'a[id^="remove-application-tenant-"]', function()
    {
        var applicant_contact_id = jQuery(this).attr('id');
        applicant_contact_id = applicant_contact_id.replace('remove-application-tenant-', '');

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

function application_update_selected_applicants()
{
    var applicant_contact_ids = jQuery("input[name='existing_application_applicant']").map(function(){
        return jQuery(this).val();
    }).get();

    if ( application_selected_applicants.length > 0 )
    {
        jQuery('#application_selected_applicants').html('<ul></ul>');

        for ( var i in application_selected_applicants )
        {
            jQuery('#application_selected_applicants ul').append('<li><a href="' + application_selected_applicants[i].id + '" class="application-remove-applicant" data-application-applicant-id="' + application_selected_applicants[i].id + '" data-application-applicant-name="' + application_selected_applicants[i].post_title + '" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + application_selected_applicants[i].post_title + '</li>');

            applicant_contact_ids.push(application_selected_applicants[i].id);
        }
        jQuery('#application_selected_applicants').show();
    }
    else
    {
        jQuery('#application_selected_applicants').html('');
        jQuery('#application_selected_applicants').hide();
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
        global $wpdb;

        $application_notes_to_write = array();

        $existing_applicants = get_post_meta($post_id, '_applicant_contact_id');
        if ( !is_array($existing_applicants) )
        {
            $existing_applicants = array($existing_applicants);
        }

        $applicant_contact_ids = !empty($_POST['_applicant_contact_ids']) ? array_unique(explode("|", $_POST['_applicant_contact_ids'])) : [];

        $applicants_to_remove = array_diff($existing_applicants, $applicant_contact_ids);
        foreach ( $applicants_to_remove as $applicant_contact_id )
        {
            delete_post_meta( $post_id, '_applicant_contact_id', ph_clean($applicant_contact_id) );

            $application_notes_to_write[] = array(
                'comment_post_ID' => $post_id,
                'note_action' => 'removed_from_application',
                'applicant_contact_id' => $applicant_contact_id,
            );

            $application_notes_to_write[] = array(
                'comment_post_ID' => $applicant_contact_id,
                'note_action' => 'removed_from_application',
            );
        }

        if ( isset($_POST['_application_applicant_create_new']) && !empty($_POST['_application_applicant_create_new']) )
        {
            // we're created a new applicant on submission
            if (!empty($_POST['_applicant_name']))
            {
                // Need to create contact/applicant
                $contact_post = array(
                    'post_title'    => ph_clean($_POST['_applicant_name']),
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

                    update_post_meta( $contact_post_id, '_telephone_number', ph_clean($_POST['_applicant_telephone_number']) );
                    update_post_meta( $contact_post_id, '_telephone_number_clean',  ph_clean($_POST['_applicant_telephone_number'], true) );

                    update_post_meta( $contact_post_id, '_email_address', str_replace(" ", "", ph_clean($_POST['_applicant_email_address'])) );

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

                    $application_notes_to_write[] = array(
                        'comment_post_ID' => $contact_post_id,
                        'note_action' => empty($existing_applicants) ? 'application_booked' : 'added_to_application',
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

                    $application_notes_to_write[] = array(
                        'comment_post_ID' => $applicant_contact_id,
                        'note_action' => empty($existing_applicants) ? 'application_booked' : 'added_to_application',
                    );
                }
            }
        }

        if ( !empty($application_notes_to_write) )
        {
            foreach ( $application_notes_to_write as $application_note )
            {
                // Add note/comment to contact
                $current_user = wp_get_current_user();

                $comment = array(
                    'note_type' => 'action',
                    'action' => $application_note['note_action'],
                );
                if ( isset($_POST['_property_id']) && !empty($_POST['_property_id']) )
                {
                    $comment['property_id'] = (int)$_POST['_property_id'];
                }

                if ( isset($application_note['applicant_contact_id']) )
                {
                    $comment['contact_id'] = $application_note['applicant_contact_id'];
                }
                else
                {
                    $comment['application_id'] = $post_id;
                }

                $data = array(
                    'comment_post_ID'      => $application_note['comment_post_ID'],
                    'comment_author'       => $current_user->display_name,
                    'comment_author_email' => 'propertyhive@noreply.com',
                    'comment_author_url'   => '',
                    'comment_date'         => date("Y-m-d H:i:s"),
                    'comment_content'      => serialize($comment),
                    'comment_approved'     => 1,
                    'comment_type'         => 'propertyhive_note',
                );
                $comment_id = wp_insert_comment( $data );
            }
        }
    }

}
