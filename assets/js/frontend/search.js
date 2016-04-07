function toggleDepartmentFields()
{
    if (jQuery('form.property-search-form').length > 0)
    {
        var selectedDepartment = "residential-sales"; // TODO: Use default from settings

        var departmentEl = jQuery('form.property-search-form [name=\'department\']')
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
                    var selected = departmentEl.filter(':checked');
                }
            }
        }

        jQuery('.sales-only').hide();
        jQuery('.lettings-only').hide();
        
        if (selected.length > 0)
        {
            selectedDepartment = selected.val();

            // controls won't always be display:block so we should get the 
            // first visible component (that isnt sales/lettings-only) and 
            // use that display
            var display = 'block';
            jQuery('form.property-search-form .control').each(function()
            {
                if (!jQuery(this).hasClass('.sales-only') && !jQuery(this).hasClass('.lettings-only') && jQuery(this).css('display') != 'none')
                {
                    display = jQuery(this).css('display');
                }
            });

            if (selectedDepartment == 'residential-sales')
            {
                jQuery('.sales-only').css('display', display);
            }
            else if (selectedDepartment == 'residential-lettings')
            {
                jQuery('.lettings-only').css('display', display);
            }
        }
    }
}

jQuery( function(jQuery){

    
    // Orderby
    jQuery( '.propertyhive-ordering' ).on( 'change', 'select.orderby', function() {
        jQuery( this ).closest( 'form' ).submit();
    });
    
    toggleDepartmentFields();
    
    jQuery('form.property-search-form [name=\'department\']').change(function()
    {
        toggleDepartmentFields();
    });

});

jQuery(window).resize(function() {
    toggleDepartmentFields();
});