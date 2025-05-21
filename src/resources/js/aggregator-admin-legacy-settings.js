( function ( $ ) {
	// eslint-disable-line no-unused-vars
	'use strict';

	const data = window.tribe_aggregator || {};

	/**
	 * Migration for Legacy Ignored Event
	 */
	$( function () {
		$( '#tribe-migrate-ical-settings' ).on( 'click', function () {
			let $this = $( this ),
				$spinner = $this.next( '.spinner' ),
				$dismiss = $this.parents( '.notice' ).eq( 0 ).find( '.notice-dismiss' ),
				$container = $this.parent(),
				action;

			if ( 'tribe-migrate-ical-settings' === $this.attr( 'id' ) ) {
				action = 'tribe_convert_legacy_ical_settings';
			}

			$spinner.css( { visibility: 'visible' } );

			$.ajax( ajaxurl, {
				dataType: 'json',
				method: 'POST',
				data: {
					action,
				},
				success( response, status ) {
					// eslint-disable-line no-unused-vars
					if ( response.status ) {
						$container.html( response.text );
						setTimeout( function () {
							$dismiss.trigger( 'click' );
						}, 5000 );
					} else {
						$container.before( $( '<p>' ).html( response.text ) );
					}
				},
				complete() {
					$spinner.css( { visibility: 'hidden' } );
				},
			} );
		} );
	} );
} )( jQuery );
