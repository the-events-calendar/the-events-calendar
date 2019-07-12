<?php
/**
 * Functions and template tags dedicated to Events.
 *
 * @since TBD
 */

use Tribe\Utils\Lazy_Collection;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Timezones as Timezones;

if ( ! function_exists( 'tribe_get_event' ) ) {
	/**
	 *
	 *
	 * @since TBD
	 *
	 * @param null   $event
	 * @param string $output
	 * @param string $filter
	 *
	 * @return array|mixed|void|WP_Post|null
	 * @throws Exception
	 */
	function tribe_get_event( $event = null, $output = OBJECT, $filter = 'raw' ) {
		// @todo support week in filter arg.
		/**
		 * Filters the event result before any logic applies.
		 *
		 * Returning a non `null` value here will short-circuit the function and return the value.
		 *
		 * @since TBD
		 *
		 * @param mixed  $return           The event object to return.
		 * @param mixed  $event            The event object to fetch.
		 * @param string $output           Optional. The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which
		 *                                 correspond to a `WP_Post` object, an associative array, or a numeric array,
		 *                                 respectively. Default OBJECT.
		 * @param string $filter           Optional. Type of filter to apply. Accepts 'raw', 'edit', 'db' or 'display'.
		 *                                 Default 'raw'.
		 */
		$return = apply_filters( 'tribe_get_event_before', null, $event, $output, $filter );

		if ( null !== $return ) {
			return $return;
		}

		$post = get_post( $event );

		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		// @todo try cache here

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
		$start_date_object = new DateTimeImmutable( $start_date, $timezone );
		$end_date_object   = new DateTimeImmutable( $end_date, $timezone );
		$end_of_day_object = new DateTimeImmutable( $end_of_day, $timezone );
		$is_multiday       = $end_of_day_object < $end_date_object;
		$multiday          = false;
		// Without a context these values will not make sense; we'll set them if the `$filter` argument is a date.
		$starts_this_week = null;
		$ends_this_week   = null;
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
			$starts_this_week = $week_start <= $start_date_object && $start_date_object < $week_end;
			$ends_this_week   = $week_start <= $end_date_object && $end_date_object < $week_end;
		}
		// This query is bound by `posts_per_page` and it's fine and reasonable; do not make it unbound.
		$organizer_fetch = static function () use ( $post_id ) {
			// @todo move to method and filter.
			$organizer_ids = (array) get_post_meta( $post_id, '_EventOrganizerID' );
			if ( empty( $organizer_ids ) ) {
				return [];
			}
			$organizer_ids = (array) tribe_organizers()
				->by( 'event', $post_id )
				->order_by( 'post__in', $organizer_ids )
				->get_ids();
			$organizers    = ! empty( $organizer_ids )
				? array_map( 'tribe_get_organizer', $organizer_ids )
				: [];

			return $organizers;
		};

		$venue_fetch = static function () use ( $post_id ) {
			// @todo move to method and filter.
			$venue = tribe_venues()->by( 'event', $post_id )->first();
			$venue = $venue instanceof WP_Post ? tribe_get_venue( $venue->ID ) : false;

			return $venue;
		};

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

		$properties = [
			'start_date'       => $start_date,
			'start_date_utc'   => $start_date_utc,
			'end_date'         => $end_date,
			'end_date_utc'     => $end_date_utc,
			'timezone'         => $timezone_string,
			'duration'         => $duration,
			'multiday'         => $multiday,
			'all_day'          => $all_day,
			'starts_this_week' => $starts_this_week,
			'ends_this_week'   => $ends_this_week,
			'featured'         => $featured,
			'organizers'       => new Lazy_Collection( $organizer_fetch ),
			'venues'           => new Lazy_Collection( $venue_fetch ),
		];

		// @todo filter here
		// @todo cache here

		foreach ( $properties as $key => $value ) {
			$post->{$key} = $value;
		}

		return $post;
	}
}