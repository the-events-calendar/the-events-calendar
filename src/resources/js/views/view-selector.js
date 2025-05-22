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
 * Configures Views Object in the Global Tribe variable.
 *
 * @since 4.9.4
 *
 * @type   {PlainObject}
 */
window.tribe.events.views.viewSelector = window.tribe.events.views.viewSelector || {};

/**
 * Initializes in a Strict env the code that manages the Event Views.
 *
 * @since 4.9.4
 *
 * @param {PlainObject} $   jQuery
 * @param {PlainObject} obj window.tribe.events.views.viewSelector
 *
 * @return {void}
 */
( function ( $, obj ) {
	'use strict';
	const $document = $( document );

	/**
	 * Selectors used for configuration and setup.
	 *
	 * @since 4.9.7
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		viewSelector: '[data-js="tribe-events-view-selector"]',
		viewSelectorTabsClass: '.tribe-events-c-view-selector--tabs',
		viewSelectorButton: '[data-js="tribe-events-view-selector-button"]',
		viewSelectorButtonActiveClass: '.tribe-events-c-view-selector__button--active',
		viewSelectorListContainer: '[data-js="tribe-events-view-selector-list-container"]',
	};

	/**
	 * Deinitialize accordion based on header and content.
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $header  jQuery Header object.
	 * @param {jQuery} $content jQuery Contents object.
	 *
	 * @return {void}
	 */
	obj.deinitAccordion = function ( $header, $content ) {
		window.tribe.events.views.accordion.deinitAccordion( 0, $header );
		window.tribe.events.views.accordion.deinitAccordionA11yAttrs( $header, $content );
		$content.css( 'display', '' );
	};

	/**
	 * Initialize accordion based on header and content.
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery View container object.
	 * @param {jQuery} $header    jQuery Header object.
	 * @param {jQuery} $content   jQuery Contents object.
	 *
	 * @return {void}
	 */
	obj.initAccordion = function ( $container, $header, $content ) {
		window.tribe.events.views.accordion.initAccordion( $container )( 0, $header );
		window.tribe.events.views.accordion.initAccordionA11yAttrs( $header, $content );
	};

	/**
	 * Deinitialize view selector accordion.
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.deinitViewSelectorAccordion = function ( $container ) {
		const $viewSelectorButton = $container.find( obj.selectors.viewSelectorButton );
		const $viewSelectorListContainer = $container.find( obj.selectors.viewSelectorListContainer );
		obj.deinitAccordion( $viewSelectorButton, $viewSelectorListContainer );
		$viewSelectorButton.removeClass( obj.selectors.viewSelectorButtonActiveClass.className() );
	};

	/**
	 * Initialize view selector accordion.
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.initViewSelectorAccordion = function ( $container ) {
		const $viewSelectorButton = $container.find( obj.selectors.viewSelectorButton );
		const $viewSelectorListContainer = $container.find( obj.selectors.viewSelectorListContainer );
		obj.initAccordion( $container, $viewSelectorButton, $viewSelectorListContainer );
	};

	/**
	 * Initializes view selector state.
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.initState = function ( $container ) {
		const $viewSelector = $container.find( obj.selectors.viewSelector );
		const state = {
			mobileInitialized: false,
			desktopInitialized: false,
		};

		$viewSelector.data( 'tribeEventsState', state );
	};

	/**
	 * Deinitializes view selector.
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.deinitViewSelector = function ( $container ) {
		obj.deinitViewSelectorAccordion( $container );
	};

	/**
	 * Initializes view selector.
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.initViewSelector = function ( $container ) {
		const $viewSelector = $container.find( obj.selectors.viewSelector );

		if ( $viewSelector.length ) {
			const state = $viewSelector.data( 'tribeEventsState' );
			const isTabs = $viewSelector.hasClass( obj.selectors.viewSelectorTabsClass.className() );

			// If view selector is tabs (has 3 or less options)
			if ( isTabs ) {
				const containerState = $container.data( 'tribeEventsState' );
				const isMobile = containerState.isMobile;

				// If viewport is mobile and mobile state is not initialized
				if ( isMobile && ! state.mobileInitialized ) {
					obj.initViewSelectorAccordion( $container );
					state.desktopInitialized = false;
					state.mobileInitialized = true;
					$viewSelector.data( 'tribeEventsState', state );

					// If viewport is desktop and desktop state is not initialized
				} else if ( ! isMobile && ! state.desktopInitialized ) {
					obj.deinitViewSelectorAccordion( $container );
					state.mobileInitialized = false;
					state.desktopInitialized = true;
					$viewSelector.data( 'tribeEventsState', state );
				}

				/**
				 * If view selector is not tabs (has more than 3 options), it is always an accordion.
				 * Check if both mobile and desktop states are initialized.
				 * If mobile and desktop states are not initialized:
				 */
			} else if ( ! state.mobileInitialized && ! state.desktopInitialized ) {
				obj.initViewSelectorAccordion( $container );
				state.desktopInitialized = true;
				state.mobileInitialized = true;
				$viewSelector.data( 'tribeEventsState', state );
			}
		}
	};

	/**
	 * Toggles active class on view selector button.
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event Event object for click event.
	 *
	 * @return {void}
	 */
	obj.handleViewSelectorButtonClick = function ( event ) {
		event.data.target.toggleClass( obj.selectors.viewSelectorButtonActiveClass.className() );
	};

	/**
	 * Handles click event on document.
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event Event object for click event.
	 *
	 * @return {void}
	 */
	obj.handleClick = function ( event ) {
		const isParentViewSelector = Boolean( $( event.target ).closest( obj.selectors.viewSelector ).length );

		if ( ! isParentViewSelector ) {
			const $container = event.data.container;
			const $viewSelector = $container.find( obj.selectors.viewSelector );
			const $viewSelectorButton = $viewSelector.find( obj.selectors.viewSelectorButton );

			/* eslint-disable max-len */
			if ( $viewSelectorButton.hasClass( obj.selectors.viewSelectorButtonActiveClass.className() ) ) {
				const $viewSelectorListContainer = $viewSelector.find( obj.selectors.viewSelectorListContainer );
				$viewSelectorButton.removeClass( obj.selectors.viewSelectorButtonActiveClass.className() );
				window.tribe.events.views.accordion.closeAccordion( $viewSelectorButton, $viewSelectorListContainer );
			}
			/* eslint-enable max-len */
		}
	};

	/**
	 * Handles resize event.
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event Event object for 'resize.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.handleResize = function ( event ) {
		obj.initViewSelector( event.data.container );
	};

	/**
	 * Unbinds events for container.
	 *
	 * @since  4.9.7
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function ( $container ) {
		$document.off( 'click', obj.handleClick );
		$container
			.off( 'resize.tribeEvents', obj.handleResize )
			.find( obj.selectors.viewSelectorButton )
			.off( 'click', obj.handleViewSelectorButtonClick );
	};

	/**
	 * Binds events for container.
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.bindEvents = function ( $container ) {
		const $viewSelectorButton = $container.find( obj.selectors.viewSelectorButton );

		$document.on( 'click', { container: $container }, obj.handleClick );
		$container.on( 'resize.tribeEvents', { container: $container }, obj.handleResize );
		$viewSelectorButton.on( 'click', { target: $viewSelectorButton }, obj.handleViewSelectorButtonClick );
	};

	/**
	 * Deinitialize view selector JS.
	 *
	 * @since 4.9.7
	 *
	 * @param {Event}       event    Event object for 'beforeAjaxSuccess.tribeEvents' event.
	 * @param {jqXHR}       jqXHR    Request object.
	 * @param {PlainObject} settings Settings that this request was made with.
	 *
	 * @return {void}
	 */
	obj.deinit = function ( event, jqXHR, settings ) {
		// eslint-disable-line no-unused-vars
		const $container = event.data.container;
		obj.deinitViewSelector( $container );
		obj.unbindEvents( $container );
		$container.off( 'beforeAjaxSuccess.tribeEvents', obj.deinit );
	};

	/**
	 * Initialize view selector JS.
	 *
	 * @since 4.9.8
	 *
	 * @param {Event}   event      Event object for 'afterSetup.tribeEvents' event.
	 * @param {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event.
	 * @param {jQuery}  $container jQuery View container object.
	 * @param {Object}  data       Data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.init = function ( event, index, $container, data ) {
		// eslint-disable-line no-unused-vars
		const $viewSelector = $container.find( obj.selectors.viewSelector );

		if ( ! $viewSelector.length ) {
			return;
		}

		obj.initState( $container );
		obj.initViewSelector( $container );
		obj.bindEvents( $container );
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.deinit );
	};

	/**
	 * Handles the initialization of the view selector when Document is ready.
	 *
	 * @since 4.9.4
	 *
	 * @return {void}
	 */
	obj.ready = function () {
		$document.on( 'afterSetup.tribeEvents', window.tribe.events.views.manager.selectors.container, obj.init );
	};

	// Configure on document ready.
	$( obj.ready );
} )( jQuery, window.tribe.events.views.viewSelector );
