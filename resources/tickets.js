jQuery( document ).ready( function ( $ ) {

	var datepickerOpts = {
		dateFormat:'yy-mm-dd',
		showAnim:'fadeIn',
		changeMonth:true,
		changeYear:true,
		numberOfMonths:3,
		showButtonPanel:true,
		onSelect:function ( dateText, inst ) {
			var the_date = $.datepicker.parseDate( 'yy-mm-dd', dateText );
			if ( inst.id === "ticket_start_date" ) {
				$( "#ticket_end_date" ).datepicker( 'option', 'minDate', the_date )
			} else {
				$( "#ticket_start_date" ).datepicker( 'option', 'maxDate', the_date )

			}
		}
	};

	$( "#ticket_start_date" ).datepicker( datepickerOpts );
	$( "#ticket_end_date" ).datepicker( datepickerOpts );

	var tickets_spinner_opts = {
		lines:13, // The number of lines to draw
		length:7, // The length of each line
		width:4, // The line thickness
		radius:10, // The radius of the inner circle
		rotate:0, // The rotation offset
		color:'#000', // #rgb or #rrggbb
		speed:1, // Rounds per second
		trail:60, // Afterglow percentage
		shadow:false, // Whether to render a shadow
		hwaccel:false, // Whether to use hardware acceleration
		className:'spinner', // The CSS class to assign to the spinner
		zIndex:2e9, // The z-index (defaults to 2000000000)
		top:'auto', // Top position relative to parent in px
		left:'auto' // Left position relative to parent in px
	};

	var tickets_spinner = null;
	if ( typeof Spinner === 'function' ) {
		tickets_spinner = new Spinner( tickets_spinner_opts );
	}


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
		$( 'html, body' ).animate( {
			scrollTop:$( "#ticket_form_table" ).offset().top - 50
		}, 500 );
		e.preventDefault();
	} );

	/* "Cancel" button action */
	$( '#ticket_form_cancel' ).click( function () {

		ticket_clear_form();

		$( 'html, body' ).animate( {
			scrollTop:$( "#event_tickets" ).offset().top - 50
		}, 500 );

	} );

	/* "Save Ticket" button action */
	$( '#ticket_form_save' ).click( function () {

		tickets_start_spin();

		var params = {
			action:'tribe-ticket-add-' + $( 'input[name=ticket_provider]:checked' ).val(),
			formdata:$( '.ticket_field' ).serialize(),
			post_ID:$( '#post_ID' ).val()
		};

		$.post(
			ajaxurl,
			params,
			function ( response ) {
				console.log( response );
				if ( response.success ) {
					ticket_clear_form();
					$( 'td.ticket_list_container' ).empty().html( response.data );
				}
			},
			'json'
		).complete( function () {
				$( 'html, body' ).animate( {
					scrollTop:$( "#event_tickets" ).offset().top - 50
				}, 500 );

				tickets_stop_spin();
			} );

	} );

	/* "Delete Ticket" link action */

	$( '.ticket_delete' ).live( 'click', function ( e ) {

		e.preventDefault();

		tickets_start_spin();

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
		).complete( function () {
				tickets_stop_spin();
			} );


	} );

	/* "Edit Ticket" link action */

	$( '.ticket_edit' ).live( 'click', function ( e ) {

		e.preventDefault();

		tickets_start_spin();

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

				$( '#ticket_start_date' ).val( response.data.start_date.substring( 0, 10 ) );
				$( '#ticket_end_date' ).val( response.data.end_date.substring( 0, 10 ) );

				var start_hour = response.data.start_date.substring( 11, 13 );
				var start_meridian = 'am';
				if ( parseInt( start_hour ) > 12 ) {
					start_meridian = 'pm';
					start_hour = parseInt( start_hour ) - 12;
					start_hour = ("0" + start_hour).slice( -2 );
				}
				if ( parseInt( start_hour ) === 12 ) {
					start_meridian = 'pm';
				}

				$( '#ticket_start_hour' ).val( start_hour );
				$( '#ticket_start_meridian' ).val( start_meridian );

				var end_hour = response.data.end_date.substring( 11, 13 );
				var end_meridian = 'am';
				if ( parseInt( end_hour ) > 12 ) {
					end_meridian = 'pm';
					end_hour = parseInt( end_hour ) - 12;
					end_hour = ("0" + end_hour).slice( -2 );
				}
				if ( parseInt( end_hour ) === 12 ) {
					end_meridian = 'pm';
				}

				$( '#ticket_end_hour' ).val( end_hour );
				$( '#ticket_end_meridian' ).val( end_meridian );

				$( '#ticket_start_minute' ).val( response.data.start_date.substring( 14, 16 ) );
				$( '#ticket_end_minute' ).val( response.data.end_date.substring( 14, 16 ) );


				$( 'tr.ticket_advanced_' + response.data.provider_class ).remove();
				$( 'tr.ticket.bottom' ).before( response.data.advanced_fields );

				$( 'input:radio[name=ticket_provider]' ).filter( '[value=' + response.data.provider_class + ']' ).click();

				$( 'a#ticket_form_toggle' ).hide();
				$( '#ticket_form' ).show();

			},
			'json'
		).complete( function () {
				$( 'html, body' ).animate( {
					scrollTop:$( "#ticket_form_table" ).offset().top - 50
				}, 500 );

				tickets_stop_spin();
			} );


	} );


	/* Helper functions */

	function ticket_clear_form() {
		$( 'a#ticket_form_toggle' ).show();

		$( '#ticket_form input:not(:button):not(:radio):not(:checkbox)' ).val( '' );
		$( '#ticket_form input:checkbox' ).attr( 'checked', false );

		$( '#ticket_form textarea' ).val( '' );

		$( '#ticket_form' ).hide();
	}

	function tickets_start_spin() {
		jQuery( '#event_tickets' ).css( 'opacity', '0.5' );
		tickets_spinner.spin( document.getElementById( 'event_tickets' ) );
	}

	function tickets_stop_spin() {
		jQuery( '#event_tickets' ).css( 'opacity', '1' );
		tickets_spinner.stop();
	}


} );