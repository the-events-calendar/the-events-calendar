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
		multidayEvent: '.tribe-events-calendar-month__multiday-event',
		multidayEventFocus: '.tribe-events-calendar-month__multiday-event--focus',
		multidayEventHover: '.tribe-events-calendar-month__multiday-event--hover',
		hiddenMultidayEvent: '.tribe-events-calendar-month__multiday-event--hidden',
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

		var $visibleMultidayEvent;
		while ( $prevDay.length ) {
			$visibleMultidayEvent = $prevDay.find( obj.selectors.multidayEvent ).not( obj.selectors.hiddenMultidayEvent );

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
	 * @param {jQuery} $visibleMultidayEvent jQuery object of visible multiday event
	 *
	 * @return {function} event handler for on hover in
	 */
	obj.onHoverIn = function( $visibleMultidayEvent ) {
		return function() {
			$visibleMultidayEvent.addClass( obj.selectors.multidayEventHover.className() );
		};
	};

	/**
	 * Remove class to visible multiday event on hidden multiday event hover
	 *
	 * @since 4.9.3
	 *
	 * @param {jQuery} $visibleMultidayEvent jQuery object of visible multiday event
	 *
	 * @return {function} event handler for on hover out
	 */
	obj.onHoverOut = function( $visibleMultidayEvent ) {
		return function() {
			$visibleMultidayEvent.removeClass( obj.selectors.multidayEventHover.className() );
		};
	};

	/**
	 * Add class to visible multiday event on hidden multiday event focus
	 *
	 * @since 4.9.3
	 *
	 * @param {jQuery} $visibleMultidayEvent jQuery object of visible multiday event
	 *
	 * @return {function} event handler for on focus
	 */
	obj.onFocus = function( $visibleMultidayEvent ) {
		return function() {
			$visibleMultidayEvent.addClass( obj.selectors.multidayEventFocus.className() );
		};
	};

	/**
	 * Remove class to visible multiday event on hidden multiday event blur
	 *
	 * @since 4.9.3
	 *
	 * @param {jQuery} $visibleMultidayEvent jQuery object of visible multiday event
	 *
	 * @return {function} event handler for on blur
	 */
	obj.onBlur = function( $visibleMultidayEvent ) {
		return function() {
			$visibleMultidayEvent.removeClass( obj.selectors.multidayEventFocus.className() );
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
			$hiddenMultidayEvent.hover( obj.onHoverIn( $visibleMultidayEvent ), obj.onHoverOut( $visibleMultidayEvent ) );
			$hiddenMultidayEvent.focus( obj.onFocus( $visibleMultidayEvent ) );
			$hiddenMultidayEvent.blur( obj.onBlur( $visibleMultidayEvent ) );
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
