jQuery(window).on('load', function()
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

	// Load viewings awaiting feedback, if div is present (i.e. if viewings module hasn't been disabled)
	if ( jQuery('#ph_dashboard_viewings_awaiting_applicant_feedback').length > 0 )
	{
		var data = {
			action: 'propertyhive_get_viewings_awaiting_applicant_feedback',
			security: 'get-viewings-awaiting-applicant-feedback'
		};

		jQuery.post(propertyhive_dashboard.ajax_url, data, function(response)
		{
			if ( response == '' || response.length == 0 )
			{
				jQuery('#ph_dashboard_viewings_awaiting_applicant_feedback').html('No viewings found awaiting feedback');
				return;
			}

			jQuery('#ph_dashboard_viewings_awaiting_applicant_feedback').html('<ul></ul>')
			for ( var i in response )
			{
				jQuery('#ph_dashboard_viewings_awaiting_applicant_feedback ul').append('<li><a class="rsswidget" style="font-weight:400" href="' + response[i].edit_link + '">' + response[i].applicant_name + '</a> viewed <a class="rsswidget" style="font-weight:400" href="' + response[i].edit_link + '">' + response[i].property_address + '</a><br><small style="opacity:0.85">' + response[i].start_date_time_formatted_Hi_jSFY + '</small></li>');
			}
			
		}, 'json');
	}

	// Load my upcoming appointments, if div is present (i.e. if viewings module etc hasn't been disabled)
	if ( jQuery('#ph_dashboard_my_upcoming_appointments').length > 0 )
	{
		var data = {
			action: 'propertyhive_get_my_upcoming_appointments',
			security: 'get-my-upcoming-appointments'
		};

		jQuery.post(propertyhive_dashboard.ajax_url, data, function(response)
		{
			if ( response == '' || response.length == 0 )
			{
				jQuery('#ph_dashboard_my_upcoming_appointments').html('No upcoming appointments found');
				return;
			}

			jQuery('#ph_dashboard_my_upcoming_appointments').html('<ul></ul>')
			for ( var i in response )
			{
				jQuery('#ph_dashboard_my_upcoming_appointments ul').append('<li><a class="rsswidget" style="font-weight:400" href="' + response[i].edit_link + '">' + response[i].title + '</a><br><small style="opacity:0.85">' + response[i].start_date_time_formatted_Hi_jSFY + '</small></li>');
			}
			
		}, 'json');
	}

	// Load upcoming and overdue key dates, if div is present (i.e. if management module hasn't been disabled)
	if ( jQuery('#ph_dashboard_upcoming_overdue_key_dates').length > 0 )
	{
		var data = {
			action: 'propertyhive_get_upcoming_overdue_key_dates',
			security: 'get_upcoming_overdue_key_dates'
		};

		jQuery.post(propertyhive_dashboard.ajax_url, data, function(response)
		{
			if ( response == '' || response.length == 0 )
			{
				jQuery('#ph_dashboard_upcoming_overdue_key_dates').html('No upcoming key dates found');
				return;
			}

			jQuery('#ph_dashboard_upcoming_overdue_key_dates').html('<ul></ul>')
			for ( var i in response )
			{
				jQuery('#ph_dashboard_upcoming_overdue_key_dates ul').append('<li><a class="rsswidget" style="font-weight:400" href="' + response[i].key_date_edit_link + '">' + response[i].description + '</a> ' + response[i].upcoming_overdue_status + ' on <a class="rsswidget" style="font-weight:400" href="' + response[i].property_edit_link + '">' + response[i].property_address + '</a><br><small style="opacity:0.85">' + response[i].due_date_time_formatted + '</small></li>');
			}
		}, 'json');
	}
});