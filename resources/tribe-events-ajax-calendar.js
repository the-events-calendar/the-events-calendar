//alert( TribeCalendar.ajaxurl );


jQuery( document ).ready( function ( $ ) {

	// handler for tribe events calendar ajax call. jquery 1.4 minimum

	$( '.tribe-events-calendar .tribe-events-nav a' ).live( 'click', function ( e ) {

		$( '.ajax-loading' ).show();

		e.preventDefault();
		var month_target = $( this ).attr( "data-month" );

		var params = {
			action:'tribe_calendar',
			eventDate:month_target
		};

		$.post(
			TribeCalendar.ajaxurl,
			params,
			function ( response ) {
				$( "#ajax-loading" ).hide();
				console.log( response );
			}
		);


	} );
} );



/*

var month_target = $( this ).attr( "data-month" );
		var params = {
			action:'calendar-mini',
			eventDate:month_target
		};
		$( "#tribe-mini-ajax-month" ).hide();
		$( "#ajax-loading-mini" ).show();
		$.post(
			TribeMiniCalendar.ajaxurl,
			params,
			function ( response ) {
				$( "#ajax-loading-mini" ).hide();
				$( "#tribe-mini-ajax-month" ).show();
				$( "#calendar_wrap" ).html( response );
			}
		);
*/