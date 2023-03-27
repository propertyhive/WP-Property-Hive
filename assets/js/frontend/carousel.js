jQuery(window).load(function()
{
	var slider = tns({
    	container: '.propertyhive-shortcode-carousel',
    	items: propertyhive_carousel_params.items,
    	controlsPosition: propertyhive_carousel_params.controlsPosition,
    	nav: false,
    	gutter: propertyhive_carousel_params.gutter,
    	mouseDrag: propertyhive_carousel_params.mouseDrag,
    	controlsText: propertyhive_carousel_params.controlsText,
    	responsive: propertyhive_carousel_params.responsive
  	});
});