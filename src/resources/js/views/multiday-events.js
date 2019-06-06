/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since  4.9.3
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Views Object in the Global Tribe variable
 *
 * @since  4.9.3
 *
 * @type   {PlainObject}
 */
tribe.events.views.multiday_events = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since  4.9.3
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} _   Underscore.js
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
	 * @since 4.9.3
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		calendar: '.tribe-events-calendar-month',
		multidayEvent: '.tribe-events-calendar-month__event-multiday',
		hiddenMultidayEvent: '.tribe-events-calendar-month__event-multiday--hidden',
		multidayEventInner: '.tribe-events-calendar-month__event-multiday-inner',
		multidayEventInnerFocus: '.tribe-events-calendar-month__event-multiday-inner--focus',
		multidayEventInnerHover: '.tribe-events-calendar-month__event-multiday-inner--hover',
	};

	/**
	 * Find visible multiday event that relates to the hidden multiday event
	 *
	 * @since 4.9.3
	 *
	 * @param {jQuery} $hiddenMultidayEvent jQuery object of hidden multiday event
	 *
	 * @return {jQuery} jQuery object of visible multiday event or false if none found
	 */
	obj.findVisibleMultidayEvents = function( $hiddenMultidayEvent ) {
		var $calendar = $hiddenMultidayEvent.closest( obj.selectors.calendar );
		var eventId = $hiddenMultidayEvent.attr( 'data-id' );

		return $calendar
			.find( obj.selectors.multidayEvent + '[data-id=' + eventId + ']' )
			.not( obj.selectors.hiddenMultidayEvent );
	};

	/**
	 * Add class to visible multiday event on hidden multiday event hover
	 *
	 * @since 4.9.3
	 *
	 * @param {jQuery} $visibleMultidayEventInner jQuery object of visible multiday event
	 *
	 * @return {function} event handler for on hover in
	 */
	obj.onHoverIn = function( $visibleMultidayEventInner ) {
		return function() {
			$visibleMultidayEventInner.addClass( obj.selectors.multidayEventInnerHover.className() );
		};
	};

	/**
	 * Remove class to visible multiday event on hidden multiday event hover
	 *
	 * @since 4.9.3
	 *
	 * @param {jQuery} $visibleMultidayEventInner jQuery object of visible multiday event
	 *
	 * @return {function} event handler for on hover out
	 */
	obj.onHoverOut = function( $visibleMultidayEventInner ) {
		return function() {
			$visibleMultidayEventInner.removeClass( obj.selectors.multidayEventInnerHover.className() );
		};
	};

	/**
	 * Add class to visible multiday event on hidden multiday event focus
	 *
	 * @since 4.9.3
	 *
	 * @param {jQuery} $visibleMultidayEventInner jQuery object of visible multiday event
	 *
	 * @return {function} event handler for on focus
	 */
	obj.onFocus = function( $visibleMultidayEventInner ) {
		return function() {
			$visibleMultidayEventInner.addClass( obj.selectors.multidayEventInnerFocus.className() );
		};
	};

	/**
	 * Remove class to visible multiday event on hidden multiday event blur
	 *
	 * @since 4.9.3
	 *
	 * @param {jQuery} $visibleMultidayEventInner jQuery object of visible multiday event
	 *
	 * @return {function} event handler for on blur
	 */
	obj.onBlur = function( $visibleMultidayEventInner ) {
		return function() {
			$visibleMultidayEventInner.removeClass( obj.selectors.multidayEventInnerFocus.className() );
		};
	};

	/**
	 * Binds events for hover and focus of hidden multiday events
	 *
	 * @since  4.9.3
	 *
	 * @param {number} index index of hidden multiday events from jQuery selector
	 * @param {HTMLElement} hiddenMultidayEvent HTML element of hidden multiday event
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( index, hiddenMultidayEvent ) {
		var $hiddenMultidayEvent = $( hiddenMultidayEvent );
		var $visibleMultidayEvents = obj.findVisibleMultidayEvents( $hiddenMultidayEvent );

		$visibleMultidayEvents.each( function( index, visibleMultidayEvent ) {
			var $visibleMultidayEvent = $( visibleMultidayEvent );
			var $visibleMultidayEventInner = $visibleMultidayEvent.find( obj.selectors.multidayEventInner );
			var $hiddenMultidayEventInner = $hiddenMultidayEvent.find( obj.selectors.multidayEventInner );

			$hiddenMultidayEventInner.hover( obj.onHoverIn( $visibleMultidayEventInner ), obj.onHoverOut( $visibleMultidayEventInner ) );
			$hiddenMultidayEventInner.focus( obj.onFocus( $visibleMultidayEventInner ) );
			$hiddenMultidayEventInner.blur( obj.onBlur( $visibleMultidayEventInner ) );
		} );
	};

	/**
	 * Handles the initialization of the multiday events when Document is ready
	 *
	 * @since  4.9.3
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		var $hiddenMultidayEvents = $( obj.selectors.multidayEvent );
		$hiddenMultidayEvents.each( obj.bindEvents );
		/**
		 * @todo: do below for ajax events
		 */
		// on 'beforeAjaxBeforeSend.tribeEvents' event, remove all listeners
		// on 'afterAjaxSuccess.tribeEvents' or 'afterAjaxError.tribeEvents', add all listeners
	};

	// Configure on document ready
	$document.ready( obj.ready );
}( jQuery, tribe.events.views.multiday_events ) );
