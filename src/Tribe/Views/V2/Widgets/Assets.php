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
use \Tribe\Events\Views\V2\Assets as TEC_Assets;

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

		tribe_asset(
			$plugin,
			'tribe-events-pro-widgets-v2-events-list-skeleton',
			'widget-events-list-skeleton.css',
			[
				'tribe-events-widgets-v2-events-list-skeleton',
			],
			'wp_print_footer_scripts',
			[
				'priority'     => 5,
				'conditionals' => [
					[ Widget_List::class, 'is_widget_in_use' ],
				],
				'groups' => [
					Widget_List::get_css_group(),
				],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-pro-widgets-v2-events-list-full',
			'widget-events-list-full.css',
			[
				'tribe-events-widgets-v2-events-list-full',
				'tribe-events-pro-widgets-v2-events-list-skeleton',
			],
			'wp_print_footer_scripts',
			[
				'priority'     => 5,
				'conditionals' => [
					'operator' => 'AND',
					[ tribe( TEC_Assets::class ), 'should_enqueue_full_styles' ],
					[ Widget_List::class, 'is_widget_in_use' ],
				],
				'groups' => [
					Widget_List::get_css_group(),
				],
			]
		);

	}
}
