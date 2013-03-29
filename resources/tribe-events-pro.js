jQuery( document ).ready( function ( $ ) {
	function tribe_ical_url() {
		var url = document.URL;

		var separator = '?';
		if ( url.indexOf( '?' ) > 0 )
			separator = '&';

		var new_link = url + separator + 'ical=1' + '&' + 'tribe_display=' + tribe_ev.state.view;

		$( 'a.tribe-events-ical' ).attr( 'href', new_link );
	}

	$( tribe_ev.events ).on( "tribe_ev_ajaxSuccess", function () {
		tribe_ical_url();
	} );

	tribe_ical_url();
} );