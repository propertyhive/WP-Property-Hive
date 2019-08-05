<?php
/**
 * Viewing Applicant Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Viewing_Applicant
 */
class PH_Meta_Box_Viewing_Applicant {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $applicant_contact_id = get_post_meta( $post->ID, '_applicant_contact_id', true );

        if ( !empty($applicant_contact_id) )
        {
            $contact = new PH_Contact($applicant_contact_id);

            echo '<p class="form-field">
            
                <label>' . __('Name', 'propertyhive') . '</label>
                
                <a href="' . get_edit_post_link($applicant_contact_id, '') . '" data-viewing-applicant-id="' . $applicant_contact_id . '" data-viewing-applicant-name="' . get_the_title($applicant_contact_id) . '">' . get_the_title($applicant_contact_id) . '</a>
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . __('Telephone Number', 'propertyhive') . '</label>
                
                ' . $contact->telephone_number . '
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . __('Email Address', 'propertyhive') . '</label>
                
                <a href="mailto:' . $contact->email_address . '">' .  $contact->email_address  . '</a>
                
            </p>';
        }
        else
        {
            echo '<div id="viewing_applicant_search_existing">';

                echo '<p class="form-field">
                
                    <label for="viewing_applicant_search">' . __('Search Applicants', 'propertyhive') . '</label>
                    
                    <span style="position:relative;">

                        <input type="text" name="viewing_applicant_search" id="viewing_applicant_search" style="width:100%;" placeholder="' . __( 'Search Existing Contacts', 'propertyhive' ) . '..." autocomplete="false">

                        <div id="viewing_search_applicant_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                        <div id="viewing_selected_applicants" style="display:none;"></div>

                    </span>
                    
                </p>

                <p class="form-field">
                
                    <label for="">&nbsp;</label>
                    
                    <a href="" class="create-viewing-applicant button">Create New Applicant</a>
                    
                </p>';

                echo '<input type="hidden" name="_applicant_contact_ids" id="_applicant_contact_ids" value="">';

            echo '</div>';

            echo '<div id="viewing_applicant_create_new" style="display:none">';

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

                echo '<p class="form-field">
                
                    <label for="">&nbsp;</label>
                    
                    <a href="" class="create-viewing-applicant-cancel">Cancel and Search Existing Applicants</a>
                    
                </p>';

            echo '</div>';

            echo '<input type="hidden" name="_viewing_applicant_create_new" id="_viewing_applicant_create_new" value="">';
?>
<script>

var viewing_selected_applicants = [];
<?php if (isset($_GET['applicant_contact_id']) && $_GET['applicant_contact_id'] != '') { ?>
viewing_selected_applicants.push({ id: <?php echo (int)$_GET['applicant_contact_id']; ?>, post_title: '<?php echo get_the_title((int)$_GET['applicant_contact_id']); ?>' });
<?php } ?>

jQuery(document).ready(function($)
{
    viewing_update_selected_applicants();

    $('a.create-viewing-applicant').click(function(e)
    {
        e.preventDefault();

        $('#_viewing_applicant_create_new').val('1');

        $('#viewing_applicant_search_existing').hide();
        $('#viewing_applicant_create_new').fadeIn();
    });

    $('a.create-viewing-applicant-cancel').click(function(e)
    {
        e.preventDefault();

        $('#_viewing_applicant_create_new').val('');

        $('#viewing_applicant_create_new').hide();
        $('#viewing_applicant_search_existing').fadeIn();
        
    });

    $('#viewing_applicant_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#viewing_applicant_search').keyup(function()
    {
        var keyword = $(this).val();

        if (keyword.length == 0)
        {
            $('#viewing_search_applicant_results').html('');
            $('#viewing_search_applicant_results').hide();
            return false;
        }

        if (keyword.length < 3)
        {
            $('#viewing_search_applicant_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
            $('#viewing_search_applicant_results').show();
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
                $('#viewing_search_applicant_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
            }
            else
            {
                $('#viewing_search_applicant_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in response )
                {
                    $('#viewing_search_applicant_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;">' + response[i].post_title + '</a></li>');
                }
            }
            $('#viewing_search_applicant_results').show();
        });
    });

    $('body').on('click', '#viewing_search_applicant_results ul li a', function(e)
    {
        e.preventDefault();

        viewing_selected_applicants = []; // reset to only allow one applicant for now
        viewing_selected_applicants.push( { id: $(this).attr('href'), post_title: $(this).text() } );

        $('#viewing_search_applicant_results').html('');
        $('#viewing_search_applicant_results').hide();

        $('#viewing_applicant_search').val('');

        viewing_update_selected_applicants();
    });

    $('body').on('click', 'a.viewing-remove-applicant', function(e)
    {
        e.preventDefault();

        var applicant_id = $(this).attr('href');

        for (var key in viewing_selected_applicants) 
        {
            if (viewing_selected_applicants[key].id == applicant_id ) 
            {
                viewing_selected_applicants.splice(key, 1);
            }
        }

        viewing_update_selected_applicants();
    });
});

function viewing_update_selected_applicants()
{
    jQuery('#_applicant_contact_ids').val('');

    if ( viewing_selected_applicants.length > 0 )
    {
        jQuery('#viewing_selected_applicants').html('<ul></ul>');
        for ( var i in viewing_selected_applicants )
        {
            jQuery('#viewing_selected_applicants ul').append('<li><a href="' + viewing_selected_applicants[i].id + '" class="viewing-remove-applicant" data-viewing-applicant-id="' + viewing_selected_applicants[i].id + '" data-viewing-applicant-name="' + viewing_selected_applicants[i].post_title + '" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + viewing_selected_applicants[i].post_title + '</li>');

            jQuery('#_applicant_contact_ids').val(viewing_selected_applicants[i].id);
        }
        jQuery('#viewing_selected_applicants').show();
    }
    else
    {
        jQuery('#viewing_selected_applicants').html('');
        jQuery('#viewing_selected_applicants').hide();
    }

    jQuery('#_applicant_contact_ids').trigger('change');
}

</script>
<?php
        }

        do_action('propertyhive_viewing_applicant_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        $contact_post_ids = array();

        if ( isset($_POST['_viewing_applicant_create_new']) && !empty($_POST['_viewing_applicant_create_new']) )
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

                    update_post_meta( $post_id, '_applicant_contact_id', $contact_post_id );

                    $contact_post_ids[] = $contact_post_id;
                }
                
            }
        }
        else
        {
            if ( isset($_POST['_applicant_contact_ids']) && !empty($_POST['_applicant_contact_ids']) )
            {
                update_post_meta( $post_id, '_applicant_contact_id', ph_clean($_POST['_applicant_contact_ids']) );

                // make the contact an applicant if not already
                $applicant_contact_ids = explode(",", $_POST['_applicant_contact_ids']);
                foreach ( $applicant_contact_ids as $applicant_contact_id )
                {
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

                    $contact_post_ids[] = $applicant_contact_id;
                }
            }
        }

        if ( !empty($contact_post_ids) )
        {
            foreach ( $contact_post_ids as $contact_post_id )
            {
                // Add note/comment to contact
                $current_user = wp_get_current_user();

                $comment = array(
                    'note_type' => 'action',
                    'action' => 'viewing_booked',
                    'viewing_id' => $post_id,
                );
                if ( isset($_POST['_property_id']) && !empty($_POST['_property_id']) )
                {
                    $comment['property_id'] = (int)$_POST['_property_id'];
                }

                $data = array(
                    'comment_post_ID'      => $applicant_contact_id,
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
