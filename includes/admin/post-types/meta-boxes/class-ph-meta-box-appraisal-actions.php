<?php
/**
 * Appraisal Actions
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Appraisal_Actions
 */
class PH_Meta_Box_Appraisal_Actions {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        $status = get_post_meta( $post->ID, '_status', TRUE );
        $department = get_post_meta( $post->ID, '_department', TRUE );

        echo '<div id="propertyhive_appraisal_actions_meta_box_container">

        	Loading...';

        echo '</div>';
?>
<script>

jQuery(document).ready(function($)
{
	$('#propertyhive_appraisal_actions_meta_box_container').on('click', 'a.appraisal-action', function(e)
	{
		e.preventDefault();

		var this_href = $(this).attr('href');

		$(this).attr('disabled', 'disabled');

		if ( this_href == '#action_panel_appraisal_won' )
		{
			var data = {
		        action:         'propertyhive_appraisal_won',
		        appraisal_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'appraisal-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	redraw_appraisal_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_appraisal_email_owner_booking_confirmation' )
		{
			var data = {
		        action:         'propertyhive_appraisal_email_owner_booking_confirmation',
		        appraisal_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'appraisal-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	if (!response.success)
	        	{
	        		alert('Error: ' + response.data);
	        	}
			    
			    redraw_appraisal_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_appraisal_revert_pending' )
		{
			var data = {
		        action:         'propertyhive_appraisal_revert_pending',
		        appraisal_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'appraisal-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	redraw_appraisal_actions();
		    }, 'json');
			return;
		}

		if ( this_href == '#action_panel_appraisal_revert_carried_out' )
		{
			var data = {
		        action:         'propertyhive_appraisal_revert_carried_out',
		        appraisal_id:    	<?php echo $post->ID; ?>,
		        security:       '<?php echo wp_create_nonce( 'appraisal-actions' ); ?>',
		    };
			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
		    {
		    	redraw_appraisal_actions();
		    }, 'json');
			return;
		}

		$('#propertyhive_appraisal_actions_meta_box').stop().fadeOut(300, function()
		{
			$(this_href).stop().fadeIn(300, function()
			{
				
			});
		});
	});

	$('#propertyhive_appraisal_actions_meta_box_container').on('click', 'a.action-cancel', function(e)
	{
		e.preventDefault();

		redraw_appraisal_actions();
	});

	$('#propertyhive_appraisal_actions_meta_box_container').on('click', 'a.owner-booking-confirmation-action-submit', function(e)
	{
		e.preventDefault();

		var ph_action_button = $(this);
		ph_action_button.attr('disabled', 'disabled');

        var data = {
            action:         'propertyhive_appraisal_email_owner_booking_confirmation',
            appraisal_id:    	<?php echo $post->ID; ?>,
            subject:        $('#_owner_confirmation_email_subject').val(),
            body:           $('#_owner_confirmation_email_body').val(),
            security:       '<?php echo wp_create_nonce( 'appraisal-actions' ); ?>',
        };
        jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
        	if (response.success)
        	{
	            redraw_appraisal_actions();
	        }
	        else
	        {
	        	alert('Error: ' + response.data);
	        	ph_action_button.attr('disabled', false);
	        }
        }, 'json');
	});

	$('#propertyhive_appraisal_actions_meta_box_container').on('click', 'a.cancelled-reason-action-submit', function(e)
	{
		e.preventDefault();

		$(this).attr('disabled', 'disabled');

		// Submit interested feedback
		var data = {
	        action:         'propertyhive_appraisal_cancelled',
	        appraisal_id:    	<?php echo $post->ID; ?>,
	        cancelled_reason: $('#_cancelled_reason').val(),
	        security:       '<?php echo wp_create_nonce( 'appraisal-actions' ); ?>',
	    };

	    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
	    {
	    	redraw_appraisal_actions();
	    }, 'json');
	});

	$('#propertyhive_appraisal_actions_meta_box_container').on('click', 'a.carried-out-action-submit', function(e)
	{
		e.preventDefault();

		$(this).attr('disabled', 'disabled');

		// Submit interested feedback
		var data = {
	        action:         'propertyhive_appraisal_carried_out',
	        appraisal_id:   <?php echo $post->ID; ?>,<?php if ($department == 'residential-sales' || ph_get_custom_department_based_on($department) == 'residential-sales' ) { ?>
	        price: 			$('#_price').val(),<?php }else{ ?>
	        rent: 			$('#_price').val(),
	    	rent_frequency: $('#_rent_frequency').val(),<?php } ?>
	        security:       '<?php echo wp_create_nonce( 'appraisal-actions' ); ?>',
	    };

	    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
	    {
	    	redraw_appraisal_actions();
	    }, 'json');
	});

	$('#propertyhive_appraisal_actions_meta_box_container').on('click', 'a.instructed-action-submit', function(e)
	{
		e.preventDefault();

		$(this).attr('disabled', 'disabled');

		// Submit interested feedback
		var data = {
	        action:         'propertyhive_appraisal_instructed',
	        appraisal_id:   <?php echo $post->ID; ?>,
	        security:       '<?php echo wp_create_nonce( 'appraisal-actions' ); ?>',
	    };

	    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
	    {
	    	redraw_appraisal_actions();
	    }, 'json');
	});

	$('#propertyhive_appraisal_actions_meta_box_container').on('click', 'a.lost-reason-action-submit', function(e)
	{
		e.preventDefault();

		$(this).attr('disabled', 'disabled');

		// Submit interested feedback
		var data = {
	        action:         'propertyhive_appraisal_lost_reason',
	        appraisal_id:    	<?php echo $post->ID; ?>,
	        lost_reason:    $('#_lost_reason').val(),
	        security:       '<?php echo wp_create_nonce( 'appraisal-actions' ); ?>',
	    };

	    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
	    {
	    	redraw_appraisal_actions();
	    }, 'json');
	});
});

jQuery(window).on('load', function($)
{
	redraw_appraisal_actions();
});

function redraw_appraisal_actions()
{
	jQuery('#propertyhive_appraisal_actions_meta_box_container').html('Loading...');

	var data = {
        action:         'propertyhive_get_appraisal_actions',
        appraisal_id:    	<?php echo $post->ID; ?>,
        security:       '<?php echo wp_create_nonce( 'appraisal-actions' ); ?>',
    };

    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
    {
    	jQuery('#propertyhive_appraisal_actions_meta_box_container').html(response);

    	jQuery(document).trigger('ph:adminAppraisalActionsRedrawn');
    	jQuery(document).trigger('ph:adminPostActionsRedrawn', ['appraisal']);
    }, 'html');

    redraw_appraisal_details_meta_box();
}

</script>
<?php
    }
}