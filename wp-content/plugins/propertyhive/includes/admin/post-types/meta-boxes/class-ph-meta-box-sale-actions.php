<?php
/**
 * Sale Actions
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Sale_Actions
 */
class PH_Meta_Box_Sale_Actions {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        $status = get_post_meta( $post->ID, '_status', TRUE );

        echo '<div id="propertyhive_sale_actions_meta_box_container">

        	Loading...';

        echo '</div>';
?>
<script>

jQuery(document).ready(function($)
{
	$('#propertyhive_sale_actions_meta_box_container').on('click', 'a.sale-action', function(e)
	{
		e.preventDefault();

		var this_href = $(this).attr('href');

		$(this).attr('disabled', 'disabled');

		if ( this_href == '#action_panel_sale_exchanged' )
		{
			var data = {
		        action:         'propertyhive_sale_exchanged',
		        sale_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'sale-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	redraw_sale_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_sale_completed' )
		{
			var data = {
		        action:         'propertyhive_sale_completed',
		        sale_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'sale-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	redraw_sale_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_sale_fallen_through' )
		{
			var data = {
		        action:         'propertyhive_sale_fallen_through',
		        sale_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'sale-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	redraw_sale_actions();
		    }, 'json');
			return;
		}

		$('#propertyhive_sale_actions_meta_box').stop().fadeOut(300, function()
		{
			$(this_href).stop().fadeIn(300, function()
			{
				
			});
		});
	});

	$('#propertyhive_sale_actions_meta_box_container').on('click', 'a.action-cancel', function(e)
	{
		e.preventDefault();

		redraw_sale_actions();
	});
});

jQuery(window).load(function($)
{
	redraw_sale_actions();
});

function redraw_sale_actions()
{
	jQuery('#propertyhive_sale_actions_meta_box_container').html('Loading...');

	var data = {
        action:         'propertyhive_get_sale_actions',
        sale_id:    	<?php echo $post->ID; ?>,
        security:       '<?php echo wp_create_nonce( 'sale-actions' ); ?>',
    };

    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
    {
    	jQuery('#propertyhive_sale_actions_meta_box_container').html(response);
    }, 'html');

    redraw_sale_details_meta_box();
}

</script>
<?php
    }
}