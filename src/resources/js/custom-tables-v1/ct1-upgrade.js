let Ct1Upgrade = {};

( function( $, obj ) {
	obj.selectors = {
		v2DisableDialog: '#tec-ct1-migration__v2-disable-dialog',
		v2Enabled: 'input[name="views_v2_enabled"]',
		alertOkButton: '.tec-ct1-upgrade-__modal-container--v2-disable-dialog .tribe-alert__continue',
		alertCloseButton: '.tec-ct1-upgrade-__modal-container--v2-disable-dialog .tribe-modal__close-button',
		rootReportNode: '.tec-ct1-upgrade__row', // Used to constrain some selectors
		barsSelector: '.tec-ct1-upgrade-bar .bar',
		barsProgressSelector: '.tec-ct1-upgrade-bar .progress',
		upgradeBox: '#tec-ct1-upgrade-box',
	};
	obj.upgradeBoxElement = null;
	obj.report_poll_interval = 5000;
	obj.poll_timeout = null;
	obj.get_report = function(successCallback) {
		// Initialize our report - heartbeat polling
		// @todo cleanup
		$.ajax({
			type : "GET",
			dataType : "json",
			url : tecCt1Upgrade.ajaxUrl,
			data : {action: tecCt1Upgrade.actions.get_report},
			success: successCallback
		});
	}
	obj.data_migration_on_dom = function(key, value) {
		const rs = obj.selectors.rootReportNode;
		$(rs+' [data-migration="'+key+'"]')
			.text(value);
	}
	obj.bar_progress = function(completed, total) {
		const percent = Math.round(completed / total);
		// Leave on default if we have less than 1 percent
		if(percent > 1) {
			$(obj.selectors.barsSelector).css('width', percent+'%');
		}
		$(obj.selectors.barsProgressSelector).attr('title', percent+'%');
	}
	obj.handle_report_data = function(data) {
		const {has_changes: hasChanges, report_html: reportHtml} = data;
		if(!(hasChanges && obj.upgradeBoxElement instanceof Element) ){
			// Nothing to update.
			return;
		}

		obj.upgradeBoxElement.innerHTML = reportHtml;
	}
	/**
	 * Fetches the report data, and delegates to the dom handlers
	 *
	 * @param successCallback
	 */
	obj.sync_report_data = function(successCallback) {
		obj.get_report(
			function (response) {
				obj.handle_report_data(response);
				if(successCallback) {
					successCallback(response);
				}
			}
		);
	}
	/**
	 * Recursive loop to poll the report data
	 */
	obj.poll_report_data = function() {
		obj.poll_timeout = setTimeout(function() {
			obj.sync_report_data(
				obj.poll_report_data
			);
		}, obj.report_poll_interval)
	}
	obj.cancel_report_poll = function () {
		// Kills any queued polls
		clearTimeout(obj.poll_timeout);
	}
	obj.start_report_polling = function() {
		// Get initial report data immediately
		obj.sync_report_data();
		// Start polling
		obj.poll_report_data();
	}

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

		obj.upgradeBoxElement = document.getElementById(obj.selectors.upgradeBox.substr(1));

		// Initialize our report - heartbeat polling
		obj.start_report_polling();
	};

	$( obj.init );

} )( jQuery, Ct1Upgrade );