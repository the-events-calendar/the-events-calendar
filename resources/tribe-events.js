jQuery(document).ready(function($) {

	function tribe_event_tooltips() {
		// large event tooltips for main calendar & tooltips for week view
		$('.events-gridview .tribe-events-calendar, .tribe-events-week .tribe-events-grid').delegate('div[id*="tribe-events-event-"]', 'mouseenter', function() {
			// Check if on week view or calendar view
			if( $('body').hasClass('tribe-events-week') ) {
				var bottomPad = $(this).find('a').outerHeight();
			} else {
				var bottomPad = $(this).find('a').outerHeight() + 18;
			}	
			$(this).find('.tribe-events-tooltip').css('bottom', bottomPad).show();
		}).delegate('div[id*="tribe-events-event-"]', 'mouseleave', function() {
			if ($.browser.msie && $.browser.version <= 9) {
         		$(this).find('.tribe-events-tooltip').hide()
      		} else {
         		$(this).find('.tribe-events-tooltip').stop(true,false).fadeOut(200);
      		}
		});
		
		// small event tooltips for calendar widget
		$('.tribe-events-calendar-widget .tribe-events-calendar').delegate('div[id*="tribe-events-daynum-"]:has(a)', 'mouseenter', function() {
			var bottomPad = $(this).outerHeight() - 6;	
			$(this).find('.tribe-events-tooltip').css('bottom', bottomPad).stop(true,false).fadeIn(100);            
		}).delegate('div[id*="tribe-events-daynum-"]:has(a)', 'mouseleave', function() {
			if ($.browser.msie && $.browser.version <= 9) {
         		$(this).find('.tribe-events-tooltip').hide()
      		} else {
         		$(this).find('.tribe-events-tooltip').stop(true,false).fadeOut(200);
      		}
		});
		// tooltip for recurring info in list view
		$('.tribe-events-list .tribe-events-event-meta, .tribe-events-single').delegate('div.event-is-recurring', 'mouseenter', function() {
			var bottomPad = $(this).outerHeight() + 12;	
			$(this).find('.tribe-events-tooltip').css('bottom', bottomPad).stop(true,false).fadeIn(100);            
		}).delegate('div.event-is-recurring', 'mouseleave', function() {
			if ($.browser.msie && $.browser.version <= 9) {
         		$(this).find('.tribe-events-tooltip').hide()
      		} else {
         		$(this).find('.tribe-events-tooltip').stop(true,false).fadeOut(200);
      		}
		});		
	}
	
	tribe_event_tooltips();
	
//	// PJAX for calendar date select
//   	$('#tribe-events-header').delegate('.tribe-events-events-dropdown', 'change', function() {
//		var baseUrl = $(this).parent().attr('action');
//		var target_url = baseUrl + $('#tribe-events-events-year').val() + '-' + $('#tribe-events-events-month').val();
//        $('.ajax-loading').show();
//		$.pjax({ url: target_url, container: '#tribe-events-header', fragment: '#tribe-events-header', timeout: 10000 });
//	});
//
//	// PJAX for calendar next/prev month links
//    $('#tribe-events-header').delegate('.tribe-events-nav-prev a, .tribe-events-nav-next a', 'click', function(e) {
//    	e.preventDefault();
//        $.pjax({ url: $(this).attr('href'), container: '#tribe-events-header', fragment: '#tribe-events-header', timeout: 10000 });
//        $('.ajax-loading').show();
//   	});
        

   	// Add classes on various loops
   	$('.tribe-events-loop .vevent:last').addClass('tribe-last');
   	$('.events-gridview table.tribe-events-calendar').find('td.tribe-events-thismonth').each(function(index) {
          $(this).children('.vevent').last().addClass('tribe-last');
    });
	
});
