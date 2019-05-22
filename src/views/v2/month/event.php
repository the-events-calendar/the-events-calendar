<?php
/**
 * View: Month Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

$event    = $this->get( 'event' );
$event_id = $event->ID;

$classes = [ 'tribe-events-calendar-month__event' ];

if ( tribe( 'tec.featured_events' )->is_featured( $event_id ) ) {
	$classes[] = 'tribe-events-calendar-month__event--featured';
}

?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

	<?php $this->template( 'month/event/featured-image', [ 'event' => $event ] ); ?>

	<div class="tribe-events-calendar-month__event-details">

		<?php $this->template( 'month/event/date', [ 'event' => $event ] ); ?>
		<?php $this->template( 'month/event/title', [ 'event' => $event ] ); ?>

	</div>

	<?php $this->template( 'month/event/tooltip', [ 'event' => $event ] ); ?>

</div>
