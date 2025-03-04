/* global ClipboardJS */
( function ( clipboard ) {
		const buttons = document.querySelectorAll(
			'.tec-events-calendar-embeds__snippet-modal-copy-button'
		);
		new clipboard( buttons );
} )( ClipboardJS );
