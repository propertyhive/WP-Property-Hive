var is_submitting = false;

jQuery(document).ready(function($)
{
	toggleApplicantRegistrationDepartmentFields();
    
    $('body').on('change', 'form.applicant-registration-form [name=\'department\'], form.account-requirements-form [name=\'department\']', function()
    {
        toggleApplicantRegistrationDepartmentFields();
    });

    if ( jQuery('form.applicant-registration-form select.ph-form-multiselect').length > 0 )
    {
        jQuery('form.applicant-registration-form select.ph-form-multiselect').each(function()
        {
            jQuery(this).multiselect({
                texts: {
                    placeholder: jQuery(this).data('blank-option')
                }
            });
        });
    }

    if ( jQuery('form.account-requirements-form select.ph-form-multiselect').length > 0 )
    {
        jQuery('form.account-requirements-form select.ph-form-multiselect').each(function()
        {
            jQuery(this).multiselect({
                texts: {
                    placeholder: jQuery(this).data('blank-option')
                }
            });
        });
    }

	// Login form being submitted
    $('body').on('submit', 'form[name=\'ph_login_form\']', function()
    {
        if (!is_submitting)
        {
            is_submitting = true;
            
            var data = $(this).serialize() + '&'+$.param({ 'action': 'propertyhive_login', 'security': propertyhive_account_params.login_nonce });
            
            var form_obj = $(this);

            form_obj.find('#loginError').hide();

            $.post( propertyhive_account_params.ajax_url, data, function(response)
            {
                if (response.success == true)
                {
                    if ( propertyhive_account_params.redirect_url && propertyhive_account_params.redirect_url != '' )
                    {
                        window.location.href = propertyhive_account_params.redirect_url;
                    }
                    else
                    {
                        if ( propertyhive_account_params.my_account_url && propertyhive_account_params.my_account_url != '' )
                        {
                            window.location.href = propertyhive_account_params.my_account_url;
                        }
                        else
                        {
                            alert('Sorry but no account page has been assigned. Therefore we are unable to redirect.');
                        }
                    }
                }
                else
                {
                    form_obj.find('#loginError').fadeIn();
                }
                
                is_submitting = false;
                
            });
        }

        return false;
    });

    // Registration form being submitted
    $('body').on('submit', 'form[name=\'ph_applicant_registration_form\']', function()
    {
        if ( !is_submitting )
        {
            form_obj = jQuery(this);

            if ( propertyhive_account_params.captcha_service == 'recaptcha-v3' && typeof grecaptcha !== 'undefined' )
            {
                grecaptcha.execute(propertyhive_account_params.recaptcha_site_key, { action: 'submit' }).then(function(token) 
                {
                    jQuery('#g-recaptcha-response').val(token);
                    ph_submit_applicant_registration_form(form_obj); // Submit form after getting the new token
                });
            }
            else
            {
                ph_submit_applicant_registration_form(form_obj); // If reCAPTCHA is undefined, still submit
            }
        }

        return false;
    });

    // Account details form being submitted
    $('body').on('submit', 'form[name=\'ph_account_details_form\']', function()
    {
        if (!is_submitting)
        {
            is_submitting = true;
            
            var data = $(this).serialize() + '&'+$.param({ 'action': 'propertyhive_save_account_details', 'ph_account_details_security': propertyhive_account_params.userdetails_nonce });
            
            var form_obj = $(this);

            form_obj.find('#detailsSuccess').hide();
            form_obj.find('#detailsValidation').hide();
            form_obj.find('#detailsError').hide();

            $.post( propertyhive_account_params.ajax_url, data, function(response)
            {
                if (response.success == true)
                {
                    form_obj.find('#detailsSuccess').fadeIn();
                }
                else
                {
                    if (response.reason == 'validation')
                    {
                        form_obj.find('#detailsValidation').fadeIn();
                    }
                    else
                    {
                        form_obj.find('#detailsError').fadeIn();
                    }
                }

                if (response.new_details_nonce) 
                {
                    propertyhive_account_params.userdetails_nonce = response.new_details_nonce;
                }
                
                is_submitting = false;
                
            });
        }

        return false;
    });

    // Account requirements form being submitted
    $('body').on('submit', 'form[name=\'ph_account_requirements_form\']', function()
    {
        if (!is_submitting)
        {
            is_submitting = true;
            
            var data = $(this).serialize() + '&'+$.param({ 'action': 'propertyhive_save_account_requirements', 'ph_account_requirements_security': propertyhive_account_params.requirements_nonce });
            
            var form_obj = $(this);

            form_obj.find('#requirementsSuccess').hide();
            form_obj.find('#requirementsValidation').hide();
            form_obj.find('#requirementsError').hide();

            $.post( propertyhive_account_params.ajax_url, data, function(response)
            {
                if (response.success == true)
                {
                    form_obj.find('#requirementsSuccess').fadeIn();
                }
                else
                {
                    if (response.reason == 'validation')
                    {
                        form_obj.find('#requirementsValidation').fadeIn();
                    }
                    else
                    {
                        form_obj.find('#requirementsError').fadeIn();
                    }
                }

                if (response.new_requirements_nonce) 
                {
                    propertyhive_account_params.requirements_nonce = response.new_requirements_nonce;
                }
                
                is_submitting = false;
                
            });
        }

        return false;
    });

	$('.my-account-navigation a[href^=\'#\']').on('click', function(e)
	{
		e.preventDefault();

		// Hide/show sections
		$('.my-account-sections > div').hide();
		$('.my-account-sections ' + $(this).attr('href')).show();

		// Remove/add active class on nav/tabs
		$('.my-account-navigation a').each(function()
		{
			$(this).parent().removeClass('active');
		});
		$(this).parent().addClass('active');
	});

    $('a.ph-forgot-password').on('click', function(e)
    {
        e.preventDefault();

        $('form[name=\'ph_login_form\']').fadeOut(100, function()
        {
            $('form[name=\'ph_lost_password_form\']').fadeIn(250);
        });
    });

    $('body').on('submit', 'form[name=\'ph_lost_password_form\']', function()
    {
        if (!is_submitting)
        {
            is_submitting = true;
            
            var data = $(this).serialize() + '&'+$.param({ 'action': 'propertyhive_lost_password', 'security': propertyhive_account_params.lost_password_nonce });
            
            var form_obj = $(this);

            form_obj.find('#lostPasswordError').hide();
            form_obj.find('#lostPasswordSuccess').hide();

            $.post( propertyhive_account_params.ajax_url, data, function(response)
            {
                if (response.success == true)
                {
                    form_obj.find('#lostPasswordSuccess').fadeIn();
                }
                else
                {
                    form_obj.find('#lostPasswordError').fadeIn();
                }
                
                is_submitting = false;
                
            });
        }

        return false;
    });

    $('body').on('submit', 'form[name=\'ph_reset_password_form\']', function()
    {
        if (!is_submitting)
        {
            is_submitting = true;
            
            var data = $(this).serialize() + '&'+$.param({ 'action': 'propertyhive_reset_password', 'security': propertyhive_account_params.reset_password_nonce });
            
            var form_obj = $(this);

            form_obj.find('#resetPasswordError').hide();
            form_obj.find('#resetPasswordSuccess').hide();

            $.post( propertyhive_account_params.ajax_url, data, function(response)
            {
                if (response.success == true)
                {
                    form_obj.find('#resetPasswordSuccess').fadeIn();
                }
                else
                {
                    if ( response.errors.length > 0 )
                    {
                        for ( var i in response.errors )
                        {
                            form_obj.find('#resetPasswordError').html(response.errors[i]);
                        }
                    }
                    form_obj.find('#resetPasswordError').fadeIn();
                }
                
                is_submitting = false;
                
            });
        }

        return false;
    });
});

function ph_submit_applicant_registration_form(form_obj)
{
    is_submitting = true;
            
    var data = form_obj.serialize() + '&' + jQuery.param({ 'action': 'propertyhive_applicant_registration', 'security': propertyhive_account_params.register_nonce });
    
    form_obj.find('#registrationSuccess').hide();
    form_obj.find('#registrationValidation').hide().text(propertyhive_account_params.default_validation_error_message);
    form_obj.find('#registrationError').hide();

    jQuery.post( propertyhive_account_params.ajax_url, data, function(response)
    {
        if (response.success == true)
        {
            if ( propertyhive_account_params.redirect_url && propertyhive_account_params.redirect_url != '' )
            {
                window.location.href = propertyhive_account_params.redirect_url;
            }
            else
            {
                if ( propertyhive_account_params.my_account_url && propertyhive_account_params.my_account_url != '' )
                {
                    window.location.href = propertyhive_account_params.my_account_url;
                }
                else
                {
                    form_obj.find('#registrationSuccess').fadeIn();

                    jQuery('html,body').animate({
                        scrollTop: form_obj.find('#registrationSuccess').offset().top - 200
                    });
                    
                    form_obj.trigger("reset");
                }
            }
        }
        else
        {
            if (response.reason == 'validation')
            {
                if ( response.errors.length > 0 )
                {
                    let error_html = '';
                    for ( var i in response.errors )
                    {
                        if ( error_html != '' ) { error_html += ', '; }
                        error_html += response.errors[i];
                    }
                    if ( error_html != '' )
                    {
                        form_obj.find('#registrationValidation').text(error_html);
                    }
                }
                form_obj.find('#registrationValidation').fadeIn();
                jQuery('html,body').scrollTop( form_obj.find('#registrationValidation').offset().top );
            }
            else
            {
                form_obj.find('#registrationError').fadeIn();
            }
        }
        
        is_submitting = false;

        if ( propertyhive_account_params.captcha_service == 'recaptcha' )
        {
            // Reset reCAPTCHA for v2 (checkbox version)
            if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.reset === 'function') {
                grecaptcha.reset();
            }
        }
    });
}

jQuery(window).on('load', function() {

    if ( window.location.hash != '' )
    {
        if ( jQuery('.my-account-navigation a[href=\'' + window.location.hash + '\']').length > 0 )
        {
            var this_href = window.location.hash;
            // Hide/show sections
            jQuery('.my-account-sections > div').hide();
            jQuery('.my-account-sections ' + this_href).show();

            // Remove/add active class on nav/tabs
            jQuery('.my-account-navigation a').each(function()
            {
                jQuery(this).parent().removeClass('active');
            });
            jQuery('.my-account-navigation a[href=\'' + window.location.hash + '\']').parent().addClass('active');
        }
    }

});

jQuery(window).resize(function() {
    toggleDepartmentFields();
});

function toggleApplicantRegistrationDepartmentFields()
{
    if (jQuery('form.applicant-registration-form').length > 0 || jQuery('form.account-requirements-form').length > 0)
    {
        // There may be multiple forms on the page so treat each one individually
        jQuery('form.applicant-registration-form, form.account-requirements-form').each(function()
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

                if ( selectedDepartment == 'residential-sales' || ( propertyhive_account_params.custom_departments[selectedDepartment] && propertyhive_account_params.custom_departments[selectedDepartment].based_on == 'residential-sales' ) )
                {
                    jQuery(this).find('.sales-only').css('display', display);
                    jQuery(this).find('.residential-only').css('display', display);
                }
                else if ( selectedDepartment == 'residential-lettings' || ( propertyhive_account_params.custom_departments[selectedDepartment] && propertyhive_account_params.custom_departments[selectedDepartment].based_on == 'residential-lettings' ) )
                {
                    jQuery(this).find('.lettings-only').css('display', display);
                    jQuery(this).find('.residential-only').css('display', display);
                }
                else if ( selectedDepartment == 'commercial' || ( propertyhive_account_params.custom_departments[selectedDepartment] && propertyhive_account_params.custom_departments[selectedDepartment].based_on == 'commercial' ) )
                {
                    jQuery(this).find('.commercial-only').css('display', display);
                }
            }
        });
    }
}