<?php
/**
 * View: Day Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/day/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */

$event    = $this->get( 'event' );
$event_id = $event->ID;

$classes = [ 'tribe-common-g-row', 'tribe-common-g-row--gutters', 'tribe-events-calendar-day__event' ];

if ( tribe( 'tec.featured_events' )->is_featured( $event_id ) ) {
	$classes[] = 'tribe-events-calendar-day__event--featured';
}

?>
<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

	<?php $this->template( 'day/event/featured-image', [ 'event' => $event ] ); ?>

	<div class="tribe-events-calendar-day__event-details tribe-common-g-col">

		<header class="tribe-events-calendar-day__event-header">
			<?php $this->template( 'day/event/date', [ 'event' => $event ] ); ?>
			<?php $this->template( 'day/event/title', [ 'event' => $event ] ); ?>
			<?php $this->template( 'day/event/venue', [ 'event' => $event ] ); ?>
		</header>

		<?php $this->template( 'day/event/description', [ 'event' => $event ] ); ?>

	</div>

</article>
