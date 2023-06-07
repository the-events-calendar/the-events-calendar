/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 6.0.9
 *
 * @type {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.admin = tribe.events.admin || {};

/**
 * Configures admin manager Object in the Global Tribe variable
 *
 * @since 6.0.9
 *
 * @type {PlainObject}
 */
tribe.events.admin.noticeInstall = {};

/**
 * Initializes in a Strict env the code that manages the Events admin notice.
 *
 * @since 6.0.9
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
	 * @since 6.0.9
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		noticeDescription: '.tec-admin__notice-install-content-description',
	};

	/**
	 * Handles the initialization of the notice actions.
	 *
	 * @since 6.0.9
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		wp.hooks.addAction(
			'stellarwp_installer_tec_error',
			'tec/eventTicketsInstallerError',
			function( selector, slug, action, message ) {
				const $button = $( selector );
				const $description = $button.siblings( obj.selectors.noticeDescription );
				$description.html( message );
				$button.remove();
			}
		);
	};

	// Configure on document ready.
	$document.ready( obj.ready );
} )( jQuery, window.underscore || window._, tribe.events.admin.noticeInstall );
