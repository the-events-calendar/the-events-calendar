/* global ClipboardJS */
( function ( cb ) {
		const btns = document.querySelectorAll(
			'.tec-events-calendar-embeds__snippet-modal-copy-button'
		);
		new cb( btns );
} )( ClipboardJS );
