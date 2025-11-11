/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 4.9.4
 *
 * @type {Object}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Month Mobile Events Object in the Global Tribe variable
 *
 * @since 4.9.4
 *
 * @type {Object}
 */
tribe.events.views.monthMobileEvents = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since 4.9.4
 *
 * @param {Object} $   jQuery
 * @param {Object} obj tribe.events.views.monthMobileEvents
 *
 * @return {void}
 */
( function ( $, obj ) {
	'use strict';
	const $document = $( document );

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since 4.9.8
	 *
	 * @type {Object}
	 */
	obj.selectors = {
		calendar: '[data-js="tribe-events-month-grid"]',
		calendarDay: '[data-js="tribe-events-calendar-month-day-cell-mobile"]',
		calendarDaySelectedClass: '.tribe-events-calendar-month__day-cell--selected',
		mobileEvents: '[data-js="tribe-events-calendar-month-mobile-events"]',
		mobileEventsMobileDayShowClass: '.tribe-events-calendar-month-mobile-events__mobile-day--show',
		mobileEventsDefaultNotices: '.tribe-events-header__messages--mobile:not(.tribe-events-header__messages--day)', // eslint-disable-line max-len
	};

	/**
	 * Closes all mobile events
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.closeAllEvents = function ( $container ) {
		$container.find( obj.selectors.calendarDay ).each( function ( index, header ) {
			const $header = $( header );
			const contentId = $header.attr( 'aria-controls' );

			/**
			 * Define empty jQuery object in the case contentId is false or undefined
			 * so that we don't get selectors like #false or #undefined.
			 * Also only perform accordion actions if header has aria-controls attribute.
			 */
			let $content = $( '' );
			if ( contentId ) {
				$content = $container.find( '#' + contentId );
				tribe.events.views.accordion.closeAccordion( $header, $content );
			}

			obj.closeMobileEvents( $header, $content );
		} );
	};

	/**
	 * Handle the display state of the default "No events found in month" messages.
	 *
	 * @param {jQuery}  $container         jQuery object of view container
	 * @param {boolean} showDefaultNotices Whether to show or hide the default notices, if no day is selected.
	 */
	obj.handleMobileDayClick = function ( $container, showDefaultNotices ) {
		const $defaultNotices = $container.find( obj.selectors.mobileEventsDefaultNotices );
		const daySelected = $container.find( obj.selectors.mobileEventsMobileDayShowClass ).length > 0;

		if ( showDefaultNotices && ! daySelected ) {
			$defaultNotices.removeClass( 'tribe-common-a11y-hidden' );
		} else {
			$defaultNotices.addClass( 'tribe-common-a11y-hidden' );
		}
	};

	/**
	 * Opens mobile events
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $header  jQuery object of mobile day button
	 * @param {jQuery} $content jQuery object of mobile events container
	 *
	 * @return {void}
	 */
	obj.openMobileEvents = function ( $header, $content ) {
		// only perform accordion actions if $header has aria-controls attribute.
		const contentId = $header.attr( 'aria-controls' );
		if ( contentId ) {
			tribe.events.views.accordion.openAccordion( $header, $content );
		}

		$header.addClass( obj.selectors.calendarDaySelectedClass.className() );
		$content.addClass( obj.selectors.mobileEventsMobileDayShowClass.className() );

		obj.focusPanel( $content );
		obj.setupTabExit( $header, $content );
	};

	/**
	 * Closes mobile events
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $header  jQuery object of mobile day button
	 * @param {jQuery} $content jQuery object of mobile events container
	 *
	 * @return {void}
	 */
	obj.closeMobileEvents = function ( $header, $content ) {
		const contentId = $header.attr( 'aria-controls' );
		if ( contentId ) {
			tribe.events.views.accordion.closeAccordion( $header, $content );
		}

		$header.removeClass( obj.selectors.calendarDaySelectedClass.className() );
		$content.removeClass( obj.selectors.mobileEventsMobileDayShowClass.className() );

		// Cleanup event listeners to avoid stacking
		$content.find( '*' ).off( 'keydown.tribeEvents' );
	};

	/**
	 * Toggles mobile events on mobile day click
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object of click event
	 *
	 * @return {void}
	 */
	obj.toggleMobileEvents = function ( event ) {
		const $container = event.data.container;
		const $header = $( event.data.target );
		const contentId = $header.attr( 'aria-controls' );

		/**
		 * Define empty jQuery object in the case contentId is false or undefined
		 * so that we don't get selectors like #false or #undefined.
		 */
		let $content = $( '' );
		if ( contentId ) {
			$content = $container.find( '#' + contentId );
		}

		if ( $header.hasClass( obj.selectors.calendarDaySelectedClass.className() ) ) {
			obj.closeMobileEvents( $header, $content );
			obj.handleMobileDayClick( $container, true );
		} else {
			obj.closeAllEvents( $container );
			obj.handleMobileDayClick( $container, false );
			obj.openMobileEvents( $header, $content );
		}
	};

	/**
	 * Unbinds events for calendar
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.unbindCalendarEvents = function ( $container ) {
		const $calendar = $container.find( obj.selectors.calendar );
		$calendar.find( obj.selectors.calendarDay ).each( function ( index, day ) {
			$( day ).off( 'click', obj.toggleMobileEvents );
		} );
	};

	/**
	 * Binds events for calendar
	 *
	 * @since 4.9.5
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.bindCalendarEvents = function ( $container ) {
		const $calendar = $container.find( obj.selectors.calendar );
		$calendar.find( obj.selectors.calendarDay ).each( function ( index, day ) {
			$( day ).on(
				'click',
				{
					target: day,
					container: $container,
					calendar: $calendar,
				},
				obj.toggleMobileEvents
			);
		} );
	};

	/**
	 * Initializes mobile events state
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.initState = function ( $container ) {
		const $mobileEvents = $container.find( obj.selectors.mobileEvents );
		const containerState = $container.data( 'tribeEventsState' );
		const isMobile = containerState.isMobile;

		const state = {
			desktopInitialized: ! isMobile,
		};

		$mobileEvents.data( 'tribeEventsState', state );
	};

	/**
	 * Handles resize event
	 *
	 * @since 4.9.8
	 *
	 * @param {Event} event event object for 'beforeAjaxSuccess.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.handleResize = function ( event ) {
		const $container = event.data.container;
		const $mobileEvents = $container.find( obj.selectors.mobileEvents );
		const state = $mobileEvents.data( 'tribeEventsState' );
		const containerState = $container.data( 'tribeEventsState' );
		const isMobile = containerState.isMobile;

		if ( ! isMobile ) {
			if ( ! state.desktopInitialized ) {
				obj.closeAllEvents( $container );
				state.desktopInitialized = true;
			}
		} else {
			obj.handleMobileDayClick( $container, true );

			if ( state.desktopInitialized ) {
				state.desktopInitialized = false;
			}
		}

		$mobileEvents.data( 'tribeEventsState', state );
	};

	/**
	 * Focuses the mobile day content panel and scrolls it into view.
	 *
	 * @since 6.15.11
	 *
	 * @param {jQuery} $content jQuery object of the active mobile events container.
	 *
	 * @return {void}
	 */
	obj.focusPanel = function ( $content ) {
		if ( ! $content.length ) {
			return;
		}

		if ( ! $content.attr( 'tabindex' ) ) {
			$content.attr( 'tabindex', '-1' );
		}

		requestAnimationFrame( function () {
			try {
				$content[ 0 ].focus( { preventScroll: true } );
			} catch ( e ) {
				$content.trigger( 'focus' );
			}

			if ( $content[ 0 ] && $content[ 0 ].scrollIntoView ) {
				$content[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'start' } );
			}
		} );
	};

	/**
	 * Focuses the next calendar day button in the grid.
	 *
	 * @since 6.15.11
	 *
	 * @param {jQuery} $header jQuery object for the current date button.
	 *
	 * @return {void}
	 */
	obj.focusNextDay = function ( $header ) {
		const $allDays = $header.closest( obj.selectors.calendar ).find( obj.selectors.calendarDay );
		const index = $allDays.index( $header );
		const $next = $allDays.eq( index + 1 );

		if ( $next.length ) {
			$next.focus();
		} else {
			const activeElement = $header[ 0 ].ownerDocument.activeElement;
			activeElement.blur();
		}
	};

	/**
	 * Ensures that when the user tabs past the last tabbable element in the event list,
	 * focus returns to the next date in the calendar grid.
	 *
	 * @since 6.15.11
	 *
	 * @param {jQuery} $header  Current date button
	 * @param {jQuery} $content Panel container
	 *
	 * @return {void}
	 */
	obj.setupTabExit = function ( $header, $content ) {
		const tabbableSelectors = 'a, button, [tabindex]:not([tabindex="-1"])';
		const $tabbables = $content.find( tabbableSelectors );
		const $last = $tabbables.length ? $( $tabbables.get( $tabbables.length - 1 ) ) : $content;

		$last.off( 'keydown.tribeEvents' ).on( 'keydown.tribeEvents', function ( e ) {
			if ( e.key === 'Tab' && ! e.shiftKey ) {
				e.preventDefault();
				obj.focusNextDay( $header );
			}
		} );
	};

	/**
	 * Deinitializes mobile days
	 *
	 * @since 4.9.8
	 *
	 * @param {Event}  event    event object for 'beforeAjaxSuccess.tribeEvents' event
	 * @param {Object} jqXHR    Request object
	 * @param {Object} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.deinit = function ( event, jqXHR, settings ) {
		// eslint-disable-line no-unused-vars
		const $container = event.data.container;
		obj.unbindCalendarEvents( $container );
		$container.off( 'resize.tribeEvents', obj.handleResize ).off( 'beforeAjaxSuccess.tribeEvents', obj.deinit );
	};

	/**
	 * Initializes mobile days
	 *
	 * @since 4.9.8
	 *
	 * @param {Event}  event      event object for 'afterSetup.tribeEvents' event
	 * @param {number} index      jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param {jQuery} $container jQuery object of view container
	 * @param {Object} data       data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.init = function ( event, index, $container, data ) {
		// eslint-disable-line no-unused-vars
		const $mobileEvents = $container.find( obj.selectors.mobileEvents );

		if ( ! $mobileEvents.length ) {
			return;
		}

		obj.handleMobileDayClick( $container, true );
		obj.initState( $container );
		obj.bindCalendarEvents( $container );
		$container
			.on( 'resize.tribeEvents', { container: $container }, obj.handleResize )
			.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.deinit );
	};

	/**
	 * Handles the initialization of the mobile days when Document is ready
	 *
	 * @since 4.9.4
	 *
	 * @return {void}
	 */
	obj.ready = function () {
		$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.init );
	};

	// Configure on document ready
	$( obj.ready );
} )( jQuery, tribe.events.views.monthMobileEvents );
