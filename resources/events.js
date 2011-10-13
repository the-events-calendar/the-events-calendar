jQuery(document).ready(function($) {
	// prepare calendar for popups
	$("table.tribe-events-calendar tbody tr").each(function(index) {
		// add a class of "right" to Friday & Saturday so tooltips stay onscreen
		$(this).find("td:gt(3)").addClass("tribe-events-right");
	});

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
         $(this).find(".tribe-events-tooltip").fadeOut(200);
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
		
		$(this).find(".tribe-events-tooltip").css('bottom', bottomPad).fadeIn(300);
	}).live('mouseleave', function() {
		if ($.browser.msie && $.browser.version <= 9) {
         $(this).find(".tribe-events-tooltip").hide()
      } else {
         $(this).find(".tribe-events-tooltip").fadeOut(200);
      }
	});
	
	// datepicker
	$(".tribe-events-events-dropdown").live('change', function() {
		baseUrl = $(this).parent().attr("action");
		
		url = baseUrl + $('#tribe-events-events-year').val() + '-' + $('#tribe-events-events-month').val();

      $('.ajax-loading').show(); 
		$.pjax({ url: url, container: '#tribe-events-content', fragment: '#tribe-events-content', timeout: 1000 });
	});
	
	// PJAX
	$('.tribe-events-prev-month a, .tribe-events-next-month a').pjax('#tribe-events-content', { timeout: 10000, fragment: '#tribe-events-content' }).live('click', function() {
     $('.ajax-loading').show(); 
   });
	
});
