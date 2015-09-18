/**
 * @var object tribe_additional_fields
 */
jQuery( document ).ready( function( $ ) {
	var $notice = $( "#tribe-additional-field-update" );
	var $status = $notice.find( "span.update-text" );
	var $link   = $status.find( "a" );
	var check   = tribe_additional_fields.update_check;


	/**
	 * Gracefully remove the update notice.
	 */
	function remove_notice() {
		$notice.fadeOut();
	}

	/**
	 * Continue to run the loop if required or update the status text with a
	 * success message before removing it.
	 *
	 * @param data
	 */
	function handle_response( data ) {
		if ( true === data.continue ) {
			check = data.check;
			trigger_update();
		} else if ( "undefined" === typeof data.continue ) {
			handle_failure;
		} else {
			$notice.removeClass( "notice-info" ).addClass( "notice-success")
			$status.html ( tribe_additional_fields.complete_msg );
			setTimeout( remove_notice, 4000 );
		}
	}

	/**
	 * Provide a message informing the user of the failure to run the
	 * update process.
	 */
	function handle_failure() {
		$notice.html( "<p>" + tribe_additional_fields.failure_msg + "</p>" );
		$notice.removeClass( "notice-info" ).addClass( "notice-error" );
	}

	/**
	 * Replace the initiate update link with some text informing users the update
	 * is in progress and start/continue the ajax loop.
	 */
	function trigger_update() {
		$status.html( tribe_additional_fields.in_progress_msg );
		$notice.removeClass( "notice-warning" ).addClass( "notice-info" );

		var request = {
			action: "additional_fields_update",
			do_additional_fields_update: check
		}

		$.post( ajaxurl, request, handle_response, 'json' ).fail( handle_failure );
	}

	/**
	 * Listen for clicks to trigger the update process.
	 */
	$link.click( function( event ) {
		event.stopImmediatePropagation();
		trigger_update();
		return false;
	} )
} );