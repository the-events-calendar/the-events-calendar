/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since TBD
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Views Object in the Global Tribe variable
 *
 * @since TBD
 *
 * @type   {PlainObject}
 */
tribe.events.views.monthMobileEvents = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since TBD
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
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		calendar: '.tribe-events-calendar-month',
		calendarDay: '.tribe-events-calendar-month__day-cell--mobile',
		calendarDaySelected: '.tribe-events-calendar-month__day-cell--selected',
		mobileEventsDay: '.tribe-events-calendar-month-mobile-events__mobile-day',
	};

	/**
	 * Closes all mobile events
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.closeAllEvents = function( $container ) {
		var $calendar = $container.find( obj.selectors.calendar );

		$calendar.find( obj.selectors.calendarDay ).each( function( index, day ) {
			var $header = $( day );
			var contentId = $header.attr( 'aria-controls' );
			var $content = $container.find( '#' + contentId );

			obj.closeMobileEvents( $header, $content );
		} );
	};

	/**
	 * Opens mobile events
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $header jQuery object of mobile day button
	 * @param {jQuery} $content jQuery object of mobile events container
	 *
	 * @return {void}
	 */
	obj.openMobileEvents = function( $header, $content ) {
		// add selected class
		$header.addClass( obj.selectors.calendarDaySelected.className() );

		// set accessibility attributes
		$header.attr( 'aria-expanded', 'true' );
		$header.attr( 'aria-selected', 'true' );
		$content.attr( 'aria-hidden', 'false' );

		// add inline css
		$content.css( 'display', 'block' );
		$content.parent().css( 'display', 'block' );
	};

	/**
	 * Closes mobile events
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $header jQuery object of mobile day button
	 * @param {jQuery} $content jQuery object of mobile events container
	 *
	 * @return {void}
	 */
	obj.closeMobileEvents = function( $header, $content ) {
		// remove selected class
		$header.removeClass( obj.selectors.calendarDaySelected.className() );

		// set accessibility attributes
		$header.attr( 'aria-expanded', 'false' );
		$header.attr( 'aria-selected', 'false' );
		$content.attr( 'aria-hidden', 'true' );

		// remove inline css
		$content.css( 'display', '' );
		$content.parent().css( 'display', '' );
	};

	/**
	 * Toggles mobile events on mobile day click
	 *
	 * @since TBD
	 *
	 * @param {Event} e event object of click event
	 *
	 * @return {void}
	 */
	obj.toggleMobileEvents = function( e ) {
		var $container = e.data.container;
		var $header = $( e.data.target );
		var contentId = $header.attr( 'aria-controls' );
		var $content = $container.find( '#' + contentId );

		if ( $header.hasClass( obj.selectors.calendarDaySelected.className() ) ) {
			obj.closeMobileEvents( $header, $content );
		} else {
			obj.closeAllEvents( $container );
			obj.openMobileEvents( $header, $content );
		}
	};

	/**
	 * Binds events for mobile day click listeners
	 *
	 * @since TBD
	 *
	 * @param {Event} event event object for 'afterSetup.tribeEvents' event
	 * @param {integer} index jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param {jQuery} $container jQuery object of view container
	 * @param {object} data data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( event, index, $container, data ) {
		var $calendar = $container.find( obj.selectors.calendar );

		$calendar.find( obj.selectors.calendarDay ).each( function( index, day ) {
			$( day ).on( 'click', { target: this, container: $container }, obj.toggleMobileEvents );
		} );
	};

	/**
	 * Handles the initialization of the mobile days when Document is ready
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.bindEvents );

		/**
		 * @todo: do below for ajax events
		 */
		// on 'beforeAjaxBeforeSend.tribeEvents' event, remove all listeners
		// on 'afterAjaxError.tribeEvents', add all listeners
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.monthMobileEvents );
