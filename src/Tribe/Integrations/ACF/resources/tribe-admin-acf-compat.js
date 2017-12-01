(function( $ ) {
	'use strict';

	var $event_details  = $( document.getElementById( 'tribe_events_event_details' ) );
	var reinit_acf_wrap = false;

	// Runs right before the datepicker div is rendered.
	$event_details.on( 'tribe.ui-datepicker-div-beforeshow', function( e, object ) {
		$dpDiv = $( object.dpDiv );
		
		// Removes ACF's datepicker-wrapper div.
		if ( $dpDiv.parent( '.acf-ui-datepicker' ).length ) {
			
			$dpDiv.parent( '.acf-ui-datepicker' ).remove();
			object.input.datepicker( 'refresh' );

			reinit_acf_wrap = true;
		}
	});

	// Runs right upon the closing of the datepicker div.
	$event_details.on( 'tribe.ui-datepicker-div-closed', function( e, object ) {

		if ( reinit_acf_wrap ) {
			// Reinstantiates ACF's datepicker-wrapper div.
			$( object.dpDiv ).wrap( '<div class="acf-ui-datepicker" />' );
			reinit_acf_wrap = false;
		}
	});

})( jQuery );