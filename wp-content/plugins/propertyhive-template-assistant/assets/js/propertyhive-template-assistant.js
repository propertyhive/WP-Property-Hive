jQuery(window).resize(function()
{
	ph_template_assistant_set_image_heights();
});

jQuery(document).ready(function()
{
	ph_template_assistant_set_image_heights();
});

jQuery(window).load(function()
{
	ph_template_assistant_set_image_heights();
});

function ph_template_assistant_set_image_heights()
{
	jQuery('.propertyhive ul.properties > li').height('auto');
	jQuery('.propertyhive ul.properties > li .thumbnail img').height('auto');

	// Check the top pos of the first and second and check if they're the same.
	// This will tell us if we're in a grid or not
	if ( 
		jQuery('.propertyhive ul.properties > li' ).eq(0).length > 0 &&
		jQuery('.propertyhive ul.properties > li' ).eq(1).length > 0 &&
		jQuery('.propertyhive ul.properties > li' ).eq(0).offset().top == jQuery('.propertyhive ul.properties > li' ).eq(1).offset().top
	)
	{
		// Make the images the same height
		jQuery('.propertyhive ul.properties > li .thumbnail img').each(function()
		{
			var ratio = 200 / 300;
			jQuery(this).height( jQuery(this).width() * ratio );
		});

		// Make the properties on each row the same height
		var row = 0;
		var previousTop = 0;
		var maxRowHeight = 0;
		var elements_in_row = new Array;
		jQuery('.propertyhive ul.properties > li').each(function()
		{    
	        var this_top = jQuery(this).offset().top;
	        var this_height = jQuery(this).height();
	        if (maxRowHeight != 0 && this_top != previousTop)
	        {
	            // we're on a new row
	            jQuery(elements_in_row).each(function()
	            {
	                jQuery(this).height(maxRowHeight);
	            });

	            row = row + 1;
	            maxRowHeight = 0;
	            previousTop = 0;
	            elements_in_row = new Array;
	        }

	        if (this_height > maxRowHeight)
	        {
	            maxRowHeight = this_height;
	        }

	        elements_in_row.push(jQuery(this));

	        previousTop = this_top;
		});
		jQuery(elements_in_row).each(function()
	    {
	        jQuery(this).height(maxRowHeight);
	    }); 
	}
}