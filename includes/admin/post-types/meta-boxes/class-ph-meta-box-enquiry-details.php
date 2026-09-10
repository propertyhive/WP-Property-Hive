<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Enquiry_Details; preserving the existing PH_* class name is required for plugin and extension compatibility.
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
            // These GET values only prefill the read-only add form; saving the enquiry
            // is separately protected by the meta-box nonce and CRM capabilities.
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Add-screen prefill values do not mutate state and are escaped by the field helpers below.
            $request_get = wp_unslash( $_GET );
            $name_prefill = ( isset( $request_get['name'] ) && is_scalar( $request_get['name'] ) ) ? sanitize_text_field( $request_get['name'] ) : '';
            $email_prefill = ( isset( $request_get['email'] ) && is_scalar( $request_get['email'] ) ) ? sanitize_text_field( $request_get['email'] ) : '';
            $telephone_prefill = ( isset( $request_get['telephone'] ) && is_scalar( $request_get['telephone'] ) ) ? sanitize_text_field( $request_get['telephone'] ) : '';
            $property_id_prefill = ( isset( $request_get['property_id'] ) && is_scalar( $request_get['property_id'] ) ) ? absint( $request_get['property_id'] ) : 0;

            $args = array( 
                'id' => '_added_manually', 
                'value' => 'yes'
            );
            propertyhive_wp_hidden_input( $args );

            $args = array( 
                'id' => 'name', 
                'label' => __( 'Name', 'propertyhive' ), 
                'desc_tip' => false,
                'type' => 'text',
                'value' => $name_prefill
            );
            propertyhive_wp_text_input( $args );

            $args = array( 
                'id' => 'email', 
                'label' => __( 'Email Address', 'propertyhive' ), 
                'desc_tip' => false,
                'type' => 'email',
                'value' => $email_prefill
            );
            propertyhive_wp_text_input( $args );

            $args = array( 
                'id' => 'telephone', 
                'label' => __( 'Telephone', 'propertyhive' ), 
                'desc_tip' => false,
                'type' => 'text',
                'value' => $telephone_prefill
            );
            propertyhive_wp_text_input( $args );

            $args = array( 
                'id' => 'body', 
                'label' => __( 'Body', 'propertyhive' ), 
                'desc_tip' => false,
            );
            propertyhive_wp_textarea_input( $args );

echo '<p class="form-field">
            
                <label for="viewing_property_search">' . esc_html(__('Search Properties', 'propertyhive')) . '</label>
                
                <span style="position:relative;">

                    <input type="text" name="viewing_property_search" id="viewing_property_search" style="width:100%;" placeholder="' . esc_attr(__( 'Search Properties', 'propertyhive' )) . '..." autocomplete="false">

                    <div id="viewing_search_property_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="viewing_selected_properties" style="display:none;"></div>

                </span>
                
            </p>';

            echo '<input type="hidden" name="property_id" id="property_id" value="">';
?>
<script>

var viewing_selected_properties = [<?php 
    if ( $property_id_prefill > 0 )
    { 
        $property = new PH_Property( $property_id_prefill );
        echo wp_json_encode( array( 'id' => $property_id_prefill, 'post_title' => $property->get_formatted_full_address() ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
    } 
?>];
var viewing_search_properties_timeout;
var viewing_search_properties_xhr = jQuery.ajax({});

jQuery(document).ready(function($)
{
    viewing_update_selected_properties();
    
    $('#viewing_property_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            e.preventDefault();
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

        //viewing_selected_properties = []; // reset to only allow one property for now
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
        jQuery('#viewing_search_property_results').html('<div style="padding:10px;"><?php echo esc_html__( 'Enter', 'propertyhive' ); ?> ' + (3 - keyword.length ) + ' <?php echo esc_html__( 'more characters', 'propertyhive' ); ?>...</div>');
        jQuery('#viewing_search_property_results').show();
        return false;
    }

    var data = {
        action:         'propertyhive_search_properties',
        keyword:        keyword,
        security:       '<?php echo esc_js(wp_create_nonce( 'search-properties' )); ?>',
    };
    viewing_search_properties_xhr.abort(); // cancel previous request
    viewing_search_properties_xhr = jQuery.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
    {
        if (response == '' || response.length == 0)
        {
            jQuery('#viewing_search_property_results').empty().append(jQuery('<div>').css('padding', '10px').text(<?php echo wp_json_encode( __( 'No results found for', 'propertyhive' ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?> + " '" + keyword + "'"));
        }
        else
        {
            jQuery('#viewing_search_property_results').html('<ul style="margin:0; padding:0;"></ul>');
            for ( var i in response )
            {
                var result_link = jQuery('<a>').attr({ href: response[i].ID, 'data-viewing-owner-id': response[i].owner_id, 'data-viewing-owner-name': response[i].owner_name }).css({ color: '#666', display: 'block', padding: '7px 10px', background: '#FFF', borderBottom: '1px solid #DDD', textDecoration: 'none' }).text(response[i].post_title);
                jQuery('#viewing_search_property_results ul').append(jQuery('<li>').css({ margin: 0, padding: 0 }).append(result_link));
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
        var hidden_field_values = new Array();
        for ( var i in viewing_selected_properties )
        {
            var remove_link = jQuery('<a>').attr({ href: viewing_selected_properties[i].id, 'data-viewing-owner-id': viewing_selected_properties[i].owner_id, 'data-viewing-owner-name': viewing_selected_properties[i].owner_name }).addClass('viewing-remove-property').css({ color: 'inherit', textDecoration: 'none' }).append(jQuery('<span>').addClass('dashicons dashicons-no-alt'));
            jQuery('#viewing_selected_properties ul').append(jQuery('<li>').append(remove_link).append(document.createTextNode(' ' + viewing_selected_properties[i].post_title)));
            if (hidden_field_values.indexOf(viewing_selected_properties[i].id) === -1) 
            {
                hidden_field_values.push(viewing_selected_properties[i].id);
            }
        }
        jQuery('#viewing_selected_properties').show();
        jQuery('#property_id').val(hidden_field_values.join("|"));
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
                'utm_source',
                'utm_medium',
                'utm_term',
                'utm_content',
                'utm_campaign',
                'gclid', 
                'fbclid'
            );
            
            $enquiry_post_id = $post->ID;
            $enquiry_meta = get_metadata( 'post', $post->ID );
            
            $name = false;
            $email = false;
            $property_post_id = false;

            foreach ($enquiry_meta as $key => $value)
            {
                if ( ! in_array( $key, $ignore_keys ) && ( substr( $key, 0, 1 ) != '_' || $key == '_property_id' ) && strpos($key, 'captcha') === FALSE )
                {
                    $is_html = false;

                    if ( $key == '_property_id' || $key == 'property_id' )
                    {
                        $property_links = array();
                        foreach ( $value as $sub_value)
                        {
                            if ( !empty($sub_value) )
                            {
                                $property_links[] = '<a href="' . esc_url(get_edit_post_link( $sub_value )) . '">' . esc_html(get_the_title( $sub_value )) . '</a>';
                                $property_post_id = $sub_value;
                            }
                        }
                        $value = implode('<br>', $property_links);

                        $key = 'property';

                        $is_html = true;
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
                        $value = '<a href="mailto:' . esc_attr($value) . '">' . esc_html($value) . '</a>';
                        $email = $value;
                        $is_html = true;
                    }

                    echo '<p class="form-field enquiry_details_field">

                            <label>' . esc_html( ucwords( str_replace('_', ' ', trim($key, "_") ) ) ) . '</label>';

                    if ( $is_html )
                    {
                        echo wp_kses(
                            nl2br( $value ),
                            array(
                                'br' => array(),
                                'a'  => array(
                                    'href' => array(),
                                    'title' => array(),
                                    'target' => array(),
                                    'rel' => array(),
                                ),
                            )
                        );
                    }
                    else
                    {
                        echo nl2br( esc_html( $value ) );
                    }

                    echo '</p>';
                }
            }

            $enquiry_contact_type = !empty($property_post_id) ? __( 'Applicant', 'propertyhive' ) : __( 'Contact', 'propertyhive' );
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
                        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Enquiry detail looks up a contact by email with posts_per_page=1 and fields=ids. One scalar email equality; posts_per_page=1.
                        'meta_query' => array(
                            array(
                                'key' => '_email_address',
                                'value' => wp_strip_all_tags($email),
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
                        echo '<a href="' . esc_url(add_query_arg( array( $url_args ), admin_url('post-new.php?post_type=viewing') )) . '" class="button" style="position:absolute; top:0; right:0;">' . esc_html(__( 'Book Viewing', 'propertyhive' )) . '</a>';
                        $right_padding = '120px';
                    }

                    echo '<a href="' . esc_url(get_edit_post_link($enquiry_contact_id, '')) . '" class="button" style="position:absolute; top:0; right:' . esc_attr($right_padding) . ';">' . esc_html(__( 'View', 'propertyhive' ) . ' ' . $enquiry_contact_type) . '</a>';
                }
                else
                {
                ?>
                    <a href="" id="create_contact_from_enquiry_button" class="button" style="position:absolute; top:0; right:0;"><?php echo esc_html(__( 'Create', 'propertyhive' ) . ' ' . $enquiry_contact_type); ?></a>

                    <script>
                        jQuery(document).ready(function($)
                        {
                            $('a#create_contact_from_enquiry_button').click(function(e)
                            {
                                if ($(this).attr('href') == '')
                                {
                                    e.preventDefault();

                                    $(this).attr('disabled', 'disabled');
                                    $(this).text(<?php echo wp_json_encode( __( 'Creating', 'propertyhive' ) . ' ' . $enquiry_contact_type . '...' ); ?>);

                                    var data = {
                                        action:         'propertyhive_create_contact_from_enquiry',
                                        post_id:        <?php echo (int)$enquiry_post_id; ?>,
                                        security:       '<?php echo esc_js(wp_create_nonce( 'create-contact-from-enquiry-nonce-' . $enquiry_post_id )); ?>',
                                    };

                                    var that = this;
                                    $.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) {
                                        if (response.error)
                                        {
                                            $(that).attr('disabled', false);
                                            $(that).text(<?php echo wp_json_encode( __( 'Create ', 'propertyhive' ) . $enquiry_contact_type ); ?>);
                                        }
                                        if (response.success)
                                        {
                                            $(that).attr('disabled', false);
                                            $(that).addClass('button-primary');
                                            $(that).attr('href', response.success);
                                            $(that).text(<?php echo wp_json_encode( $enquiry_contact_type . __( ' Created. View Now', 'propertyhive' ) ); ?>);
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
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }

        // The meta-box nonce was verified above before any submitted fields are read.
        $request_post = wp_unslash( $_POST );
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $request_post['post_ID'] ) || ! is_scalar( $request_post['post_ID'] ) || absint( $request_post['post_ID'] ) !== (int) $post_id ) {
            return;
        }

        global $wpdb;

        $added_manually = ( isset( $request_post['_added_manually'] ) && is_scalar( $request_post['_added_manually'] ) ) ? sanitize_key( $request_post['_added_manually'] ) : '';
        if ( 'yes' === $added_manually )
        {
            update_post_meta( $post_id, '_added_manually', $added_manually );
            $name = ( isset( $request_post['name'] ) && is_scalar( $request_post['name'] ) ) ? sanitize_text_field( $request_post['name'] ) : '';
            $email = ( isset( $request_post['email'] ) && is_scalar( $request_post['email'] ) ) ? sanitize_text_field( $request_post['email'] ) : '';
            $telephone = ( isset( $request_post['telephone'] ) && is_scalar( $request_post['telephone'] ) ) ? sanitize_text_field( $request_post['telephone'] ) : '';
            $body = ( isset( $request_post['body'] ) && is_scalar( $request_post['body'] ) ) ? sanitize_textarea_field( $request_post['body'] ) : '';
            update_post_meta( $post_id, 'name', wp_slash( $name ) );
            update_post_meta( $post_id, 'email', wp_slash( $email ) );
            update_post_meta( $post_id, 'telephone', wp_slash( $telephone ) );
            update_post_meta( $post_id, 'body', wp_slash( $body ) );

            delete_post_meta( $post_id, 'property_id' );
            $property_id_input = ( isset( $request_post['property_id'] ) && is_scalar( $request_post['property_id'] ) ) ? sanitize_text_field( $request_post['property_id'] ) : '';
            if ( '' !== $property_id_input )
            {
                $explode_property_id = explode( '|', $property_id_input );
                foreach ( $explode_property_id as $property_id )
                {
                    $property_id = absint( $property_id );
                    if ( $property_id > 0 ) {
                        add_post_meta( $post_id, 'property_id', $property_id );
                    }
                }
            }
        }
    }

}
