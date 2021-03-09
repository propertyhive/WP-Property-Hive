function toggleDepartmentFields()
{
    if (jQuery('form.property-search-form').length > 0)
    {
        // There may be multiple forms on the page so treat each one individually
        jQuery('form.property-search-form').each(function()
        {
            var selectedDepartment = "residential-sales"; // TODO: Use default from settings

            var departmentEl = jQuery(this).find('[name=\'department\']')
            if (departmentEl.length > 0)
            {
                switch (departmentEl.prop('tagName').toLowerCase())
                {
                    case "select":
                    {
                        var selected = departmentEl;
                        break;
                    }
                    default:
                    {
                        if ( departmentEl.attr('type') == 'hidden' )
                        {
                            var selected = departmentEl;
                        }
                        else
                        {
                            var selected = departmentEl.filter(':checked');
                        }
                    }
                }
            }
            
            jQuery(this).find('.sales-only').hide();
            jQuery(this).find('.lettings-only').hide();
            jQuery(this).find('.residential-only').hide();
            jQuery(this).find('.commercial-only').hide();
            jQuery(this).find('.commercial-sales-only').hide();
            jQuery(this).find('.commercial-lettings-only').hide();
            
            if (selected.length > 0)
            {
                selectedDepartment = selected.val();

                // controls won't always be display:block so we should get the 
                // first visible component (that isnt sales/lettings-only) and 
                // use that display
                var display = 'block';
                var found = false;
                jQuery(this).find('.control').each(function()
                {
                    if (!jQuery(this).hasClass('.sales-only') && !jQuery(this).hasClass('.lettings-only') && !jQuery(this).hasClass('.residential-only') && !jQuery(this).hasClass('.commercial-only') && !jQuery(this).hasClass('.commercial-sales-only') && !jQuery(this).hasClass('.commercial-lettings-only') && jQuery(this).css('display') != 'none')
                    {
                        display = jQuery(this).css('display');
                        found = true;
                    }
                });

                if (found == false)
                {
                    if (jQuery(this).css('display') == 'table')
                    {
                        display = 'table-cell';
                    }
                }

                if ( selectedDepartment == 'residential-sales' || ( propertyhive_search_params.custom_departments[selectedDepartment] && propertyhive_search_params.custom_departments[selectedDepartment].based_on == 'residential-sales' ) )
                {
                    jQuery(this).find('.sales-only').css('display', display);
                    jQuery(this).find('.residential-only').css('display', display);
                }
                else if ( selectedDepartment == 'residential-lettings' || ( propertyhive_search_params.custom_departments[selectedDepartment] && propertyhive_search_params.custom_departments[selectedDepartment].based_on == 'residential-lettings' ))
                {
                    jQuery(this).find('.lettings-only').css('display', display);
                    jQuery(this).find('.residential-only').css('display', display);
                }
                else if ( selectedDepartment == 'commercial' || ( propertyhive_search_params.custom_departments[selectedDepartment] && propertyhive_search_params.custom_departments[selectedDepartment].based_on == 'commercial' ) )
                {
                    jQuery(this).find('.commercial-only').css('display', display);
                    jQuery(this).find('.commercial-sales-only').css('display', 'none');
                    jQuery(this).find('.commercial-lettings-only').css('display', 'none');

                    if ( jQuery(this).find('[name=\'commercial_for_sale_to_rent\']').length > 0 )
                    {
                        var commercial_for_sale_to_rent = jQuery(this).find('[name=\'commercial_for_sale_to_rent\']').val();

                        if ( commercial_for_sale_to_rent == 'for_sale' )
                        {
                            jQuery(this).find('.commercial-sales-only').css('display', display);
                            jQuery(this).find('.commercial-lettings-only').css('display', 'none');
                        }
                        if ( commercial_for_sale_to_rent == 'to_rent' )
                        {
                            jQuery(this).find('.commercial-sales-only').css('display', 'none');
                            jQuery(this).find('.commercial-lettings-only').css('display', display);
                        }
                    }
                }

                if ( jQuery(this).find('[name=\'availability\']').length > 0 && typeof availability_departments !== "undefined" )
                {
                    if ( Object.keys(availability_departments).length > 0 )
                    {
                        jQuery(this).find('[name=\'availability\']').empty();

                        for ( var i in availabilities_order )
                        {
                            var availability_id = availabilities_order[i];
                            var availability_text = availabilities[availabilities_order[i]];

                            var this_availability_departments = [];
                            var availability_departments_exist = true;
                            if ( typeof availability_departments[availability_id] !== 'undefined' )
                            {
                                this_availability_departments = availability_departments[availability_id];
                            }
                            else
                            {
                                availability_departments_exist = false;
                            }

                            if ( jQuery.inArray( selectedDepartment, this_availability_departments ) > -1 || !availability_departments_exist )
                            {
                                jQuery(this).find('[name=\'availability\']').append( jQuery("<option />").val(availability_id).text(availability_text) );
                            }
                            jQuery(this).find('[name=\'availability\']').val(selected_availability);
                        }
                        if ( jQuery(this).find('[name=\'availability\']').val() == '' || jQuery(this).find('[name=\'availability\']').val() == null )
                        {
                            jQuery(this).find('[name=\'availability\']').val( jQuery(this).find('[name=\'availability\'] option:first').val() );
                        }
                    }
                }
            }
        });
    }
}

jQuery( function(jQuery){
    
    // Orderby
    jQuery( '.propertyhive-ordering' ).on( 'change', 'select.orderby', function() {
        jQuery( this ).closest( 'form' ).submit();
    });
    
    toggleDepartmentFields();
    
    jQuery('body').on('change', 'form.property-search-form [name=\'department\']', function()
    {
        toggleDepartmentFields();
    });

    jQuery('body').on('change', 'form.property-search-form [name=\'commercial_for_sale_to_rent\']', function()
    {
        toggleDepartmentFields();
    });

    if ( jQuery('form.property-search-form select.ph-form-multiselect').length > 0 )
    {
        jQuery('form.property-search-form select.ph-form-multiselect').each(function()
        {
            jQuery(this).multiselect({
                texts: {
                    placeholder: jQuery(this).data('blank-option')
                }
            });
        });
    }
});

jQuery(window).resize(function() {
    toggleDepartmentFields();
});