/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since  TBD
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Views Object in the Global Tribe variable
 *
 * @since  TBD
 *
 * @type   {PlainObject}
 */
tribe.events.views.scripts = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since  TBD
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} _   Underscore.js
 * @param  {PlainObject} obj tribe.events.views.scripts
 *
 * @return {void}
 */
( function( $, _, obj ) {
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
		tooltip: '.tribe-events-tooltip',
		tooltipContent: '.tribe-events-tooltip__content',
	};

	/**
	 * Initialize accessible tooltips via tooltipster
	 *
	 * @since TBD
	 *
	 */
	obj.initTooltips = function() {

		$( obj.selectors.tooltip ).tooltipster( {
			interactive: true,
			theme: [ 'tribe-common', 'tribe-events', 'tribe-events-tooltip-theme' ],
			functionInit: function( instance, helper ) {
				var content = $( helper.origin ).find( obj.selectors.tooltipContent ).html();
				instance.content( content );
				$( helper.origin )
					.focus( function() {
						$( this ).tooltipster( 'open' );
					})
					.blur( function() {
						$( this ).tooltipster( 'close' );
				});
			},
			functionReady: function( instance, helper ) {
				$( helper.origin ).find( obj.selectors.tooltipContent ).attr( 'aria-hidden', false );
			},
			functionAfter: function( instance, helper ) {
				$( helper.origin ).find( obj.selectors.tooltipContent ).attr( 'aria-hidden', true );
			}
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

		obj.initTooltips();

	};

	// Configure on document ready
	$document.ready( obj.ready );
}( jQuery, window.underscore || window._, tribe.events.views.scripts ) );
