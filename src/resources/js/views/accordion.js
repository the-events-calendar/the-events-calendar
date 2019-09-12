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
 * Configures Accordion Object in the Global Tribe variable
 *
 * @since 4.9.4
 *
 * @type   {PlainObject}
 */
tribe.events.views.accordion = {};

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
		accordionTrigger: '[data-js="tribe-events-accordion-trigger"]',
	};

	/**
	 * Sets open accordion accessibility attributes
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $header jQuery object of header
	 * @param {jQuery} $content jQuery object of contents
	 *
	 * @return {void}
	 */
	obj.setOpenAccordionA11yAttrs = function( $header, $content ) {
		$header
			.attr( 'aria-expanded', 'true' )
			.attr( 'aria-selected', 'true' );
		$content
			.attr( 'aria-hidden', 'false' );
	};

	/**
	 * Sets close accordion accessibility attributes
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $header jQuery object of header
	 * @param {jQuery} $content jQuery object of contents
	 *
	 * @return {void}
	 */
	obj.setCloseAccordionA11yAttrs = function( $header, $content ) {
		$header
			.attr( 'aria-expanded', 'false' )
			.attr( 'aria-selected', 'false' );
		$content
			.attr( 'aria-hidden', 'true' );
	};

	/**
	 * Closes all accordions in $container
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.closeAllAccordions = function( $container ) {
		$container.find( obj.selectors.accordionTrigger ).each( function( index, header ) {
			var $header = $( header );
			var contentId = $header.attr( 'aria-controls' );
			var $content = $document.find( '#' + contentId );

			obj.closeAccordion( $header, $content );
		} );
	};

	/**
	 * Opens accordion
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $header jQuery object of header
	 * @param {jQuery} $content jQuery object of contents
	 *
	 * @return {void}
	 */
	obj.openAccordion = function( $header, $content ) {
		obj.setOpenAccordionA11yAttrs( $header, $content );
		$content.css( 'display', 'block' );
	};

	/**
	 * Closes accordion
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $header jQuery object of header
	 * @param {jQuery} $content jQuery object of contents
	 *
	 * @return {void}
	 */
	obj.closeAccordion = function( $header, $content ) {
		obj.setCloseAccordionA11yAttrs( $header, $content );
		$content.css( 'display', '' );
	};

	/**
	 * Toggles accordion on header click
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object of click event
	 *
	 * @return {void}
	 */
	obj.toggleAccordion = function( event ) {
		var $container = event.data.container;
		var $header = $( event.data.target );
		var contentId = $header.attr( 'aria-controls' );
		var $content = $container.find( '#' + contentId );

		if ( 'true' === $header.attr( 'aria-expanded' ) ) {
			obj.closeAccordion( $header, $content );
		} else {
			obj.openAccordion( $header, $content );
		}
	};

	/**
	 * Deinitializes accordion accessibility attributes
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $header jQuery object of header
	 * @param {jQuery} $content jQuery object of contents
	 *
	 * @return {void}
	 */
	obj.deinitAccordionA11yAttrs = function( $header, $content ) {
		$header
			.removeAttr( 'aria-expanded' )
			.removeAttr( 'aria-selected' )
			.removeAttr( 'aria-controls' );
		$content
			.removeAttr( 'aria-hidden' );
	};

	/**
	 * Initializes accordion accessibility attributes
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $header jQuery object of header
	 * @param {jQuery} $content jQuery object of contents
	 *
	 * @return {void}
	 */
	obj.initAccordionA11yAttrs = function( $header, $content ) {
		$header
			.attr( 'aria-expanded', 'false' )
			.attr( 'aria-selected', 'false' )
			.attr( 'aria-controls', $content.attr( 'id' ) );
		$content.attr( 'aria-hidden', 'true' );
	};

	/**
	 * Deinitializes accordion
	 *
	 * @since 4.9.4
	 *
	 * @param {integer} index jQuery.each index param
	 * @param {HTMLElement} header header element from which to remove event
	 *
	 * @return {void}
	 */
	obj.deinitAccordion = function( index, header ) {
		$( header ).off( 'click', obj.toggleAccordion );
	};

	/**
	 * Initializes accordion
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {function} function to add event listener to header
	 */
	obj.initAccordion = function( $container ) {
		return function( index, header ) {
			$( header ).on( 'click', { target: header, container: $container }, obj.toggleAccordion );
		};
	};

	/**
	 * Unbinds events for accordion click listeners
	 *
	 * @since  4.9.5
	 *
	 * @param  {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.unbindAccordionEvents = function( $container ) {
		$container
			.find( obj.selectors.accordionTrigger )
			.each( obj.deinitAccordion );
	};

	/**
	 * Binds events for accordion click listeners
	 *
	 * @since 4.9.4
	 *
	 * @param  {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.bindAccordionEvents = function( $container ) {
		$container
			.find( obj.selectors.accordionTrigger )
			.each( obj.initAccordion( $container ) );
	};

	/**
	 * Unbinds events for container
	 *
	 * @since  4.9.5
	 *
	 * @param  {Event}       event    event object for 'beforeAjaxSuccess.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function( event, jqXHR, settings ) {
		var $container = event.data.container;
		obj.unbindAccordionEvents( $container );
	};

	/**
	 * Binds events for container
	 *
	 * @since  4.9.5
	 *
	 * @param  {Event}   event      event object for 'afterSetup.tribeEvents' event
	 * @param  {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param  {jQuery}  $container jQuery object of view container
	 * @param  {object}  data       data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( event, index, $container, data ) {
		obj.bindAccordionEvents( $container );
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.unbindEvents );
	};

	/**
	 * Handles the initialization of the accordions when Document is ready
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
} )( jQuery, tribe.events.views.accordion );
