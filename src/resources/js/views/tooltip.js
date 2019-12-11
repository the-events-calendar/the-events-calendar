/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since  4.9.4
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Views Tooltip Object in the Global Tribe variable
 *
 * @since  4.9.4
 *
 * @type   {PlainObject}
 */
tribe.events.views.tooltip = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since  4.9.4
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.events.views.tooltip
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	var $document = $( document );

	/**
	 * Config used for tooltip setup
	 *
	 * @since 4.9.10
	 *
	 * @type {PlainObject}
	 */
	obj.config = {
		delayHoverIn: 300,
		delayHoverOut: 300,
	};

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since 4.9.10
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		tooltipTrigger: '[data-js~="tribe-events-tooltip"]',
		tribeEventsTooltipTriggerHoverClass: '.tribe-events-tooltip-trigger--hover',
		tribeEventsTooltipThemeClass: '.tribe-events-tooltip-theme',
		tribeEventsTooltipThemeHoverClass: '.tribe-events-tooltip-theme--hover',
		tribeCommonClass: '.tribe-common',
		tribeEventsClass: '.tribe-events',
	};

	/**
	 * Handle tooltip focus event
	 *
	 * @since 4.9.10
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.handleOriginFocus = function( event ) {
		setTimeout( function() {
			if (
				event.data.target.is( ':focus' ) ||
				event.data.target.hasClass( obj.selectors.tribeEventsTooltipTriggerHoverClass.className() )
			) {
				event.data.target.tooltipster( 'open' );
			}
		}, obj.config.delayHoverIn );
	};

	/**
	 * Handle tooltip blur event
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.handleOriginBlur = function( event ) {
		event.data.target.tooltipster( 'close' );
	};

	/**
	 * Handle origin mouseenter and touchstart events
	 *
	 * @since 4.9.10
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.handleOriginHoverIn = function( event ) {
		event.data.target.addClass( obj.selectors.tribeEventsTooltipTriggerHoverClass.className() );
	};

	/**
	 * Handle origin mouseleave and touchleave events
	 *
	 * @since 4.9.10
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.handleOriginHoverOut = function( event ) {
		event.data.target.removeClass( obj.selectors.tribeEventsTooltipTriggerHoverClass.className() );
	};

	/**
	 * Handle tooltip mouseenter and touchstart event
	 *
	 * @since 4.9.10
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.handleTooltipHoverIn = function( event ) {
		event.data.target.addClass( obj.selectors.tribeEventsTooltipThemeHoverClass.className() );
	};

	/**
	 * Handle tooltip mouseleave and touchleave events
	 *
	 * @since 4.9.10
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.handleTooltipHoverOut = function( event ) {
		event.data.target.removeClass( obj.selectors.tribeEventsTooltipThemeHoverClass.className() );
	};

	/**
	 * Handle tooltip instance closing event
	 *
	 * @since 4.9.10
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.handleInstanceClose = function( event ) {
		var $origin = event.data.origin;
		var $tooltip = $( event.tooltip );

		// if trigger is focused, hovered, or tooltip is hovered, do not close tooltip
		if (
			$origin.is( ':focus' ) ||
			$origin.hasClass( obj.selectors.tribeEventsTooltipTriggerHoverClass.className() ) ||
			$tooltip.hasClass( obj.selectors.tribeEventsTooltipThemeHoverClass.className() )
		) {
			event.stop();
		}
	};

	/**
	 * Handle tooltip instance close event
	 *
	 * @since 4.9.10
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.handleInstanceClosing = function( event ) {
		$( event.tooltip )
			.off( 'mouseenter touchstart', obj.handleTooltipHoverIn )
			.off( 'mouseleave touchleave', obj.handleTooltipHoverOut );
	};

	/**
	 * Override of the `functionInit` tooltipster method.
	 * A custom function to be fired only once at instantiation.
	 *
	 * @since 4.9.10
	 *
	 * @param {Tooltipster} instance instance of Tooltipster
	 * @param {PlainObject} helper   helper object with tooltip origin
	 *
	 * @return {void}
	 */
	obj.onFunctionInit = function( instance, helper ) {
		var $origin = $( helper.origin );
		$origin
			.on( 'focus', { target: $origin }, obj.handleOriginFocus )
			.on( 'blur', { target: $origin }, obj.handleOriginBlur )
			.on( 'mouseenter touchstart', { target: $origin }, obj.handleOriginHoverIn )
			.on( 'mouseleave touchleave', { target: $origin }, obj.handleOriginHoverOut );
		instance
			.on( 'close', { origin: $origin }, obj.handleInstanceClose )
			.on( 'closing', { origin: $origin }, obj.handleInstanceClosing );
	};

	/**
	 * Override of the `functionReady` tooltipster method.
	 * A custom function to be fired when the tooltip and its contents have been added to the DOM.
	 *
	 * @since 4.9.10
	 *
	 * @param {Tooltipster} instance instance of Tooltipster
	 * @param {PlainObject} helper   helper object with tooltip origin
	 *
	 * @return {void}
	 */
	obj.onFunctionReady = function( instance, helper ) {
		var $tooltip = $( helper.tooltip );
		$tooltip
			.on( 'mouseenter touchstart', { target: $tooltip }, obj.handleTooltipHoverIn )
			.on( 'mouseleave touchleave', { target: $tooltip }, obj.handleTooltipHoverOut );
	};

	/**
	 * Deinitialize accessible tooltips via tooltipster
	 *
	 * @since 4.9.10
	 *
	 * @param {jQuery} $container jQuery object of view container.
	 *
	 * @return {void}
	 */
	obj.deinitTooltips = function( $container ) {
		$container
			.find( obj.selectors.tooltipTrigger )
			.each( function( index, trigger ) {
				$( trigger )
					.off( 'focus', obj.handleOriginFocus )
					.off( 'blur', obj.handleOriginBlur )
					.off( 'mouseenter touchstart', obj.handleOriginHoverIn )
					.off( 'mouseleave touchleave', obj.handleOriginHoverOut )
					.tooltipster( 'instance' )
					.off( 'close', obj.handleInstanceClose )
					.off( 'closing', obj.handleInstanceClosing );
			} );
	};

	/**
	 * Initialize accessible tooltips via tooltipster
	 *
	 * @since 4.9.10
	 *
	 * @param {jQuery} $container jQuery object of view container.
	 *
	 * @return {void}
	 */
	obj.initTooltips = function( $container ) {
		var theme = $container.data( 'tribeEventsTooltipTheme' );

		$container
			.find( obj.selectors.tooltipTrigger )
			.each( function( index, trigger ) {
				$( trigger ).tooltipster( {
					animationDuration: 0,
					interactive: true,
					delay: [ obj.config.delayHoverIn, obj.config.delayHoverOut ],
					delayTouch: [ obj.config.delayHoverIn, obj.config.delayHoverOut ],
					theme: theme,
					functionInit: obj.onFunctionInit,
					functionReady: obj.onFunctionReady,
				} );
			} );
	};

	/**
	 * Initialize tooltip theme
	 *
	 * @since 4.9.10
	 *
	 * @param {jQuery} $container jQuery object of view container.
	 *
	 * @return {void}
	 */
	obj.initTheme = function( $container ) {
		$container.trigger( 'beforeTooltipInitTheme.tribeEvents', [ $container ] );

		var theme = [
			obj.selectors.tribeEventsTooltipThemeClass.className(),
			obj.selectors.tribeCommonClass.className(),
			obj.selectors.tribeEventsClass.className(),
		];
		$container.data( 'tribeEventsTooltipTheme', theme );

		$container.trigger( 'afterTooltipInitTheme.tribeEvents', [ $container ] );
	};

	/**
	 * Deinitialize tooltip JS.
	 *
	 * @since 4.9.5
	 *
	 * @param  {Event}       event    event object for 'beforeAjaxSuccess.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.deinit = function( event, jqXHR, settings ) {
		var $container = event.data.container;
		obj.deinitTooltips( $container );
	};

	/**
	 * Initialize tooltips JS.
	 *
	 * @since 4.9.5
	 *
	 * @param {Event}   event      event object for 'afterSetup.tribeEvents' event
	 * @param {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event.
	 * @param {jQuery}  $container jQuery object of view container.
	 * @param {object}  data       data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		obj.initTheme( $container );
		obj.initTooltips( $container );
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.deinit );
	};

	/**
	 * Handles the initialization of the scripts when Document is ready
	 *
	 * @since  4.9.4
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.init );
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.tooltip );
