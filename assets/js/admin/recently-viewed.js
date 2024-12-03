jQuery( function ( $ ) {

	if ($('#contextual-help-link-wrap .show-recently-viewed').length == 0)
	{
		$("#screen-meta-links").append('<div id="contextual-help-link-wrap" class="hide-if-no-js screen-meta-toggle" style="z-index:2;"><button class="show-settings show-recently-viewed" style="cursor:pointer">Recently Viewed</button></div>');

		var html = '';

		if ( Object.keys(propertyhive_admin_recently_viewed.recently_viewed).length > 0 )
		{
			html += '<ul>';
			for ( var i in propertyhive_admin_recently_viewed.recently_viewed )
			{
				html += '<li><a href="' + propertyhive_admin_recently_viewed.recently_viewed[i].edit_link + '">' + propertyhive_admin_recently_viewed.recently_viewed[i].title + '</a></li>';
			}
			html += '</ul>';
		}
		else
		{
			html += '<div class="none">No recently viewed items to display</div>';
		}

		$("#screen-meta-links").append('<div class="ph-recently-viewed-popup">' + html + '</div>');

		$('body').on('click', '#contextual-help-link-wrap .show-recently-viewed', function(e)
		{
			e.preventDefault();

			jQuery('.ph-recently-viewed-popup').css({
				top: $(this).position().top + $(this).outerHeight(),
				right: $(document).width() - ($(this).parent().offset().left + $(this).parent().outerWidth())
			}).fadeToggle('fast');
		});
	}
});