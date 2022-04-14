<?php

namespace TEC\Events\Editor\Full_Site;

use \Tribe__Events__Main as Events_Main;

/**
 * Class Assets
 *
 * @since   TBD
 *
 * @package TEC\Events\Editor\Full_Site
 */
class Assets extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
     * @since TBD
	 */
	public function register() {
		$plugin = Events_Main::instance();

		tribe_asset(
			$plugin,
			'tec-events-full-site',
			'app/full-site.js',
			[
				'react',
				'react-dom',
				'wp-components',
				'wp-api',
				'wp-api-request',
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-editor',
				'tribe-common-gutenberg-data',
				'tribe-common-gutenberg-utils',
				'tribe-common-gutenberg-store',
				'tribe-common-gutenberg-icons',
				'tribe-common-gutenberg-hoc',
				'tribe-common-gutenberg-elements',
				'tribe-common-gutenberg-components',
			],
			'enqueue_block_editor_assets',
			[
				'in_footer'    => false,
				'localize'     => [],
				'conditionals' => [ $this, 'is_full_site_editor' ],
				'priority'     => 106,
			]
		);
	}

	public function is_full_site_editor() {
		return tec_is_full_site_editor();
	}
}
