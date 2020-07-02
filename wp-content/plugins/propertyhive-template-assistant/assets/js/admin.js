jQuery( function($){

	$('.ph_additional_fields tbody.has-rows').sortable({
         opacity: 0.8,
         revert: true,
         update : function (event, ui) 
         {
         		$('.ph_additional_fields tbody.has-rows').sortable( "destroy" );

              	var new_order = '';
              	jQuery('.ph_additional_fields tbody.has-rows').find('tr').each( function () 
              	{
                  	if (new_order != '')
                  	{
                      	new_order += ',';
                  	}
                  	new_order = new_order + jQuery(this).attr('id').replace('custom_field_', '');
              	});

              	// reload page
              	window.location.href = ph_template_assistant.admin_template_assistant_settings_url + '&section=custom-fields&neworder=' + new_order;
        }
    });
	$( '.ph_additional_fields tbody' ).disableSelection();

});