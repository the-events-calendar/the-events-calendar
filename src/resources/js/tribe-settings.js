jQuery( document ).ready( function( $ ) {

	// init chosen
	$( '.tribe-field-dropdown_chosen select' ).chosen();

	updateMapsFields();

	// toggle view of the google maps size fields
	$( '.google-embed-size input' ).change( updateMapsFields );

	// toggle view of the google maps size fields
	function updateMapsFields() {
		if ( $( '.google-embed-size input' ).attr( "checked" ) ) {
			$( '.google-embed-field' ).slideDown();
		}
		else {
			$( '.google-embed-field' ).slideUp();
		}
	}

} );
