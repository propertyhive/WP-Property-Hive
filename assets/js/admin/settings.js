jQuery( function($){

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
    });
});