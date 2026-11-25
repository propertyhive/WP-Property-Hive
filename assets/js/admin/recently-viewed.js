jQuery( function ( $ ) {

	if ($('#contextual-help-link-wrap .show-recently-viewed').length == 0)
	{
		$("#screen-meta-links").append('<div id="contextual-help-link-wrap" class="hide-if-no-js screen-meta-toggle" style="z-index:2;"><button class="button button-compact show-settings show-recently-viewed" style="cursor:pointer">Recently Viewed</button></div>');

		var popup = $('<div class="ph-recently-viewed-popup"></div>');

		if ( Object.keys(propertyhive_admin_recently_viewed.recently_viewed).length > 0 )
		{
			var list = $('<ul></ul>');
			for ( var i in propertyhive_admin_recently_viewed.recently_viewed )
			{
				var item = propertyhive_admin_recently_viewed.recently_viewed[i];
				// Titles can include stored contact names or enquiry subjects: render as text.
				var link = $('<a></a>').attr('href', item.edit_link).text(item.title);
				list.append($('<li></li>').append(link));
			}
			popup.append(list);
		}
		else
		{
			popup.append($('<div class="none"></div>').text('No recently viewed items to display'));
		}

		$('#screen-meta-links').append(popup);

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