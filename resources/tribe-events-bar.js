jQuery( document ).ready( function ( $ ) {

	$( 'select[name=tribe-events-bar-view]' ).change( function () {
		var el = $( this );
		$( 'form#tribe-events-bar-form' ).attr( 'action', el.val() ).submit();
	} );

	$( 'a.tribe-events-bar-view' ).on( 'click', function ( e ) {
		e.preventDefault();
		var el = $( this );
		$( 'form#tribe-events-bar-form' ).attr( 'action', el.attr( 'href' ) ).submit();
	} );

} );