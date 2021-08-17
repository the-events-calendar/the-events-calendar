<?php
/**
 * View: Day Single Event Description
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/day/event/description.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 5.0.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( (string) $event->excerpt ) ) {
	return;
}
?>
<div class="tribe-events-calendar-day__event-description tribe-common-b2 tribe-common-a11y-hidden">
	<?php echo (string) $event->excerpt; ?>
</div>
