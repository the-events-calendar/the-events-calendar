/* global ClipboardJS */
( function ( $, cb ) {
	$( document ).ready( function() {
		const btns = document
			.querySelectorAll( '.tec-events-calendar-embeds-snippet-modal-copy-button' );
		new cb( btns );
	} );
} )( jQuery, ClipboardJS );
