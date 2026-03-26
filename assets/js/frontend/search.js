function toggleDepartmentFields()
{
    if (jQuery('form.property-search-form').length > 0)
    {
        // There may be multiple forms on the page so treat each one individually
        jQuery('form.property-search-form').each(function()
        {
            var selectedDepartment = "residential-sales"; // TODO: Use default from settings

            var selected;

            var departmentEl = jQuery(this).find('[name=\'department\']')
            if (departmentEl.length > 0)
            {
                switch (departmentEl.prop('tagName').toLowerCase())
                {
                    case "select":
                    {
                        selected = departmentEl;
                        break;
                    }
                    default:
                    {
                        if ( departmentEl.attr('type') == 'hidden' )
                        {
                            selected = departmentEl;
                        }
                        else
                        {
                            selected = departmentEl.filter(':checked');
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
            
            if (selected && selected.length > 0)
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
                            var availability_text = availabilities[availabilities_order[i]].label;

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

                            if ( availability_id == '' || jQuery.inArray( selectedDepartment, this_availability_departments ) > -1 || !availability_departments_exist )
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

                jQuery(this).trigger('ph:toggleSearchDepartment', [selectedDepartment]);
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

let ph_turnstile_widgets = {}; // store widget IDs so we can reset later
function ph_init_turnstile() 
{
    const widgets = document.querySelectorAll('.turnstile');

    widgets.forEach(el => {
        // If the widget has already been rendered once:
        if ( ph_turnstile_widgets[el.dataset.tsId] ) 
        {
            // Only reset if element is visible
            if ( el.offsetParent !== null ) 
            {
                turnstile.reset(ph_turnstile_widgets[el.dataset.tsId]);
            }
            return;
        }

        // Only render if the element is actually visible
        if ( el.offsetParent === null ) 
        {
            return; // skip hidden ones (e.g., in closed modal)
        }

        // Create a unique ID for this element
        const id = Math.random().toString(36).substring(2);
        el.dataset.tsId = id;

        // Render Turnstile widget
        ph_turnstile_widgets[id] = turnstile.render(el, {
            sitekey: el.dataset.sitekey,
            callback: (token) => {
                console.log("Turnstile token:", token);
            }
        });
    });
}

jQuery(document).on('afterShow.fb', function(e, instance, slide) {
    ph_init_turnstile();
});

jQuery(window).resize(function()
{
    ph_template_assistant_set_image_heights();
});

jQuery(document).ready(function()
{
    ph_template_assistant_set_image_heights();
});

jQuery(window).on('load', function()
{
    ph_template_assistant_set_image_heights();
});

function ph_template_assistant_set_image_heights()
{
    if ( propertyhive_search_params.hasOwnProperty('do_image_resize') && propertyhive_search_params.do_image_resize === '1' )
    {

    }
    else
    {
        return;
    }

    jQuery('.propertyhive ul.properties > li').height('auto');
    jQuery('.propertyhive ul.properties > li .thumbnail img').height('auto');

    // Check the top pos of the first and second and check if they're the same.
    // This will tell us if we're in a grid or not
    if ( 
        jQuery('.propertyhive ul.properties > li' ).eq(0).length > 0 &&
        jQuery('.propertyhive ul.properties > li' ).eq(1).length > 0 &&
        Math.round(jQuery('.propertyhive ul.properties > li' ).eq(0).offset().top) == Math.round(jQuery('.propertyhive ul.properties > li' ).eq(1).offset().top)
    )
    {
        // Make the images the same height
        jQuery('.propertyhive ul.properties > li .thumbnail img').each(function()
        {
            var ratio = 2 / 3;
            if ( propertyhive_search_params.hasOwnProperty('image_ratio') )
            {
                ratio = propertyhive_search_params.image_ratio;
            }
            jQuery(this).height( jQuery(this).width() * ratio );
        });

        // Make the properties on each row the same height
        var row = 0;
        var previousTop = 0;
        var maxRowHeight = 0;
        var elements_in_row = new Array;
        jQuery('.propertyhive ul.properties > li').each(function()
        {    
            var this_top = jQuery(this).offset().top;
            var this_height = jQuery(this).height();
            if (maxRowHeight != 0 && Math.round(this_top) != Math.round(previousTop))
            {
                // we're on a new row
                jQuery(elements_in_row).each(function()
                {
                    jQuery(this).height(maxRowHeight);
                });

                row = row + 1;
                maxRowHeight = 0;
                previousTop = 0;
                elements_in_row = new Array;
            }

            if (this_height > maxRowHeight)
            {
                maxRowHeight = this_height;
            }

            elements_in_row.push(jQuery(this));

            previousTop = Math.round(this_top);
        });
        jQuery(elements_in_row).each(function()
        {
            jQuery(this).height(maxRowHeight);
        }); 
    }
}