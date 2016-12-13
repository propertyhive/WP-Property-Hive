jQuery(window).load(function()
{
	// Load news
	var data = {
		action: 'propertyhive_get_news',
		security: 'get-news'
	};

	jQuery.post(propertyhive_dashboard.ajax_url, data, function(response)
	{
		if ( response == '' || response.length == 0 )
		{
			jQuery('#ph_dashboard_news').html('Unable to retrieve latest posts.');
			return;
		}

		jQuery('#ph_dashboard_news').html('<ul></ul>')
		for ( var i in response )
		{
			jQuery('#ph_dashboard_news ul').append('<li><a class="rsswidget" style="font-weight:400" href="' + response[i].permalink + '">' + response[i].title + '</a><br><small style="opacity:0.85">' + response[i].date + '</small></li>');
		}
		
	}, 'json');
});