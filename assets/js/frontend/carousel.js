jQuery(window).load(function()
{
	/*jQuery('.propertyhive-carousel ul.properties').slick({
    	slide: 'li',
    	slidesToShow:3,
   		slidesToScroll:1,
  	});*/

  	jQuery('.propertyhive-carousel ul.properties').owlCarousel({
    	itemElement: 'LI',
    	stageElement: 'UL'
    	//slidesToShow:3,
   		//slidesToScroll:1,
  	});
});