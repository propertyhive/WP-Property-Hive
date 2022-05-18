jQuery( function($){

    $('a#add_department').click(function(e)
    {
        e.preventDefault();

        var new_department_html = $('#active_department_template').html();

        new_department_html = new_department_html.replace(/template/g, 'phnew-' + $('#propertyhive_new_custom_departments').val());

        $('#active_departments').append(new_department_html);

        var new_custom_departments = $('#propertyhive_custom_departments').val();
        if ( new_custom_departments != '' )
        {
            new_custom_departments += ',';
        }
        new_custom_departments += 'phnew-' + $('#propertyhive_new_custom_departments').val();
        $('#propertyhive_custom_departments').val( new_custom_departments );

        $('#propertyhive_new_custom_departments').val( parseInt($('#propertyhive_new_custom_departments').val()) + 1 );
    });
    
    $(document).on('click', 'a.delete-department', function(e)
    {
        e.preventDefault();

        var confirmBox = confirm('Are you sure you wish to delete this department?');

        if (confirmBox)
        {
            var custom_department_key = $(this).attr('data-department');

            $('#propertyhive_active_department_fieldset_' + custom_department_key).remove();

            var new_custom_departments = '';
            var existing_new_custom_departments = $('#propertyhive_custom_departments').val().split(",");
            for ( var i in existing_new_custom_departments )
            {
                if ( existing_new_custom_departments[i] != custom_department_key )
                {
                    if ( new_custom_departments != '' )
                    {
                        new_custom_departments += ',';
                    }
                    new_custom_departments += existing_new_custom_departments[i];
                }
            }
            $('#propertyhive_custom_departments').val( new_custom_departments );
        }
    });

    $('input.colorpick').wpColorPicker();

    $('form').submit(function()
    {
        // Check for confirm removal checkbox
        // and make sure it's ticked
        if ( $('input[type=\'checkbox\'][name=\'confirm_removal\']').length > 0 )
        {
            if ( !$('input[type=\'checkbox\'][name=\'confirm_removal\']').is( ":checked" ) )
            {
                alert( propertyhive_admin_settings.confirm_not_selected_warning );
                return false;
            }
        }

        // Make sure a department has been ticked
        if ( $('input[type=\'checkbox\'][name^=\'propertyhive_active_departments_\']').length > 0 )
        {
            var department_ticked = false;
            $('input[type=\'checkbox\'][name^=\'propertyhive_active_departments_\']').each(function()
            {
                if ( $(this).is( ":checked" ) )
                {
                    department_ticked = true;
                }
            });

            if ( !department_ticked )
            {
                alert( propertyhive_admin_settings.no_departments_selected_warning );
                return false;
            }

            // Make sure primary department is in the list of ticked departments
            var selected_primary_department = $("input[type=\'radio\'][name=\'propertyhive_primary_department\']:checked").val();
            selected_primary_department = selected_primary_department.replace("residential-", "");
            if ( !$('input[type=\'checkbox\'][name=\'propertyhive_active_departments_' + selected_primary_department + '\']').is( ":checked" ) )
            {
                alert( propertyhive_admin_settings.primary_department_not_active_warning );
                return false;
            }
        }

        // Validate disabled modules
        if ( $('input[type=\'checkbox\'][name^=\'propertyhive_module_disabled_\']').length > 0 )
        {
            if ( 
                $('input[type=\'checkbox\'][name=\'propertyhive_module_disabled_contacts\']').is( ":checked" ) &&
                (
                    !$('input[type=\'checkbox\'][name=\'propertyhive_module_disabled_viewings\']').is( ":checked" ) ||
                    !$('input[type=\'checkbox\'][name=\'propertyhive_module_disabled_offers_sales\']').is( ":checked" )
                )
            )
            {
                alert( 'The contacts module must be enabled in order to use the viewings, offers and sales modules' );
                return false;
            }
        };

        if ( $('select[name=\'propertyhive_default_country\']').length > 0 )
        {
            // Make sure default country is in list of selected countries
            var selected_countries = $('select[name=\'propertyhive_countries[]\']').val();
            if ( selected_countries == null )
            {
                alert( propertyhive_admin_settings.no_countries_selected );
                return false;
            }
            var default_country = $('select[name=\'propertyhive_default_country\']').val();
            var default_in_selected = false;
            for ( var i in selected_countries )
            {
                if ( default_country == selected_countries[i] )
                {
                    default_in_selected = true;
                }
            }
            if ( !default_in_selected )
            {
                alert( propertyhive_admin_settings.default_country_not_in_selected );
                return false;
            }
        }

        // Disable submit button when form is being submitted to prevent double submissions
        $('p.submit input[type=\'submit\']').attr('disabled', 'disabled');
    });

    $('a.batch-delete').click(function()
    {
        var term_ids = new Array;

        $('input[name=\'term_id[]\']:checked').each(function()
        {
            term_ids.push( $(this).val() );
        });
        
        if ( term_ids.length > 0 )
        {
            window.location.href = propertyhive_admin_settings.admin_url + 'admin.php?page=ph-settings&tab=customfields&section=' + propertyhive_admin_settings.taxonomy_section + '-delete&id=' + term_ids.join("-");
        }

        return false;
    });

    $('.select_all').change(function()
    {
        if ( this.checked )
        {
            $('input[name=\'term_id[]\']').attr('checked', 'checked');

            // If at least one has been checked, enable Delete Selected button
            if ( $('input[name=\'term_id[]\']:checked').length > 0 )
            {
                $('a.batch-delete').attr('disabled', false);
            }
        }
        else
        {
            $('input[name=\'term_id[]\']').removeAttr('checked');

            // Disable Delete Selected button
            $('a.batch-delete').attr('disabled', 'disabled');
        }
    });

    $('input[name=\'term_id[]\']').change(function()
    {
        if ( $('input[name=\'term_id[]\']:checked').length > 0 )
        {
            $('a.batch-delete').attr('disabled', false);
        }
        else
        {
            $('a.batch-delete').attr('disabled', 'disabled');
        }

        // If we're unchecking a term, uncheck the Select All box
        if ( !this.checked )
        {
            $('.select_all').removeAttr('checked');
        }
    });

    jQuery('select[name=\'propertyhive_countries[]\']').change(function()
    {
        fill_search_form_currency_options();
    });
    fill_search_form_currency_options();

    jQuery('input[name^=\'propertyhive_active_departments\']').change(function()
    {
        toggle_department_specific_options();
    });
    toggle_department_specific_options();

    jQuery('[name=\'propertyhive_maps_provider\']').change(function()
    {
        ph_toggle_maps_provider_options();
    });
    ph_toggle_maps_provider_options();

    jQuery('[name=\'propertyhive_geocoding_provider\']').change(function()
    {
        ph_toggle_geocoding_provider_options();
    });
    ph_toggle_geocoding_provider_options();

    jQuery('[name=\'propertyhive_auto_incremental_reference_numbers\']').change(function()
    {
        ph_toggle_auto_incremental_reference_number_options();
    });
    ph_toggle_auto_incremental_reference_number_options();
});

function ph_toggle_maps_provider_options()
{
    if ( jQuery('[name=\'propertyhive_maps_provider\']:checked').val() == 'osm' )
    {
        jQuery('#row_propertyhive_google_maps_api_key').hide();
    }
    else
    {
        jQuery('#row_propertyhive_google_maps_api_key').show();
    }
}

function ph_toggle_geocoding_provider_options()
{
    if ( jQuery('[name=\'propertyhive_geocoding_provider\']:checked').val() == 'osm' )
    {
        jQuery('#row_propertyhive_google_maps_geocoding_api_key').hide();
        jQuery('#row_propertyhive_osm_html').show();
    }
    else
    {
        jQuery('#row_propertyhive_google_maps_geocoding_api_key').show();
        jQuery('#row_propertyhive_osm_html').hide();
    }
}

function ph_toggle_auto_incremental_reference_number_options()
{
    if ( jQuery('[name=\'propertyhive_auto_incremental_reference_numbers\']').is(":checked") )
    {
        jQuery('#row_propertyhive_auto_incremental_next').show();
    }
    else
    {
        jQuery('#row_propertyhive_auto_incremental_next').hide();
    }
}

function fill_search_form_currency_options()
{
    var selected_countries = jQuery('select[name=\'propertyhive_countries[]\']').val();
    var selected_currency = jQuery('#propertyhive_search_form_currency').val();

    var new_currency_options = new Array();

    for ( var i in selected_countries)
    {
        var country = countries[selected_countries[i]];

        new_currency_options.push( country.currency_code );
    }

    jQuery('#propertyhive_search_form_currency').find('option').remove();
    if ( new_currency_options.length > 0 )
    {
        new_currency_options = jQuery.unique( new_currency_options );

        /*new_currency_options.sort(function(a, b) {
            var a1 = a.new_currency_options, b1 = b.new_currency_options;
            if(a1 == b1) return 0;
            return a1 > b1 ? 1 : -1;
        });*/

        for ( var i in new_currency_options)
        {
            jQuery('#propertyhive_search_form_currency').append('<option value="' + new_currency_options[i] + '">' + new_currency_options[i] + '</option>');
        }
        jQuery('#propertyhive_search_form_currency').val(selected_currency);

        if ( new_currency_options.length > 1 )
        {
            jQuery('#propertyhive_search_form_currency').parent().parent().show();
        }
        else
        {
            jQuery('#propertyhive_search_form_currency').parent().parent().hide();
        }

        if ( jQuery("#propertyhive_search_form_currency :selected").length == 0 )
        {
            jQuery("#propertyhive_search_form_currency").val( jQuery("#propertyhive_search_form_currency option:first").val() );

        }
    }
}

function toggle_department_specific_options()
{
    jQuery('#row_propertyhive_lettings_fees').hide();
    jQuery('#row_propertyhive_lettings_fees_commercial').hide();
    jQuery('#row_propertyhive_lettings_fees_display_search_results').hide();

    if (jQuery('#propertyhive_active_departments_lettings').prop('checked') == true)
    {
        jQuery('#row_propertyhive_lettings_fees').show();
        jQuery('#row_propertyhive_lettings_fees_display_search_results').show();
    }
    if (jQuery('#propertyhive_active_departments_commercial').prop('checked') == true)
    {
        jQuery('#row_propertyhive_lettings_fees_commercial').show();
        jQuery('#row_propertyhive_lettings_fees_display_search_results').show();
    }
}