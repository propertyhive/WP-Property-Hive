jQuery( function($){
    
    // Floorplans lightbox
    $('.action-floorplans a').click(function()
    {
        var floorplan_urls = $(this).attr('data-floorplan-urls');
        if (floorplan_urls != '')
        {
            floorplan_urls = floorplan_urls.split("|");
            
            $.prettyPhoto.open(floorplan_urls);
        }
        else
        {
            alert("No floorplans exist for this property");
        }
        
        return false;
    });
    
});