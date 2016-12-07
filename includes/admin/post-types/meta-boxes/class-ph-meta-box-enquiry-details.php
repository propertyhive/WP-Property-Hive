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
        global $wpdb, $thepostid, $post;
        
        $ignore_keys = array(
            '_status',
            '_source',
            '_negotiator_id',
            '_office_id',
            '_action'
        );
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group" style="position:relative;">';
        
        $enquiry_meta = get_metadata( 'post', $post->ID );
        
        $name = false;
        $email = false;

        foreach ($enquiry_meta as $key => $value)
        {
            if ( ! in_array( $key, $ignore_keys ) && substr( $key, 0, 1 ) != '_' )
            {
                if ( $key == '_property_id' )
                {
                    $value = '<a href="' . get_edit_post_link( $value[0] ) . '">' . get_the_title( $value[0] ) . '</a>';
                }
                else
                {
                    $value = ( ( isset( $value[0] ) && ! empty( $value[0] )) ? $value[0] : '-' );
                }
                
                echo '<p class="form-field enquiry_details_field">
        
                        <label for="source">' . $key . '</label>
                      
                        ' . nl2br( $value ) . '
                      
                      </p>';

                if ( strpos($key, 'name') !== false )
                {
                    $name = $value;
                }
                if ( strpos($key, 'email') !== false )
                {
                    $email = $value;
                }
            }
        }

        if ( $name !== false && $email !== false )
        {
            $enquiry_post = $post;

            // Check email address doesn't exist already as a contact
            $args = array(
                'post_type' => 'contact',
                'post_status' => 'any',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'key' => '_email_address',
                        'value' => $email,
                    )
                )
            );

            $contact_query = new WP_Query( $args );

            if ( $contact_query->have_posts() )
            {
                while ( $contact_query->have_posts() )
                {
                    $contact_query->the_post();

                    echo '<a href="' . get_edit_post_link(get_the_ID(), '') . '" class="button" style="position:absolute; top:0; right:0;">' . __( 'View Contact', 'propertyhive' ) . '</a>';
                }
            }
            else
            {
?>
                <a href="" id="create_contact_from_enquiry_button" class="button" style="position:absolute; top:0; right:0;"><?php echo __( 'Create Contact', 'propertyhive' ); ?></a>

                <script>
                    jQuery(document).ready(function($)
                    {
                        $('a#create_contact_from_enquiry_button').click(function(e)
                        {
                            if ($(this).attr('href') == '')
                            {
                                e.preventDefault();

                                $(this).attr('disabled', 'disabled');
                                $(this).html('<?php echo __( 'Creating Contact...', 'propertyhive' ); ?>');

                                var data = {
                                    action:         'propertyhive_create_contact_from_enquiry',
                                    post_id:        <?php echo $enquiry_post->ID; ?>,
                                    security:       '<?php echo wp_create_nonce( 'create-content-from-enquiry-nonce-' . $enquiry_post->ID ); ?>',
                                };

                                var that = this;
                                $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                                    if (response.error)
                                    {
                                        $(that).attr('disabled', false);
                                        $(that).html('<?php echo __( 'Create Contact', 'propertyhive' ); ?>');
                                    }
                                    if (response.success)
                                    {
                                        $(that).attr('disabled', false);
                                        $(that).addClass('button-primary');
                                        $(that).attr('href', response.success);
                                        $(that).html('<?php echo __( 'Contact Created. View Now', 'propertyhive' ); ?>');
                                    }
                                }, 'json');
                            }
                        });
                    });
                </script>
<?php
            }
            wp_reset_postdata();

            $post = $enquiry_post;
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
        
        
    }

}
