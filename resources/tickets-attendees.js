jQuery( document ).ready( function( $ ) {


	if ( AttendeesPointer ) {
		options = $.extend( AttendeesPointer.options, {
			close: function() {
				$.post( ajaxurl, {
					pointer: AttendeesPointer.pointer_id,
					action : 'dismiss-wp-pointer'
				} );
			}
		} );

		$( AttendeesPointer.target ).pointer( options ).pointer( 'open' );
	}

	$( 'input.print' ).on( 'click', function( e ) {
		window.print();
	} );

	$( "#attendees_email_wrapper" ).dialog( {
		autoOpen   : false,
		dialogClass: 'attendees_email_dialog',
		height     : 'auto',
		width      : 400,
		modal      : true,
		buttons    : {
			"Send": function() {

				var $errors = $( '.attendees_email_dialog #email_errors' );
				var $response = $( '.attendees_email_dialog #email_response' );
				var $send = $( '.attendees_email_dialog #email_send, .attendees_email_dialog .ui-dialog-buttonpane' );

				$errors.show();

				var $email = tribe_validate_email();

				if ( $email !== false ) {

					$response.show();
					$send.hide();

					var opts = {
						action  : 'tribe-ticket-email-attendee-list',
						email   : $email,
						nonce   : Attendees.nonce,
						event_id: $( '#event_id' ).val()
					};

					$.post( ajaxurl, opts, function( response ) {
						if ( response.success ) {
							$errors.removeClass( 'ui-state-error' ).removeClass( 'ui-state-highlight' ).text( '' );
							var combo = $( '#email_to_user' );
							combo.prop( 'disabled', false );
							combo.val( '' );
							$( '#email_to_address' ).val( '' );
							$( '#attendees_email_wrapper' ).dialog( "close" );
							$response.hide();
							$send.show();
							$errors.hide();
						}
						else {
							tribe_status_bg = $response.css( 'background' );
							$errors.removeClass( 'ui-state-highlight' ).addClass( 'ui-state-error' ).text( response.message );
							$( '.ui-dialog-buttonpane' ).show();
							$( '.ui-button-text-only:first' ).hide();
							$( '#email_response' ).css( 'background', 'none' );
						}
					} );
				}

			},
			Close : function() {
				$( this ).dialog( "close" );
				$( '.ui-button-text-only:first' ).show();
				$( '.attendees_email_dialog #email_response' ).hide();
				$( '.attendees_email_dialog #email_send, .attendees_email_dialog .ui-dialog-buttonpane' ).show();
				$( '.attendees_email_dialog #email_errors' ).removeClass( 'ui-state-error' ).removeClass( 'ui-state-highlight' ).text( '' ).hide();

			}
		} } );

	$( "input.email" ).click( function() {

		/* Cleanup */
		var combo = $( '#email_to_user' );
		combo.prop( 'disabled', false );
		combo.val( '' );
		$( '#email_to_address' ).val( '' );
		$( '#email_response' ).removeClass( 'ui-state-error' ).removeClass( 'ui-state-highlight' ).text( '' );
		$( '.ui-button-text-only:first' ).show();
		$( '.attendees_email_dialog #email_response' ).hide();
		$( '.attendees_email_dialog #email_send, .attendees_email_dialog .ui-dialog-buttonpane' ).show();

		$( "#attendees_email_wrapper" ).dialog( "open" );

	} );


	$( '#email_to_address' ).on( 'keyup paste', function() {

		var email = jQuery( this ).val().trim();
		var combo = $( '#email_to_user' );

		if ( email === '' ) {
			combo.prop( 'disabled', false );
		}
		else {
			combo.val( '' );
			combo.prop( 'disabled', 'disabled' );
		}

	} );


	$( '#filter_attendee' ).on( 'keyup paste', function() {

		var search = jQuery( this ).val().toLowerCase();

		$( '#the-list' ).find( 'tr' ).each( function( i, e ) {

			var row = $( e );

			// Search by code (order, attendee and security numbers)
			var order = row.children( 'td.order_id' ).children( 'a' ).text();
			var attendee = row.children( 'td.attendee_id' ).text();
			var security = row.children( 'td.security' ).text();
			var code_found = attendee.indexOf( search ) === 0 || order.indexOf( search ) === 0 || security.indexOf( search ) === 0;

			// Search by name (we will also look at second/third names etc, not just the first name)
			var name = row.children( 'td.purchaser_name').text().toLowerCase();
			var name_found = name.indexOf( search ) === 0 || name.indexOf( " " + search ) > 1;

			if ( code_found || name_found ) {
				row.show();
			}
			else {
				row.hide();
			}
		} );

	} );


	$( '.tickets_checkin' ).click( function( e ) {

		var obj = jQuery( this );

		var params = {
			action  : 'tribe-ticket-checkin-' + obj.attr( 'data-provider' ),
			provider: obj.attr( 'data-provider' ),
			order_ID: obj.attr( 'data-attendee-id' ),
			nonce   : Attendees.checkin_nonce
		};

		$.post(
			ajaxurl,
			params,
			function( response ) {
				if ( response.success ) {
					obj.parent( 'td' ).parent( 'tr' ).addClass( 'tickets_checked' );

					$( '#total_checkedin' ).text( parseInt( $( '#total_checkedin' ).text() ) + 1 );
				}
			},
			'json'
		);

		e.preventDefault();
	} );


	$( '.tickets_uncheckin' ).click( function( e ) {

		var obj = jQuery( this );

		var params = {
			action  : 'tribe-ticket-uncheckin-' + obj.attr( 'data-provider' ),
			provider: obj.attr( 'data-provider' ),
			order_ID: obj.attr( 'data-attendee-id' ),
			nonce   : Attendees.uncheckin_nonce
		};

		$.post(
			ajaxurl,
			params,
			function( response ) {
				if ( response.success ) {
					obj.parent( 'span' ).parent( 'td' ).parent( 'tr' ).removeClass( 'tickets_checked' );
					$( '#total_checkedin' ).text( parseInt( $( '#total_checkedin' ).text() ) - 1 );
				}
			},
			'json'
		);

		e.preventDefault();
	} );

	function tribe_is_email( emailAddress ) {
		var pattern = new RegExp( /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i );
		return pattern.test( emailAddress );
	}

	function tribe_validate_email() {
		$( '#email_errors' ).removeClass( 'ui-state-error' ).addClass( 'ui-state-highlight' ).text( Attendees.sending );
		var $address = $( '#email_to_address' ).val();
		var $user = $( '#email_to_user' ).val();
		var $email = false;

		if ( $user > - 1 ) {
			$email = $user;
		}

		if ( $.trim( $address ) !== '' && tribe_is_email( $address ) ) {
			$email = $address;
		}

		if ( ! $email ) {
			$( '#email_errors' ).removeClass( 'ui-state-highlight' ).addClass( 'ui-state-error' ).text( Attendees.required );
		}

		return $email;
	}

	function tribe_array_filter( arr ) {

		var retObj = {},
			k;

		for ( k in arr ) {
			if ( arr[k] ) {
				retObj[k] = arr[k];
			}
		}

		return retObj;
	}


} );