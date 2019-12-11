<script>
( function( $ ) {
	'use strict';
	var $document = $( document );
	var obj = {};

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		container: '[data-js="tribe-events-view"]',
		dataScript: '[data-js="tribe-events-view-data"]',
		breakpointXsmallClass: '.tribe-common--breakpoint-xsmall',
		breakpointMediumClass: '.tribe-common--breakpoint-medium',
		breakpointFullClass: '.tribe-common--breakpoint-full',
	};

	/**
	 * State for script
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.state = {
		currentScript: null,
		container: null,
		data: {},
		initialized: false,
	};

	obj.setContainerClasses = function() {
		window.innerWidth;
	};

	/**
	 * Handles resize event for window
	 *
	 * @since  TBD
	 *
	 * @return {void}
	 */
	obj.handleResize = function( event ) {

	};

	/**
	 * Unbinds events for container
	 *
	 * @since  TBD
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function() {
		obj.state.container.off( 'beforeAjaxSuccess.tribeEvents', obj.deinit );
		window.off( 'resize', obj.handleResize );
		window.on( 'resize', obj.handleResize );
	};

	/**
	 * Binds events for container
	 *
	 * @since  TBD
	 *
	 * @return {void}
	 */
	obj.bindEvents = function() {
		obj.state.container.on( 'beforeAjaxSuccess.tribeEvents', obj.deinit );
		window.on( 'resize', obj.handleResize );
	};

	/**
	 * Deinitialize breakpoints JS
	 *
	 * @since  TBD
	 *
	 * @param  {Event}       event    event object for 'beforeAjaxSuccess.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.deinit = function( event, jqXHR, settings ) {
		obj.unbindEvents();
		obj.state.container = null;
		obj.state.data = {};
		obj.state.initialized = false;
	};

	/**
	 * Sets up data for container
	 *
	 * @since  TBD
	 *
	 * @return {void}
	 */
	obj.setupData = function() {
		var $data = obj.state.container.find( obj.selectors.dataScript );

		// If we have data element set it up.
		if ( $data.length ) {
			obj.state.data = JSON.parse( $.trim( $data.text() ) );
		}
	}

	/**
	 * Initialize breakpoints JS
	 *
	 * @since  TBD
	 *
	 * @return {void}
	 */
	obj.init = function() {
		if ( obj.state.initialized ) {
			return;
		}

		obj.state.container = obj.state.currentScript.prev( obj.selectors.container );
		obj.setupData();
		obj.bindEvents();
		obj.setContainerClasses();
		obj.state.initialized = true;
	};

	/**
	 * Setup breakpoints JS
	 *
	 * @since  TBD
	 *
	 * @return {void}
	 */
	obj.setup = function() {
		var scripts = document.getElementsByTagName( 'script' );
		obj.state.currentScript = $( scripts[ scripts.length - 1 ] );
	};

	obj.setup();
	obj.init();
	$document.on( 'afterSetup.tribeEvents', obj.selectors.container, obj.init );

} )( jQuery );
</script>
