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
 * Configures Events Bar Object in the Global Tribe variable
 *
 * @since TBD
 *
 * @type   {PlainObject}
 */
tribe.events.views.eventsBarInputs = {};

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
		input: '[data-js="tribe-events-events-bar-input-control-input"]',
		inputWrapper: '[data-js="tribe-events-events-bar-input-control"]',
	};

	/**
	 * Toggles input class if it has a value
	 *
	 * @since TBD
	 *
	 * @param {Event} event event object of click event
	 *
	 * @return {void}
	 */
	obj.setInputFocusClass = function( event ) {
		var $input = event.data.target;
		var $wrapper = event.data.wrapper;

		// Set the focus class if it has content.
		$wrapper.toggleClass( event.data.inputClassFocus, '' !== $input.val().trim() );
	};

	/**
	 * Bind events for the events bar input, on focus and according to their value.
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $container jQuery object of container.
	 *
	 * @return {void}
	 */
	obj.bindEventsInputFocus = function( $container ) {

		$container.find( obj.selectors.inputWrapper ).each( function( index, wrapper ) {

			var inputWrapperClass = wrapper.className.match( /tribe-common-c-search__[a-z\-\_]+/i );

			if ( ! inputWrapperClass ) {
				return;
			}

			var inputWrapperFocus = inputWrapperClass[0] + '-focus';
			var $wrapper = $( wrapper );
			var $input = $wrapper.find( obj.selectors.input );

			// Bail in case we dont find the input.
			if ( ! $input.length ) {
				return;
			}

			$wrapper.toggleClass( inputWrapperFocus, '' !== $input.val().trim() );

			$input.on( 'change', { target: $input, wrapper: $wrapper, inputClassFocus: inputWrapperFocus }, obj.setInputFocusClass );
		});
	}

	/**
	 * Binds events for the events bar change listeners
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
		// Bind event for the keyword input.
		obj.bindEventsInputFocus( $container );
	};

	/**
	 * Handles the initialization of the accordions when Document is ready
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
} )( jQuery, tribe.events.views.eventsBarInputs );
