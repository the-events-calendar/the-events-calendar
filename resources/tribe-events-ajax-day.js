jQuery( document ).ready( function ( $ ) {
	// PJAX for calendar next/prev month links
    $('#tribe-events-header').delegate('.tribe-events-nav-prev a, .tribe-events-nav-next a', 'click', function(e) {
    	e.preventDefault();
        $.pjax({ url: $(this).attr('href'), container: '#tribe-events-header', fragment: '#tribe-events-header', timeout: 10000 });
        $('.ajax-loading').show();      
   	});
});


// jQuery( document ).ready( function ( $ ) {	
	
// 	window.onpopstate = function( event ) {	
		
// 		var tribe_pop = false;
// 		var pop_date = event.state.date;
// 		tribe_events_day_ajax_post( pop_date, null, tribe_pop );
// 	};

// 	$( '.tribe-events-day .tribe-events-nav a' ).live( 'click', function ( e ) {
// 		e.preventDefault();
// 		var tribe_pop = true;
// 		var month_target = $( this ).attr( "data-day" );
// 		var href_target = $( this ).attr( "href" );
// 		tribe_events_day_ajax_post( month_target, href_target, tribe_pop );
// 	} );

// 	$( '.tribe-events-day select.tribe-events-events-dropdown' ).live( 'change', function ( e ) {

// 		var tribe_pop = true;
// 		var baseUrl = $(this).parent().attr('action');
// 		var date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();		
// 		var href_target = baseUrl + date + '/';		
// 		tribe_events_day_ajax_post( date, href_target, tribe_pop );
// 	} );

// 	function tribe_events_day_ajax_post( date, href_target, tribe_pop ) {

// 		$( '.ajax-loading' ).show();	
                
// 		var params = {
// 			action:'tribe_event_day',
// 			eventDate:date
// 		};

// 		$.post(
// 			TribeCalendar.ajaxurl,
// 			params,
// 			function ( response ) {
// 				$( "#ajax-loading" ).hide();
// 				if ( response !== '' ) {
// 					$( '#tribe-events-content.tribe-events-day-grid' ).replaceWith( response );
// 					if( tribe_pop ) {
// 						history.pushState({
// 							"date": date
// 						}, null, href_target);
// 					}
// 				}
// 			}
// 		);
// 	}

// } );