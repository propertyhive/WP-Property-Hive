<?php
/**
 * Contact Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Contact_Contact_Details
 */
class PH_Meta_Box_Contact_Contact_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        propertyhive_wp_text_input( array( 
            'id' => '_telephone_number', 
            'label' => __( 'Telephone Number', 'propertyhive' ), 
            'desc_tip' => false,
            'type' => 'text'
        ) );
        
        propertyhive_wp_text_input( array( 
            'id' => '_email_address', 
            'label' => __( 'Email Address', 'propertyhive' ), 
            'desc_tip' => true,
            'description' => __( 'If the contact has multiple email addresses simply separate them using a comma', 'propertyhive' ), 
            'type' => 'text'
        ) );

        propertyhive_wp_checkboxes( array( 
            'id' => '_forbidden_contact_methods', 
            'label' => __( 'Do Not Contact Via', 'propertyhive' ), 
            'options' => array(
                'telephone' => 'Telephone',
                'email' => 'Email'
            )
        ) );
        
        propertyhive_wp_textarea_input( array( 
            'id' => '_contact_notes', 
            'label' => __( 'Contact Notes', 'propertyhive' ), 
            'desc_tip' => false,
            'placeholder' => __( 'e.g. Works nights so do not call between 11am and 2pm', 'propertyhive' ), 
        ) );
        

        do_action('propertyhive_contact_contact_details_fields');
	    
        echo '</div>';
        
        echo '</div>';
        /*
        
?>
<script language="javascript" type="text/javascript">

    var form_validated = false;
    jQuery(document).ready(function() 
    {
        jQuery('#post').submit(function() 
        {
            if ( !form_validated )
            {
                var form_data = jQuery( this ).serialize();

                var data = {
                    action: 'propertyhive_validate_save_contact',
                    security: '<?php echo wp_create_nonce( 'contact-save-validation' ); ?>',
                    form_data: form_data
                };

                jQuery.post(ajaxurl, data, function(response) 
                {
                    if ( response.errors && response.errors.length > 0 ) 
                    {
                        alert(response.errors.join("\n"));
                        return false;
                    }
                    else
                    {
                        form_validated = true;
                        jQuery('#post').submit();
                        return true;
                    }
                }, 'json');

                return false;
            }
        });
    });

</script>
<?php
        */
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        update_post_meta( $post_id, '_telephone_number',  ph_clean($_POST['_telephone_number']) );
        update_post_meta( $post_id, '_telephone_number_clean',  ph_clean($_POST['_telephone_number']), true );
        update_post_meta( $post_id, '_email_address', str_replace(" ", "", ph_clean($_POST['_email_address'])) );
        update_post_meta( $post_id, '_contact_notes', sanitize_textarea_field($_POST['_contact_notes']) );
        update_post_meta( $post_id, '_forbidden_contact_methods', ( (isset($_POST['_forbidden_contact_methods'])) ? ph_clean($_POST['_forbidden_contact_methods']) : '' ) );

        do_action( 'propertyhive_save_contact_contact_details', $post_id );
    }

}
