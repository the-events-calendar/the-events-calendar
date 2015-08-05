var tribe_events_pro_admin = {
	event: {}
};

( function( $, my ) {
	my.init = function() {
		this.init_settings();
		// Admin Google Maps Preview
		// @todo - check if this is still used anywhere, i don't think it is. 
		// this file is only enqueued on admin pages, these divs are not in the admin
		$( '#event_venue input, #event_venue select' ).change( function() {

			var selectedOption = $( '#saved_venue option:selected' );
			if ( selectedOption.val() === 0 ) {
				var form = $( this ).closest( 'form' ), street = form.find( '[name="venue[Address]"]' ).val(),
					city = form.find( '[name="venue[City]"]' ).val(),
					country = form.find( '[name="venue[Country]"]' ).val(),
					state = form.find( '[name="venue[Country]"] option:selected' ).val() === 'US'
						? form.find( '[name="venue[State]"]' ).val() : form.find( '[name="venue[Province]"]' ).val(),
					zip = form.find( '[name="venue[Zip]"]' ).val(),
					address = street + ',' + city + ',' + state + ',' + country + ',' + zip;

				if ( typeof codeAddress === 'function' ) {
					codeAddress( address );
				}
			}
			else {
				if ( typeof codeAddress === 'function' ) {
					codeAddress( selectedOption.data( 'address' ) );
				}
			}

		} );

		$( '#doaction, #doaction2' ).click( function( e ) {
			var n = $( this ).attr( 'id' ).substr( 2 );
			if ( $( 'select[name="' + n + '"]' ).val() === 'edit' && $( '.post_type_page' ).val() === 'tribe_events' ) {
				e.preventDefault();

				var ids = [];

				$( '#bulk-titles div' ).each( function() {
					var id = $( this ).attr( 'id' ), postId = id.replace( 'ttle', '' ),
						title = $( '#post-' + postId + ' .row-title' ).first().text(),
						tempHolder = $( '<div/>' ).append( $( this ).find( 'a' ) );
					$( this ).html( '' ).append( tempHolder ).append( title );

					if ( ids[id] ) {
						$( this ).remove();
					}

					ids[id] = true;
				} );
			}
		} );

		$( 'body' ).on( 'click', '.ui-dialog-titlebar .ui-dialog-titlebar-close', function() {
			tribe_events_pro_admin.reset_submit_button();
		} );

		$( 'input[name="post[]"]' ).click( function() {
			var event_id = $( this ).val();

			if ( $( this ).is( ':checked' ) ) {
				$( 'input[name="post[]"][value="' + event_id + '"]' ).prop( 'checked', true );
			}
			else {
				$( 'input[name="post[]"][value="' + event_id + '"]' ).prop( 'checked', false );
			}
		} );

		$( '.wp-list-table.posts' ).on( 'click', '.tribe-split', function() {
			var message = '';
			if ( $( this ).hasClass( 'tribe-split-all' ) ) {
				message = TribeEventsProAdmin.recurrence.splitAllMessage;
			}
			else {
				message = TribeEventsProAdmin.recurrence.splitSingleMessage;
			}
			if ( !window.confirm( message ) ) {
				return false;
			}
		} );

		/* Fix for deleting multiple events */
		$( '.wp-admin.events-cal.edit-php #doaction' ).click( function( e ) {
			if ( $( '[name="action"] option:selected' ).val() === 'trash' ) {
				if ( $( '.tribe-recurring-event-parent [name="post[]"]:checked').length > 0 && !confirm( TribeEventsProAdmin.recurrence.bulkDeleteConfirmationMessage ) ) {
					e.preventDefault();
				}
			}
		} );

		/**
		 * Test to see if we are in the editor and have a recurring event in need of
		 * realtime updates.
		 */
		if ( 'object' === typeof TribeEventsProRecurrenceUpdate ) {
			var notice = $( 'div.tribe-events-recurring-update-msg' );
			var spinner  = notice.find( 'img' );
			var progress = notice.find( 'div.progress' );
			var bar      = notice.find( 'div.bar' );
			var time     = Date.now();

			function handleResponse( data ) {
				var now     = Date.now();
				var elapsed = now - time;

				if ( data.html ) {
					notice.html( html );
				}
				if ( data.progress ) {
					updateProgress( data.progress, data.progressText );
				}
				if ( data.continue ) {
					// If multiple editors are open for the same event we don't want to hammer the server
					// and so a min delay of 1/2 sec is introduced between update requests
					if ( elapsed < 500 ) {
						setTimeout( sendRequest, 500 - elapsed  );
					} else {
						sendRequest();
					}
				}
				if ( data.complete ) {
					spinner.replaceWith( TribeEventsProRecurrenceUpdate.completeMsg );
					notice.removeClass( 'updating' ).addClass( 'completed' );
					setTimeout( removeNotice, 1000 );
				}
			}

			function sendRequest() {
				var payload = {
					event:  TribeEventsProRecurrenceUpdate.eventID,
					check:  TribeEventsProRecurrenceUpdate.check,
					action: 'tribe_events_pro_recurrence_realtime_update'
				};
				$.post( ajaxurl, payload, handleResponse, 'json' );
			}

			function updateProgress( percentage, text ) {
				percentage = parseInt( percentage );

				// The percentage should never be out of bounds, but let's handle such a thing gracefully if it arises
				if ( percentage < 0 || percentage > 100 ) {
					return;
				}

				bar.css( 'width', percentage + '%' );
				progress.attr( 'title', text );
			}

			function removeNotice() {
				var effect = {
					opacity: 0,
					height:  'toggle'
				};

				notice.animate( effect, 1000, function() {
					notice.remove();
				} );
			}

			function start() {
				sendRequest();
				updateProgress( TribeEventsProRecurrenceUpdate.progress, TribeEventsProRecurrenceUpdate.progressText );
			}

			setTimeout( start );
		}
	};

	/**
	 * initializes Pro features on the settings page
	 */
	my.init_settings = function() {
		var $hidesub = $( '[name="hideSubsequentRecurrencesDefault"]' );
		var $userhide = $( '[name="userToggleSubsequentRecurrences"]' );

		if ( ! $hidesub.length || ! $userhide.length ) {
			return;
		}

		var $userwrap = $( '#tribe-field-userToggleSubsequentRecurrences' );

		if ( $hidesub.is( ':checked' ) ) {
			$userhide.prop( 'checked', false );
			$userwrap.hide();
		}

		$hidesub.on( 'click', function() {
			var $el = $( this );

			if ( ! $el.is( ':checked' ) ) {
				$userwrap.show();
			} else {
				$userhide.prop( 'checked', false );
				$userwrap.hide();
			}
		} );
	};

	$( function() {
		my.init();
	} );
} )( jQuery, tribe_events_pro_admin );
