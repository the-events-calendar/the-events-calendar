<?php

use TEC\Common\Contracts\Service_Provider;


class Tribe__Events__Aggregator__CLI__Service_Provider extends Service_Provider {


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
			[
				'shortdesc' => 'Create, run and manage Event Aggregator imports.', // Intentionally not translated, so it can load as early as possible.
				'longdesc'  => 'If required the commands will use the API keys and licenses set for the current site.', // Intentionally not translated, so it can load as early as possible.
			]
		);
	}
}
