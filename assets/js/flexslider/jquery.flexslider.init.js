jQuery(window).on('load', function() {
    ph_init_slideshow();
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

            // Ensure all images in the carousel are loaded before adjusting heights
            var thumbnails = $carousel.find('.slides img');
            var imagesLoadedCount = 0;
            var totalImages = thumbnails.length;

            function setThumbnailHeight() {
                thumbnails.css('height', 'auto');
                var thumbnail_height = thumbnails.eq(0).height();
                // Check if the first thumbnail has a valid height
                if (thumbnail_height > 0) 
                {
                    thumbnails.each(function() {
                        jQuery(this).height(thumbnail_height);
                    });
                }
                else
                {
                    setTimeout(setThumbnailHeight, 100); // Retry after a short delay
                }
            }

            // Check if all images are loaded
            function checkAllImagesLoaded() {
                imagesLoadedCount++;
                if (imagesLoadedCount === totalImages) {
                    setThumbnailHeight();
                    jQuery(window).on('resize', setThumbnailHeight);
                }
            }

            // Attach load event to each image
            thumbnails.each(function() {
                if (this.complete) {
                    // Image is already loaded
                    checkAllImagesLoaded();
                } else {
                    // Attach event listener to load event
                    jQuery(this).on('load', checkAllImagesLoaded);
                }
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