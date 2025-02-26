var is_submitting = false;
var form_obj;
jQuery( function($){

    // Enquiry form being submitted
    jQuery('body').on('submit', 'form[name=\'ph_property_enquiry\']', function()
    {
        if ( !is_submitting )
        {
            form_obj = jQuery(this);

            if ( propertyhive_make_property_enquiry_params.captcha_service == 'recaptcha-v3' && typeof grecaptcha !== 'undefined' )
            {
                grecaptcha.execute(propertyhive_make_property_enquiry_params.recaptcha_site_key, { action: 'submit' }).then(function(token) 
                {
                    jQuery('#g-recaptcha-response').val(token);
                    ph_submit_property_enquiry(); // Submit form after getting the new token
                });
            }
            else
            {
                ph_submit_property_enquiry(); // If reCAPTCHA is undefined, still submit
            }
        }

        return false;
    });

});

function ph_submit_property_enquiry()
{
    is_submitting = true;

    var data = form_obj.serialize() + '&' + jQuery.param({ 'action': 'propertyhive_make_property_enquiry' });

    form_obj.find('#enquirySuccess').hide();
    form_obj.find('#enquiryValidation').hide().text(propertyhive_make_property_enquiry_params.default_validation_error_message);
    form_obj.find('#enquiryError').hide();

    jQuery.post( propertyhive_make_property_enquiry_params.ajax_url, data, function(response) {

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
            console.log(response);
            if (response.reason == 'validation')
            {
                if ( response.errors && response.errors.length > 0 )
                {
                    let error_html = '';
                    for ( var i in response.errors )
                    {
                        if ( error_html != '' ) { error_html += ', '; }
                        error_html += response.errors[i]; 
                    }
                    form_obj.find('#enquiryValidation').text(error_html);
                }
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

        if ( propertyhive_make_property_enquiry_params.captcha_service == 'recaptcha' )
        {
            // Reset reCAPTCHA for v2 (checkbox version)
            if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.reset === 'function') {
                grecaptcha.reset();
            }
        }
    });
}