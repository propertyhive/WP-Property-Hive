<?php
/**
 * Enquiry Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Enquiry_Details
 */
class PH_Meta_Box_Enquiry_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid, $post, $current_screen;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group" style="position:relative;">';

        if ( $current_screen->action == 'add' )
        {
            $args = array( 
                'id' => '_added_manually', 
                'value' => 'yes'
            );
            propertyhive_wp_hidden_input( $args );

            $args = array( 
                'id' => 'name', 
                'label' => __( 'Name', 'propertyhive' ), 
                'desc_tip' => false,
                'type' => 'text'
            );
            propertyhive_wp_text_input( $args );

            $args = array( 
                'id' => 'email', 
                'label' => __( 'Email Address', 'propertyhive' ), 
                'desc_tip' => false,
                'type' => 'email'
            );
            propertyhive_wp_text_input( $args );

            $args = array( 
                'id' => 'telephone', 
                'label' => __( 'Telephone', 'propertyhive' ), 
                'desc_tip' => false,
                'type' => 'text'
            );
            propertyhive_wp_text_input( $args );

            $args = array( 
                'id' => 'body', 
                'label' => __( 'Body', 'propertyhive' ), 
                'desc_tip' => false,
            );
            propertyhive_wp_textarea_input( $args );

echo '<p class="form-field">
            
                <label for="viewing_property_search">' . __('Search Properties', 'propertyhive') . '</label>
                
                <span style="position:relative;">

                    <input type="text" name="viewing_property_search" id="viewing_property_search" style="width:100%;" placeholder="' . __( 'Search Properties', 'propertyhive' ) . '..." autocomplete="false">

                    <div id="viewing_search_property_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="viewing_selected_properties" style="display:none;"></div>

                </span>
                
            </p>';

            echo '<input type="hidden" name="property_id" id="property_id" value="">';
?>
<script>

var viewing_selected_properties = [<?php 
    if ( isset($_GET['property_id']) && ph_clean($_GET['property_id']) != '' ) 
    { 
        $property = new PH_Property( (int)$_GET['property_id'] );
        echo '{ id: ' . (int)$_GET['property_id'] . ', post_title: "' . $property->get_formatted_full_address() . '" }';
    } 
?>];
var viewing_search_properties_timeout;

jQuery(document).ready(function($)
{
    viewing_update_selected_properties();
    
    $('#viewing_property_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#viewing_property_search').keyup(function()
    {
        clearTimeout(viewing_search_properties_timeout);
        viewing_search_properties_timeout = setTimeout(function() { viewing_perform_property_search(); }, 400);
    });

    $('body').on('click', '#viewing_search_property_results ul li a', function(e)
    {
        e.preventDefault();

        viewing_selected_properties = []; // reset to only allow one property for now
        viewing_selected_properties.push({ id: $(this).attr('href'), post_title: $(this).text(), owner_id: $(this).attr('data-viewing-owner-id'), owner_name: $(this).attr('data-viewing-owner-name') });

        $('#viewing_search_property_results').html('');
        $('#viewing_search_property_results').hide();

        $('#viewing_property_search').val('');

        viewing_update_selected_properties();
    });

    $('body').on('click', 'a.viewing-remove-property', function(e)
    {
        e.preventDefault();

        var property_id = $(this).attr('href');

        for (var key in viewing_selected_properties) 
        {
            if (viewing_selected_properties[key].id == property_id ) 
            {
                viewing_selected_properties.splice(key, 1);
            }
        }

        viewing_update_selected_properties();
    });
});

function viewing_perform_property_search()
{
    var keyword = jQuery('#viewing_property_search').val();

    if (keyword.length == 0)
    {
        jQuery('#viewing_search_property_results').html('');
        jQuery('#viewing_search_property_results').hide();
        return false;
    }

    if (keyword.length < 3)
    {
        jQuery('#viewing_search_property_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
        jQuery('#viewing_search_property_results').show();
        return false;
    }

    var data = {
        action:         'propertyhive_search_properties',
        keyword:        keyword,
        security:       '<?php echo wp_create_nonce( 'search-properties' ); ?>',
    };
    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
    {
        if (response == '' || response.length == 0)
        {
            jQuery('#viewing_search_property_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
        }
        else
        {
            jQuery('#viewing_search_property_results').html('<ul style="margin:0; padding:0;"></ul>');
            for ( var i in response )
            {
                jQuery('#viewing_search_property_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;" data-viewing-owner-id="' + response[i].owner_id + '" data-viewing-owner-name="' + response[i].owner_name + '">' + response[i].post_title + '</a></li>');
            }
        }
        jQuery('#viewing_search_property_results').show();
    });
}

function viewing_update_selected_properties()
{
    jQuery('#property_id').val('');

    if ( viewing_selected_properties.length > 0 )
    {
        jQuery('#viewing_selected_properties').html('<ul></ul>');
        for ( var i in viewing_selected_properties )
        {
            jQuery('#viewing_selected_properties ul').append('<li><a href="' + viewing_selected_properties[i].id + '" class="viewing-remove-property" style="color:inherit; text-decoration:none;" data-viewing-owner-id="' + viewing_selected_properties[i].owner_id + '" data-viewing-owner-name="' + viewing_selected_properties[i].owner_name + '"><span class="dashicons dashicons-no-alt"></span></a> ' + viewing_selected_properties[i].post_title + '</li>');

            jQuery('#property_id').val(viewing_selected_properties[i].id);
        }
        jQuery('#viewing_selected_properties').show();
    }
    else
    {
        jQuery('#viewing_selected_properties').html('');
        jQuery('#viewing_selected_properties').hide();
    }

    jQuery('#property_id').trigger('change');
}

</script>
<?php
        }
        else
        {
            $ignore_keys = array(
                '_status',
                '_source',
                '_negotiator_id',
                '_office_id',
                '_action',
                '_contact_id',
            );
            
            $enquiry_post_id = $post->ID;
            $enquiry_meta = get_metadata( 'post', $post->ID );
            
            $name = false;
            $email = false;
            $property_post_id = false;

            foreach ($enquiry_meta as $key => $value)
            {
                if ( ! in_array( $key, $ignore_keys ) && ( substr( $key, 0, 1 ) != '_' || $key == '_property_id' ) && strpos($key, 'recaptcha') === FALSE )
                {
                    if ( $key == '_property_id' || $key == 'property_id' )
                    {
                        $property_links = array();
                        foreach ( $value as $sub_value)
                        {
                            $property_links[] = '<a href="' . get_edit_post_link( $sub_value ) . '">' . get_the_title( $sub_value ) . '</a>';
                        }
                        $value = implode('<br>', $property_links);

                        $key = 'property';

                        $property_post_id = $sub_value;
                    }
                    else
                    {
                        $value = ( ( isset( $value[0] ) && ! empty( $value[0] )) ? $value[0] : '-' );
                    }
                    
                    if ( strpos($key, 'name') !== false && $value != '-' )
                    {
                        $name = $value;
                    }
                    if ( strpos($key, 'email') !== false && $value != '-' )
                    {
                        $value = '<a href="mailto:' . $value . '">' . $value . '</a>';
                        $email = $value;
                    }

                    echo '<p class="form-field enquiry_details_field">

                            <label>' . ucwords( str_replace('_', ' ', trim($key, "_") ) ) . '</label>

                            ' . nl2br( $value ) . '

                          </p>';
                }
            }

            $enquiry_contact_type = !empty($property_post_id) ? 'Applicant' : 'Contact';
            $enquiry_contact_id = get_post_meta( $enquiry_post_id, '_contact_id', true );

            if ( !empty($enquiry_contact_id) || ( $name !== false || $email !== false ) )
            {
                if( empty($enquiry_contact_id) && !empty($email) )
                {
                    // Check email address doesn't exist already as a contact
                    $args = array(
                        'post_type' => 'contact',
                        'post_status' => 'any',
                        'posts_per_page' => 1,
                        'fields' => 'ids',
                        'meta_query' => array(
                            array(
                                'key' => '_email_address',
                                'value' => strip_tags($email),
                            )
                        )
                    );

                    $contact_query = new WP_Query( $args );

                    if ( $contact_query->have_posts() )
                    {
                        foreach ($contact_query->get_posts() as $p) 
                        {
                            $enquiry_contact_id = $p;

                        }
                    }
                }

                if ( !empty($enquiry_contact_id) )
                {
                    $right_padding = '0';
                    if ( !empty($property_post_id) )
                    {
                        $url_args = array(
                            'applicant_contact_id' => $enquiry_contact_id,
                            'property_id'          => $property_post_id,
                        );
                        $url_args = apply_filters('propertyhive_enquiry_book_viewing_link_args', $url_args);
                        echo '<a href="' . add_query_arg( array( $url_args ), admin_url('post-new.php?post_type=viewing') ) . '" class="button" style="position:absolute; top:0; right:00;">' . __( 'Book Viewing', 'propertyhive' ) . '</a>';
                        $right_padding = '105px';
                    }

                    echo '<a href="' . get_edit_post_link($enquiry_contact_id, '') . '" class="button" style="position:absolute; top:0; right:' . $right_padding . ';">' . __( 'View ' . $enquiry_contact_type, 'propertyhive' ) . '</a>';
                }
                else
                {
                ?>
                    <a href="" id="create_contact_from_enquiry_button" class="button" style="position:absolute; top:0; right:0;"><?php echo __( 'Create ' . $enquiry_contact_type, 'propertyhive' ); ?></a>

                    <script>
                        jQuery(document).ready(function($)
                        {
                            $('a#create_contact_from_enquiry_button').click(function(e)
                            {
                                if ($(this).attr('href') == '')
                                {
                                    e.preventDefault();

                                    $(this).attr('disabled', 'disabled');
                                    $(this).html('<?php echo __( 'Creating ' . $enquiry_contact_type . '...', 'propertyhive' ); ?>');

                                    var data = {
                                        action:         'propertyhive_create_contact_from_enquiry',
                                        post_id:        <?php echo $enquiry_post_id; ?>,
                                        security:       '<?php echo wp_create_nonce( 'create-content-from-enquiry-nonce-' . $enquiry_post_id ); ?>',
                                    };

                                    var that = this;
                                    $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                                        if (response.error)
                                        {
                                            $(that).attr('disabled', false);
                                            $(that).html('<?php echo __( 'Create ' . $enquiry_contact_type, 'propertyhive' ); ?>');
                                        }
                                        if (response.success)
                                        {
                                            $(that).attr('disabled', false);
                                            $(that).addClass('button-primary');
                                            $(that).attr('href', response.success);
                                            $(that).html('<?php echo __( $enquiry_contact_type . ' Created. View Now', 'propertyhive' ); ?>');
                                        }
                                    }, 'json');
                                }
                            });
                        });
                    </script>
                <?php
                }
            }
        }

        do_action('propertyhive_enquiry_details_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        if ( isset($_POST['_added_manually']) && $_POST['_added_manually'] == 'yes' )
        {
            update_post_meta( $post_id, '_added_manually', ph_clean($_POST['_added_manually']) );
            update_post_meta( $post_id, 'name', ph_clean($_POST['name']) );
            update_post_meta( $post_id, 'email', ph_clean($_POST['email']) );
            update_post_meta( $post_id, 'telephone', ph_clean($_POST['telephone']) );
            update_post_meta( $post_id, 'body', sanitize_textarea_field($_POST['body']) );
            if ( isset($_POST['property_id']) && $_POST['property_id'] != '' ) { update_post_meta( $post_id, 'property_id', (int)$_POST['property_id'] ); }
        }
    }

}
