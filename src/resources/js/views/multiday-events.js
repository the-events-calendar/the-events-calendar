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
		day: '.tribe-events-calendar-month__day',
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
	 * @return {(jQuery\|boolean)} jQuery object of visible multiday event or false if none found
	 */
	obj.findVisibleMultidayEvent = function( $hiddenMultidayEvent ) {
		var $prevDay = $hiddenMultidayEvent.closest( obj.selectors.day ).prev();
		var eventId = $hiddenMultidayEvent.attr( 'data-id' );

		var $visibleMultidayEvent;
		while ( $prevDay.length && eventId ) {
			$visibleMultidayEvent = $prevDay
				.find( obj.selectors.multidayEvent + '[data-id=' + eventId + ']' )
				.not( obj.selectors.hiddenMultidayEvent )
				.find();

			if ( $visibleMultidayEvent.length ) {
				return $visibleMultidayEvent;
			}
		}

		return false;
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
			$visibleMultidayEventInner.addClass( obj.selectors.multidayEventHover.className() );
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
			$visibleMultidayEventInner.removeClass( obj.selectors.multidayEventHover.className() );
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
			$visibleMultidayEventInner.addClass( obj.selectors.multidayEventFocus.className() );
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
			$visibleMultidayEventInner.removeClass( obj.selectors.multidayEventFocus.className() );
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
		var $visibleMultidayEvent = obj.findVisibleMultidayEvent( $hiddenMultidayEvent );

		if ( $visibleMultidayEvent ) {
			var $visibleMultidayEventInner = $visibleMultidayEvent.find( obj.selectors.multidayEventInner );
			$hiddenMultidayEvent.hover( obj.onHoverIn( $visibleMultidayEventInner ), obj.onHoverOut( $visibleMultidayEventInner ) );
			$hiddenMultidayEvent.focus( obj.onFocus( $visibleMultidayEventInner ) );
			$hiddenMultidayEvent.blur( obj.onBlur( $visibleMultidayEventInner ) );
		}
	};

	/**
	 * Handles the initialization of the multiday events when Document is ready
	 *
	 * @since  4.9.3
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		obj.$hiddenMultidayEvents = $( obj.selectors.hiddenMultidayEvent );
		obj.$hiddenMultidayEvents.each( obj.bindEvents );
		/**
		 * @todo: do below for ajax events
		 */
		// on 'beforeAjaxBeforeSend.tribeEvents' event, remove all listeners
		// on 'afterAjaxSuccess.tribeEvents' or 'afterAjaxError.tribeEvents', add all listeners
	};

	// Configure on document ready
	$document.ready( obj.ready );
}( jQuery, tribe.events.views.multiday_events ) );
