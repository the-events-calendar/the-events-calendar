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
	 * Set viewport state for container
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.setViewport = function( $container ) {
		var state = $container.data( 'tribeEventsState' );

		if ( ! state ) {
			state = {};
		}

		state.isMobile = $container.outerWidth() < obj.options.MOBILE_BREAKPOINT;
		$container.data( 'tribeEventsState', state );
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
		var $container = event.data.container;
		obj.setViewport( $container );
		$container.trigger( 'resize.tribeEvents' );
	};

	/**
	 * Unbind events for window resize
	 *
	 * @since 5.0.0
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function( $container ) {
		$window.off( 'resize', obj.handleResize );
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
	 * Deinitialize viewport JS
	 *
	 * @since  5.0.0
	 *
	 * @param  {Event}       event    event object for 'beforeAjaxSuccess.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.deinit = function( event, jqXHR, settings ) {
		var $container = event.data.container;
		obj.unbindEvents( $container );
		$container.off( 'beforeAjaxSuccess.tribeEvents', obj.deinit );
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
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.deinit );
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
