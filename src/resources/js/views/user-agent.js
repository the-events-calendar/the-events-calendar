/**
 * Detects the user agent and adds a class to the body to indicate the device type.
 *
 * @since TBD
 */
( function() {
	const userAgent = window.navigator.userAgent;
	const deviceClasses = {
		android: /android/i,
		iphone: /iPhone/i
	};
alert( userAgent );
	Object.entries( deviceClasses ).forEach( ( [ device, pattern ] ) => {
		if ( userAgent.match( pattern ) ) {
			document.body.classList.add( `tec-is-${ device }` );
		}
	} );
} )();
