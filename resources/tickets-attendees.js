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

} );