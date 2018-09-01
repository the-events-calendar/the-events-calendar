<?php

/**
 * Class Tribe__Events__Aggregator__Processes__Queue_Control
 *
 * @since 4.6.22
 */
class Tribe__Events__Aggregator__Processes__Queue_Control {

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
		$clear_queues = tribe_get_request_var( 'clear_queues', false );

		if ( empty( $clear_queues ) ) {
			return;
		}

		$this->clear_queues();

		$location = null !== $location ? $location : remove_query_arg( 'clear_queues' );

		wp_redirect( $location );
		tribe_exit();
	}

	/**
	 * Clears the queues, in whatever state they are, related to Event Aggregator imports.
	 *
	 * @since 4.6.22
	 */
	public function clear_queues() {
		Tribe__Process__Queue::delete_all_queues( 'tribe_queue_ea_import_events' );
	}
}
