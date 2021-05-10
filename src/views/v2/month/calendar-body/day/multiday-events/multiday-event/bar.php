<?php
/**
 * View: Month View - Multiday Event Event Bar
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/multiday-events/multiday-event/bar.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @since 5.1.1
 *
 * @var boolean $should_display If the event starts today and this week.
 * @var string $grid_start_date The `Y-m-d` date of the day where the grid starts.
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 *
 * @version 5.1.1
 */

/*
 * To keep the calendar accessible, in the context of a week, we only print
 * the event bar on the first day of the event or the first day of the week.
 */
if (
	! $is_start_of_week
	&& ! in_array( $day_date, $event->displays_on, true )
) {
	return;
}
?>
<div class="tribe-events-calendar-month__multiday-event-bar">
	<div class="tribe-events-calendar-month__multiday-event-bar-inner">
		<?php $this->template( 'month/calendar-body/day/multiday-events/multiday-event/bar/featured', [ 'event' => $event ] ); ?>
		<?php $this->template( 'month/calendar-body/day/multiday-events/multiday-event/bar/title', [ 'event' => $event ] ); ?>
	</div>
</div>
