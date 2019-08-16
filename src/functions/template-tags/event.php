<?php
/**
 * Functions and template tags dedicated to Events.
 *
 * @since 4.9.7
 */

use Tribe\Utils\Lazy_Collection;
use Tribe\Utils\Post_Thumbnail;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Timezones as Timezones;

if ( ! function_exists( 'tribe_get_event' ) ) {
	/**
	 * Fetches and returns a dedcorated post object representing an Event.
	 *
	 * @since 4.9.7
	 *
	 * @param null|int|WP_Post $event  The event ID or post object or `null` to use the global one.
	 * @param string|null      $output The required return type. One of `OBJECT`, `ARRAY_A`, or `ARRAY_N`, which
	 *                                 correspond to a WP_Post object, an associative array, or a numeric array,
	 *                                 respectively. Defaults to `OBJECT`.
	 * @param string           $filter Type of filter to apply. Accepts 'raw', a valid date string or
	 *                                 object to localize the event in a specific time-frame.
	 *
	 * @return array|mixed|void|WP_Post|null {
	 *                              The Event post object or array, `null` if not found.
	 *
	 *                              @type string $start_date The event start date, in `Y-m-d H:i:s` format.
	 *                              @type string $start_date_utc The event UTC start date, in `Y-m-d H:i:s` format.
	 *                              @type string $end_date The event end date, in `Y-m-d H:i:s` format.
	 *                              @type string $end_date_utc The event UTC end date, in `Y-m-d H:i:s` format.
	 *                              @type array $dates An array containing the event.start, end and UTC date objects. {
	 *                                              @type DateTimeImmutable $start The event start date object.
	 *                                              @type DateTimeImmutable $start_utc The event UTC start date object.
	 *                                              @type DateTimeImmutable $end The event end date object.
	 *                                              @type DateTimeImmutable $end_utc The event UTC end date object.
	 *                                          }
	 *                              @type string $timezone The event timezone string.
	 *                              @type int $duration The event duration in seconds.
	 *                              @type false|int $multiday Whether the event is multi-day or not and its day.
	 *                                                        duration if it is.
	 *                              @type bool $all_day Whether the event is an all-day one or not.
	 *                              @type null|bool $starts_this_week Whether the event starts on the week of the date
	 *                                                                specified in the `$filter` argument or not, `null`
	 *                                                                if no date is specified in the filter.
	 *                              @type null|bool $ends_this_week Whether the event ends on the week of the date
	 *                                                              specified in the `$filter` argument or not, `null`
	 *                                                              if no date is specified in the filter.
	 *                              @type null|bool $happens_this_week Whether the event happens on the week of the date
	 *                                                              specified in the `$filter` argument or not, `null`
	 *                                                              if no date is specified in the filter.
	 *                              @type null|int $this_week_duration The days duration of the event on the week
	 *                                                                 specified in the `$filter` argument, `null`
	 *                                                                 if no date is specified in the filter.
	 *                              @type bool $featured Whether the event is a featured one or not.
	 *                              @type string $cost The event formatted cost string, as returned by the `tribe_get_cost`
	 *                                                 `tribe_get_cost` function.
	 *                              @type Lazy_Collection $organizers A collection of Organizers, lazily fetched and
	 *                                                                eventually resolved to an array.
	 *                              @type Lazy_Collection $venues A collection of Venues, lazily fetched and
	 *                                                            eventually resolved to an array.
	 *                              @type Post_Thumbnail $thumbnail The post thumbnail information.
	 *                          }
	 */
	function tribe_get_event( $event = null, $output = OBJECT, $filter = 'raw' ) {
		/**
		 * Filters the event result before any logic applies.
		 *
		 * Returning a non `null` value here will short-circuit the function and return the value.
		 * Note: this value will not be cached and the caching of this value is a duty left to the filtering function.
		 *
		 * @since 4.9.7
		 *
		 * @param mixed       $return      The event object to return.
		 * @param mixed       $event       The event object to fetch.
		 * @param string|null $output      The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which
		 *                                 correspond to a `WP_Post` object, an associative array, or a numeric array,
		 *                                 respectively. Defaults to `OBJECT`.
		 * @param string      $filter      Type of filter to apply. Accepts 'raw', a valid date string or
		 *                                 object to localize the event in a specific time-frame.
		 */
		$return = apply_filters( 'tribe_get_event_before', null, $event, $output, $filter );

		if ( null !== $return ) {
			return $return;
		}

		$post = get_post( $event );

		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		// Cache by post ID and filter.
		$cache_key = 'events_' . $post->ID . '_' . $filter;
		$cache     = new Tribe__Cache();
		$cached    = $cache->get( $cache_key, Tribe__Cache_Listener::TRIGGER_SAVE_POST );

		if ( false !== $cached ) {
			switch ( $output ) {
				case ARRAY_A:
					return (array) $cached;
				case ARRAY_N:
					return array_values( (array) $cached );
				case OBJECT:
				default;
					return $cached;
			}
		}

		$post_id         = $post->ID;
		$start_date      = get_post_meta( $post_id, '_EventStartDate', true );
		$start_date_utc  = get_post_meta( $post_id, '_EventStartDateUTC', true );
		$end_date        = get_post_meta( $post_id, '_EventEndDate', true );
		$end_date_utc    = get_post_meta( $post_id, '_EventEndDateUTC', true );
		$duration        = (int) get_post_meta( $post_id, '_EventDuration', true );
		$timezone_string = Timezones::get_event_timezone_string( $post_id );
		$all_day         = (bool) get_post_meta( $post_id, '_EventAllDay', true );
		// An event is multi-day if its end date is after the end-of-day cutoff of the start date.
		$end_of_day        = tribe_end_of_day( $start_date );
		$timezone          = Timezones::build_timezone_object( $timezone_string );
		$utc_timezone = new DateTimezone('UTC');
		$start_date_object = new DateTimeImmutable( $start_date, $timezone );
		$end_date_object = new DateTimeImmutable( $end_date, $timezone );
		$start_date_utc_object = new DateTimeImmutable( $start_date_utc, $utc_timezone );
		$end_date_utc_object = new DateTimeImmutable( $end_date_utc, $utc_timezone );
		$end_of_day_object = new DateTimeImmutable( $end_of_day, $timezone );
		$is_multiday       = $end_of_day_object < $end_date_object;
		$multiday          = false;
		// Without a context these values will not make sense; we'll set them if the `$filter` argument is a date.
		$starts_this_week   = null;
		$ends_this_week     = null;
		$happens_this_week  = null;
		$this_week_duration = null;
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
			$starts_this_week   = $week_start <= $start_date_object && $start_date_object < $week_end;
			$ends_this_week     = $week_start <= $end_date_object && $end_date_object < $week_end;
			$happens_this_week = $week_start <= $end_date_object && $start_date_object <= $week_end;
			if ( $happens_this_week ) {
				$this_week_duration = 1;
				if ( $is_multiday ) {
					/*
					 * We add one second during this calculation to cope with all-day events starting on 12:00 AM.
					 * Due to how DateTime diff works diffing two following midnights would yield a diff of 2 days.
					 */
					$one_second = new \DateInterval( 'PT1S' );

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
		$featured = (bool) get_post_meta( $post_id, Tribe__Events__Featured_Events::FEATURED_EVENT_KEY, true );

		$organizer_fetch = Tribe__Events__Organizer::get_fetch_callback( $post_id );
		$venue_fetch     = Tribe__Events__Venue::get_fetch_callback( $post_id );

		$properties = [
			'start_date'         => $start_date,
			'start_date_utc'     => $start_date_utc,
			'end_date'           => $end_date,
			'end_date_utc'       => $end_date_utc,
			'dates'              => (object) [
				'start'     => $start_date_object,
				'start_utc' => $start_date_utc_object,
				'end'       => $end_date_object,
				'end_utc'   => $end_date_utc_object,
			],
			'timezone'           => $timezone_string,
			'duration'           => $duration,
			'multiday'           => $multiday,
			'all_day'            => $all_day,
			'starts_this_week'   => $starts_this_week,
			'ends_this_week'     => $ends_this_week,
			'this_week_duration' => $this_week_duration,
			'happens_this_week'  => $happens_this_week,
			'featured'           => $featured,
			'cost'               => tribe_get_cost( $post_id ),
			'organizers'         => new Lazy_Collection( $organizer_fetch ),
			'venues'             => new Lazy_Collection( $venue_fetch ),
			'thumbnail'          => new Post_Thumbnail( $post_id ),
			'permalink'          => get_permalink( $post_id ),
		];

		foreach ( $properties as $key => $value ) {
			$post->{$key} = $value;
		}

		/**
		 * Filters the event post object before caching it and returning it.
		 *
		 * Note: this value will be cached; as such this filter might not run on each request.
		 * If you need to filter the output value on each call of this function then use the `tribe_get_event_before`
		 * filter.
		 *
		 * @since 4.9.7
		 *
		 * @param WP_Post $post   The event post object, decorated with a set of custom properties.
		 * @param string  $output The output format to use.
		 * @param string  $filter The filter, or context of the fetch.
		 */
		$post = apply_filters( 'tribe_get_event', $post, $output, $filter );

		/*
		 * Cache without expiration, but only until a post of the types managed by The Events Calendar is
		 * updated or created.
		 */
		$cache->set( $cache_key, $post, 0, Tribe__Cache_Listener::TRIGGER_SAVE_POST );

		return $post;
	}
}
