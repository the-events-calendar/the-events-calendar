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
 * Configures Viewport Object in the Global Tribe variable
 *
 * @since TBD
 *
 * @type   {PlainObject}
 */
tribe.events.views.viewport = {};

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
	var $window = $( window );
	var $document = $( document );

	/**
	 * Object of options
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.options = {
		MOBILE_BREAKPOINT: 768,
	};

	/**
	 * Object of state
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.state = {
		isMobile: true,
	};

	/**
	 * Set viewport state
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.setViewport = function() {
		obj.state.isMobile = window.innerWidth < obj.options.MOBILE_BREAKPOINT;
		$document.trigger( 'resize.tribeEvents' );
	};

	/**
	 * Handles window resize event
	 *
	 * @since TBD
	 *
	 * @param {Event} event event object for 'resize' event
	 *
	 * @return {void}
	 */
	obj.handleResize = function( event ) {
		obj.setViewport();
	};

	/**
	 * Bind events for window resize
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( $container ) {
		$window.on( 'resize', obj.handleResize );
	};

	/**
	 * Initialize viewport JS
	 *
	 * @since  TBD
	 *
	 * @return {void}
	 */
	obj.init = function() {
		obj.bindEvents();
		obj.setViewport();
	};

	/**
	 * Handles the initialization of viewport when Document is ready
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		obj.init();
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.viewport );
