jQuery( document ).ready( function ( $ ) {

	$( '#filter_attendee' ).on( 'keyup paste', function () {

		var search = jQuery( this ).val().toLowerCase();

		$( 'td.column-attendee' ).each( function ( i, e ) {
			var attendee_obj = jQuery( e );
			var attendee = attendee_obj.text().toLowerCase();
			var order_id = attendee_obj.prev( 'td' ).children( 'a' ).text();

			if ( attendee.indexOf( search ) >= 0 || order_id.indexOf( search ) >= 0 ) {
				attendee_obj.parent( 'tr' ).show();
			} else {
				attendee_obj.parent( 'tr' ).hide();
			}
		} );

	} );


	$( '.tickets_checkin' ).click( function ( e ) {

		var obj = jQuery( this );

		var params = {
			action:'tribe-ticket-checkin-' + obj.attr( 'data-provider' ),
			provider:obj.attr( 'data-provider' ),
			order_ID:obj.attr( 'data-order-id' )
		};

		$.post(
			ajaxurl,
			params,
			function ( response ) {
				if ( response.success ) {
					obj.parent( 'td' ).parent( 'tr' ).addClass('tickets_checked');
				}
			},
			'json'
		);

		e.preventDefault();
	} );

} );