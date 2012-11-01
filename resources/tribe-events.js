jQuery(document).ready(function($) {


	function tribe_event_tooltips() {
		// large event tooltips for main calendar & tooltips for week view
		$('.events-gridview .tribe-events-calendar, .tribe-events-week .tribe-events-grid').delegate('div[id*="tribe-events-event-"]', 'mouseenter', function() {
			// Check if on week view or calendar view
			if( $('body').hasClass('tribe-events-week') ) {
				var bottomPad = $(this).outerHeight() + 5;
			} else if( $('body').hasClass('events-gridview') ) { // Cal View Tooltips
				var bottomPad = $(this).find('a').outerHeight() + 18;
			} else if( $('body').is('.single-tribe_events, .events-list') ) { // Single/List View Recurring Tooltips
				var bottomPad = $(this).outerHeight() + 12;
			}
			// Widget Tooltips
			if( $(this).parents('.tribe-events-calendar-widget').length ) {
				var bottomPad = $(this).outerHeight() - 6;
			}
			$(this).find('.tribe-events-tooltip').css('bottom', bottomPad).show();
		}).delegate('div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', 'mouseleave', function() {
			if ($.browser.msie && $.browser.version <= 9) {
         		$(this).find('.tribe-events-tooltip').hide()
      		} else {
         		$(this).find('.tribe-events-tooltip').stop(true,false).fadeOut(200);
      		}
		});		
	}
	
	tribe_event_tooltips();
	
   	// Add classes on various loops
   	$('.tribe-events-loop .vevent:last').addClass('tribe-last');
   	$('.events-gridview table.tribe-events-calendar').find('td.tribe-events-thismonth').each(function(index) {
          $(this).children('.vevent').last().addClass('tribe-last');
    });
	
});
