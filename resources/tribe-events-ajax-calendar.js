//alert( TribeCalendar.ajaxurl );


jQuery( document ).ready( function ( $ ) {

	var hasPushstate = !!(window.history && history.pushState);
	
	if( hasPushstate ) {
	
		window.onpopstate = function(event) {	

			var tribe_nopop = false;
			var pop_date = event.state.date;		
			tribe_events_calendar_ajax_post( pop_date, null, tribe_nopop );
		};

		$( '.tribe-events-calendar .tribe-events-nav a' ).live( 'click', function ( e ) {

			e.preventDefault();
			var tribe_nopop = true;
			var month_target = $( this ).attr( "data-month" );
			var href_target = $( this ).attr( "href" );
			tribe_events_calendar_ajax_post( month_target, href_target, tribe_nopop );
		} );

		$( '.tribe-events-calendar select.tribe-events-events-dropdown' ).live( 'change', function ( e ) {

			var tribe_nopop = true;
			var baseUrl = $(this).parent().attr('action');
			var date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();		
			var href_target = baseUrl + date + '/';		
			tribe_events_calendar_ajax_post( date, href_target, tribe_nopop );
		} );

		function tribe_events_calendar_ajax_post( date, href_target, tribe_nopop ) {

			$( '.ajax-loading' ).show();	

			var params = {
				action:'tribe_calendar',
				eventDate:date
			};

			$.post(
				TribeCalendar.ajaxurl,
				params,
				function ( response ) {
					$( "#ajax-loading" ).hide();
					if ( response !== '' ) {
						$( '#tribe-events-content.tribe-events-calendar' ).html( response );
						var page_title = $(response).find("#tribe-events-header").attr('data-title');	
						$(document).attr('title', page_title);
						if( tribe_nopop ) {
							history.pushState({
								"date": date
							}, page_title, href_target);
						}
					}
				}
			);
		}
	} else {
		$( '.tribe-events-calendar .tribe-events-nav a' ).live( 'click', function ( e ) {
			$( '.ajax-loading' ).show();
		} );
		
		$( '.tribe-events-calendar select.tribe-events-events-dropdown' ).live( 'change', function ( e ) {
			
			$( '.ajax-loading' ).show();
			var baseUrl = $(this).parent().attr('action');
			var date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();		
			var href_target = baseUrl + date + '/';		
			window.location = href_target;			
		} );
	}

} );