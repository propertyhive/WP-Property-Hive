document.addEventListener('DOMContentLoaded', function () {
    // Create the modal HTML
    const modalHTML = `
        <div id="surveyModal">
            <div class="modal-container">
                <div class="modal-header">
                    ${surveyModalTranslations.modalHeader}
                </div>
                <div class="modal-content">
                    <h3>${surveyModalTranslations.modalTitle}</h3>
                    <form id="deactivationSurvey">
                        <label><input type="radio" name="reason" value="Not needed"> ${surveyModalTranslations.notNeeded}</label>
                        <label><input type="radio" name="reason" value="Too expensive"> ${surveyModalTranslations.tooExpensive}</label>
                        <label><input type="radio" name="reason" value="Found a better plugin"> ${surveyModalTranslations.betterPlugin}</label>
                        <label><input type="radio" name="reason" value="Bugs/issues"> ${surveyModalTranslations.bugsIssues}</label>
                        <label><input type="radio" name="reason" value="Other"> ${surveyModalTranslations.other}</label>
                        <textarea id="otherReasonBox" name="otherReason" placeholder="${surveyModalTranslations.otherPlaceholder}" rows="4" cols="50"></textarea>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="anonymous" id="anonymous">
                        <label><input type="checkbox" name="anonymous" value="yes"> ${surveyModalTranslations.anonymous}</label>
                    </div>
                    <button type="button" id="deactivateButton" class="button">${surveyModalTranslations.skipDeactivate}</button>&nbsp;
                    <button type="button" id="cancelButton" class="button">${surveyModalTranslations.cancel}</button>
                </div>
            </div>
        </div>
    `;

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // References to modal and buttons
    const modal = document.getElementById('surveyModal');
    const deactivateButton = document.getElementById('deactivateButton');
    const cancelButton = document.getElementById('cancelButton');
    const otherReasonBox = document.getElementById('otherReasonBox');
    const anonymous = document.getElementById('anonymous');

    // Show/hide "Other" text box and change button text
    document.querySelectorAll('input[name="reason"]').forEach((input) => {
        input.addEventListener('change', function () {
            if (this.value === 'Other') {
                otherReasonBox.style.display = 'block';
            } else {
                otherReasonBox.style.display = 'none';
            }

            // Change button text to "Deactivate"
            deactivateButton.textContent = surveyModalTranslations.deactivate;
            deactivateButton.className = "button button-primary";

            anonymous.style.display = 'block';
        });
    });

    // Close modal when clicking outside modal content
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Cancel button closes the modal
    cancelButton.addEventListener('click', closeModal);

    // Deactivate button functionality
    deactivateButton.addEventListener('click', function () {
        const selectedReason = document.querySelector('input[name="reason"]:checked');
        const otherReason = otherReasonBox.value;
        const anonymousCheckbox = document.querySelector('input[name="anonymous"]');
        const isAnonymous = anonymousCheckbox && anonymousCheckbox.checked;

        // If a reason is selected, send data to the third-party URL
        if (selectedReason) {
            const reasonData = {
                action: 'propertyhive_deactivate_survey',
                nonce: surveyModalTranslations.nonce,
                reason: selectedReason.value,
                comments: selectedReason.value === 'Other' ? otherReason : null,
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
                    deactivatePlugin();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    deactivatePlugin();
                },
            });
        } else {
            // No reason selected, deactivate immediately
            deactivatePlugin();
        }
    });

    // Function to deactivate plugin
    function deactivatePlugin() {
        const originalDeactivateLink = document.getElementById('deactivate-propertyhive');
        if (originalDeactivateLink) {
            window.location.href = originalDeactivateLink.href;
        }
    }

    // Function to close modal
    function closeModal() {
        modal.style.display = 'none';
        resetModal(); // Reset modal when closed
    }

    // Function to reset modal
    function resetModal() {
        // Clear selected options
        const selectedOptions = document.querySelectorAll('input[name="reason"]:checked');
        selectedOptions.forEach((option) => (option.checked = false));

        // Hide the "Other" text box
        otherReasonBox.style.display = 'none';
        otherReasonBox.value = '';

        // Reset the button text
        deactivateButton.textContent = surveyModalTranslations.skipDeactivate;
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
