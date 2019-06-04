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
		hiddenEvent: '.tribe-events-calendar-month__multiday-event--hidden',
	};

	obj.bindEvents = function() {
		// bind events for
		// hidden multiday hover
		// hidden multiday focus
		// recursively look for non-hidden multiday, add class to hover/focus
		// find day, add hover class
	};

	/**
	 * Handles the initialization of the multiday events when Document is ready
	 *
	 * @since  4.9.3
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		obj.$hiddenEvents = $( obj.selectors.hiddenEvent );
		obj.$hiddenEvents.each( obj.bindEvents );
	};

	// Configure on document ready
	$document.ready( obj.ready );
}( jQuery, tribe.events.views.multiday_events ) );
