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
 * @version 4.9.4
 *
 */
?>
<p class="tribe-events-calendar-month__calendar-event-tooltip-description tribe-common-b3">
	<?php echo tribe_events_get_the_excerpt( $event->ID, wp_kses_allowed_html( 'post' ) ); ?>
</p>
