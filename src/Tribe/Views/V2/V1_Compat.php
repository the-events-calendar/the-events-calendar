<?php
/**
 * Handles compatibility with version 1 of the View system.
 *
 * This provider should provide a quick way to know how, where and how we're modifying, updating and removing filters
 * and actions from v1 View system to make v2 play nice with it.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( Tribe\Events\Views\V2\V1_Compat::class ), 'some_filtering_method' ] );
 * remove_filter( 'some_filter', [ tribe( 'views-v2.v1-compat' ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( Tribe\Events\Views\V2\V1_Compat::class ), 'some_method' ] );
 * remove_action( 'some_action', [ tribe( 'views-v2.v1-compat' ), 'some_method' ] );
 *
 * @package Tribe\Events\Views\V2
 * @since 4.9.2
 */

namespace Tribe\Events\Views\V2;

use Tribe__Events__Backcompat as V1_Backcompat;
use Tribe__Events__Main as TEC;
use Tribe__Events__Query as V1_Query;

/**
 * Class V1_Compat
 *
 * @package Tribe\Events\Views\V2
 * @since 4.9.2
 */
class V1_Compat extends \tad_DI52_ServiceProvider {

	/**
	 * Registers the provider and sets it up to update, move or remove Views v1 filters.
	 */
	public function register() {
		/*
		 * Depending on the context of the request, this might fire before or after Common did bootstrap.
		 * Let's handle both cases checking whether Common has already loaded or not.
		 */
		if ( ! did_action( 'tribe_common_loaded' ) ) {
			// Common did not bootstrap yet.
			add_action( 'tribe_common_loaded', [ $this, 'remove_v1_filters' ] );

			return;
		}

		// Common did already bootstrap: let's remove the filters now.
		$this->remove_v1_filters();
	}

	/**
	 * Removes a list of Views v1 filters to ensure a "clean slate" to handle requests using Views v2 logic.
	 *
	 * This method is meant to fire after Common and `Tribe__Events__Main` did bootstrap.
	 *
	 * @since 4.9.2
	 */
	public function remove_v1_filters() {
		$backcompat        = V1_Backcompat::instance();

		$filters_to_remove = [
			'query_vars'              => [
				[ 'callback' => [ TEC::instance(), 'eventQueryVars' ] ],
			],
			'parse_query'             => [
				[ 'callback' => [ TEC::instance(), 'setDisplay' ], 'priority' => 51 ],
				[ 'callback' => [ $backcompat, 'change_qv_to_list' ], 'priority' => 45 ],
				[ 'callback' => [ V1_Query::class, 'parse_query' ], 'priority' => 50 ],
			],
			'pre_get_posts'           => [
				[ 'callback' => [ V1_Query::class, 'pre_get_posts' ], 'priority' => 50 ],
			],
			'posts_results'           => [
				[ 'callback' => [ V1_Query::class, 'posts_results' ], 'priority' => 10 ],
			],
			'wp'                      => [
				[ 'callback' => [ TEC::instance(), 'issue_noindex' ], 'priority' => 10 ],
			],
			'tribe_get_single_option' => [
				[
					'callback' => [ $backcompat, 'filter_multiday_cutoff' ],
					'priority' => 10,
				],
				[ 'callback' => [ $backcompat, 'filter_enabled_views' ], 'priority' => 10 ],
				[ 'callback' => [ $backcompat, 'filter_default_view' ], 'priority' => 10 ],
			],
		];

		foreach ( $filters_to_remove as $tag => $filters ) {
			foreach ( $filters as $filter_data ) {
				$callback = $filter_data['callback'];
				$priority = isset( $filter_data['priority'] ) ? $filter_data['priority'] : 10;
				/*
				 * Why are we not checking with `has_filter` or `has_action` if the filter is actually hooked?
				 * The check is made internally in the `remove_filter` function anyway, it's not efficient to run
				 * the same check twice.
				 */
				remove_filter( $tag, $callback, $priority );
			}
		}
	}
}
