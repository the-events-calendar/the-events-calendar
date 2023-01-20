/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since TBD
 *
 * @type {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.admin = tribe.events.admin || {};

/**
 * Configures admin manager Object in the Global Tribe variable
 *
 * @since TBD
 *
 * @type {PlainObject}
 */
tribe.events.admin.noticeInstall = {};

/**
 * Initializes in a Strict env the code that manages the Events admin notice.
 *
 * @since TBD
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} _   Underscore.js
 * @param  {PlainObject} obj tribe.events.admin.noticeInstall
 *
 * @return {void}
 */
( function( $, _, obj ) {
	'use strict';
	const $document = $( document );

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since TBD
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		noticeInstallButton: '.tec-admin__notice-install-content-button',
		noticeDescription: '.tec-admin__notice-install-content-description',
	};

	/**
	 * Gets the AJAX request data.
	 *
	 * @since TBD
	 *
	 * @param  {Element|jQuery} $button The button where the configuration data is.
	 *
	 * @return {Object} data
	 */
	obj.getData = function( $button ) {
		const data = {
			'action': 'notice_install_event_tickets',
			'request': $button.data( 'action' ),
			'slug': $button.data( 'plugin-slug' ),
			'_wpnonce': $button.data( 'nonce' ),
		};

		return data;
	};

	/**
	 * Handles the plugin install AJAX call.
	 *
	 * @since TBD
	 */
	obj.handleInstall = function() {
		const $button = $( this );
		const ajaxUrl = TribeEventsAdminNoticeInstall.ajaxurl;
		const data = obj.getData( $button );

		$button.addClass( 'is-busy' );
		$button.prop( 'disabled', true );

		if ( 'install' === data.request ) {
			$button.text( $button.data( 'installing-label' ) );
		} else if ( 'activate' === data.request  ) {
			$button.text( $button.data( 'activating-label' ) );
		}

		$.post( ajaxUrl, data, function( response ) {
			$button.removeClass( 'is-busy' );
			$button.prop( 'disabled', false );

			if ( 'undefined' === typeof response.data || 'object' !== typeof response.data ) {
				return;
			}

			if ( response.success ) {
				if ( 'install' === data.request ) {
					$button.text( $button.data( 'installed-label' ) );
				} else if ( 'activate' === data.request  ) {
					$button.text( $button.data( 'activated-label' ) );
				}

				location.replace( $button.data('redirect-url') );
			} else {
				const $description = $button.siblings( obj.selectors.noticeDescription );
				$description.html( response.data.message );
				$button.remove();
			}
		} );
	}

	/**
	 * Handles the initialization of the notice actions.
	 *
	 * @since TBD
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$( obj.selectors.noticeInstallButton ).on( 'click', obj.handleInstall );
	};

	// Configure on document ready.
	$document.ready( obj.ready );
} )( jQuery, window.underscore || window._, tribe.events.admin.noticeInstall );
