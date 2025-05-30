<?php
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
        global $wpdb;
        
        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' || $status == 'won' || $status == 'lost' || $status == 'instructed' )
        {
            $department = get_post_meta( $post_id, '_department', TRUE );

            if ( $department == 'residential-sales' || ph_get_custom_department_based_on($department) == 'residential-sales' )
            {
                $price = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_valued_price']));
                update_post_meta( $post_id, '_valued_price', $price );
                update_post_meta( $post_id, '_valued_price_actual', $price );
            }
            elseif ( $department == 'residential-lettings' || ph_get_custom_department_based_on($department) == 'residential-lettings' )
            {
                $rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['_valued_rent']));
                update_post_meta( $post_id, '_valued_rent', $rent );

                update_post_meta( $post_id, '_rent_frequency', ph_clean($_POST['_valued_rent_frequency']) );

                switch (ph_clean($_POST['_valued_rent_frequency']))
                {
                    case "pd": { $price = ($rent * 365) / 12; break; }
                    case "pppw":
                    {
                        $bedrooms = get_post_meta( $post_id, '_bedrooms', true );
                        if ( ( $bedrooms !== FALSE && $bedrooms != 0 && $bedrooms != '' ) && apply_filters( 'propertyhive_pppw_to_consider_bedrooms', true ) == true )
                        {
                            $price = (($rent * 52) / 12) * $bedrooms;
                        }
                        else
                        {
                            $price = ($rent * 52) / 12;
                        }
                        break;
                    }
                    case "pw": { $price = ($rent * 52) / 12; break; }
                    case "pcm": { $price = $rent; break; }
                    case "pq": { $price = ($rent * 4) / 12; break; }
                    case "pa": { $price = ($rent / 12); break; }
                }
                update_post_meta( $post_id, '_valued_price_actual', $price );
            }
        }
        if ( $status == 'lost' )
        {
            update_post_meta( $post_id, '_lost_reason', sanitize_textarea_field($_POST['_lost_reason']) );
        }

        do_action( 'propertyhive_save_appraisal_details', $post_id );
    }

}
