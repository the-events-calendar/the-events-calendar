/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 5.12.0
 *
 * @type  {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Views Object in the Global Tribe variable
 *
 * @since 5.12.0
 *
 * @type  {PlainObject}
 */
tribe.events.views.icalLinks = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since 5.12.0
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.events.views.icalLinks
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since 5.12.0
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		icalLinks: '.tribe-events-c-subscribe-dropdown',
		icalLinksButton: '.tribe-events-c-subscribe-dropdown__button',
		icalLinksButtonActiveClass: 'tribe-events-c-subscribe-dropdown__button--active',
		icalLinksListContainer: '.tribe-events-c-subscribe-dropdown__content',
		icalLinksListContainerShow: 'tribe-events-c-subscribe-dropdown__content--show',
		icalLinksIcon: '.tribe-events-c-subscribe-dropdown__button-icon',
		icalLinksIconRotate: 'tribe-events-c-subscribe-dropdown__button-icon--rotate',
	};

	/**
	 * Toggles dropdown content visibility and rotates the icon.
	 *
	 * @since 5.12.0
	 * @since TBD - Added logic to hide dropdown content on click.
	 *
	 * @param {Event} event event object for click event
	 *
	 * @return {void}
	 */
	obj.handleIcalLinksButtonClick = function( event ) {
		// Stop event propagation to prevent triggering other click events.
		event.stopPropagation();

		let $button  = $( event.target ).closest( obj.selectors.icalLinksButton );
		let $content = $button.siblings( obj.selectors.icalLinksListContainer );
		let $icon    = $button.find( obj.selectors.icalLinksIcon );

		// Hide all other dropdown content elements.
		$( obj.selectors.icalLinksListContainer ).not( $content ).hide();

		// Remove the rotate class from all other icon elements.
		$( obj.selectors.icalLinksIcon).not( $icon ).removeClass( obj.selectors.icalLinksIconRotate );
		
		// Toggle the rotate class for the current icon element.
		$icon.toggleClass( obj.selectors.icalLinksIconRotate );

		// Toggle the visibility of the current content element.
		$content.toggle();
	};

	/**
	 * Closes dropdown content when clicked outside of the dropdown area.
	 *
	 * @since TBD
	 *
	 * @param {Event} event event object for click event
	 *
	 * @return {void}
	 */
	obj.handleClickOutside = function( event ) {
		// Check whether the clicked element is not a part of the dropdown area.
		if ( ! $( event.target ).closest( obj.selectors.icalLinks ).length ) {
			// Hide all dropdown content elements.
			$( obj.selectors.icalLinksListContainer ).hide();
			// Remove the rotate class from all icon elements.
			$( obj.selectors.icalLinksIcon ).removeClass( obj.selectors.icalLinksIconRotate );
		}
	};

	/**
	 * Binds events for container
	 *
	 * @since 5.12.0
	 *
	 * @param  {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( $container ) { // eslint-disable-line no-unused-vars
		$( document ).on(
			'click focus focus-within',
			obj.selectors.icalLinksButton,
			obj.handleIcalLinksButtonClick
		);

		$( document ).on(
			'click focus focus-within',
			obj.handleClickOutside
		);
	};

	/**
	 * Unbinds events for container
	 *
	 * @since  4.9.7
	 *
	 * @param  {jQuery}  $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function( $container ) {
		$container
			.find( obj.selectors.icalLinksButton )
			.off( 'click', obj.handleIcalLinksButtonClick );

		$( document ).off( 'click', obj.handleClickOutside );
	};

	/**
	 * Deinitialize ical links JS
	 *
	 * @since 5.12.0
	 *
	 * @param  {Event}       event    event object for 'beforeAjaxSuccess.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.deinit = function( event, jqXHR, settings ) { // eslint-disable-line no-unused-vars
		var $container = event.data.container;
		obj.unbindEvents( $container );
		$container.off( 'beforeAjaxSuccess.tribeEvents', obj.deinit );
	};

	/**
	 * Initialize view selector JS
	 *
	 * @since 5.12.0
	 *
	 * @param  {Event}   event      event object for 'afterSetup.tribeEvents' event
	 * @param  {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param  {jQuery}  $container jQuery object of links container
	 * @param  {object}  data       data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) { // eslint-disable-line no-unused-vars
		var $icalLinks = $container.find( obj.selectors.icalLinks );

		if ( ! $icalLinks.length ) {
			return;
		}

		obj.bindEvents( $container );
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.deinit );
	};

	/**
	 * Handles the initialization of the view selector when Document is ready
	 *
	 * @since 5.12.0
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$( document ).on(
			'afterSetup.tribeEvents',
			obj.init
		);
	};

	// Configure on document ready
	$( obj.ready );

} )( jQuery, tribe.events.views.icalLinks );
