<?php
/**
 * Tenancy Actions
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Tenancy_Actions
 */
class PH_Meta_Box_Tenancy_Actions {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        $status = get_post_meta( $post->ID, '_status', TRUE );

        echo '<div id="propertyhive_tenancy_actions_meta_box_container">

        	Loading...';

        echo '</div>';
?>
<script>

jQuery(document).ready(function($)
{
	$('#propertyhive_tenancy_actions_meta_box_container').on('click', 'a.tenancy-action', function(e)
	{
		e.preventDefault();

		var this_href = $(this).attr('href');

		$(this).attr('disabled', 'disabled');

		if ( this_href == '#action_panel_application_withdrawn' )
		{
			var data = {
		        action:         'propertyhive_application_withdrawn',
		        tenancy_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'tenancy-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	window.location.href = window.location.href;
		    }, 'json');
			return;
		}

		$('#propertyhive_tenancy_actions_meta_box').stop().fadeOut(300, function()
		{
			$(this_href).stop().fadeIn(300, function()
			{
				
			});
		});
	});

	$('#propertyhive_tenancy_actions_meta_box_container').on('click', 'a.action-cancel', function(e)
	{
		e.preventDefault();

		redraw_tenancy_actions();
	});
});

jQuery(window).load(function($)
{
	redraw_tenancy_actions();
});

function redraw_tenancy_actions()
{
	jQuery('#propertyhive_tenancy_actions_meta_box_container').html('Loading...');

	var data = {
        action:         'propertyhive_get_tenancy_actions',
        tenancy_id:    	<?php echo $post->ID; ?>,
        security:       '<?php echo wp_create_nonce( 'tenancy-actions' ); ?>',
    };

    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
    {
    	jQuery('#propertyhive_tenancy_actions_meta_box_container').html(response);
    }, 'html');
}

</script>
<?php
    }
}