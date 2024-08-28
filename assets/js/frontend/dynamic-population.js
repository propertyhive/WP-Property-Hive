jQuery(document).ready(function()
{
	ph_populate_subsequent_dropdowns(true);

	jQuery('select[data-dynamic-population-level]').change(function()
	{
		ph_populate_subsequent_dropdowns(false);
	});
});

function ph_populate_subsequent_dropdowns(init)
{
	jQuery('form').each(function()
	{
		jQuery(this).find('input[type=\'hidden\'][name=\'other_' + propertyhive_dynamic_population_params.taxonomy + '[]\']').remove();
		jQuery(this).find('select[data-dynamic-population-level]').attr('name', 'other_' + propertyhive_dynamic_population_params.taxonomy + '[]');

		for ( var i = 1; i <= propertyhive_dynamic_population_params.levels_of_taxonomy; i = i + 1 )
		{
			if ( jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\']').val() != '' && jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\']').val() != null )
			{
				var value = parseInt(jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\']').val());
				
				if ( ( i + 1 ) <= propertyhive_dynamic_population_params.levels_of_taxonomy )
				{
					var previous_value = '';
					if ( jQuery(this).find('select[data-dynamic-population-level=\'' + ( i + 1 ) + '\']').val() != '' && jQuery(this).find('select[data-dynamic-population-level=\'' + ( i + 1 ) + '\']').val() != null )
					{
						previous_value = jQuery(this).find('select[data-dynamic-population-level=\'' + ( i + 1 ) + '\']').val();
					}
					// fill options of next dropdown
					jQuery(this).find('select[data-dynamic-population-level=\'' + ( i + 1 ) + '\']').html('');
					jQuery(this).find('select[data-dynamic-population-level=\'' + ( i + 1 ) + '\']').append(jQuery('<option>', { 
				        value: '',
				        text : 'Any'
				    }));

					// Sort options by label
		            var sortedOptions = Object.entries(propertyhive_dynamic_population_params.options)
		                .filter(([key, option]) => parseInt(option.parent) === value)
		                .sort(([, a], [, b]) => a.label.localeCompare(b.label));

		            // Populate the dropdown with sorted options
		            sortedOptions.forEach(([key, option]) => {
		                jQuery(this).find('select[data-dynamic-population-level=\'' + (i + 1) + '\']').append(jQuery('<option>', {
		                    value: key,
		                    text: option.label
		                }));
		            });

					if ( !init && previous_value != '' )
					{
						jQuery(this).find('select[data-dynamic-population-level=\'' + ( i + 1 ) + '\']').val(previous_value);
					}

					jQuery(this).find('select[data-dynamic-population-level=\'' + ( i + 1 ) + '\']').attr('disabled', false);
				}

				if (jQuery(this).find('select[data-dynamic-population-level=\'' + ( i + 1 ) + '\']').val() == '' || jQuery(this).find('select[data-dynamic-population-level=\'' + ( i + 1 ) + '\']').val() == null)
				{ 
					jQuery(this).find('select[data-dynamic-population-level=\'' +  i + '\']').attr('name', propertyhive_dynamic_population_params.taxonomy);
				}
			}
			else
			{
				jQuery(this).find('select[data-dynamic-population-level=\'' + ( i + 1 ) + '\']').attr('disabled', 'disabled');

				jQuery(this).find('select[data-dynamic-population-level=\'' + ( i + 1 ) + '\']').html('');
				jQuery(this).find('select[data-dynamic-population-level=\'' + ( i + 1 ) + '\']').append(jQuery('<option>', { 
			        value: '',
			        text : ''
			    }));
			}

			if ( init )
			{
				for ( var j in propertyhive_dynamic_population_params.other_values )
				{
					if ( jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\'] option[value=\'' + propertyhive_dynamic_population_params.other_values[j] + '\']').length > 0 )
					{
						jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\']').val( propertyhive_dynamic_population_params.other_values[j] );
					}
					ph_populate_subsequent_dropdowns(false);
				}
				if ( jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\'] option[value=\'' +  propertyhive_dynamic_population_params.value + '\']').length > 0 )
				{
					jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\']').val( propertyhive_dynamic_population_params.value);
					ph_populate_subsequent_dropdowns(false);
				}

			}
		}
	});
}