window.addEventListener('load', function () 
{
    const tours = tour_plugin.tours;
    const driver = window.driver.js.driver;

    const tourId = 'example-tour';

    if (!tours || !tours[tourId]) {
        console.error('Tour not found:', tourId);
        return;
    }

    const tourSteps = tours[tourId];

    const driverObj = driver({
        showProgress: true,
        steps: tourSteps,
    });

    setTimeout(() => { driverObj.drive(0) }, 250);
});
