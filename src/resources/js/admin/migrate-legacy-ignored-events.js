/**
 * Migration for Lagacy Ignored Event
 */
( function( $ ) {
	$( document ).ready( function() {
		$( '#tribe-migrate-legacy-events' ).on( 'click', function() {
			var $this = $( this ),
				$spinner = $this.next( '.spinner' ),
				$dismiss = $this.parents( '.notice' ).eq( 0 ).find( '.notice-dismiss' ),
				$container = $this.parent();

			$spinner.css( { visibility: 'visible' } );

			$.ajax( ajaxurl, {
				dataType: 'json',
				method: 'POST',
				data: {
					action: 'tribe_convert_legacy_ignored_events'
				},
				success: function ( response, status ) {
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
}( jQuery ) );