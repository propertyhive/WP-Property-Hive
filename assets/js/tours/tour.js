window.addEventListener('load', function () 
{
    const tours = tour_plugin.tours;
    const driver = window.driver.js.driver;

    function revert_to_first_tab(activeElement, activeStep, options) {
        jQuery('#propertyhive_metabox_tabs a').eq(0).trigger('click');
    }

    // Function to resolve string to function
    function getCallbackFunction(callbackName) {
        const callbacks = {
            revert_to_first_tab
        };

        return callbacks[callbackName] || null;
    }

    const tourId = 'add-property';

    if (!tours || !tours[tourId]) {
        console.log('Tour not found');
        return;
    }

    const tourSteps = tours[tourId].map((step) => ({
        ...step,
        popover: {
            ...step.popover,
            onNextClick: step.popover.onNextClick
                ? (activeElement, activeStep, options) =>
                      getCallbackFunction(step.popover.onNextClick)?.(
                          activeElement,
                          activeStep,
                          { ...options, driver: driverObj } // Pass driverObj explicitly
                      ) || driverObj.moveNext()
                : () => driverObj.moveNext(), // Default behavior
            onPrevClick: step.popover.onPrevClick
                ? (activeElement, activeStep, options) =>
                      getCallbackFunction(step.popover.onPrevClick)?.(
                          activeElement,
                          activeStep,
                          { ...options, driver: driverObj }
                      ) || driverObj.movePrevious()
                : () => driverObj.movePrevious(), // Default behavior
        },
    }));

    const driverObj = driver({
        showProgress: true,
        steps: tourSteps,
    });

    setTimeout(() => { driverObj.drive(0) }, 250);
});
