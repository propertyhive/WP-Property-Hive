jQuery(document).ready(function()
{
	ph_init_dynamic_population(document);
});

function ph_init_dynamic_population(context)
{
	ph_bind_dynamic_population_handlers(context);
	ph_populate_subsequent_dropdowns(true, context);
}

function ph_get_dynamic_population_params()
{
	return typeof window.propertyhive_dynamic_population_params === 'object' ? window.propertyhive_dynamic_population_params : null;
}

function ph_bind_dynamic_population_handlers(context)
{
	var root = context ? jQuery(context) : jQuery(document);
	var fields = root.is('select[data-dynamic-population-level]') ? root : root.find('select[data-dynamic-population-level]');

	fields.off('change.propertyhiveDynamicPopulation').on('change.propertyhiveDynamicPopulation', function()
	{
		ph_populate_subsequent_dropdowns(false, jQuery(this).closest('form'));
	});
}

function ph_populate_subsequent_dropdowns(init, context)
{
	var params = ph_get_dynamic_population_params();
	var root = context ? jQuery(context) : jQuery(document);
	var forms = root.is('form') ? root : root.find('form');

	if (!params)
	{
		return;
	}

	if (root.is('select[data-dynamic-population-level]'))
	{
		forms = root.closest('form');
	}

	forms.each(function()
	{
		var form = this;

		jQuery(this).find('input[type=\'hidden\'][name=\'other_' + params.taxonomy + '[]\']').remove();
		jQuery(this).find('select[data-dynamic-population-level]').attr('name', 'other_' + params.taxonomy + '[]');

		for ( var i = 1; i <= params.levels_of_taxonomy; i = i + 1 )
		{
			if ( jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\']').val() != '' && jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\']').val() != null )
			{
				var value = parseInt(jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\']').val());
				
				if ( ( i + 1 ) <= params.levels_of_taxonomy )
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
		            var sortedOptions = Object.entries(params.options)
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
					jQuery(this).find('select[data-dynamic-population-level=\'' +  i + '\']').attr('name', params.taxonomy);
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
				for ( var j in params.other_values )
				{
					if ( jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\'] option[value=\'' + params.other_values[j] + '\']').length > 0 )
					{
						jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\']').val( params.other_values[j] );
					}
					ph_populate_subsequent_dropdowns(false, form);
				}
				if ( jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\'] option[value=\'' +  params.value + '\']').length > 0 )
				{
					jQuery(this).find('select[data-dynamic-population-level=\'' + i + '\']').val( params.value);
					ph_populate_subsequent_dropdowns(false, form);
				}

			}
		}
	});
}

window.ph_init_dynamic_population = ph_init_dynamic_population;
window.ph_populate_subsequent_dropdowns = ph_populate_subsequent_dropdowns;
