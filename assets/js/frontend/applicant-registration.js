function toggleApplicantRegistrationDepartmentFields()
{
    if (jQuery('form.applicant-registration-form').length > 0)
    {
        // There may be multiple forms on the page so treat each one individually
        jQuery('form.applicant-registration-form').each(function()
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
            
            if (selected.length > 0)
            {
                selectedDepartment = selected.val();

                // controls won't always be display:block so we should get the 
                // first visible component (that isnt sales/lettings-only) and 
                // use that display
                var display = 'block';
                jQuery(this).find('.control').each(function()
                {
                    if (!jQuery(this).hasClass('.sales-only') && !jQuery(this).hasClass('.lettings-only') && !jQuery(this).hasClass('.residential-only') && !jQuery(this).hasClass('.commercial-only') && jQuery(this).css('display') != 'none')
                    {
                        display = jQuery(this).css('display');
                    }
                });

                if (selectedDepartment == 'residential-sales')
                {
                    jQuery(this).find('.sales-only').css('display', display);
                    jQuery(this).find('.residential-only').css('display', display);
                }
                else if (selectedDepartment == 'residential-lettings')
                {
                    jQuery(this).find('.lettings-only').css('display', display);
                    jQuery(this).find('.residential-only').css('display', display);
                }
                else if (selectedDepartment == 'commercial')
                {
                    jQuery(this).find('.commercial-only').css('display', display);
                }
            }
        });
    }
}

var is_submitting = false;

jQuery( function($){

    toggleApplicantRegistrationDepartmentFields();
    
    $('form.applicant-registration-form [name=\'department\']').change(function()
    {
        toggleApplicantRegistrationDepartmentFields();
    });

    // Enquiry form being submitted
    $('body').on('submit', 'form[name=\'ph_applicant_registration_form\']', function()
    {
        if (!is_submitting)
        {
            is_submitting = true;
            
            var data = $(this).serialize() + '&'+$.param({ 'action': 'propertyhive_applicant_registration' });
            
            var form_obj = $(this);

            form_obj.find('#enquirySuccess').hide();
            form_obj.find('#enquiryValidation').hide();
            form_obj.find('#enquiryError').hide();

            $.post( propertyhive_applicant_registration_params.ajax_url, data, function(response)
            {
                if (response.success == true)
                {
                    if ( propertyhive_applicant_registration_params.redirect_url && propertyhive_applicant_registration_params.redirect_url != '' )
                    {
                        window.location.href = propertyhive_applicant_registration_params.redirect_url;
                    }
                    else
                    {
                        if ( propertyhive_applicant_registration_params.my_account_url && propertyhive_applicant_registration_params.my_account_url != '' )
                        {
                            window.location.href = propertyhive_applicant_registration_params.my_account_url;
                        }
                        else
                        {
                            form_obj.find('#enquirySuccess').fadeIn();
                            
                            form_obj.trigger("reset");
                        }
                    }
                }
                else
                {
                    if (response.reason == 'validation')
                    {
                        form_obj.find('#enquiryValidation').fadeIn();
                    }
                    else if (response.reason == 'nosend')
                    {
                        form_obj.find('#enquiryError').fadeIn();
                    }
                }
                
                is_submitting = false;
                
            });
        }

        return false;
    });

});

jQuery(window).resize(function() {
    toggleDepartmentFields();
});