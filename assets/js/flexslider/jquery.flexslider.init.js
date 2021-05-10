jQuery(window).on('load', function() {
    ph_init_slideshow();
});

// Resize flexsider image to prevent images showing as incorrect height when lazy loading
jQuery('#slider img').on('load',function(){
    jQuery(window).trigger('resize');
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