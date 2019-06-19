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
tribe.events.views.multidayEvents = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
	 *
	 * @param {jQuery} $hiddenMultidayEvent jQuery object of hidden multiday event
	 *
	 * @return {jQuery} jQuery object of visible multiday event or false if none found
	 */
	obj.findVisibleMultidayEvents = function( $hiddenMultidayEvent ) {
		var $calendar = $hiddenMultidayEvent.closest( obj.selectors.calendar );
		var eventId = $hiddenMultidayEvent.data( 'event-id' );

		return $calendar
			.find( obj.selectors.multidayEvent + '[data-event-id=' + eventId + ']' )
			.not( obj.selectors.hiddenMultidayEvent );
	};

	/**
	 * Toggle hover class on visible multiday event when hidden multiday triggers hover event
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $visibleMultidayEventInner jQuery object of visible multiday event
	 *
	 * @return {function} event handler for mouseenter and mouseleave events
	 */
	obj.toggleHoverClass = function( $visibleMultidayEventInner ) {
		return function() {
			$visibleMultidayEventInner.toggleClass( obj.selectors.multidayEventInnerHover.className() );
		};
	};

	/**
	 * Toggle focus class on visible multiday event when hidden multiday triggers focus event
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $visibleMultidayEventInner jQuery object of visible multiday event
	 *
	 * @return {function} event handler for focus and blur events
	 */
	obj.toggleFocusClass = function( $visibleMultidayEventInner ) {
		return function() {
			$visibleMultidayEventInner.toggleClass( obj.selectors.multidayEventInnerFocus.className() );
		};
	};

	/**
	 * Binds events for hover and focus of hidden multiday events.
	 *
	 * @since TBD
	 *
	 * @param {Event}   event      JS event triggered.
	 * @param {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event.
	 * @param {jQuery}  $container jQuery object of view container.
	 * @param {object}  data       data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( event, index, $container, data ) {
		var $hiddenMultidayEvents = $container.find( obj.selectors.multidayEvent );

		$hiddenMultidayEvents.each( function( index, hiddenMultidayEvent ) {
			var $hiddenMultidayEvent = $( hiddenMultidayEvent );
			var $visibleMultidayEvents = obj.findVisibleMultidayEvents( $hiddenMultidayEvent );

			$visibleMultidayEvents.each( function( index, visibleMultidayEvent ) {
				var $visibleMultidayEvent = $( visibleMultidayEvent );
				var $visibleMultidayEventInner = $visibleMultidayEvent.find( obj.selectors.multidayEventInner );
				var $hiddenMultidayEventInner = $hiddenMultidayEvent.find( obj.selectors.multidayEventInner );

				$hiddenMultidayEventInner
					.on( 'mouseenter mouseleave', obj.toggleHoverClass( $visibleMultidayEventInner ) )
					.on( 'focus blur', obj.toggleFocusClass( $visibleMultidayEventInner ) )
			} );
		} );
	};

	/**
	 * Handles the initialization of the multiday events when Document is ready
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		/**
		 * @todo: this isn't working... need to figure out why and fix.
		 */
		$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.bindEvents );

		/**
		 * @todo: do below for ajax events
		 */
		// on 'beforeAjaxBeforeSend.tribeEvents' event, remove all listeners
		// on 'afterAjaxError.tribeEvents', add all listeners
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.multidayEvents );
