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

});

function activateTipTip() {
    var tiptip_args = {
		'attribute': 'data-tip',
		'fadeIn': 50,
		'fadeOut': 50,
		'delay': 200
	};

	$( '.help_tip' ).tipTip( tiptip_args );
}