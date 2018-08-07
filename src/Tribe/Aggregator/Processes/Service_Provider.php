<?php

/**
 * Class Tribe__Events__Aggregator__Processes__Service_Provider
 *
 * @since 4.6.16
 */
class Tribe__Events__Aggregator__Processes__Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.6.16
	 */
	public function register() {
		tribe_register( 'events-aggregator.record-items', 'Tribe__Events__Aggregator__Record__Items' );
		tribe_register( 'events-aggregator.processes.import-events', 'Tribe__Events__Aggregator__Processes__Import_Events' );
		tribe_singleton( 'events-aggregator.queue-control', 'Tribe__Events__Aggregator__Processes__Queue_Control' );

		add_filter( 'tribe_process_queues', array( $this, 'filter_tribe_process_queues' ) );

		if ( tribe_get_request_var( 'clear_queues', false ) ) {
			$clear_queues = tribe_callback( 'events-aggregator.queue-control', 'clear_queues_and_redirect' );
			add_action( 'tribe_aggregator_page_request', $clear_queues, 9, 0 );
		}
	}

	/**
	 * Registers the event import background process.
	 *
	 * @since 4.6.16
	 *
	 * @param array $queues
	 *
	 * @return array
	 */
	public function filter_tribe_process_queues( array $queues = array() ) {
		$queues[] = 'Tribe__Events__Aggregator__Processes__Import_Events';

		return $queues;
	}
}