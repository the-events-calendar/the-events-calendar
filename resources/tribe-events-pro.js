jQuery( document ).ready( function ( $ ) {
	function tribe_ical_url() {
		var url;
		if ( tribe_ev.state.do_string ) {
			url = tribe_ev.data.cur_url + '?' + tribe_ev.state.params;
		} else {
			url = document.URL;
		}

		var separator = '?';
		if ( url.indexOf( '?' ) > 0 )
			separator = '&';

		var new_link = url + separator + 'ical=1';

		$( 'a.tribe-events-ical' ).attr( 'href', new_link );
	}

	$( tribe_ev.events ).on( "tribe_ev_ajaxSuccess", function () {
		tribe_ical_url();
	} );

	tribe_ical_url();
} );