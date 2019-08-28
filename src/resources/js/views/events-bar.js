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
		searchButtonActiveClass: '.tribe-events-c-events-bar__search-button--active',
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
	 * @since 4.9.7
	 *
	 * @param {Event} event event object of click event
	 *
	 * @return {void}
	 */
	obj.handleTabClick = function( event ) {
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
	 * @since 4.9.7
	 *
	 * @param {Event} event event object of keydown event
	 *
	 * @return {void}
	 */
	obj.handleTabKeydown = function( event ) {
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
	 * @since 4.9.7
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
					.off( 'keydown', obj.handleTabKeydown )
					.off( 'click', obj.handleTabClick );
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
	 * @since 4.9.7
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
					.on( 'keydown', { container: $container }, obj.handleTabKeydown )
					.on( 'click', { container: $container }, obj.handleTabClick );
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
		var $searchFiltersContainer = $container.find( obj.selectors.searchFiltersContainer );
		obj.initAccordion( $container, $searchButton, $searchFiltersContainer );
		$searchButton.on( 'click', { target: $searchButton }, obj.handleSearchButtonClick );
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
	 * Deinitializes events bar
	 *
	 * @since 4.9.5
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.deinitEventsBar = function( $container ) {
		obj.deinitTablist( $container );
		obj.deinitFiltersAccordion( $container );
		obj.deinitSearchAccordion( $container );
	};

	/**
	 * Initializes events bar
	 *
	 * @since 4.9.7
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.initEventsBar = function( $container ) {
		var $eventsBar = $container.find( obj.selectors.eventsBar );

		if ( $eventsBar.length ) {
			var state = $eventsBar.data( 'state' );
			var $filtersButton = $container.find( obj.selectors.filtersButton );

			// If viewport is mobile and mobile state is not initialized
			if ( tribe.events.views.viewport.state.isMobile && ! state.mobileInitialized ) {
				if ( $filtersButton.length ) {
					obj.initTablist( $container );
					obj.deinitFiltersAccordion( $container );
				}
				obj.initSearchAccordion( $container );
				state.desktopInitialized = false;
				state.mobileInitialized = true;
				$eventsBar.data( 'state', state );

			// If viewport is desktop and desktop state is not initialized
			} else if ( ! tribe.events.views.viewport.state.isMobile && ! state.desktopInitialized ) {
				if ( $filtersButton.length ) {
					obj.deinitTablist( $container );
					obj.initFiltersAccordion( $container );
				}
				obj.deinitSearchAccordion( $container );
				state.mobileInitialized = false;
				state.desktopInitialized = true;
				$eventsBar.data( 'state', state );
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
		var isParentSearchFiltersContainer = Boolean( $target.closest( obj.selectors.searchFiltersContainer ).length );

		if ( ! ( isParentSearchButton || isParentSearchFiltersContainer ) ) {
			var $container = event.data.container;
			var $eventsBar = $container.find( obj.selectors.eventsBar );
			var $searchButton = $eventsBar.find( obj.selectors.searchButton );

			if ( $searchButton.hasClass( obj.selectors.searchButtonActiveClass.className() ) ) {
				var $searchFiltersContainer = $eventsBar.find( obj.selectors.searchFiltersContainer );
				$searchButton.removeClass( obj.selectors.searchButtonActiveClass.className() );
				tribe.events.views.accordion.closeAccordion( $searchButton, $searchFiltersContainer );
			}
		}
	};

	/**
	 * Unbind events for events bar
	 *
	 * @since 4.9.7
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function() {
		$document
			.off( 'resize.tribeEvents', obj.handleResize )
			.off( 'click', obj.handleClick );
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
		$document
			.on( 'resize.tribeEvents', { container: $container }, obj.handleResize )
			.on( 'click', { container: $container }, obj.handleClick );
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
		obj.unbindEvents();
	};

	/**
	 * Initialize events bar JS
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
	obj.init = function( event, index, $container, data ) {
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
