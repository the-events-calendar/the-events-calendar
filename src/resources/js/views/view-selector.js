/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 4.9.4
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Views Object in the Global Tribe variable
 *
 * @since 4.9.4
 *
 * @type   {PlainObject}
 */
tribe.events.views.viewSelector = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since 4.9.4
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
	 * @since 4.9.4
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
	 * @since 4.9.4
	 *
	 * @param {Event } event event object for click event
	 *
	 * @return {void}
	 */
	obj.handleClick = function( event ) {
		$( event.data.target ).toggleClass( obj.selectors.viewSelectorButtonActive.className() );
	};

	/**
	 * Unbinds events for view selector
	 *
	 * @since 4.9.4
	 *
	 * @param  {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.unbindViewSelectorEvents = function( $container ) {
		$container
			.find( obj.selectors.viewSelectorButton )
			.each( function( index, header ) {
				$( header ).off( 'click', obj.handleClick );
			} );
	};

	/**
	 * Binds events for view selector
	 *
	 * @since  4.9.5
	 *
	 * @param  {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.bindViewSelectorEvents = function( $container ) {
		$container
			.find( obj.selectors.viewSelectorButton )
			.each( function( index, header ) {
				$( header ).on( 'click', { target: header }, obj.handleClick );
			} );
	};

	/**
	 * Unbinds events for container
	 *
	 * @since  4.9.5
	 *
	 * @param  {Event}       event    event object for 'afterSetup.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function( event, jqXHR, settings ) {
		var $container = event.data.container;
		obj.unbindViewSelectorEvents( $container );
	};

	/**
	 * Binds events for container
	 *
	 * @since 4.9.5
	 *
	 * @param  {Event}   event      event object for 'afterSetup.tribeEvents' event
	 * @param  {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param  {jQuery}  $container jQuery object of view container
	 * @param  {object}  data       data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( event, index, $container, data ) {
		obj.bindViewSelectorEvents( $container );
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.unbindEvents );
	};

	/**
	 * Handles the initialization of the view selector when Document is ready
	 *
	 * @since 4.9.4
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.bindEvents );
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.viewSelector );
