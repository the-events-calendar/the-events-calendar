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
		if ( ! is_array( $events ) ) {
			return false;
		}

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

	/**
	 * Determines if a given event from a list of events should have a time separator
	 * for the Day view template structure. Rounded down to the hour.
	 *
	 * @since  4.9.5
	 *
	 * @param  array       $events WP_Post or numeric ID for events
	 * @param  WP_Post|int $event  Event we want to determine
	 *
	 * @return boolean
	 */
	public static function should_have_time( $events, $event ) {
		if ( ! is_array( $events ) ) {
			return false;
		}

		$ids = array_map( static function( $event ) {
			return absint( is_numeric( $event ) ? $event : $event->ID );
		}, $events );

		$event_id = is_numeric( $event ) ? $event : $event->ID;

		$start_hours = array_map( static function( $id ) {
			return tribe_get_start_date( $id, true, 'Y-m-d H' );
		}, $ids );

		$start_hours_ids = array_unique( array_combine( $ids, $start_hours ) );

		return isset( $start_hours_ids[ $event_id ] );
	}

	/**
	 * Determines if a given event from a list of events should have a type separator
	 * for the day view template structure.
	 *
	 * @since  4.9.11
	 *
	 * @param  array       $events WP_Post or numeric ID for events.
	 * @param  WP_Post|int $event  Event we want to determine.
	 *
	 * @return boolean
	 */
	public static function should_have_type( array $events, \WP_Post $event ) {
		if ( ! is_array( $events ) ) {
			return false;
		}

		$event_id = $event->ID;

		if ( empty( $event->timeslot ) ) {
			return false;
		}

		$ids = array_map(
			static function( $event ) {
				return absint( is_numeric( $event ) ? $event : $event->ID );
			},
			$events
		);


		$index = array_search( $event_id, $ids );

		// Return false if it wasn't found.
		if ( false === $index ) {
			return $index;
		}

		$is_first        = 0 === $index;
		$is_new_timeslot = ! $is_first && $events[ $index ]->timeslot !== $events[ $index - 1 ]->timeslot;

		// Should have type separator if it's the first element or if it's a new timeslot.
		$should_have = $is_first || $is_new_timeslot;

		return $should_have;
	}
}