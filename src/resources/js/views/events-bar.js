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
		searchButton: '',
		filterButton: '',
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
		is_mobile: true,
	};

	/**
	 * Set viewport state
	 *
	 * @since TBD
	 *
	 * @returns {void}
	 */
	obj.setViewport = function() {
		obj.state.is_mobile = $window.width() < obj.options.MOBILE_BREAKPOINT;
	};

	/**
	 * Handles window resize event
	 *
	 * @since TBD
	 *
	 * @param {Event} event event object for 'resize' event
	 *
	 * @returns {void}
	 */
	obj.handleResize = function( event ) {

	};

	/**
	 * Initialize events bar.
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
		$eventsBar = $container.find( obj.selectors.eventsBar );

		// if filter bar exists (or some other check)
		if ( $eventsBar.hasClass( obj.selectors.hasFilterBarClass ) ) {
			// add aria attributes
			// add event listeners
		}

		/**
		 * default is mobile, init different cases:
		 *   init without filter bar, mobile
		 *   init with filter bar, mobile
		 *   init without filter bar, desktop
		 *   init with filter bar, desktop
		 */
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
		$window.on( 'resize', obj.handleResize );

		/**
		 * @todo: do below for ajax events
		 */
		// on 'beforeAjaxBeforeSend.tribeEvents' event, remove all listeners
		// on 'afterAjaxError.tribeEvents', add all listeners
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.eventsBar );
