<?php
/**
 * Month view demo data and temporary template tags to handle the data.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.4
 */

// @todo: remove this when we hydrate the month view with data and we use the correct template tags.

if ( ! function_exists( 'tribe_events_views_v2_month_demo_day_get_data' ) ) :
/**
 * Get the day data of the $month array
 *
 * @since 4.9.4
 *
 * @return bool
 */
function tribe_events_views_v2_month_demo_day_get_data( $month = [], $day_number = 0 ) {
	$key = array_search( $day_number, array_column( $month, 'daynum' ) );

	return false !== $key ? $month[ $key ] : $key;
}
endif;

if ( ! function_exists( 'tribe_events_views_v2_month_demo_day_has_events' ) ) :
/**
 * Check if a given day has events
 *
 * @since 4.9.4
 *
 * @return bool
 */
function tribe_events_views_v2_month_demo_day_has_events( $month = [], $day_number = 0 ) {

	if ( ! $day_data = tribe_events_views_v2_month_demo_day_get_data( $month, $day_number ) ) {
		return false;
	}

	return (bool) $day_data[ 'total_events' ];
}
endif;


if ( ! function_exists( 'tribe_events_views_v2_month_demo_day_get_events' ) ) :
/**
 * Get events for a given day
 *
 * @since 4.9.4
 *
 * @return array
 */
function tribe_events_views_v2_month_demo_day_get_events( $month = [], $day_number = 0 ) {

	if ( ! $has_events = tribe_events_views_v2_month_demo_day_has_events( $month, $day_number ) ) {
		return false;
	}

	$day_data = tribe_events_views_v2_month_demo_day_get_data( $month, $day_number );

	return $day_data[ 'events' ];
}
endif;


if ( ! function_exists( 'tribe_events_views_v2_month_demo_day_get_events_multiday' ) ) :
/**
 * Get multiday events for a given day
 *
 * @since 4.9.4
 *
 * @return array
 */
function tribe_events_views_v2_month_demo_day_get_events_multiday( $month = [], $day_number = 0 ) {
	$events = tribe_events_views_v2_month_demo_day_get_events( $month, $day_number );

	if ( ! $events ) {
		return [];
	}

	$multiday = [];

	foreach ( $events as $event ) {

		// Add it to the array if multiday is set and multiday is true.
		// Or if it false (we are using this as empty spaces)
		if (
			( isset( $event['multiday'] ) && $event['multiday'] )
			|| false === $event
		) {
			$multiday[] = $event;
		}
	}

	return $multiday;
}
endif;


if ( ! function_exists( 'tribe_events_views_v2_month_demo_day_get_events_regular' ) ) :
/**
 * Get regular events for a given day.
 *
 * @since 4.9.4
 *
 * @return array
 */
function tribe_events_views_v2_month_demo_day_get_events_regular( $month = [], $day_number = 0 ) {
	$events = tribe_events_views_v2_month_demo_day_get_events( $month, $day_number );

	if ( ! $events ) {
		return [];
	}

	$regular = [];

	foreach ( $events as $event ) {

		if ( false === $event ) {
			// it's an empty space, continue with the next one.
			continue;
		}

		// Add it to the new array if multiday exists and is set to false.
		// or if multiday is not set.
		if (
			( isset( $event['multiday'] ) && ! $event['multiday'] )
			|| ! isset( $event['multiday'] )
		) {
			$regular[] = $event;
		}
	}

	return $regular;
}
endif;


if ( ! function_exists( 'tribe_events_views_v2_month_demo_add_data' ) ) :
/**
 * Return an array with the events for the month
 *
 * @since 4.9.4
 *
 * @return array
 */
function tribe_events_views_v2_month_demo_add_data() {

	$event1 = [
			'title'         => esc_html__( 'WordCamp Buenos Aires', 'the-events-calendar' ),
			'ID'            => 1,
			'multiday'      => true,
			'start_date'    => 1,
			'start_this_week' => true,
			'end_this_week' => true,
			'duration'      => 2, // duration in days
		];

	$event2 = [
			'title'         => esc_html__( 'TedX Argentina', 'the-events-calendar' ),
			'ID'            => 14,
			'multiday'      => true,
			'start_date'    => 1,
			'start_this_week' => true,
			'end_this_week' => true,
			'duration'      => 3, // duration in days
			'featured'      => true,
		];

	$event3 = [
			'title'         => esc_html__( 'UX and UI Workshop', 'the-events-calendar' ),
			'ID'            => 18,
			'multiday'      => true,
			'start_date'    => 2,
			'start_this_week' => true,
			'end_this_week' => true,
			'duration'      => 3, // duration in days
		];

	$event4 = [
			'title'         => esc_html__( 'Contributors hackaton', 'the-events-calendar' ),
			'ID'            => 23,
			'multiday'      => true,
			'start_date'    => 4,
			'start_this_week' => true,
			'end_this_week' => true,
			'duration'      => 2, // duration in days
			'featured'      => true,
		];

	$event_multi_5 = [
			'title'         => esc_html__( 'Prepping long weekend', 'the-events-calendar' ),
			'ID'            => 25,
			'multiday'      => true,
			'start_date'    => 19,
			'start_this_week' => true,
			'end_this_week' => true,
			'duration'      => 2, // duration in days
			'featured'      => false,
		];

	$event_multi_weekend_pre = [
			'title'         => esc_html__( 'Long weekend', 'the-events-calendar' ),
			'ID'            => 40,
			'multiday'      => true,
			'start_date'    => 19,
			'start_this_week' => true,
			'end_this_week' => false,
			'duration'      => 3, // duration in days
			'featured'      => false,
		];

	$event_multi_weekend_post = [
			'title'         => esc_html__( 'Long weekend', 'the-events-calendar' ),
			'ID'            => 40,
			'multiday'      => true,
			'start_date'    => 22,
			'end_this_week' => true,
			'start_this_week' => false,
			'duration'      => 2, // duration in days
			'featured'      => false,
		];

	$event_not_multi_1 = [
			'title'         => esc_html__( 'Melbourne WordPress Meetup', 'the-events-calendar' ),
			'ID'            => 57,
			'multiday'      => false,
			'featured'      => true,
			'recurring'     => false,
		];

	$event_not_multi_2 = [
		'title'         => esc_html__( 'North Sydney Meetup', 'the-events-calendar' ),
		'ID'            => 53,
		'multiday'      => false,
		'featured'      => true,
		'recurring'     => false,
		'image'         => 'https://cldup.com/xfPL3E4sMe-2000x2000.jpeg',
	];

	$event_not_multi_3 = [
		'title'         => esc_html__( 'HackNight #CodeforthePeople', 'the-events-calendar' ),
		'ID'            => 59,
		'multiday'      => false,
		'featured'      => false,
		'recurring'     => false,
		'image'         => 'https://cldup.com/GkpQuY_i8k-1200x1200.jpeg',
	];

	$event_not_multi_4 = [
		'title'         => esc_html__( 'Winnipeg WordPress Meetup', 'the-events-calendar' ),
		'ID'            => 39,
		'multiday'      => false,
		'featured'      => false,
		'recurring'     => false,
		'image'         => 'https://cldup.com/Nhp8FNOyBl-2000x2000.jpeg',
	];

	$event_not_multi_5 = [
		'title'         => esc_html__( 'Hannover WordPress Meetup', 'the-events-calendar' ),
		'ID'            => 89,
		'multiday'      => false,
		'featured'      => false,
		'recurring'     => false,
	];

	$event_not_multi_recurring = [
		'title'         => esc_html__( 'Taco Tuesdays!', 'the-events-calendar' ),
		'ID'            => 69,
		'multiday'      => false,
		'featured'      => false,
		'recurring'     => true,
	];


	// Day 1
	$day_1_events = [ $event1, $event2 ];

	$day1 = [
			'daynum' => 1,
			'events' => $day_1_events,
			'total_events' => count( $day_1_events ),
		];

	// Day 2
	$day_2_events = [ $event1, $event2, $event3 ];

	$day2 = [
			'daynum' => 2,
			'events' => $day_2_events,
			'total_events' => count( $day_2_events )
		];

	// Day 3
	$day_3_events = [ false, $event2, $event3 ];

	$day3 = [
			'daynum' => 3,
			'events' => $day_3_events,
			'total_events' => count( $day_3_events )
		];

	// Day 4
	$day_4_events = [ $event4, false, $event3 ];

	$day4 = [
			'daynum' => 4,
			'events' => $day_4_events,
			'total_events' => count( $day_4_events )
		];

	// Day 5
	$day_5_events = [ $event4, $event3 ];

	$day5 = [
			'daynum' => 5,
			'events' => $day_5_events,
			'total_events' => count( $day_5_events )
		];

	// Day 6
	$day_6_events = [ $event_not_multi_2 ];
	$day6 = [
		'daynum' => 6,
		'events' => $day_6_events,
		'total_events' => count( $day_6_events )
	];

	$day7 = [];

	$week1 = [ $day1, $day2, $day3, $day4, $day5, $day6 ];

	/*
		Week 2
	*/

	$day_9_events = [ $event_not_multi_recurring, $event_not_multi_1 ];

	$day9 = [
			'daynum' => 9,
			'events' => $day_9_events,
			'total_events' => count( $day_9_events )
		];

	$day_10_events = [ $event_not_multi_3 ];

	$day10 = [
			'daynum' => 10,
			'events' => $day_10_events,
			'total_events' => count( $day_10_events )
		];

	$week2 = [ $day9, $day10 ];

	/*
		Week 3
	*/

	$day_16_events = [ $event_not_multi_recurring ];

	$day16 = [
			'daynum' => 16,
			'events' => $day_16_events,
			'total_events' => count( $day_16_events )
		];

	$day_19_events = [ $event_multi_weekend_pre, $event_multi_5 ];

	$day19 = [
			'daynum' => 19,
			'events' => $day_19_events,
			'total_events' => count( $day_19_events )
		];

	$day_20_events = [ $event_multi_weekend_pre, $event_multi_5 ];

	$day20 = [
			'daynum' => 20,
			'events' => $day_20_events,
			'total_events' => count( $day_20_events )
		];

	$day21 = [
			'daynum' => 21,
			'events' => [ $event_multi_weekend_pre, false ],
			'total_events' => count( [ $event_multi_weekend_pre, false ] )
		];

	$week3 = [ $day16, $day19, $day20, $day21 ];

	/*
		Week 4
	*/

	$day_22_events = [ $event_multi_weekend_post ];

	$day22 = [
			'daynum' => 22,
			'events' => $day_22_events,
			'total_events' => count( $day_22_events )
		];

	$day_23_events = [ $event_multi_weekend_post, $event_not_multi_recurring ];

	$day23 = [
			'daynum' => 23,
			'events' => $day_23_events,
			'total_events' => count( $day_23_events )
		];

	$day26 = [
			'daynum' => 26,
			'events' => [ $event_not_multi_4 ],
			'total_events' => count( [ $event_not_multi_4 ] )
		];

	$day27 = [
			'daynum' => 27,
			'events' => [ $event_not_multi_5 ],
			'total_events' => count( [ $event_not_multi_5 ] )
		];

	$week4 = [ $day22, $day23, $day26, $day27 ];

	$month = array_merge( $week1, $week2, $week3, $week4 );


	return $month;

}
add_filter( 'tribe_events_views_v2_month_demo_data', 'tribe_events_views_v2_month_demo_add_data' );
endif;