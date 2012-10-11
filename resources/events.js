jQuery(document).ready(function($) {
	
	function tribe_event_nudge() {		
		// prepare calendar for popups
		$('table.tribe-events-calendar tbody tr, table.tribe-events-grid tr.tribe-week-events-row').each(function() {
			// add a class of "tribe-events-right" to last 3 days of week so tooltips stay onscreen. To be replaced by php.
			$(this).find('td:gt(3)').addClass('tribe-events-right');
		});
	}
	
	tribe_event_nudge();

	// big popups
	$("table.tribe-events-calendar:not(.tribe-events-calendar-widget) .tribe-events-event:not(.daynum)").live('mouseenter', function() {
		
		// one for IE6, one for everybody else
		if ($.browser.msie && $.browser.version == 6) {
			var bottomPad = $(this).parents("td").outerHeight() + 5;
		}
		else {
			var bottomPad = $(this).find('a').outerHeight() + 18;
		}
		
		$(this).find(".tribe-events-tooltip").css('bottom', bottomPad).show();
	}).live('mouseleave', function() {
		if ($.browser.msie && $.browser.version <= 9) {
			$(this).find(".tribe-events-tooltip").hide()
		} else {
			$(this).find(".tribe-events-tooltip").stop(true,false).fadeOut(200);
		}
	});
	
	// little popups
	$("table.tribe-events-calendar-widget .tribe-events-event:has(a)").live('mouseenter', function() {
		
		// one for IE6, one for everybody else
		if ($.browser.msie && $.browser.version == 6) {
			var bottomPad = $(this).outerHeight();
		}
		else {
			var bottomPad = $(this).outerHeight() + 3;
		}
		
		$(this).find(".tribe-events-tooltip").css('bottom', bottomPad).stop(true,false).fadeIn(300);
	}).live('mouseleave', function() {
		if ($.browser.msie && $.browser.version <= 9) {
			$(this).find(".tribe-events-tooltip").hide()
		} else {
			$(this).find(".tribe-events-tooltip").stop(true,false).fadeOut(200);
		}
	});
	
	// datepicker PJAX
	
	$(".tribe-events-events-dropdown").live('change', function() {
		var baseUrl = $(this).parent().attr("action");		
		var target_url = baseUrl + $('#tribe-events-events-year').val() + '-' + $('#tribe-events-events-month').val();
		$.pjax({
			url: target_url, 
			container: '#tribe-events-content', 
			fragment: '#tribe-events-content', 
			timeout: 10000
		});
	});
	
	// next prev PJAX

	$(document).pjax('a.tribe-pjax', {
		timeout: 10000, 
		fragment: '#tribe-events-content', 
		container:  '#tribe-events-content'
	})
	.bind('pjax:start', function() {
		$('.ajax-loading').show()
	})
	.bind('pjax:end',   function() {
		$('.ajax-loading').hide();
		tribe_event_nudge()
	});
       
	if ($.support.pjax) {
		$.pjax.defaults.scrollTo = false;     
	}  
	
});