<?php
/**
 * Models an Event.
 *
 * @since   4.9.9
 *
 * @package Tribe\Events\Models\Post_Types
 */

namespace Tribe\Events\Models\Post_Types;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeZone;
use Tribe\Models\Post_Types\Base;
use Tribe\Utils\Lazy_Collection;
use Tribe\Utils\Lazy_String;
use Tribe\Utils\Post_Thumbnail;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Featured_Events as Featured;
use Tribe__Events__Organizer as Organizer;
use Tribe__Events__Timezones as Timezones;
use Tribe__Events__Venue as Venue;

/**
 * Class Event
 *
 * @since   4.9.9
 *
 * @package Tribe\Events\Post_Types
 */
class Event extends Base {


	/**
	 * {@inheritDoc}
	 */
	protected function build_properties( $filter ) {
		try {
			$cache_this = $this->get_caching_callback( $filter );

			$now     = Dates::build_date_object( 'now' );
			$post_id = $this->post->ID;

			$start_date            = get_post_meta( $post_id, '_EventStartDate', true );
			$start_date_utc        = get_post_meta( $post_id, '_EventStartDateUTC', true );
			$end_date              = get_post_meta( $post_id, '_EventEndDate', true );
			$end_date_utc          = get_post_meta( $post_id, '_EventEndDateUTC', true );
			$duration              = (int) get_post_meta( $post_id, '_EventDuration', true );
			$timezone_string       = Timezones::get_event_timezone_string( $post_id );
			$all_day               = tribe_is_truthy( get_post_meta( $post_id, '_EventAllDay', true ) );
			$end_of_day            = tribe_end_of_day( $start_date );
			$timezone              = Timezones::build_timezone_object( $timezone_string );
			$utc_timezone          = new DateTimezone( 'UTC' );
			$start_date_object     = new DateTimeImmutable( $start_date, $timezone );
			$end_date_object       = new DateTimeImmutable( $end_date, $timezone );
			$start_date_utc_object = new DateTimeImmutable( $start_date_utc, $utc_timezone );
			$end_date_utc_object   = new DateTimeImmutable( $end_date_utc, $utc_timezone );
			$end_of_day_object     = new DateTimeImmutable( $end_of_day, $timezone );

			if ( empty( $duration ) ) {
				// This is really an edge case, but here we have the information to rebuild it.
				$duration = $end_date_utc_object->getTimestamp() - $start_date_utc_object->getTimestamp();
			}

			// An event is multi-day if its end date is after the end-of-day cutoff of the start date.
			$is_multiday           = $end_of_day_object < $end_date_object;
			$multiday              = false;

			// Without a context these values will not make sense; we'll set them if the `$filter` argument is a date.
			$starts_this_week      = null;
			$ends_this_week        = null;
			$happens_this_week     = null;
			$this_week_duration    = null;

			if ( Dates::is_valid_date( $filter ) ) {
				$week_start = Dates::build_date_object( $filter, $timezone );
				// Sunday is 0.
				$week_start_day = (int) get_option( 'start_of_week' );
				$offset         = (int) $week_start->format( 'N' ) >= $week_start_day
					? $week_start_day
					: $week_start->format( 'N' ) - $week_start_day;

				$week_start->setISODate( (int) $week_start->format( 'o' ), (int) $week_start->format( 'W' ), $offset );
				$week_end = clone $week_start;
				// 7 days later the week ends.
				$week_end->add( new DateInterval( 'P7D' ) );
				// Inclusive in respect to the start, exclusive to the end.
				$starts_this_week  = $week_start <= $start_date_object && $start_date_object < $week_end;
				$ends_this_week    = $week_start <= $end_date_object && $end_date_object < $week_end;
				$happens_this_week = $week_start <= $end_date_object && $start_date_object <= $week_end;
				if ( $happens_this_week ) {
					$this_week_duration = 1;
					if ( $is_multiday ) {
						/*
						 * We add one second during this calculation to cope with all-day events starting on 12:00 AM.
						 * Due to how DateTime diff works diffing two following midnights would yield a diff of 2 days.
						 */
						$one_second = new DateInterval( 'PT1S' );

						$this_week_duration = min(
							7,
							$week_end->diff( $start_date_object->add( $one_second ) )->days + 1,
							$end_date_object->diff( $week_start )->days + 1,
							$end_date_object->diff( $start_date_object->add( $one_second ) )->days + 1
						);
					}
				}
			}

			// Multi-day events will span at least two days: the day they start on and the following one.
			if ( $is_multiday ) {
				/*
				 * Count the number of cut-offs happening before the end date and add 1.
				 * Do not add 1 for all-day events as they span cut-off to cut-off.
				 */
				$multiday = $all_day ? 0 : 1;
				$one_day  = new DateInterval( 'P1D' );
				// The end date should be inclusive, since it's not in the DatePeriod we work-around it adding a second.
				$period = new DatePeriod( $end_of_day_object, $one_day, $end_date_object );
				foreach ( $period as $date ) {
					++ $multiday;
				};
			}

			$featured        = tribe_is_truthy( get_post_meta( $post_id, Featured::FEATURED_EVENT_KEY, true ) );
			$organizer_fetch = Organizer::get_fetch_callback( $post_id );
			$venue_fetch     = Venue::get_fetch_callback( $post_id );

			$properties = [
				'start_date'             => $start_date,
				'start_date_utc'         => $start_date_utc,
				'end_date'               => $end_date,
				'end_date_utc'           => $end_date_utc,
				'dates'                  => (object) [
					'start'     => $start_date_object,
					'start_utc' => $start_date_utc_object,
					'end'       => $end_date_object,
					'end_utc'   => $end_date_utc_object,
				],
				'timezone'               => $timezone_string,
				'duration'               => $duration,
				'multiday'               => $multiday,
				'is_past'                => $start_date_object < $now,
				'all_day'                => $all_day,
				'starts_this_week'       => $starts_this_week,
				'ends_this_week'         => $ends_this_week,
				'this_week_duration'     => $this_week_duration,
				'happens_this_week'      => $happens_this_week,
				'featured'               => $featured,
				'cost'                   => tribe_get_cost( $post_id, true ),
				'organizers'             => ( new Lazy_Collection( $organizer_fetch ) )->on_resolve( $cache_this ),
				'venues'                 => ( new Lazy_Collection( $venue_fetch ) )->on_resolve( $cache_this ),
				'thumbnail'              => ( new Post_Thumbnail( $post_id ) )->on_resolve( $cache_this ),
				'permalink'              => get_permalink( $post_id ),
				'schedule_details'       => ( new Lazy_String(
					static function () use ( $post_id )
					{
						return tribe_events_event_schedule_details( $post_id );
					}, false
				) )->on_resolve( $cache_this ),
				'plain_schedule_details' => ( new Lazy_String(
					static function () use ( $post_id )
					{
						return tribe_events_event_schedule_details( $post_id, '', '', false );
					}, false
				) )->on_resolve( $cache_this )
			];
		} catch ( \Exception $e ) {
			return [];
		}

		return $properties;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_cache_slug() {
		return 'events';
	}

}