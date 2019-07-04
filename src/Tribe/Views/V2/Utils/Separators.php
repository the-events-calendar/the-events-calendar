<?php
/**
 * Provides common View v2 utilities.
 *
 * @since   4.9.4
 * @package Tribe\Events\Views\V2\Utils
 */
namespace Tribe\Events\Views\V2\Utils;

use Tribe__Utils__Array as Arr;

/**
 * Class Utils Separators
 * @since   4.9.4
 * @package Tribe\Events\Views\V2\Utils
 */
class Separators {

	/**
	 * Determines if a given event from a list of events should have a month separator
	 * for the List view template structure.
	 *
	 * @since  4.9.4
	 *
	 * @param  array       $events WP_Post or numeric ID for events
	 * @param  WP_Post|int $event  Event we want to determine
	 *
	 * @return boolean
	 */
	public static function should_have_month( $events, $event ) {
		$ids = array_map( static function( $event ) {
			return absint( is_numeric( $event ) ? $event : $event->ID );
		}, $events );

		$event_id = is_numeric( $event ) ? $event : $event->ID;

		$start_dates = array_map( static function( $id ) {
			return tribe_get_start_date( $id, true, 'Y-m' );
		}, $ids );

		$start_month_ids = array_unique( array_combine( $ids, $start_dates ) );

		return isset( $start_month_ids[ $event_id ] );
	}
}