<?php
/**
 * View: Month View - Single Multiday Event Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/multiday-events/multiday-event/bar/title.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 5.1.1
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 *
 * @version 5.1.1
 */

?>
<h3 class="tribe-events-calendar-month__multiday-event-bar-title tribe-common-h8">
	<?php echo $event->title; // phpcs:ignore  ?>
</h3>
