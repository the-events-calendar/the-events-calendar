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
 * Configures Events Bar Object in the Global Tribe variable.
 *
 * @since 4.9.4
 *
 * @type   {PlainObject}
 */
window.tribe.events.views.eventsBar = window.tribe.events.views.eventsBar || {};

/**
 * Initializes in a Strict env the code that manages the Event Views.
 *
 * @since 4.9.4
 *
 * @param {PlainObject} $   jQuery.
 * @param {PlainObject} obj window.tribe.events.views.eventsBar.
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
		eventsBar: '[data-js="tribe-events-events-bar"]',
		searchButton: '[data-js="tribe-events-search-button"]',
		searchButtonActiveClass: '.tribe-events-c-events-bar__search-button--active',
		searchContainer: '[data-js="tribe-events-search-container"]',
	};

	/**
	 * Object of key codes.
	 *
	 * @since 4.9.4
	 *
	 * @type {PlainObject}
	 */
	obj.keyCode = {
		END: 35,
		HOME: 36,
		LEFT: 37,
		RIGHT: 39,
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
	 * Toggles active class on search button.
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event Event object for click event.
	 *
	 * @return {void}
	 */
	obj.handleSearchButtonClick = function ( event ) {
		event.data.target.toggleClass( obj.selectors.searchButtonActiveClass.className() );
	};

	/**
	 * Deinitialize search button accordion.
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.deinitSearchAccordion = function ( $container ) {
		const $searchButton = $container.find( obj.selectors.searchButton );
		$searchButton.removeClass( obj.selectors.searchButtonActiveClass.className() );
		const $searchContainer = $container.find( obj.selectors.searchContainer );
		obj.deinitAccordion( $searchButton, $searchContainer );
		$searchButton.off( 'click', obj.handleSearchButtonClick );
	};

	/**
	 * Initialize search button accordion.
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.initSearchAccordion = function ( $container ) {
		const $searchButton = $container.find( obj.selectors.searchButton );
		const $searchContainer = $container.find( obj.selectors.searchContainer );
		obj.initAccordion( $container, $searchButton, $searchContainer );
		$searchButton.on( 'click', { target: $searchButton }, obj.handleSearchButtonClick );
	};

	/**
	 * Initializes events bar state.
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.initState = function ( $container ) {
		const $eventsBar = $container.find( obj.selectors.eventsBar );
		const state = {
			mobileInitialized: false,
			desktopInitialized: false,
		};

		$eventsBar.data( 'tribeEventsState', state );
	};

	/**
	 * Deinitialize events bar.
	 *
	 * @since 4.9.5
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.deinitEventsBar = function ( $container ) {
		obj.deinitSearchAccordion( $container );
	};

	/**
	 * Initializes events bar.
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.initEventsBar = function ( $container ) {
		const $eventsBar = $container.find( obj.selectors.eventsBar );

		if ( $eventsBar.length ) {
			const state = $eventsBar.data( 'tribeEventsState' );
			const containerState = $container.data( 'tribeEventsState' );
			const isMobile = containerState.isMobile;

			// If viewport is mobile and mobile state is not initialized
			if ( isMobile && ! state.mobileInitialized ) {
				obj.initSearchAccordion( $container );
				state.desktopInitialized = false;
				state.mobileInitialized = true;
				$eventsBar.data( 'tribeEventsState', state );

				// If viewport is desktop and desktop state is not initialized
			} else if ( ! isMobile && ! state.desktopInitialized ) {
				obj.deinitSearchAccordion( $container );
				state.mobileInitialized = false;
				state.desktopInitialized = true;
				$eventsBar.data( 'tribeEventsState', state );
			}
		}
	};

	/**
	 * Handles 'resize.tribeEvents' event.
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event Event object for 'resize.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.handleResize = function ( event ) {
		obj.initEventsBar( event.data.container );
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
		const $target = $( event.target );
		const isParentSearchButton = Boolean( $target.closest( obj.selectors.searchButton ).length );
		const isParentSearchContainer = Boolean( $target.closest( obj.selectors.searchContainer ).length ); // eslint-disable-line max-len

		if ( ! ( isParentSearchButton || isParentSearchContainer ) ) {
			const $container = event.data.container;
			const $eventsBar = $container.find( obj.selectors.eventsBar );
			const $searchButton = $eventsBar.find( obj.selectors.searchButton );

			if ( $searchButton.hasClass( obj.selectors.searchButtonActiveClass.className() ) ) {
				const $searchContainer = $eventsBar.find( obj.selectors.searchContainer );
				$searchButton.removeClass( obj.selectors.searchButtonActiveClass.className() );
				window.tribe.events.views.accordion.closeAccordion( $searchButton, $searchContainer );
			}
		}
	};

	/**
	 * Unbind events for events bar.
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function ( $container ) {
		$container.off( 'resize.tribeEvents', obj.handleResize );
		$document.off( 'click', obj.handleClick );
	};

	/**
	 * Bind events for events bar.
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery View container object.
	 *
	 * @return {void}
	 */
	obj.bindEvents = function ( $container ) {
		$container.on( 'resize.tribeEvents', { container: $container }, obj.handleResize );
		$document.on( 'click', { container: $container }, obj.handleClick );
	};

	/**
	 * Deinitialize events bar JS.
	 *
	 * @since 4.9.4
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
		obj.deinitEventsBar( $container );
		obj.unbindEvents( $container );
		$container.off( 'beforeAjaxSuccess.tribeEvents', obj.deinit );
	};

	/**
	 * Initialize events bar JS.
	 *
	 * @since  4.9.8
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
		const $eventsBar = $container.find( obj.selectors.eventsBar );

		if ( ! $eventsBar.length ) {
			return;
		}

		obj.initState( $container );
		obj.initEventsBar( $container );
		obj.bindEvents( $container );
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.deinit );
	};

	/**
	 * Handles the initialization of events bar when Document is ready.
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
} )( jQuery, window.tribe.events.views.eventsBar );
