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
 * @param  {PlainObject} obj tribe.events.views.manager
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	var $window = $( window );
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
		tabList: '[data-js="tribe-events-events-bar-tablist"]',
		tab: '[data-js~="tribe-events-events-bar-tab"]',
		tabPanel: '[data-js~="tribe-events-events-bar-tabpanel"]',
		searchButton: '[data-js="tribe-events-search-button"]',
		filtersButton: '[data-js="tribe-events-filters-button"]',
		searchFiltersContainer: '[data-js="tribe-events-search-filters-container"]',
		filtersContainer: '[data-js~="tribe-events-events-bar-filters"]',
		hasFilterBarClass: '.tribe-events-c-events-bar--has-filter-bar',
		activeTabClass: '.tribe-events-c-events-bar__tab--active',
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
	 * Object of options
	 *
	 * @since 4.9.4
	 *
	 * @type {PlainObject}
	 */
	obj.options = {
		MOBILE_BREAKPOINT: 768,
	};

	/**
	 * Object of state
	 *
	 * @since 4.9.4
	 *
	 * @type {PlainObject}
	 */
	obj.state = {
		isMobile: true,
	};

	/**
	 * Set viewport state
	 *
	 * @since 4.9.4
	 *
	 * @return {void}
	 */
	obj.setViewport = function() {
		obj.state.isMobile = window.innerWidth < obj.options.MOBILE_BREAKPOINT;
	};

	/**
	 * Deselects all tabs
	 *
	 * @since 4.9.4
	 *
	 * @param {array} tabs array of jQuery objects of tabs
	 *
	 * @return {void}
	 */
	obj.deselectTabs = function( tabs ) {
		tabs.forEach( function( $tab ) {
			$tab
				.attr( 'tabindex', '-1' )
				.attr( 'aria-selected', 'false' )
				.removeClass( 'tribe-events-c-events-bar__tab--active' );
		} );
	};

	/**
	 * Hides all tab panels
	 *
	 * @since 4.9.4
	 *
	 * @param {array} tabPanels array of jQuery objects of tabPanels
	 *
	 * @return {void}
	 */
	obj.hideTabPanels = function( tabPanels ) {
		tabPanels.forEach( function( $tabPanel ) {
			$tabPanel.prop( 'hidden', true );
		} );
	};

	/**
	 * Select tab based on index
	 *
	 * @since 4.9.4
	 *
	 * @param {array} tabs array of jQuery objects of tabs
	 * @param {array} tabPanels array of jQuery objects of tabPanels
	 * @param {jQuery} $tab jQuery object of tab to be selected
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.selectTab = function( tabs, tabPanels, $tab, $container ) {
		obj.deselectTabs( tabs );
		obj.hideTabPanels( tabPanels );

		$tab
			.attr( 'aria-selected', 'true' )
			.removeAttr( 'tabindex' )
			.addClass( 'tribe-events-c-events-bar__tab--active' )
			.focus();
		$container
			.find( '#' + $tab.attr( 'aria-controls' ) )
			.removeProp( 'hidden' );
	};

	/**
	 * Gets current tab index
	 *
	 * @since 4.9.4
	 *
	 * @param {array} tabs array of jQuery objects of tabs
	 *
	 * @return {integer} index of current tab
	 */
	obj.getCurrentTab = function( tabs ) {
		var currentTab;

		tabs.forEach( function( $tab, index ) {
			if ( $tab.is( document.activeElement ) ) {
				currentTab = index;
			}
		} );

		return currentTab;
	};

	/**
	 * Handles 'click' event on tab
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object of click event
	 *
	 * @return {void}
	 */
	obj.handleClick = function( event ) {
		var $container = $( event.data.container );
		var $eventsBar = $container.find( obj.selectors.eventsBar );
		var state = $eventsBar.data( 'state' );
		var tabs = state.tabs;
		var tabPanels = state.tabPanels;
		var selectedTab = $( event.target ).closest( obj.selectors.tab );

		obj.selectTab( tabs, tabPanels, selectedTab, $container );
	};

	/**
	 * Handles 'keydown' event on tab
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object of keydown event
	 *
	 * @return {void}
	 */
	obj.handleKeydown = function( event ) {
		var key = event.which || event.keyCode;
		var $container = $( event.data.container );
		var $eventsBar = $container.find( obj.selectors.eventsBar );
		var state = $eventsBar.data( 'state' );
		var tabs = state.tabs;
		var tabPanels = state.tabPanels;
		var currentTab = obj.getCurrentTab( tabs );
		var nextTab;

		switch ( key ) {
			case obj.keyCode.LEFT:
				nextTab = 0 === currentTab ? tabs.length - 1 : currentTab - 1;
				break;
			case obj.keyCode.RIGHT:
				nextTab = tabs.length - 1 === currentTab ? 0 : currentTab + 1;
				break;
			case obj.keyCode.HOME:
				nextTab = 0;
				break;
			case obj.keyCode.END:
				nextTab = tabs.length - 1;
				break;
			default:
				return;
		}

		obj.selectTab( tabs, tabPanels, tabs[ nextTab ], $container );
		event.preventDefault();
	};

	/**
	 * Deinitializes tablist
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.deinitTablist = function( $container ) {
		$container
			.find( obj.selectors.tab )
			.each( function( index, tab ) {
				$( tab )
					.removeAttr( 'aria-selected' )
					.removeAttr( 'tabindex' )
					.off( 'keydown', obj.handleKeydown )
					.off( 'click', obj.handleClick );
			} );
		$container
			.find( obj.selectors.tabPanel )
			.each( function( index, tabpanel ) {
				$( tabpanel )
					.removeAttr( 'role' )
					.removeAttr( 'aria-labelledby' )
					.removeProp( 'hidden' );
			} );
	};

	/**
	 * Initializes tablist
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.initTablist = function( $container ) {
		var $eventsBar = $container.find( obj.selectors.eventsBar );
		var state = $eventsBar.data( 'state' );
		var tabs = [];
		var tabpanels = [];

		$container
			.find( obj.selectors.tab )
			.each( function( index, tab ) {
				var $tab = $( tab );
				var $tabpanel = $container.find( '#' + $tab.attr( 'aria-controls' ) );

				$tab
					.attr( 'aria-selected', 'true' )
					.on( 'keydown', { container: $container }, obj.handleKeydown )
					.on( 'click', { container: $container }, obj.handleClick );
				$tabpanel
					.attr( 'role', 'tabpanel' )
					.attr( 'aria-labelledby', $tab.attr( 'id' ) );

				if ( index !== 0 ) {
					$tab.attr( 'tabindex', '-1' );
					$tabpanel.prop( 'hidden', true );
				}

				tabs.push( $tab );
				tabpanels.push( $tabpanel );
			} );

		state.tabs = tabs;
		state.tabPanels = tabpanels;
		$eventsBar.data( 'state', state );
	};

	/**
	 * Deinitialize accordion based on header and content
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $header jQuery object of header
	 * @param {jQuery} $content jQuery object of contents
	 *
	 * @return {void}
	 */
	obj.deinitAccordion = function( $header, $content ) {
		tribe.events.views.accordion.deinitAccordion( 0, $header );
		$header
			.removeAttr( 'aria-expanded' )
			.removeAttr( 'aria-selected' )
			.removeAttr( 'aria-controls' );
		$content
			.removeAttr( 'aria-hidden' )
			.css( 'display', '' );
	};

	/**
	 * Initialize accordion based on header and content
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
	 * @param {jQuery} $header jQuery object of header
	 * @param {jQuery} $content jQuery object of contents
	 *
	 * @return {void}
	 */
	obj.initAccordion = function( $container, $header, $content ) {
		tribe.events.views.accordion.initAccordion( $container )( 0, $header );
		$header
			.attr( 'aria-expanded', 'false' )
			.attr( 'aria-selected', 'false' )
			.attr( 'aria-controls', $content.attr( 'id' ) );
		$content.attr( 'aria-hidden', 'true' );
	};

	/**
	 * Deinitialize filter button accordion
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.deinitFiltersAccordion = function( $container ) {
		var $filtersButton = $container.find( obj.selectors.filtersButton );

		if ( $filtersButton.length ) {
			var $filtersContainer = $container.find( obj.selectors.filtersContainer );
			obj.deinitAccordion( $filtersButton, $filtersContainer );
		}
	};

	/**
	 * Initialize filter button accordion
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.initFiltersAccordion = function( $container ) {
		var $filtersButton = $container.find( obj.selectors.filtersButton );

		if ( $filtersButton.length ) {
			var $filtersContainer = $container.find( obj.selectors.filtersContainer );
			obj.initAccordion( $container, $filtersButton, $filtersContainer );
		}
	};

	/**
	 * Deinitialize search button accordion
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.deinitSearchAccordion = function( $container ) {
		var $searchButton = $container.find( obj.selectors.searchButton );
		var $searchFiltersContainer = $container.find( obj.selectors.searchFiltersContainer );
		obj.deinitAccordion( $searchButton, $searchFiltersContainer );
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
		var $searchFiltersContainer = $container.find( obj.selectors.searchFiltersContainer );
		obj.initAccordion( $container, $searchButton, $searchFiltersContainer );
	};

	/**
	 * Initializes events bar state
	 *
	 * @since 4.9.4
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
			tabs: [],
			tabPanels: [],
			currentTab: 0,
		};

		$eventsBar.data( 'state', state );
	};

	/**
	 * Initializes events bar
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.initEventsBar = function( $container ) {
		var $eventsBar = $container.find( obj.selectors.eventsBar );
		var $filtersButton = $container.find( obj.selectors.filtersButton );
		var state = $eventsBar.data( 'state' );

		if ( obj.state.isMobile && ! state.mobileInitialized ) {
			if ( $filtersButton.length ) {
				obj.initTablist( $container );
				obj.deinitFiltersAccordion( $container );
			}
			obj.initSearchAccordion( $container );
			state.desktopInitialized = false;
			state.mobileInitialized = true;
			$eventsBar.data( 'state', state );
		} else if ( ! obj.state.isMobile && ! state.desktopInitialized ) {
			if ( $filtersButton.length ) {
				obj.deinitTablist( $container );
				obj.initFiltersAccordion( $container );
			}
			obj.deinitSearchAccordion( $container );
			state.mobileInitialized = false;
			state.desktopInitialized = true;
			$eventsBar.data( 'state', state );
		}
	};

	/**
	 * Handles window resize event
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object for 'resize' event
	 *
	 * @return {void}
	 */
	obj.handleResize = function( event ) {
		obj.setViewport();
		obj.initEventsBar( event.data.container );
	};

	/**
	 * Bind events for window resize
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( $container ) {
		$window.on( 'resize', { container: $container }, obj.handleResize );
	};

	/**
	 * Initialize events bar JS
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object for 'afterSetup.tribeEvents' event
	 * @param {integer} index jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param {jQuery} $container jQuery object of view container
	 * @param {object} data data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		obj.setViewport();
		obj.initState( $container );
		obj.initEventsBar( $container );
		obj.bindEvents( $container );
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

		/**
		 * @todo: do below for ajax events
		 */
		// on 'beforeAjaxBeforeSend.tribeEvents' event, remove all listeners
		// on 'afterAjaxError.tribeEvents', add all listeners
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.eventsBar );
