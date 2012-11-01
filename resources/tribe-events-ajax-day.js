//jQuery( document ).ready( function ( $ ) {
//	// PJAX for calendar next/prev month links
//    $('#tribe-events-header').delegate('.tribe-events-nav-prev a, .tribe-events-nav-next a', 'click', function(e) {
//    	e.preventDefault();
//        $.pjax({ url: $(this).attr('href'), container: '#tribe-events-header', fragment: '#tribe-events-header', timeout: 10000 });
//        $('.ajax-loading').show();      
//   	});
//});


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

jQuery( document ).ready( function ( $ ) {

	// we'll determine if the browser supports pushstate and drop those that say they do but do it badly ;)
	
	var hasPushstate = window.history && window.history.pushState && !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]|WebApps\/.+CFNetwork)/);
	
	if( hasPushstate ) {	
		
		// let's fix any browser that fires popstate on first load incorrectly
		
		var popped = ('state' in window.history), initialURL = location.href;
		
		$(window).bind('popstate', function(event) {
			
			var initialPop = !popped && location.href == initialURL;
			popped = true;
			
			// if it was an inital load, let's get out of here
			
			if ( initialPop ) return;
			
			// this really is popstate, let's fire the ajax but not overwrite our history
			
			if( event.state ) {
				var tribe_nopop = false;
				var pop_date = event.state.date;				
				tribe_events_calendar_ajax_post( pop_date, null, tribe_nopop );
			}
		} );	

		$( '#tribe-events-content .tribe-events-sub-nav a' ).live( 'click', function ( e ) {

			e.preventDefault();
			var tribe_nopop = true;
			var day_target = $( this ).attr( "data-day" );
			var href_target = $( this ).attr( "href" );
			tribe_events_calendar_ajax_post( day_target, href_target, tribe_nopop );
		} );		

		function tribe_events_calendar_ajax_post( date, href_target, tribe_nopop ) {

			$( '.ajax-loading' ).show();	

			var params = {
				action:'tribe_event_day',
				eventDate:date
			};

			$.post(
				TribeCalendar.ajaxurl,
				params,
				function ( response ) {
					$( "#ajax-loading" ).hide();
					if ( response !== '' ) {
						$( '#tribe-events-content.tribe-events-list' ).replaceWith( response );
												
//						var page_title = $(response).find("#tribe-events-header").attr('data-title');	
//						$(document).attr('title', page_title);
						
						// let's write our history for this ajax request and save the date for popstate requests to use only if not a popstate request itself
						
						if( tribe_nopop ) {
							history.pushState({
								"date": date
							}, null, href_target);
						}
					}
				}
			);
		}
	} 
} );