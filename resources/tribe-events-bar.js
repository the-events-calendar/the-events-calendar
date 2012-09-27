jQuery( document ).ready( function ( $ ) {

	$( 'select#tribe-events-bar-view' );

	$( 'a.tribe-events-bar-view' ).on( 'click', function ( e ) {
		e.preventDefault();
		var el = $( this );
		$( 'form#tribe-events-bar-form' ).attr( 'action', el.attr( 'href' ) ).submit();
	} );

} );