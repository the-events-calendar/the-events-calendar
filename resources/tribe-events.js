jQuery(document).ready(function($) {

	function tribe_event_nudge() {		
		// prepare calendar for popups
		$('table.tribe-events-calendar tbody tr, table.tribe-events-grid tr.tribe-week-events-row, .tribe-events-week .tribe-grid-content-wrap').each(function() {
			// add a class of "tribe-events-right" to last 3 days of week so tooltips stay onscreen. To be replaced by php.
			$(this).find('td:gt(3)').addClass('tribe-events-right');
			$(this).find('.column:gt(3)').addClass('tribe-events-right');
		});
	}
	
	tribe_event_nudge();

	function tribe_event_tooltips() {
		// Global tooltips
		$('.tribe-events-calendar, .tribe-events-grid, .tribe-events-list .tribe-events-event-meta, .tribe-events-single').delegate('div[id*="tribe-events-event-"], div[id*="tribe-events-daynum-"]:has(a), div.event-is-recurring', 'mouseenter', function() {
			// Week View Tooltips
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
	
	// PJAX for calendar date select
   	$('#tribe-events-header').delegate('.tribe-events-events-dropdown', 'change', function() {                
		var baseUrl = $(this).parent().attr('action');		
		var target_url = baseUrl + $('#tribe-events-events-year').val() + '-' + $('#tribe-events-events-month').val();
        $('.ajax-loading').show(); 
		$.pjax({ url: target_url, container: '#tribe-events-header', fragment: '#tribe-events-header', timeout: 10000 });
	});
	
	// PJAX for calendar next/prev month links
    $('#tribe-events-header').delegate('.tribe-events-nav-prev a, .tribe-events-nav-next a', 'click', function(e) {
    	e.preventDefault();
        $.pjax({ url: $(this).attr('href'), container: '#tribe-events-header', fragment: '#tribe-events-header', timeout: 10000 });
        $('.ajax-loading').show();      
   	});
        
    // Bind "tribe-events-right" class to last three days of calendar after ajax
    $('body').bind('end.pjax', function() { 
     	tribe_event_nudge();
    });
   
   	// Add classes on various loops
   	$('.tribe-events-loop .vevent:last').addClass('tribe-last');
   	$('.events-gridview table.tribe-events-calendar').find('td.tribe-events-thismonth').each(function(index) {
          $(this).children('.vevent').last().addClass('tribe-last');
    });
	
});
