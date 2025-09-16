window.tribe.upgradePage = window.tribe.upgradePage || {};

( function ( $, obj ) {
	'use strict';

	obj.localized = window.tribe_upgrade_page || {};

	obj.setup = function () {
		if ( $( '#current-settings-tab' ).val() != 'upgrade' ) {
			// eslint-disable-line eqeqeq
			// if it is already enabled, we don't need to show the button
			if ( obj.localized.v2_is_enabled == '1' ) {
				// eslint-disable-line eqeqeq
				return;
			}

			$( '.tribe_settings > h1' ).append(
				'<button id="upgrade-button">✨ ' + obj.localized.button_text + '</button>'
			);
			$( document ).on( 'click', '#upgrade-button', function ( e ) {
				// eslint-disable-line no-unused-vars,max-len
				document.location = '?page=tribe-common&tab=upgrade&post_type=tribe_events';
			} );

			return;
		}

		$( '#tribeSaveSettings' ).hide();
		$( '#tribe-field-views_v2_enabled input' ).hide().prop( 'checked', true );

		$( document ).on( 'click', '#tribe-upgrade-step1 button', function ( e ) {
			e.preventDefault();

			$( '#tribe-upgrade-step1' ).addClass( 'hidden' );
			$( '#tribe-upgrade-step2' ).removeClass( 'hidden' );
		} );

		$( document ).on( 'click', '#tribe-upgrade-step2 button', function ( e ) {
			e.preventDefault();
			$( '#tribeSaveSettings' ).trigger( 'click' );
		} );
	};

	$( obj.setup );
} )( jQuery, window.tribe.upgradePage );
