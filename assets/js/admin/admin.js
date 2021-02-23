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

	$( '.open_lightbox' ).click(function(e)
	{
		e.preventDefault();
		
		var post_id = $(this).attr('id');

		$.fancybox.open({
			src  : '#viewing_quick_view_lightbox_' + post_id,
			type : 'inline',
			opts : {
				smallBtn: true
			}
		});

		var data = {
			'action': 'propertyhive_open_admin_list_lightbox',
			'post_id': post_id
		};

		$.post( ajaxurl, data, function(response) {
			$('#viewing_quick_view_lightbox_' + post_id).html(response);
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