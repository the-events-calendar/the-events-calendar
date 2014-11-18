jQuery( document ).ready( function( $ ) {

	// Admin Google Maps Preview
	// @todo - check if this is still used anywhere, i don't think it is. 
	// this file is only enqueued on admin pages, these divs are not in the admin
	$( '#event_venue input, #event_venue select' ).change( function() {

		var selectedOption = $( '#saved_venue option:selected' );
		if ( selectedOption.val() == 0 ) {
			var form = $( this ).closest( 'form' ), street = form.find( '[name="venue[Address]"]' ).val(),
				city = form.find( '[name="venue[City]"]' ).val(),
				country = form.find( '[name="venue[Country]"]' ).val(),
				state = form.find( '[name="venue[Country]"] option:selected' ).val() == "US"
					? form.find( '[name="venue[State]"]' ).val() : form.find( '[name="venue[Province]"]' ).val(),
				zip = form.find( '[name="venue[Zip]"]' ).val(),
				address = street + "," + city + "," + state + "," + country + "," + zip;

			if ( typeof codeAddress == 'function' ) {
				codeAddress( address );
			}
		}
		else {
			if ( typeof codeAddress == 'function' ) {
				codeAddress( selectedOption.data( 'address' ) );
			}
		}

	} );

	$( '#doaction, #doaction2' ).click( function( e ) {
		var n = $( this ).attr( 'id' ).substr( 2 );
		if ( $( 'select[name="' + n + '"]' ).val() == 'edit' && $( '.post_type_page' ).val() == 'tribe_events' ) {
			e.preventDefault();

			var ids = new Array();

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

	function resetSubmitButton() {
		$( '#publishing-action .button-primary-disabled' ).removeClass( 'button-primary-disabled' );
		$( '#publishing-action .spinner' ).hide();

	}

	function validRecDays() {
		if ( $( '[name="recurrence[custom-interval]"]' ).val() != parseInt( $( '[name="recurrence[custom-interval]"]' ).val() ) &&
			$( '[name="recurrence[type]"] option:selected' ).val() == "Custom" ) {
			return false;
		}

		return true;
	}

	$( '.wp-admin.events-cal #post' ).submit( function( e ) {
		if ( !validRecDays() ) {
			e.preventDefault();
			alert( $( '#rec-days-error' ).text() );
			$( '#rec-days-error' ).show();
			resetSubmitButton();
		}
	} );

	$( 'body' ).live( 'click', '.ui-dialog-titlebar .ui-dialog-titlebar-close', function() {
		resetSubmitButton();
	} );

	function validRecEnd() {
		if ( $( '[name="recurrence[type]"]' ).val() != "None" &&
			$( '[name="recurrence[end-type]"] option:selected' ).val() == "On" ) {
			return $( '[name="recurrence[end]"]' ).val() && !$( '[name="recurrence[end]"]' ).hasClass( 'placeholder' );
		}

		return true;
	}

	$( '.wp-admin.events-cal #post' ).submit( function( e ) {
		if ( !validRecEnd() ) {
			e.preventDefault();
			alert( $( '#rec-end-error' ).text() );
			$( '#rec-end-error' ).show();
			resetSubmitButton();
		}
	} );

	function updateRecurrenceText() {
		var rec_type = $( '[name="recurrence[type]"]' ).val();
		var rec_end = $( '[name="recurrence[end]"]' ).val();
		var rec_end_type = $( '[name="recurrence[end-type]"]' ).val();
		var rec_end_count = parseInt( $( '[name="recurrence[end-count]"]' ).val() );
		var rec_custom_type = $( '[name="recurrence[custom-type]"]' ).val();
		var rec_custom_interval = $( '[name="recurrence[custom-interval]"]' ).val();
		var rec_custom_interval_text = $( '#recurrence-interval-type' ).text();
		var rec_custom_week_day = $( '[name="recurrence[custom-week-day][]"]:checked' ).map(function() {
			return $( this ).val();
		} ).get();
		var rec_custom_month_number = $( '[name="recurrence[custom-month-number]"]' ).val();
		var rec_custom_month_day = $( '[name="recurrence[custom-month-day]"]' ).val();
		var rec_custom_year_month = $( '[name="recurrence[custom-year-month][]"]:checked' ).map(function() {
			return $( this ).val();
		} ).get();
		var rec_custom_year_filter = $( '[name="recurrence[custom-year-filter]"]' ).val();
		var rec_custom_year_month_number = $( '[name="recurrence[custom-year-month-number]"]' ).val();
		var rec_custom_year_month_day = $( '[name="recurrence[custom-year-month-day]"]' ).val();

		var weekdays = new Array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
		var months = new Array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
		var day_numbers = new Array( 'last', 'first', 'second', 'third', 'fourth' );

		var recurrence_text = 'Your event first occurs on ' + $( '#EventStartDate' ).val();
		var event_end_date = $( '#EventEndDate' ).val().split( '-' );
		event_end_date = new Date( event_end_date[0], event_end_date[1] - 1, event_end_date[2] );

		var event_start_date = $( '#EventStartDate' ).val().split( '-' );
		event_start_date = new Date( event_start_date[0], event_start_date[1] - 1, event_start_date[2] );

		var final_date_start = event_start_date;
		var final_date_end = event_end_date;

		if ( rec_custom_type == 'Daily' ) {
			if ( rec_custom_interval > 1 ) {
				rec_custom_interval_text = 'days'
			}
			else {
				rec_custom_interval_text = 'day'
			}
		}
		if ( rec_custom_type == 'Weekly' ) {
			if ( rec_custom_interval > 1 ) {
				rec_custom_interval_text = 'weeks'
			}
			else {
				rec_custom_interval_text = 'week'
			}
		}
		if ( rec_custom_type == 'Monthly' ) {
			if ( rec_custom_interval > 1 ) {
				rec_custom_interval_text = 'months'
			}
			else {
				rec_custom_interval_text = 'month'
			}
		}
		if ( rec_custom_type == 'Yearly' ) {
			if ( rec_custom_interval > 1 ) {
				rec_custom_interval_text = 'years'
			}
			else {
				rec_custom_interval_text = 'year'
			}
		}

		if ( rec_end_type == 'After' ) {
			if ( rec_type == 'Every Day' ) {
				final_date_start.setDate( event_start_date.getDate() + rec_end_count - 1 );
				final_date_end.setDate( event_end_date.getDate() + rec_end_count - 1 );
			}
			else if ( rec_type == 'Every Week' ) {
				final_date_start.setDate( event_start_date.getDate() + ( ( rec_end_count - 1 ) * 7 ) );
				final_date_end.setDate( event_end_date.getDate() + ( ( rec_end_count - 1 ) * 7 ) );
			}
			else if ( rec_type == 'Every Month' ) {
				final_date_start.setMonth( event_start_date.getMonth() + rec_end_count - 1 );
				final_date_end.setMonth( event_end_date.getMonth() + rec_end_count - 1 );
			}
			else if ( rec_type == 'Every Year' ) {
				final_date_start.setYear( event_start_date.getFullYear() + rec_end_count - 1 );
				final_date_end.setYear( event_end_date.getFullYear() + rec_end_count - 1 );
			}
			else if ( rec_type == 'Custom' ) {
				if ( rec_custom_type == 'Daily' ) {
					final_date_start.setDate( event_start_date.getDate() + ( ( rec_end_count - 1 ) * rec_custom_interval ) );
					final_date_end.setDate( event_end_date.getDate() + ( ( rec_end_count - 1 ) * rec_custom_interval ) );
				}
				else if ( rec_custom_type == 'Weekly' && rec_end_count > 1 ) {
					/*
					 Get the next day of week and order the other days based off that.
					 Figure out how many recurrences there are.
					 Figure out the weekday of the last recurrence.
					 Figure out the final date based off the first recurrence of that final weekday.
					 */
					var week_days_for_rec = new Array( '8' );
					var event_start_day_of_week = event_start_date.getDay();
					var has_extra_day = 0;
					for ( var i = 0; i < rec_custom_week_day.length; i++ ) {
						if ( rec_custom_week_day.length == 1 ||
							( ( rec_custom_week_day[i] - event_start_day_of_week ) > 0 && ( rec_custom_week_day[i] - event_start_day_of_week ) < week_days_for_rec[0] ) ) {
							for ( var x = 0, y = i; x < rec_custom_week_day.length; x++, y++ ) {
								week_days_for_rec[x] = rec_custom_week_day[y];
								if ( y + 1 == rec_custom_week_day.length && y > 0 ) {
									y = -1;
								}
							}
						}
					}
					// week_days_for_rec is now in order of the weekdays starting with the first one after the first instance.
					var array_index_of_final = (rec_end_count % rec_custom_week_day.length) - 1;
					if ( array_index_of_final == '-1' ) {
						array_index_of_final = rec_custom_week_day.length - 1;
					}
					if ( event_start_day_of_week + 1 != week_days_for_rec[rec_custom_week_day.length - 1] ) {
						has_extra_day = 1;
						array_index_of_final--;
						if ( array_index_of_final == '-1' ) {
							array_index_of_final = rec_custom_week_day.length - 1;
						}
					}
					var number_of_weeks_to_add = Math.ceil( ( rec_end_count / rec_custom_week_day.length) - (has_extra_day / rec_custom_week_day.length) ) - 1;

				}
			}
		}

		if ( rec_type != 'Custom' ) {
			if ( rec_end_type == 'After' ) {
				recurrence_text += ' and repeats ' + rec_type.toLowerCase() + ' for ' + rec_end_count + ' ' + $( '#occurence-count-text' ).text();
			}
			else if ( rec_end_type == 'On' && rec_end != '' ) {
				recurrence_text += ' and repeats ' + rec_type.toLowerCase() + ' until ' + rec_end;
			}
			recurrence_text += '.';
		}
		else {
			recurrence_text += ' and repeats ' + rec_custom_type.toLowerCase();
			if ( rec_custom_interval > 1 ) {
				recurrence_text += ' every ' + rec_custom_interval + ' ' + rec_custom_interval_text.toLowerCase();
			}
			else {
				recurrence_text += ' every ' + rec_custom_interval_text.toLowerCase();
			}

			if ( rec_custom_type == 'Weekly' && rec_custom_week_day && rec_custom_week_day.length > 0 ) {
				recurrence_text += ' on ';

				for ( var i = 0; i < rec_custom_week_day.length; i++ ) {
					if ( i == 0 ) {
						recurrence_text += weekdays[rec_custom_week_day[i] - 1];
					}
					else if ( i != (rec_custom_week_day.length - 1) && i > 0 ) {
						recurrence_text += ', ' + weekdays[rec_custom_week_day[i] - 1];
					}
					else {
						if ( rec_custom_week_day.length > 2 ) {
							recurrence_text += ',';
						}
						recurrence_text += ' and ' + weekdays[rec_custom_week_day[i] - 1];
					}
				}
			}

			if ( rec_custom_type == 'Monthly' ) {
				recurrence_text += ' on';

				if ( isNaN( rec_custom_month_number ) ) {
					recurrence_text += ' the ' + rec_custom_month_number.toLowerCase()
					if ( rec_custom_month_day == '-1' ) {
						recurrence_text += ' day';
					}
					else {
						recurrence_text += ' ' + weekdays[rec_custom_month_day - 1];
					}
				}
				else {
					recurrence_text += ' day ' + rec_custom_month_number;
				}
			}

			if ( rec_custom_type == 'Yearly' ) {
				recurrence_text += ' in ';

				for ( var i = 0; i < rec_custom_year_month.length; i++ ) {
					if ( i == 0 ) {
						recurrence_text += months[rec_custom_year_month[i] - 1];
					}
					else if ( i != (rec_custom_year_month.length - 1) && i > 0 ) {
						recurrence_text += ', ' + months[rec_custom_year_month[i] - 1];
					}
					else {
						if ( rec_custom_year_month.length > 2 ) {
							recurrence_text += ',';
						}
						recurrence_text += ' and ' + months[rec_custom_year_month[i] - 1];
					}
				}

				if ( $( '[name="recurrence[custom-year-filter]"]' ).is( ':checked' ) ) {
					recurrence_text += ' on the ';
					if ( rec_custom_year_month_number == '-1' ) {
						recurrence_text += day_numbers[0];
					}
					else {
						recurrence_text += day_numbers[rec_custom_year_month_number];
					}
					recurrence_text += ' ' + weekdays[rec_custom_year_month_day - 1];
				}
			}

			if ( rec_end_type == 'After' ) {
				recurrence_text += ' for ' + rec_end_count + ' occurrences';
			}
			else if ( rec_end_type == 'On' && rec_end != '' ) {
				recurrence_text += ' until ' + rec_end;
			}
			recurrence_text += '.';
		}
		if ( rec_end_type == 'After' && rec_type != 'Custom' ) {
			recurrence_text += ' The final occurrence begins';
			if ( final_date_end.getTime() === final_date_start.getTime() ) {
				recurrence_text += ' and ends on ' + final_date_end.getFullYear() + '-' + ( final_date_end.getMonth() + 1 ) + '-' + final_date_end.getDate() + '.';
			}
			else {
				recurrence_text += ' on ' + final_date_start.getFullYear() + '-' + ( final_date_start.getMonth() + 1 ) + '-' + final_date_start.getDate() + ' and ends on ' + final_date_end.getFullYear() + '-' + ( final_date_end.getMonth() + 1 ) + '-' + final_date_end.getDate() + '.';
			}
		}

		if ( rec_type == 'None' ) {
			$( '.recurrence-pattern-description-row' ).hide();
		}

		// Update the text.
		$( '#recurrence-pattern-description' ).text( recurrence_text );
	}

	var recurrence_updated = function() {
		updateRecurrenceText();
		set_recurrence_end_min_date();
	};
	$( '#recurrence_end, #EventStartDate, #EventEndDate' ).datepicker( 'option', 'onClose', recurrence_updated );

	$( '.recurrence-row, .custom-recurrence-row' ).on( 'change', function( event ) {
		if ( !$( '.recurrence-pattern-description-row' ).is( ':visible' ) ) {
			$( '.recurrence-pattern-description-row' ).show();
		}
		updateRecurrenceText();
	} );

	var set_recurrence_end_min_date = function() {
		var start = $( '#EventStartDate' ).val();
		if ( start != '' ) {
			$( '#recurrence_end' ).attr( 'placeholder', start ).datepicker( 'option', 'minDate', start );
		}
	};
	set_recurrence_end_min_date();

	$( 'input[name="post[]"]' ).click( function( e ) {
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
		if ( $( "[name='action'] option:selected" ).val() == "trash" ) {
			if ( $( '.tribe-recurring-event-parent [name="post[]"]:checked').length > 0 && !confirm( TribeEventsProAdmin.recurrence.bulkDeleteConfirmationMessage ) ) {
				e.preventDefault();
			}
		}
	} );

} );
