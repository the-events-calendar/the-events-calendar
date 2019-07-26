/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since  4.9.4
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};
tribe.events.views.manager = tribe.events.views.manager || {};

/**
 * Configures Views Tooltip Object in the Global Tribe variable
 *
 * @since  4.9.4
 *
 * @type   {PlainObject}
 */
tribe.events.views.tooltip = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since  4.9.4
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.events.views.tooltip
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
		tooltip: '[data-js="tribe-events-tooltip"]',
		tooltipContent: '[data-js="tribe-events-tooltip-content"]',
	};

	/**
	 * Override of the `functionInit` tooltipster method.
	 * A custom function to be fired only once at instantiation.
	 *
	 * @since 4.9.4
	 *
	 * @param {Tooltipster} instance instance of Tooltipster
	 * @param {PlainObject} helper   helper object with tooltip origin
	 *
	 * @return {void}
	 */
	obj.onFunctionInit = function( instance, helper ) {
		var $origin = $( helper.origin );
		var content = $origin.find( obj.selectors.tooltipContent ).html();
		instance.content( content );
		$origin
			.on( 'focus', { target: $origin }, obj.handleOriginFocus )
			.on( 'blur', { target: $origin }, obj.handleOriginBlur );
	};

	/**
	 * Handle tooltip focus event
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.handleOriginFocus = function( event ) {
		event.data.target.tooltipster( 'open' );
	};

	/**
	 * Handle tooltip blur event
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.handleOriginBlur = function( event ) {
		event.data.target.tooltipster( 'close' );
	};

	/**
	 * Override of the `functionReady` tooltipster method.
	 * A custom function to be fired when the tooltip and its contents have been added to the DOM.
	 *
	 * @since 4.9.4
	 *
	 * @param {Tooltipster} instance instance of Tooltipster
	 * @param {PlainObject} helper   helper object with tooltip origin
	 *
	 * @return {void}
	 */
	obj.onFunctionReady = function( instance, helper ) {
		$( helper.origin ).find( obj.selectors.tooltipContent ).attr( 'aria-hidden', 'false' );
	};

	/**
	 * Override of the `functionAfter` tooltipster method.
	 * A custom function to be fired once the tooltip has been closed and removed from the DOM.
	 *
	 * @since 4.9.4
	 *
	 * @param {Tooltipster} instance instance of Tooltipster
	 * @param {PlainObject} helper   helper object with tooltip origin
	 *
	 * @return {void}
	 */
	obj.onFunctionAfter = function( instance, helper ) {
		$( helper.origin ).find( obj.selectors.tooltipContent ).attr( 'aria-hidden', 'true' );
	};

	/**
	 * Deinitialize accessible tooltips via tooltipster
	 *
	 * @since 4.9.5
	 *
	 * @param {jQuery} $container jQuery object of view container.
	 *
	 * @return {void}
	 */
	obj.deinitTooltips = function( $container ) {
		$container
			.find( obj.selectors.tooltip )
			.each( function( index, tooltip ) {
				$( tooltip )
					.off( 'focus', obj.handleOriginFocus )
					.off( 'blur', obj.handleOriginBlur );
			} );
	};

	/**
	 * Initialize accessible tooltips via tooltipster
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container.
	 *
	 * @return {void}
	 */
	obj.initTooltips = function( $container ) {
		$container
			.find( obj.selectors.tooltip )
			.each( function( index, tooltip ) {
				$( tooltip ).tooltipster( {
					interactive: true,
					theme: [ 'tribe-common', 'tribe-events', 'tribe-events-tooltip-theme' ],
					functionInit: obj.onFunctionInit,
					functionReady: obj.onFunctionReady,
					functionAfter: obj.onFunctionAfter,
				} );
			} );
	};

	/**
	 * Deinitialize tooltip JS.
	 *
	 * @since 4.9.5
	 *
	 * @param  {Event}       event    event object for 'afterSetup.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.deinit = function( event, jqXHR, settings ) {
		var $container = event.data.container;
		obj.deinitTooltips( $container );
	};

	/**
	 * Initialize tooltips JS.
	 *
	 * @since 4.9.5
	 *
	 * @param {Event}   event      JS event triggered.
	 * @param {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event.
	 * @param {jQuery}  $container jQuery object of view container.
	 * @param {object}  data       data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		obj.initTooltips( $container );
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.deinit );
	};

	/**
	 * Handles the initialization of the scripts when Document is ready
	 *
	 * @since  4.9.4
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.init );
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.tooltip );
