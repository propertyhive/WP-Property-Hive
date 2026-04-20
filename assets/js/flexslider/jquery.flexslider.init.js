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

jQuery(function ($) {
  document.addEventListener('click', function (e) {
    var link = e.target.closest('#slider .slides li a[data-fancybox]');
    if (!link) return;

    var $link = $(link);
    var $li = $link.closest('li');
    var $slider = $link.closest('.flexslider');
    var group = $link.attr('data-fancybox');
    var href = $link.attr('href') || '';

    // Only handle actual image links
    if (!href.match(/\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i)) {
      return;
    }

    // Stop Fancybox's default delegated handler from seeing this click
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    var $realItems = $slider.find('.slides li:not(.clone) a[data-fancybox="' + group + '"]');

    var startIndex;
    if ($li.hasClass('clone')) {
      startIndex = $realItems.filter(function () {
        return $(this).attr('href') === href;
      }).first().parent().index();
    } else {
      startIndex = $realItems.index(link);
    }

    if (startIndex < 0) {
      startIndex = 0;
    }

    $.fancybox.open(
      $realItems.map(function () {
        return {
          src: $(this).attr('href'),
          opts: {
            caption: $(this).attr('title') || ''
          }
        };
      }).get(),
      {
        loop: true
      },
      startIndex
    );
  }, true); // <- capture phase
});

function ph_init_slideshow() 
{
    // Get all carousel elements on the page
    jQuery('[id="slider"]').each(function() 
    {
        var slider = jQuery(this);

        // Get the parent container of this carousel
        var parentContainer = slider.parent();

        // Find the related slider within the same parent container
        var carousel = parentContainer.find('#carousel');

        // Initialize the carousel and slider within this parent container
        carousel.flexslider({
            animation: "slide",
            controlNav: false,
            animationLoop: true,
            slideshow: false,
            itemWidth: 150,
            itemMargin: 5,
            asNavFor: slider
        });

        slider.flexslider({
            animation: "slide",
            controlNav: false,
            animationLoop: true,
            slideshow: false,
            sync: carousel,
            smoothHeight: true
        });
    });
}