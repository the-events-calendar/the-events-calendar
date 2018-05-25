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

		add_filter( 'tribe_process_queues', array( $this, 'filter_tribe_process_queues' ) );
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