/**
 * Keep lock explanations dismissible with Escape without moving keyboard focus.
 * Delegation also covers rows replaced by WordPress Quick Edit.
 */
( () => {
	const badgeSelector = '.tec-occurrence-admin__badge';
	const dismissedClass = 'tec-occurrence-admin__badge--dismissed';

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' ) {
			document.querySelectorAll( badgeSelector ).forEach( ( badge ) => badge.classList.add( dismissedClass ) );
		}
	} );

	[ 'pointerover', 'focusin' ].forEach( ( type ) => {
		document.addEventListener( type, ( event ) => {
			const badge = event.target.closest( badgeSelector );
			if ( badge && ! badge.contains( event.relatedTarget ) ) {
				badge.classList.remove( dismissedClass );
			}
		} );
	} );
} )();
