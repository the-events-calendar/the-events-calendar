<?php
/**
 * View: Month View - Day Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/day-events.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
$day_number = $this->get( 'day' );
$month      = $this->get( 'month' );

// Get the multiday events for that day
// @todo: This is a function with demo purposes.
$events_regular = tribe_events_views_v2_month_demo_day_get_events_regular( $month, $day_number );

// Bail if there are no events
if ( ! $events_regular ) {
	return;
}
?>
<div class="tribe-events-calendar-month__day-events">

	<?php foreach ( $events_regular as $event ) : ?>

		<?php $this->template( 'month/calendar-event', [ 'event' => (object) $event, 'day' => $day_number ] ); ?>

	<?php endforeach; ?>

</div>