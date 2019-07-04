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
 * @version 4.9.4
 *
 */
$day_number = $this->get( 'day' );
$month      = $this->get( 'month' );

// Get the calendar events for that day
// @todo: This is a function with demo purposes.
$calendar_events = tribe_events_views_v2_month_demo_day_get_events_regular( $month, $day_number );

// Bail if there are no events
if ( ! $calendar_events ) {
	return;
}
?>
<div class="tribe-events-calendar-month__more-events">
	<a href="#" class="tribe-events-calendar-month__more-events-link tribe-common-h8 tribe-common-h--alt tribe-common-anchor-thin">+ 2 More</a>
</div>
