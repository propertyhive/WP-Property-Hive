<?php
/**
 * Viewing Actions
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Viewing_Actions
 */
class PH_Meta_Box_Viewing_Actions {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        $status = get_post_meta( $post->ID, '_status', TRUE );

        echo '<div id="propertyhive_viewing_actions_meta_box_container">

        	Loading...';

        echo '</div>';
?>
<script>

jQuery(document).ready(function($)
{
	$('#propertyhive_viewing_actions_meta_box_container').on('click', 'a.viewing-action', function(e)
	{
		e.preventDefault();

		var this_href = $(this).attr('href');

		$(this).attr('disabled', 'disabled');

		if ( this_href == '#action_panel_viewing_carried_out' )
		{
			var data = {
		        action:         'propertyhive_viewing_carried_out',
		        viewing_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'viewing-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	redraw_viewing_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_viewing_cancelled' )
		{
			var data = {
		        action:         'propertyhive_viewing_cancelled',
		        viewing_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'viewing-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	redraw_viewing_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_viewing_feedback_not_required' )
		{
			var data = {
		        action:         'propertyhive_viewing_feedback_not_required',
		        viewing_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'viewing-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	redraw_viewing_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_viewing_revert_feedback_passed_on' )
		{
			var data = {
		        action:         'propertyhive_viewing_feedback_passed_on',
		        viewing_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'viewing-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	redraw_viewing_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_viewing_revert_pending' )
		{
			var data = {
		        action:         'propertyhive_viewing_revert_pending',
		        viewing_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'viewing-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	redraw_viewing_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_viewing_revert_feedback_pending' )
		{
			var data = {
		        action:         'propertyhive_viewing_revert_feedback_pending',
		        viewing_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'viewing-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	redraw_viewing_actions();
		    }, 'json');
			return;
		}

		$('#propertyhive_viewing_actions_meta_box').stop().fadeOut(300, function()
		{
			$(this_href).stop().fadeIn(300, function()
			{
				
			});
		});
	});

	$('#propertyhive_viewing_actions_meta_box_container').on('click', 'a.action-cancel', function(e)
	{
		e.preventDefault();

		redraw_viewing_actions();
	});

	$('#propertyhive_viewing_actions_meta_box_container').on('click', 'a.interested-feedback-action-submit', function(e)
	{
		e.preventDefault();

		$(this).attr('disabled', 'disabled');

		// Submit interested feedback
		var data = {
	        action:         'propertyhive_viewing_interested_feedback',
	        viewing_id:    	<?php echo $post->ID; ?>,
	        feedback:    	$('#_interested_feedback').val(),
	        security:       '<?php echo wp_create_nonce( 'viewing-actions' ); ?>',
	    };

	    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
	    {
	    	redraw_viewing_actions();
	    }, 'json');
	});

	$('#propertyhive_viewing_actions_meta_box_container').on('click', 'a.not-interested-feedback-action-submit', function(e)
	{
		e.preventDefault();

		$(this).attr('disabled', 'disabled');

		// Submit interested feedback
		var data = {
	        action:         'propertyhive_viewing_not_interested_feedback',
	        viewing_id:    	<?php echo $post->ID; ?>,
	        feedback:    	$('#_not_interested_feedback').val(),
	        security:       '<?php echo wp_create_nonce( 'viewing-actions' ); ?>',
	    };

	    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
	    {
	    	redraw_viewing_actions();
	    }, 'json');
	})
});

jQuery(window).load(function($)
{
	redraw_viewing_actions();
});

function redraw_viewing_actions()
{
	jQuery('#propertyhive_viewing_actions_meta_box_container').html('Loading...');

	var data = {
        action:         'propertyhive_get_viewing_actions',
        viewing_id:    	<?php echo $post->ID; ?>,
        security:       '<?php echo wp_create_nonce( 'viewing-actions' ); ?>',
    };

    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
    {
    	jQuery('#propertyhive_viewing_actions_meta_box_container').html(response);
    }, 'html');

    redraw_viewing_details_meta_box();
}

</script>
<?php
    }
}