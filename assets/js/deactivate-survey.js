document.addEventListener('DOMContentLoaded', function () {
    // Create the modal HTML
    const phModalHTML = `
        <div id="propertyhive_survey_modal">
            <div class="modal-container">
                <div class="modal-header">
                    ${deactivation_survey.modalHeader}
                </div>
                <div class="modal-content">
                    <h3>${deactivation_survey.modalTitle}</h3>
                    <form id="deactivationSurvey">
                        <label><input type="radio" name="reason" value="Broke site"> ${deactivation_survey.brokeSite}</label>
                        <label><input type="radio" name="reason" value="Confusing"> ${deactivation_survey.confusing}</label>
                        <label><input type="radio" name="reason" value="Poor performance"> ${deactivation_survey.poorPerformance}</label>
                        <label><input type="radio" name="reason" value="Inadequate support documentation"> ${deactivation_survey.inadequateSupport}</label>
                        <label><input type="radio" name="reason" value="Not needed"> ${deactivation_survey.notNeeded}</label>
                        <label><input type="radio" name="reason" value="Found a better plugin"> ${deactivation_survey.betterPlugin}</label>
                        <label><input type="radio" name="reason" value="Temporary"> ${deactivation_survey.temporary}</label>
                        <label><input type="radio" name="reason" value="Other"> ${deactivation_survey.other}</label>
                        
                        <label id="otherLabel" style="margin-top:25px;">${deactivation_survey.otherLabel}</label>
                        <textarea id="phOtherReasonBox" name="otherReason" rows="4" cols="50"></textarea>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="anonymous" id="anonymous">
                        <label><input type="checkbox" name="anonymous" value="yes"> ${deactivation_survey.anonymous}</label>
                    </div>
                    <button type="button" id="deactivateButton" class="button">${deactivation_survey.skipDeactivate}</button>&nbsp;
                    <button type="button" id="cancelButton" class="button">${deactivation_survey.cancel}</button>
                </div>
            </div>
        </div>
    `;

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', phModalHTML);

    // References to modal and buttons
    const modal = document.getElementById('propertyhive_survey_modal');
    const deactivateButton = document.getElementById('deactivateButton');
    const cancelButton = document.getElementById('cancelButton');
    const otherLabel = document.getElementById('otherLabel');
    const anonymous = document.getElementById('anonymous');

    // Show/hide "Other" text box and change button text
    document.querySelectorAll('input[name="reason"]').forEach((input) => {
        input.addEventListener('change', function () {
            
            switch (this.value) 
            {
                case "Broke site": { otherLabel.innerHTML = deactivation_survey.brokeSiteLabel; break; }
                case "Found a better plugin": { otherLabel.innerHTML = deactivation_survey.betterPluginLabel; break; }
                default: { otherLabel.innerHTML = deactivation_survey.otherLabel; break; }
            }

            // Change button text to "Deactivate"
            deactivateButton.textContent = deactivation_survey.deactivate;
            deactivateButton.className = "button button-primary";

            anonymous.style.display = 'block';
        });
    });

    // Close modal when clicking outside modal content
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            ph_deactivate_close_modal();
        }
    });

    // Cancel button closes the modal
    cancelButton.addEventListener('click', ph_deactivate_close_modal);

    // Deactivate button functionality
    deactivateButton.addEventListener('click', function () {
        const selectedReason = document.querySelector('input[name="reason"]:checked');
        const otherReason = phOtherReasonBox.value;
        const anonymousCheckbox = document.querySelector('input[name="anonymous"]');
        const isAnonymous = anonymousCheckbox && anonymousCheckbox.checked;

        // If a reason is selected, send data to the third-party URL
        if (selectedReason) {
            const reasonData = {
                action: 'propertyhive_deactivate_survey',
                nonce: deactivation_survey.nonce,
                reason: selectedReason.value,
                comments: otherReason,
                anonymous: isAnonymous ? 'yes' : 'no',
            };

            jQuery.ajax({
                url: ajaxurl, // Provided by WordPress
                method: 'POST',
                data: reasonData, // jQuery automatically converts this to x-www-form-urlencoded
                dataType: 'json', // Expect JSON response from server
                success: function (response) {
                    if (response.success) {
                        console.log('Success:', response.data);
                    } else {
                        console.error('Error:', response.data);
                    }
                    ph_deactivate_plugin();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    ph_deactivate_plugin();
                },
            });
        } else {
            // No reason selected, deactivate immediately
            ph_deactivate_plugin();
        }
    });

    document.getElementById('phOtherReasonBox').addEventListener('input', function () {

        const radioButtons = document.querySelectorAll('input[name="reason"]');
        const otherReasonRadio = document.querySelector('input[name="reason"][value="Other"]');

        // Check if any radio button is selected
        const isAnyRadioSelected = Array.from(radioButtons).some(radio => radio.checked);

        // If none are selected, select the 'Other' radio button
        if (!isAnyRadioSelected) {
            otherReasonRadio.checked = true;

            const event = new Event('change', { bubbles: true });
            otherReasonRadio.dispatchEvent(event);
        }
    });

    // Function to deactivate plugin
    function ph_deactivate_plugin() {
        const originalDeactivateLink = document.getElementById('deactivate-propertyhive');
        if (originalDeactivateLink) {
            window.location.href = originalDeactivateLink.href;
        }
    }

    // Function to close modal
    function ph_deactivate_close_modal() {
        modal.style.display = 'none';
        ph_deactivate_reset_modal(); // Reset modal when closed
    }

    // Function to reset modal
    function ph_deactivate_reset_modal() {
        // Clear selected options
        const selectedOptions = document.querySelectorAll('input[name="reason"]:checked');
        selectedOptions.forEach((option) => (option.checked = false));

        // Hide the "Other" text box
        otherLabel.innerHTML = deactivation_survey.otherLabel;

        // Reset the button text
        deactivateButton.textContent = deactivation_survey.skipDeactivate;
        deactivateButton.className = 'button';

        anonymous.style.display = 'none';
    }

    // Attach event listener to your specified button
    const deactivateButtonTrigger = document.getElementById('deactivate-propertyhive');
    if (deactivateButtonTrigger) {
        deactivateButtonTrigger.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default deactivation
            modal.style.display = 'flex'; // Show the modal
        });
    }
});
