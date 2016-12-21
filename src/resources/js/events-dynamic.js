/**
 * Event Date and Time Dynamic Helper Text
 *
 * @var object tribe_dynamic_helper_text
 */
var tribe_dynamic_helper_text = tribe_dynamic_helper_text || {};

(function ( $, obj ) {
	'use strict';

	obj.field_class = '.event-dynamic-helper-text';
	obj.date_fmt_settings = {
		dateSettings: {
			days: tribe_dynamic_help_text.days,
			daysShort: tribe_dynamic_help_text.daysShort,
			months: tribe_dynamic_help_text.months,
			monthsShort: tribe_dynamic_help_text.monthsShort,
		}
	};
	obj.date_fmt = new DateFormatter( obj.date_fmt_settings );

	obj.text = JSON.parse( tribe_dynamic_help_text.msgs );

	//Date Formats
	obj.date_with_year = tribe_dynamic_help_text.date_with_year;
	obj.date_no_year = tribe_dynamic_help_text.date_no_year;
	obj.datepicker_format = tribe_dynamic_help_text.datepicker_format;

	//Setup object variables
	obj.dynamic_text, obj.start_date, obj.start_time, obj.end_date, obj.end_time, obj.all_day = '';

	/**
	 * Setup Dynamic Text on Load
	 */
	obj.init = function () {

		//setup text and display
		obj.setup_and_display_text();

		//detect event date & time changes
		obj.event_date_change();

	};

	/**
	 * Wrapper Method to call methods to update and display text
	 */
	obj.setup_and_display_text = function () {

		//get current field values
		obj.update();

		//determine message to use based on values
		obj.msg_logic();

		//parse the message and insert into dymanic text div
		obj.parse_and_display_text();

	};

	/**
	 * Get Event Date and Time Values
	 */
	obj.update = function () {

		obj.start_date = $( '#EventStartDate' ).val();
		obj.start_time = $( '#EventStartTime' ).val();
		obj.end_date = $( '#EventEndDate' ).val();
		obj.end_time = $( '#EventEndTime' ).val();
		obj.all_day = $( '#allDayCheckbox' ).prop( 'checked' ) ? true : '';

	};

	/**
	 * Determine Message to Use based on Date and Time
	 */
	obj.msg_logic = function () {

		if ( obj.start_date == obj.end_date && !obj.all_day && obj.start_time != obj.end_time ) {
			//single date, different start and end time
			obj.dynamic_text = obj.text[0];

		} else if ( obj.start_date == obj.end_date && !obj.all_day && obj.start_time == obj.end_time ) {
			//single date, same start and end time
			obj.dynamic_text = obj.text[1];

		} else if ( obj.start_date == obj.end_date && obj.all_day ) {
			//single date, all day
			obj.dynamic_text = obj.text[2];

		} else if ( obj.start_date != obj.end_date && !obj.all_day && obj.start_time != obj.end_time ) {
			//different date, different start and end time
			obj.dynamic_text = obj.text[3];

		} else if ( obj.start_date != obj.end_date && !obj.all_day && obj.start_time == obj.end_time ) {
			//different date, same start and end time
			obj.dynamic_text = obj.text[4];

		} else if ( obj.start_date != obj.end_date && obj.all_day ) {
			//different date, all day
			obj.dynamic_text = obj.text[5];
		}


	};

	/**
	 * Parse the Message and Insert into Div
	 */
	obj.parse_and_display_text = function () {

		obj.dynamic_text = obj.dynamic_text.replace( "%%starttime%%", obj.start_time );
		obj.dynamic_text = obj.dynamic_text.replace( "%%endtime%%", obj.end_time );
		obj.dynamic_text = obj.dynamic_text.replace( "%%startdatewithyear%%", obj.date_formatter( obj.start_date, obj.datepicker_format, obj.date_with_year ) );
		obj.dynamic_text = obj.dynamic_text.replace( "%%enddatewithyear%%", obj.date_formatter( obj.end_date, obj.datepicker_format, obj.date_with_year ) );
		obj.dynamic_text = obj.dynamic_text.replace( "%%startdatenoyear%%", obj.date_formatter( obj.start_date, obj.datepicker_format, obj.date_no_year ) );
		obj.dynamic_text = obj.dynamic_text.replace( "%%enddatenoyear%%", obj.date_formatter( obj.end_date, obj.datepicker_format, obj.date_no_year ) );

		$( obj.field_class ).html( obj.dynamic_text );

	};

	/**
	 * Format Date using DateFormatter library enabling PHP date-time formats
	 *
	 * @param date
	 * @param datepicker
	 * @param dateformat
	 * @returns {*}
	 */
	obj.date_formatter = function ( date, datepicker, dateformat ) {

		return obj.date_fmt.formatDate( obj.date_fmt.parseDate( date, datepicker ), dateformat );

	};

	/**
	 * Detect Changes to Event Start and End Dates, and All Day Checkbox
	 */
	obj.event_date_change = function () {

		$( '#EventStartDate, #EventStartTime, #EventEndDate, #EventEndTime, #allDayCheckbox' ).on( 'change', function () {

			obj.setup_and_display_text();

		} );

	};

	/**
	 * Init Dynamic Help if on Single Event Editor
	 */
	$( function () {
		if ( $( '#eventDetails, #event_datepickers' ).hasClass( 'eventForm' ) ) {
			obj.init();
		}
	} );

})( jQuery, tribe_dynamic_helper_text );