<?php
/**
 * View: Month Calendar Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day/calendar-events/calendar-event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */

$event    = $this->get( 'event' );
//$event_id = $event->ID;

$classes = [ 'tribe-events-calendar-month__calendar-event' ];

/* @todo fix this once we make event dynamic */
if ( isset( $event->featured ) && $event->featured ) {
	$classes[] = 'tribe-events-calendar-month__calendar-event--featured';
}
?>
<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

	<?php $this->template( 'month/calendar-body/day/calendar-events/calendar-event/featured-image', [ 'event' => $event ] ); ?>

	<div class="tribe-events-calendar-month__calendar-event-details">

		<?php $this->template( 'month/calendar-body/day/calendar-events/calendar-event/date', [ 'event' => $event ] ); ?>
		<?php $this->template( 'month/calendar-body/day/calendar-events/calendar-event/title', [ 'event' => $event ] ); ?>

		<?php $this->template( 'month/calendar-body/day/calendar-events/calendar-event/tooltip', [ 'event' => $event ] ); ?>

	</div>

</article>
