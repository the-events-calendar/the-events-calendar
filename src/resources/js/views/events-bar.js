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
		input: '.tribe-common-form-control-text__input',
		inputWrapper: '.tribe-common-form-control-text',
		inputKeywordWrapper: '.tribe-common-c-search__input-control--keyword',
		inputKeywordWrapperFocus: '.tribe-common-c-search__input-control--keyword-focus',
		inputLocationWrapper: '.tribe-common-c-search__input-control--location',
		inputLocationWrapperFocus: '.tribe-common-c-search__input-control--location-focus',
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

		// Set the focus class if it has content.
		$input
			.closest( obj.selectors.inputWrapper )
			.toggleClass( event.data.inputClassFocus, '' !== $input.val().trim() );
	};

	/**
	 * Bind events for the keyword input of the events bar
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $container jQuery object of container.
	 *
	 * @return {void}
	 */
	obj.bindEventsInputKeyword = function( $container ) {

		// Bind event for the keyword input.
		$container
			.find( obj.selectors.inputKeywordWrapper )
			.find( obj.selectors.input )
			.each( function( index, input ) {
				$( input )
					.closest( obj.selectors.inputKeywordWrapper )
					.toggleClass( obj.selectors.inputKeywordWrapperFocus.className(), '' !== $( input ).val().trim() );

				$( input ).on( 'change', { target: $( this ), inputClassFocus: obj.selectors.inputKeywordWrapperFocus.className() }, obj.setInputFocusClass );
			} );
	};

	/**
	 * Bind events for the location input of the events bar
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $container jQuery object of container.
	 *
	 * @return {void}
	 */
	obj.bindEventsInputLocation = function( $container ) {

		// Bind event for the location input.
		$container
			.find( obj.selectors.inputLocationdWrapper )
			.find( obj.selectors.input )
			.each( function( index, input ) {
				$( input )
					.closest( obj.selectors.inputLocationWrapper )
					.toggleClass( obj.selectors.inputLocationWrapperFocus.className(), '' !== $( input ).val().trim() );

				$( input ).on( 'change', { target: $( this ), inputClassFocus: obj.selectors.inputLocationWrapperFocus.className() }, obj.setInputFocusClass );

			} );
	};

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
		obj.bindEventsInputKeyword( $container );

		// Bind event for the location input.
		obj.bindEventsInputLocation( $container );

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
