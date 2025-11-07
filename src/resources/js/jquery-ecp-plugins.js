jQuery( function ( $ ) {
	// placeholders
	if ( ! supports_input_placeholder() ) {
		$( '[placeholder]' )
			.on( {
				focus() {
					const input = $( this );
					if ( input.val() === input.attr( 'placeholder' ) ) {
						input.val( '' );
						input.removeClass( 'placeholder' );
					}
				},
				blur() {
					const input = $( this );
					if ( input.val() === '' || input.val() === input.attr( 'placeholder' ) ) {
						input.addClass( 'placeholder' );
						input.val( input.attr( 'placeholder' ) );
					}
				},
			} )
			.trigger( 'blur' )
			.parents( 'form' )
			.on( 'submit', function () {
				$( this )
					.find( '[placeholder]' )
					.each( function () {
						const input = $( this );
						if ( input.val() === input.attr( 'placeholder' ) ) {
							input.val( '' );
						}
					} );
			} );
	}

	function supports_input_placeholder() {
		const i = document.createElement( 'input' );
		return 'placeholder' in i;
	}
} );
