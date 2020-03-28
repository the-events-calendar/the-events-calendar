/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 4.9.5
 *
 * @type {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Datepicker Object in the Global Tribe variable
 *
 * @since 4.9.5
 *
 * @type {PlainObject}
 */
tribe.events.views.datepicker = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since 4.9.5
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.events.views.datepicker
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	var $document = $( document );

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since 4.9.5
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		datepickerFormClass: '.tribe-events-c-top-bar__datepicker-form',
		datepickerContainer: '[data-js="tribe-events-top-bar-datepicker-container"]',
		datepickerDaysBody: '.datepicker-days tbody',
		input: '[data-js="tribe-events-top-bar-date"]',
		button: '[data-js="tribe-events-top-bar-datepicker-button"]',
		buttonOpenClass: '.tribe-events-c-top-bar__datepicker-button--open',
		dateInput: '[name="tribe-events-views[tribe-bar-date]"]',
	};

	/**
	 * Object of state
	 *
	 * @since 4.9.5
	 *
	 * @type {PlainObject}
	 */
	obj.state = {
		initialized: false,
	};

	/**
	 * Object of datepicker options
	 *
	 * @since 4.9.10
	 *
	 * @type {PlainObject}
	 */
	obj.options = {
		container: null,
		daysOfWeekDisabled: [],
		maxViewMode: 'decade',
		minViewMode: 'month',
		orientation: 'bottom left',
		showOnFocus: false,
		templates: {
			leftArrow: '',
			rightArrow: '',
		},
	};

	/**
	 * Object of key codes
	 *
	 * @since 5.0.0
	 *
	 * @type {PlainObject}
	 */
	obj.keyCode = {
		ENTER: 13,
	};

	/**
	 * Date object representing today
	 *
	 * @since 4.9.13
	 *
	 * @type {Date|null}
	 */
	obj.today = null;

	/**
	 * Object of date format map.
	 * Date formats are mapped from PHP to Bootstrap Datepicker format.
	 *
	 * @since 4.9.11
	 *
	 * @type {PlainObject}
	 *
	 * @see https://bootstrap-datepicker.readthedocs.io/en/latest/options.html#format
	 */
	obj.dateFormatMap = {
		d: 'dd',
		j: 'd',
		m: 'mm',
		n: 'm',
		Y: 'yyyy',
	};

	/**
	 * Mutation observer to watch for mutations
	 *
	 * @since 4.9.10
	 *
	 * @type {MutationObserver}
	 */
	obj.observer = null;

	/**
	 * Pads number with extra 0 if needed to make it double digit
	 *
	 * @since 4.9.5
	 *
	 * @param {integer} number number to pad with extra 0
	 *
	 * @return {string} string representation of padded number
	 */
	obj.padNumber = function( number ) {
		var numStr = number + '';
		var padding = numStr.length > 1 ? '' : '0';
		return padding + numStr;
	};

	/**
	 * Performs an AJAX request using manager.js request method
	 *
	 * @since 4.9.5
	 *
	 * @param {object} viewData object of view data
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.request = function( viewData, $container ) {
		var data = {
			view_data: viewData,
			_wpnonce: $container.data( 'view-rest-nonce' ),
		};

		tribe.events.views.manager.request( data, $container );
	};

	/**
	 * Create the Date input that will be preprended on the form created.
	 *
	 * @since 4.9.11
	 *
	 * @param {string} value string representation of the date value
	 *
	 * @return {jQuery}
	 */
	obj.createDateInputObj = function( value ) {
		var $input = $( '<input>' );
		$input.attr( {
			type: 'hidden',
			name: 'tribe-events-views[tribe-bar-date]',
			value: value,
		} );

		return $input;
	};

	/**
	 * Submits request after date change from datepicker based on live refresh setting.
	 *
	 * @since 4.9.11
	 *
	 * @param {jQuery} $container jQuery object of view container
	 * @param {string} value string representation of the date value
	 *
	 * @return {void}
	 */
	obj.submitRequest = function( $container, value ) {
		var viewData = {
			[ 'tribe-bar-date' ]: value,
		};

		obj.request( viewData, $container );
	};

	/**
	 * Handle datepicker changeDate event
	 *
	 * @since 4.9.5
	 *
	 * @param {Event} event event object for 'changeDate' event
	 *
	 * @return {void}
	 */
	obj.handleChangeDate = function( event ) {
		var $container = event.data.container;
		var date = event.date.getDate();
		var month = event.date.getMonth() + 1;
		var year = event.date.getFullYear();

		var paddedDate = obj.padNumber( date );
		var paddedMonth = obj.padNumber( month );

		var dateValue = [ year, paddedMonth, paddedDate ].join( '-' );

		obj.submitRequest( $container, dateValue );
	};

	/**
	 * Handle datepicker changeMonth event
	 *
	 * @since 4.9.5
	 *
	 * @param {Event} event event object for 'changeMonth' event
	 *
	 * @return {void}
	 */
	obj.handleChangeMonth = function( event ) {
		var $container = event.data.container;
		var month, year;

		if ( event.date ) {
			month = event.date.getMonth() + 1;
			year = event.date.getFullYear();
		} else {
			var date = $container
				.find( obj.selectors.input )
				.bootstrapDatepicker( 'getDate' );
			month = date.getMonth() + 1;
			year = date.getFullYear();
		}

		var paddedMonth = obj.padNumber( month );

		var dateValue = [ year, paddedMonth ].join( '-' );

		obj.submitRequest( $container, dateValue );
	};

	/**
	 * Handle datepicker keydown event
	 *
	 * @since 5.0.0
	 *
	 * @param {Event} event event object for 'keydown' event
	 *
	 * @return {void}
	 */
	obj.handleKeyDown = function(event) {
		if ( event.keyCode !== obj.keyCode.ENTER ) {
			return;
		}

		event.data.input.bootstrapDatepicker().trigger( 'changeMonth' );
	}

	/**
	 * Handle datepicker show event
	 *
	 * @since 4.9.13
	 *
	 * @param {Event} event event object for 'show' event
	 *
	 * @return {void}
	 */
	obj.handleShow = function( event ) {
		event.data.datepickerButton.addClass( obj.selectors.buttonOpenClass.className() );
	};

	/**
	 * Handle datepicker hide event
	 *
	 * @since 4.9.8
	 *
	 * @param {Event} event event object for 'hide' event
	 *
	 * @return {void}
	 */
	obj.handleHide = function( event ) {
		var $datepickerButton = event.data.datepickerButton
		var state = $datepickerButton.data( 'tribeEventsState' );

		event.data.observer.disconnect();

		if ( state.isTarget ) {
			event.data.input.bootstrapDatepicker( 'show' );
			return;
		}

		$datepickerButton
			.removeClass( obj.selectors.buttonOpenClass.className() )
			.focus();
	};

	/**
	 * Handle datepicker button mousedown
	 *
	 * @since 4.9.8
	 *
	 * @param {Event} event event object for 'mousedown' event
	 *
	 * @return {void}
	 */
	obj.handleMousedown = function( event ) {
		var $datepickerButton = event.data.target;
		var state = $datepickerButton.data( 'tribeEventsState' );

		if ( 'touchstart' === event.type ) {
			var method = $datepickerButton.hasClass( obj.selectors.buttonOpenClass.className() ) ? 'hide' : 'show';
			var tapHide = 'hide' === method;
			state.isTarget = false;

			$datepickerButton
				.data( 'tribeTapHide', tapHide )
				.data( 'tribeEventsState', state )
				.off( 'mousedown', obj.handleMousedown );

			return;
		}

		state.isTarget = true;
		$datepickerButton.data( 'tribeEventsState', state );
	};

	/**
	 * Handle datepicker button click
	 *
	 * @since 4.9.8
	 *
	 * @param {Event} event event object for 'click' event
	 *
	 * @return {void}
	 */
	obj.handleClick = function( event ) {
		var $input = event.data.input;
		var $datepickerButton = event.data.target;
		var state = $datepickerButton.data( 'tribeEventsState' );
		var method = $datepickerButton.hasClass( obj.selectors.buttonOpenClass.className() ) ? 'hide' : 'show';
		var tapHide = $datepickerButton.data( 'tribeTapHide' );

		if ( tapHide ) {
			return;
		}

		state.isTarget = false;

		$datepickerButton.data( 'tribeEventsState', state );
		$input.bootstrapDatepicker( method );

		if ( 'show' === method ) {
			$input.focus();
		}
	};

	/**
	 * Curry function to handle mutations
	 * Used to pass in `data`
	 *
	 * @since 4.9.7
	 *
	 * @param {PlainObject} data data object to be passed for use in handler
	 *
	 * @return {function}
	 */
	obj.handleMutation = function( data ) {
		var $container = data.container;

		/**
		 * Handle mutations from mutation observer
		 *
		 * @since 4.9.7
		 *
		 * @param {array} mutationsList list of mutations that have occurred
		 * @param {MutationObserver} observer mutation observer instance
		 *
		 * @return {void}
		 */
		return function( mutationsList, observer ) {
			for ( var mutation of mutationsList ) {
				// if datepicker switches months via prev/next arrows or by selecting a month on month picker
				if (
					'childList' === mutation.type &&
					$container.find( obj.selectors.datepickerDaysBody ).is( mutation.target ) &&
					mutation.addedNodes.length
				) {
					$container.trigger( 'handleMutationMonthChange.tribeEvents' );
				}
			}
		};
	};

	/**
	 * Set today to date object representing today
	 *
	 * @since 4.9.13
	 *
	 * @param {string} today string representation of today's date according to website time
	 *
	 * @return {void}
	 */
	obj.setToday = function( today ) {
		var date = today;
		if ( today.indexOf( ' ' ) >= 0 ) {
			date = today.split( ' ' )[0];
		}

		obj.today = new Date( date );
	};

	/**
	 * Determine whether or not date is the same as today.
	 * The function uses UTC values to maintain consistency with website date.
	 * Function will return false if proper unit is not provided.
	 *
	 * @since 4.9.13
	 *
	 * @param {Date}   date Date object representing the date being compared
	 * @param {string} unit Unit to compare dates to
	 *
	 * @return {bool}
	 */
	obj.isSameAsToday = function( date, unit ) {
		switch ( unit ) {
			case 'year':
				return date.getFullYear() === obj.today.getUTCFullYear();
			case 'month':
				return obj.isSameAsToday( date, 'year' ) && date.getMonth() === obj.today.getUTCMonth();
			case 'day':
				return obj.isSameAsToday( date, 'month' ) && date.getDate() === obj.today.getUTCDate();
			default:
				return false;
		}
	}

	/**
	 * Determine whether or not date is before today.
	 * The function uses UTC values to maintain consistency with website date.
	 * Function will return false if proper unit is not provided.
	 *
	 * @since 4.9.13
	 *
	 * @param {Date}   date Date object representing the date being compared
	 * @param {string} unit Unit to compare dates to
	 *
	 * @return {bool}
	 */
	obj.isBeforeToday = function( date, unit ) {
		switch ( unit ) {
			case 'year':
				return date.getFullYear() < obj.today.getUTCFullYear();
			case 'month':
				return obj.isBeforeToday( date, 'year' )
					|| ( obj.isSameAsToday( date, 'year' ) && date.getMonth() < obj.today.getUTCMonth() );
			case 'day':
				return obj.isBeforeToday( date, 'month' )
					|| ( obj.isSameAsToday( date, 'month' ) && date.getDate() < obj.today.getUTCDate() );
			default:
				return false;
		}
	};

	/**
	 * Filter datepicker day cells
	 *
	 * @since 4.9.13
	 *
	 * @return {string|void}
	 */
	obj.filterDayCells = function( date ) {
		if ( obj.isBeforeToday( date, 'day' ) ) {
			return 'past';
		} else if ( obj.isSameAsToday( date, 'day' ) ) {
			return 'current';
		}
	};

	/**
	 * Filter datepicker month cells
	 *
	 * @since 4.9.13
	 *
	 * @return {string|void}
	 */
	obj.filterMonthCells = function( date ) {
		if ( obj.isBeforeToday( date, 'month' ) ) {
			return 'past';
		} else if ( obj.isSameAsToday( date, 'month' ) ) {
			return 'current';
		}
	};

	/**
	 * Filter datepicker year cells
	 *
	 * @since 4.9.13
	 *
	 * @return {string|void}
	 */
	obj.filterYearCells = function( date ) {
		if ( obj.isBeforeToday( date, 'year' ) ) {
			return 'past';
		} else if ( obj.isSameAsToday( date, 'year' ) ) {
			return 'current';
		}
	};

	/**
	 * Convert date format from PHP to Bootstrap datepicker format.
	 *
	 * @since 4.9.11
	 *
	 * @param {string} dateFormat datepicker date format in PHP format.
	 *
	 * @return {string}
	 */
	obj.convertDateFormat = function( dateFormat ) {
		var convertedDateFormat = dateFormat;
		Object.keys( obj.dateFormatMap ).forEach( function( key ) {
			convertedDateFormat = convertedDateFormat.replace( key, obj.dateFormatMap[ key ] );
		} );

		return convertedDateFormat;
	};

	/**
	 * Initialize datepicker date format.
	 *
	 * @since 4.9.11
	 *
	 * @param {object} data data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.initDateFormat = function( data ) {
		var dateFormats = data.date_formats || {};
		var dateFormat = dateFormats.compact;
		var convertedDateFormat = obj.convertDateFormat( dateFormat );
		obj.options.format = convertedDateFormat;
	};

	/**
	 * Deinitialize datepicker JS
	 *
	 * @since  4.9.5
	 *
	 * @param  {Event}       event    event object for 'beforeAjaxSuccess.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.deinit = function( event, jqXHR, settings ) {
		var $container = event.data.container;

		$container.trigger( 'beforeDatepickerDeinit.tribeEvents', [ jqXHR, settings ] );

		var $input = $container.find( obj.selectors.input );
		var $datepickerButton = $container.find( obj.selectors.button );

		$input.bootstrapDatepicker( 'destroy' ).off();
		$datepickerButton.off();
		$container.off( 'beforeAjaxSuccess.tribeEvents', obj.deinit );

		$container.trigger( 'afterDatepickerDeinit.tribeEvents', [ jqXHR, settings ] );
	};

	/**
	 * Initialize datepicker JS
	 *
	 * @since  4.9.8
	 *
	 * @param  {Event}   event      event object for 'afterSetup.tribeEvents' event
	 * @param  {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param  {jQuery}  $container jQuery object of view container
	 * @param  {object}  data       data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		$container.trigger( 'beforeDatepickerInit.tribeEvents', [ index, $container, data ] );

		var $input = $container.find( obj.selectors.input );
		var $datepickerButton = $container.find( obj.selectors.button );
		var viewSlug = data.slug;
		var isMonthView = 'month' === viewSlug;

		// set up datepicker change event and handler
		var changeEvent = isMonthView ? 'changeMonth' : 'changeDate';
		var changeHandler = isMonthView ? obj.handleChangeMonth : obj.handleChangeDate;

		// set up datepicker button state
		var state = {
			isTarget: false,
		};

		// set up mutation observer
		obj.observer = new MutationObserver( obj.handleMutation( { container: $container } ) );

		// set up today's date
		obj.setToday( data.today );

		// set options for datepicker
		obj.initDateFormat( data );
		obj.options.weekStart = data.start_of_week;
		obj.options.container = $container.find( obj.selectors.datepickerContainer );
		obj.options.minViewMode = isMonthView ? 'year' : 'month';
		var tribeL10nDatatables = window.tribe_l10n_datatables || {};
		var datepickerI18n = tribeL10nDatatables.datepicker || {};
		var nextText = datepickerI18n.nextText || 'Next';
		var prevText = datepickerI18n.prevText || 'Prev';
		obj.options.templates.leftArrow = '<span class="tribe-common-svgicon"></span><span class="tribe-common-a11y-visual-hide">' + prevText + '</span>',
		obj.options.templates.rightArrow = '<span class="tribe-common-svgicon"></span><span class="tribe-common-a11y-visual-hide">' + nextText + '</span>',
		obj.options.beforeShowDay = obj.filterDayCells;
		obj.options.beforeShowMonth = obj.filterMonthCells;
		obj.options.beforeShowYear = obj.filterYearCells;

		$input
			.bootstrapDatepicker( obj.options )
			.on( changeEvent, { container: $container }, changeHandler )
			.on( 'show', { datepickerButton: $datepickerButton }, obj.handleShow )
			.on( 'hide', { datepickerButton: $datepickerButton, input: $input, observer: obj.observer }, obj.handleHide );

		if ( isMonthView ) {
			$input
				.bootstrapDatepicker()
				.on( 'keydown', { input: $input }, obj.handleKeyDown );
		}

		$datepickerButton
			.on( 'touchstart mousedown', { target: $datepickerButton }, obj.handleMousedown )
			.on( 'click', { target: $datepickerButton, input: $input }, obj.handleClick )
			.data( 'tribeEventsState', state );

		// deinit datepicker and event handlers before success
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container, viewSlug: viewSlug }, obj.deinit );

		$container.trigger( 'afterDatepickerInit.tribeEvents', [ index, $container, data ] );
	};

	/**
	 * Initialize datepicker i18n
	 *
	 * @since 4.9.5
	 *
	 * @return {void}
	 */
	obj.initDatepickerI18n = function() {
		var tribeL10nDatatables = window.tribe_l10n_datatables || {};
		var datepickerI18n = tribeL10nDatatables.datepicker || {};

		datepickerI18n.dayNames &&
			( $.fn.bootstrapDatepicker.dates.en.days = datepickerI18n.dayNames );
		datepickerI18n.dayNamesShort &&
			( $.fn.bootstrapDatepicker.dates.en.daysShort = datepickerI18n.dayNamesShort );
		datepickerI18n.dayNamesMin &&
			( $.fn.bootstrapDatepicker.dates.en.daysMin = datepickerI18n.dayNamesMin );
		datepickerI18n.monthNames &&
			( $.fn.bootstrapDatepicker.dates.en.months = datepickerI18n.monthNames );
		datepickerI18n.monthNamesMin &&
			( $.fn.bootstrapDatepicker.dates.en.monthsShort = datepickerI18n.monthNamesMin );
		datepickerI18n.today &&
			( $.fn.bootstrapDatepicker.dates.en.today = datepickerI18n.today );
		datepickerI18n.clear &&
			( $.fn.bootstrapDatepicker.dates.en.clear = datepickerI18n.clear );
	};

	/**
	 * Initialize datepicker to jQuery object
	 *
	 * @since 4.9.5
	 *
	 * @return {void}
	 */
	obj.initDatepicker = function() {
		if ( $.fn.datepicker && $.fn.datepicker.noConflict ) {
			var datepicker = $.fn.datepicker.noConflict();
			$.fn.bootstrapDatepicker = datepicker;

			obj.initDatepickerI18n();
			obj.state.initialized = true;
		}
	};

	/**
	 * Handles the initialization of the Datepicker when Document is ready
	 *
	 * @since 4.9.5
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		obj.initDatepicker();

		if ( obj.state.initialized ) {
			$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.init );
		}
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.datepicker );
