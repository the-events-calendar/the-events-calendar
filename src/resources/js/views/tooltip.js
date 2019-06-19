/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since  TBD
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};
tribe.events.views.manager = tribe.events.views.manager || {};

/**
 * Configures Views Tooltip Object in the Global Tribe variable
 *
 * @since  TBD
 *
 * @type   {PlainObject}
 */
tribe.events.views.tooltip = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since  TBD
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
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		tooltip: '[data-js="tribe-events-tooltip"]',
		tooltipContent: '[data-js="tribe-events-tooltip-content"]',
	};

	/**
	 * Override of the `functionInit` tooltipster method.
	 *
	 * A custom function to be fired only once at instantiation.
	 *
	 * @since TBD
	 *
	 */
	obj.onFunctionInit = function( instance, helper ) {

		var content = $( helper.origin ).find( obj.selectors.tooltipContent ).html();
		instance.content( content );
		$( helper.origin )
			.focus( function() {
				obj.onOriginFocus( $( this ) )
			})
			.blur( function() {
				obj.onOriginBlur( $( this ) )
			});
	};

	/**
	 * On tooltip focus
	 *
	 * @since TBD
	 *
	 */
	obj.onOriginFocus = function( el ) {
		el.tooltipster( 'open' );
	};

	/**
	 * On tooltip blur
	 *
	 * @since TBD
	 *
	 */
	obj.onOriginBlur = function( el ) {
		el.tooltipster( 'close' );
	};

	/**
	 * Override of the `functionReady` tooltipster method.
	 *
	 * A custom function to be fired when the tooltip and its contents have been added to the DOM.
	 *
	 * @since TBD
	 *
	 */
	obj.onFunctionReady = function( instance, helper ) {

		$( helper.origin ).find( obj.selectors.tooltipContent ).attr( 'aria-hidden', false );
	};

	/**
	 * Override of the `functionAfter` tooltipster method.
	 *
	 * A custom function to be fired once the tooltip has been closed and removed from the DOM.
	 *
	 * @since TBD
	 *
	 */
	obj.onFunctionAfter = function( instance, helper ) {

		$( helper.origin ).find( obj.selectors.tooltipContent ).attr( 'aria-hidden', true );
	};

	/**
	 * Initialize accessible tooltips via tooltipster
	 *
	 * @since TBD
	 *
	 * @param {Event}   event      JS event triggered.
	 * @param {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event.
	 * @param {jQuery}  $container jQuery object of view container.
	 * @param {object}  data       data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.initTooltips = function( event, index, $container, data ) {
		$container.find( obj.selectors.tooltip ).each( function( index, tooltip ) {
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
	 * Handles the initialization of the scripts when Document is ready
	 *
	 * @since  TBD
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		// @todo: make it work with variable instead of function, so it's triggered how's supposed to be
		$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.initTooltips );
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.tooltip );
