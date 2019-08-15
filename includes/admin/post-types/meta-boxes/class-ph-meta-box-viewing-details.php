<?php
/**
 * Viewing Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Viewing_Details
 */
class PH_Meta_Box_Viewing_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        $viewing = new PH_Viewing($post->ID);
        
        echo '<div id="propertyhive_viewing_details_meta_box_container">Loading...</div>';
?>
<script>

jQuery(window).load(function()
{
    redraw_viewing_details_meta_box();
});

function redraw_viewing_details_meta_box()
{
    jQuery('#propertyhive_viewing_details_meta_box_container').html('Loading...');

    var data = {
        action:         'propertyhive_get_viewing_details_meta_box',
        viewing_id:     <?php echo $post->ID; ?>,
        security:       '<?php echo wp_create_nonce( 'viewing-details-meta-box' ); ?>',
    };

    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
    {
        jQuery('#propertyhive_viewing_details_meta_box_container').html(response);
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
    
        update_post_meta( $post_id, '_feedback', sanitize_textarea_field($_POST['_feedback']) );

        do_action( 'propertyhive_save_viewing_details', $post_id );
    }

}
