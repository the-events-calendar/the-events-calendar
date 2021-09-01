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
 * Configures Multiday Events Object in the Global Tribe variable
 *
 * @since 4.9.4
 *
 * @type  {PlainObject}
 */
tribe.events.views.multidayEvents = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since 4.9.4
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.events.views.multidayEvents
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	var $document = $( document );

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since 4.9.5
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {};

	/**
	 * Selector prefixes used for creating selectors
	 *
	 * @since 4.9.5
	 *
	 * @type {PlainObject}
	 */
	obj.selectorPrefixes = {
		month: '.tribe-events-calendar-month__',
	};

	/**
	 * Selector suffixes used for creating selectors
	 *
	 * @since 4.9.5
	 *
	 * @type {PlainObject}
	 */
	obj.selectorSuffixes = {
		multidayEvent: 'multiday-event',
		hiddenMultidayEvent: 'multiday-event-hidden',
		multidayEventBarInner: 'multiday-event-bar-inner',
		multidayEventBarInnerFocus: 'multiday-event-bar-inner--focus',
		multidayEventBarInnerHover: 'multiday-event-bar-inner--hover',
	};

	/**
	 * Find visible multiday event that relates to the hidden multiday event
	 *
	 * @since 4.9.5
	 *
	 * @param {jQuery} $container jQuery object of view container.
	 * @param {jQuery} $hiddenMultidayEvent jQuery object of hidden multiday event
	 *
	 * @return {jQuery} jQuery object of visible multiday event or false if none found
	 */
	obj.findVisibleMultidayEvents = function( $container, $hiddenMultidayEvent ) {
		var eventId = $hiddenMultidayEvent.closest( obj.selectors.multidayEvent ).data( 'event-id' );

		return $container
			.find( obj.selectors.multidayEvent + '[data-event-id=' + eventId + ']' )
	};

	/**
	 * Toggle hover class on visible multiday event when hidden multiday triggers hover event
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.toggleHoverClass = function( event ) {
		event.data.target.toggleClass( obj.selectors.multidayEventBarInnerHover.className() );
	};

	/**
	 * Toggle focus class on visible multiday event when hidden multiday triggers focus event
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.toggleFocusClass = function( event ) {
		event.data.target.toggleClass( obj.selectors.multidayEventBarInnerFocus.className() );
	};

	/**
	 * Unbinds events for hover and focus of hidden multiday events.
	 *
	 * @since 4.9.5
	 *
	 * @param {jQuery} $container jQuery object of view container.
	 *
	 * @return {void}
	 */
	obj.unbindMultidayEvents = function( $container ) {
		var $hiddenMultidayEvents = $container.find( obj.selectors.hiddenMultidayEvent );

		$hiddenMultidayEvents.each( function( hiddenIndex, hiddenMultidayEvent ) {
			$( hiddenMultidayEvent ).off();
		} );
	};

	/**
	 * Binds events for hover and focus of hidden multiday events.
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $container jQuery object of view container.
	 *
	 * @return {void}
	 */
	obj.bindMultidayEvents = function( $container ) {
		var $hiddenMultidayEvents = $container.find( obj.selectors.hiddenMultidayEvent );

		$hiddenMultidayEvents.each( function( hiddenIndex, hiddenMultidayEvent ) {
			var $hiddenMultidayEvent = $( hiddenMultidayEvent );
			var $visibleMultidayEvents = obj.findVisibleMultidayEvents( $container, $hiddenMultidayEvent ); // eslint-disable-line max-len

			$visibleMultidayEvents.each( function( visibleIndex, visibleMultidayEvent ) {
				var $visibleMultidayEvent = $( visibleMultidayEvent );
				var $visiblemultidayEventBarInner = $visibleMultidayEvent.find( obj.selectors.multidayEventBarInner ); // eslint-disable-line max-len

				$hiddenMultidayEvent
					.on(
						'mouseenter mouseleave',
						{ target: $visiblemultidayEventBarInner },
						obj.toggleHoverClass
					)
					.on( 'focus blur', { target: $visiblemultidayEventBarInner }, obj.toggleFocusClass );
			} );
		} );
	};

	/**
	 * Resets selectors to empty object
	 *
	 * @since 4.9.5
	 *
	 * @return {void}
	 */
	obj.deinitSelectors = function() {
		obj.selectors = {};
	};

	/**
	 * Initializes selectors based on view slug
	 *
	 * @since 4.9.5
	 *
	 * @param {string} viewSlug slug of view
	 *
	 * @return {void}
	 */
	obj.initSelectors = function( viewSlug ) {
		var selectorPrefix = obj.selectorPrefixes[ viewSlug ];

		Object
			.keys( obj.selectorSuffixes )
			.forEach( function( key ) {
				obj.selectors[ key ] = selectorPrefix + obj.selectorSuffixes[ key ];
			} );
	};

	/**
	 * Unbinds events for container.
	 *
	 * @since 4.9.5
	 *
	 * @param  {Event}       event    event object for 'beforeAjaxSuccess.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.unbindEvents = function( event, jqXHR, settings ) { // eslint-disable-line no-unused-vars
		var $container = event.data.container;
		obj.deinitSelectors();
		obj.unbindMultidayEvents( $container );
		$container.off( 'beforeAjaxSuccess.tribeEvents', obj.unbindEvents );
	};

	/**
	 * Binds events for container.
	 *
	 * @since 4.9.9
	 *
	 * @param {jQuery}  $container jQuery object of view container.
	 * @param {object}  data       data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( $container, data ) {
		var viewSlug = data.slug;
		var allowedViews = $container.data( 'tribeEventsMultidayEventsAllowedViews' );

		if ( -1 === allowedViews.indexOf( viewSlug ) ) {
			return;
		}

		obj.initSelectors( viewSlug );
		obj.bindMultidayEvents( $container );
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.unbindEvents );
	};

	/**
	 * Initialize allowed views
	 *
	 * @since 4.9.9
	 *
	 * @param {jQuery} $container jQuery object of view container.
	 *
	 * @return {void}
	 */
	obj.initAllowedViews = function( $container ) {
		$container.trigger( 'beforeMultidayEventsInitAllowedViews.tribeEvents', [ $container ] );

		var theme = [ 'month' ];
		$container.data( 'tribeEventsMultidayEventsAllowedViews', theme );

		$container.trigger( 'afterMultidayEventsInitAllowedViews.tribeEvents', [ $container ] );
	};

	/**
	 * Initialize multiday events.
	 *
	 * @since 4.9.9
	 *
	 * @param {Event}   event      event object for 'afterSetup.tribeEvents' event
	 * @param {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event.
	 * @param {jQuery}  $container jQuery object of view container.
	 * @param {object}  data       data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		obj.initAllowedViews( $container );
		obj.bindEvents( $container, data );
	};

	/**
	 * Handles the initialization of multiday events when Document is ready
	 *
	 * @since 4.9.4
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on(
			'afterSetup.tribeEvents',
			tribe.events.views.manager.selectors.container,
			obj.init
		);
	};

	// Configure on document ready
	$( obj.ready );
} )( jQuery, tribe.events.views.multidayEvents );
