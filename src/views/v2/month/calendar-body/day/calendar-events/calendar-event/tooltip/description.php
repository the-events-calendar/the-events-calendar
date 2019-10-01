<?php
/**
 * View: Month View - Calendar Event Tooltip Description
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day/calendar-events/calendar-event/tooltip/description.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event->excerpt ) ) {
	return;
}
?>
<div class="tribe-events-calendar-month__calendar-event-tooltip-description tribe-common-b3">
	<?php echo $event->excerpt; ?>
</div>
