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
 * Configures Events Bar Object in the Global Tribe variable
 *
 * @since 4.9.4
 *
 * @type   {PlainObject}
 */
tribe.events.views.eventsBar = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since 4.9.4
 *
 * @param {PlainObject} $   jQuery
 * @param {PlainObject} obj tribe.events.views.eventsBar
 *
 * @return {void}
 */
( function ( $, obj ) {
	'use strict';
	const $document = $( document );

	/**
	 * Selectors used for configuration and setup
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
	 * Object of key codes
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
	 * Deinitialize accordion based on header and content
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $header  jQuery object of header
	 * @param {jQuery} $content jQuery object of contents
	 *
	 * @return {void}
	 */
	obj.deinitAccordion = function ( $header, $content ) {
		tribe.events.views.accordion.deinitAccordion( 0, $header );
		tribe.events.views.accordion.deinitAccordionA11yAttrs( $header, $content );
		$content.css( 'display', '' );
	};

	/**
	 * Initialize accordion based on header and content
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery object of view container
	 * @param {jQuery} $header    jQuery object of header
	 * @param {jQuery} $content   jQuery object of contents
	 *
	 * @return {void}
	 */
	obj.initAccordion = function ( $container, $header, $content ) {
		tribe.events.views.accordion.initAccordion( $container )( 0, $header );
		tribe.events.views.accordion.initAccordionA11yAttrs( $header, $content );
	};

	/**
	 * Toggles active class on search button
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event event object for click event
	 *
	 * @return {void}
	 */
	obj.handleSearchButtonClick = function ( event ) {
		event.data.target.toggleClass( obj.selectors.searchButtonActiveClass.className() );
	};

	/**
	 * Deinitialize search button accordion
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $container jQuery object of view container
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
	 * Initialize search button accordion
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
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
	 * Initializes events bar state
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $container jQuery object of view container
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
	 * Deinitializes events bar
	 *
	 * @since 4.9.5
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.deinitEventsBar = function ( $container ) {
		obj.deinitSearchAccordion( $container );
	};

	/**
	 * Initializes events bar
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $container jQuery object of view container
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
	 * Handles 'resize.tribeEvents' event
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event event object for 'resize.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.handleResize = function ( event ) {
		obj.initEventsBar( event.data.container );
	};

	/**
	 * Handles click event on document
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event event object for click event
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
				tribe.events.views.accordion.closeAccordion( $searchButton, $searchContainer );
			}
		}
	};

	/**
	 * Unbind events for events bar
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function ( $container ) {
		$container.off( 'resize.tribeEvents', obj.handleResize );
		$document.off( 'click', obj.handleClick );
	};

	/**
	 * Bind events for events bar
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.bindEvents = function ( $container ) {
		$container.on( 'resize.tribeEvents', { container: $container }, obj.handleResize );
		$document.on( 'click', { container: $container }, obj.handleClick );
	};

	/**
	 * Deinitialize events bar JS
	 *
	 * @since 4.9.4
	 *
	 * @param {Event}       event    event object for 'beforeAjaxSuccess.tribeEvents' event
	 * @param {jqXHR}       jqXHR    Request object
	 * @param {PlainObject} settings Settings that this request was made with
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
	 * Initialize events bar JS
	 *
	 * @since  4.9.8
	 *
	 * @param {Event}   event      event object for 'afterSetup.tribeEvents' event
	 * @param {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param {jQuery}  $container jQuery object of view container
	 * @param {Object}  data       data object passed from 'afterSetup.tribeEvents' event
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
	 * Handles the initialization of events bar when Document is ready
	 *
	 * @since 4.9.4
	 *
	 * @return {void}
	 */
	obj.ready = function () {
		$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.init );
	};

	// Configure on document ready
	$( obj.ready );

	obj.viewSelector = {
		container: '[data-js="tribe-events-view-selector"]',
		button: '[data-js="tribe-events-view-selector-button"]',
		listContainer: '[data-js="tribe-events-view-selector-list-container"]',
		activeClass: 'tribe-events-c-view-selector__button--active',
		ariaExpanded: 'aria-expanded',
	};

	obj.closeViewSelector = function($container) {
		const $button = $container.find(obj.viewSelector.button);
		const $list = $container.find(obj.viewSelector.listContainer);
		$button.removeClass(obj.viewSelector.activeClass);
		$button.attr(obj.viewSelector.ariaExpanded, 'false');
		$list.hide().attr('aria-hidden', 'true');
	};

	obj.openViewSelector = function($container) {
		const $button = $container.find(obj.viewSelector.button);
		const $list = $container.find(obj.viewSelector.listContainer);
		$button.addClass(obj.viewSelector.activeClass);
		$button.attr(obj.viewSelector.ariaExpanded, 'true');
		$list.show().attr('aria-hidden', 'false');
	};

	obj.initViewSelectorA11y = function() {
		const $viewSelector = $(obj.viewSelector.container);
		if (!$viewSelector.length) return;
		const $button = $viewSelector.find(obj.viewSelector.button);
		const $list = $viewSelector.find(obj.viewSelector.listContainer);

		$viewSelector.on('keydown', function(e) {
			if (e.key === 'Escape' || e.keyCode === 27) {
				obj.closeViewSelector($viewSelector);
				$button.focus();
			}
		});

		$list.on('focusout', function(e) {
			setTimeout(function() {
				const focused = document.activeElement;
				if (!$list[0].contains(focused) && focused !== $button[0]) {
					obj.closeViewSelector($viewSelector);
				}
			}, 10);
		});

		$list.on('keydown', function(e) {
			if (e.key === 'Tab' || e.keyCode === 9) {
				const $focusables = $list.find('a:visible');
				const first = $focusables[0];
				const last = $focusables[$focusables.length - 1];
				if (!e.shiftKey && document.activeElement === last) {
					obj.closeViewSelector($viewSelector);
				}
				if (e.shiftKey && document.activeElement === first) {
					obj.closeViewSelector($viewSelector);
				}
			}
		});
	};

	$( obj.initViewSelectorA11y );
} )( jQuery, tribe.events.views.eventsBar );
