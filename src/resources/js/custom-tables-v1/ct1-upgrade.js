let Ct1Upgrade = {};

( function( $, obj ) {
	obj.selectors = {
		v2DisableDialog: '#tec-recurrence-migration__v2-disable-dialog',
		v2Enabled: 'input[name="views_v2_enabled"]',
		alertOkButton: '.tec-upgrade-recurrence__modal-container--v2-disable-dialog .tribe-alert__continue',
		alertCloseButton: '.tec-upgrade-recurrence__modal-container--v2-disable-dialog .tribe-modal__close-button'
	};

	obj.init = function() {
		$( document ).on( 'change', obj.selectors.v2Enabled, function() {
			if ( $( this ).is( ':checked' ) ) {
				return;
			}

			$( obj.selectors.v2DisableDialog ).click();
		});

		$( document ).on( 'click', obj.selectors.alertOkButton, function() {
			$( obj.selectors.alertCloseButton ).click();
		} );
	};

	$( obj.init );

} )( jQuery, Ct1Upgrade );