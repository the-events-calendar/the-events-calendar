jQuery( document ).ready( function( $ ) {
	// placeholders
	if ( ! supports_input_placeholder() ) {
		$( '[placeholder]' ).focus(function() {
			var input = $( this );
			if ( input.val() == input.attr( 'placeholder' ) ) {
				input.val( '' );
				input.removeClass( 'placeholder' );
			}
		} ).blur(function() {
			var input = $( this );
			if ( input.val() == '' || input.val() == input.attr( 'placeholder' ) ) {
				input.addClass( 'placeholder' );
				input.val( input.attr( 'placeholder' ) );
			}
		} ).blur().parents( 'form' ).submit( function() {
			$( this ).find( '[placeholder]' ).each( function() {
				var input = $( this );
				if ( input.val() == input.attr( 'placeholder' ) ) {
					input.val( '' );
				}
			} )
		} );
	}

	function supports_input_placeholder() {
		var i = document.createElement( 'input' );
		return 'placeholder' in i;
	}
} );