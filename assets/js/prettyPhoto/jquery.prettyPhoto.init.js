(function($) {
    $(function() {
    
    	// Lightbox
    	/*$("a.zoom").prettyPhoto({
    		hook: 'data-rel',
    		social_tools: false,
    		theme: 'pp_propertyhive',
    		horizontal_padding: 20,
    		opacity: 0.8,
    		deeplinking: false
    	});*/
    	$("a[data-rel^='prettyPhoto']").prettyPhoto({
    		hook: 'data-rel',
    		social_tools: false,
    		show_title: false,
    		//theme: 'pp_propertyhive',
    		deeplinking: false
    	});
    
    });
})(jQuery);
