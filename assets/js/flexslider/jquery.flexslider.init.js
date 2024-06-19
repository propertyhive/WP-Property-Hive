jQuery(window).on('load', function() {
    ph_init_slideshow();
});

// Resize flexsider image to prevent images showing as incorrect height when lazy loading
jQuery(document).on('load', '#slider.flexslider .slides img, #carousel.thumbnails .slides img', function() {
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

function ph_init_slideshow() {
    // Loop through each instance of #slider using querySelectorAll
    document.querySelectorAll('#slider').forEach(function(slider, index) {
        var $slider = jQuery(slider);

        // Find the corresponding #carousel within the same parent container if it exists
        var $parent = $slider.parent(); // Adjust this selector as needed based on your HTML structure
        var $carousel = $parent.find('#carousel').length ? $parent.find('#carousel') : null;

        if ($carousel && $carousel.length) {
            // Initialize the carousel if it exists
            $carousel.flexslider({
                animation: "slide",
                controlNav: false,
                animationLoop: true,
                slideshow: false,
                itemWidth: 150,
                itemMargin: 5,
                asNavFor: $slider
            });

            // Initialize the slider
            $slider.flexslider({
                animation: "slide",
                controlNav: false,
                animationLoop: true,
                slideshow: false,
                sync: $carousel,
                smoothHeight: true
            });
        } else {
            // Initialize the slider without carousel sync
            $slider.flexslider({
                animation: "slide",
                controlNav: false,
                animationLoop: true,
                slideshow: false,
                smoothHeight: true
            });
        }
    });
}