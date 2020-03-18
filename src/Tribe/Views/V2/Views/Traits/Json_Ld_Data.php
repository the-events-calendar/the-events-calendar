<?php
/**
 * Provides methods for Views to produce JSON-LD structured output.
 *
 * View classes using this Trait can define a `cache_key` property to cache the JSON-LD data output.
 *
 * @since   5.0.2
 *
 * @package Tribe\Events\Views\V2\Views\Traits
 */

namespace Tribe\Events\Views\V2\Views\Traits;

use Tribe__Cache_Listener as Listener;

/**
 * Trait Json_Ld_Data
 *
 * @since   5.0.2
 *
 * @package Tribe\Events\Views\V2\Views\Traits
 */
trait Json_Ld_Data {

	/**
	 * Build the JSON-LD markup for a View provided events.
	 *
	 * @since 5.0.2
	 *
	 * @param array<\WP_Post|int|array<string,int>> $events Either a list of event post objects or IDs (e.g. List View)
	 *                                                      or a map of days and events per day (e.g. Month View).
	 *
	 * @return string The JSON-LD information corresponding to the events, or an empty string if there are no events
	 *                or there was an issue building the JSON-LD output.
	 */
	protected function build_json_ld_data( array $events = [] ) {
		if ( method_exists( $this, 'get_cache_html_key' ) ) {
			$cache_key = $this->get_cache_html_key() . 'json_ld';
			/** @var \Tribe__Cache $cache */
			$cache  = tribe( 'cache' );
			$cached = $cache->get( $cache_key, Listener::TRIGGER_SAVE_POST );

			if ( false !== $cached ) {
				tribe_cache()['json-ld-data'] = array_filter(
					array_merge( (array) tribe_cache()['json-ld-data'],
					[ $cached ] )
				);

				return $cached;
			}
		}

		if ( empty( $events ) ) {
			return '';
		}

		$idify = static function ( $event ) {
			return $event instanceof \WP_Post ? $event->ID : absint( $event );
		};

		$canary          = reset( $events );
		$is_nested_array = ! $canary instanceof \WP_Post;
		if ( $is_nested_array && is_array( $events ) ) {
			$events = array_merge( ...array_values( $events ) );
		}

		$event_ids = array_unique( array_map( $idify, $events ) );

		$json_ld_data = \Tribe__Events__JSON_LD__Event::instance()->get_markup( $event_ids );

		tribe_cache()['json-ld-data'] = array_filter(
			array_merge( (array) tribe_cache()['json-ld-data'],
				[ $json_ld_data ] )
		);

		if ( isset( $cache_key ) ) {
			/** @var \Tribe__Cache $cache */
			$cache = tribe( 'cache' );
			$cache->set( $cache_key, $json_ld_data, DAY_IN_SECONDS, Listener::TRIGGER_SAVE_POST );
		}

		return $json_ld_data;
	}
}
