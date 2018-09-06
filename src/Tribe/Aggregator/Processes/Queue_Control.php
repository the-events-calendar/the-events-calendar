<?php

/**
 * Class Tribe__Events__Aggregator__Processes__Queue_Control
 *
 * @since 4.6.22
 */
class Tribe__Events__Aggregator__Processes__Queue_Control {

	const CLEAR_PROCESSES = 'tribe_clear_ea_processes';
	const CLEAR_RESULT = 'tribe_clear_ea_processes_result';

	/**
	 * Clears the queues, in whatever state they are, related to Event Aggregator imports
	 * and redirects the user to the current page or a specified location.
	 *
	 * @since 4.6.22
	 *
	 * @param null|string $location The location the user should be redirected to or null
	 *                              to use the current location.
	 */
	public function clear_queues_and_redirect( $location = null ) {
		$clear_queues = tribe_get_request_var( self::CLEAR_PROCESSES, false );

		if ( empty( $clear_queues ) ) {
			return;
		}

		$cleared = $this->clear_queues();

		$location = null !== $location
			? $location
			: remove_query_arg( self::CLEAR_PROCESSES );

		$location = add_query_arg( array( self::CLEAR_RESULT => $cleared ), $location );

		wp_redirect( $location );
		tribe_exit();
	}

	/**
	 * Clears the queues, in whatever state they are, related to Event Aggregator imports.
	 *
	 * @since 4.6.22
	 *
	 * @return int The number of cleared queue processes.
	 */
	public function clear_queues() {
		return Tribe__Process__Queue::delete_all_queues( 'ea_import_events' );
	}
}
