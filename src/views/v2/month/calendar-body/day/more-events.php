<?php
/**
 * View: Month View - More Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day/more-events.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
$day_number = $this->get( 'day' );

/**
 * Adding this as a temprorary data structure.
 * @todo: This array should contain the month with real events.
 */
$month_data = apply_filters( 'tribe_events_views_v2_month_demo_data', [] );

// Get the calendar events for that day
// @todo: This is a function with demo purposes.
// @todo: When BE is ready, this should be replaced with $day['events'];
$calendar_events = tribe_events_views_v2_month_demo_day_get_events_regular( $month_data, $day_number );

// Bail if there are no events
if ( ! $calendar_events ) {
	return;
}
?>
<div class="tribe-events-calendar-month__more-events">
	<a href="#" class="tribe-events-calendar-month__more-events-link tribe-common-h8 tribe-common-h--alt tribe-common-anchor-thin">+ 2 More</a>
</div>
