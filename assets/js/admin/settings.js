jQuery( function($){
   
    // Check for confirm removal checkbox
    // and make sure it's ticked
    $('form').submit(function()
    {
        if ( $('input[type=\'checkbox\'][name=\'confirm_removal\']').length > 0 )
        {
            if ( !$('input[type=\'checkbox\'][name=\'confirm_removal\']').is( ":checked" ) )
            {
                alert( propertyhive_admin_settings.confirm_not_selected_warning );
                return false;
            }
        }
    });

});