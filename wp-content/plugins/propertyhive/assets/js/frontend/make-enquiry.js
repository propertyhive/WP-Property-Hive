var is_submitting = false;
var form_obj;
jQuery( function($){

    // Enquiry form being submitted
    $('body').on('submit', 'form[name=\'ph_property_enquiry\']', function()
    {
        if (!is_submitting)
        {
            is_submitting = true;

            var data = $(this).serialize() + '&'+$.param({ 'action': 'propertyhive_make_property_enquiry' });

            form_obj = $(this);

            form_obj.find('#enquirySuccess').hide();
            form_obj.find('#enquiryValidation').hide();
            form_obj.find('#enquiryError').hide();

            $.post( propertyhive_make_property_enquiry_params.ajax_url, data, function(response) {

                if (response.success == true)
                {
                    if ( propertyhive_make_property_enquiry_params.redirect_url && propertyhive_make_property_enquiry_params.redirect_url != '' )
                    {
                        window.location.href = propertyhive_make_property_enquiry_params.redirect_url;
                    }
                    else
                    {
                        form_obj.find('#enquirySuccess').fadeIn();
                        form_obj.trigger('ph:success');

                        form_obj.trigger("reset");
                    }
                }
                else
                {
                    if (response.reason == 'validation')
                    {
                        form_obj.find('#enquiryValidation').fadeIn();
                        form_obj.trigger('ph:validation');
                    }
                    else if (response.reason == 'nosend')
                    {
                        form_obj.find('#enquiryError').fadeIn();
                        form_obj.trigger('ph:nosend');
                    }
                }

                is_submitting = false;

                if ( typeof grecaptcha != 'undefined' )
                {
                    grecaptcha.reset();
                }

            });
        }

        return false;
    });

});