var tribe_aggregator = tribe_aggregator || {};

( function( $, data ) { // eslint-disable-line no-unused-vars
	"use strict";

	/**
	 * Migration for Legacy Ignored Event
	 */
	$( function() {
		$( '#tribe-migrate-ical-settings' ).on( 'click', function() {
			var $this = $( this ),
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
					action: action
				},
				success: function ( response, status ) { // eslint-disable-line no-unused-vars
					if ( response.status ) {
						$container.html( response.text );
						setTimeout( function () {
							$dismiss.trigger( 'click' );
						}, 5000 );
					} else {
						$container.before( $( '<p>' ).html( response.text ) );
					}
				},
				complete: function () {
					$spinner.css( { visibility: 'hidden' } );
				}
			} );
		} );
	} );
}( jQuery, tribe_aggregator ) );
