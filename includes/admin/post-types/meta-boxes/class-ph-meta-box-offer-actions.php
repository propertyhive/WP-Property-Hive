<?php
/**
 * Offer Actions
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Offer_Actions
 */
class PH_Meta_Box_Offer_Actions {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        $status = get_post_meta( $post->ID, '_status', TRUE );

        echo '<div id="propertyhive_offer_actions_meta_box_container">

        	Loading...';

        echo '</div>';
?>
<script>

jQuery(document).ready(function($)
{
	$('#propertyhive_offer_actions_meta_box_container').on('click', 'a.offer-action', function(e)
	{
		e.preventDefault();

		var this_href = $(this).attr('href');

		$(this).attr('disabled', 'disabled');

		if ( this_href == '#action_panel_offer_accepted' )
		{
			var data = {
		        action:         'propertyhive_offer_accepted',
		        offer_id:    	<?php echo (int)$post->ID; ?>,
		        security:       '<?php echo esc_js(wp_create_nonce( 'offer-actions' )); ?>',
		    };
			jQuery.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
		    {
		    	redraw_offer_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_offer_declined' )
		{
			var data = {
		        action:         'propertyhive_offer_declined',
		        offer_id:    	<?php echo (int)$post->ID; ?>,
		        security:       '<?php echo esc_js(wp_create_nonce( 'offer-actions' )); ?>',
		    };
			jQuery.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
		    {
		    	redraw_offer_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_offer_withdrawn' )
		{
			var data = {
		        action:         'propertyhive_offer_withdrawn',
		        offer_id:    	<?php echo (int)$post->ID; ?>,
		        security:       '<?php echo esc_js(wp_create_nonce( 'offer-actions' )); ?>',
		    };
			jQuery.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
		    {
		    	redraw_offer_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_offer_revert_pending' )
		{
			var data = {
		        action:         'propertyhive_offer_revert_pending',
		        offer_id:    	<?php echo (int)$post->ID; ?>,
		        security:       '<?php echo esc_js(wp_create_nonce( 'offer-actions' )); ?>',
		    };
			jQuery.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
		    {
		    	redraw_offer_actions();
		    }, 'json');
			return;
		}

		$('#propertyhive_offer_actions_meta_box').stop().fadeOut(300, function()
		{
			$(this_href).stop().fadeIn(300, function()
			{
				
			});
		});
	});

	$('#propertyhive_offer_actions_meta_box_container').on('click', 'a.action-cancel', function(e)
	{
		e.preventDefault();

		redraw_offer_actions();
	});
});

jQuery(window).on('load', function($)
{
	redraw_offer_actions();
});

function redraw_offer_actions()
{
	jQuery('#propertyhive_offer_actions_meta_box_container').html('Loading...');

	var data = {
        action:         'propertyhive_get_offer_actions',
        offer_id:    	<?php echo (int)$post->ID; ?>,
        security:       '<?php echo esc_js(wp_create_nonce( 'offer-actions' )); ?>',
    };

    jQuery.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
    {
    	jQuery('#propertyhive_offer_actions_meta_box_container').html(response);

    	jQuery(document).trigger('ph:adminOfferActionsRedrawn');
    	jQuery(document).trigger('ph:adminPostActionsRedrawn', ['offer']);
    }, 'html');

    redraw_offer_details_meta_box();
}

</script>
<?php
    }
}