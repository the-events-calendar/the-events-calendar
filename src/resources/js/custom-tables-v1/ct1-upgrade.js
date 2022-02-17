let Ct1Upgrade = {};

( function( $, obj ) {
	obj.selectors = {
		v2DisableDialog: '#tec-ct1-migration__v2-disable-dialog',
		v2Enabled: 'input[name="views_v2_enabled"]',
		alertOkButton: '.tec-ct1-upgrade-__modal-container--v2-disable-dialog .tribe-alert__continue',
		alertCloseButton: '.tec-ct1-upgrade-__modal-container--v2-disable-dialog .tribe-modal__close-button',
		rootReportNode: '.tec-ct1-upgrade--migration-prompt',
	};
	obj.report_poll_interval = 5000;
	obj.poll_timeout = null;
	obj.get_report = function(successCallback) {
		// Initialize our report - heartbeat polling
		// @todo cleanup
		$.ajax({
			type : "GET",
			dataType : "json",
			// @todo remove this hard-coded URL and use the one localized from the back-end
			url : "/wp-admin/admin-ajax.php",
			data : {action: tecCt1Upgrade.actions.get_report},
			success: successCallback
		});
	}
	obj.data_migration_on_dom = function(key, value) {
		const rs = obj.selectors.rootReportNode;
		$(rs+' [data-migration="'+key+'"]')
			.text(value);
	}
	obj.handle_report_data = function(data) {
		const {has_changes, events} = data;
		const rs = obj.selectors.rootReportNode;
		// Sync all "listeners" with the data we have received.
		Object.keys(data).forEach(function (key){
			obj.data_migration_on_dom(key, data[key])
		});

		if(has_changes) {
			// @todo localize from backend.
			$(rs+' .tec-ct1-upgrade__report-pre-message p').html('<strong>Changes to events!</strong> The following events will be modified during the migration process:');
		} else {
			// @todo localize from backend.
			$(rs+' .tec-ct1-upgrade__report-pre-message p').html('<strong>Events can migrate with no changes!</strong>');
		}
		// Clear events
		$(rs+' .tec-ct1-upgrade__report-events-list').text('');
		// @todo Get this working - break out into function?
		events.forEach(function(event){
			$(rs+' .tec-ct1-upgrade__report-events-list').append(
				`<li><a href="${event.events[event.source_event_post_id].permalink}">${event.events[event.source_event_post_id].post_title}</a> - ${event.actions_message}</li>`
			);
		})

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

		// Initialize our report - heartbeat polling
		obj.start_report_polling();
	};

	$( obj.init );

} )( jQuery, Ct1Upgrade );