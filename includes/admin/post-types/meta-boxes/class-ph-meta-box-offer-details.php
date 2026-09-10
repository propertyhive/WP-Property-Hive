<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Offer Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Offer_Details
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Offer_Details; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Offer_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        echo '<div id="propertyhive_offer_details_meta_box_container">Loading...</div>';
?>
<script>

jQuery(window).on('load', function()
{
    redraw_offer_details_meta_box();
});

function redraw_offer_details_meta_box()
{
    jQuery('#propertyhive_offer_details_meta_box_container').html('Loading...');

    var data = {
        action:         'propertyhive_get_offer_details_meta_box',
        offer_id:       <?php echo (int)$post->ID; ?>,
        security:       '<?php echo esc_js(wp_create_nonce( 'offer-details-meta-box' )); ?>',
    };

    jQuery.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
    {
        jQuery('#propertyhive_offer_details_meta_box_container').html(response);
        initialise_datepicker();
    }, 'html');
}

</script>
<?php
        
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

        $status = get_post_meta( $post_id, '_status', TRUE );
        if ( $status == '' )
        {
            update_post_meta( $post_id, '_status', 'pending' );
        }

        $request_post = wp_unslash( $_POST );
        if ( isset( $request_post['_offer_date'], $request_post['_offer_time_hours'], $request_post['_offer_time_minutes'] ) && is_string( $request_post['_offer_date'] ) && is_scalar( $request_post['_offer_time_hours'] ) && is_scalar( $request_post['_offer_time_minutes'] ) )
        {
            $hours = str_pad( min( 23, absint( $request_post['_offer_time_hours'] ) ), 2, '0', STR_PAD_LEFT );
            $minutes = str_pad( min( 59, absint( $request_post['_offer_time_minutes'] ) ), 2, '0', STR_PAD_LEFT );
            $date = sanitize_text_field( $request_post['_offer_date'] );
            update_post_meta( $post_id, '_offer_date_time', wp_slash( $date . ' ' . $hours . ':' . $minutes . ':00' ) );
        }

        if ( isset( $request_post['_amount'] ) && is_string( $request_post['_amount'] ) )
        {
            // Keep the existing decimal separator and currency formatting contract.
            $amount = preg_replace( '/[^0-9.]/', '', sanitize_text_field( $request_post['_amount'] ) );
            update_post_meta( $post_id, '_amount', $amount );
        }

        do_action( 'propertyhive_save_offer_details', $post_id );
    }

}
