/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 4.9.7
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Viewport Object in the Global Tribe variable
 *
 * @since 4.9.7
 *
 * @type   {PlainObject}
 */
tribe.events.views.viewport = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since 4.9.7
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.events.views.viewport
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	var $window = $( window );
	var $document = $( document );

	/**
	 * Object of options
	 *
	 * @since 4.9.7
	 *
	 * @type {PlainObject}
	 */
	obj.options = {
		MOBILE_BREAKPOINT: tribe.events.views.breakpoints.breakpoints.medium || 768,
	};

	/**
	 * Object of state
	 *
	 * @since 4.9.7
	 *
	 * @type {PlainObject}
	 */
	obj.state = {
		isMobile: true,
	};

	/**
	 * Set viewport state
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.setViewport = function( $container ) {
		obj.state.isMobile = $container.outerWidth() < obj.options.MOBILE_BREAKPOINT;
		$document.trigger( 'resize.tribeEvents' );
	};

	/**
	 * Handles window resize event
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event event object for 'resize' event
	 *
	 * @return {void}
	 */
	obj.handleResize = function( event ) {
		obj.setViewport( event.data.container );
	};

	/**
	 * Bind events for window resize
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( $container ) {
		$window.on( 'resize', { container: $container }, obj.handleResize );
	};

	/**
	 * Initialize viewport JS
	 *
	 * @since  4.9.7
	 *
	 * @param  {Event}   event      event object for 'afterSetup.tribeEvents' event
	 * @param  {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param  {jQuery}  $container jQuery object of view container
	 * @param  {object}  data       data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		obj.bindEvents( $container );
		obj.setViewport( $container );
	};

	/**
	 * Handles the initialization of viewport when Document is ready
	 *
	 * @since 4.9.7
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.init );
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.viewport );
