//alert( TribeCalendar.ajaxurl );


jQuery( document ).ready( function ( $ ) {

	$( '.tribe-events-calendar .tribe-events-nav a' ).live( 'click', function ( e ) {

		e.preventDefault();
		var month_target = $( this ).attr( "data-month" );
		tribe_events_calendar_ajax_post( month_target );
	} );

	$( '.tribe-events-calendar select.tribe-events-events-dropdown' ).live( 'change', function ( e ) {

		var date = $( '#tribe-events-events-year' ).val() + '-' + $( '#tribe-events-events-month' ).val();
		tribe_events_calendar_ajax_post( date );
	} );

	function tribe_events_calendar_ajax_post( date ) {

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
					$( '.tribe-events-calendar' ).html( response );
				}
			}
		);
	}

} );