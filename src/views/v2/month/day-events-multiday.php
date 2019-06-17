<?php
/**
 * View: Month View - Day Events Multiday
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/day-events-multiday.php
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
$events_multiday = tribe_events_views_v2_month_demo_day_get_events_multiday( $month, $day_number );

if ( ! $events_multiday ) {
	return;
}
?>

<?php foreach ( $events_multiday as $event ) : ?>

	<?php
	// we receive false, we need to fill an empty space
	if ( false === $event ) {
		$this->template( 'month/event-multiday-spacer' );
		continue;
	}

	$this->template( 'month/event-multiday', [ 'event' => (object) $event, 'day' => $day_number ] );
	?>

<?php endforeach; ?>
