<?php
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

jQuery(window).load(function()
{
    redraw_offer_details_meta_box();
});

function redraw_offer_details_meta_box()
{
    jQuery('#propertyhive_offer_details_meta_box_container').html('Loading...');

    var data = {
        action:         'propertyhive_get_offer_details_meta_box',
        offer_id:       <?php echo $post->ID; ?>,
        security:       '<?php echo wp_create_nonce( 'offer-details-meta-box' ); ?>',
    };

    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
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
        global $wpdb;

        $status = get_post_meta( $post_id, '_status', TRUE );
        if ( $status == '' )
        {
            update_post_meta( $post_id, '_status', 'pending' );
        }

        update_post_meta( $post_id, '_offer_date_time', $_POST['_offer_date'] . ' ' . $_POST['_offer_time_hours'] . ':' . $_POST['_offer_time_minutes'] . ':00' );

        $amount = preg_replace("/[^0-9]/", '', $_POST['_amount']);
        update_post_meta( $post_id, '_amount', $amount );
    }

}
