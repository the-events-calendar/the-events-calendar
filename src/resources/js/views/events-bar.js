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
tribe.events.views.eventsBar = {};

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
		inputKeyword: '.tribe-common-c-search__input--keyword',
		inputKeywordFocus: '.tribe-common-c-search__input--keyword-focus',
		inputLocation: '.tribe-common-c-search__input--location',
		inputLocationFocus: '.tribe-common-c-search__input--location-focus',
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
		var $input = $( event.target );

		// Set the focus class if it has content.
		$input.toggleClass( event.data.inputClassFocus, '' !== $input.val().trim() );

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
		$container
			.find( obj.selectors.inputKeyword )
			.each( function( index, input ) {
				console.log( $( input ).val() );
				$( input ).toggleClass( obj.selectors.inputKeywordFocus.className(), '' !== $( input ).val().trim() );
				$( input ).on( 'change', { target: $( this ), inputClassFocus: obj.selectors.inputKeywordFocus.className() }, obj.setInputFocusClass );
			} );

		// Bind event for the location input.
		$container
			.find( obj.selectors.inputLocation )
			.each( function( index, input ) {
				$( input ).toggleClass( obj.selectors.inputLocationFocus, '' !==  $( input ).val().trim() );
				$( input ).on( 'change', { target: $( this ), inputClassFocus: obj.selectors.inputLocationFocus.className() }, obj.setInputFocusClass );
			} );
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
} )( jQuery, tribe.events.views.eventsBar );
