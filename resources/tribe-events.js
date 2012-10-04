jQuery(document).ready(function($) {

	function tribe_event_nudge() {		
		// prepare calendar for popups
		$('table.tribe-events-calendar tbody tr').each(function() {
			// add a class of "tribe-events-right" to last 3 days of week so tooltips stay onscreen. To be replaced by php.
			$(this).find('td:gt(3)').addClass('tribe-events-right');
		});	
	}
	
	tribe_event_nudge();

	function tribe_event_tooltips() { 
		// large event tooltips for main calendar
		$('.events-gridview .tribe-events-calendar').delegate('div[id*="tribe-events-event-"]', 'mouseenter', function() {
			var bottomPad = $(this).find('a').outerHeight() + 18;		
			$(this).find('.tribe-events-tooltip').css('bottom', bottomPad).show();
		}).delegate('div[id*="tribe-events-event-"]', 'mouseleave', function() {
			if ($.browser.msie && $.browser.version <= 9) {
         		$(this).find('.tribe-events-tooltip').hide()
      		} else {
         		$(this).find('.tribe-events-tooltip').fadeOut(200);
      		}
		});
		
		// small event tooltips for calendar widget
		$('.tribe-events-calendar-widget .tribe-events-calendar').delegate('div[id*="tribe-events-daynum-"]:has(a)', 'mouseenter', function() {
			var bottomPad = $(this).outerHeight() + 3;	
			$(this).find('.tribe-events-tooltip').css('bottom', bottomPad).stop(true,true).fadeIn(100);            
		}).delegate('div[id*="tribe-events-daynum-"]:has(a)', 'mouseleave', function() {
			if ($.browser.msie && $.browser.version <= 9) {
         		$(this).find('.tribe-events-tooltip').hide()
      		} else {
         		$(this).find('.tribe-events-tooltip').fadeOut(200);
      		}
		});
	}
	
	tribe_event_tooltips();
	
	// PJAX for calendar date select
	$('.events-gridview #tribe-events-content').delegate('.tribe-events-events-dropdown', 'change', function() {                
		var baseUrl = $(this).parent().attr('action');		
		var target_url = baseUrl + $('#tribe-events-events-year').val() + '-' + $('#tribe-events-events-month').val();
        $('.ajax-loading').show(); 
		$.pjax({ url: target_url, container: '#tribe-events-content', fragment: '#tribe-events-content', timeout: 10000 });
	});
	
	// PJAX for calendar next/prev month links
    $('.events-gridview #tribe-events-content').delegate('.tribe-events-prev-month a, .tribe-events-next-month a', 'click', function(e) {
    	e.preventDefault();
        $.pjax({ url: $(this).attr('href'), container: '#tribe-events-content', fragment: '#tribe-events-content', timeout: 10000 });
        $('.ajax-loading').show();      
   	});
        
    // Bind "tribe-events-right" class to last three days of calendar after ajax
    $('body').bind('end.pjax', function() { 
     	tribe_event_nudge();
    });
   
   	// Add class on list & venue loops
   	$('.tribe-events-loop .vevent:last').addClass('tribe-last');
   	$('.events-gridview table.tribe-events-calendar').find('td.tribe-events-thismonth').each(function(index) {
          $(this).children('.vevent').last().addClass('tribe-last');
    });
	
});