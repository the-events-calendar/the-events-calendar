jQuery(document).ready(function($) {
  
	$('body').on( 'click', 'a.tribe-mini-ajax', function(e){
		e.preventDefault();    
		var month_target = $(this).attr("data-month");
		var params = {
			action: 'calendar-mini',
			eventDate: month_target
		};
		$("#tribe-mini-ajax-month").hide();
		$("#ajax-loading-mini").show();
		$.post(
			TribeMiniCalendar.ajaxurl,
			params,
			function ( response ) {
				$("#ajax-loading-mini").hide();
				$("#tribe-mini-ajax-month").show();
				$("#calendar_wrap").html( response );
			}
			);
	});   
});