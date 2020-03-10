/* globals jQuery, tribe */
/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 5.0.0
 *
 * @type   {Object}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Breakpoints Object in the Global Tribe variable
 *
 * @since 5.0.0
 *
 * @type   {Object}
 */
tribe.events.views.breakpoints = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since  5.0.0
 *
 * @param  {FunctionConstructor} $   jQuery
 * @param  {Object} obj tribe.events.views.breakpoints
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	var $document = $( document );

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since 5.0.0
	 *
	 * @type {Object}
	 */
	obj.selectors = {
		container: '[data-js="tribe-events-view"]',
		dataScript: '[data-js="tribe-events-view-data"]',
		breakpointClassPrefix: 'tribe-common--breakpoint-',
	};

	/**
	 * Object of breakpoints
	 *
	 * @since 5.0.0
	 *
	 * @type {Object}
	 */
	obj.breakpoints = {};

	/**
	 * Sets container classes based on breakpoint
	 *
	 * @since  5.0.0
	 *
	 * @param  {jQuery}  $container jQuery object of view container.
	 * @param  {object}  data       data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.setContainerClasses = function( $container, data ) {
		var breakpoints = Object.keys( data.breakpoints );

		breakpoints.forEach( function( breakpoint ) {
			var className = obj.selectors.breakpointClassPrefix + breakpoint;
			obj.breakpoints[ breakpoint ] = data.breakpoints[ breakpoint ];

			if ( $container.outerWidth() < data.breakpoints[ breakpoint ] ) {
				$container.removeClass( className );
			} else {
				$container.addClass( className );
			}
		} );
	};

	/**
	 * Handles resize event for window
	 *
	 * @since  5.0.0
	 *
	 * @param  {Event} event event object for 'resize' event
	 *
	 * @return {void}
	 */
	obj.handleResize = function( event ) {
		obj.setContainerClasses( event.data.container, event.data.data );
	};

	/**
	 * Unbinds events for container
	 *
	 * @since  5.0.0
	 *
	 * @param  {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function( $container ) {
		$container
			.off( 'resize.tribeEvents', obj.handleResize )
			.off( 'beforeAjaxSuccess.tribeEvents', obj.deinit );
	};

	/**
	 * Binds events for container
	 *
	 * @since  5.0.0
	 *
	 * @param  {jQuery}  $container jQuery object of view container.
	 * @param  {object}  data       data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( $container, data ) {
		$container
			.on( 'resize.tribeEvents', { container: $container, data: data }, obj.handleResize )
			.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.deinit );
	};

	/**
	 * De-initialize breakpoints JS
	 *
	 * @since  5.0.0
	 *
	 * @param  {Event}       event    event object for 'beforeAjaxSuccess.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {Object} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.deinit = function( event, jqXHR, settings ) {
		obj.unbindEvents( event.data.container );
	};

	/**
	 * Common initialization tasks
	 *
	 * @since  5.0.0
	 *
	 * @param  {jQuery}  $container jQuery object of view container.
	 * @param  {object}  data       data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.initTasks = function( $container, data ) {
		if ( ! ( $container instanceof jQuery ) ) {
			// eslint-disable-next-line no-param-reassign
			$container = $( $container );
		}

		obj.bindEvents( $container, data );
		obj.setContainerClasses( $container, data );

		var state = { initialized: true };
		$container.data( 'tribeEventsBreakpoints', state );
	};

	/**
	 * Initialize breakpoints JS
	 *
	 * @since  5.0.0
	 *
	 * @param  {Event}   event      event object for 'afterSetup.tribeEvents' event
	 * @param  {int}     index      jQuery.each index param from 'afterSetup.tribeEvents' event.
	 * @param  {jQuery}  $container jQuery object of view container.
	 * @param  {object}  data       data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		if ( ! ( $container instanceof jQuery ) ) {
			// eslint-disable-next-line no-param-reassign
			$container = $( $container );
		}

		var state = $container.data( 'tribeEventsBreakpoints' );
		if ( state && state.initialized ) {
			return;
		}

		obj.initTasks( $container, data );
	};

	/**
	 * Setup breakpoints JS
	 *
	 * @since  5.0.0
	 *
	 * @param  {HTMLElement} container HTML element of the script tag calling setup
	 *
	 * @return {void}
	 */
	obj.setup = function( container ) {
		var $container = $( container );

		if ( ! $container.is( obj.selectors.container ) ) {
			return;
		}
		var $data = $container.find( obj.selectors.dataScript );
		var data = {};

		// If we have data element set it up.
		if ( $data.length ) {
			data = JSON.parse( $.trim( $data.text() ) );
		}

		obj.initTasks( $container, data );
	};

	/**
	 * Handles the initialization of breakpoints when Document is ready
	 *
	 * @since  5.0.0
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on( 'afterSetup.tribeEvents', obj.selectors.container, obj.init );
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.breakpoints );
