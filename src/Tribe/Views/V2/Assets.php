<?php
/**
 * Handles registering all Assets for the Events V2 Views
 *
 * To remove a Assets:
 * tribe( 'assets' )->remove( 'asset-name' );
 *
 * @since TBD
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as Plugin;

/**
 * Register
 *
 * @since TBD
 *
 * @package Tribe\Events\Views\V2
 */
class Assets {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register() {
		$plugin = Plugin::instance();

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-manager',
			'views/manager.js',
			[ 'jquery', 'tribe-common' ],
			null // prevent it from loading
		);
	}
}