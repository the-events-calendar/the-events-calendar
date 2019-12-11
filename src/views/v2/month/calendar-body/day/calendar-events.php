<?php
/**
 * View: Month View - Calendar Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/calendar-events.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.8
 *
 * @var array $day_events An array of the day event post objects. Each event is a `WP_Post` instance with additional
 *                        properties as set from the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of each event object.
 */

// Bail if there are no events for day.
if ( empty ( $day_events ) ) {
	return;
}
?>

<?php foreach ( $day_events as $event ) : ?>
	<?php $this->setup_postdata( $event ); ?>

	<?php $this->template( 'month/calendar-body/day/calendar-events/calendar-event', [ 'event' => $event ] ); ?>

<?php endforeach; ?>
