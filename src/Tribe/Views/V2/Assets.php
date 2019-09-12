<?php
/**
 * Handles registering all Assets for the Events V2 Views
 *
 * To remove a Assets:
 * tribe( 'assets' )->remove( 'asset-name' );
 *
 * @since 4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2;

use Tribe__Events__Main as Plugin;
use Tribe\Events\Views\V2\Template_Bootstrap;

/**
 * Register
 *
 * @since 4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
class Assets extends \tad_DI52_ServiceProvider {

	/**
	 * Key for this group of assets.
	 *
	 * @since 4.9.4
	 *
	 * @var string
	 */
	public static $group_key = 'events-views-v2';

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.9.2
	 */
	public function register() {
		$plugin = Plugin::instance();

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-full',
			'views-full.css',
			[ 'tribe-common-style', 'tribe-tooltipster-css' ],
			'wp_enqueue_scripts',
			[
				'priority'     => 10,
				'conditionals' => [ $this, 'should_enqueue_frontend' ],
				'groups'       => [ static::$group_key ],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-bootstrap-datepicker',
			'vendor/bootstrap-datepicker/js/bootstrap-datepicker.js',
			[ 'jquery' ],
			'wp_enqueue_scripts',
			[
				'priority'     => 10,
				'conditionals' => [ $this, 'should_enqueue_frontend' ],
				'groups'       => [ static::$group_key ],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-manager',
			'views/manager.js',
			[
				'jquery',
				'tribe-common',
				'tribe-query-string',
				'underscore',
				'tribe-events-views-v2-viewport',
				'tribe-events-views-v2-accordion',
				'tribe-events-views-v2-view-selector',
				'tribe-events-views-v2-navigation-scroll',
				'tribe-events-views-v2-multiday-events',
				'tribe-events-views-v2-month-mobile-events',
				'tribe-events-views-v2-month-grid',
				'tribe-events-views-v2-tooltip',
				'tribe-events-views-v2-events-bar',
				'tribe-events-views-v2-events-bar-inputs',
				'tribe-events-views-v2-datepicker',
			],
			'wp_enqueue_scripts',
			[
				'priority'     => 20,
				'conditionals' => [ $this, 'should_enqueue_frontend' ],
				'groups'       => [ static::$group_key ],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-viewport',
			'views/viewport.js',
			[ 'jquery', 'tribe-common' ],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-accordion',
			'views/accordion.js',
			[ 'jquery', 'tribe-common' ],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-view-selector',
			'views/view-selector.js',
			[ 'jquery', 'tribe-common', 'tribe-events-views-v2-viewport', 'tribe-events-views-v2-accordion', ],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-navigation-scroll',
			'views/navigation-scroll.js',
			[ 'jquery', 'tribe-common' ],
			null,
			[
				'priority' => 15,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-multiday-events',
			'views/multiday-events.js',
			[ 'jquery', 'tribe-common' ],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-month-mobile-events',
			'views/month-mobile-events.js',
			[ 'jquery', 'tribe-common', 'tribe-events-views-v2-viewport', 'tribe-events-views-v2-accordion' ],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-month-grid',
			'views/month-grid.js',
			[ 'jquery', 'tribe-common' ],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-tooltip',
			'views/tooltip.js',
			[ 'jquery', 'tribe-common', 'tribe-tooltipster' ],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-events-bar',
			'views/events-bar.js',
			[ 'jquery', 'tribe-common', 'tribe-events-views-v2-accordion' ],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-events-bar-inputs',
			'views/events-bar-inputs.js',
			[ 'jquery', 'tribe-common' ],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-datepicker',
			'views/datepicker.js',
			[ 'jquery', 'tribe-common', 'tribe-events-views-v2-bootstrap-datepicker' ],
			null,
			[
				'priority' => 10,
			]
		);

		/**
		 * @todo: remove once we can not load v1 scripts in v2
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'disable_v1' ], 200 );
	}

	/**
	 * Removes assets from View V1 when V2 is loaded.
	 *
	 * @since 4.9.5
	 *
	 * @return void
	 */
	public function disable_v1() {
		wp_deregister_script( 'tribe-events-calendar-script' );
	}

	/**
	 * Checks if we should enqueue frontend assets for the V2 views
	 *
	 * @since 4.9.4
	 *
	 * @return bool
	 */
	public function should_enqueue_frontend() {

		$should_enqueue = tribe( Template_Bootstrap::class )->should_load();

		/**
		 * Allow filtering of where the base Frontend Assets will be loaded
		 *
		 * @since 4.9.4
		 *
		 * @param bool $should_enqueue
		 */
		return apply_filters( 'tribe_events_views_v2_assets_should_enqueue_frontend', $should_enqueue );
	}
}
