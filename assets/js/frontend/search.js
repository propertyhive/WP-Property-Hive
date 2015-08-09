jQuery( function($){
    
    // Orderby
    $( '.propertyhive-ordering' ).on( 'change', 'select.orderby', function() {
        $( this ).closest( 'form' ).submit();
    });
    
    toggleDepartmentFields();
    
    $('input[type=\'radio\'][name=\'department\']').change(function()
    {
        toggleDepartmentFields();
    });

    function toggleDepartmentFields()
    {
        var selectedDepartment = "";
        var selected = $('input[type=\'radio\'][name=\'department\']:checked');
        
        $('.sales-only').hide();
        $('.lettings-only').hide();
        
        if (selected.length > 0)
        {
            selectedDepartment = selected.val();
            
            if (selectedDepartment == 'residential-sales')
            {
                $('.sales-only').show();
            }
            else if (selectedDepartment == 'residential-lettings')
            {
                $('.lettings-only').show();
            }
        }
    }

});

