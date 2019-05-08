/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since  TBD
 *
 * @type   {Object}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Views Object in the Global Tribe variable
 *
 * @since  TBD
 *
 * @type   {Object}
 */
tribe.events.views.manager = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since  TBD
 *
 * @param  {Object} $   jQuery
 * @param  {Object} _   Underscore.js
 * @param  {Object} obj tribe.events.views.manager
 *
 * @return void
 */
( function( $, _, obj ) {
	'use strict';
	var $document = $( document );

	obj.selectors = {
		container: '.tribe-events-container'
	};

	obj.$containers = $();

	obj.setup = function( index, container ) {

	};

	obj.getAjaxSettings = function( container ) {
		let ajaxSettings = {
			accepts: 'html',
			dataType: 'html',
			method: 'GET',
			'async': true, // async is keywork
			beforeSend: obj.ajaxBeforeSend,
			complete: obj.ajaxComplete,
			success: obj.ajaxSuccess,
			error: obj.ajaxError,
			context: container,
		};

		return ajaxSettings;
	};

	/**
	 * Handles the initialization of the manager when Document is ready
	 *
	 * @since  TBD
	 *
	 * @return {function}
	 */
	obj.ready = function() {
		obj.$containers = $( obj.selectors.container );
		obj.$containers.each( obj.setup );
	};

	// Configure on document ready
	$document.ready( obj.ready );
}( jQuery, window.underscore || window._, tribe.events.views.manager ) );
