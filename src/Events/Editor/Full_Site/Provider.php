<?php

namespace TEC\Events\Editor\Full_Site;

use Tribe\Events\Editor\Blocks\Archive_Events;
use \Tribe__Events__Main as Events_Main;
use TEC\Common\Contracts\Service_Provider;


/**
 * Class Provider
 *
 * @since 5.14.2
 *
 * @package TEC\Events\Editor\Full_Site
 */
class Provider extends Service_Provider {

	/**
	 * Register the provider singletons.
	 *
	 * @since 5.14.2
	 */
	public function register() {
		if ( ! tec_is_full_site_editor() ) {
			return;
		}

		$this->container->singleton( Templates::class );

		// Register singletons.
		$this->register_singletons();

		// Register the Service Provider for Hooks.
		$this->container->register( Hooks::class );

		// Register the Service Provider for Assets.
		$this->register_assets();

		// Register the service provider itself on the container.
		$this->container->singleton( static::class, $this );
	}

	/**
	 * Registers any requires singletons.
	 *
	 * @since 5.14.2
	 *
	 */
	private function register_singletons() {
		$this->container->singleton( Archive_Events::class, Archive_Events::class, [ 'load' ] );
	}

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.14.2
	 */
	public function register_assets() {
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

	/**
	 * Internal FSE function for asset conditional testing.
	 *
	 * @since 5.14.2
	 *
	 * @return boolean Whether The current theme supports full-site editing or not.
	 */
	public function is_full_site_editor() {
		return tec_is_full_site_editor();
	}
}
