(function( $ ) {

	// Collection of timers for this page.
	var timers = new Array();

	// Actual timer code, which wakes up every second and updates all the timers on the page.
	function updateTimers() {
		var i;
		for ( i = 0; i < timers.length; i++ ) {
			updateTimer( timers[ i ] );
		}
		window.setTimeout( updateTimers, 1000 );
	}

	// Utility function for adding zero padding.
	function zeroPad( n ) {
		n = parseInt( n );
		if ( n < 10 ) {
			return '0' + n;
		}
		else {
			return n;
		}
	}

	// Creates a new timer object.
	function Timer( id, seconds, format, complete ) {
		this.id = id;
		this.seconds = seconds;
		this.format = format;
		this.complete = complete;
	}

	// Decrements a given timer object and renders it in the
	// appropriate div.
	function updateTimer( t ) {
		var output = t.complete;
		t.seconds -= 1;
		if ( t.seconds > 0 ) {
			var days = zeroPad( ( t.seconds ) / ( 60 * 60 * 24 ) );
			var hours = zeroPad( ( t.seconds % ( 60 * 60 * 24 ) / (60 * 60)) );
			var minutes = zeroPad( ( t.seconds % ( 60 * 60 ) ) / ( 60 ) );
			var seconds = zeroPad( ( t.seconds % 60 ) );
			output = t.format.replace( 'DD', days ).replace( 'HH', hours ).replace( 'MM', minutes ).replace( 'SS', seconds );
		}
		$( "#" + t.id ).html( output );
	}

	$( document ).ready( function() {
		// Find all countdown timer divs, create a timer object for each
		// one and kick off the timers.
		var countdown_timers = $( '.tribe-countdown-timer' );
		if ( $( countdown_timers ).length > 0 ) {
			$( countdown_timers ).each( function( index, value ) {
				var unique_id = 'tribe-countdown-' + Math.floor( Math.random() * 99999 );
				var seconds = $( value ).find( 'span.tribe-countdown-seconds' ).text();
				var format = $( value ).find( 'span.tribe-countdown-format' ).html();
				var complete = $( 'h3.tribe-countdown-complete' ).show();

				// Wrap the timer in a span with a unique id so we can refer to it
				// in the timer update code.
				$( value ).wrap( '<span class="tribe-countdown-time" id=' + unique_id + '/>' );
				timers.push( new Timer( unique_id, seconds, format, complete ) );

				// Kick off first update if we're at the end.
				if ( index == countdown_timers.length - 1 ) {
					updateTimers();
				}
			} );
		}
	} );

})( jQuery );
