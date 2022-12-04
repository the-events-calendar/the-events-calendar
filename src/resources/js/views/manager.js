/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since  4.9.2
 *
 * @type   {Object}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Views Object in the Global Tribe variable
 *
 * @since  4.9.2
 *
 * @type   {Object}
 */
tribe.events.views.manager = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since  4.9.2
 *
 * @param  {Object} $   jQuery
 * @param  {Object} _   Underscore.js
 * @param  {Object} obj tribe.events.views.manager
 *
 * @return {void}
 */
( function( $, _, obj ) {
	'use strict';
	var $window = $( window );

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since 4.9.2
	 *
	 * @type {Object}
	 */
	obj.selectors = {
		container: '[data-js="tribe-events-view"]',
		form: '[data-js="tribe-events-view-form"]',
		link: '[data-js="tribe-events-view-link"]',
		dataScript: '[data-js="tribe-events-view-data"]',
		loader: '.tribe-events-view-loader',
		loaderText: '.tribe-events-view-loader__text',
		hiddenElement: '.tribe-common-a11y-hidden',
	};

	/**
	 * Object with the details of the last location URL.
	 *
	 * @since 5.7.0
	 *
	 * @type {{origin: string, pathname: string}}
	 */
	obj.lastLocation = {
		origin: '',
		pathname: '',
	};

	/**
	 * Flag when a popstate change is happening.
	 *
	 * @since 4.9.12
	 *
	 * @type {boolean}
	 */
	obj.doingPopstate = false;

	/**
	 * Stores the current ajax request been handled by the manager.
	 *
	 * @since 4.9.12
	 *
	 * @type {jqXHR|null}
	 */
	obj.currentAjaxRequest = null;

	/**
	 * Stores the last container that used PushState, which prevents fails.
	 *
	 * @todo @bordoni @paul once shortcodes start managing URLs this will need
	 *       to improve to a full tracker of history.
	 *
	 * @since 4.9.12
	 *
	 * @type {jQuery}
	 */
	obj.$lastContainer = $();

	/**
	 * Containers on the current page that were initialized.
	 *
	 * @since 4.9.2
	 *
	 * @type {jQuery}
	 */
	obj.$containers = $();

	/**
	 * Clean up the container and event listeners
	 *
	 * @since 5.0.0
	 *
	 * @param  {jQuery} container Which element we are going to clean up
	 *
	 * @return {void}
	 */
	obj.cleanup = function( container ) {
		var $container = $( container );
		var $form = $container.find( obj.selectors.form );
		var $data = $container.find( obj.selectors.dataScript );
		var data  = {};

		// If we have data element set it up.
		if ( $data.length ) {
			data = JSON.parse( $data.text().trim() );
		}

		$container.trigger( 'beforeCleanup.tribeEvents', [ $container, data ] );

		$container.find( obj.selectors.link ).off( 'click.tribeEvents', obj.onLinkClick );

		if ( $form.length ) {
			$form.off( 'submit.tribeEvents', obj.onSubmit );
		}

		$container.trigger( 'afterCleanup.tribeEvents', [ $container, data ] );
	};

	/**
	 * Setup the container for views management
	 *
	 * @since 4.9.2
	 *
	 * @todo  Requirement to setup other JS modules after hijacking Click and Submit
	 *
	 * @param  {Integer}        index     jQuery.each index param
	 * @param  {Element|jQuery} container Which element we are going to setup
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
			data = JSON.parse( $data.text().trim() );
		}

		$container.trigger( 'beforeSetup.tribeEvents', [ index, $container, data ] );

		$container.find( obj.selectors.link ).on( 'click.tribeEvents', obj.onLinkClick );

		// Only catch the submit if properly setup on a form
		if ( $form.length ) {
			$form.on( 'submit.tribeEvents', obj.onSubmit );
		}

		$container.trigger( 'afterSetup.tribeEvents', [ index, $container, data ] );
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
	 * Given an Element determines it's view container data from the script.
	 *
	 * @since 4.9.2
	 *
	 * @param  {jQuery} $container Which element we getting the data from.
	 *
	 * @return {mixed}
	 */
	obj.getContainerData = function( $container ) {
		var $data = $container.find( obj.selectors.dataScript );

		// Bail in case we dont find data script.
		if ( ! $data.length ) {
			return;
		}

		var data = JSON.parse( $data.text().trim() );

		return data;
	};

	/**
	 * Given an container determines if it should manage URL.
	 *
	 * @since 4.9.4
	 *
	 * @param  {Element|jQuery} $container Which element we are using as the container.
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
	 * @param  {jQuery} $container Which element we are updating the URL from.
	 *
	 * @return {void}
	 */
	obj.updateUrl = function( $container ) {
		// When handling popstate (browser back/next) it will not handle this part.
		if ( obj.doingPopstate ) {
			return;
		}

		// Bail when we dont manage URLs
		if ( ! obj.shouldManageUrl( $container ) ) {
			return;
		}

		var $data = $container.find( obj.selectors.dataScript );

		// Bail in case we dont find data script.
		if ( ! $data.length ) {
			return;
		}

		var data = JSON.parse( $data.text().trim() );

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
		 * Compatibility for browsers updating title
		 */
		document.title = data.title;

		// Push browser history
		window.history.pushState( null, data.title, data.url );
		obj.lastLocation.pathname = document.location.pathname;
		obj.lastLocation.origin = document.location.origin;
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
		var $container = obj.getContainer( this );

		$container.trigger( 'beforeOnLinkClick.tribeEvents', event );

		event.preventDefault();

		var containerData = obj.getContainerData( $container );

		var $link = $( this );
		var url = $link.attr( 'href' );
		var prevUrl = containerData.prev_url;
		var nonce = $link.data( 'view-rest-nonce' );
		var shouldManageUrl = obj.shouldManageUrl( $container );
		var shortcodeId = $container.data( 'view-shortcode' );

		// Fetch nonce from container if the link doesn't have any
		if ( ! nonce ) {
			nonce = $container.data( 'view-rest-nonce' );
		}

		var data = {
			prev_url: encodeURI( decodeURI( prevUrl ) ),
			url: encodeURI( decodeURI( url ) ),
			should_manage_url: shouldManageUrl,
			_wpnonce: nonce,
		};

		if ( shortcodeId ) {
			data.shortcode = shortcodeId;
		}

		obj.request( data, $container );

		$container.trigger( 'afterOnLinkClick.tribeEvents', event );

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
		var $container = obj.getContainer( this );
		$container.trigger( 'beforeOnSubmit.tribeEvents', event );

		event.preventDefault();

		// The submit event is triggered on the form, not the container.
		var $form = $( this );
		var nonce = $container.data( 'view-rest-nonce' );

		var formData = Qs.parse( $form.serialize() );

		var data = {
			view_data: formData[ 'tribe-events-views' ],
			_wpnonce: nonce,
		};

		// Pass the data to the request reading it from `tribe-events-views`.
		obj.request( data, $container );

		$container.trigger( 'afterOnSubmit.tribeEvents', event );

		return false;
	};

	/**
	 * Catches the normal browser interactions for Next and Previous pages
	 * so that we can use the manager to load the page requested instead
	 * of just changing the URL.
	 *
	 * @since  4.9.12
	 *
	 * @param  {Event} event DOM Event related to the window popstate
	 *
	 * @return {boolean}     Will always return false on this one.
	 */
	obj.onPopState = function( event ) {
		var target = event.originalEvent.target;
		var url = target.location.href;
		var $container = obj.getLastContainer();

		// We are at the same URL + path as before so not really a change on the
		// actual URL happen, it might be just a hash change which shouldn't
		// trigger and XHR request.
		// eslint-disable-next-line max-len
		if ( obj.lastLocation.origin === target.location.origin && obj.lastLocation.pathname === target.location.pathname ) {
			return false;
		}

		obj.lastLocation.pathname = document.location.pathname;
		obj.lastLocation.origin = document.location.origin;

		if ( ! $container ) {
			return false;
		}

		if ( obj.currentAjaxRequest ) {
			obj.currentAjaxRequest.abort();
		}

		// Flag that we are doing popstate globally.
		obj.doingPopstate = true;

		$container.trigger( 'beforePopState.tribeEvents', event );

		var nonce = $container.data( 'view-rest-nonce' );

		var data = {
			url: url,
			_wpnonce: nonce,
		};

		obj.request( data, $container );

		return false;
	};

	/**
	 * Sets up the request data for AJAX request.
	 *
	 * @since 5.2.0
	 *
	 * @param  {object}         data       Data object to modify and setup.
	 * @param  {Element|jQuery} $container Which container we are dealing with.
	 *
	 * @return {void}
	 */
	obj.setupRequestData = function( data, $container ) {
		var shouldManageUrl = obj.shouldManageUrl( $container );
		var containerData = obj.getContainerData( $container );

		if ( ! data.url ) {
			data.url = containerData.url;
		}

		if ( ! data.prev_url ) {
			data.prev_url = containerData.prev_url;
		}

		data.should_manage_url = shouldManageUrl;

		// Allow other values to be passed to request from container data.
		var requestData = $container.data( 'tribeRequestData' );

		if ( ! $.isPlainObject( requestData ) ) {
			return data;
		}

		return $.extend( requestData, data );
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
		$container.trigger( 'beforeRequest.tribeEvents', [ data, $container ] );

		var settings = obj.getAjaxSettings( $container );

		// Pass the data setup to the $.ajax settings
		settings.data = obj.setupRequestData( data, $container );

		obj.currentAjaxRequest = $.ajax( settings );

		$container.trigger( 'afterRequest.tribeEvents', [ data, $container ] );
	};

	/**
	 * Gets the jQuery.ajax() settings provided a views container
	 *
	 * @since 4.9.2
	 *
	 * @param  {Element|jQuery} $container Which container we are dealing with
	 *
	 * @return {Object}
	 */
	obj.getAjaxSettings = function( $container ) {
		var ajaxSettings = {
			url: $container.data( 'view-rest-url' ),
			accepts: 'html',
			dataType: 'html',
			method: $container.data( 'view-rest-method' ) || 'POST',
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
	 * @param  {Object} settings Settings that this request will be made with
	 *
	 * @return {void}
	 */
	obj.ajaxBeforeSend = function( jqXHR, settings ) {
		var $container = this;
		var $loader = $container.find( obj.selectors.loader );

		$container.trigger( 'beforeAjaxBeforeSend.tribeEvents', [ jqXHR, settings ] );

		if ( $loader.length ) {
			$loader.removeClass( obj.selectors.hiddenElement.className() );
			var $loaderText = $loader.find( obj.selectors.loaderText );
			$loaderText.text( $loaderText.text() );
		}
		$container.attr( 'aria-busy', 'true' );

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

		// Flag that we are done with popstate if that was the case.
		if ( obj.doingPopstate ) {
			obj.doingPopstate = false;
		}

		// Reset the current ajax request on the manager object.
		obj.currentAjaxRequest = null;
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

		// Clean up the container and event listeners
		obj.cleanup( $container );

		/*
		 * Dispatch an event before the container is replaced; bound events are
		 * removed!
		 */
		document.dispatchEvent(
				new CustomEvent(
						'containerReplaceBefore.tribeEvents',
						{ detail: $container }
				)
		);

		// Replace the current container with the new Data.
		$container.replaceWith( $html );
		$container = $html;

		// Setup the container with the data received.
		obj.setup( 0, $container );

		// Dispatch an event after the container is replaced and set up.
		document.dispatchEvent(
				new CustomEvent(
						'containerReplaceAfter.tribeEvents',
						{ detail: $container }
				)
		);

		// Update the global set of containers with all of the manager object.
		obj.selectContainers();

		// Trigger the browser pushState
		obj.updateUrl( $container );

		$container.trigger( 'afterAjaxSuccess.tribeEvents', [ data, textStatus, jqXHR ] );

		if ( obj.shouldManageUrl( $container ) ) {
			obj.$lastContainer = $container;
		}
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
	 * @param  {Object} settings Settings that this request was made with
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
	 * Saves all the containers in the page into the object.
	 *
	 * @since  4.9.12
	 *
	 * @return {jQuery} Which containers were selected.
	 */
	obj.selectContainers = function() {
		obj.$containers = $( obj.selectors.container );
		return obj.$containers;
	};

	/**
	 * Selects the last container to change the URL.
	 *
	 * @since  4.9.12
	 *
	 * @return {jQuery} Last container element.
	 */
	obj.getLastContainer = function() {
		/**
		 * @todo @bordoni @paul improve this when shortcodes are also managing the URL.
		 */
		if ( ! obj.$lastContainer.length ) {
			obj.$lastContainer = obj.$containers.filter( '[data-view-manage-url="1"]' ).eq( 0 );
		}

		return obj.$lastContainer;
	};

	/**
	 * Handles the initialization of the manager when Document is ready.
	 *
	 * @since  4.9.2
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		obj.selectContainers().each( obj.setup );
		obj.lastLocation = {
			origin: document.location.origin,
			pathname: document.location.pathname,
		};
	};

	// Configure on document ready.
	$( obj.ready );

	// Attaches the popstate method to the window object.
	$window.on( 'popstate', obj.onPopState );
} )( jQuery, window.underscore || window._, tribe.events.views.manager );
