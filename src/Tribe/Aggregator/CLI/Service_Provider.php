<?php

class Tribe__Events__Aggregator__CLI__Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		/**
		 * While using wp-cli PHP version will be 5.3.29 or later but this file might still be parsed from PHP 5.2
		 * so we must avoid PHP 5.3+ syntax here.
		 */
		WP_CLI::add_command(
			'event-aggregator',
			'Tribe__Events__Aggregator__CLI__Command',
			array(
				'shortdesc' => __( 'Crete, run and manage Event Aggregator imports.', 'the-events-calendar' ),
				'longdesc' => __( 'If required the commands will use the API keys and licenses set for the current site.' ),
			)
		);
	}
}