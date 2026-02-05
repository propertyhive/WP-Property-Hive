/* global propertyhive_admin */

/**
 * Property Hive Admin JS
 */
jQuery( function ( $ ) {

	$( document.body )
		.on( 'init_tooltips', function() {
			activateTipTip();
		});

	// Tooltips
	$( document.body ).trigger( 'init_tooltips' );

	$( '#ph_dismiss_notice_retired_template_assistant' ).click(function(e)
	{
		e.preventDefault();
		
		var data = {
			'action': 'propertyhive_dismiss_notice_retired_template_assistant'
		};

		$.post( ajaxurl, data, function(response) {
			$( '#ph_notice_retired_template_assistant' ).fadeOut();
		});
	});

	$( '#ph_dismiss_notice_leave_review' ).click(function(e)
	{
		e.preventDefault();
		
		var data = {
			'action': 'propertyhive_dismiss_notice_leave_review'
		};

		$.post( ajaxurl, data, function(response) {
			$( '#ph_notice_leave_review' ).fadeOut();
		});
	});

	$( '#ph_dismiss_notice_epl' ).click(function(e)
	{
		e.preventDefault();

		var data = {
			'action': 'propertyhive_dismiss_notice_epl'
		};

		$.post( ajaxurl, data, function(response) {
			$( '#ph_notice_epl' ).fadeOut();
		});
	});

	$( '#ph_dismiss_notice_demo_data' ).click(function(e)
	{
		e.preventDefault();

		var data = {
			'action': 'propertyhive_dismiss_notice_demo_data'
		};

		$.post( ajaxurl, data, function(response) {
			$( '#ph_notice_demo_data' ).fadeOut();
		});
	});

	$( '#ph_dismiss_notice_missing_search_results' ).click(function(e)
	{
		e.preventDefault();

		var data = {
			'action': 'propertyhive_dismiss_notice_missing_search_results'
		};

		$.post( ajaxurl, data, function(response) {
			$( '#ph_notice_missing_search_results' ).fadeOut();
		});
	});

	$( '#ph_dismiss_notice_missing_google_maps_api_key' ).click(function(e)
	{
		e.preventDefault();

		var data = {
			'action': 'propertyhive_dismiss_notice_missing_google_maps_api_key'
		};

		$.post( ajaxurl, data, function(response) {
			$( '#ph_notice_missing_google_maps_api_key' ).fadeOut();
		});
	});

	$( '#ph_dismiss_notice_invalid_expired_license_key' ).click(function(e)
	{
		e.preventDefault();

		var data = {
			'action': 'propertyhive_dismiss_notice_invalid_expired_license_key'
		};

		$.post( ajaxurl, data, function(response) {
			$( '#ph_notice_invalid_expired_license_key' ).fadeOut();
		});
	});

	$( '#ph_dismiss_notice_email_cron_not_running' ).click(function(e)
	{
		e.preventDefault();

		var data = {
			'action': 'propertyhive_dismiss_notice_email_cron_not_running'
		};

		$.post( ajaxurl, data, function(response) {
			$( '#ph_notice_email_cron_not_running' ).fadeOut();
		});
	});

	if ( propertyhive_admin.ajax_actions && propertyhive_admin.ajax_actions.length > 0 )
	{
        for ( var i in propertyhive_admin.ajax_actions )
        {
            var ajax_action = propertyhive_admin.ajax_actions[i].split('^');

            jQuery('#' + ajax_action[0].replace('get_', 'propertyhive_')).html('Loading...');

            if ( ajax_action[2] ) // callback
            {
                eval(ajax_action[2]);
            }
            else
            {
                var data = {
                    action: 'propertyhive_' + ajax_action[0],
                    post_id: propertyhive_admin.post_id,
                    security: ajax_action[1]
                }

                jQuery.ajax({
                	type: "POST",
					url: ajaxurl,
					data: data,
					target_div: '#' + ajax_action[0].replace('get_', 'propertyhive_'),
					success: function(response) 
	                {
	                    jQuery(this.target_div).html(response);
	                    activateTipTip();
	                },
					dataType: 'html'
                });
            }
        }
    }
});

function activateTipTip() {
    var tiptip_args = {
		'attribute': 'data-tip',
		'fadeIn': 50,
		'fadeOut': 50,
		'delay': 200
	};

	jQuery( '.help_tip' ).tipTip( tiptip_args );
}