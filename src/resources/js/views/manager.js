/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since  TBD
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Views Object in the Global Tribe variable
 *
 * @since  TBD
 *
 * @type   {PlainObject}
 */
tribe.events.views.manager = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since  TBD
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} _   Underscore.js
 * @param  {PlainObject} obj tribe.events.views.manager
 *
 * @return {void}
 */
( function( $, _, obj ) {
	'use strict';
	var $document = $( document );

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		container: '.tribe-events-container',
		link: '.tribe-events-navigation-link',
		loader: '.tribe-events-view-loader',
		hiddenElement: '.tribe-hidden'
	};

	/**
	 * Containers on the current page that were initialized
	 *
	 * @since TBD
	 *
	 * @type {jQuery}
	 */
	obj.$containers = $();

	/**
	 * From a jQuery.serialize() we make the arguments a little bit more organized
	 *
	 * @since TBD
	 *
	 * @todo  move this to a Common Util class
	 *
	 * @param  {string} query jQuery.serialize() string
	 *
	 * @return {PlainObject}
	 */
	const parseQuery = function( query ) {
		const emptyData = new RegExp( '^[^=]+=$' );
		const plusSign = /\+/g;

		return query.split( '&' ).filter( function( dataEntry ){
			return ! emptyData.test( dataEntry );
		} ).reduce( function( dataCouples, couple ){
			const split = couple.split( '=' );
			dataCouples[ decodeURIComponent( split[0].replace( plusSign, '%20' ) ) ] = decodeURIComponent( split[1].replace( plusSign, '%20' ) );

			return dataCouples;
		}, {} );
	};

	/**
	 * Setup the container for views management
	 *
	 * @since TBD
	 *
	 * @todo  Requirement to setup other JS modules after hijacking Click and Submit
	 *
	 * @param  {integer} index     jQuery.each index param
	 * @param  {Element} container Which element we are going to setup
	 *
	 * @return {void}
	 */
	obj.setup = function( index, container ) {
		var $container = $( container );

		$container.find( obj.selectors.link ).on( 'click.tribeEvents', obj.onLinkClick );

		// Only catch the submit if properly setup on a form
		if ( $container.is( 'form' ) ) {
			$container.on( 'submit.tribeEvents', obj.onSubmit );
		}
	};

	/**
	 * Given an Element determines it's view container
	 *
	 * @since TBD
	 *
	 * @param  {Element|jQuery} element Which element we getting the container from
	 *
	 * @return {jQuery}
	 */
	obj.getContainer = function( element ) {
		var $element = $( element );

		if ( ! $element.is( obj.selectors.container ) ) {
			return $element.parents( obj.selectors.container ).eq( 0 );
		}

		return $element;
	};

	/**
	 * Hijacks the link click and passes the URL as param for REST API
	 *
	 * @since TBD
	 *
	 * @param  {Event} event DOM Event related to the Click action
	 *
	 * @return {boolean}
	 */
	obj.onLinkClick = function( event ) {
		event.preventDefault();
		var $link = $( this );
		var $container = obj.getContainer( this );
		var url = $link.attr( 'href' );
		var data = {
			url: url
		};

		obj.request( data, $container );

		return false;
	};

	/**
	 * Hijacks the form submit passes all form details to the REST API
	 *
	 * @since TBD
	 *
	 * @todo  make sure we are only capturing fields on our Namespace
	 *
	 * @param  {Event} event DOM Event related to the Click action
	 *
	 * @return {boolean}
	 */
	obj.onSubmit = function( event ) {
		event.preventDefault();
		var $container = $( this );
		var data = parseQuery( $container.serialize() );

		obj.request( data, $container );

		return false;
	};

	/**
	 * Performs an AJAX request given the data for the REST API and which container
	 * we are going to pass the answer to.
	 *
	 * @since TBD
	 *
	 * @param  {object}         data       DOM Event related to the Click action
	 * @param  {Element|jQuery} $container Which container we are dealing with
	 *
	 * @return {void}
	 */
	obj.request = function( data, $container ) {
		var settings = obj.getAjaxSettings( $container );

		// Pass the data received to the $.ajax settings
		settings.data = data;

		$.ajax( settings );
	};

	/**
	 * Gets the jQuery.ajax() settings provided a views container
	 *
	 * @since TBD
	 *
	 * @param  {Element|jQuery} $container Which container we are dealing with
	 *
	 * @return {PlainObject}
	 */
	obj.getAjaxSettings = function( $container ) {
		var ajaxSettings = {
			url: $container.data( 'rest-url' ),
			accepts: 'html',
			dataType: 'html',
			method: 'GET',
			'async': true, // async is keywork
			beforeSend: obj.ajaxBeforeSend,
			complete: obj.ajaxComplete,
			success: obj.ajaxSuccess,
			error: obj.ajaxError,
			context: $container,
		};

		return ajaxSettings;
	};

	/**
	 * Triggered on jQuery.ajax() beforeSend action, which we hook into to
	 * setup a Loading Lock, as well as trigger a before and after hook, so
	 * third-party developers can always extend all requests
	 *
	 * Context with the View container used to fire this AJAX call
	 *
	 * @since TBD
	 *
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request will be made with
	 *
	 * @return {void}
	 */
	obj.ajaxBeforeSend = function( jqXHR, settings ) {
		var $container = this;
		var $loader = $container.find( obj.selectors.loader );

		$container.trigger( 'beforeAjaxBeforeSend.tribeEvents', [ jqXHR, settings ] );

		console.log( jqXHR, settings, this );

		if ( $loader.length ) {
			$loader.removeClass( obj.selectors.hiddenElement.className() );
		}

		$container.trigger( 'afterAjaxBeforeSend.tribeEvents', [ jqXHR, settings ] );
	};

	/**
	 * Triggered on jQuery.ajax() complete action, which we hook into to
	 * removal of Loading Lock, as well as trigger a before and after hook,
	 * so third-party developers can always extend all requests
	 *
	 * Context with the View container used to fire this AJAX call
	 *
	 * @since TBD
	 *
	 * @param  {jqXHR}  qXHR       Request object
	 * @param  {String} textStatus Status for the request
	 *
	 * @return {void}
	 */
	obj.ajaxComplete = function( jqXHR, textStatus ) {
		var $container = this;
		var $loader = $container.find( obj.selectors.loader );

		$container.trigger( 'beforeAjaxComplete.tribeEvents', [ jqXHR, textStatus ] );

		console.log( jqXHR, textStatus, this );

		if ( $loader.length ) {
			$loader.addClass( obj.selectors.hiddenElement.className() );
		}

		$container.trigger( 'afterAjaxComplete.tribeEvents', [ jqXHR, textStatus ] );
	};

	/**
	 * Triggered on jQuery.ajax() success action, which we hook into to
	 * replace the contents of the container which is the base behavior
	 * for the views manager, as well as trigger a before and after hook,
	 * so third-party developers can always extend all requests
	 *
	 * Context with the View container used to fire this AJAX call
	 *
	 * @since TBD
	 *
	 * @param  {String} html       HTML sent from the REST API
	 * @param  {String} textStatus Status for the request
	 * @param  {jqXHR}  qXHR       Request object
	 *
	 * @return {void}
	 */
	obj.ajaxSuccess = function( data, textStatus, jqXHR ) {
		var $container = this;

		$container.trigger( 'beforeAjaxSuccess.tribeEvents', [ data, textStatus, jqXHR ] );

		console.log( data, textStatus, jqXHR, this );

		var $html = $( data );

		// Replace the current container with the new Data
		$container.replaceWith( $html );

		// Setup the container with the data received
		obj.setup( 0, $html );

		$container.trigger( 'afterAjaxSuccess.tribeEvents', [ data, textStatus, jqXHR ] );
	};

	/**
	 * Triggered on jQuery.ajax() error action, which we hook into to
	 * display error and keep the user on the same "page", as well as
	 * trigger a before and after hook, so third-party developers can
	 * always extend all requests
	 *
	 * Context with the View container used to fire this AJAX call
	 *
	 * @since TBD
	 *
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.ajaxError = function( jqXHR, settings ) {
		var $container = this;

		$container.trigger( 'beforeAjaxError.tribeEvents', [ jqXHR, settings ] );

		console.log( jqXHR, settings, this );

		$container.trigger( 'afterAjaxError.tribeEvents', [ jqXHR, settings ] );
	};

	/**
	 * Handles the initialization of the manager when Document is ready
	 *
	 * @since  TBD
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		obj.$containers = $( obj.selectors.container );
		obj.$containers.each( obj.setup );
	};

	// Configure on document ready
	$document.ready( obj.ready );
}( jQuery, window.underscore || window._, tribe.events.views.manager ) );
