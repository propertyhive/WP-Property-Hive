jQuery( window ).on( 'elementor/frontend/init', function()
{
	elementorFrontend.hooks.addAction( 'frontend/element_ready/property-images.default', function($scope, jQuery)
	{
         ph_init_slideshow();
    });

    elementorFrontend.hooks.addAction( 'frontend/element_ready/property-map.default', function($scope, jQuery)
	{
         initialize_property_map();
    });

    elementorFrontend.hooks.addAction( 'frontend/element_ready/property-street-view.default', function($scope, jQuery)
	{
         initialize_property_street_view();
    });
});