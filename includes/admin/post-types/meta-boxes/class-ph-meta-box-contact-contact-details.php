<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Contact_Contact_Details; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Contact_Contact_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        echo '<input type="hidden" name="propertyhive_contact_details_present" value="1">';
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

        propertyhive_wp_text_input( array(
            'id' => '_dear',
            'label' => __( 'Dear', 'propertyhive' ),
            'desc_tip' => true,
            'description' => __( 'How the contact\'s name should appear on the salutation line of documents and emails. This can be brought out with the contact_dear tags. If this isn\'t populated, the whole contact name will be used', 'propertyhive' ),
            'type' => 'text'
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
        // Verify the form boundary here as well as in the central save dispatcher.
        if ( ! isset( $_POST['propertyhive_meta_nonce'] ) || ! is_string( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['propertyhive_meta_nonce'] ) ), 'propertyhive_save_data' ) ) {
            return;
        }
        if ( ! current_user_can( 'manage_propertyhive' ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['post_ID'] ) || ! is_scalar( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== (int) $post_id ) {
            return;
        }

        global $wpdb;
        
        if ( isset( $_POST['_telephone_number'] ) && is_string( $_POST['_telephone_number'] ) ) {
            $ph_contact_telephone_value = sanitize_text_field( wp_unslash( $_POST['_telephone_number'] ) );
            update_post_meta( $post_id, '_telephone_number', wp_slash( $ph_contact_telephone_value ) );
            update_post_meta( $post_id, '_telephone_number_clean', ph_clean( ph_clean_telephone_number( $ph_contact_telephone_value ) ) );
        }
        if ( isset( $_POST['_email_address'] ) && is_string( $_POST['_email_address'] ) ) {
            $ph_contact_email_value = sanitize_text_field( wp_unslash( $_POST['_email_address'] ) );
            update_post_meta( $post_id, '_email_address', str_replace( ' ', '', wp_slash( $ph_contact_email_value ) ) );
        }
        if ( isset( $_POST['_contact_notes'] ) && is_string( $_POST['_contact_notes'] ) ) {
            update_post_meta( $post_id, '_contact_notes', wp_slash( sanitize_textarea_field( wp_unslash( $_POST['_contact_notes'] ) ) ) );
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Check list element types before unslashing and sanitizing the complete list below.
        if ( isset( $_POST['_forbidden_contact_methods'] ) && is_array( $_POST['_forbidden_contact_methods'] ) && count( array_filter( $_POST['_forbidden_contact_methods'], 'is_string' ) ) === count( $_POST['_forbidden_contact_methods'] ) ) {
            update_post_meta( $post_id, '_forbidden_contact_methods', wp_slash( ph_clean( wp_unslash( $_POST['_forbidden_contact_methods'] ) ) ) );
        } elseif ( ! isset( $_POST['_forbidden_contact_methods'] ) && isset( $_POST['propertyhive_contact_details_present'] ) ) {
            update_post_meta( $post_id, '_forbidden_contact_methods', '' );
        }
        if ( isset( $_POST['_dear'] ) && is_string( $_POST['_dear'] ) ) {
            update_post_meta( $post_id, '_dear', wp_slash( sanitize_text_field( wp_unslash( $_POST['_dear'] ) ) ) );
        }

        do_action( 'propertyhive_save_contact_contact_details', $post_id );
    }

}
