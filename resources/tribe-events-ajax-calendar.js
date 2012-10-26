//alert( TribeCalendar.ajaxurl );


jQuery( document ).ready( function ( $ ) {	
	
	window.onpopstate = function(event) {	
		
		var tribe_pop = false;
		var pop_date = event.state.date;
		var pop_target = event.state.target;
		tribe_events_calendar_ajax_post( pop_date, pop_target, tribe_pop );
	};

	$( '.tribe-events-calendar .tribe-events-nav a' ).live( 'click', function ( e ) {

		e.preventDefault();
		var tribe_pop = true;
		var month_target = $( this ).attr( "data-month" );
		var href_target = $( this ).attr( "href" );
		tribe_events_calendar_ajax_post( month_target, href_target, tribe_pop );
	} );

	$( '.tribe-events-calendar select.tribe-events-events-dropdown' ).live( 'change', function ( e ) {

		var tribe_pop = true;
		var baseUrl = $(this).parent().attr('action');
		var date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();		
		var href_target = baseUrl + date + '/';		
		tribe_events_calendar_ajax_post( date, href_target, tribe_pop );
	} );

	function tribe_events_calendar_ajax_post( date, href_target, tribe_pop ) {

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
					if( tribe_pop ) {
						history.pushState({
							"date": date, 
							"target": href_target
						}, null, href_target);
					}
				}
			}
		);
	}

} );