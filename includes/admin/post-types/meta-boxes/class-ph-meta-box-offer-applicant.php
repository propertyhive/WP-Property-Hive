<?php
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
class PH_Meta_Box_Offer_Applicant {

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

            $fields = array(
                'name' => array(
                    'label' => __('Name', 'propertyhive'),
                    'value' => '<a href="' . get_edit_post_link($applicant_contact_id, '') . '" data-viewing-applicant-id="' . $applicant_contact_id . '" data-viewing-applicant-name="' . get_the_title($applicant_contact_id) . '">' . get_the_title($applicant_contact_id) . '</a>',
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

            $fields = apply_filters( 'propertyhive_offer_applicant_fields', $fields, $post->ID, $applicant_contact_id );

            foreach ( $fields as $key => $field )
            {
                echo '<p class="form-field ' . esc_attr($key) . '">
            
                    <label>' . esc_html($field['label']) . '</label>
                    
                    ' . $field['value'] . '
                    
                </p>';
            }
        }
        else
        {
            echo '<p class="form-field">
            
                <label for="offer_applicant_search">' . __('Search Applicants', 'propertyhive') . '</label>
                
                <span style="position:relative;">

                    <input type="text" name="offer_applicant_search" id="offer_applicant_search" style="width:100%;" placeholder="' . __( 'Search Existing Contacts', 'propertyhive' ) . '..." autocomplete="false">

                    <div id="offer_search_applicant_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="offer_selected_applicants" style="display:none;"></div>

                </span>
                
            </p>';

            echo '<input type="hidden" name="_applicant_contact_ids" id="_applicant_contact_ids" value="">';
?>
<script>

var offer_selected_applicants = [];
<?php if (isset($_GET['applicant_contact_id']) && $_GET['applicant_contact_id'] != '') { ?>
offer_selected_applicants[<?php echo (int)$_GET['applicant_contact_id']; ?>] = ({ post_title: '<?php echo get_the_title((int)$_GET['applicant_contact_id']); ?>' });
<?php } ?>

jQuery(document).ready(function($)
{
    offer_update_selected_applicants();

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
            $('#offer_search_applicant_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
            $('#offer_search_applicant_results').show();
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
                $('#offer_search_applicant_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
            }
            else
            {
                $('#offer_search_applicant_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in response )
                {
                    $('#offer_search_applicant_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;" data-applicant-name="' + response[i].post_title + '"><strong>' + response[i].post_title + '</strong><br><small style="color:#999; padding-top:1px; display:block; line-height:1.5em">' + ( response[i].address_full_formatted != '' ? response[i].address_full_formatted + '<br>' : '' ) + ( response[i].telephone_number != '' ? response[i].telephone_number + '<br>' : '' ) + ( response[i].email_address != '' ? response[i].email_address : '' ) + '</small></a></li>');
                }
            }
            $('#offer_search_applicant_results').show();
        });
    });

    $('body').on('click', '#offer_search_applicant_results ul li a', function(e)
    {
        e.preventDefault();

        offer_selected_applicants = []; // reset to only allow one applicant for now
        offer_selected_applicants[$(this).attr('href')] = ({ post_title: $(this).attr('data-applicant-name') });

        $('#offer_search_applicant_results').html('');
        $('#offer_search_applicant_results').hide();

        $('#offer_applicant_search').val('');

        offer_update_selected_applicants();
    });

    $('body').on('click', 'a.offer-remove-applicant', function(e)
    {
        e.preventDefault();

        var applicant_id = $(this).attr('href');

        delete(offer_selected_applicants[applicant_id]);

        offer_update_selected_applicants();
    });
});

function offer_update_selected_applicants()
{
    jQuery('#_applicant_contact_ids').val();

    if ( Object.keys(offer_selected_applicants).length > 0 )
    {
        jQuery('#offer_selected_applicants').html('<ul></ul>');
        for ( var i in offer_selected_applicants )
        {
            jQuery('#offer_selected_applicants ul').append('<li><a href="' + i + '" class="offer-remove-applicant" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + offer_selected_applicants[i].post_title + '</li>');

            jQuery('#_applicant_contact_ids').val(i);
        }
        jQuery('#offer_selected_applicants').show();
    }
    else
    {
        jQuery('#offer_selected_applicants').html('');
        jQuery('#offer_selected_applicants').hide();
    }
}

</script>
<?php
        }
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        if ( isset($_POST['_applicant_contact_ids']) && !empty($_POST['_applicant_contact_ids']) )
        {
            update_post_meta( $post_id, '_applicant_contact_id', (int)$_POST['_applicant_contact_ids'] );
        }
    }

}
