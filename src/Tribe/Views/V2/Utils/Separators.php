<?php
/**
 * Provides common View v2 utilities.
 *
 * @since   4.9.4
 * @package Tribe\Events\Views\V2\Utils
 */

namespace Tribe\Events\Views\V2\Utils;

use Tribe__Date_Utils as Dates;

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
	 * Note that events will NOT be sorted by date for this check: this is by design. There are other criteria by which
	 * events might be sorted this method should not interfere with.
	 * The method will perform the check using the "display" date of the events since this is a front-end facing method.
	 *
	 * @since  4.9.4
	 *
	 * @param array                          $events       WP_Post or numeric ID for events.
	 * @param \WP_Post|int                   $event        Event we want to check.
	 * @param string|\DateTimeInterface|null $request_date A request date that should be used as context for the
	 *                                                     evaluation.
	 *
	 * @return boolean Whether the event, in the context of this event set, should show the month separator or not.
	 */
	public static function should_have_month( $events, $event, $request_date = null ) {
		if ( ! is_array( $events ) ) {
			return false;
		}

		$events = array_filter( array_map( 'tribe_get_event', $events ), static function ( $event ) {
			return $event instanceof \WP_Post;
		} );

		$event = tribe_get_event( $event );

		if ( empty( $events ) || ! $event instanceof \WP_Post ) {
			return false;
		}

		if ( $event->ID === reset( $events )->ID ) {
			// The first event in a set should always trigger the month separator display.
			return true;
		}

		if ( null !== $request_date ) {
			$request_date = Dates::build_date_object( $request_date );
		}

		// Reduce events to only keep the starting ones.
		$start_events_ids = array_unique(
			array_combine(
				wp_list_pluck( $events, 'ID' ),
				array_map( static function ( \WP_Post $event ) use ( $request_date ) {
					/*
					 * If we have a request date we "move forward" the event to it.
					 * If the event is in this set, then we assume it fits.
					 * This is usually the case w/ multi-day events that start "in the past" in relation to a request
					 * date; in that case we display them not in their original month, but in the request date one.
					 */
					$the_date = null !== $request_date
						? max( $event->dates->start_display, $request_date )
						: $event->dates->start_display;

					return $the_date->format( 'Y-m' );
				}, $events )
			)
		);

		return $event->ID === array_search( $event->dates->start_display->format( 'Y-m' ), $start_events_ids, true );
	}

	/**
	 * Determines if a given event from a list of events should have a time separator
	 * for the Day view template structure. Rounded down to the hour.
	 *
	 * @since  4.9.5
	 *
	 * @param  array       $events WP_Post or numeric ID for events.
	 * @param  \WP_Post|int $event  Event we want to check.
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
			return Dates::round_nearest_half_hour( tribe_get_start_date( $id, true, Dates::DBDATETIMEFORMAT ) );
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
