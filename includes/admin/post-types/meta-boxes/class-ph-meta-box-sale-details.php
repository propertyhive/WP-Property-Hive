<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Sale Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Sale_Details
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Sale_Details; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Sale_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        echo '<div id="propertyhive_sale_details_meta_box_container">Loading...</div>';
?>
<script>

jQuery(window).on('load', function()
{
    redraw_sale_details_meta_box();
});

function redraw_sale_details_meta_box()
{
    jQuery('#propertyhive_sale_details_meta_box_container').html('Loading...');

    var data = {
        action:         'propertyhive_get_sale_details_meta_box',
        sale_id:       <?php echo (int)$post->ID; ?>,
        security:       '<?php echo esc_js(wp_create_nonce( 'sale-details-meta-box' )); ?>',
    };

    jQuery.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
    {
        jQuery('#propertyhive_sale_details_meta_box_container').html(response);
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
            update_post_meta( $post_id, '_status', 'current' );
        }

        $request_post = wp_unslash( $_POST );
        if ( isset( $request_post['_sale_date'] ) && is_string( $request_post['_sale_date'] ) )
        {
            update_post_meta( $post_id, '_sale_date_time', wp_slash( sanitize_text_field( $request_post['_sale_date'] ) . ' 00:00:00' ) );
        }

        if ( isset( $request_post['_amount'] ) && is_string( $request_post['_amount'] ) )
        {
            $amount = preg_replace( '/[^0-9.]/', '', sanitize_text_field( $request_post['_amount'] ) );
            update_post_meta( $post_id, '_amount', $amount );
        }

        do_action( 'propertyhive_save_sale_details', $post_id );
    }

}
