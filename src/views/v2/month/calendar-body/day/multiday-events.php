<?php
/**
 * View: Month View - Multiday Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day/multiday-events.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */
$day_number = $this->get( 'day_number' );

/**
 * Adding this as a temprorary data structure.
 * @todo: This array should contain the month with real events.
 */
$month_data = apply_filters( 'tribe_events_views_v2_month_demo_data', [] );

// Get the multiday events for that day
// @todo: This is a function with demo purposes.
// @todo: When BE is ready, this should be replaced with $day['multiday_events'];
$multiday_events = tribe_events_views_v2_month_demo_day_get_events_multiday( $month_data, $day_number );

if ( ! $multiday_events ) {
	return;
}
?>

<?php foreach ( $multiday_events as $event ) : ?>

	<?php
	// we receive false, we need to fill an empty space
	if ( false === $event ) {
		$this->template( 'month/calendar-body/day/multiday-events/multiday-event-spacer' );
		continue;
	}

	$this->template( 'month/calendar-body/day/multiday-events/multiday-event', [ 'event' => (object) $event, 'day' => $day_number ] );
	?>

<?php endforeach; ?>
