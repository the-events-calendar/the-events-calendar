<?php
/**
 * Provides methods for Views to produce JSON-LD structured output.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Views\Traits
 */

namespace Tribe\Events\Views\V2\Views\Traits;

/**
 * Trait Json_Ld_Data
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Views\Traits
 */
trait Json_Ld_Data {

	/**
	 * Build the JSON-LD formatted output for a View provided its events.
	 *
	 * @since TBD
	 *
	 * @param array<\WP_Post|int|array<string,int>> $events Either a list of event post objects or IDs (e.g. List View)
	 *                                                      or a map of days and events per day (e.g. Month View).
	 *
	 * @return string The JSON-LD information corresponding to the events, or an empty string if there are no events
	 *                or there was an issue building the JSON-LD output.
	 */
	protected function build_json_ld_data( array $events = [] ) {
		if ( empty( $events ) ) {
			return '';
		}

		$idify = static function ( $event ) {
			return $event instanceof \WP_Post ? $event->ID : absint( $event );
		};

		if ( is_array( $events ) ) {
			$events = array_merge( ...array_values( $events ) );
		}

		$event_ids = array_unique( array_map( $idify, $events ) );

		$json_ld_data = \Tribe__Events__JSON_LD__Event::instance()->get_markup( $event_ids );

		return $json_ld_data;
	}
}
