jQuery( document ).ready( function ( $ ) {


	if ( AttendeesPointer ) {
		options = $.extend( AttendeesPointer.options, {
			close:function () {
				$.post( ajaxurl, {
					pointer:AttendeesPointer.pointer_id,
					action :'dismiss-wp-pointer'
				} );
			}
		} );

		$( AttendeesPointer.target ).pointer( options ).pointer( 'open' );
	}

	$( 'input.print' ).on( 'click', function ( e ) {
		window.print();
	} );


	$( "#attendees_email_wrapper" ).dialog( {
		autoOpen:false,
		height  :200,
		width   :450,
		modal   :true,
		buttons :{
			"Send":function () {

				var $email = tribe_validate_email();

				if ( $email !== false ) {
					console.log( $email );
					var opts = {
						action:'tribe-ticket-email-attendee-list',
						email :$email
					};

					$.post( ajaxurl, opts, function ( response ) {
						if ( response.success ) {
							$( '#email_response' ).removeClass( 'ui-state-error' ).removeClass( 'ui-state-highlight' ).text( '' );
							$( '#email_to_address' ).val( '' );
							$( '#attendees_email_wrapper' ).dialog( "close" );
						}
					} );
				}

			},
			close :function () {
				$( this ).dialog( "close" );
			}
		} } );

	$( "#email" ).click( function () {
		$( "#attendees_email_wrapper" ).dialog( "open" );
	} );

	$( '#filter_attendee' ).on( 'keyup paste', function () {

		var search = jQuery( this ).val().toLowerCase();

		$( 'td.column-security' ).each( function ( i, e ) {
			var attendeeobj = jQuery( e );
			var attendee = attendeeobj.text().toLowerCase();
			var orderid = attendeeobj.prev( 'td' ).prev( 'td' ).prev( 'td' ).prev( 'td' ).children( 'a' ).text();
			var ticketid = attendeeobj.prev( 'td' ).text();

			if ( attendee.indexOf( search ) === 0 || orderid.indexOf( search ) === 0 || ticketid.indexOf( search ) === 0 ) {
				attendeeobj.parent( 'tr' ).show();
			} else {
				attendeeobj.parent( 'tr' ).hide();
			}
		} );

	} );


	$( '.tickets_checkin' ).click( function ( e ) {

		var obj = jQuery( this );

		var params = {
			action  :'tribe-ticket-checkin-' + obj.attr( 'data-provider' ),
			provider:obj.attr( 'data-provider' ),
			order_ID:obj.attr( 'data-attendee-id' )
		};

		$.post(
			ajaxurl,
			params,
			function ( response ) {
				if ( response.success ) {
					obj.parent( 'td' ).parent( 'tr' ).addClass( 'tickets_checked' );

					$( '#total_checkedin' ).text( parseInt( $( '#total_checkedin' ).text() ) + 1 );
				}
			},
			'json'
		);

		e.preventDefault();
	} );


	$( '.tickets_uncheckin' ).click( function ( e ) {

		var obj = jQuery( this );

		var params = {
			action  :'tribe-ticket-uncheckin-' + obj.attr( 'data-provider' ),
			provider:obj.attr( 'data-provider' ),
			order_ID:obj.attr( 'data-attendee-id' )
		};

		$.post(
			ajaxurl,
			params,
			function ( response ) {
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
		$( '#email_response' ).removeClass( 'ui-state-error' ).addClass( 'ui-state-highlight' ).text( 'Sending...' );
		var $address = $( '#email_to_address' ).val();
		var $user = $( '#email_to_user' ).val();
		var $email = false;

		if ( $.trim( $address ) !== '' ) {
			if ( !tribe_is_email( $address ) )
				$( '#email_response' ).removeClass( 'ui-state-highlight' ).addClass( 'ui-state-error' ).text( 'Email address is invalid' );
			else
				$email = $address;
		} else {
			if ( $user > -1 )
				$email = $user;
		}

		if ( !$email ) {
			$( '#email_response' ).removeClass( 'ui-state-highlight' ).addClass( 'ui-state-error' ).text( 'You need to select an user or type an address' );
		}

		return $email;
	}


} );