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
 * @param  {PlainObject} obj tribe.events.views.manager
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
		datepickerContainer: '[data-js="tribe-events-top-bar-datepicker-container"]',
		datepickerDays: '.datepicker-days',
		datepickerDaysBody: '.datepicker-days tbody',
		datepickerDaysRow: '.datepicker-days tbody tr',
		datepickerDay: '.day',
		datepickerDayNotDisabled: '.day:not(.disabled)',
		input: '[data-js="tribe-events-top-bar-date"]',
		button: '[data-js="tribe-events-top-bar-datepicker-button"]',
		buttonOpenClass: '.tribe-events-c-top-bar__datepicker-button--open',
		activeClass: '.active',
		disabledClass: '.disabled',
		focusedClass: '.focused',
		hoveredClass: '.hovered',
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
			url: window.location.href,
			view_data: viewData,
			_wpnonce: $container.data( 'view-rest-nonce' ),
		};

		tribe.events.views.manager.request( data, $container );
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

		/**
		 * @todo: use format from BE. Paul.
		 */
		var viewData = {
			[ 'tribe-bar-date' ]: [ year, paddedMonth, paddedDate ].join( '-' ),
		};

		obj.request( viewData, $container );
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
		var month = event.date.getMonth() + 1;
		var year = event.date.getFullYear();

		var paddedMonth = obj.padNumber( month );

		/**
		 * @todo: use format from BE. Paul.
		 */
		var viewData = {
			[ 'tribe-bar-date' ]: [ year, paddedMonth ].join( '-' ),
		};

		obj.request( viewData, $container );
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

		event.data.datepickerButton.removeClass( obj.selectors.buttonOpenClass.className() );
	};

	/**
	 * Toggle hover class
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event event object for 'mouseenter' and 'mouseleave' events
	 *
	 * @return {void}
	 */
	obj.toggleHoverClass = function( event ) {
		event.data.row.toggleClass( obj.selectors.hoveredClass.className() );
	};

	/**
	 * Handle disabled day click event
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event event object for 'click' event
	 *
	 * @return {void}
	 */
	obj.handleDisabledDayClick = function( event ) {
		event.data.row.find( obj.selectors.datepickerDayNotDisabled ).click();
	};

	/**
	 * Bind datepicker row events
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event event object for 'show' event
	 *
	 * @return {void}
	 */
	obj.bindRowEvents = function( event ) {
		var $datepickerDays = event.data.container.find( obj.selectors.datepickerDays );
		var config = { attributes: true, childList: true, subtree: true };

		var $container = event.data.container;
		var $rows = $container.find( obj.selectors.datepickerDaysRow );

		// for each row, add mouseenter and mouseleave event listeners to toggle hover class
		$rows.each( function( index, row ) {
			var $row = $( row );
			$row
				.off( 'mouseenter mouseleave', obj.toggleHoverClass )
				.on( 'mouseenter mouseleave', { row: $row }, obj.toggleHoverClass )
				.find( obj.selectors.datepickerDay )
				.each( function( index, day ) {
					var $day = $( day );

					// if day has disabled class, allow clicking day to select first day of the week
					if ( $day.hasClass( obj.selectors.disabledClass.className() ) ) {
						$day
							.off( 'click', obj.handleDisabledDayClick )
							.on( 'click', { row: $row }, obj.handleDisabledDayClick );
					}

					// if day has focused class, add focused class to row
					if ( $day.hasClass( obj.selectors.focusedClass.className() ) ) {
						$row.addClass( obj.selectors.focusedClass.className() );
					}

					// if day has active class, add active class to row
					if ( $day.hasClass( obj.selectors.activeClass.className() ) ) {
						$row.addClass( obj.selectors.activeClass.className() );
					}
				} );
		} );

		event.data.observer.observe( $datepickerDays[ 0 ], config );
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

		state.isTarget = false;

		$datepickerButton
			.toggleClass( obj.selectors.buttonOpenClass.className() )
			.data( 'tribeEventsState', state );
		$input
			.focus()
			.bootstrapDatepicker( method );
	};

	/**
	 * Handle mutations from mutation observer
	 *
	 * @since 4.9.7
	 *
	 * @param {PlainObject} data data object to be passed for use in handler
	 *
	 * @return {void}
	 */
	obj.handleMutation = function( data ) {
		var $container = data.container;
		return function( mutationsList, observer ) {
			for ( var mutation of mutationsList ) {
				if (
					'childList' === mutation.type &&
					$container.find( obj.selectors.datepickerDaysBody ).is( mutation.target ) &&
					mutation.addedNodes.length
				) {
					obj.bindRowEvents( { data: { container: $container, observer: observer } } );
				}
			}
		};
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
		var $input = $container.find( obj.selectors.input );
		var $datepickerButton = $container.find( obj.selectors.button );
		var viewSlug = event.data.viewSlug;
		var isMonthView = 'month' === viewSlug;
		var isWeekView = 'week' === viewSlug;
		var changeEvent = isMonthView ? 'changeMonth' : 'changeDate';
		var changeHandler = isMonthView ? obj.handleChangeMonth : obj.handleChangeDate;

		$input
			.bootstrapDatepicker( 'destroy' )
			.off( changeEvent, changeHandler )
			.off( 'hide', obj.handleHide );
		$datepickerButton
			.off( 'mousedown', obj.handleMousedown )
			.off( 'click', obj.handleClick );

		if ( isWeekView ) {
			$input.off( 'show', obj.bindRowEvents );
		}
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
		var $input = $container.find( obj.selectors.input );
		var $datepickerButton = $container.find( obj.selectors.button );
		var viewSlug = data.slug;
		var isMonthView = 'month' === viewSlug;
		var isWeekView = 'week' === viewSlug;
		var minViewMode = isMonthView ? 'year' : 'month';
		/**
		 * @todo: use format from BE. Paul.
		 */
		var daysOfWeekDisabled = isWeekView ? [ 1, 2, 3, 4, 5, 6 ] : [];
		var changeEvent = isMonthView ? 'changeMonth' : 'changeDate';
		var changeHandler = isMonthView ? obj.handleChangeMonth : obj.handleChangeDate;

		var tribeL10nDatatables = window.tribe_l10n_datatables || {};
		var datepickerI18n = tribeL10nDatatables.datepicker || {};
		var nextText = datepickerI18n.nextText || 'Next';
		var prevText = datepickerI18n.prevText || 'Prev';

		var state = {
			isTarget: false,
		};

		var observer = new MutationObserver( obj.handleMutation( { container: $container } ) );

		$input
			.bootstrapDatepicker( {
				container: $container.find( obj.selectors.datepickerContainer ),
				daysOfWeekDisabled: daysOfWeekDisabled,
				/**
				 * @todo: use format from BE. Paul.
				 */
				format: 'yyyy-mm-dd',
				maxViewMode: 'decade',
				minViewMode: minViewMode,
				orientation: 'bottom left',
				showOnFocus: false,
				templates: {
					leftArrow: '<span class="tribe-common-svgicon"></span><span class="tribe-common-a11y-visual-hide">' + prevText + '</span>',
					rightArrow: '<span class="tribe-common-svgicon"></span><span class="tribe-common-a11y-visual-hide">' + nextText + '</span>',
				},
			} )
			.on( changeEvent, { container: $container }, changeHandler )
			.on( 'hide', { datepickerButton: $datepickerButton, input: $input, observer: observer }, obj.handleHide );

		$datepickerButton
			.on( 'mousedown touchstart', { target: $datepickerButton }, obj.handleMousedown )
			.on( 'click', { target: $datepickerButton, input: $input }, obj.handleClick )
			.data( 'tribeEventsState', state );

		if ( isWeekView ) {
			$input.on( 'show', { container: $container, observer: observer }, obj.bindRowEvents );
		}

		// deinit datepicker and event handlers before success
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container, viewSlug: viewSlug }, obj.deinit );
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
