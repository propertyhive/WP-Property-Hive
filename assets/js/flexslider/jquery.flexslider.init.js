jQuery(window).on('load', function() {
    ph_init_slideshow();
});

// Resize flexsider image to prevent images showing as incorrect height when lazy loading
jQuery('#slider.flexslider .slides img').on('load', function(){
    setTimeout(function() { jQuery(window).trigger('resize'); }, 500);
});
jQuery('#carousel.thumbnails .slides img').on('load', function(){
    setTimeout(function() { jQuery(window).trigger('resize'); }, 500);
});

jQuery(window).on('resize', function() {
    // set height of all thumbnails to be the same (i.e. height of the first one) 
    jQuery('#carousel.thumbnails .slides img').css('height', 'auto');
    var thumbnail_height = jQuery('#carousel.thumbnails .slides img:eq(0)').height();
    jQuery('#carousel.thumbnails .slides img').each(function()
    {
        jQuery(this).height(thumbnail_height);
    });
});

function ph_init_slideshow()
{
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