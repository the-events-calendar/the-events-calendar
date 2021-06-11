<?php
/**
 * View: Month View - Multiday Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/multiday-events.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.9.4
 *
 * @var string $day_date         The `Y-m-d` date of the day currently being displayed.
 * @var array  $multiday_events  An array representing the day "stack" of multi-day events. The stack is composed of
 *                               events post objects (instances of the the `WP_Post` class with additional properties)
 *                               and spacer indicators.
 *                               The stack is pre-calculated for the day, events and spacers are in the correct order.
 * @var bool   $is_start_of_week Whether the current day is the first day in the week or not.
 *
 * @see tribe_get_event() For the format of the event object and its properties.
 */

if ( 0 === count( $multiday_events ) ) {
	return;
}
?>

<?php foreach ( $multiday_events as $event ) : ?>
	<?php
	// If we receive a falsy value, then we need to add a spacer in the "stack".
	if ( false === $event ) {
		$this->template( 'month/calendar-body/day/multiday-events/multiday-event-spacer' );
		continue;
	}

	$this->setup_postdata( $event );

	$this->template( 'month/calendar-body/day/multiday-events/multiday-event', [
		'day_date'         => $day_date,
		'event'            => $event,
		'is_start_of_week' => $is_start_of_week,
	] );
	?>

<?php endforeach; ?>
