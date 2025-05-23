/**
 * Makes sure we have all the required levels on the Tribe Object.
 *
 * @since 4.9.4
 *
 * @type   {PlainObject}
 */
window.tribe.events = window.tribe.events || {};
window.tribe.events.views = window.tribe.events.views || {};

/**
 * Configures Events Bar Inputs Object in the Global Tribe variable.
 *
 * @since 4.9.4
 *
 * @type   {PlainObject}
 */
window.tribe.events.views.eventsBarInputs = window.tribe.events.views.eventsBarInputs || {};

/**
 * Initializes in a Strict env the code that manages the Event Views.
 *
 * @since 4.9.4
 *
 * @param {PlainObject} $   jQuery.
 * @param {PlainObject} obj window.tribe.events.views.eventsBarInputs.
 *
 * @return {void}
 */
( function ( $, obj ) {
	'use strict';
	const $document = $( document );

	/**
	 * Selectors used for configuration and setup.
	 *
	 * @since 4.9.4
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		input: '[data-js="tribe-events-events-bar-input-control-input"]',
		inputWrapper: '[data-js="tribe-events-events-bar-input-control"]',
	};

	/**
	 * Toggles input class if it has a value.
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event Event object of click event.
	 *
	 * @return {void}
	 */
	obj.handleInputChange = function ( event ) {
		const $input = event.data.target;
		const $wrapper = event.data.wrapper;

		// Set the focus class if it has content.
		$wrapper.toggleClass( event.data.inputClassFocus, '' !== $input.val().trim() );
	};

	/**
	 * Unbind events for the events bar inputs.
	 *
	 * @since 4.9.5
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.unbindInputEvents = function ( $container ) {
		$container.find( obj.selectors.inputWrapper ).each( function ( index, wrapper ) {
			const $input = $( wrapper ).find( obj.selectors.input );

			// Bail in case we don't find the input.
			if ( ! $input.length ) {
				return;
			}

			$input.off();
		} );
	};

	/**
	 * Bind events for the events bar inputs, on focus and according to their value.
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.bindInputEvents = function ( $container ) {
		$container.find( obj.selectors.inputWrapper ).each( function ( index, wrapper ) {
			const inputWrapperClass = wrapper.className.match( /tribe-events-c-search__input-control--[a-z]+/ );

			if ( ! inputWrapperClass ) {
				return;
			}

			const inputWrapperFocus = inputWrapperClass[ 0 ] + '-focus';
			const $wrapper = $( wrapper );
			const $input = $wrapper.find( obj.selectors.input );

			// Bail in case we don't find the input.
			if ( ! $input.length ) {
				return;
			}

			$wrapper.toggleClass( inputWrapperFocus, '' !== $input.val().trim() );

			$input.on(
				'change',
				{ target: $input, wrapper: $wrapper, inputClassFocus: inputWrapperFocus },
				obj.handleInputChange
			);
		} );
	};

	/**
	 * Unbinds events for container.
	 *
	 * @since  4.9.5
	 *
	 * @param {Event}       event    Event object for 'beforeAjaxSuccess.tribeEvents' event.
	 * @param {jqXHR}       jqXHR    Request object.
	 * @param {PlainObject} settings Settings that this request was made with.
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function ( event, jqXHR, settings ) {
		// eslint-disable-line no-unused-vars
		const $container = event.data.container;
		obj.unbindInputEvents( $container );
		$container.off( 'beforeAjaxSuccess.tribeEvents', obj.unbindEvents );
	};

	/**
	 * Binds events for the events bar input change listeners.
	 *
	 * @since 4.9.8
	 *
	 * @param {Event}   event      Event object for 'afterSetup.tribeEvents' event.
	 * @param {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event.
	 * @param {jQuery}  $container jQuery object of view container.
	 * @param {Object}  data       Data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.bindEvents = function ( event, index, $container, data ) {
		// eslint-disable-line no-unused-vars, max-len
		const $inputWrapper = $container.find( obj.selectors.inputWrapper );

		if ( ! $inputWrapper.length ) {
			return;
		}

		obj.bindInputEvents( $container );
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.unbindEvents );
	};

	/**
	 * Handles the initialization of the Events Bar Inputs when Document is ready.
	 *
	 * @since 4.9.4
	 *
	 * @return {void}
	 */
	obj.ready = function () {
		$document.on( 'afterSetup.tribeEvents', window.tribe.events.views.manager.selectors.container, obj.bindEvents );
	};

	// Configure on document ready.
	$( obj.ready );
} )( jQuery, window.tribe.events.views.eventsBarInputs );
