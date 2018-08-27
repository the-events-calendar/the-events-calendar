/**
 * Handle the timezone updater process.
 */
jQuery( document ).ready( function( $ ) {
	// Do not proceed if tribe_timezone_update is not available
	if ( "object" !== typeof tribe_timezone_update ) {
		return;
	}

	/**
	 * Controls the update loop.
	 *
	 * @param response
	 */
	function update( response ) {
		// Refresh the admin notice
		if ( "string" === typeof response.html ) {
			admin_notice.html( response.html );
		}

		// "Soft failure"?
		if ( 0 == response ) {
			failure();
			return;
		}

		// Stop here if the task completed
		if ( ! response.continue ) {
			// Refresh page (so the new timezone settings are exposed)
			window.location.assign( window.location );
			return;
		}

		// Form a fresh request
		var request = {
			action: "tribe_timezone_update",
			check:  check_value
		};

		$.post( ajaxurl, request, update, "json").fail( failure );
	}

	/**
	 * If the ajax loop failed for any reason, display an appropriate message.
	 */
	function failure() {
		admin_notice.html( "<p>" + failure_msg + "</p>" );
	}

	var admin_notice = $( ".tribe-events-timezone-update-msg" );
	var failure_msg  = tribe_timezone_update.failure_msg;
	var check_value  = tribe_timezone_update.check;

	if ( tribe_timezone_update.continue ) {
		update( tribe_timezone_update );
	}
} );