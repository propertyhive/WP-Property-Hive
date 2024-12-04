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
                    <button type="button" id="deactivateButton" class="button button-primary">${surveyModalTranslations.skipDeactivate}</button>
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

        // If a reason is selected, send data to the third-party URL
        if (selectedReason) {
            const reasonData = {
                reason: selectedReason.value,
                comments: selectedReason.value === 'Other' ? otherReason : null,
            };

            fetch('https://test.com/deactivate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(reasonData),
            })
                .then((response) => {
                    if (response.ok) {
                        deactivatePlugin();
                    } else {
                        alert('Failed to submit survey. Deactivating without sending feedback.');
                        deactivatePlugin();
                    }
                })
                .catch(() => {
                    alert('Error sending survey data. Deactivating without sending feedback.');
                    deactivatePlugin();
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
