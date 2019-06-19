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
tribe.events.views.viewSelector = {};

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
		viewSelector: '[data-js="tribe-events-view-selector"]',
		viewSelectorButton: '[data-js="tribe-events-accordion-trigger"]',
		viewSelectorButtonActive: '.tribe-events-c-view-selector__button--active',
	};

	/**
	 * Toggles active class on view selector button
	 *
	 * @since TBD
	 *
	 * @param {Event } event event object for click event
	 *
	 * @return {void}
	 */
	obj.handleClick = function( event ) {
		$( event.data.target ).toggleClass( obj.selectors.viewSelectorButtonActive.className() );
	};

	/**
	 * Binds events for view selector click listeners
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
		$container
			.find( obj.selectors.viewSelector )
			.find( obj.selectors.viewSelectorButton )
			.each( function( index, header ) {
				$( header ).on( 'click', { target: this }, obj.handleClick );
			} );
	};

	/**
	 * Handles the initialization of the view selector when Document is ready
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
} )( jQuery, tribe.events.views.viewSelector );
