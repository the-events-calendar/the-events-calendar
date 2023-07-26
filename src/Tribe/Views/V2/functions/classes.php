<?php
/**
 * Calendar Class Functions
 *
 * @since 5.1.1
 */
namespace Tribe\Events\Views\V2;

use Tribe__Date_Utils as Dates;

// Don't load directly!
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Main' ) ) {
	return;
}

/**
 * Used in the multiday month loop.
 * Outputs classes for the multiday event (article).
 *
 * @since 5.1.1
 *
 * @param WP_Post $event            An event post object with event-specific properties added from the the `tribe_get_event`
 *                                  function.
 * @param string  $day_date         The `Y-m-d` date of the day currently being displayed.
 * @param bool    $is_start_of_week Whether the current grid day being rendered is the first day of the week or not.
 * @param string  $today_date       Today's date in the `Y-m-d` format.
 *
 * @return array<string> $classes   The classes to add to the multiday event.
 */
function month_multiday_classes( $event, $day_date, $is_start_of_week, $today_date ) {
	$classes = tribe_get_post_class( [ 'tribe-events-calendar-month__multiday-event' ], $event->ID );

	if ( ! empty( $event->featured ) ) {
		$classes[] = 'tribe-events-calendar-month__multiday-event--featured';
	}

	/*
	 * To keep the calendar accessible, in the context of a week, we'll print the event only on either its first day
	 * or the first day of the week.
	 */
	$should_display = $is_start_of_week || in_array( $day_date, $event->displays_on, true );

	// If doesn't start today and this week, let's not add the left border or set the width.
	if ( ! $should_display ) {
		/**
		 * Allows filtering the multiday event classes.
		 *
		 * @since 5.1.1
		 *
		 * @param array<string> $classes    An array of thee classes to be applied.
		 * @param WP_Post $event            An event post object with event-specific properties added from the the `tribe_get_event`
		 *                                  function.
		 * @param string  $day_date         The `Y-m-d` date of the day currently being displayed.
		 * @param bool    $is_start_of_week Whether the current grid day being rendered is the first day of the week or not.
		 * @param string  $today_date       Today's date in the `Y-m-d` format.
		 */
		return apply_filters( 'tribe_events_views_v2_month_multiday_classes', $classes, $event, $day_date, $is_start_of_week, $today_date );
	}

	/*
	* The "duration" here is how many days the event will take this week, not in total.
	* The two values might be the same but they will differ for events that last more than one week.
	*/
	$classes[] = 'tribe-events-calendar-month__multiday-event--width-' . $event->this_week_duration;
	$classes[] = 'tribe-events-calendar-month__multiday-event--display';

	// If it ends this week, let's add the start class (left border).
	if ( $event->starts_this_week ) {
		$classes[] = 'tribe-events-calendar-month__multiday-event--start';
	}

	// If it ends this week, let's add the end class (right border).
	if ( $event->ends_this_week ) {
		$classes[] = 'tribe-events-calendar-month__multiday-event--end';
	}

	if ( $event->dates->end->format( 'Y-m-d' ) < $today_date ) {
		$classes[] = 'tribe-events-calendar-month__multiday-event--past';
	}

	/**
	 * Allows filtering the multiday event classes.
	 *
	 * @since 5.1.1
	 *
	 * @param array<string> $classes    An array of thee classes to be applied.
	 * @param WP_Post $event            An event post object with event-specific properties added from the the `tribe_get_event`
	 *                                  function.
	 * @param string  $day_date         The `Y-m-d` date of the day currently being displayed.
	 * @param bool    $is_start_of_week Whether the current grid day being rendered is the first day of the week or not.
	 * @param string  $today_date       Today's date in the `Y-m-d` format.
	 */
	return apply_filters( 'tribe_events_views_v2_month_multiday_classes', $classes, $event, $day_date, $is_start_of_week, $today_date );
}

/**
 * Used in the Month View days loop.
 * Outputs classes for each day "cell".
 *
 * @since 6.0.2
 *
 * @param array<mixed> $day          The current day data.
 * @param string       $day_date     The current day date, in the `Y-m-d` format.
 * @param \DateTime    $request_date The request date for the view.
 * @param string       $today_date   Today's date in the `Y-m-d` format.
 *
 * @return array<string,bool> $day_classes The classes to add to the day "cell".
 */
function month_day_classes( array $day, string $day_date, \DateTime $request_date, string $today_date ) {
	// If for some reason we don't have a request date, use today's date.
	$comparison_date = ! empty( $request_date ) ? $request_date->format( 'Y-m-d' ) : $today_date;

	/**
	 * Allows filtering the date used for comparison when generating the Month View day cell classes.
	 *
	 * @since 6.0.2
	 *
	 * @param string       $comparison_date The date used for comparisons.
	 * @param DateTime     $request_date    The request date for the view.
	 * @param string       $day_date        The current day date, in the `Y-m-d` format.
	 * @param array<mixed> $day             The current day data.
	 */
	$comparison_date =  apply_filters( 'tec_events_month_day_classes_comparison_date', $comparison_date, $request_date, $day_date, $day  );

	// Convert it to a date object.
	$comparison_date = Dates::immutable( $comparison_date );

	// Classes in array are applied if the value is truthy, not applied if the value is falsy.
	$day_classes = [
		'tribe-events-calendar-month__day'              => true,
		// Add a class for the current day.
		'tribe-events-calendar-month__day--current'     => $comparison_date->format( 'Y-m-d' ) === $day_date,
		// Add a class for the past days (includes days in the requested month).
		'tribe-events-calendar-month__day--past'        => $comparison_date->format( 'Y-m-d' ) > $day_date,
		// Not the requested month.
		'tribe-events-calendar-month__day--other-month' => $day[ 'month_number' ] !== $comparison_date->format( 'm' ),
		// Past month.
		'tribe-events-calendar-month__day--past-month'  => $day[ 'month_number' ] < $comparison_date->format( 'm' ),
		// Future month.
		'tribe-events-calendar-month__day--next-month'  => $day[ 'month_number' ] > $comparison_date->format( 'm' ),
	];

	/**
	 * Allows filtering the final list of classes for each Month View day cell.
	 *
	 * @since 6.0.2
	 *
	 * @param array<string,bool> $day_classes     The classes to add to the day "cell".
	 * @param string             $comparison_date The date that was used for comparisons.
	 * @param array<mixed>       $day             The current day data.
	 *
	 * @return array<string> $day_classes The final list of classes to add to the day "cell".
	 */
	return (array) apply_filters( 'tec_events_month_day_classes', $day_classes, $comparison_date, $day );
}
