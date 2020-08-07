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
	 * @since TBD Refactored the compilation of the list of filters to remove and extracted the `get_filters_to_remove`
	 *        method.
	 */
	public function remove_v1_filters() {
		$filters_to_remove = $this->get_filters_to_remove();

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

	/**
	 * Returns a map of the filters to remove, by filter, or action, handle.
	 *
	 * @since TBD Refactored method out of the `remove_v1_filters` method.
	 *
	 * @param object|null $only_by            An optional instance, or class name, that should be used to filter the map
	 *                                        of filters to remove to only return, for each filter, callables managed by
	 *                                        the specified object or class.
	 *
	 * @return array<string,array<callable>> A map of the callables to remove from the filters or actions, by
	 *                                       filter or action handle.
	 */
	protected function get_filters_to_remove( $only_by = null ) {
		$backcompat = V1_Backcompat::instance();
		$tec_bar    = tribe( 'tec.bar' );

		$filters_to_remove = [
			'query_vars'                       => [
				[ 'callback' => [ TEC::instance(), 'eventQueryVars' ] ],
			],
			'parse_query'                      => [
				[ 'callback' => [ TEC::instance(), 'setDisplay' ], 'priority' => 51 ],
				[ 'callback' => [ $backcompat, 'change_qv_to_list' ], 'priority' => 45 ],
			],
			'wp'                               => [
				[ 'callback' => [ TEC::instance(), 'issue_noindex' ], 'priority' => 10 ],
			],
			'tribe_get_single_option'          => [
				[
					'callback' => [ $backcompat, 'filter_multiday_cutoff' ],
					'priority' => 10,
				],
				[ 'callback' => [ $backcompat, 'filter_enabled_views' ], 'priority' => 10 ],
				[ 'callback' => [ $backcompat, 'filter_default_view' ], 'priority' => 10 ],
			],
			'wp_enqueue_scripts'               => [
				[ 'callback' => [ $tec_bar, 'load_script' ], 'priority' => 9 ]
			],
			'body_class'                       => [
				[ 'callback' => [ $tec_bar, 'body_class' ], 'priority' => 10 ]
			],
			'tribe_events_bar_before_template' => [
				[ 'callback' => [ $tec_bar, 'disabled_bar_before' ], 'priority' => 10 ]
			],
			'tribe_events_bar_after_template'  => [
				[ 'callback' => [ $tec_bar, 'disabled_bar_after' ], 'priority' => 10 ]
			],
		];

		if ( $only_by ) {
			foreach ( $filters_to_remove as &$filter_to_remove ) {
				foreach ( $filter_to_remove as $entry_index => $filter_entry ) {
					if ( $only_by === $filter_entry['callback'][0] ) {
						unset( $filter_to_remove[ $entry_index ] );
					}
				}
			}
		}

		return $filters_to_remove;
	}
}
