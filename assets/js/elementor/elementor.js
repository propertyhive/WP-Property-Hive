var propertyhive_tiny_slider_elementor_instances = [];
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

     elementorFrontend.hooks.addAction( 'frontend/element_ready/property-search-form.default', function($scope, jQuery)
     {
         toggleDepartmentFields();
     });

     elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function($scope, jQuery)
     {
          if ( propertyhive_tiny_slider_elementor_instances.length > 0 )
          {
               for ( var i = 0; i < propertyhive_tiny_slider_elementor_instances.length; ++i )
               {
                    propertyhive_tiny_slider_elementor_instances[i].destroy();
               }
          }
          propertyhive_tiny_slider_elementor_instances = [];

          document.querySelectorAll('.propertyhive-shortcode-carousel').forEach(slider => {
               propertyhive_tiny_slider_elementor_instances.push(tns({
                    container: slider,
                    items: propertyhive_carousel_params.items,
                    controlsPosition: propertyhive_carousel_params.controlsPosition,
                    nav: false,
                    gutter: propertyhive_carousel_params.gutter,
                    mouseDrag: propertyhive_carousel_params.mouseDrag,
                    controlsText: propertyhive_carousel_params.controlsText,
                    responsive: propertyhive_carousel_params.responsive
               }));
          });
     });
});