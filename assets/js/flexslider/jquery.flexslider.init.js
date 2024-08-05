jQuery(window).on('load', function() {
    ph_init_slideshow();

    // Ensure images are fully loaded before resizing
    jQuery('#carousel.thumbnails .slides img').each(function() {
        jQuery(this).on('load', function() {
            setTimeout(function() { jQuery(window).trigger('resize'); }, 500);
        });
        // Trigger the load event for cached images
        if (this.complete) jQuery(this).trigger('load');
    });
});

jQuery(window).on('resize', function() {
    // Set height of all thumbnails to be the same (i.e., height of the first one)
    jQuery('#carousel.thumbnails .slides img').css('height', 'auto');
    var thumbnail_height = jQuery('#carousel.thumbnails .slides img:eq(0)').height();
    
    // Apply the height to all thumbnails if it's greater than 0
    if (thumbnail_height > 0) {
        jQuery('#carousel.thumbnails .slides img').each(function() {
            jQuery(this).height(thumbnail_height);
        });
    } else {
        // Force redraw if height is 0
        jQuery('#carousel.thumbnails .slides img').hide().show();
    }
});

function ph_init_slideshow() {
    // The slider being synced must be initialized first
    jQuery('#carousel').flexslider({
        animation: "slide",
        controlNav: false,
        animationLoop: true,
        slideshow: false,
        itemWidth: 150,
        itemMargin: 5,
        asNavFor: '#slider'
    });

    jQuery('#slider').flexslider({
        animation: "slide",
        controlNav: false,
        animationLoop: true,
        slideshow: false,
        sync: "#carousel",
        smoothHeight: true
    });
}