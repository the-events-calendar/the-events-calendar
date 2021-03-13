<?php
/**
 * Handles registering all Assets for the Events V2 Widgets
 *
 * To remove a Assets:
 * tribe( 'assets' )->remove( 'asset-name' );
 *
 * @since TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
namespace Tribe\Events\Views\V2\Widgets;

use Tribe__Events__Main as Plugin;

/**
 * Register Assets related to Widgets.
 *
 * @since TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Assets extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
    * @since TBD
	 */
	public function register() {
		$plugin = Plugin::instance();

	}
}
