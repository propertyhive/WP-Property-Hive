<?php
/**
 * Contact Solicitor Details
 *
 * @author      PropertyHive
 * @category    Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Contact_Solicitor
 */
class PH_Meta_Box_Contact_Solicitor {

    /**
     * Output the metabox
     */
    public static function output( $post ) {

        echo '<div class="propertyhive_meta_box">';

        echo '<div class="options_group">';

        $contact_solicitor_contact_id = get_post_meta( $post->ID, '_contact_solicitor_contact_id', true );

        if ( !empty($contact_solicitor_contact_id) )
        {
            $contact = new PH_Contact($contact_solicitor_contact_id);

            $fields = array(
                'name' => array(
                    'label' => __('Name', 'propertyhive'),
                    'value' => '<a href="' . esc_url(get_edit_post_link($contact_solicitor_contact_id, '')) . '">' . esc_html(get_the_title($contact_solicitor_contact_id) . ( $contact->company_name != '' && $contact->company_name != get_the_title($contact_solicitor_contact_id) ? ' (' . $contact->company_name . ')' : '' )) . '</a>',
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

            $fields = apply_filters( 'propertyhive_contact_solicitor_fields', $fields, $post->ID, $contact_solicitor_contact_id );

            foreach ( $fields as $key => $field )
            {
                echo '<p class="form-field ' . esc_attr($key) . '">

                    <label>' . esc_html($field['label']) . '</label>

                    ' . $field['value'] . '

                </p>';
            }

            echo '<p class="form-field">

                <label></label>

                <a class="button" href="' . esc_url(wp_nonce_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ), '1', 'remove_contact_solicitor' )) . '">' . esc_html(__( 'Remove Solicitor', 'propertyhive' )) . '</a>

            </p>';
        }
        else
        {
            ?>
            <p class="form-field">

                <label for="contact_solicitor_search"><?php echo esc_html(__('Search Solicitors', 'propertyhive')); ?></label>

                <span style="position:relative;">

                    <input type="text" name="contact_solicitor_search" id="contact_solicitor_search" style="width:100%;" placeholder="<?php echo esc_attr(__( 'Search Existing Contacts', 'propertyhive' )); ?>..." autocomplete="false">

                    <div id="search_contact_solicitor_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="selected_contact_solicitors" style="display:none;"></div>

                </span>

            </p>

            <input type="hidden" name="_contact_solicitor_contact_id" id="_contact_solicitor_contact_id" value="">
            <?php
?>
<script>

var selected_contact_solicitors = [];

jQuery(document).ready(function($)
{
    update_selected_contact_solicitors();

    $('#contact_solicitor_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#contact_solicitor_search').keyup(function()
    {
        var keyword = $(this).val();

        if (keyword.length == 0)
        {
            $('#search_contact_solicitor_results').html('');
            $('#search_contact_solicitor_results').hide();
            return false;
        }

        if (keyword.length < 3)
        {
            $('#search_contact_solicitor_results').html('<div style="padding:10px;"><?php echo esc_html__( 'Enter', 'propertyhive' ); ?> ' + (3 - keyword.length ) + ' <?php echo esc_html__( 'more characters', 'propertyhive' ); ?>...</div>');
            $('#search_contact_solicitor_results').show();
            return false;
        }

        var data = {
            action:         'propertyhive_search_contacts',
            keyword:        keyword,
            contact_type:   'thirdparty',
            security:       '<?php echo esc_js(wp_create_nonce( 'search-contacts' )); ?>',
        };
        $.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response)
        {
            if (response == '' || response.length == 0)
            {
                $('#search_contact_solicitor_results').html('<div style="padding:10px;"><?php echo esc_html__( 'No results found for', 'propertyhive' ); ?> \'' + keyword + '\'</div>');
            }
            else
            {
                $('#search_contact_solicitor_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in response )
                {
                    $('#search_contact_solicitor_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;" data-contact-solicitor-name="' + response[i].post_title + '"><strong>' + response[i].post_title + '</strong><small style="color:#999; padding-top:1px; display:block; line-height:1.5em">' + ( response[i].address_full_formatted != '' ? response[i].address_full_formatted + '<br>' : '' ) + ( response[i].telephone_number != '' ? response[i].telephone_number + '<br>' : '' ) + ( response[i].email_address != '' ? response[i].email_address : '' ) + '</small></a></li>');
                }
            }
            $('#search_contact_solicitor_results').show();
        });
    });

    $('body').on('click', '#search_contact_solicitor_results ul li a', function(e)
    {
        e.preventDefault();

        selected_contact_solicitors = []; // reset to only allow one solicitor
        selected_contact_solicitors[$(this).attr('href')] = ({ post_title: $(this).attr('data-contact-solicitor-name') });

        $('#search_contact_solicitor_results').html('');
        $('#search_contact_solicitor_results').hide();

        $('#contact_solicitor_search').val('');

        update_selected_contact_solicitors();
    });

    $('body').on('click', 'a.remove-contact-solicitor', function(e)
    {
        e.preventDefault();

        var contact_solicitor_id = $(this).attr('href');

        delete(selected_contact_solicitors[contact_solicitor_id]);

        update_selected_contact_solicitors();
    });
});

function update_selected_contact_solicitors()
{
    jQuery('#_contact_solicitor_contact_id').val();
    if ( Object.keys(selected_contact_solicitors).length > 0 )
    {
        jQuery('#selected_contact_solicitors').html('<ul></ul>');
        for ( var i in selected_contact_solicitors )
        {
            jQuery('#selected_contact_solicitors ul').append('<li><a href="' + i + '" class="remove-contact-solicitor" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + selected_contact_solicitors[i].post_title + '</li>');

            jQuery('#_contact_solicitor_contact_id').val(i);
        }
        jQuery('#selected_contact_solicitors').show();
    }
    else
    {
        jQuery('#selected_contact_solicitors').html('');
        jQuery('#selected_contact_solicitors').hide();
    }
}

</script>
<?php
        }

        do_action('propertyhive_contact_solicitor_fields');

        echo '</div>';

        echo '</div>';

    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        if ( isset($_POST['_contact_solicitor_contact_id']) && $_POST['_contact_solicitor_contact_id'] != '' )
        {
            update_post_meta( $post_id, '_contact_solicitor_contact_id', (int)$_POST['_contact_solicitor_contact_id'] );
        }
    }

}
