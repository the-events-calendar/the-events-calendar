var tribe_aggregator = tribe_aggregator || {};
( function( $, _, ea ) {
	'use strict';

	ea.facebook = {};

	ea.facebook.receive_message = function( event ) {
		var response = event.data;

		if ( 'resize' === response.success ) {
			var $iframe = $( document.getElementById( 'facebook-login' ) );
			$iframe.width( response.width ).height( response.height );
		} else if ( true === response.success && response.data.token ) {
			var $container = $( '.enter-credentials, #tribe-field-facebook_token' );

			response.data.tribe_credentials_which = 'facebook';
			response.data._wpnonce = $container.find( '[name="_wpnonce"]' ).val();
			response.data._wp_http_referer = $container.find( '[name="_wp_http_referer"]' ).val();

			var jqxhr = $.ajax( {
				type: 'POST',
				url: ajaxurl + '?action=tribe_aggregator_save_credentials',
				data: response.data,
				dataType: 'json'
			} );

			jqxhr.done( function( wp_response ){
				if ( $container.hasClass( 'enter-credentials' ) ){
					$container.addClass( 'credentials-entered has-credentials' ).removeClass( 'enter-credentials' );
					$container.find( '#tribe-has-facebook-credentials' ).val( 1 ).trigger( 'change' );
				} else {

				}
			} );
		} else {
			var $status = $( '.tribe-ea-status' );
			$status.text( $status.data( 'errorMessage' ) );
		}
	};

	// Listen to message from child window
	var listen = window[ ( window.addEventListener ? 'addEventListener' : 'attachEvent' ) ];
	listen( ( window.addEventListener ? "message" : "onmessage" ), ea.facebook.receive_message, false );

} )( jQuery, window.underscore || window._, tribe_aggregator );
