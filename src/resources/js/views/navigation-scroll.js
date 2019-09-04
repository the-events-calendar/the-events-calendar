tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Views navigation scroller Object in the Global Tribe variable
 *
 * @since  4.9.8
 *
 * @type   {PlainObject}
 */
tribe.events.views.navigationScroll = {};

/**
 * Initializes in a Strict env the code that scrolling when navigation happens
 * on the new Manager for Views V2.
 *
 * @since  4.9.8
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.events.views.navigationScroll
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	var $document = $( document );
	var $window = $( window );

	/**
	 * When we have an AJAX Success scroll up when the window is below 25% of the container position.
	 *
	 * @since 4.9.5
	 *
	 * @param  {Event}   event      event object for 'afterSetup.tribeEvents' event
	 * @param  {String} html       HTML sent from the REST API
	 * @param  {String} textStatus Status for the request
	 * @param  {jqXHR}  qXHR       Request object
	 *
	 * @return {void}
	 */
	obj.scrollUp = function( event, html, textStatus, qXHR ) {
		var $container = $( event.target );
		var windowTop = $window.scrollTop();
		var containerOffset = $container.offset();
		var scrollTopRequirement = windowTop * 0.75;

		if ( scrollTopRequirement > containerOffset.top ) {
			$window.scrollTop( containerOffset.top );
		}
	};

	/**
	 * Handles the initialization of the scripts when Document is ready
	 *
	 * @since  4.9.4
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on( 'afterAjaxSuccess.tribeEvents', tribe.events.views.manager.selectors.container, obj.scrollUp );
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.navigationScroll );
