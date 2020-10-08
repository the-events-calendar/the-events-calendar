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
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.events.views.eventsBar
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
	 * @param {jQuery} $header jQuery object of header
	 * @param {jQuery} $content jQuery object of contents
	 *
	 * @return {void}
	 */
	obj.deinitAccordion = function( $header, $content ) {
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
	 * @param {jQuery} $header jQuery object of header
	 * @param {jQuery} $content jQuery object of contents
	 *
	 * @return {void}
	 */
	obj.initAccordion = function( $container, $header, $content ) {
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
	obj.handleSearchButtonClick = function( event ) {
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
	obj.deinitSearchAccordion = function( $container ) {
		var $searchButton = $container.find( obj.selectors.searchButton );
		$searchButton.removeClass( obj.selectors.searchButtonActiveClass.className() );
		var $searchContainer = $container.find( obj.selectors.searchContainer );
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
	obj.initSearchAccordion = function( $container ) {
		var $searchButton = $container.find( obj.selectors.searchButton );
		var $searchContainer = $container.find( obj.selectors.searchContainer );
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
	obj.initState = function( $container ) {
		var $eventsBar = $container.find( obj.selectors.eventsBar );
		var state = {
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
	obj.deinitEventsBar = function( $container ) {
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
	obj.initEventsBar = function( $container ) {
		var $eventsBar = $container.find( obj.selectors.eventsBar );

		if ( $eventsBar.length ) {
			var state = $eventsBar.data( 'tribeEventsState' );
			var containerState = $container.data( 'tribeEventsState' );
			var isMobile = containerState.isMobile;

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
	obj.handleResize = function( event ) {
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
	obj.handleClick = function( event ) {
		var $target = $( event.target );
		var isParentSearchButton = Boolean( $target.closest( obj.selectors.searchButton ).length );
		var isParentSearchContainer = Boolean( $target.closest( obj.selectors.searchContainer ).length );

		if ( ! ( isParentSearchButton || isParentSearchContainer ) ) {
			var $container = event.data.container;
			var $eventsBar = $container.find( obj.selectors.eventsBar );
			var $searchButton = $eventsBar.find( obj.selectors.searchButton );

			if ( $searchButton.hasClass( obj.selectors.searchButtonActiveClass.className() ) ) {
				var $searchContainer = $eventsBar.find( obj.selectors.searchContainer );
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
	 * @param  {jQuery}  $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function( $container ) {
		$container.off( 'resize.tribeEvents', obj.handleResize );
		$document.off( 'click', obj.handleClick );
	};

	/**
	 * Bind events for events bar
	 *
	 * @since 4.9.7
	 *
	 * @param  {jQuery}  $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( $container ) {
		$container.on( 'resize.tribeEvents', { container: $container }, obj.handleResize );
		$document.on( 'click', { container: $container }, obj.handleClick );
	};

	/**
	 * Deinitialize events bar JS
	 *
	 * @since 4.9.4
	 *
	 * @param  {Event}       event    event object for 'beforeAjaxSuccess.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.deinit = function( event, jqXHR, settings ) {
		var $container = event.data.container;
		obj.deinitEventsBar( $container );
		obj.unbindEvents( $container );
		$container.off( 'beforeAjaxSuccess.tribeEvents', obj.deinit );
	};

	/**
	 * Initialize events bar JS
	 *
	 * @since  4.9.8
	 *
	 * @param  {Event}   event      event object for 'afterSetup.tribeEvents' event
	 * @param  {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param  {jQuery}  $container jQuery object of view container
	 * @param  {object}  data       data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		var $eventsBar = $container.find( obj.selectors.eventsBar );

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
	obj.ready = function() {
		$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.init );
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.eventsBar );
