jQuery(document).ready(function($) {
	// prepare calendar for popups
	$("table.tec-calendar tbody tr").each(function(index) {
		// add a class of "right" to Friday & Saturday so tooltips stay onscreen
		$(this).find("td:gt(3)").addClass("tec-right");
	});

	// big popups
	$("table.tec-calendar:not(.tec-calendar-widget) .tec-event a").live('mouseenter', function() {
		
		// one for IE6, one for everybody else
		if ($.browser.msie && $.browser.version == 6) {
			var bottomPad = $(this).parents("td").outerHeight() + 5;
		}
		else {
			var bottomPad = $(this).outerHeight() + 18;
		}
		
		$(this).next(".tec-tooltip").css('bottom', bottomPad).fadeIn(300);
	}).live('mouseleave', function() {
		$(this).next(".tec-tooltip").fadeOut(100);
	});
	
	// little popups
	$("table.tec-calendar-widget .tec-event:has(a)").live('mouseenter', function() {
		
		// one for IE6, one for everybody else
		if ($.browser.msie && $.browser.version == 6) {
			var bottomPad = $(this).outerHeight();
		}
		else {
			var bottomPad = $(this).outerHeight() + 3;
		}
		
		$(this).find(".tec-tooltip").css('bottom', bottomPad).fadeIn(300);
	}).live('mouseleave', function() {
		$(this).find(".tec-tooltip").fadeOut(100);
	});
	
	// datepicker
	$(".tec-events-dropdown").live('change', function() {
		baseUrl = $(this).parent().attr("action");
		
		url = baseUrl + $('#tec-events-year').val() + '-' + $('#tec-events-month').val();
		
		$.pjax({ url: url, container: '#tec-content', fragment: '#tec-content' });
	});
	
	// PJAX
	$('.tec-prev-month a, .tec-next-month a').pjax('#tec-content', { timeout: 10000, fragment: '#tec-content' });
	
});