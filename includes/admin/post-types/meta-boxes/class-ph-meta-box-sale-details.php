<?php
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
        global $wpdb;

        $status = get_post_meta( $post_id, '_status', TRUE );
        if ( $status == '' )
        {
            update_post_meta( $post_id, '_status', 'current' );
        }

        update_post_meta( $post_id, '_sale_date_time', ph_clean($_POST['_sale_date']) . ' 00:00:00' );

        $amount = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_amount']));
        update_post_meta( $post_id, '_amount', $amount );

        do_action( 'propertyhive_save_sale_details', $post_id );
    }

}
