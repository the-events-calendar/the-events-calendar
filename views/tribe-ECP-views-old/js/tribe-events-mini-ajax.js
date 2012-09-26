jQuery(document).ready(function($) {
  
  // handler for tribe events calendar widget ajax call. jquery 1.4 minimum
  
  $('#calendar_wrap .tribe-mini-ajax').live( 'click', function(e){
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