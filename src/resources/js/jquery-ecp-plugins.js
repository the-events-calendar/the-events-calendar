jQuery( function( $ ) {
	// placeholders
	if ( ! supports_input_placeholder() ) {
		$( '[placeholder]' )
			.on( {
				'focus': function() {
					var input = $( this );
					if ( input.val() == input.attr( 'placeholder' ) ) { // eslint-disable-line eqeqeq
						input.val( '' );
						input.removeClass( 'placeholder' );
					}
				},
				'blur': function() {
					var input = $( this );
					if ( input.val() == '' || input.val() == input.attr( 'placeholder' ) ) { // eslint-disable-line eqeqeq,max-len
						input.addClass( 'placeholder' );
						input.val( input.attr( 'placeholder' ) );
					}
				},
			} )
			.trigger( 'blur' )
			.parents( 'form' ).on(
				'submit',
				function() {
					$( this ).find( '[placeholder]' ).each( function() {
						var input = $( this );
						if ( input.val() == input.attr( 'placeholder' ) ) { // eslint-disable-line eqeqeq
							input.val( '' );
						}
					} );
				}
			);
	}

	function supports_input_placeholder() {
		var i = document.createElement( 'input' );
		return 'placeholder' in i;
	}
} );
