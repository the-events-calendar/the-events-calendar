<?php
/**
 * Handles queries specific to Views v2.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Query
 */

namespace Tribe\Events\Views\V2\Query;

use Tribe__Cache_Listener as Cache_Listener;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;
use Tribe__Utils__Array as Arr;

/**
 * Class Query
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Query
 */
class Query {
	/**
	 * The prefix used to store the values cached by this class.
	 *
	 * @since TBD
	 */
	const CACHE_PREFIX = 'tribe_days_';

	/**
	 * Fetches the event-relevant post information of all events for a period and updates the `tribe_days` cache.
	 *
	 * The result of this method, the one stored in cache ,is a raw one, code using the results of this query is
	 * supposed to manipulate the data to order, filter and sort it!
	 * The method will cache the results for each day in the `tribe_days` cache.
	 * The results stored in cache, each an array, for the period, not sorted, not ordered, are instances of the
	 * `Event_Result` class.
	 *
	 * @since TBD
	 *
	 * @param \DateTimeInterface $start The start date of the period, correct time should be set already.
	 * @param \DateTimeInterface $end The end date of the period, correct time should be set already.
	 */
	public static function update_period_cache( \DateTimeInterface $start, \DateTimeInterface $end ) {
		global $wpdb;

		$use_site_timezone = Timezones::is_mode( Timezones::SITE_TIMEZONE );

		// @todo @lucatume wherever this SQL lives it should have a filterable limit and run until a set does not fill it.
		$query = "
		SELECT p.ID,
		   start_date.meta_value AS 'start_date',
		   end_date.meta_value   AS 'end_date',
		   -- provided the UTC/local time and the event timezone we can always locate it in time, so we pull it here.
		   timezone.meta_value   AS 'timezone',
		   -- we cannot reconstruct if an event is all-day or not from its start and end dates, so we need the flag.
		   all_day.meta_value    AS 'all_day',
		   p.post_status

		FROM {$wpdb->posts} p
				 INNER JOIN (
					SELECT p.ID, start_date.meta_value FROM {$wpdb->posts} p
					INNER JOIN {$wpdb->postmeta} start_date 
						ON (p.ID = start_date.post_id AND start_date.meta_key = %s)
					WHERE p.post_type = 'tribe_events'
					-- Starts before the period end.
					AND start_date.meta_value <= %s
				) start_date ON p.ID = start_date.ID
				 INNER JOIN {$wpdb->postmeta} end_date 
				 	ON (p.ID = end_date.post_id AND end_date.meta_key = %s)
				 INNER JOIN {$wpdb->postmeta} timezone 
				 	ON (p.ID = timezone.post_id AND timezone.meta_key = '_EventTimezone')
				 -- LEFT JOIN to allow NULL post_id if meta key not set.
				 LEFT JOIN {$wpdb->postmeta} all_day 
				 	ON (p.ID = all_day.post_id AND all_day.meta_key = '_EventAllDay')

		WHERE p.post_type = 'tribe_events'
		  -- End after the period start.
		  AND end_date.meta_value >= %s
		  AND (all_day.post_id IS NULL OR all_day.meta_value = 'yes'); ";

		$results = $wpdb->get_results(
			$wpdb->prepare(
				$query,
				$use_site_timezone ? '_EventStartDateUTC' : '_EventStartDate',
				$end->format( Dates::DBDATETIMEFORMAT ),
				$use_site_timezone ? '_EventSTartDateUTC' : '_EventEndDate',
				$start->format( Dates::DBDATETIMEFORMAT )
			),
			ARRAY_A
		);

		try {
			$one_day = new \DateInterval( 'P1D' );
		} catch ( \Exception $e ) {
			// This should not happen, but let's make sure.
			return;
		}

		$request_period = new \DatePeriod( $start, $one_day, $end );

		$grouped_by_start_date = [];
		if ( ! empty( $results ) ) {
			$site_timezone = Timezones::build_timezone_object();

			// @todo refactor this into smaller methods.
			$grouped_by_start_date = array_reduce( $results,
				static function ( array $buffer, array $result ) use ( $use_site_timezone, $site_timezone, $one_day )
				{
					$display_timezone = $use_site_timezone
						? $site_timezone
						: Timezones::build_timezone_object( $result['timezone'] );
					$start_date       = Dates::build_date_object( $result['start_date'], $display_timezone );
					$end_date         = Dates::build_date_object( $result['end_date'], $display_timezone );
					if (
						$start_date->format( Dates::DBDATEFORMAT ) === $end_date->format( Dates::DBDATEFORMAT )
					) {
						$overlapping_days = [ $start_date->format( Dates::DBDATEFORMAT ) ];
					} else {
						/*
						 * "Move" the end date, adding a day to it, to make sure the end date is included in the period.
						 * Else multi-day events would only overlap the first two dates.
						 */
						$moved_end_date = clone $end_date;
						$moved_end_date->add( $one_day );
						$period           = new \DatePeriod( $start_date, $one_day, $moved_end_date );
						$overlapping_days = [];
						/** @var \DateTimeInterface $d */
						foreach ( $period as $d ) {
							// This is skipping the end day on multi-day events.
							$overlapping_days[] = $d->format( Dates::DBDATEFORMAT );
							// Sanity check: break when the current day is equal to the event end date.
							$reached_end = $d->format( Dates::DBDATEFORMAT ) === $end_date->format( Dates::DBDATEFORMAT );
							if ( $reached_end ) {
								break;
							}
						}
					}

					// Normalize the timezone to the site one.
					$result['start_date'] = $start_date->setTimezone( $site_timezone )->format( 'Y-m-d H:i:s' );
					$result['end_date']   = $end_date->setTimezone( $site_timezone )->format( 'Y-m-d H:i:s' );

					foreach ( $overlapping_days as $overlap_day ) {
						if ( isset( $buffer[ $overlap_day ] ) ) {
							$buffer[ $overlap_day ][] = new Event_Result( $result );
						} else {
							$buffer[ $overlap_day ] = [ new Event_Result( $result ) ];
						}
					}

					return $buffer;
				}, [] );
		}

		$cache = new \Tribe__Cache();
		/** @var \DateTime $day */
		foreach ( $request_period as $day ) {
			$day_string = $day->format(Dates::DBDATEFORMAT);
			// Note: unsorted and "raw".
			$day_event_results = Arr::get( $grouped_by_start_date, $day_string, [] );
			$cache->set(
				static::get_cache_key( $day_string ),
				$day_event_results,
				WEEK_IN_SECONDS,
				Cache_Listener::TRIGGER_SAVE_POST
			);
		}
	}

	/**
	 * Fetches the db rows, and updates the caches, for the specified post IDs with a single query.
	 *
	 * @since TBD
	 *
	 * @param array $post_ids An array of post IDs.
	 */
	public static function update_posts_cache( $post_ids ) {
		global $wpdb;

		$already_cached_ids = [];
		foreach ( $post_ids as $post_id ) {
			if ( wp_cache_get( $post_id, 'posts' ) instanceof \WP_Post ) {
				$already_cached_ids[] = $post_id;
			}
		}

		$required = array_diff( $post_ids, $already_cached_ids );

		if ( empty( $required ) ) {
			return;
		}

		// @todo @lucatume this should have a filterable limit and run until it does not fill it.

		$interval     = implode( ',', array_map( 'absint', $post_ids ) );
		$posts_query  = "SELECT * FROM {$wpdb->posts} WHERE ID IN ({$interval})";
		$post_objects = $wpdb->get_results( $posts_query );
		if ( is_array( $post_objects ) && ! empty( $post_objects ) ) {
			foreach ( $post_objects as $post_object ) {
				$post = new \WP_Post( $post_object );
				wp_cache_set( $post_object->ID, $post, 'posts' );
			}
		}
	}

	/**
	 * Returns the cache key used to store a value, including the prefix used by the class.
	 *
	 * @since TBD
	 *
	 * @param string $key The prefixed or non prefixed key.
	 *
	 * @return string The full, prefixed, cache key for a value.
	 */
	public static function get_cache_key( $key ) {
		if ( 0 === strpos( $key, static::CACHE_PREFIX ) ) {
			$key = substr( $key, strlen( static::CACHE_PREFIX ) );
		}

		return self::CACHE_PREFIX . $key;
	}
}
