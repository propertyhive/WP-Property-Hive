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

		if ( 
			this_href == '#action_panel_tenancy_offer_accepted' ||
			this_href == '#action_panel_tenancy_offer_declined' ||
			this_href == '#action_panel_tenancy_revert_offer_pending' ||
			this_href == '#action_panel_tenancy_application_withdrawn' ||
			this_href == '#action_panel_tenancy_revert_application_pending' ||
			this_href == '#action_panel_tenancy_renewing' ||
			this_href == '#action_panel_tenancy_not_renewing' ||
			this_href == '#action_panel_tenancy_periodic' ||
			this_href == '#action_panel_tenancy_terminate' ||
			this_href == '#action_panel_tenancy_revert'
		)
		{
			var data = {
		        action:         this_href.replace("#action_panel_", "propertyhive_"),
		        tenancy_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'tenancy-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	history.replaceState(null, null, ' ');
		    	window.location.href = window.location.href;
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_tenancy_date_change' )
		{
			$('#action_panel_tenancy_date_change label').html( jQuery(this).data('date-change-label') );
			$('#_tenancy_date_change_action').val( jQuery(this).data('date-change-action') );
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

	$('#propertyhive_tenancy_actions_meta_box_container').on('click', 'a.tenancy-date-change-action-submit', function(e)
	{
		e.preventDefault();

		$(this).attr('disabled', 'disabled');

		var action = 'propertyhive_tenancy_' + jQuery('#_tenancy_date_change_action').val();

		var data = {
	        action:         action,
	        tenancy_id:    	<?php echo $post->ID; ?>,
	        date: 			$('#_tenancy_date').val(),
	        security:       '<?php echo wp_create_nonce( 'tenancy-actions' ); ?>',
	    };

	    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
	    {
	    	history.replaceState(null, null, ' ');
	    	window.location.href = window.location.href;
	    }, 'json');
	});

	$('#propertyhive_tenancy_actions_meta_box_container').on('click', 'a.tenancy-mark-as-let-action-submit', function(e)
	{
		e.preventDefault();

		$(this).attr('disabled', 'disabled');

		var data = {
	        action:         'propertyhive_tenancy_mark_as_let',
	        tenancy_id:    	<?php echo $post->ID; ?>,
	        availability_id: $('#_mark_as_let_property_availability').val(),
	        remove_applicants_from_mailing_list: $('#_mark_as_let_remove_applicants_from_mailing_list').prop("checked"),
	        update_applicant_address: $('#_mark_as_let_update_applicant_address').prop("checked"),
	        security:       '<?php echo wp_create_nonce( 'tenancy-actions' ); ?>',
	    };

	    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
	    {
	    	history.replaceState(null, null, ' ');
	    	window.location.href = window.location.href;
	    }, 'json');
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