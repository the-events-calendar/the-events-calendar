/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since TBD
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Events Bar Object in the Global Tribe variable
 *
 * @since TBD
 *
 * @type   {PlainObject}
 */
tribe.events.views.eventsBar = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since TBD
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
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		eventsBar: '',
		tabList: '',
		searchTab: '',
		filterTab: '',
		searchTabPanel: '',
		filterTabPanel: '',
		hasFilterBarClass: '',
	};

	/**
	 * Object of key codes
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.options = {
		MOBILE_BREAKPOINT: 768,
	};

	/**
	 * Object of state
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.state = {
		isMobile: true,
	};

	/**
	 * Set viewport state
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.setViewport = function() {
		obj.state.isMobile = $window.width() < obj.options.MOBILE_BREAKPOINT;
	};

	/**
	 * Deselects all tabs
	 *
	 * @since TBD
	 *
	 * @param {array} tabs array of jQuery objects of tabs
	 *
	 * @return {void}
	 */
	obj.deselectTabs = function( tabs ) {
		tabs.forEach( function( $tab ) {
			$tab
				.attr( 'tabindex', '-1' )
				.attr( 'aria-selected', 'false' );
		} );
	};

	/**
	 * Hides all tab panels
	 *
	 * @since TBD
	 *
	 * @param {array} tabPanels array of jQuery objects of tabPanels
	 *
	 * @return {void}
	 */
	obj.hideTabPanels = function( tabPanels ) {
		tabPanels.forEach( function( $tabPanel ) {
			$tabPanel.prop( 'hidden' );
		} );
	};

	/**
	 * Select tab based on index
	 *
	 * @since TBD
	 *
	 * @param {array} tabs array of jQuery objects of tabs
	 * @param {array} tabPanels array of jQuery objects of tabPanels
	 * @param {integer} index index of tab to be selected
	 *
	 * @return {void}
	 */
	obj.selectTab = function( tabs, tabPanels, index ) {
		obj.deselectTabs( tabs );
		obj.hideTabPanels( tabPanels );

		tabs[ index ]
			.attr( 'aria-selected', 'true' )
			.removeAttr( 'tabindex' );

		tabs[ index ]
			.find( '#' + tabs[ index ].attr( 'aria-controls' ) )
			.removeProp( 'hidden' );
	};

	/**
	 * Handles 'keydown' event on tabs
	 *
	 * @since TBD
	 *
	 * @param {Event} event event object of keydown event
	 *
	 * @return {void}
	 */
	obj.handleKeydown = function( event ) {
		var key = event.which || event.keyCode;
		var $eventsBar = $( event.data.eventsBar );
		var state = $eventsBar.data( 'state' );
		var tabs = state.tabs;
		var tabPanels = state.tabPanels;
		var currentTab = state.currentTab;
		var nextTab;

		switch ( key ) {
			case obj.keyCode.LEFT:
				nextTab = 0 === state.currentTab ? tabs.length - 1 : currentTab - 1;
				break;
			case obj.keyCode.RIGHT:
				nextTab = tabs.length - 1 === state.currentTab ? 0 : currentTab + 1;
				if ( tabs.length - 1 === state.currentTab ) {
					nextTab = 0;
				} else {
					nextTab = currentTab + 1;
				}
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

		obj.selectTab( tabs, tabPanels, nextTab );
		state.currentTab = nextTab;
		$eventsBar.data( 'state', state );
		event.preventDefault();
	};

	/**
	 * Deinitializes tablist
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.deinitTablist = function( $container ) {
		var $searchTab = $container.find( obj.selectors.searchTab );
		var $filterTab = $container.find( obj.selectors.filterTab );
		var $searchTabPanel = $container.find( obj.selectors.searchTabPanel );
		var $filterTabPanel = $container.find( obj.selectors.filterTabPanel );

		$searchTab
			.removeAttr( 'role' )
			.removeAttr( 'aria-selected' )
			.removeAttr( 'aria-controls' )
			.removeAttr( 'tabindex' )
			.off( 'keydown', obj.handleKeydown );
		$filterTab
			.removeAttr( 'role' )
			.removeAttr( 'aria-selected' )
			.removeAttr( 'aria-controls' )
			.removeAttr( 'tabindex' )
			.off( 'keydown', obj.handleKeydown );
		$searchTabPanel
			.removeAttr( 'role' )
			.removeAttr( 'aria-labelledby' )
			.removeProp( 'hidden' );
		$filterTabPanel
			.removeAttr( 'role' )
			.removeAttr( 'aria-labelledby' )
			.removeProp( 'hidden' );
	};

	/**
	 * Initializes tablist
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.initTablist = function( $container ) {
		var $eventsBar = $container.find( obj.selectors.eventsBar );
		var $searchTab = $container.find( obj.selectors.searchTab );
		var $filterTab = $container.find( obj.selectors.filterTab );
		var $searchTabPanel = $container.find( obj.selectors.searchTabPanel );
		var $filterTabPanel = $container.find( obj.selectors.filterTabPanel );

		$searchTab
			.attr( 'role', 'tab' )
			.attr( 'aria-selected', 'true' )
			.attr( 'aria-controls', $searchTabPanel.attr( 'id' ) )
			.on( 'keydown', { eventsBar: $eventsBar }, obj.handleKeydown );
		$filterTab
			.attr( 'role', 'tab' )
			.attr( 'aria-selected', 'false' )
			.attr( 'aria-controls', $filterTabPanel.attr( 'id' ) )
			.attr( 'tabindex', '-1' )
			.on( 'keydown', { eventsBar: $eventsBar }, obj.handleKeydown );
		$searchTabPanel
			.attr( 'role', 'tabpanel' )
			.attr( 'aria-labelledby', $searchTab.attr( 'id' ) );
		$filterTabPanel
			.attr( 'role', 'tabpanel' )
			.attr( 'aria-labelledby', $filterTab.attr( 'id' ) )
			.prop( 'hidden' );
	};

	/**
	 * Deinitialize filter button accordion
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.deinitFilterAccordion = function( $container ) {
		var $filterTab = $container.find( obj.selectors.filterTab );
		var $filterTabPanel = $container.find( obj.selectors.filterTabPanel );

		tribe.events.views.accordion.deinitAccordion( 0, $filterTab );
		$filterTab
			.removeAttr( 'aria-expanded' )
			.removeAttr( 'aria-selected' )
			.removeAttr( 'aria-controls' );
		$filterTabPanel
			.removeAttr( 'aria-hidden' )
			.css( 'display', '' );
	};

	/**
	 * Initialize filter button accordion
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.initFilterAccordion = function( $container ) {
		var $filterTab = $container.find( obj.selectors.filterTab );
		var $filterTabPanel = $container.find( obj.selectors.filterTabPanel );

		tribe.events.views.accordion.initAccordion( $container )( 0, $filterTab );
		$filterTab
			.attr( 'aria-expanded', 'false' )
			.attr( 'aria-selected', 'false' )
			.attr( 'aria-controls', $filterTabPanel.attr( 'id' ) );
		$filterTabPanel.attr( 'aria-hidden', 'true' );
	};

	/**
	 * Initializes events bar state
	 *
	 * @since TBD
	 *
	 * @param {jQuery} $eventsBar jQuery object of events bar
	 *
	 * @return {void}
	 */
	obj.initState = function( $eventsBar ) {
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
	 * @since TBD
	 *
	 * @param {jQuery} $container jQuery object of view container
	 *
	 * @return {void}
	 */
	obj.initEventsBar = function( $container ) {
		var $eventsBar = $container.find( obj.selectors.eventsBar );
		var state = $eventsBar.data( 'state' );

		// if filter bar exists (or some other check)
		if ( $eventsBar.hasClass( obj.selectors.hasFilterBarClass ) ) {
			if ( obj.state.isMobile && ! state.mobileInitialized ) {
				obj.deinitFilterAccordion( $container );
				obj.initTablist( $container );
				state.desktopInitialized = false;
				state.mobileInitialized = true;
				$eventsBar.data( 'state', state );
			} else if ( ! obj.state.isMobile && ! state.desktopInitialized ) {
				obj.deinitTablist( $container );
				obj.initFilterAccordion( $container );
				state.mobileInitialized = false;
				state.desktopInitialized = true;
				$eventsBar.data( 'state', state );
			}
		}
	};

	/**
	 * Handles window resize event
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
	 *
	 * @param {Event} event event object for 'afterSetup.tribeEvents' event
	 * @param {integer} index jQuery.each index param from 'afterSetup.tribeEvents' event
	 * @param {jQuery} $container jQuery object of view container
	 * @param {object} data data object passed from 'afterSetup.tribeEvents' event
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		var $eventsBar = $container.find( obj.selectors.eventsBar );

		obj.setViewport();
		obj.initState( $eventsBar );
		obj.initEventsBar( $container );
		obj.bindEvents( $container );
	};

	/**
	 * Handles the initialization of events bar when Document is ready
	 *
	 * @since TBD
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
