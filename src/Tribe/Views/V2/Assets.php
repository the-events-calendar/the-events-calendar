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
use Tribe__Events__Templates;

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
	 * Caches the result of the `should_enqueue_frontend` check.
	 *
	 * @since 4.9.13
	 *
	 * @var bool
	 */
	protected $should_enqueue_frontend;

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.9.2
	 */
	public function register() {
		$plugin = Plugin::instance();

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-bootstrap-datepicker-styles',
			'vendor/bootstrap-datepicker/css/bootstrap-datepicker.standalone.css',
			[],
			'wp_enqueue_scripts',
			[
				'priority'     => 10,
				'conditionals' => [ $this, 'should_enqueue_frontend' ],
				'groups'       => [ static::$group_key ],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-skeleton',
			'views-skeleton.css',
			[
				'tribe-common-skeleton-style',
				'tribe-events-views-v2-bootstrap-datepicker-styles',
				'tribe-tooltipster-css',
			],
			'wp_enqueue_scripts',
			[
				'priority'     => 10,
				'conditionals' => [ $this, 'should_enqueue_frontend' ],
				'groups'       => [ static::$group_key ],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-full',
			'views-full.css',
			[
				'tribe-common-full-style',
				'tribe-events-views-v2-skeleton',
			],
			'wp_enqueue_scripts',
			[
				'priority'     => 10,
				'conditionals' => [
					'operator' => 'AND',
					[ $this, 'should_enqueue_frontend' ],
					[ $this, 'should_enqueue_full_styles' ],
				],
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
			[
				'jquery',
				'tribe-common',
				'tribe-events-views-v2-breakpoints',
			],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-accordion',
			'views/accordion.js',
			[
				'jquery',
				'tribe-common',
			],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-view-selector',
			'views/view-selector.js',
			[
				'jquery',
				'tribe-common',
				'tribe-events-views-v2-viewport',
				'tribe-events-views-v2-accordion',
			],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-navigation-scroll',
			'views/navigation-scroll.js',
			[
				'jquery',
				'tribe-common',
			],
			null,
			[
				'priority' => 15,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-multiday-events',
			'views/multiday-events.js',
			[
				'jquery',
				'tribe-common',
			],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-month-mobile-events',
			'views/month-mobile-events.js',
			[
				'jquery',
				'tribe-common',
				'tribe-events-views-v2-viewport',
				'tribe-events-views-v2-accordion',
			],
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
			[
				'jquery',
				'tribe-common',
				'tribe-tooltipster',
			],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-events-bar',
			'views/events-bar.js',
			[
				'jquery',
				'tribe-common',
				'tribe-events-views-v2-viewport',
				'tribe-events-views-v2-accordion',
			],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-events-bar-inputs',
			'views/events-bar-inputs.js',
			[
				'jquery',
				'tribe-common',
			],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-datepicker',
			'views/datepicker.js',
			[
				'jquery',
				'tribe-common',
				'tribe-events-views-v2-bootstrap-datepicker',
			],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-breakpoints',
			'views/breakpoints.js',
			[
				'jquery',
				'tribe-common',
			],
			'wp_enqueue_scripts',
			[
				'priority'     => 10,
				'conditionals' => [ $this, 'should_enqueue_frontend' ],
				'groups'       => [ static::$group_key ],
				'in_footer'    => false,
			]
		);

		$overrides_stylesheet = Tribe__Events__Templates::locate_stylesheet( 'tribe-events/tribe-events.css' );

		if ( ! empty( $overrides_stylesheet ) ) {
			tribe_asset(
				$plugin,
				'tribe-events-views-v2-override-style',
				$overrides_stylesheet,
				[
					'tribe-common-full-style',
					'tribe-events-views-v2-skeleton',
				],
				'wp_enqueue_scripts',
				[
					'priority'     => 10,
					'conditionals' => [ $this, 'should_enqueue_frontend' ],
					'groups'       => [ static::$group_key ],
				]
			);
		}
	}

	/**
	 * Removes assets from View V1 when V2 is loaded.
	 *
	 * @since 4.9.5
	 *
	 * @return void
	 */
	public function disable_v1() {
		// Dont disable V1 on Single Event page
		if ( tribe( Template_Bootstrap::class )->is_single_event() ) {
			return;
		}

		add_filter( 'tribe_asset_enqueue_tribe-events-calendar-script', '__return_false' );
		add_filter( 'tribe_asset_enqueue_tribe-events-bar', '__return_false' );
		add_filter( 'tribe_asset_enqueue_the-events-calendar', '__return_false' );
		add_filter( 'tribe_asset_enqueue_tribe-events-ajax-day', '__return_false' );
		add_filter( 'tribe_asset_enqueue_tribe-events-list', '__return_false' );

		add_filter( 'tribe_asset_enqueue_tribe-events-calendar-mobile-style', '__return_false' );
		add_filter( 'tribe_asset_enqueue_tribe-events-calendar-full-mobile-style', '__return_false' );
		add_filter( 'tribe_asset_enqueue_tribe-events-full-calendar-style', '__return_false' );
		add_filter( 'tribe_asset_enqueue_tribe-events-calendar-style', '__return_false' );
		add_filter( 'tribe_asset_enqueue_tribe-events-calendar-override-style', '__return_false' );

		add_filter( 'tribe_events_assets_should_enqueue_frontend', '__return_false' );
	}

	/**
	 * Checks if we should enqueue frontend assets for the V2 views.
	 *
	 * @since 4.9.4
	 * @since 4.9.13 Cache the check value.
	 *
	 * @return bool
	 */
	public function should_enqueue_frontend() {
		if ( null !== $this->should_enqueue_frontend ) {
			return $this->should_enqueue_frontend;
		}

		$should_enqueue = tribe( Template_Bootstrap::class )->should_load();

		/**
		 * Allow filtering of where the base Frontend Assets will be loaded.
		 *
		 * @since 4.9.4
		 *
		 * @param bool $should_enqueue
		 */
		$should_enqueue =  apply_filters( 'tribe_events_views_v2_assets_should_enqueue_frontend', $should_enqueue );

		$this->should_enqueue_frontend = $should_enqueue;

		return $should_enqueue;
	}


	/**
	 * Checks if we are using skeleton setting for Style.
	 *
	 * @since  4.9.11
	 *
	 * @return bool
	 */
	public function is_skeleton_style() {
		$style_option = tribe_get_option( 'stylesheetOption', 'tribe' );
		return 'skeleton' === $style_option;
	}

	/**
	 * Verifies if we dont have skeleton active, which will trigger true for the two other possible options.
	 * Options:
	 * - `full` - Deprecated
	 * - `tribe`  - All styles load
	 *
	 * @since  4.9.11
	 *
	 * @return bool
	 */
	public function should_enqueue_full_styles() {
		$should_enqueue = ! $this->is_skeleton_style();

		/**
		 * Allow filtering of where the base Frontend Assets will be loaded.
		 *
		 * @since 4.9.11
		 *
		 * @param bool $is_skeleton_style
		 */
		return apply_filters( 'tribe_events_views_v2_assets_should_enqueue_full_styles', $should_enqueue );
	}
}
