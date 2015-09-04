var tribe_events_pro_admin = tribe_events_pro_admin || {};

tribe_events_pro_admin.recurrence = {
	recurrence_count: 0,
	exclusion_count: 0,
	event: {}
};

( function( $, my ) {
	'use strict';

	my.init = function() {
		this.init_recurrence();
	};

	/**
	 * initialize the recurrence behaviors and UI
	 */
	my.init_recurrence = function() {
		this.$recurrence_staging = $( '#tribe-recurrence-staging' );
		this.$recurrence_tmpl = $( '#tmpl-tribe-recurrence' );

		if ( ! this.$recurrence_tmpl.length ) {
			return;
		}

		this.recurrence_template = Handlebars.compile( this.$recurrence_tmpl.html() );
		this.$add_recurrence = $( '#tribe-add-recurrence' );
		this.$recurrence_rules = $( '.tribe-event-recurrence-rule' );

		this.$exclusion_staging = $( '#tribe-exclusion-staging' );
		this.$exclusion_tmpl = $( '#tmpl-tribe-exclusion' );
		this.exclusion_template = Handlebars.compile( this.$exclusion_tmpl.html() );
		this.$add_exclusion = $( '#tribe-add-exclusion' );
		this.$exclusion_rules = $( '.tribe-event-recurrence-exclusion' );

		this.recurrence_errors = {
			days: [],
			end: []
		};

		window.Handlebars.registerHelper( {
			tribe_recurrence_select: function( value, options ) {
				var $el = $( '<select />' ).html( options.fn( this ) );
				$el.find( '[value="' + value + '"]' ).attr( 'selected', 'selected' );
				return $el.html();
			},
			tribe_checked_if_is: function( value, goal ) {
				return value === goal ? 'checked' : '';
			},
			tribe_checked_if_is_not: function( value, goal ) {
				return value !== goal ? 'checked' : '';
			},
			tribe_checked_if_in: function( value, collection ) {
				return -1 !== $.inArray( value, collection ) ? 'checked' : '';
			}
		} );

		this.populate_recurrence( tribe_events_pro_recurrence_data );

		$( '.eventForm' )
			.on( 'submit', '.wp-admin.events-cal #post', this.event.submit_validation )
			.on( 'change', '[data-field="type"]', this.event.recurrence_type_changed )
			.on( 'change', '[data-field="end-type"]', this.event.recurrence_end_type_changed )
			.on( 'change', '[data-field="custom-type"]', this.event.recurrence_custom_type_changed )
			.on( 'change', '[data-field="custom-month-number"]', this.event.recurrence_custom_month_changed )
			.on( 'change', '.recurrence_end_count', this.event.recurrence_end_count_changed )
			.on( 'change', '.recurrence-row, .custom-recurrence-row', this.event.recurrence_row_changed )
			.on( 'change', '.tribe-custom-same-time input', this.event.same_time_changed )
			.on( 'change', '#EventStartDate, #EventEndDate, select[name="EventStartHour"], select[name="EventStartMinute"], select[name="EventStartMeridian"], select[name="EventEndHour"], select[name="EventEndMinute"], select[name="EventEndMeridian"]', this.event.datepicker_updated )
			.on( 'click', '#tribe-add-recurrence', this.event.add_recurrence )
			.on( 'click', '#tribe-add-exclusion', this.event.add_exclusion )
			.on( 'click', '.tribe-event-recurrence .tribe-handle', this.event.toggle_rule );

		// If recurrence changes on a recurring event, then show warning
		if ( $( 'input[data-field="is_recurring"][value="true"]' ).length ) {
			$( '.eventForm' ).on( 'change', '.recurrence-row input, .custom-recurrence-row input, .recurrence-row select, .custom-recurrence-row select', this.event.recurrence_changed );
			$( '.eventForm' ).on( 'recurrenceEndChanged', '[data-field="end"]', this.event.recurrence_changed );
		}

		$( '[data-field="end"]' ).datepicker( 'option', 'onSelect', this.event.datepicker_end_date_changed );
		$( '.recurrence_end, #EventStartDate, #EventEndDate' ).datepicker( 'option', 'onClose', this.event.datepicker_updated );

		this.set_recurrence_end_min_date();
	};

	/**
	 * adds a recurrence rule to the list of available rules
	 */
	my.add_recurrence = function( data ) {
		if ( 'undefined' !== typeof data && 'undefined' !== typeof data.end && data.end ) {
			var date_format = tribe_datepicker_opts.dateFormat.toUpperCase().replace( 'YY', 'YYYY' );
			data.end = moment( data.end ).format( date_format );
		}

		this.$recurrence_staging.append( this.recurrence_template( data ) );

		var $rule = this.$recurrence_staging.find( '.tribe-event-recurrence' );

		// replace all of the recurrence[] instances with recurrence[x] where x is a number
		$rule.find( '[name*="recurrence[rules][]"]' ).each( function() {
			var $field = $( this );
			$field.attr( 'name', $field.attr( 'name' ).replace( /recurrence\[rules\]\[\]/, 'recurrence[rules][' + my.recurrence_count + ']' ) );
		} );

		if ( ! data ) {
			$rule.find( '.tribe-same-time-checkbox' ).prop( 'checked', true );
		}

		$rule.find( '.tribe-datepicker' ).datepicker( tribe_datepicker_opts );
		$rule.insertBefore( this.$recurrence_staging );

		this.set_recurrence_data_attributes( $rule );
		this.adjust_rule_helper_text( $rule );
		this.update_rule_recurrence_text( $rule );

		// re-initialize recurrence rules
		this.$recurrence_rules = this.$recurrence_rules.add( $rule );
		this.recurrence_count++;

		this.check_for_useful_rule();
	};

	/**
	 * checks for a useful rule and adds a class to the containing eventtable
	 */
	my.check_for_useful_rule = function() {
		var $rule = this.$recurrence_rules.filter( ':first' );
		var rule_set = false;

		this.$recurrence_rules.find( '[data-field="type"] option:selected' ).each( function() {
			var $el = $( this );
			if ( 'None' !== $el.val() ) {
				rule_set = true;
			}
		} );

		if ( rule_set ) {
			$rule.closest( 'table' ).addClass( 'tribe-has-recurrence-rule' );
		} else {
			$rule.closest( 'table' ).removeClass( 'tribe-has-recurrence-rule' );
		}
	};

	/**
	 * adds an exclusion rule to the list of available rules
	 */
	my.add_exclusion = function( data ) {
		this.$exclusion_staging.append( this.exclusion_template( data ) );

		var $rule = this.$exclusion_staging.find( '.tribe-event-recurrence' );

		// replace all of the recurrence[] instances with recurrence[x] where x is a number
		$rule.find( '[name*="recurrence[exclusions][]"]' ).each( function() {
			var $field = $( this );
			$field.attr( 'name', $field.attr( 'name' ).replace( /recurrence\[exclusions\]\[\]/, 'recurrence[exclusions][' + my.exclusion_count + ']' ) );
		} );

		$rule.find( '.tribe-datepicker' ).datepicker( tribe_datepicker_opts );
		$rule.insertBefore( this.$exclusion_staging );

		this.set_recurrence_data_attributes( $rule );
		this.adjust_rule_helper_text( $rule );

		// re-initialize recurrence rules
		this.$exclusion_rules = this.$exclusion_rules.add( $rule );
		this.exclusion_count++;
	};

	/**
	 * populate recurrence UI based on recurrence data
	 */
	my.populate_recurrence = function( data ) {
		var i = 0;

		if ( 'undefined' === typeof data.rules || ! data.rules.length ) {
			this.add_recurrence();
			return;
		}

		for ( i in data.rules ) {
			this.add_recurrence( data.rules[ i ] );
		}//end for

		if ( 'undefined' !== typeof data.exclusions && data.exclusions.length ) {
			for ( i in data.exclusions ) {
				this.add_exclusion( data.exclusions[ i ] );
			}//end for
		}
	};

	/**
	 * Adjust the Custom frequency helper text
	 */
	my.adjust_rule_helper_text = function( $rule ) {
		var $custom_type_option = $rule.find( '[data-field="custom-type"] option:selected' );
		$rule.find( '.recurrence-interval-type' ).text( $custom_type_option.data( 'plural' ) );
		$rule.find( '[data-field="custom-type-text"]' ).val( $custom_type_option.data( 'plural' ) );
	};

	/**
	 * checks the current state of fields and sets appropraite data attributes for them
	 * on the recurrence rule
	 */
	my.set_recurrence_data_attributes = function( $rules ) {
		var $rules_to_set = $rules || this.$recurrence_rules;

		$rules_to_set.each( function() {
			var $rule = $( this );
			var $field = null;
			var fields = [
				'is_recurring',
				'type',
				'end-type',
				'custom-type',
				'custom-month-number'
			];

			for ( var i in fields ) {
				$field = $rule.find( '[data-field="' + fields[ i ] + '"]' );

				if ( 'custom-month-number' === fields[ i ] ) {
					$rule.attr( 'data-recurrence-' + fields[ i ], isNaN( $field.val() ) ? 'string' : 'numeric' );
				} else if ( 'custom-month-number' === fields[ i ] ) {
					$rule.attr( 'data-recurrence-' + fields[ i ], $field.is( ':checked' ) ? 'yes' : 'no' );
				} else {
					$rule.attr( 'data-recurrence-' + fields[ i ], $field.val() );
				}
			}

			var $custom_type = $rule.find( '[data-field="custom-type"]' );
			var type = null;

			switch ( $custom_type.val() ) {
				case 'Weekly':
					type = 'week';
					break;
				case 'Monthly':
					type = 'month';
					break;
				case 'Yearly':
					type = 'year';
					break;
				case 'Daily':
				default:
					type = 'day';
					break;
			}

			var $same_time = $rule.find( '[data-field="custom-' + type + '-same-time"]' );
			$rule.attr( 'data-recurrence-same-time', $same_time.filter( ':checked' ).length ? 'yes' : 'no' );
		} );
	};

	/**
	 * Sets the min date for recurrence rules
	 */
	my.set_recurrence_end_min_date = function() {
		var start = $( '#EventStartDate' ).val();
		if ( '' === start ) {
			return;
		}

		$( '.recurrence_end' ).attr( 'placeholder', start ).datepicker( 'option', 'minDate', start );
	};

	/**
	 * returns whether or not the recurrence rules have valid recurrence days
	 */
	my.has_valid_recurrence_days = function() {
		var valid = true;

		this.$recurrence_rules.each( function( index ) {
			if ( ! my.has_valid_recurrence_days_for_rule( $( this ) ) ) {
				valid = false;
				my.recurrence_errors.days.push( index );
			}
		} );

		return valid;
	};

	/**
	 * returns whether or not a specific recurrence rule has valid recurrence days
	 */
	my.has_valid_recurrence_days_for_rule = function( $recurrence_rule ) {
		var $interval = $recurrence_rule.find( '[data-field="custom-interval"]' );
		var $recurrence_type = $recurrence_rule.find( '[data-field="type"] option:selected' );

		if ( $interval.val() !== parseInt( $interval.val(), 10 ) && 'Custom' === $recurrence_type.val() ) {
			return false;
		}

		return true;
	};

	/**
	 * returns whether or not the recurrence rules have valid recurrence ends
	 */
	my.has_valid_recurrence_ends = function() {
		var valid = true;

		this.$recurrence_rules.each( function( index ) {
			if ( ! my.has_valid_recurrence_end_for_rule( $( this ) ) ) {
				valid = false;
				my.recurrence_errors.end.push( index );
			}
		} );

		return valid;
	};

	/**
	 * returns whether or not a specific recurrence rule has valid recurrence days
	 */
	my.has_valid_recurrence_end_for_rule = function( $recurrence_rule ) {
		var $end = $recurrence_rule.find( '[data-field="end"]' );
		var $end_type = $recurrence_rule.find( '[data-field="end-type"]' );
		var $recurrence_type = $recurrence_rule.find( '[data-field="type"]' );

		if ( 'None' !== $recurrence_type.val() && 'On' === $end_type.val() ) {
			return $end.val() && ! $end.hasClass( 'placeholder' );
		}

		return true;
	};

	/**
	 * resets the post submission button state
	 */
	my.reset_submit_button = function() {
		$( '#publishing-action .button-primary-disabled' ).removeClass( 'button-primary-disabled' );
		$( '#publishing-action .spinner' ).hide();
	};

	/**
	 * validates the recurrence data
	 */
	my.validate_recurrence = function() {
		var valid = true;

		if ( ! this.has_valid_recurrence_days() ) {
			valid = false;

			alert( $( '.rec-days-error:first' ).text() );

			$( '.rec-days-error' ).each( function( index ) {
				if ( $.inArray( index, my.recurrence_errors.days ) ) {
					$( this ).show();
				}
			} );
		}

		if ( ! this.has_valid_recurrence_ends() ) {
			valid = false;

			alert( $( '.rec-end-error:first' ).text() );

			$( '.rec-end-error' ).each( function( index ) {
				if ( $.inArray( index, my.recurrence_errors.end ) ) {
					$( this ).show();
				}
			} );
		}

		return valid;
	};

	/**
	 * Toggles a recurrence rule open/closed
	 */
	my.toggle_rule = function( $rule, state ) {
		if ( 'undefined' !== state && state ) {
			if ( 'open' === state ) {
				$rule.addClass( 'tribe-open' );
			} else {
				$rule.removeClass( 'tribe-open' );
			}
		} else {
			$rule.toggleClass( 'tribe-open' );
		}
	};

	/**
	 * Updates recurrence text
	 */
	my.update_recurrence_text = function() {
		this.$recurrence_rules.each( function() {
			my.update_rule_recurrence_text( $( this ) );
		} );
	};

	my.update_rule_recurrence_text = function( $rule ) {
		var type = $rule.find( '[data-field="type"] option:selected' ).val();
		var end_type = $rule.find( '[data-field="end-type"] option:selected' ).val();
		var end = $rule.find( '[data-field="end"]' ).val();
		var end_count = $rule.find( '[data-field="end-count"]' ).val();
		var custom_type = $rule.find( '[data-field="custom-type"] option:selected' ).val();
		var same_time = $rule.find( '.tribe-same-time-checkbox:checked' ).length ? true : false;
		var interval = $rule.find( '[data-field="custom-interval"]' ).val();
		var year_filtered = $rule.find( '[data-field="custom-year-filter"]:checked' ).length ? true : false;

		if ( ! end ) {
			end = $rule.find( '[data-field="end"]' ).attr( 'placeholder' );
		}

		type = type.toLowerCase().replace( ' ', '-' );
		end_type = end_type.toLowerCase().replace( ' ', '-' );
		custom_type = custom_type.toLowerCase().replace( ' ', '-' );

		if ( 'none' === type ) {
			$rule.find( '.tribe-event-recurrence-description' ).html( '' );
		}

		var date_format = tribe_datepicker_opts.dateFormat.toUpperCase();
		date_format = date_format.replace( 'YY', 'YYYY' );

		var $event_form = $rule.closest( '.eventForm' );

		if ( $event_form.find( '[name="EventStartMeridian"]' ).length ) {
			date_format = date_format + ' hh:mm a';
		} else {
			date_format = date_format + ' HH:mm';
		}

		var $start_date = $( document.getElementById( 'EventStartDate' ) );
		var start_date = $start_date.val();
		var $selected_start_meridian = $event_form.find( '[name="EventStartMeridian"] option:selected' );

		start_date += ' ' + $event_form.find( '[name="EventStartHour"] option:selected' ).val() + ':' + $event_form.find( '[name="EventStartMinute"] option:selected' ).val();

		if ( $selected_start_meridian.length ) {
			start_date += ' ' + $selected_start_meridian.val().toUpperCase();
		}

		var $end_date = $( document.getElementById( 'EventEndDate' ) );
		var end_date = $end_date.val();
		var $selected_end_meridian = $event_form.find( '[name="EventEndMeridian"] option:selected' );

		end_date += ' ' + $event_form.find( '[name="EventEndHour"] option:selected' ).val() + ':' + $event_form.find( '[name="EventEndMinute"] option:selected' ).val();

		if ( $selected_end_meridian.length ) {
			end_date += ' ' + $selected_end_meridian.val().toUpperCase();
		}

		var start_moment = moment( start_date, date_format );
		var end_moment = moment( end_date, date_format );

		var num_days = end_moment.diff( start_moment, 'days' );

		// make sure we always round hours UP to when dealing with decimal lengths more than 2. Example: 4.333333 would become 4.34
		var num_hours = Math.ceil( ( end_moment.diff( start_moment, 'hours', true ) - ( num_days * 24 ) ) * 100 ) / 100;

		var new_start_time = $rule.find( '[data-field="custom-start-time-hour"] option:selected' ).val() + ':'+
			$rule.find( '[data-field="custom-start-time-minute"] option:selected' ).val() + ' ' +
			$rule.find( '[data-field="custom-start-time-meridian"] option:selected' ).val();
		var new_start = start_moment.format( 'YYYY-MM-DD' ) + ' ' + new_start_time;

		var new_end_time = $rule.find( '[data-field="custom-end-time-hour"] option:selected' ).val() + ':' +
			$rule.find( '[data-field="custom-end-time-minute"] option:selected' ).val() + ' ' +
			$rule.find( '[data-field="custom-end-time-meridian"] option:selected' ).val();
		var new_end = end_moment.format( 'YYYY-MM-DD' ) + ' ' + new_end_time;

		var new_start_moment = moment( new_start, date_format );
		var new_end_moment = moment( new_end, date_format );

		var new_num_days = new_end_moment.diff( new_start_moment, 'days' );

		// make sure we always round hours UP to when dealing with decimal lengths more than 2. Example: 4.333333 would become 4.34
		var new_num_hours = Math.ceil( ( new_end_moment.diff( new_start_moment, 'hours', true ) - ( num_days * 24 ) ) * 100 ) / 100;

		var weekdays = [];
		var months = [];
		var month_number = null;
		var month_day = null;
		var month_day_description = null;

		if ( 'weekly' === custom_type ) {
			$rule.find( '[data-field="custom-week-day"]:checked' ).each( function() {
				weekdays.push( tribe_events_pro_recurrence_strings.date.weekdays[ parseInt( $( this ).val(), 10 ) - 1 ] );
			} );

			if ( 0 === weekdays.length ) {
				weekdays = tribe_events_pro_recurrence_strings.date.day_placeholder;
			} else if ( 2 === weekdays.length ) {
				weekdays = weekdays.join( ' ' + tribe_events_pro_recurrence_strings.date.collection_joiner + ' ' );
			} else {
				weekdays = weekdays.join( ', ' );
				weekdays = weekdays.replace( /(.*),/, '$1, ' + tribe_events_pro_recurrence_strings.date.collection_joiner );
			}
		}

		if ( 'monthly' === custom_type ) {
			month_number = $rule.find( '[data-field="custom-month-number"] option:selected' ).val();
			month_day = $rule.find( '[data-field="custom-month-day"] option:selected' ).val();
		}

		if ( 'yearly' === custom_type ) {
			month_number = $rule.find( '[data-field="custom-year-month-number"] option:selected' ).val();
			month_day = $rule.find( '[data-field="custom-year-month-day"] option:selected' ).val();

			$rule.find( '[data-field="custom-year-month"]:checked' ).each( function() {
				months.push( tribe_events_pro_recurrence_strings.date.months[ parseInt( $( this ).val(), 10 ) - 1 ] );
			} );

			if ( 0 === months.length ) {
				months = tribe_events_pro_recurrence_strings.date.month_placeholder;
			} else if ( 2 === months.length ) {
				months = months.join( ' ' + tribe_events_pro_recurrence_strings.date.collection_joiner + ' ' );
			} else {
				months = months.join( ', ' );
				months = months.replace( /(.*),/, '$1, ' + tribe_events_pro_recurrence_strings.date.collection_joiner );
			}
		}

		var key = type;

		if ( 'custom' === type ) {
			key += '-' + custom_type + '-' + end_type + '-' + ( same_time ? 'same' : 'diff' ) + '-time';

			if ( 'monthly' === custom_type && ! isNaN( month_number ) ) {
				key += '-numeric';
			}

			if ( 'yearly' === custom_type && ! year_filtered ) {
				key += '-unfiltered';
			}
		} else {
			key += '-' + end_type;
		}

		if (
			'weekly' === custom_type
			&& 0 === weekdays.length
		) {
			key = 'every-week-on';
		} else if (
			'monthly' === custom_type
			&& ! month_number
			&& ! month_day
		) {
			key = 'every-month-on';
		} else if (
			'yearly' === custom_type
			&& ! month_number
			&& ! month_day
		) {
			key = 'every-year-on';
		}

		var text = tribe_events_pro_recurrence_strings.recurrence[ key ];

		// if a month_number and month_day is defined, build the month_day_description
		if ( month_number && month_day ) {
			// if the month number IS a number, then set the 'day' to blank so it doesn't display
			if ( isNaN( month_number ) || ( 'yearly' === custom_type && year_filtered ) ) {
				if ( 'yearly' === custom_type ) {
					switch ( month_number ) {
						case '1': month_number = 'first'; break;
						case '2': month_number = 'second'; break;
						case '3': month_number = 'third'; break;
						case '4': month_number = 'fourth'; break;
						case '5': month_number = 'fifth'; break;
						case '-1': month_number = 'last'; break;
					}
				}

				month_day_description = tribe_events_pro_recurrence_strings.date[ month_number.toLowerCase() + '_x' ];

				if ( ! isNaN( month_day ) && month_day > 0 ) {
					month_day_description = month_day_description.replace( '%1$s', tribe_events_pro_recurrence_strings.date.weekdays[ parseInt( month_day, 10 ) - 1 ] );
				} else if ( ! isNaN( month_day ) ) {
					month_day_description = month_day_description.replace( '%1$s', tribe_events_pro_recurrence_strings.date.day );
				} else {
					month_day_description = month_day_description.replace( '%1$s', tribe_events_pro_recurrence_strings.date.day_placeholder );
				}
			} else if ( 'yearly' === custom_type && ! year_filtered ) {
				month_day_description = start_moment.format( 'D' );
			} else {
				month_day_description = month_number;
			}
		} else {
			month_day_description = tribe_events_pro_recurrence_strings.date.day_placeholder;
		}

		switch ( key ) {
			case 'every-day-on':
			case 'every-week-on':
			case 'every-month-on':
			case 'every-year-on':
			case 'every-day-never':
			case 'every-week-never':
			case 'every-month-never':
			case 'every-year-never':
				text = text.replace( '%1$s', num_days );
				text = text.replace( '%2$s', num_hours );
				text = text.replace( '%3$s', end );
				break;
			case 'every-day-after':
			case 'every-week-after':
			case 'every-month-after':
			case 'every-year-after':
				text = text.replace( '%1$s', num_days );
				text = text.replace( '%2$s', num_hours );
				text = text.replace( '%3$s', end_count );
				break;
			case 'custom-daily-on-same-time':
			case 'custom-daily-never-same-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', num_days );
				text = text.replace( '%3$s', num_hours );
				text = text.replace( '%4$s', end );
				break;
			case 'custom-daily-after-same-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', num_days );
				text = text.replace( '%3$s', num_hours );
				text = text.replace( '%4$s', end_count );
				break;
			case 'custom-daily-on-diff-time':
			case 'custom-daily-never-diff-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', new_start_time );
				text = text.replace( '%3$s', new_num_days );
				text = text.replace( '%4$s', new_num_hours );
				text = text.replace( '%5$s', end );
				break;
			case 'custom-daily-after-diff-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', new_start_time );
				text = text.replace( '%3$s', new_num_days );
				text = text.replace( '%4$s', new_num_hours );
				text = text.replace( '%5$s', end_count );
				break;
			case 'custom-weekly-on-same-time':
			case 'custom-weekly-never-same-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', weekdays );
				text = text.replace( '%3$s', num_days );
				text = text.replace( '%4$s', num_hours );
				text = text.replace( '%5$s', end );
				break;
			case 'custom-weekly-after-same-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', weekdays );
				text = text.replace( '%3$s', num_days );
				text = text.replace( '%4$s', num_hours );
				text = text.replace( '%5$s', end_count );
				break;
			case 'custom-weekly-on-diff-time':
			case 'custom-weekly-never-diff-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', weekdays );
				text = text.replace( '%3$s', new_start_time );
				text = text.replace( '%4$s', new_num_days );
				text = text.replace( '%5$s', new_num_hours );
				text = text.replace( '%6$s', end );
				break;
			case 'custom-weekly-after-diff-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', weekdays );
				text = text.replace( '%3$s', new_start_time );
				text = text.replace( '%4$s', new_num_days );
				text = text.replace( '%5$s', new_num_hours );
				text = text.replace( '%6$s', end_count );
				break;
			case 'custom-monthly-on-same-time-numeric':
			case 'custom-monthly-never-same-time-numeric':
			case 'custom-monthly-on-same-time':
			case 'custom-monthly-never-same-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', month_day_description );
				text = text.replace( '%3$s', num_days );
				text = text.replace( '%4$s', num_hours );
				text = text.replace( '%5$s', end );
				break;
			case 'custom-monthly-after-same-time-numeric':
			case 'custom-monthly-after-same-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', month_day_description );
				text = text.replace( '%3$s', num_days );
				text = text.replace( '%4$s', num_hours );
				text = text.replace( '%5$s', end_count );
				break;
			case 'custom-monthly-on-diff-time-numeric':
			case 'custom-monthly-never-diff-time-numeric':
			case 'custom-monthly-on-diff-time':
			case 'custom-monthly-never-diff-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', month_day_description );
				text = text.replace( '%3$s', new_start_time );
				text = text.replace( '%4$s', new_num_days );
				text = text.replace( '%5$s', new_num_hours );
				text = text.replace( '%6$s', end );
				break;
			case 'custom-monthly-after-diff-time-numeric':
			case 'custom-monthly-after-diff-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', month_day_description );
				text = text.replace( '%3$s', new_start_time );
				text = text.replace( '%4$s', new_num_days );
				text = text.replace( '%5$s', new_num_hours );
				text = text.replace( '%6$s', end_count );
				break;
			case 'custom-yearly-on-same-time-unfiltered':
			case 'custom-yearly-never-same-time-unfiltered':
			case 'custom-yearly-on-same-time':
			case 'custom-yearly-never-same-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', months );
				text = text.replace( '%3$s', month_day_description );
				text = text.replace( '%4$s', num_days );
				text = text.replace( '%5$s', num_hours );
				text = text.replace( '%6$s', end );
				break;
			case 'custom-yearly-after-same-time-unfiltered':
			case 'custom-yearly-after-same-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', months );
				text = text.replace( '%3$s', month_day_description );
				text = text.replace( '%4$s', num_days );
				text = text.replace( '%5$s', num_hours );
				text = text.replace( '%6$s', end_count );
				break;
			case 'custom-yearly-on-diff-time-unfiltered':
			case 'custom-yearly-never-diff-time-unfiltered':
			case 'custom-yearly-on-diff-time':
			case 'custom-yearly-never-diff-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', months );
				text = text.replace( '%3$s', month_day_description );
				text = text.replace( '%4$s', new_start_time );
				text = text.replace( '%5$s', new_num_days );
				text = text.replace( '%6$s', new_num_hours );
				text = text.replace( '%7$s', end );
				break;
			case 'custom-yearly-after-diff-time-unfiltered':
			case 'custom-yearly-after-diff-time':
				text = text.replace( '%1$s', interval );
				text = text.replace( '%2$s', months );
				text = text.replace( '%3$s', month_day_description );
				text = text.replace( '%4$s', new_start_time );
				text = text.replace( '%5$s', new_num_days );
				text = text.replace( '%6$s', new_num_hours );
				text = text.replace( '%7$s', end_count );
				break;
		}

		$rule.find( '.tribe-event-recurrence-description' ).html( text );
	};

	my.event.add_recurrence = function( e ) {
		e.preventDefault();
		my.add_recurrence();
	};

	my.event.add_exclusion = function( e ) {
		e.preventDefault();
		my.add_exclusion();
	};

	/**
	 * Handles when a recurrence type changes
	 */
	my.event.recurrence_type_changed = function() {
		var $el = $( this );
		var $rule = $el.closest( '.tribe-event-recurrence' );
		$rule.addClass( 'tribe-open' );

		var val = $el.find( 'option:selected' ).val();

		if ( 'Custom' === val ) {
			$rule.find( '[data-field="custom-type"]' ).change();
		}

		var $count_text = $rule.find( '.occurence-count-text' );
		var end_count = parseInt( $rule.find( '.recurrence_end_count' ).val(), 10 );
		var type_text = $el.data( 'plural' );

		if ( 1 === end_count ) {
			type_text = $el.data( 'single' );
		}

		$count_text.text( type_text );
		$rule.find( '[data-field="occurrence-count-text"]' ).val( $count_text.text() );

		my.set_recurrence_data_attributes( $rule );
		my.check_for_useful_rule();
	};

	/**
	 * Handles when a recurrence end type changes
	 */
	my.event.recurrence_end_type_changed = function() {
		var $el = $( this );
		var $rule = $el.closest( '.tribe-event-recurrence' );

		my.set_recurrence_data_attributes( $rule );
	};

	/**
	 * Handles when a recurrence custom type changes
	 */
	my.event.recurrence_custom_type_changed = function() {
		var $el = $( this );
		var $rule = $el.closest( '.tribe-event-recurrence' );

		if ( $rule.is( '.tribe-event-recurrence-exclusion' ) ) {
			$rule.addClass( 'tribe-open' );
		}

		my.adjust_rule_helper_text( $rule );
		my.set_recurrence_data_attributes( $rule );
	};

	/**
	 * When a recurrence row changes, make sure the recurrence changed row is displayed
	 */
	my.event.recurrence_changed = function() {
		var $el = $( this );
		var $rule = $el.closest( '.tribe-event-recurrence' );
		$rule.attr( 'data-recurrence-changed', 'yes' );
		my.toggle_rule( $rule, 'open' );
	};

	/**
	 * Handles when the recurrence end count changes
	 */
	my.event.recurrence_end_count_changed = function() {
		var $el = $( this );
		var $rule = $el.closest( '.tribe-event-recurrence' );

		$rule.find( '[data-field="type"]' ).change();
	};

	/**
	 * Handles the changing of custom month numbers
	 */
	my.event.recurrence_custom_month_changed = function() {
		var $el = $( this );
		var $rule = $el.closest( '.tribe-event-recurrence' );

		my.set_recurrence_data_attributes( $rule );
	};

	/**
	 * validates the recurrence data before submission occurs
	 */
	my.event.submit_validation = function() {
		if ( ! tribe_events_pro_admin.validate_recurrence() ) {
			e.preventDefault();
			tribe_events_pro_admin.reset_submit_button();
		}
	};

	my.event.datepicker_updated = function() {
		tribe_events_pro_admin.recurrence.update_recurrence_text();
		tribe_events_pro_admin.recurrence.set_recurrence_end_min_date();
	};

	my.event.datepicker_end_date_changed = function() {
		$( this ).removeClass( 'placeholder' );
		$( this ).trigger( 'recurrenceEndChanged' );
	};

	my.event.recurrence_row_changed = function() {
		var $row = $( '.recurrence-pattern-description-row' );
		if ( ! $row.is( ':visible' ) ) {
			$row.show();
		}
		tribe_events_pro_admin.recurrence.update_recurrence_text();
	};

	/**
	 * handles when the "Same Time" checkbox is toggled
	 */
	my.event.same_time_changed = function() {
		if ( 'undefined' !== typeof my.updating_same_time_checked && true === my.updating_same_time_checked ) {
			return;
		}

		var $el = $( this );
		var $rule = $el.closest( '.tribe-event-recurrence' );

		my.updating_same_time_checked = true;

		if ( $el.filter( ':checked' ).length ) {
			$rule.find( '.tribe-custom-same-time input' ).prop( 'checked', true );
		} else {
			$rule.find( '.tribe-custom-same-time input' ).prop( 'checked', false );
		}

		my.updating_same_time_checked = false;

		my.set_recurrence_data_attributes( $rule );
	};

	/**
	 * Toggles a rule open/closed
	 */
	my.event.toggle_rule = function() {
		var $el = $( this );

		my.toggle_rule( $el.closest( '.tribe-event-recurrence' ) );
	};

	$( function() {
		my.init();
	} );
} )( jQuery, tribe_events_pro_admin.recurrence );
