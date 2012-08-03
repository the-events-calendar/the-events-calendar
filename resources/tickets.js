jQuery( document ).ready( function ( $ ) {

	/* Show the advanced metabox for the selected provider and hide the others on selection change */
	$( 'input[name=ticket_provider]:radio' ).change( function () {
		$( 'tr.ticket_advanced' ).hide();
		$( 'tr.ticket_advanced_' + this.value ).show();
	} );

	/* Show the advanced metabox for the selected provider and hide the others at ready */
	$( 'input[name=ticket_provider]:checked' ).each( function () {
		$( 'tr.ticket_advanced' ).hide();
		$( 'tr.ticket_advanced_' + this.value ).show();
	} );

	/* "Add a ticket" link action */
	$( 'a#ticket_form_toggle' ).click( function ( e ) {
		$( this ).hide();
		ticket_clear_form();
		$( '#ticket_form' ).show();
		e.preventDefault();
	} );

	/* "Cancel" button action */
	$( '#ticket_form_cancel' ).click( function () {
		ticket_clear_form();

	} );

	/* "Save Ticket" button action */
	$( '#ticket_form_save' ).click( function () {

		var params = {
			action:'tribe-ticket-add-' + $( 'input[name=ticket_provider]:checked' ).val(),
			formdata:$( '.ticket_field' ).serialize(),
			post_ID:$( '#post_ID' ).val()
		};

		$.post(
			ajaxurl,
			params,
			function ( response ) {
				console.log(response);
				if ( response.success ) {
					ticket_clear_form();
					$( 'td.ticket_list_container' ).empty().html( response.data );
				}
			},
			'json'
		);

	} );

	/* "Delete Ticket" link action */

	$( '.ticket_delete' ).live( 'click', function ( e ) {

		e.preventDefault();

		var params = {
			action:'tribe-ticket-delete-' + $( this ).attr( "attr-provider" ),
			post_ID:$( '#post_ID' ).val(),
			ticket_id:$( this ).attr( "attr-ticket-id" )
		};

		$.post(
			ajaxurl,
			params,
			function ( response ) {

				if ( response.success ) {
					$( 'td.ticket_list_container' ).empty().html( response.data );
				}
			},
			'json'
		);


	} );

	/* "Edit Ticket" link action */

	$( '.ticket_edit' ).live( 'click', function ( e ) {

		var params = {
			action:'tribe-ticket-edit-' + $( this ).attr( "attr-provider" ),
			post_ID:$( '#post_ID' ).val(),
			ticket_id:$( this ).attr( "attr-ticket-id" )
		};

		$.post(
			ajaxurl,
			params,
			function ( response ) {
				ticket_clear_form();

				$( '#ticket_id' ).val( response.data.ID );
				$( '#ticket_name' ).val( response.data.name );
				$( '#ticket_description' ).val( response.data.description );
				$( '#ticket_price' ).val( response.data.price );
				$( 'tr.ticket_advanced_' + response.data.provider_class ).remove();
				$( 'tr.ticket.bottom' ).before( response.data.advanced_fields );

				$( 'input:radio[name=ticket_provider]' ).filter( '[value=' + response.data.provider_class + ']' ).click();

				$( 'a#ticket_form_toggle' ).hide();
				$( '#ticket_form' ).show();

			},
			'json'
		);

		e.preventDefault();

	} );


	/* Helper functions */

	function ticket_clear_form() {
		$( 'a#ticket_form_toggle' ).show();

		$( '#ticket_form input:not(:button):not(:radio)' ).val( '' );
		$( '#ticket_form textarea' ).val( '' );

		$( '#ticket_form' ).hide();
	}


} );