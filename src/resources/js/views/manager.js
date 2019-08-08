/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since  4.9.2
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Views Object in the Global Tribe variable
 *
 * @since  4.9.2
 *
 * @type   {PlainObject}
 */
tribe.events.views.manager = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since  4.9.2
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
	 * @since 4.9.2
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		container: '[data-js="tribe-events-view"]',
		form: '[data-js="tribe-events-view-form"]',
		link: '[data-js="tribe-events-view-link"]',
		dataScript: '[data-js="tribe-events-view-data"]',
		loader: '.tribe-events-view-loader',
		hiddenElement: '.tribe-common-a11y-hidden',
	};

	/**
	 * Containers on the current page that were initialized
	 *
	 * @since 4.9.2
	 *
	 * @type {jQuery}
	 */
	obj.$containers = $();

	/**
	 * Setup the container for views management
	 *
	 * @since 4.9.2
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
		var $form = $container.find( obj.selectors.form );
		var $data = $container.find( obj.selectors.dataScript );
		var data  = {};

		// If we have data element set it up.
		if ( $data.length ) {
			data = JSON.parse( $.trim( $data.text() ) );
		}

		$container.trigger( 'beforeSetup.tribeEvents', [ index, $container, data ] );

		$container.find( obj.selectors.link ).on( 'click.tribeEvents', obj.onLinkClick );

		// Only catch the submit if properly setup on a form
		if ( $form ) {
			$form.on( 'submit.tribeEvents', obj.onSubmit );
		}

		$container.trigger( 'afterSetup.tribeEvents', [ index, $container, data ] );

		// Binds and action to the container that will update the URL based on backed
		$container.on( 'updateUrl.tribeEvents', obj.onUpdateUrl );
	};

	/**
	 * Given an Element determines it's view container
	 *
	 * @since 4.9.2
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
	 * Given an container determines if it should manage URL.
	 *
	 * @since 4.9.4
	 *
	 * @param  {Element|jQuery} element Which element we are using as the container.
	 *
	 * @return {Boolean}
	 */
	obj.shouldManageUrl = function( $container ) {
		var shouldManageUrl = $container.data( 'view-manage-url' );
		var tribeIsTruthy   = /^(true|1|on|yes)$/;

		// When undefined we use true as the default.
		if ( typeof shouldManageUrl === typeof undefined ) {
			shouldManageUrl = true;
		} else {
			// When not undefined we cast as string and test for valid boolean truth.
			shouldManageUrl = tribeIsTruthy.test( String( shouldManageUrl ) );
		}

		return shouldManageUrl;
	};

	/**
	 * Using data passed by the Backend once we fetch a new HTML via an
	 * container action.
	 *
	 * Usage, on the AJAX request we will pass data back using a <script>
	 * formatted as a `application/json` that we will parse and apply here.
	 *
	 * @since 4.9.4
	 *
	 * @param  {Event}  event DOM Event related to the Click action
	 *
	 * @return {void}
	 */
	obj.onUpdateUrl = function( event ) {
		var $container = $( this );

		// Bail when we dont manage URLs
		if ( ! obj.shouldManageUrl( $container ) ) {
			return;
		}

		var $data = $container.find( obj.selectors.dataScript );

		// Bail in case we dont find data script.
		if ( ! $data.length ) {
			return;
		}

		var data = JSON.parse( $.trim( $data.text() ) );

		// Bail when the data is not a valid object
		if ( ! _.isObject( data ) ) {
			return;
		}

		// Bail when URL is not present
		if ( _.isUndefined( data.url ) ) {
			return;
		}

		// Bail when Title is not present
		if ( _.isUndefined( data.title ) ) {
			return;
		}

		/**
		 * Compatitiblity for browsers updating title
		 */
		document.title = data.title;

		// Push browser history
		window.history.pushState( null, data.title, data.url );
	};

	/**
	 * Hijacks the link click and passes the URL as param for REST API
	 *
	 * @since 4.9.2
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
		var nonce = $link.data( 'view-rest-nonce' );
		var shouldManageUrl = obj.shouldManageUrl( $container );

		// Fetch nonce from container if the link doesnt have any
		if ( ! nonce ) {
			nonce = $container.data( 'view-rest-nonce' );
		}

		var data = {
			url: url,
			should_manage_url: shouldManageUrl,
			_wpnonce: nonce
		};

		obj.request( data, $container );

		return false;
	};

	/**
	 * Hijacks the form submit passes all form details to the REST API
	 *
	 * @since 4.9.2
	 *
	 * @todo  make sure we are only capturing fields on our Namespace
	 *
	 * @param  {Event} event DOM Event related to the Click action
	 *
	 * @return {boolean}
	 */
	obj.onSubmit = function( event ) {
		event.preventDefault();

		// The submit event is triggered on the form, not the container.
		var $form = $( this );
		var $container = $form.closest( obj.selectors.container );
		var nonce = $container.data( 'view-rest-nonce' );

		var formData = Qs.parse( $form.serialize() );

		var data = {
			url: window.location.href,
			view_data: formData['tribe-events-views'],
			_wpnonce: nonce
		};

		// Pass the data to the request reading it from `tribe-events-views`.
		obj.request( data, $container );

		return false;
	};

	/**
	 * Performs an AJAX request given the data for the REST API and which container
	 * we are going to pass the answer to.
	 *
	 * @since 4.9.2
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
	 * @since 4.9.2
	 *
	 * @param  {Element|jQuery} $container Which container we are dealing with
	 *
	 * @return {PlainObject}
	 */
	obj.getAjaxSettings = function( $container ) {
		var ajaxSettings = {
			url: $container.data('view-rest-url'),
			accepts: 'html',
			dataType: 'html',
			method: 'GET',
			'async': true, // async is keyword
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
	 * @since 4.9.2
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
	 * @since 4.9.2
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
	 * @since 4.9.2
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

		var $html = $( data );

		// Replace the current container with the new Data
		$container.replaceWith( $html );
		$container = $html;

		// Setup the container with the data received
		obj.setup( 0, $html );

		// Trigger the browser pushState
		$container.trigger( 'updateUrl.tribeEvents' );

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
	 * @since 4.9.2
	 *
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.ajaxError = function( jqXHR, settings ) {
		var $container = this;

		$container.trigger( 'beforeAjaxError.tribeEvents', [ jqXHR, settings ] );

		/**
		 * @todo  we need to handle errors here
		 */

		$container.trigger( 'afterAjaxError.tribeEvents', [ jqXHR, settings ] );
	};

	/**
	 * Handles the initialization of the manager when Document is ready
	 *
	 * @since  4.9.2
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		obj.$containers = $( obj.selectors.container );
		obj.$containers.each( obj.setup );
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, window.underscore || window._, tribe.events.views.manager );
