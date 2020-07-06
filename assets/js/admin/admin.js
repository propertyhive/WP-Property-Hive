/* global propertyhive_admin */

/**
 * Property Hive Admin JS
 */
jQuery( function ( $ ) {

	if ($('#contextual-help-link-wrap').length == 0)
	{
		$("#screen-meta-links").append('<div id="contextual-help-link-wrap" class="hide-if-no-js screen-meta-toggle" style="z-index:2;"><button class="show-settings" style="cursor:pointer">Recently Viewed</button></div>');

		var html = '';

		if ( Object.keys(propertyhive_admin.recently_viewed).length > 0 )
		{
			html += '<ul>';
			for ( var i in propertyhive_admin.recently_viewed )
			{
				html += '<li><a href="' + propertyhive_admin.recently_viewed[i].edit_link + '">' + propertyhive_admin.recently_viewed[i].title + '</a></li>';
			}
			html += '</ul>';
		}
		else
		{
			html += '<div class="none">No recently viewed items to display</div>';
		}

		$("#screen-meta-links").append('<div class="ph-recently-viewed-popup">' + html + '</div>');

		$('body').on('click', '#contextual-help-link-wrap .show-settings', function(e)
		{
			e.preventDefault();

			jQuery('.ph-recently-viewed-popup').css({
				top: $(this).position().top + $(this).outerHeight(),
				right: $(document).width() - ($(this).parent().offset().left + $(this).parent().outerWidth())
			}).fadeToggle('fast');
		});
	}

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