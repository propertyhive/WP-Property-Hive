jQuery(document).ready(function()
{
	ph_append_full_details_layout_assistant_skeleton();

	jQuery('.open-full-details-layout-assistant').click(function(e)
	{
		e.preventDefault();

		ph_open_full_details_layout_assistant();
	});

	jQuery('.ph-ai-designer__input button').on('click', function(e)
	{
		e.preventDefault();

		ph_full_details_prompt_submit();
	});
});

var ph_viewing_revision_id = propertyhive_admin_settings_frontend_ai_layout_assistant.revision_id;

// Basic hide/show functions
function ph_append_full_details_layout_assistant_skeleton()
{
	// Create wrapper
    const wrapper = document.createElement('div');
    wrapper.id = 'ph-ai-designer';

    wrapper.innerHTML = `
        <div class="ph-ai-designer__sidebar">

            <div class="ph-ai-designer__header">
                <h2>AI Layout Designer</h2>
            </div>

            <div class="ph-ai-designer__messages">
                <div class="ph-ai-message ph-ai-message--assistant">
                    Hi 👋 Describe the property layout you'd like.
                </div>
            </div>

            <div class="ph-ai-designer__input">
                <textarea
                    placeholder="E.g. Move the gallery above the title and make the price twice as big and green..."
                ></textarea>

                <button type="button" class="button button-primary">
                    Generate Layout
                </button>
            </div>

        </div>

        <div class="ph-ai-designer__preview">
            <iframe
                src="${propertyhive_admin_settings_frontend_ai_layout_assistant.preview_url}"
                frameborder="0"
            ></iframe>
        </div>
    `;

    document.body.appendChild(wrapper);
}

function ph_open_full_details_layout_assistant()
{
	jQuery('#ph-ai-designer').css('display', 'grid');
}

function ph_close_full_details_layout_assistant()
{
	jQuery('#ph-ai-designer').hide();
}

// Chat/request functions
var ph_making_full_details_ai_request = false;
var ph_ai_iframe_scroll_top;
function ph_full_details_prompt_submit()
{
	if ( ph_making_full_details_ai_request === true )
	{
		return;
	}

	const input_prompt = jQuery('.ph-ai-designer__input textarea').val().trim();

	if ( input_prompt === '' )
    {
        alert('Please enter a prompt');
        return;
    }

    // Move prompt to history messages
    let $message = jQuery('<div>', {
        class: 'ph-ai-message ph-ai-message--user'
    });

    $message.text(input_prompt);

    jQuery('.ph-ai-designer__messages').append($message);

    jQuery('.ph-ai-designer__input textarea').val('').focus();

    jQuery('.ph-ai-designer__messages').animate({ scrollTop: jQuery('.ph-ai-designer__messages').prop("scrollHeight")}, 250);

    ph_making_full_details_ai_request = true;

    jQuery('.ph-ai-designer__input button').prop('disabled', true);

	jQuery.ajax({
    	url : ajaxurl,
    	type: 'POST',
    	dataType: 'json',
    	data : {
    		action: "propertyhive_submit_full_details_ai_layout_assistant_prompt",
    		prompt: input_prompt,
            revision_id: ph_viewing_revision_id,
    		nonce: propertyhive_admin_settings_frontend_ai_layout_assistant.nonce
    	},
    	success: function(response) 
    	{
    		if ( response.success )
            {
                console.log(response.response);

                // Update UI here
                const iframe = document.querySelector('.ph-ai-designer__preview iframe');
                const ph_ai_iframe_scroll_top = iframe.contentWindow.scrollY;
                jQuery('.ph-ai-designer__preview iframe').attr('src', propertyhive_admin_settings_frontend_ai_layout_assistant.preview_url + '&' + response.response.preview_url_arg);
                
                iframe.onload = function () {

                    /*iframe.contentWindow.scrollTo({
                        top: ph_ai_iframe_scroll_top || 0,
                        behavior: 'instant'
                    });*/

                };

                if ( response.response.layout.change_summary )
                {
                    let $message = jQuery('<div>', {
                        class: 'ph-ai-message ph-ai-message--assistant'
                    });

                    $message.html(response.response.layout.change_summary);

                    jQuery('.ph-ai-designer__messages').append($message);

                    jQuery('.ph-ai-designer__messages').animate({ scrollTop: jQuery('.ph-ai-designer__messages').prop("scrollHeight")}, 250);

                    ph_viewing_revision_id = response.response.layout_revision_id;
                }
            }
            else
            {
                alert(response.data || 'Something went wrong');
            }
    	},
    	error: function(jqXHR, textStatus, errorThrown)
    	{
	        console.error('AJAX Error:', textStatus, errorThrown, jqXHR.status, jqXHR.responseText);
	        alert('Failed to send prompt. Please refresh and try again, or check the console for more details. Error: ' + (errorThrown || 'Unknown error'));
	    },
	    complete: function(jqXHR, textStatus)
	    {
	    	ph_making_full_details_ai_request = false;
            jQuery('.ph-ai-designer__input button').prop('disabled', '');
	    }
    });
}