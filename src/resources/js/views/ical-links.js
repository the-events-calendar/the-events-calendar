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
		icalLinksButtonText: '.tribe-events-c-subscribe-dropdown__button-text',
		icalLinksButtonActiveClass: 'tribe-events-c-subscribe-dropdown__button--active',
		icalLinksListContainer: '.tribe-events-c-subscribe-dropdown__content',
		icalLinksListContainerShow: 'tribe-events-c-subscribe-dropdown__content--show',
		icalLinksIcon: '.tribe-events-c-subscribe-dropdown__button-icon',
		icalLinksIconRotate: 'tribe-events-c-subscribe-dropdown__button-icon--rotate',
	};

	/**
	 * Toggles active class on view selector button
	 *
	 * @since 5.12.0
	 * @since 6.0.13 - Added logic to toggle dropdown content visibility and icon rotation.
	 *
	 * @param {Event} event event object for click event
	 *
	 * @return {void}
	 */
	obj.handleIcalLinksButtonClick = function( event ) {
		// Stop event propagation to prevent triggering other click events.
		event.stopPropagation();
		var $button     = $( event.target ).closest( obj.selectors.icalLinksButton );
		var $content    = $button.siblings( obj.selectors.icalLinksListContainer );
		var $icon       = $button.find( obj.selectors.icalLinksIcon );

		obj.handleAccordionToggle( event );

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
	 * Handles the pre-toggle logic for the accordion.
	 *
	 * @since 6.2.1
	 *
	 * @param {event} event The triggering event object.
	 */
	obj.handleAccordionToggle = function( event ) {
		var $button     = $( event.target ).closest( obj.selectors.icalLinksButton );
		var $buttonText = $button.find( obj.selectors.icalLinksButtonText );

		if ( ! $button ) {
			return;
		}

		if ( ! $buttonText) {
			return;
		}

		// Toggle the active class for the button element.
		obj.handleToggleAccordionExpanded( $buttonText );

	}

	/**
	 * Handles the toggling of classes and attributes for the accordion.
	 *
	 * @since 6.2.1
	 *
	 * @param {object} $ele The jQuery object of the toggle button.
	 */
	obj.handleToggleAccordionExpanded = function( $ele ) {
		// Toggle the aria-expanded attribute and class for the button element.
		var $expanded = $ele.attr( 'aria-expanded' );

		if ( 'true' === $expanded ) {
			// Set aria attribute on button to false.
			$ele.attr( 'aria-expanded', false );
			// Remove the rotate class from the icon element.
			$( obj.selectors.icalLinksIcon ).removeClass( obj.selectors.icalLinksIconRotate );
		} else {
			// Set aria attribute on button to true.
			$ele.attr( 'aria-expanded', true );
			// Add the rotate class to the icon element.
			$( obj.selectors.icalLinksIcon ).addClass( obj.selectors.icalLinksIconRotate );
		}
	}

	/**
	 * Resets all dropdown content elements to their default state.
	 *
	 * @since 6.2.1
	 */
	obj.resetAccordions = function() {
		// Hide all dropdown content elements.
		$( obj.selectors.icalLinksListContainer ).hide();
		// Fix aria attributes on button.
		$( obj.selectors.icalLinksButtonText ).attr( 'aria-expanded', false );
		// Remove the rotate class from all icon elements.
		$( obj.selectors.icalLinksIcon ).removeClass( obj.selectors.icalLinksIconRotate );
	}

	/**
	 * Closes dropdown content when clicked outside of the dropdown area.
	 *
	 * @since 6.0.13
	 *
	 * @param {Event} event event object for click event
	 *
	 * @return {void}
	 */
	obj.handleClickOutside = function( event ) {
		// Check whether the clicked element is a part of the dropdown area.
		if ( $( event.target ).closest( obj.selectors.icalLinks ).length ) {
			// If so, bail.
			return;
		}

		// Reset all dropdown content elements.
		obj.resetAccordions();
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
			'click',
			obj.selectors.icalLinksButton,
			obj.handleIcalLinksButtonClick
		);

		$( document ).on(
			'click, focusin',
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
	 * @since 6.0.13 - Added logic to ensure that the 'init' method of the 'obj' object is
	 *              called when the document is fully loaded and ready for manipulation.
	 *
	 * @return {void}
	 */
	$( document ).ready( function() {
		obj.init( null, 0, $( 'body' ), {} );
	});

	// Configure on document ready
	$( obj.ready );

} )( jQuery, tribe.events.views.icalLinks );
