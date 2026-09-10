<?php
// phpcs:set WordPress.Security.ValidatedSanitizedInput customSanitizingFunctions[] ph_clean
// ph_clean() recursively sanitizes text; presence, shape and unslashing checks remain separate.

/**
 * Appraisal Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Appraisal_Details
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Appraisal_Details; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Appraisal_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        $appraisal = new PH_Appraisal($post->ID);
        
        echo '<div id="propertyhive_appraisal_details_meta_box_container">Loading...</div>';
?>
<script>

jQuery(window).on('load', function()
{
    redraw_appraisal_details_meta_box();
});

function redraw_appraisal_details_meta_box()
{
    jQuery('#propertyhive_appraisal_details_meta_box_container').html('Loading...');

    var data = {
        action:         'propertyhive_get_appraisal_details_meta_box',
        appraisal_id:     <?php echo (int)$post->ID; ?>,
        security:       '<?php echo esc_js(wp_create_nonce( 'appraisal-details-meta-box' )); ?>',
    };

    jQuery.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
    {
        jQuery('#propertyhive_appraisal_details_meta_box_container').html(response);
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

        if ( $status == 'carried_out' || $status == 'won' || $status == 'lost' || $status == 'instructed' )
        {
            $department = get_post_meta( $post_id, '_department', TRUE );

            if ( ( $department == 'residential-sales' || ph_get_custom_department_based_on($department) == 'residential-sales' ) && isset( $_POST['_valued_price'] ) && is_string( $_POST['_valued_price'] ) )
            {
                $price = preg_replace("/[^0-9.]/", '', ph_clean( wp_unslash( $_POST['_valued_price'] ) ));
                update_post_meta( $post_id, '_valued_price', $price );
                update_post_meta( $post_id, '_valued_price_actual', $price );
            }
            elseif ( ( $department == 'residential-lettings' || ph_get_custom_department_based_on($department) == 'residential-lettings' ) && isset( $_POST['_valued_rent'], $_POST['_valued_rent_frequency'] ) && is_string( $_POST['_valued_rent'] ) && is_string( $_POST['_valued_rent_frequency'] ) )
            {
                $rent = preg_replace("/[^0-9.]/", '', ph_clean( wp_unslash( $_POST['_valued_rent'] ) ));
                $frequency = ph_clean( wp_unslash( $_POST['_valued_rent_frequency'] ) );
                if ( ( '' !== $rent && ! is_numeric( $rent ) ) || ! in_array( $frequency, array( 'pd', 'pppw', 'pw', 'pcm', 'pq', 'pa' ), true ) ) {
                    return;
                }
                $numeric_rent = (float) $rent;
                update_post_meta( $post_id, '_valued_rent', $rent );

                update_post_meta( $post_id, '_rent_frequency', $frequency );

                switch ($frequency)
                {
                    case "pd": { $price = ($numeric_rent * 365) / 12; break; }
                    case "pppw":
                    {
                        $bedrooms = get_post_meta( $post_id, '_bedrooms', true );
                        if ( ( $bedrooms !== FALSE && $bedrooms != 0 && $bedrooms != '' ) && apply_filters( 'propertyhive_pppw_to_consider_bedrooms', true ) == true )
                        {
                            $price = (($numeric_rent * 52) / 12) * $bedrooms;
                        }
                        else
                        {
                            $price = ($numeric_rent * 52) / 12;
                        }
                        break;
                    }
                    case "pw": { $price = ($numeric_rent * 52) / 12; break; }
                    case "pcm": { $price = $rent; break; }
                    case "pq": { $price = ($numeric_rent * 4) / 12; break; }
                    case "pa": { $price = ($numeric_rent / 12); break; }
                }
                update_post_meta( $post_id, '_valued_price_actual', $price );
            }
        }
        if ( $status == 'lost' && isset( $_POST['_lost_reason'] ) && is_string( $_POST['_lost_reason'] ) )
        {
            update_post_meta( $post_id, '_lost_reason', wp_slash( sanitize_textarea_field( wp_unslash( $_POST['_lost_reason'] ) ) ) );
        }

        do_action( 'propertyhive_save_appraisal_details', $post_id );
    }

}
