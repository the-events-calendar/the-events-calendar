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
 * Configures Views Object in the Global Tribe variable
 *
 * @since 4.9.4
 *
 * @type   {PlainObject}
 */
tribe.events.views.viewSelector = {};

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
	 * Deinitialize view selector accordion
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.deinitViewSelectorAccordion = function( $container ) {
		var $viewSelectorButton = $container.find( obj.selectors.viewSelectorButton );
		var $viewSelectorListContainer = $container.find( obj.selectors.viewSelectorListContainer );
		obj.deinitAccordion( $viewSelectorButton, $viewSelectorListContainer );
	};

	/**
	 * Initialize view selector accordion
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.initViewSelectorAccordion = function( $container ) {
		var $viewSelectorButton = $container.find( obj.selectors.viewSelectorButton );
		var $viewSelectorListContainer = $container.find( obj.selectors.viewSelectorListContainer );
		obj.initAccordion( $container, $viewSelectorButton, $viewSelectorListContainer );
	};

	/**
	 * Initializes view selector state
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.initState = function( $container ) {
		var $viewSelector = $container.find( obj.selectors.viewSelector );
		var state = {
			mobileInitialized: false,
			desktopInitialized: false,
		};

		$viewSelector.data( 'tribeEventsState', state );
	};

	/**
	 * Deinitializes view selector
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.deinitViewSelector = function( $container ) {
		obj.deinitViewSelectorAccordion( $container );
	};

	/**
	 * Initializes view selector
	 *
	 * @since 4.9.8
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.initViewSelector = function( $container ) {
		var $viewSelector = $container.find( obj.selectors.viewSelector );

		if ( $viewSelector.length ) {
			var state = $viewSelector.data( 'tribeEventsState' );
			var isTabs = $viewSelector.hasClass( obj.selectors.viewSelectorTabsClass.className() );

			// If view selector is tabs (has 3 or less options)
			if ( isTabs ) {
				// If viewport is mobile and mobile state is not initialized
				if ( tribe.events.views.viewport.state.isMobile && ! state.mobileInitialized ) {
					obj.initViewSelectorAccordion( $container );
					state.desktopInitialized = false;
					state.mobileInitialized = true;
					$viewSelector.data( 'tribeEventsState', state );

				// If viewport is desktop and desktop state is not initialized
				} else if ( ! tribe.events.views.viewport.state.isMobile && ! state.desktopInitialized ) {
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
	 * Toggles active class on view selector button
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event event object for click event
	 *
	 * @return {void}
	 */
	obj.handleViewSelectorButtonClick = function( event ) {
		event.data.target.toggleClass( obj.selectors.viewSelectorButtonActiveClass.className() );
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
		var isParentViewSelector = Boolean( $( event.target ).closest( obj.selectors.viewSelector ).length );

		if ( ! isParentViewSelector ) {
			var $container = event.data.container;
			var $viewSelector = $container.find( obj.selectors.viewSelector );
			var $viewSelectorButton = $viewSelector.find( obj.selectors.viewSelectorButton );

			if ( $viewSelectorButton.hasClass( obj.selectors.viewSelectorButtonActiveClass.className() ) ) {
				var $viewSelectorListContainer = $viewSelector.find( obj.selectors.viewSelectorListContainer );
				$viewSelectorButton.removeClass( obj.selectors.viewSelectorButtonActiveClass.className() );
				tribe.events.views.accordion.closeAccordion( $viewSelectorButton, $viewSelectorListContainer );
			}
		}
	};

	/**
	 * Handles resize event
	 *
	 * @since 4.9.7
	 *
	 * @param {Event} event event object for 'resize.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.handleResize = function( event ) {
		obj.initViewSelector( event.data.container );
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
		$document
			.off( 'resize.tribeEvents', obj.handleResize )
			.off( 'click', obj.handleClick );
		$container
			.find( obj.selectors.viewSelectorButton )
			.off( 'click', obj.handleViewSelectorButtonClick );
	};

	/**
	 * Binds events for container
	 *
	 * @since 4.9.7
	 *
	 * @param  {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( $container ) {
		var $viewSelectorButton = $container.find( obj.selectors.viewSelectorButton );

		$document
			.on( 'resize.tribeEvents', { container: $container }, obj.handleResize )
			.on( 'click', { container: $container }, obj.handleClick );
		$viewSelectorButton.on( 'click', { target: $viewSelectorButton }, obj.handleViewSelectorButtonClick );
	};

	/**
	 * Deinitialize view selector JS
	 *
	 * @since 4.9.7
	 *
	 * @param  {Event}       event    event object for 'beforeAjaxSuccess.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.deinit = function( event, jqXHR, settings ) {
		var $container = event.data.container;
		obj.deinitViewSelector( $container );
		obj.unbindEvents( $container );
	};

	/**
	 * Initialize view selector JS
	 *
	 * @since 4.9.8
	 *
	 * @param  {Event}   event      event object for 'afterSetup.tribeEvents' event
	 * @param  {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param  {jQuery}  $container jQuery object of view container
	 * @param  {object}  data       data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		var $viewSelector = $container.find( obj.selectors.viewSelector );

		if ( ! $viewSelector.length ) return;

		obj.initState( $container );
		obj.initViewSelector( $container );
		obj.bindEvents( $container );
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.deinit );
	};

	/**
	 * Handles the initialization of the view selector when Document is ready
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
} )( jQuery, tribe.events.views.viewSelector );
