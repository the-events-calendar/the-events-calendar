<?php
/**
 * View: List View - Single Event Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/event/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

$event_id = $event->ID;
?>
<div class="tribe-events-calendar-list__event--datetime">
	<time datetime="1970-01-01T00:00:00+00:00">
		<?php echo tribe_events_event_schedule_details(); ?>
	</time>
</div>