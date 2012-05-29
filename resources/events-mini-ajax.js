jQuery(document).ready(function($) {
  
  // handler for tribe events calendar widget ajax call. jquery 1.4 minimum
  
  $('#calendar_wrap').delegate('.tribe-mini-ajax', 'click', function(e){ 
    e.preventDefault();    
    var month_target = $(this).attr("href").split('/').pop();
    var params = {
      action: 'calendar-mini',
      eventDate: month_target
    };
    $("#ajax-loading-mini").show();
    $.post(
	  TribeMiniCalendar.ajaxurl,
      params,
      function ( response ) {
        $("#ajax-loading-mini").hide();
        $("#calendar_wrap").html( response );
      }
      );
  });   
});